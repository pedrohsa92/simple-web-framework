<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;

class PwaModel extends SimpleModel
{
  protected string $tabela = 'pwa';
  protected string $prefixo = 'pwa';
  protected string $identificador = 'id';
  protected string $escopoContexto = 'manual';
  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function __construct()
  {
    parent::__construct($this->tabela, $this->prefixo, $this->identificador);
  }

  public function getPwaByEmpresa(int $empresaId, ?int $organizacaoId = null): object
  {
    $organizacaoId = $organizacaoId ?: (int) ($this->contexto->organizacao->org_id ?? 0);
    if ($empresaId <= 0 || $organizacaoId <= 0) {
      return $this->respostaErro('Contexto da empresa não identificado.');
    }

    return $this->rawQuery(
      "SELECT * FROM {$this->getTable()} WHERE pwa_idempresa = :pwa_idempresa AND pwa_idorganizacao = :pwa_idorganizacao LIMIT 1",
      ['pwa_idempresa' => $empresaId, 'pwa_idorganizacao' => $organizacaoId],
      true,
      'PWA encontrado com sucesso||Erro ao buscar PWA'
    );
  }

  public function salvarPwa(array $dadosRecebidos, int $organizacaoId, int $empresaId): object
  {
    if ($organizacaoId <= 0 || $empresaId <= 0) {
      return $this->respostaErro('Contexto do PWA não identificado.');
    }

    $dados = $this->normalizarDados($dadosRecebidos);
    if ($dados['pwa_nome'] === '' || $dados['pwa_nomecurto'] === '') {
      return (new SimpleResponse('Atenção', 'Informe o nome e o nome curto do PWA.'))->warning()->noDestroy();
    }

    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $dados['pwa_cor_tema']) || !preg_match('/^#[0-9a-fA-F]{6}$/', $dados['pwa_cor_fundo'])) {
      return (new SimpleResponse('Atenção', 'Informe as cores no formato hexadecimal válido.'))->warning()->noDestroy();
    }

    $getAtual = $this->getPwaByEmpresa($empresaId, $organizacaoId);
    if (!$getAtual->status) {
      return $getAtual;
    }

    $atual = $getAtual->data[0] ?? null;
    if ($atual) {
      $dados['pwa_versao'] = max(1, (int) ($atual->pwa_versao ?? 1)) + 1;
      $dados['pwa_versaomanifesto'] = max(1, (int) ($atual->pwa_versaomanifesto ?? 1)) + 1;
      $dados['pwa_ultimageracao'] = date('Y-m-d H:i:s');
      $salvo = $this->update(
        $dados,
        ['pwa_id' => (int) $atual->pwa_id, 'pwa_idorganizacao' => $organizacaoId, 'pwa_idempresa' => $empresaId],
        'PWA atualizado com sucesso||Erro ao atualizar PWA'
      );
    } else {
      $dados['pwa_chave'] = 'PWA_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
      $dados['pwa_idorganizacao'] = $organizacaoId;
      $dados['pwa_idempresa'] = $empresaId;
      $dados['pwa_ultimageracao'] = date('Y-m-d H:i:s');
      $salvo = $this->insert($dados, 'PWA cadastrado com sucesso||Erro ao cadastrar PWA');
    }

    if ($salvo->status) {
      $this->gerarArquivosPwa($empresaId, $organizacaoId);
    }

    return $salvo;
  }

  public function getImagensByEmpresa(int $empresaId): array
  {
    $base = "storage/empresa/{$empresaId}/pwa";
    $icones = [];
    foreach ([192, 168, 100, 72, 48, 32] as $tamanho) {
      $caminho = "{$base}/{$tamanho}x{$tamanho}.png";
      if ($this->arquivoPublicoExiste($caminho)) {
        $icones[] = ['label' => "{$tamanho}x{$tamanho}", 'src' => Helpers::storage($caminho)];
      }
    }

    $capturas = [];
    foreach (['1080x1920', '1280x720', '1440x2560', '1920x1080', '2048x1536'] as $tamanho) {
      $caminho = "{$base}/screenshots/{$tamanho}.png";
      if ($this->arquivoPublicoExiste($caminho)) {
        $capturas[] = ['label' => $tamanho, 'src' => Helpers::storage($caminho)];
      }
    }

    return ['icones' => $icones, 'capturas' => $capturas];
  }

  private function normalizarDados(array $recebidos): array
  {
    $status = ($recebidos['pwa_status'] ?? 'Inativo') === 'Ativo' ? 'Ativo' : 'Inativo';
    return [
      'pwa_status' => $status,
      'pwa_statusclass' => $status === 'Ativo' ? 'success' : 'danger',
      'pwa_offline_ativo' => 1,
      'pwa_nome' => trim((string) ($recebidos['pwa_nome'] ?? '')),
      'pwa_nomecurto' => trim((string) ($recebidos['pwa_nomecurto'] ?? '')),
      'pwa_descricao' => trim((string) ($recebidos['pwa_descricao'] ?? '')) ?: null,
      'pwa_idioma' => 'pt-BR',
      'pwa_direcao' => 'ltr',
      'pwa_exibicao' => 'standalone',
      'pwa_exibicaoalternativa' => json_encode(['standalone'], JSON_UNESCAPED_UNICODE),
      'pwa_orientacao' => 'portrait',
      'pwa_cor_tema' => strtoupper(trim((string) ($recebidos['pwa_cor_tema'] ?? '#0D6EFD'))),
      'pwa_cor_fundo' => strtoupper(trim((string) ($recebidos['pwa_cor_fundo'] ?? '#FFFFFF'))),
      'pwa_categorias' => json_encode(['business', 'productivity'], JSON_UNESCAPED_UNICODE),
      'pwa_classificacao_iarc' => null,
      'pwa_aplicativosrelacionados' => json_encode([], JSON_UNESCAPED_UNICODE),
      'pwa_preferiraplicativosrelacionados' => 0,
      'pwa_atalhos' => json_encode([], JSON_UNESCAPED_UNICODE),
      'pwa_nomeversao' => 'Produção'
    ];
  }

  private function gerarArquivosPwa(int $empresaId, int $organizacaoId): void
  {
    $get = $this->getPwaByEmpresa($empresaId, $organizacaoId);
    $pwa = $get->status ? ($get->data[0] ?? null) : null;
    if (!$pwa) return;

    $pwa->pwa_icones = $this->gerarIcones($empresaId);
    $pwa->pwa_capturas = $this->gerarCapturas($empresaId);
    $urlPaginaEmpresa = $this->getUrlPaginaEmpresaPublica($empresaId, $organizacaoId);
    $basePath = dirname(__DIR__, 2);
    file_put_contents($basePath . "/manifest-{$empresaId}.json", json_encode($this->renderManifest($pwa, $urlPaginaEmpresa), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    file_put_contents($basePath . "/serviceworker-{$empresaId}.js", $this->renderServiceWorker($pwa, $urlPaginaEmpresa));
    file_put_contents($basePath . "/pwa-{$empresaId}.js", $this->renderPwaScript($empresaId));
  }

  private function gerarIcones(int $empresaId): array
  {
    $base = "storage/empresa/{$empresaId}/pwa";
    $logo = $this->encontrarLogo($empresaId);
    $icones = [];
    foreach ([192, 168, 100, 72, 48, 32] as $tamanho) {
      $relativo = "{$base}/{$tamanho}x{$tamanho}.png";
      $destino = dirname(__DIR__, 2) . '/' . $relativo;
      if ($logo && function_exists('imagecreatetruecolor')) {
        $this->redimensionarImagem($logo, $destino, $tamanho, $tamanho);
      }
      if ($this->arquivoPublicoExiste($relativo)) {
        $icones[] = ['src' => Helpers::storage($relativo), 'sizes' => "{$tamanho}x{$tamanho}", 'type' => 'image/png'];
      }
    }
    if (!$icones) {
      $icones[] = ['src' => Helpers::storage('storage/global/favicon.png'), 'sizes' => '192x192', 'type' => 'image/png'];
    }
    return $icones;
  }

  private function gerarCapturas(int $empresaId): array
  {
    $icone = dirname(__DIR__, 2) . "/storage/empresa/{$empresaId}/pwa/192x192.png";
    if (!file_exists($icone)) $icone = dirname(__DIR__, 2) . '/storage/global/favicon.png';
    if (!file_exists($icone) || !function_exists('imagecreatetruecolor')) return [];

    $capturas = [];
    foreach ([[1080, 1920], [1280, 720], [1440, 2560], [1920, 1080], [2048, 1536]] as [$largura, $altura]) {
      $relativo = "storage/empresa/{$empresaId}/pwa/screenshots/{$largura}x{$altura}.png";
      $destino = dirname(__DIR__, 2) . '/' . $relativo;
      if ($this->gerarCaptura($icone, $destino, $largura, $altura)) {
        $capturas[] = ['src' => Helpers::storage($relativo), 'sizes' => "{$largura}x{$altura}", 'type' => 'image/png'];
      }
    }
    return $capturas;
  }

  private function encontrarLogo(int $empresaId): ?string
  {
    $get = (new EmpresaModel())->getEmpresaById($empresaId, false);
    $empresa = $get->status ? ($get->data[0] ?? null) : null;
    $logo = trim((string) ($empresa->emp_logo ?? ''));
    $raiz = dirname(__DIR__, 2);
    $candidatos = [];
    if ($logo !== '') {
      $candidatos[] = $raiz . '/' . ltrim($logo, '/');
      $candidatos[] = $raiz . "/storage/empresa/{$empresaId}/logo/" . basename($logo);
    }
    $candidatos[] = $raiz . '/storage/global/favicon.png';
    foreach ($candidatos as $candidato) {
      if (is_file($candidato) && @getimagesize($candidato)) return $candidato;
    }
    return null;
  }

  private function redimensionarImagem(string $origem, string $destino, int $largura, int $altura): bool
  {
    $info = @getimagesize($origem);
    if (!$info) return false;
    $imagem = match ($info[2]) {
      IMAGETYPE_JPEG => @imagecreatefromjpeg($origem),
      IMAGETYPE_PNG => @imagecreatefrompng($origem),
      IMAGETYPE_GIF => @imagecreatefromgif($origem),
      IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($origem) : false,
      default => false
    };
    if (!$imagem) return false;
    $nova = imagecreatetruecolor($largura, $altura);
    imagealphablending($nova, false);
    imagesavealpha($nova, true);
    imagecopyresampled($nova, $imagem, 0, 0, 0, 0, $largura, $altura, $info[0], $info[1]);
    if (!is_dir(dirname($destino))) mkdir(dirname($destino), 0775, true);
    $ok = imagepng($nova, $destino, 9);
    imagedestroy($nova);
    imagedestroy($imagem);
    return $ok;
  }

  private function gerarCaptura(string $iconePath, string $destino, int $largura, int $altura): bool
  {
    $info = @getimagesize($iconePath);
    if (!$info) return false;
    $icone = match ($info[2]) {
      IMAGETYPE_JPEG => @imagecreatefromjpeg($iconePath),
      IMAGETYPE_PNG => @imagecreatefrompng($iconePath),
      IMAGETYPE_GIF => @imagecreatefromgif($iconePath),
      IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($iconePath) : false,
      default => false
    };
    if (!$icone) return false;
    $fundo = imagecreatetruecolor($largura, $altura);
    imagefill($fundo, 0, 0, imagecolorallocate($fundo, 255, 255, 255));
    $tamanho = (int) (min($largura, $altura) * 0.3);
    $redimensionado = imagecreatetruecolor($tamanho, $tamanho);
    imagealphablending($redimensionado, false);
    imagesavealpha($redimensionado, true);
    imagecopyresampled($redimensionado, $icone, 0, 0, 0, 0, $tamanho, $tamanho, $info[0], $info[1]);
    imagecopy($fundo, $redimensionado, (int) (($largura - $tamanho) / 2), (int) (($altura - $tamanho) / 2), 0, 0, $tamanho, $tamanho);
    if (!is_dir(dirname($destino))) mkdir(dirname($destino), 0775, true);
    $ok = imagepng($fundo, $destino);
    imagedestroy($fundo);
    imagedestroy($redimensionado);
    imagedestroy($icone);
    return $ok;
  }

  private function arquivoPublicoExiste(string $caminho): bool
  {
    return is_file(dirname(__DIR__, 2) . '/' . ltrim($caminho, '/'));
  }

  private function getUrlPaginaEmpresaPublica(int $empresaId, int $organizacaoId): string
  {
    $empresaTable = DB_PREFIX . '_empresa';
    $resultado = $this->rawQuery(
      "SELECT emp_urlamigavel, emp_status, emp_webapp_status
       FROM {$empresaTable}
       WHERE emp_id = :pwa_empresa_url_id
         AND emp_idorganizacao = :pwa_empresa_url_org
       LIMIT 1",
      [
        'pwa_empresa_url_id' => $empresaId,
        'pwa_empresa_url_org' => $organizacaoId
      ],
      true,
      'URL pública da empresa encontrada||Erro ao buscar URL pública da empresa'
    );
    $empresa = $resultado->status ? ($resultado->data[0] ?? null) : null;
    $slug = strtolower(trim((string) ($empresa->emp_urlamigavel ?? '')));

    if (
      $empresa &&
      ($empresa->emp_status ?? '') === 'Ativo' &&
      ($empresa->emp_webapp_status ?? '') === 'Publica' &&
      preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)
    ) {
      return Helpers::url($slug);
    }

    return Helpers::url();
  }

  private function renderManifest(object $pwa, string $urlPaginaEmpresa): array
  {
    return [
      'id' => $urlPaginaEmpresa,
      'name' => $pwa->pwa_nome,
      'short_name' => $pwa->pwa_nomecurto,
      'start_url' => $urlPaginaEmpresa,
      'scope' => Helpers::url(),
      'display' => $pwa->pwa_exibicao,
      'theme_color' => $pwa->pwa_cor_tema,
      'background_color' => $pwa->pwa_cor_fundo,
      'icons' => $pwa->pwa_icones,
      'description' => $pwa->pwa_descricao,
      'lang' => $pwa->pwa_idioma,
      'dir' => $pwa->pwa_direcao,
      'orientation' => $pwa->pwa_orientacao,
      'categories' => json_decode((string) $pwa->pwa_categorias, true) ?: [],
      'version' => (int) $pwa->pwa_versao,
      'version_name' => $pwa->pwa_nomeversao,
      'manifest_version' => (int) $pwa->pwa_versaomanifesto
    ];
  }

  private function renderServiceWorker(object $pwa, string $urlPaginaEmpresa): string
  {
    $cache = 'icliv-pwa-' . (int) $pwa->pwa_idempresa . '-v' . (int) $pwa->pwa_versao;
    $baseUrl = rtrim(Helpers::url(), '/');
    $startUrlJson = json_encode($urlPaginaEmpresa, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return "const CACHE_NAME = '{$cache}';\nconst BASE_URL = '{$baseUrl}';\nconst START_URL = {$startUrlJson};\n"
      . "self.addEventListener('install', event => event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.add(START_URL)).then(() => self.skipWaiting())));\n"
      . "self.addEventListener('activate', event => event.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(key => key.startsWith('icliv-pwa-') && key !== CACHE_NAME).map(key => caches.delete(key)))).then(() => self.clients.claim())));\n"
      . "self.addEventListener('fetch', event => { if (event.request.method !== 'GET') return; event.respondWith(fetch(event.request).then(response => { if (response.ok && new URL(event.request.url).origin === self.location.origin) { const copy = response.clone(); caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy)); } return response; }).catch(() => caches.match(event.request))); });\n";
  }

  private function renderPwaScript(int $empresaId): string
  {
    $manifest = Helpers::url("manifest-{$empresaId}.json");
    $serviceWorker = Helpers::url("serviceworker-{$empresaId}.js");
    return "(function(){var m=document.createElement('link');m.rel='manifest';m.href='{$manifest}';document.head.appendChild(m);if('serviceWorker' in navigator){navigator.serviceWorker.register('{$serviceWorker}').catch(function(){});}window.iclivInstalarPwa=function(){return navigator.serviceWorker&&navigator.serviceWorker.getRegistration();};})();\n";
  }

  private function respostaErro(string $texto): object
  {
    return (new SimpleResponse('Erro', $texto))->erro()->noDestroy();
  }
}
