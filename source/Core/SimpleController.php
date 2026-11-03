<?php

namespace Source\Core;

use Helpers;
use League\Plates\Engine;
use Source\Core\SimpleResponse;
use Source\Core\SimpleSession;
use Source\Model\PlanoContratadoModel;
use Source\Model\UsuarioModel;

class SimpleController
{
  protected Engine $templates;
  protected SimpleSession $session;

  protected ?object $projeto = null;
  protected ?object $usuarioLogado = null;
  protected ?object $contexto = null;

  protected object $pagina;
  protected ?string $tabLink = null;

  public function __construct()
  {
    $this->session = new SimpleSession();

    $this->projeto = $this->session->get('PROJETO') ?? null;
    $this->usuarioLogado = $this->session->get('USUARIO') ?? null;
    $this->contexto = $this->session->get('CONTEXTO') ?? null;

    if (is_null($this->projeto)) {
      (new SimpleResponse("Erro de Configuração", "Projeto não definido em sessão"))
        ->erro()
        ->html();
      exit;
    }

    if (!is_null($this->usuarioLogado)) {
      $this->usuarioLogado = (new UsuarioModel())->atualizarUsuarioLogado();
      $this->carregarContexto();
    }

    $this->validarUsuarioBloqueado();
    $this->validarRegularizacao();
    $this->validarConfiguracaoInicial();
    $this->iniciarTemplates();
    $this->iniciarPagina();
  }

  protected function carregarContexto(): void
  {
    $this->contexto = $this->session->get('CONTEXTO') ?? null;

    if ($this->contextoValido()) {
      return;
    }

    if (
      !empty($this->usuarioLogado->organizacao->org_id)
      && !empty($this->usuarioLogado->empresa->emp_id)
    ) {
      $this->contexto = (object) [
        'origem' => 'principal',
        'organizacao' => $this->usuarioLogado->organizacao,
        'empresa' => $this->usuarioLogado->empresa
      ];

      $this->session->set('CONTEXTO', $this->contexto);
      return;
    }

    $this->contexto = null;
  }

  protected function contextoValido(): bool
  {
    if (!is_object($this->contexto)) {
      return false;
    }

    if (empty($this->contexto->origem)) {
      return false;
    }

    if (empty($this->contexto->organizacao->org_id)) {
      return false;
    }

    if (empty($this->contexto->empresa->emp_id)) {
      return false;
    }

    return true;
  }

  protected function podeRegularizarPlano(): bool
  {
    $permissoes = (array) ($this->usuarioLogado->permissoes ?? []);

    return isset($permissoes['organizacao.plano.contratar'])
      && (int) $permissoes['organizacao.plano.contratar'] >= 1;
  }

  protected function caminhoRequisicao(): string
  {
    $caminho = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $base = parse_url($this->baseUrl(), PHP_URL_PATH) ?? '';

    if ($base !== '' && $base !== '/' && str_starts_with($caminho, $base)) {
      $caminho = substr($caminho, strlen($base));
    }

    return trim($caminho, '/');
  }

  protected function locationRequisicao(): string
  {
    $rota = $this->caminhoRequisicao();
    $query = trim((string) ($_SERVER['QUERY_STRING'] ?? ''));

    return $rota . ($query !== '' ? '?' . $query : '');
  }

  protected function normalizarLocationLogin(?string $location): string
  {
    $location = trim(rawurldecode((string) $location));

    if ($location === '') {
      return 'dashboard';
    }

    $partes = parse_url($location);

    if ($partes === false || !empty($partes['scheme']) || !empty($partes['host']) || !empty($partes['user']) || !empty($partes['pass'])) {
      return 'dashboard';
    }

    $caminho = trim((string) ($partes['path'] ?? ''), '/');

    if ($caminho === '' || str_contains($caminho, '..') || str_contains($caminho, '\\')) {
      return 'dashboard';
    }

    $query = isset($partes['query']) && $partes['query'] !== '' ? '?' . $partes['query'] : '';

    return $caminho . $query;
  }

