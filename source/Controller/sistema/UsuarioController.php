<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Core\SimpleSession;
use Source\Model\OrganizacaoModel;
use Source\Model\EmpresaModel;
use Source\Model\UsuarioModel;
use Source\Model\EnderecoModel;
use Source\Model\NivelAcessoModel;
use Source\Model\PermissaoModel;
use Source\Model\TipoContatoModel;
use Source\Model\CadastroBasicoModel;
use Source\Model\ContatoModel;
use Source\Model\ArquivoModel;
use Source\Service\LimitePlanoService;

class UsuarioController extends SimpleController
{
  use CadastroBasicoModuloTrait;

  protected function moduloCadastroBasico(): string { return 'usuario'; }

  public function __construct()
  {
    parent::__construct();

    $this->setFramework();

    $this->setAssetsCss('phsa.css');
    $this->setAssetsCss('sistema.css');
    $this->setAssetsCss('global.css');

    $this->setAssetsJs('sistema.js'); 
    $this->setAssetsJs('simplemodal.js'); 
    $this->setAssetsJs('global.js');
  }

  public function login()
  {
    if (!is_null($this->usuarioLogado) && !empty($this->usuarioLogado->usu_id)) {
      $location = $this->normalizarLocationLogin($_GET['location'] ?? 'dashboard');
      header('Location: ' . Helpers::url($location));
      exit;
    }

    $this->setMetaTags([
      'title' => 'Login',
      'description' => 'Página de login',
      'image' => Helpers::url('storage/global/logo.png')
    ]);

    $this->render('sistema/usuario/login', [
      'getSexo' => (new CadastroBasicoModel('sexo'))->getCadastroBasicoAtivo(),
      'getEstadoCivil' => (new CadastroBasicoModel('estadocivil'))->getCadastroBasicoAtivo()
    ]);
  }