  protected function validarRegularizacao(): void
  {
    if (is_null($this->usuarioLogado) || empty($this->contexto->organizacao->org_id)) {
      return;
    }

    $rota = $this->caminhoRequisicao();

    if ($rota === '' || str_starts_with($rota, 'contrate') || str_starts_with($rota, 'webhook')) {
      return;
    }

    $situacao = (new PlanoContratadoModel())->getSituacaoAcessoPorOrganizacao((int) $this->contexto->organizacao->org_id);

    if (($situacao->modo ?? 'liberado') !== 'regularizacao') {
      return;
    }

    $admin = $this->podeRegularizarPlano();

    if ($admin) {
      $permitidas = [
        'organizacao/regularizacao',
        'organizacao/plano',
        'organizacao/plano/renovar',
        'organizacao/gerarPagamento',
        'organizacao/salvarRenovacao',
        'login',
        'usuario/logout',
        'usuario/setTema',
        'usuario/setSideBar',
        'usuario/setPaletaCores',
        'contexto/listar',
        'contexto/trocar',
        'usuario/bloqueado',
        'usuario/desbloquearUsuario'
      ];

      $permitida = in_array($rota, $permitidas, true)
        || str_starts_with($rota, 'organizacao/plano/');

      if ($permitida) {
        return;
      }

      header('Location: ' . Helpers::url('organizacao/regularizacao'));
      exit;
    }

    $permitida = in_array($rota, [
      'dashboard',
      'dashboard/getIndicadores',
      'organizacao/regularizacao',
      'login',
      'usuario/logout',
      'usuario/setTema',
      'usuario/setSideBar',
      'usuario/setPaletaCores',
      'usuario/bloqueado',
      'usuario/desbloquearUsuario'
    ], true);

    if ($permitida) {
      return;
    }

    header('Location: ' . Helpers::url('organizacao/regularizacao'));
    exit;
  }

  protected function validarConfiguracaoInicial(): void
  {
    if (is_null($this->usuarioLogado) || !$this->contextoValido()) {
      return;
    }

    $rota = $this->caminhoRequisicao();
    $rotasPermitidas = [
      'dashboard/assistente',
      'dashboard/salvarAssistente',
      'usuario/logout',
      'usuario/setTema',
      'usuario/setSideBar',
      'usuario/setPaletaCores',
      'contexto/listar',
      'contexto/trocar'
    ];

    if (in_array($rota, $rotasPermitidas, true)) {
      return;
    }

    // O plano é resolvido pelo SimpleWeb por organização. Sem plano vigente,
    // a configuração inicial ainda não é aplicável.
    if (!is_object($this->contexto->plano ?? null) || empty($this->contexto->plano->plc_id)) {
      return;
    }

    $empresaId = (int) ($this->contexto->empresa->emp_id ?? 0);
    $organizacaoId = (int) ($this->contexto->organizacao->org_id ?? 0);
    if ($empresaId <= 0 || $organizacaoId <= 0) {
      return;
    }

    $empresaModel = new SimpleModel('empresa', 'emp');
    $resultado = $empresaModel->rawQuery(
      "SELECT emp_configuracao_inicial
       FROM {$empresaModel->getTable()}
       WHERE emp_id = :emp_id
         AND emp_idorganizacao = :org_id
         AND emp_status = 'Ativo'
       LIMIT 1",
      ['emp_id' => $empresaId, 'org_id' => $organizacaoId],
      true,
      'Configuração inicial encontrada||Erro ao consultar configuração inicial'
    );

    if (!$resultado->status || $resultado->total !== 1) {
      return;
    }

    if ((int) ($resultado->data[0]->emp_configuracao_inicial ?? 0) === 1) {
      return;
    }

    header('Location: ' . Helpers::url('dashboard/assistente'));
    exit;
  }

  protected function getOrganizacaoAtiva(): ?object
  {
    return $this->contexto->organizacao
      ?? $this->usuarioLogado->organizacao
      ?? null;
  }

  protected function getEmpresaAtiva(): ?object
  {
    return $this->contexto->empresa
      ?? $this->usuarioLogado->empresa
      ?? null;
  }