  public function validarLogin(): void
  {
    $login = trim((string) ($_POST['usu_login'] ?? ''));
    $senha = trim((string) ($_POST['usu_senha'] ?? ''));
    $location = $this->normalizarLocationLogin($_POST['location'] ?? 'dashboard');
    $empId = !empty($_POST['usu_idempresa']) ? (int) $_POST['usu_idempresa'] : null;

    if ($login === '') {
      (new SimpleResponse("Acesso negado", "Informe seu e-mail ou usuário."))
        ->erro()
        ->json();
      exit;
    }

    if ($senha === '') {
      (new SimpleResponse("Acesso negado", "Informe sua senha."))
        ->erro()
        ->json();
      exit;
    }

    $usuarioModel = new UsuarioModel();
    $getUsuarios = $usuarioModel->getUsuariosLogin($login, $empId);

    if (!$getUsuarios->status) {
      (new SimpleResponse("Acesso negado", "Erro ao validar o acesso."))
        ->erro()
        ->setArrayParams([
          'errorInfo' => $getUsuarios->errorInfo ?? null
        ])
        ->json();
      exit;
    }

    if ($getUsuarios->total == 0) {
      (new SimpleResponse("Acesso negado", "Usuário ou senha inválidos."))
        ->erro()
        ->json();
      exit;
    }

    // O login utiliza o contexto principal salvo no cadastro do usuário.
    // A troca de organização ou igreja ocorre depois da autenticação.
    if ($getUsuarios->total > 1 && is_null($empId)) {
      $getUsuarios->data = [$getUsuarios->data[0]];
      $getUsuarios->total = 1;
    }

    if ($getUsuarios->total != 1) {
      (new SimpleResponse("Acesso negado", "Não foi possível identificar a empresa do usuário."))
        ->erro()
        ->json();
      exit;
    }

    $usuario = $getUsuarios->data[0];

    if (($usuario->org_statusambiente ?? 'Ativo') !== 'Ativo') {
      (new SimpleResponse("Ambiente indisponível", "O ambiente desta organização não está ativo no momento."))
        ->warning()
        ->json();
      exit;
    }

    if (
      ($usuario->org_ambiente ?? 'Compartilhado') === 'Dedicado' &&
      (!defined('CONF_ORG_ID') || (int) CONF_ORG_ID !== (int) $usuario->usu_idorganizacao)
    ) {
      if (empty($usuario->org_url_dedicada)) {
        (new SimpleResponse("Ambiente dedicado não configurado", "A URL dedicada da organização não foi configurada."))
          ->erro()
          ->json();
        exit;
      }

      (new SimpleResponse("Redirecionando", "Você será redirecionado para o ambiente dedicado."))
        ->sucesso()
        ->setArrayParams([
          'url' => rtrim($usuario->org_url_dedicada, '/') . '/login?' . http_build_query([
            'location' => $location
          ])
        ])
        ->json();
      exit;
    }

    if (empty($usuario->usu_senha) || !password_verify($senha, $usuario->usu_senha)) {
      (new SimpleResponse("Acesso negado", "Usuário ou senha inválidos."))
        ->erro()
        ->json();
      exit;
    }

    $registroLogin = $usuarioModel->registrarLogin((int) $usuario->usu_id);

    if (!$registroLogin->status) {
      (new SimpleResponse("Erro ao registrar login", "Não foi possível registrar o acesso do usuário."))
        ->erro()
        ->setArrayParams([
          'errorInfo' => $registroLogin->errorInfo ?? null
        ])
        ->json();
      exit;
    }

    $organizacaoModel = new OrganizacaoModel();
    $getOrganizacao = $organizacaoModel->findById((int) $usuario->usu_idorganizacao);

    if (!$getOrganizacao->status || $getOrganizacao->total != 1) {
      (new SimpleResponse("Erro ao iniciar sessão", "Não foi possível carregar a organização do usuário."))
        ->erro()
        ->json();
      exit;
    }

    $empresaModel = new EmpresaModel();
    $getEmpresa = $empresaModel->findById((int) $usuario->usu_idempresa);

    if (!$getEmpresa->status || $getEmpresa->total != 1) {
      (new SimpleResponse("Erro ao iniciar sessão", "Não foi possível carregar a empresa do usuário."))
        ->erro()
        ->json();
      exit;
    }

    $session = new SimpleSession();
    $session->set('ORGANIZACAO', $getOrganizacao->data[0]);
    $session->set('EMPRESA', $getEmpresa->data[0]);
    $session->set('USUARIO', $usuarioModel->prepararUsuarioSessao($usuario));

    (new SimpleResponse("Login realizado", "Acesso validado com sucesso."))
      ->sucesso()
      ->setArrayParams([
        'location' => Helpers::url($location ?: 'dashboard')
      ])
      ->json();
    exit;
  }

  public function setSideBar(): void
  {
    $this->validarUsuarioLogado(true);

    $sidebarState = (int) ($_POST['usu_sidebar'] ?? 1);

    if (!in_array($sidebarState, [1, 2, 3])) {
      $sidebarState = 1;
    }

    $usuarioModel = new UsuarioModel();

    $response = $usuarioModel->update(
      [
        'usu_sidebar' => $sidebarState
      ],
      [
        'usu_id' => $this->usuarioLogado->usu_id
      ],
      "Sidebar atualizada com sucesso||Erro ao atualizar sidebar"
    );

    if ($response->status) {
      $this->usuarioLogado->usu_sidebar = $sidebarState;
      $this->session->set('USUARIO', $this->usuarioLogado);
    }

    $response->json();
    exit;
  }

  public function setPaletaCores(): void
  {
    $this->validarUsuarioLogado(true);

    $paletaCores = trim((string) ($_POST['usu_paletacores'] ?? ''));

    if ($paletaCores === '') {
      $paletaCores = null;
    }

    if (!is_null($paletaCores)) {
      $decode = json_decode($paletaCores, true);

      if (!is_array($decode)) {
        (new SimpleResponse("Erro", "Paleta de cores inválida"))
          ->erro()
          ->json();

        exit;
      }

      $camposPermitidos = [
        'softBg',
        'softText',
        'softHoverBg',
        'softHoverText',

        'primaryBg',
        'primaryText',
        'primaryHoverBg',
        'primaryHoverText',

        'strongBg',
        'strongText',
        'strongHoverBg',
        'strongHoverText',

        'sidebarText',

        'sidebarActiveBg',
        'sidebarActiveText',
        'sidebarHoverBg',
        'sidebarHoverText',

        'sidebarSubmenuActiveBg',
        'sidebarSubmenuActiveText',
        'sidebarSubmenuHoverBg',
        'sidebarSubmenuHoverText'
      ];

      $paletaTratada = [];

      foreach ($camposPermitidos as $campo) {
        $cor = trim((string) ($decode[$campo] ?? ''));

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $cor)) {
          (new SimpleResponse("Erro", "Cor inválida no campo: {$campo}"))
            ->erro()
            ->json();

          exit;
        }

        $paletaTratada[$campo] = strtolower($cor);
      }