  protected function baseUrl(): string
  {
    $host = $_SERVER['HTTP_HOST'] ?? '';

    return rtrim((strpos($host, 'localhost') !== false ? CONF_URL_LOCALHOST : CONF_URL_PRODUCAO), '/');
  }

  protected function iniciarTemplates(): void
  {
    $this->templates = new Engine(__DIR__ . '/../Web');
    $this->templates->addFolder('site', __DIR__ . '/../Web/template/site');
    $this->templates->addFolder('sistema', __DIR__ . '/../Web/template/sistema');
  }

  protected function iniciarPagina(): void
  {
    $this->pagina = (object) [
      'meta' => '',
      'webapp' => '',
      'pwa' => '',
      'serviceworker' => '',
      'structuredata' => '',
      'framework' => (object) [
        'css' => '',
        'js' => ''
      ],
      'assets' => (object) [
        'css' => '',
        'js' => ''
      ],
      'breadcrumb' => []
    ];
  }

  protected function validarUsuarioBloqueado(): void
  {
    if (is_null($this->usuarioLogado)) {
      return;
    }

    if (empty($this->usuarioLogado->bloqueado)) {
      return;
    }

    $requestUri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');

    $rotasPermitidas = [
      'usuario/bloqueado',
      'usuario/desbloquearUsuario',
      'usuario/logout'
    ];

    foreach ($rotasPermitidas as $rota) {
      if (str_contains($requestUri, $rota)) {
        return;
      }
    }

    header("Location: " . Helpers::url('usuario/bloqueado'));
    exit;
  }

  public function validarUsuarioLogado(bool $json = false): void
  {
    if (!is_null($this->usuarioLogado)) {
      return;
    }

    $response = new SimpleResponse("Acesso Negado", "Você precisa estar logado para acessar esta página.");

    if ($json || ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
      $response->erro()->json();
      exit;
    }

    $loginQuery = http_build_query([
      'location' => $this->locationRequisicao(),
      'message' => $response->text
    ]);

    $loginUrl = Helpers::url('login') . '?' . $loginQuery;

    if (!headers_sent()) {
      header('Location: ' . $loginUrl);
      exit;
    }

    $response->erro()->page(true, $loginUrl);
    exit;
  }

  public function validarPermissoes(array $permissoes, string $condicao = '||'): object
  {
    $this->validarUsuarioLogado(($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');

    $permissoesUsuario = (array) ($this->usuarioLogado->permissoes ?? []);
    $resultados = [];

    foreach ($permissoes as $permissao) {
      $resultados[$permissao] = isset($permissoesUsuario[$permissao]) && (int) $permissoesUsuario[$permissao] >= 1;
    }

    $permitido = $condicao === '&&'
      ? !in_array(false, $resultados, true)
      : in_array(true, $resultados, true);

    if ($permitido) {
      return (new SimpleResponse("Permissão", "Permissão concedida"))
        ->sucesso()
        ->setArrayParams([
          'permissoes' => $resultados
        ])
        ->noDestroy();
    }

    $negadas = array_keys(array_filter($resultados, fn($item) => !$item));
    $text = "Você não tem permissão: " . implode(', ', $negadas);

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
      (new SimpleResponse("Permissão Negada", $text))
        ->erro()
        ->json();

      exit;
    }

    $this->render('template/sistema/erro', [
      'erro' => (object) [
        'status' => false,
        'title' => 'Permissão Negada',
        'text' => $text
      ]
    ]);

    exit;
  }

  public function render(string $view, array $data = []): void
  {
    if (empty($this->pagina->meta)) {
      $this->setMetaTags();
    }

    if (empty($this->pagina->webapp)) {
      $this->setWebAppMetaTags();
    }

    if (empty($this->pagina->pwa)) {
      $this->setPwa();
    }

    if (empty($this->pagina->structuredata)) {
      $this->setStructuredData();
    }

    $data['projeto'] = $this->projeto;
    $data['usuarioLogado'] = $this->usuarioLogado;
    $data['contexto'] = $this->contexto;
    $data['pagina'] = $this->pagina;
    $data['tabLink'] = $this->tabLink ?? $data['tabLink'] ?? null;

    echo $this->templates->render($view, $data);
  }

  public function setBreadcrumb(array $items): void
  {
    $breadcrumb = [
      (object) [
        'title' => 'Página Inicial',
        'label' => '<i class="fas fa-home"></i>',
        'url' => Helpers::url('dashboard'),
        'active' => false
      ]
    ];

    foreach ($items as $index => $item) {
      $breadcrumb[] = (object) [
        'title' => $item['title'] ?? $item['label'] ?? 'Sem título',
        'label' => $item['label'] ?? 'Sem rótulo',
        'url' => $item['url'] ?? '#',
        'active' => $index === array_key_last($items)
      ];
    }

    $this->pagina->breadcrumb = $breadcrumb;
  }

  public function setTabLink(?string $url = ''): void
  {
    $this->tabLink = $url;
  }

  public function setMetaTags(array $options = []): void
  {
    $empresa = $this->getEmpresaAtiva();
    $organizacao = $this->getOrganizacaoAtiva();
    $projeto = $this->projeto;

    $title = $options['title']
      ?? $empresa->emp_nomefantasia
      ?? $organizacao->org_nomefantasia
      ?? $projeto->prj_titulo
      ?? 'Página sem título';

    $description = $options['description']
      ?? $empresa->emp_observacao
      ?? $organizacao->org_observacao
      ?? $projeto->prj_descricao
      ?? 'Página sem descrição';

    $image = $options['image'] ?? Helpers::storage(
      $empresa->emp_logo
        ?? $organizacao->org_logo
        ?? $projeto->prj_logo
        ?? 'storage/global/logo.png'
    );

    $url = $options['url'] ?? $this->baseUrl() . ($_SERVER['REQUEST_URI'] ?? '');

    $this->pagina->meta = "
      <meta name='description' content='{$description}'>
      <meta property='og:title' content='{$title}'>
      <meta property='og:description' content='{$description}'>
      <meta property='og:image' content='{$image}'>
      <meta property='og:url' content='{$url}'>
      <meta name='twitter:card' content='summary_large_image'>
      <link rel='canonical' href='{$url}'>
      <title>{$title}</title>";
  }

  public function setWebAppMetaTags(array $options = []): void
  {
    $empresa = $this->getEmpresaAtiva();
    $organizacao = $this->getOrganizacaoAtiva();

    $favicon = $options['favicon'] ?? Helpers::storage(
      $organizacao->org_favicon
        ?? $this->projeto->prj_favicon
        ?? 'storage/global/favicon.png'
    );

    $themeColor = $options['theme-color'] ?? '#000000';

    $this->pagina->webapp = "
      <meta name='theme-color' content='{$themeColor}'>
      <meta name='mobile-web-app-capable' content='yes'>
      <meta name='apple-mobile-web-app-status-bar-style' content='black-translucent'>
      <link rel='apple-touch-icon' href='{$favicon}'>
      <link rel='shortcut icon' href='{$favicon}'>";
  }

  public function setPwa(?int $empId = null): void
  {
    $empresa = $this->getEmpresaAtiva();
    $empresaId = $empId ?? $empresa->emp_id ?? null;

    if (!$empresaId) {
      return;
    }

    $manifestPath = __DIR__ . "/../../manifest-{$empresaId}.json";

    if (file_exists($manifestPath)) {
      $this->pagina->pwa .= "<link rel='manifest' href='" . Helpers::url("manifest-{$empresaId}.json") . "'>";
    }

    $pwaJsPath = __DIR__ . "/../../pwa-{$empresaId}.js";

    if (file_exists($pwaJsPath)) {
      $this->pagina->pwa .= "<script src='" . Helpers::url("pwa-{$empresaId}.js") . "' defer></script>";
    }

    $serviceWorkerPath = __DIR__ . "/../../serviceworker-{$empresaId}.js";

    if (file_exists($serviceWorkerPath)) {
      $serviceWorkerUrl = Helpers::url("serviceworker-{$empresaId}.js");

      $this->pagina->serviceworker = "
        <script>
          if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('{$serviceWorkerUrl}');
          }
        </script>";
    }
  }