      $paletaCores = json_encode($paletaTratada, JSON_UNESCAPED_UNICODE);
    }

    $usuarioModel = new UsuarioModel();

    $response = $usuarioModel->update(
      [
        'usu_paletacores' => $paletaCores
      ],
      [
        'usu_id' => $this->usuarioLogado->usu_id
      ],
      "Paleta de cores atualizada com sucesso||Erro ao atualizar paleta de cores"
    );

    if ($response->status) {
      $this->usuarioLogado->usu_paletacores = $paletaCores;
      $this->session->set('USUARIO', $this->usuarioLogado);
    }

    $response->json();
    exit;
  }

  public function setTema(): void
  {
    $this->validarUsuarioLogado(true);

    $novoTema = trim((string) ($_POST['usu_tema'] ?? 'light'));

    if (!in_array($novoTema, ['light', 'dark'])) {
      $novoTema = 'light';
    }

    $usuarioModel = new UsuarioModel();

    $response = $usuarioModel->update(
      [
        'usu_tema' => $novoTema
      ],
      [
        'usu_id' => $this->usuarioLogado->usu_id
      ],
      "Tema atualizado com sucesso||Erro ao atualizar tema"
    );

    if ($response->status) {
      $this->usuarioLogado->usu_tema = $novoTema;
      $this->session->set('USUARIO', $this->usuarioLogado);
    }

    $response->json();
    exit;
  }

  public function logout()
  {
    $session = new SimpleSession();
    $session->remove('USUARIO');
    $session->clear();
    
    if ( $_GET['location']!='' ) {
      header("Location: " . Helpers::url($_GET['location']));
      exit;
    }else{
      $response = new SimpleResponse('Sucesso', 'Você foi desconectado com sucesso.');
      $response->sucesso()->json();
    }
  }

  public function bloqueado(): void
  {
    $this->validarUsuarioLogado();

    (new UsuarioModel())->bloquearSessaoUsuario();

    $this->setMetaTags([
      'title' => 'Sessão bloqueada',
      'description' => 'Sua sessão está bloqueada. Informe sua senha para desbloquear.',
      'image' => Helpers::url('storage/global/simpleweb.png')
    ]);

    $this->render('sistema/usuario/bloqueado', [
      'usuario' => $this->usuarioLogado
    ]);
  }

  public function desbloquearUsuario(): void
  {
    $this->validarUsuarioLogado(true);

    $senha = trim((string) ($_POST['usu_senha'] ?? ''));

    if ($senha === '') {
      (new SimpleResponse("Atenção", "Informe sua senha para desbloquear a sessão"))
        ->warning()
        ->json();
      exit;
    }

    $response = (new UsuarioModel())->desbloquearSessaoUsuario($senha);

    if ($response->status) {
      $response->setArrayParams([
        'location' => Helpers::url('dashboard')
      ]);
    }

    $response->json();
    exit;
  }

  public function list(): void
  {
    $this->validarPermissoes(['usuario.visualizar']);

    $usuarioModel = new UsuarioModel();
    $getTotalUsuarios = $usuarioModel->contarUsuariosContexto();

    $this->setBreadcrumb([
      [
        'title' => 'Usuários',
        'label' => 'Usuários',
        'url' => Helpers::url('usuario/list')
      ]
    ]);

    $this->render('sistema/usuario/list', [
      'getInfo' => (object) [
        'status' => true,
        'data' => [],
        'total' => $getTotalUsuarios->status
          ? (int) ($getTotalUsuarios->data[0]->total ?? 0)
          : 0
      ],
      'titulo' => 'Usuários',
      'descricao' => 'Gerencie os usuários vinculados ao contexto atual.',
      'urlCadastrar' => Helpers::url('usuario/form'),
      'urlPesquisar' => Helpers::url('usuario/pesquisarUsuario'),
      'labelPesquisaSimples' => 'Pesquisar por nome, CPF/CNPJ, e-mail ou usuário',
      'labelTotal' => 'usuário(s)',
      'nomeArquivo' => 'usuarios',
      'prefixo' => 'usu'
    ]);
  }

  public function pesquisarUsuario(): void
  {
    $this->validarPermissoes(['usuario.visualizar']);

    $response = (new UsuarioModel())->pesquisarUsuario($_POST);

    $response->json();
    exit;
  }

  public function form($url = null): void
  {
    $id = 0;

    if (is_array($url)) {
      $id = (int) ($url['id'] ?? 0);
    } elseif (!is_null($url)) {
      $id = (int) $url;
    }

    if ($id > 0) {
      $this->validarPermissoes([
        'usuario.visualizar',
        'usuario.atualizar'
      ]);
    } else {
      $this->validarPermissoes([
        'usuario.visualizar',
        'usuario.criar'
      ]);

      $limiteUsuario = (new LimitePlanoService($this->contexto))->verificar('usuario');

      if ($limiteUsuario->status && !$limiteUsuario->permitido) {
        $limiteUsuario->url_voltar = Helpers::url('usuario/list');
        $this->render('template/sistema/limite', [
          'limite' => $limiteUsuario
        ]);
        return;
      }
    }

    $usuarioModel = new UsuarioModel();

    if ($id > 0) {
      $getUsuario = $usuarioModel->getUsuarioById($id);

      if (!$getUsuario->status || $getUsuario->total != 1) {
        $this->render('template/sistema/erro', [
          'erro' => (object) [
            'status' => false,
            'title' => 'Usuário não encontrado',
            'text' => 'O usuário informado não foi localizado ou não pertence ao contexto atual.'
          ]
        ]);
        return;
      }

      $usuario = $getUsuario->data[0];
    } else {
      $usuario = null;
    }

    $this->setBreadcrumb([
      [
        'title' => 'Pesquisar usuário',
        'label' => 'Usuários',
        'url' => Helpers::url('usuario/list'),
        'active' => false
      ],
      [
        'title' => !is_null($usuario) && $usuario->usu_id > 0 ? 'Editando usuário' : 'Novo usuário',
        'label' => !is_null($usuario) && $usuario->usu_id > 0 ? Helpers::gerarAssinatura($usuario->usu_razaosocial) : 'Novo',
        'url' => '#'
      ]
    ]);

    $dadosComplementares = (object) [
      'status' => false,
      'alert' => []
    ];

    $arquivoConfig = null;
    if (!is_null($usuario)) {
      $arquivoConfig = (object) [
        'status' => true,
        'title' => 'Arquivos',
        'text' => '',
        'nomeModulo' => 'usuário',
        'origem' => 'usuario',
        'idorigem' => $usuario->usu_id,
        'idorganizacao' => $usuario->usu_idorganizacao,
        'idempresa' => $usuario->usu_idempresa,
        'limite' => $this->contexto->plano->plc_parametro->usuario->arquivo->limite,
        'titulo' => 'Arquivos',
        'subtitulo' => 'Gerencie os arquivos vinculados a este usuário.',
        'permitir_criar' => isset($this->usuarioLogado->permissoes->{'arquivo.criar'}),
        'permitir_editar' => isset($this->usuarioLogado->permissoes->{'arquivo.atualizar'}),
        'permitir_excluir' => isset($this->usuarioLogado->permissoes->{'arquivo.excluir'}),
        'permitir_principal' => true,
        'obrigatorio' => false,
        'recarregar_ao_salvar' => true,
        'accept' => '.pdf,.txt,.doc,.docx,.xls,.xlsx,.json,.xml,.jpg,.jpeg,.png,.webp',
        'multiple' => true,
        'data' => [],
        'total' => 0,
        'atingiu_limite' => false,
        'pode_criar' => false,
        'componente_id' => ''
      ];

      if (
        empty($arquivoConfig->origem) ||
        empty($arquivoConfig->idorigem) ||
        empty($arquivoConfig->idorganizacao) ||
        $arquivoConfig->idempresa < 0
      ) {
        $arquivoConfig->status = false;
        $arquivoConfig->title = 'Erro';
        $arquivoConfig->text = 'Configuração de arquivo inválida.';
      }

      if ($arquivoConfig->status) {
        $getArquivo = (new ArquivoModel())->getArquivosByConfig($arquivoConfig);

        if (!$getArquivo->status) {
          $arquivoConfig->status = false;
          $arquivoConfig->title = 'Erro';
          $arquivoConfig->text = $getArquivo->text ?? 'Erro ao buscar arquivos.';
        } else {
          $arquivoConfig->data = $getArquivo->data;
          $arquivoConfig->total = $getArquivo->total;
        }
      }

      $arquivoConfig->atingiu_limite = $arquivoConfig->limite > 0 && $arquivoConfig->total >= $arquivoConfig->limite;
      $arquivoConfig->pode_criar = $arquivoConfig->permitir_criar && !$arquivoConfig->atingiu_limite;
      $arquivoConfig->componente_id = 'arquivo_' . md5(
        $arquivoConfig->origem .
        '_' . $arquivoConfig->idorigem .
        '_' . $arquivoConfig->idorganizacao .
        '_' . $arquivoConfig->idempresa
      );
    }

    $contatoConfig = null;
    if (!is_null($usuario)) {
      $contatoConfig = (object) [
        'status' => true,
        'title' => 'Contatos',
        'text' => '',
        'nomeModulo' => 'usuário',
        'origem' => 'usuario',
        'idorigem' => $usuario->usu_id,
        'idorganizacao' => $this->contexto->organizacao->org_id,
        'idempresa' => $this->contexto->empresa->emp_id,
        'limite' => $this->contexto->plano->plc_parametro->usuario->contato->limite,
        'titulo' => 'Contatos',
        'subtitulo' => 'Gerencie os contatos complementares vinculados a este usuário.',
        'permitir_criar' => isset($this->usuarioLogado->permissoes->{'contato.criar'}),
        'permitir_editar' => isset($this->usuarioLogado->permissoes->{'contato.atualizar'}),
        'permitir_excluir' => isset($this->usuarioLogado->permissoes->{'contato.excluir'}),
        'obrigatorio' => false,
        'recarregar_ao_salvar' => true,
        'data' => [],
        'total' => 0,
        'atingiu_limite' => false,
        'pode_criar' => false,
        'componente_id' => ''
      ];

      if (
        empty($contatoConfig->origem) ||
        empty($contatoConfig->idorigem) ||
        empty($contatoConfig->idorganizacao) ||
        $contatoConfig->idempresa < 0
      ) {
        $contatoConfig->status = false;
        $contatoConfig->title = 'Erro';
        $contatoConfig->text = 'Configuração de contato inválida.';
      }

      if ($contatoConfig->status) {
        $getContato = (new ContatoModel())->getContatosByConfig($contatoConfig);

        if (!$getContato->status) {
          $contatoConfig->status = false;
          $contatoConfig->title = 'Erro';
          $contatoConfig->text = $getContato->text ?? 'Erro ao buscar contatos.';
        } else {
          $contatoConfig->data = $getContato->data;
          $contatoConfig->total = $getContato->total;
        }
      }

      $contatoConfig->atingiu_limite = $contatoConfig->limite > 0 && $contatoConfig->total >= $contatoConfig->limite;
      $contatoConfig->pode_criar = $contatoConfig->permitir_criar && !$contatoConfig->atingiu_limite;
      $contatoConfig->componente_id = 'contato_' . md5(
        $contatoConfig->origem .
        '_' . $contatoConfig->idorigem .
        '_' . $contatoConfig->idorganizacao .
        '_' . $contatoConfig->idempresa
      );
    }

    $enderecoConfig = null;
    if (!is_null($usuario)) {
      $enderecoConfig = (object) [
        'status' => true,
        'title' => 'Endereços',
        'text' => '',
        'nomeModulo' => 'usuário',
        'origem' => 'usuario',
        'idorigem' => $usuario->usu_id,
        'idorganizacao' => $this->contexto->organizacao->org_id,
        'idempresa' => $this->contexto->empresa->emp_id,
        'limite' => $this->contexto->plano->plc_parametro->usuario->endereco->limite,
        'titulo' => 'Endereços',
        'subtitulo' => 'Gerencie os endereços vinculados a este usuário.',
        'permitir_criar' => isset($this->usuarioLogado->permissoes->{'endereco.criar'}),
        'permitir_editar' => isset($this->usuarioLogado->permissoes->{'endereco.atualizar'}),
        'permitir_excluir' => isset($this->usuarioLogado->permissoes->{'endereco.excluir'}),
        'permitir_principal' => true,
        'obrigatorio' => false,
        'recarregar_ao_salvar' => true,
        'data' => [],
        'total' => 0,
        'atingiu_limite' => false,
        'pode_criar' => false,
        'componente_id' => ''
      ];

      if (
        empty($enderecoConfig->origem) ||
        empty($enderecoConfig->idorigem) ||
        empty($enderecoConfig->idorganizacao) ||
        $enderecoConfig->idempresa < 0
      ) {
        $enderecoConfig->status = false;
        $enderecoConfig->title = 'Erro';
        $enderecoConfig->text = 'Configuração de endereço inválida.';
      }

      if ($enderecoConfig->status) {
        $getEndereco = (new EnderecoModel())->getEnderecosByConfig($enderecoConfig);

        if (!$getEndereco->status) {
          $enderecoConfig->status = false;
          $enderecoConfig->title = 'Erro';
          $enderecoConfig->text = $getEndereco->text ?? 'Erro ao buscar endereços.';
        } else {
          $enderecoConfig->data = $getEndereco->data;
          $enderecoConfig->total = $getEndereco->total;
        }
      }

      $enderecoConfig->atingiu_limite = $enderecoConfig->limite > 0 && $enderecoConfig->total >= $enderecoConfig->limite;
      $enderecoConfig->pode_criar = $enderecoConfig->permitir_criar && !$enderecoConfig->atingiu_limite;
      $enderecoConfig->componente_id = 'endereco_' . md5(
        $enderecoConfig->origem .
        '_' . $enderecoConfig->idorigem .
        '_' . $enderecoConfig->idorganizacao .
        '_' . $enderecoConfig->idempresa
      );
    }

    $this->render('sistema/usuario/form', [
      'usuario' => $usuario,
      'getNivelAcesso' => (new NivelAcessoModel())->getNiveisAtivos(),
      'getSexo' => (new CadastroBasicoModel('sexo'))->getCadastroBasicoAtivo('usuario'),
      'getEstadoCivil' => (new CadastroBasicoModel('estadocivil'))->getCadastroBasicoAtivo('usuario'),
      'getNaturezaJuridica' => (new CadastroBasicoModel('naturezajuridica'))->getCadastroBasicoAtivo('usuario'),
      'getNaturalidade' => (new CadastroBasicoModel('naturalidade'))->getCadastroBasicoAtivo('usuario'),
      'dadosComplementares' => $dadosComplementares,
      'arquivoConfig' => $arquivoConfig,
      'contatoConfig' => $contatoConfig,
      'enderecoConfig' => $enderecoConfig
    ]);
  }

  public function salvarUsuario(): void
  {
    if ((int) ($_POST['usu_id'] ?? 0) > 0) {
      $this->validarPermissoes(['usuario.atualizar']);
    } else {
      $this->validarPermissoes(['usuario.criar']);
    }

    echo json_encode( ( new UsuarioModel() )->salvarUsuario($_POST));
    exit;
  }

  public function pesquisarRapida(): void
  {
    $this->validarPermissoes(['usuario.visualizar']);

    $conteudo = $_POST['conteudo'] ?? '';

    $usuarioModel = new UsuarioModel();
    $response = $usuarioModel->pesquisarRapida($conteudo);

    $response->json();
  }

  // ANTIGO:
  public function novoUsuario()
  {
    $postData = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

    if (!$postData) {
      $response = new SimpleResponse('Erro', 'Nenhum dado foi recebido');
      $response->erro()->json();
    }

    $usuarioModel = new UsuarioModel();
    $enderecoModel = new EnderecoModel();

    if ( isset($postData['usu_carteirinha']) && !empty($postData['usu_carteirinha']) ) {
      $usuarioModel->validarDuplicidade(
        'usu_carteirinha', 
        $postData['usu_carteirinha'], 
        'A carteirinha informada já está cadastrada'
      );
    }

    $usuarioModel->validarDuplicidade(
      'usu_email', 
      $postData['usu_email'], 
      'O email informado já está cadastrado'
    );

    $postData['usu_senha'] = password_hash($postData['usu_senha'], PASSWORD_DEFAULT);
    $postData['usu_username'] = Helpers::removeCaracteresEspeciais($postData['usu_username']);
    $postData['usu_ministerio'] = json_encode($postData['usu_ministerio'], JSON_UNESCAPED_UNICODE);

    if ( empty($postData['usu_carteirinha']) ) {
      $postData['usu_carteirinha'] = null;
    }

    $insertResponse = $usuarioModel->insertUsuario($postData);

    if ( !$insertResponse->status ) {
      $response = new SimpleResponse('Erro', $insertResponse->text, [
        'debug' => $insertResponse->errorInfo
      ]);
      $response->erro()->json();
    }

    $enderecoData = [
      'end_cep' => Helpers::removeCaracteresEspeciais($postData['end_cep']),
      'end_numero' => $postData['end_numero'],
      'end_logradouro' => $postData['end_logradouro'],
      'end_cidade' => $postData['end_cidade'],
      'end_bairro' => $postData['end_bairro'],
      'end_uf' => $postData['end_uf'],
      'end_estado' => $postData['end_estado'],
      'end_idorigem' => $insertResponse->lastModifyId,
      'end_origem' => 'usuario'
    ];

    $enderecoResponse = $enderecoModel->insertEndereco($enderecoData);

    if (!$enderecoResponse->status) {
      $response = new SimpleResponse('Erro', $enderecoResponse->text, [
        'debug' => $enderecoResponse->errorInfo
      ]);
      $response->erro()->json();
    }

    $response = new SimpleResponse('Tudo Certo', 'Cadastro realizado com sucesso');
    $response->sucesso()->json();
  }
}