  public function setStructuredData(array $options = []): void
  {
    $empresa = $this->getEmpresaAtiva();
    $organizacao = $this->getOrganizacaoAtiva();

    $structuredData = [
      "@context" => "https://schema.org",
      "@type" => $options['type'] ?? "Organization",
      "name" => $options['name']
        ?? $empresa->emp_nomefantasia
        ?? $organizacao->org_nomefantasia
        ?? $this->projeto->prj_titulo
        ?? "Projeto",
      "url" => $options['url'] ?? $this->baseUrl(),
      "logo" => $options['logo'] ?? Helpers::storage(
        $empresa->emp_logo
          ?? $organizacao->org_logo
          ?? $this->projeto->prj_logo
          ?? 'storage/global/logo.png'
      ),
      "sameAs" => $options['sameAs'] ?? []
    ];

    $this->pagina->structuredata = '<script type="application/ld+json">' . json_encode($structuredData, JSON_UNESCAPED_UNICODE) . '</script>';
  }

  public function setFramework(array $libs = [], bool $incrementa = true): void
  {
    $jsonFile = __DIR__ . "/../../framework/SimpleFramework.json";

    if (!file_exists($jsonFile)) {
      (new SimpleResponse("Erro de Configuração", "O arquivo SimpleFramework.json não foi encontrado"))
        ->erro()
        ->html();
      exit;
    }

    $frameworkData = json_decode(file_get_contents($jsonFile));

    if (json_last_error() !== JSON_ERROR_NONE) {
      (new SimpleResponse("Erro de Configuração", "Erro ao decodificar SimpleFramework.json"))
        ->erro()
        ->html();
      exit;
    }

    if (!$incrementa) {
      $this->pagina->framework->css = '';
      $this->pagina->framework->js = '';
    }

    $frameworkLibs = $incrementa ? ($frameworkData->framework ?? []) : [];

    foreach ($libs as $lib) {
      if (is_object($lib)) {
        $frameworkLibs[] = $lib;
        continue;
      }

      $found = array_values(array_filter($frameworkData->framework ?? [], fn($item) => $item->name === $lib));

      if (!empty($found)) {
        $frameworkLibs[] = $found[0];
      }
    }

    foreach ($frameworkLibs as $libData) {
      if (isset($libData->css)) {
        foreach ((array) $libData->css as $cssFile) {
          $cssUrl = $this->baseUrl() . "/framework/{$libData->name}/{$cssFile}";

          if (!str_contains($this->pagina->framework->css, $cssUrl)) {
            $this->pagina->framework->css .= "<link rel='stylesheet' href='{$cssUrl}'>\n";
          }
        }
      }

      if (isset($libData->js)) {
        foreach ((array) $libData->js as $jsFile) {
          $jsUrl = $this->baseUrl() . "/framework/{$libData->name}/{$jsFile}";

          if (!str_contains($this->pagina->framework->js, $jsUrl)) {
            $this->pagina->framework->js .= "<script src='{$jsUrl}'></script>\n";
          }
        }
      }
    }
  }

  public function setAssetsCss($cssFiles): void
  {
    foreach ((array) $cssFiles as $cssFile) {
      $filePath = __DIR__ . "/../Web/assets/css/{$cssFile}";
      $cssUrl = $this->baseUrl() . "/source/Web/assets/css/{$cssFile}";

      if (file_exists($filePath)) {
        $this->pagina->assets->css .= "<link rel='stylesheet' href='{$cssUrl}'>\n";
      }
    }
  }

  public function setAssetsJs($jsFiles): void
  {
    foreach ((array) $jsFiles as $jsFile) {
      $fileName = explode('?', (string) $jsFile, 2)[0];
      $filePath = __DIR__ . "/../Web/assets/js/{$fileName}";
      $jsUrl = $this->baseUrl() . "/source/Web/assets/js/{$jsFile}";

      if (file_exists($filePath)) {
        $this->pagina->assets->js .= "<script src='{$jsUrl}'></script>\n";
      }
    }
  }
}
