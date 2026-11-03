<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Model\EnderecoModel;
use Source\Model\PlanoContratadoModel;
use Source\Model\PlanoModel;
use Source\Model\PalavraChaveModel;
use Source\Model\ProjetoModel;
use Source\Model\EmailTemplateModel;
use Source\Model\EmailEnviadoModel;
use Source\Model\ConhecimentoModel;

class ProjetoController extends SimpleController
{
  public function __construct()
  {
    parent::__construct();

    $this->validarUsuarioLogado();
    $this->setFramework();

    $this->setAssetsCss([
      'phsa.css',
      'sistema.css',
      'global.css'
    ]);

    $this->setAssetsJs([
      'sistema.js',
      'simplemodal.js',
      'global.js'
    ]);
  }

  public function projeto($url = null): void
  {
    $this->validarPermissoes(['projeto.visualizar', 'projeto.atualizar']);

    if (is_array($url)) {
      $this->setTabLink($url['tabLink'] ?? null);
    }

    $getProjeto = (new ProjetoModel())->getProjeto();

    if (!$getProjeto->status || $getProjeto->total !== 1) {
      $this->render('template/sistema/erro', [
        'erro' => (object) [
          'status' => false,
          'title' => 'Projeto não encontrado',
          'text' => 'O projeto principal (prj_id = 1) não foi localizado.'
        ]
      ]);
      return;
    }

    $projeto = $getProjeto->data[0];
    $podeVisualizarPalavrasChaves = Helpers::pode('projeto.palavrachave.visualizar');
    $getPalavrasChaves = $podeVisualizarPalavrasChaves
      ? (new PalavraChaveModel())->getPalavrasChaves()
      : null;

    $this->setBreadcrumb([
      [
        'title' => 'Projeto',
        'label' => Helpers::gerarAssinatura($projeto->prj_titulo ?? $projeto->prj_nome ?? 'LevHub'),
        'url' => Helpers::url('projeto')
      ]
    ]);

    $this->render('sistema/projeto/form', [
      'projeto' => $projeto,
      'enderecoProjeto' => $this->buscarEnderecoProjeto($projeto),
      'organizacoes' => (new ProjetoModel())->getOrganizacoesResumo(),
      'totalOrganizacoes' => $this->contarOrganizacoes(),
      'palavrasChaves' => $getPalavrasChaves && $getPalavrasChaves->status ? ($getPalavrasChaves->data ?? []) : [],
      'catalogoPalavrasChaves' => PalavraChaveModel::getCatalogo(),
      'podeVisualizarPalavrasChaves' => $podeVisualizarPalavrasChaves,
      'podeAtualizarPalavrasChaves' => Helpers::pode('projeto.palavrachave.atualizar')
    ]);
  }

  public function salvarProjeto(): void
  {
    $this->validarPermissoes(['projeto.atualizar']);

    $response = (new ProjetoModel())->salvarProjeto($_POST);

    if ($response->status && isset($_POST['end_cep'])) {
      $endereco = $_POST;
      $endereco['end_origem'] = 'projeto';
      $endereco['end_idorigem'] = (int) ($_POST['prj_id'] ?? 1);
      $endereco['end_idorganizacao'] = 0;
      $endereco['end_idempresa'] = 0;

      if ((int) ($endereco['end_id'] ?? 0) <= 0) {
        $enderecoAtual = $this->buscarEnderecoProjeto((object) [
          'prj_id' => $endereco['end_idorigem']
        ]);

        if ($enderecoAtual) {
          $endereco['end_id'] = (int) $enderecoAtual->end_id;
        }
      }

      $salvarEndereco = (new EnderecoModel())->salvarEndereco($endereco);

      if (!$salvarEndereco->status) {
        $response = $salvarEndereco;
      }
    }

    echo json_encode($response);
    exit;
  }

  public function salvarDocumento(): void
  {
    $this->validarPermissoes(['projeto.atualizar']);

    echo json_encode((new ProjetoModel())->salvarDocumento($_POST));
    exit;
  }

  public function salvarImagens(): void
  {
    $this->validarPermissoes(['projeto.atualizar']);

    echo json_encode((new ProjetoModel())->salvarImagens($_POST));
    exit;
  }

  public function salvarGateway(): void
  {
    $this->validarPermissoes(['projeto.atualizar']);

    echo json_encode((new ProjetoModel())->salvarGateway($_POST));
    exit;
  }

  public function pesquisarOrganizacoes(): void
  {
    $this->validarPermissoes(['projeto.visualizar']);

    echo json_encode((new ProjetoModel())->getOrganizacoesResumo($_POST['pesquisa'] ?? ''));
    exit;
  }

  public function pesquisarPalavrasChaves(): void
  {
    $this->validarPermissoes(['projeto.palavrachave.visualizar']);

    echo json_encode((new PalavraChaveModel())->getPalavrasChaves($_POST['pesquisa'] ?? ''));
    exit;
  }

  public function salvarPalavraChave(): void
  {
    $this->validarPermissoes(['projeto.palavrachave.atualizar']);

    echo json_encode((new PalavraChaveModel())->salvarPalavraChave($_POST));
    exit;
  }

  public function excluirPalavraChave(): void
  {
    $this->validarPermissoes(['projeto.palavrachave.atualizar']);

    echo json_encode((new PalavraChaveModel())->excluirPalavraChave((int) ($_POST['pch_id'] ?? 0)));
    exit;
  }

  public function email(): void
  {
    $this->validarPermissoes(['projeto.email.visualizar']);

    $templates = (new EmailTemplateModel())->getEmails();
    $templatesData = $templates->status ? ($templates->data ?? []) : [];
    $indicadores = (new EmailEnviadoModel())->getIndicadoresProjeto();
    $indicadoresData = ($indicadores->status && !empty($indicadores->data[0]))
      ? $indicadores->data[0]
      : (object) ['total' => 0, 'pendentes' => 0, 'agendados' => 0, 'enviados' => 0, 'falhos' => 0, 'cancelados' => 0];
    $ultimos = (new EmailEnviadoModel())->getUltimosProjeto();

    $this->setBreadcrumb([
      ['title' => 'Projeto', 'label' => 'Projeto', 'url' => Helpers::url('projeto'), 'active' => false],
      ['title' => 'E-mails', 'label' => 'E-mails']
    ]);

    $this->render('sistema/projeto/emailHub', [
      'templates' => $templatesData,
      'indicadores' => $indicadoresData,
      'ultimos' => ($ultimos->status ? ($ultimos->data ?? []) : []),
      'podeAtualizar' => Helpers::pode('projeto.email.atualizar')
    ]);
  }

  public function emailConfiguracao(): void
  {
    $this->validarPermissoes(['projeto.email.visualizar']);

    $getProjeto = (new ProjetoModel())->getProjeto();

    if (!$getProjeto->status || $getProjeto->total !== 1) {
      $this->render('template/sistema/erro', [
        'erro' => (object) [
          'status' => false,
          'title' => 'Projeto nÃ£o encontrado',
          'text' => 'O projeto principal (prj_id = 1) nÃ£o foi localizado.'
        ]
      ]);
      return;
    }

    $this->setBreadcrumb([
      ['title' => 'Projeto', 'label' => 'Projeto', 'url' => Helpers::url('projeto'), 'active' => false],
      ['title' => 'E-mails', 'label' => 'E-mails', 'url' => Helpers::url('projeto/email'), 'active' => false],
      ['title' => 'Configuração', 'label' => 'Configuração']
    ]);

    $this->render('sistema/projeto/emailConfiguracao', [
      'projeto' => $getProjeto->data[0],
      'podeAtualizar' => Helpers::pode('projeto.email.atualizar')
    ]);
  }

  public function emailTemplate($url = null): void
  {
    $this->validarPermissoes(['projeto.email.visualizar']);

    $idParametro = is_array($url) ? ($url['id'] ?? 0) : $url;
    $id = strtolower((string) $idParametro) === 'novo' ? 0 : (int) $idParametro;
    $model = new EmailTemplateModel();
    $palavras = (new PalavraChaveModel())->getPalavrasChavesAtivas();
    $palavrasData = $palavras->status ? ($palavras->data ?? []) : [];
    $dadosComuns = [
      'palavrasChavesAtivas' => $palavrasData,
      'catalogoPalavrasChaves' => PalavraChaveModel::getCatalogo(),
      'podeAtualizar' => Helpers::pode('projeto.email.atualizar')
    ];

    if ($id > 0) {
      $getEmail = $model->getEmailById($id);
      if (!$getEmail->status || $getEmail->total !== 1) {
        $this->render('template/sistema/erro', [
          'erro' => (object) ['status' => false, 'title' => 'Template não encontrado', 'text' => 'O template de e-mail informado não foi localizado.']
        ]);
        return;
      }

      $email = $getEmail->data[0];
      $this->setBreadcrumb([
        ['title' => 'Projeto', 'label' => 'Projeto', 'url' => Helpers::url('projeto'), 'active' => false],
        ['title' => 'E-mails', 'label' => 'E-mails', 'url' => Helpers::url('projeto/email'), 'active' => false],
        ['title' => 'Templates', 'label' => 'Templates', 'url' => Helpers::url('projeto/email/template'), 'active' => false],
        ['title' => 'Template', 'label' => $email->emt_nome ?? 'Template']
      ]);
      $this->render('sistema/projeto/email', array_merge($dadosComuns, ['email' => $email, 'emails' => null]));
      return;
    }

    if (strtolower((string) $idParametro) === 'novo') {
      $this->setBreadcrumb([
        ['title' => 'Projeto', 'label' => 'Projeto', 'url' => Helpers::url('projeto'), 'active' => false],
        ['title' => 'E-mails', 'label' => 'E-mails', 'url' => Helpers::url('projeto/email'), 'active' => false],
        ['title' => 'Templates', 'label' => 'Templates', 'url' => Helpers::url('projeto/email/template'), 'active' => false],
        ['title' => 'Novo template', 'label' => 'Novo template']
      ]);
      $this->render('sistema/projeto/email', array_merge($dadosComuns, [
        'email' => (object) ['emt_id' => 0, 'emt_status' => 'Ativo', 'emt_statusclass' => 'success', 'emt_port' => 587, 'emt_palavrachave' => null],
        'emails' => null
      ]));
      return;
    }

    $this->setBreadcrumb([
      ['title' => 'Projeto', 'label' => 'Projeto', 'url' => Helpers::url('projeto'), 'active' => false],
      ['title' => 'E-mails', 'label' => 'E-mails', 'url' => Helpers::url('projeto/email'), 'active' => false],
      ['title' => 'Templates', 'label' => 'Templates']
    ]);
    $this->render('sistema/projeto/email', array_merge($dadosComuns, [
      'email' => null,
      'emails' => $model->getEmails()
    ]));
  }

  public function emailCampanha(): void
  {
    $this->validarPermissoes(['projeto.email.visualizar']);

    $this->setBreadcrumb([
      ['title' => 'Projeto', 'label' => 'Projeto', 'url' => Helpers::url('projeto'), 'active' => false],
      ['title' => 'E-mails', 'label' => 'E-mails', 'url' => Helpers::url('projeto/email'), 'active' => false],
      ['title' => 'Campanhas', 'label' => 'Campanhas']
    ]);

    $this->render('sistema/projeto/emailModulo', [
      'modulo' => (object) [
        'titulo' => 'Campanhas',
        'icone' => 'fa-bullhorn',
        'descricao' => 'Crie e organize comunicações planejadas para os públicos do projeto.',
        'mensagem' => 'A gestão de campanhas será disponibilizada nesta área quando o disparo planejado de e-mails estiver pronto.',
        'acao' => 'Voltar ao hub de e-mails'
      ]
    ]);
  }

  public function emailEnviado($url = null): void
  {
    $this->validarPermissoes(['projeto.email.visualizar']);

    $id = is_array($url) ? (int) ($url['id'] ?? 0) : (int) $url;
    $model = new EmailEnviadoModel();

    if ($id > 0) {
      $getEnvio = $model->getEnvioProjetoById($id);

      if (!$getEnvio->status || $getEnvio->total !== 1) {
        $this->render('template/sistema/erro', [
          'erro' => (object) [
            'status' => false,
            'title' => 'Envio nÃ£o encontrado',
            'text' => 'O envio de e-mail informado nÃ£o foi localizado.'
          ]
        ]);
        return;
      }

      $this->setBreadcrumb([
        ['title' => 'Projeto', 'label' => 'Projeto', 'url' => Helpers::url('projeto'), 'active' => false],
        ['title' => 'E-mails', 'label' => 'E-mails', 'url' => Helpers::url('projeto/email'), 'active' => false],
        ['title' => 'Envios', 'label' => 'Envios', 'url' => Helpers::url('projeto/email/enviado'), 'active' => false],
        ['title' => 'Envio', 'label' => 'Envio #' . $id]
      ]);

      $this->render('sistema/projeto/emailEnviado', [
        'envio' => $getEnvio->data[0],
        'envios' => null
      ]);
      return;
    }

    $this->setBreadcrumb([
      ['title' => 'Projeto', 'label' => 'Projeto', 'url' => Helpers::url('projeto'), 'active' => false],
      ['title' => 'E-mails', 'label' => 'E-mails', 'url' => Helpers::url('projeto/email'), 'active' => false],
      ['title' => 'Envios', 'label' => $id > 0 ? 'Envio #' . $id : 'Envios']
    ]);

    $this->render('sistema/projeto/emailEnviado', [
      'envio' => null,
      'envios' => $model->getEnviosProjeto()
    ]);
    return;
  }

  public function pesquisarEmailsEnviados(): void
  {
    $this->validarPermissoes(['projeto.email.visualizar']);
    echo json_encode((new EmailEnviadoModel())->getEnviosProjeto($_POST['pesquisa'] ?? ''));
    exit;
  }

  public function pesquisarEmails(): void
  {
    $this->validarPermissoes(['projeto.email.visualizar']);
    echo json_encode((new EmailTemplateModel())->getEmails($_POST['pesquisa'] ?? ''));
    exit;
  }

  public function salvarEmail(): void
  {
    $this->validarPermissoes(['projeto.email.atualizar']);
    echo json_encode((new EmailTemplateModel())->salvarEmail($_POST));
    exit;
  }

  public function testarConexaoEmail(): void
  {
    $this->validarPermissoes(['projeto.email.visualizar']);
    echo json_encode((new EmailTemplateModel())->testarConexao((int) ($_POST['emt_id'] ?? 0)));
    exit;
  }

  public function salvarEmailConfiguracao(): void
  {
    $this->validarPermissoes(['projeto.email.atualizar']);
    echo json_encode((new ProjetoModel())->salvarEmailConfiguracao($_POST));
    exit;
  }

  public function plano($url = null): void
  {
    $this->validarPermissoes(['projeto.plano.visualizar', 'projeto.plano.atualizar']);

    $id = is_array($url) ? (int) ($url['id'] ?? 0) : (int) $url;
    $model = new PlanoModel();

    if ($id > 0) {
      $getPlano = $model->getPlanoById($id);

      if (!$getPlano->status || $getPlano->total !== 1) {
        $this->render('template/sistema/erro', [
          'erro' => (object) [
            'status' => false,
            'title' => 'Plano não encontrado',
            'text' => 'O plano informado não foi localizado.'
          ]
        ]);
        return;
      }

      $plano = $getPlano->data[0];

      $this->setBreadcrumb([
        [
          'title' => 'Projeto',
          'label' => 'Projeto',
          'url' => Helpers::url('projeto'),
          'active' => false
        ],
        [
          'title' => 'Planos',
          'label' => 'Planos',
          'url' => Helpers::url('projeto/plano'),
          'active' => false
        ],
        [
          'title' => 'Plano',
          'label' => $plano->pla_nome ?? 'Plano'
        ]
      ]);

      $this->render('sistema/projeto/plano', [
        'plano' => $plano,
        'planos' => null,
        'podeAtualizar' => Helpers::pode('projeto.plano.atualizar')
      ]);
      return;
    }

    $this->setBreadcrumb([
      [
        'title' => 'Projeto',
        'label' => 'Projeto',
        'url' => Helpers::url('projeto'),
        'active' => false
      ],
      [
        'title' => 'Planos',
        'label' => 'Planos'
      ]
    ]);

    $this->render('sistema/projeto/plano', [
      'plano' => null,
      'planos' => $model->getPlanos(),
      'podeAtualizar' => Helpers::pode('projeto.plano.atualizar')
    ]);
  }

  public function salvarPlano(): void
  {
    $this->validarPermissoes(['projeto.plano.atualizar']);

    echo json_encode((new PlanoModel())->salvarPlano($_POST));
    exit;
  }

  public function contratacao($url = null): void
  {
    $this->validarPermissoes(['projeto.contratacao.visualizar', 'projeto.contratacao.atualizar']);

    $id = is_array($url) ? (int) ($url['id'] ?? 0) : (int) $url;
    $model = new PlanoContratadoModel();

    if ($id > 0) {
      $getContratacao = $model->getContratacaoById($id);

      if (!$getContratacao->status || $getContratacao->total !== 1) {
        $this->render('template/sistema/erro', [
          'erro' => (object) [
            'status' => false,
            'title' => 'Contratação não encontrada',
            'text' => 'A contratação informada não foi localizada.'
          ]
        ]);
        return;
      }

      $contratacao = $getContratacao->data[0];

      $this->setBreadcrumb([
        [
          'title' => 'Projeto',
          'label' => 'Projeto',
          'url' => Helpers::url('projeto'),
          'active' => false
        ],
        [
          'title' => 'Contratações',
          'label' => 'Contratações',
          'url' => Helpers::url('projeto/contratacao'),
          'active' => false
        ],
        [
          'title' => 'Contratação',
          'label' => 'PLC #' . $contratacao->plc_id
        ]
      ]);

      $this->render('sistema/projeto/contratacao', [
        'contratacao' => $contratacao,
        'contratacoes' => null
      ]);
      return;
    }

    $this->setBreadcrumb([
      [
        'title' => 'Projeto',
        'label' => 'Projeto',
        'url' => Helpers::url('projeto'),
        'active' => false
      ],
      [
        'title' => 'Contratações',
        'label' => 'Contratações'
      ]
    ]);

    $this->render('sistema/projeto/contratacao', [
      'contratacao' => null,
      'contratacoes' => $model->getContratacoes()
    ]);
  }

  public function pesquisarContratacoes(): void
  {
    $this->validarPermissoes(['projeto.contratacao.visualizar', 'projeto.contratacao.atualizar']);

    echo json_encode((new PlanoContratadoModel())->getContratacoes($_POST['pesquisa'] ?? ''));
    exit;
  }


  public function conhecimentoList(): void
  {
    $this->validarPermissoes(['projeto.conhecimento.visualizar', 'projeto.conhecimento.criar', 'projeto.conhecimento.atualizar']);

    $model = new ConhecimentoModel();
    $podeVisualizarRegistros = Helpers::pode(['projeto.conhecimento.visualizar', 'projeto.conhecimento.atualizar']);
    $totalRegistros = $podeVisualizarRegistros ? $model->contarConhecimentos() : null;
    $alertaFlash = $this->session->get('FLASH_PROJETO_CONHECIMENTO_ERRO');
    $this->session->remove('FLASH_PROJETO_CONHECIMENTO_ERRO');

    $this->setBreadcrumb([
      [
        'title' => 'Projeto',
        'label' => 'Projeto',
        'url' => Helpers::url('projeto'),
        'active' => false
      ],
      [
        'title' => 'Conhecimento',
        'label' => 'Conhecimento'
      ]
    ]);

    $this->render('sistema/projeto/conhecimentoList', [
      'getInfo' => (object) [
        'status' => true,
        'data' => [],
        'total' => $totalRegistros && $totalRegistros->status ? (int) ($totalRegistros->data[0]->total ?? 0) : 0
      ],
      'titulo' => 'Conhecimentos',
      'descricao' => 'Gerencie os conteúdos de orientação do projeto.',
      'urlCadastrar' => Helpers::pode('projeto.conhecimento.criar') ? Helpers::url('projeto/conhecimento/form') : '',
      'urlPesquisar' => Helpers::url('projeto/conhecimento/pesquisar'),
      'ocultarCadastrar' => !Helpers::pode('projeto.conhecimento.criar'),
      'labelPesquisaSimples' => 'Pesquisar por título, descrição, categoria ou tags',
      'labelTotal' => 'conhecimento(s)',
      'nomeArquivo' => 'conhecimentos',
      'prefixo' => 'con',
      'podeAtualizar' => Helpers::pode('projeto.conhecimento.atualizar'),
      'podeVisualizarRegistros' => $podeVisualizarRegistros,
      'alertaFlash' => $alertaFlash
    ]);
  }

  public function pesquisarConhecimento(): void
  {
    $this->validarPermissoes(['projeto.conhecimento.visualizar', 'projeto.conhecimento.criar', 'projeto.conhecimento.atualizar']);

    if (!Helpers::pode(['projeto.conhecimento.visualizar', 'projeto.conhecimento.atualizar'])) {
      (new \Source\Core\SimpleResponse('Pesquisa indisponível', 'A visualização dos conhecimentos requer a permissão correspondente.'))
        ->sucesso()
        ->setArrayParams(['data' => [], 'total' => 0])
        ->json();
      exit;
    }

    (new ConhecimentoModel())->pesquisarConhecimentos($_POST)->json();
    exit;
  }

  public function conhecimentoForm($url = null): void
  {
    $idParametro = is_array($url) ? ($url['id'] ?? 0) : $url;
    $id = strtolower((string) $idParametro) === 'novo' ? 0 : (int) $idParametro;
    $this->validarPermissoes($id > 0
      ? ['projeto.conhecimento.visualizar', 'projeto.conhecimento.atualizar']
      : ['projeto.conhecimento.criar']);
    $model = new ConhecimentoModel();
    $registro = (object) [
      'con_id' => 0,
      'con_idcategoria' => 0,
      'con_status' => 'Ativo',
      'con_statusclass' => 'success',
      'con_titulo' => '',
      'con_descricao_resumida' => '',
      'con_conteudo' => '',
      'con_tags' => '[]',
      'con_ordem' => 0
    ];

    if ($id > 0) {
      $getRegistro = $model->getConhecimentoById($id);
      if (!$getRegistro->status || $getRegistro->total !== 1) {
        $this->render('template/sistema/erro', [
          'erro' => (object) [
            'status' => false,
            'title' => 'Conhecimento não encontrado',
            'text' => 'O conhecimento informado não foi localizado.'
          ]
        ]);
        return;
      }

      $registro = $getRegistro->data[0];
    }

    $urlConhecimento = Helpers::pode(['projeto.conhecimento.visualizar', 'projeto.conhecimento.atualizar'])
      ? 'projeto/conhecimento/list'
      : 'projeto/conhecimento/form';

    $this->setBreadcrumb([
      [
        'title' => 'Projeto',
        'label' => 'Projeto',
        'url' => Helpers::url('projeto'),
        'active' => false
      ],
      [
        'title' => 'Conhecimento',
        'label' => 'Conhecimento',
        'url' => Helpers::url($urlConhecimento),
        'active' => false
      ],
      [
        'title' => 'Registro',
        'label' => $id > 0 ? ($registro->con_titulo ?? 'Conhecimento') : 'Novo conhecimento'
      ]
    ]);

    $midias = $id > 0 ? $model->getMidiasByConhecimentoId($id) : null;

    $this->render('sistema/projeto/conhecimentoForm', [
      'conhecimento' => $registro,
      'categorias' => $model->getCategoriasAtivas(),
      'midias' => $midias && $midias->status ? ($midias->data ?? []) : [],
      'podeAtualizar' => $id > 0
        ? Helpers::pode('projeto.conhecimento.atualizar')
        : Helpers::pode('projeto.conhecimento.criar')
    ]);
  }

  public function conhecimentoCategoria(): void
  {
    $this->validarPermissoes(['projeto.conhecimento.visualizar', 'projeto.conhecimento.atualizar', 'projeto.conhecimento.criar']);

    $model = new ConhecimentoModel();
    $categorias = $model->getCategorias();
    $podeAcessarLista = Helpers::pode(['projeto.conhecimento.visualizar', 'projeto.conhecimento.atualizar']);

    $this->setBreadcrumb([
      [
        'title' => 'Projeto',
        'label' => 'Projeto',
        'url' => Helpers::url('projeto'),
        'active' => false
      ],
      [
        'title' => 'Conhecimento',
        'label' => 'Conhecimento',
        'url' => Helpers::url($podeAcessarLista ? 'projeto/conhecimento/list' : 'projeto/conhecimento/form'),
        'active' => false
      ],
      [
        'title' => 'Categorias',
        'label' => 'Categorias'
      ]
    ]);

    $this->render('sistema/projeto/conhecimentoCategoria', [
      'categorias' => $categorias->status ? ($categorias->data ?? []) : [],
      'podeAtualizar' => Helpers::pode('projeto.conhecimento.atualizar'),
      'podeCriar' => Helpers::pode('projeto.conhecimento.criar'),
      'podeAcessarLista' => $podeAcessarLista
    ]);
  }

  public function conhecimentoCategoriaForm($url = null): void
  {
    $idParametro = is_array($url) ? ($url['id'] ?? 0) : ($url ?? 0);
    $id = strtolower((string) $idParametro) === 'novo' ? 0 : (int) $idParametro;
    $this->validarPermissoes($id > 0
      ? ['projeto.conhecimento.visualizar', 'projeto.conhecimento.atualizar']
      : ['projeto.conhecimento.criar']);

    $model = new ConhecimentoModel();
    $categoria = (object) [
      'cct_id' => 0,
      'cct_status' => 'Ativo',
      'cct_statusclass' => 'success',
      'cct_nome' => '',
      'cct_ordem' => 0,
      'cct_dtcad' => null,
      'cct_dtalter' => null,
      'cct_log' => '[]'
    ];

    if ($id > 0) {
      $resposta = $model->getCategoriaById($id);
      if (!$resposta->status || $resposta->total !== 1) {
        $this->swal = $this->createObjErro('Categoria não encontrada.', true);
        $this->redirect('projeto/conhecimento/categoria');
        return;
      }
      $categoria = $resposta->data[0];
    }

    $podeAcessarLista = Helpers::pode(['projeto.conhecimento.visualizar', 'projeto.conhecimento.atualizar']);
    $urlLista = 'projeto/conhecimento/categoria';
    $this->setBreadcrumb([
      ['title' => 'Projeto', 'label' => 'Projeto', 'url' => Helpers::url('projeto'), 'active' => false],
      ['title' => 'Conhecimento', 'label' => 'Conhecimento', 'url' => Helpers::url($podeAcessarLista ? 'projeto/conhecimento/list' : 'projeto/conhecimento/form'), 'active' => false],
      ['title' => 'Categorias', 'label' => 'Categorias', 'url' => Helpers::url($urlLista), 'active' => false],
      ['title' => $id > 0 ? 'Editar categoria' : 'Nova categoria', 'label' => $id > 0 ? $categoria->cct_nome : 'Cadastrar']
    ]);
    $this->setMetaTags([
      'title' => $id > 0 ? 'Editar categoria de conhecimento' : 'Nova categoria de conhecimento',
      'description' => 'Cadastre e organize as categorias da Central de Ajuda.'
    ]);

    $this->render('sistema/projeto/conhecimentoCategoriaForm', [
      'categoria' => $categoria,
      'podeAtualizar' => Helpers::pode('projeto.conhecimento.atualizar'),
      'podeCriar' => Helpers::pode('projeto.conhecimento.criar'),
      'urlLista' => Helpers::url($urlLista)
    ]);
  }

  public function salvarConhecimento(): void
  {
    $id = (int) ($_POST['con_id'] ?? 0);
    $this->validarPermissoes([$id > 0 ? 'projeto.conhecimento.atualizar' : 'projeto.conhecimento.criar']);
    $response = (new ConhecimentoModel())->salvarConhecimento($_POST);
    if ($response->status && $id <= 0 && !Helpers::pode('projeto.conhecimento.atualizar')) {
      $response->location = Helpers::url('projeto/conhecimento/form');
    }
    echo json_encode($response);
    exit;
  }

  public function salvarConhecimentoMidia(): void
  {
    $this->validarPermissoes(['projeto.conhecimento.atualizar']);
    // Mantém o POST como fonte primária; o ID na query é fallback para uploads
    // multipart cujo corpo não tenha sido interpretado pelo PHP.
    $dados = $_POST + $_GET;
    $dados['cmi_arquivo'] = $_FILES['cmi_arquivo'] ?? null;

    if (!isset($dados['cmi_idconhecimento'])) {
      $diagnostico = [
        'method' => $_SERVER['REQUEST_METHOD'] ?? null,
        'contentType' => $_SERVER['CONTENT_TYPE'] ?? null,
        'contentLength' => (int) ($_SERVER['CONTENT_LENGTH'] ?? 0),
        'postMaxSize' => ini_get('post_max_size'),
        'uploadMaxFilesize' => ini_get('upload_max_filesize'),
        'postFieldsReceived' => array_keys($_POST),
        'queryFieldsReceived' => array_keys($_GET),
        'filesReceived' => array_keys($_FILES)
      ];
      $resposta = (new \Source\Core\SimpleResponse(
        'Dados do envio não recebidos',
        'A chamada chegou ao controller, mas o PHP não interpretou os campos do formulário. Confira os dados técnicos da resposta.'
      ))
        ->erro()
        ->setArrayParams(['debug' => (object) $diagnostico])
        ->noDestroy();
      echo json_encode($resposta);
      exit;
    }

    $model = new ConhecimentoModel();
    if (isset($dados['cmi_idconhecimento']) && !isset($dados['cmi_chunk_id']) && isset($_GET['cmi_idconhecimento'])) {
      $diagnostico = [
        'contentType' => $_SERVER['CONTENT_TYPE'] ?? null,
        'contentLength' => (int) ($_SERVER['CONTENT_LENGTH'] ?? 0),
        'postMaxSize' => ini_get('post_max_size'),
        'uploadMaxFilesize' => ini_get('upload_max_filesize'),
        'postFieldsReceived' => array_keys($_POST),
        'queryFieldsReceived' => array_keys($_GET),
        'filesReceived' => array_keys($_FILES)
      ];
      $resposta = (new \Source\Core\SimpleResponse(
        'Campos do envio não recebidos',
        'O ID chegou pela URL, mas os campos do bloco não chegaram no formulário. Confira os dados técnicos da resposta.'
      ))
        ->erro()
        ->setArrayParams(['debug' => (object) $diagnostico])
        ->noDestroy();
      echo json_encode($resposta);
      exit;
    }

    $resposta = isset($dados['cmi_chunk_id'])
      ? $model->salvarMidiaChunk($dados)
      : $model->salvarMidia($dados);
    echo json_encode($resposta);
    exit;
  }

  public function alterarStatusConhecimentoMidia(): void
  {
    $this->validarPermissoes(['projeto.conhecimento.atualizar']);
    echo json_encode((new ConhecimentoModel())->alterarStatusMidia($_POST));
    exit;
  }

  public function salvarConhecimentoCategoria(): void
  {
    $id = (int) ($_POST['cct_id'] ?? 0);
    $this->validarPermissoes([$id > 0 ? 'projeto.conhecimento.atualizar' : 'projeto.conhecimento.criar']);
    $response = (new ConhecimentoModel())->salvarCategoria($_POST);
    if ($response->status) {
      if ($id > 0) {
        $response->location = Helpers::url('projeto/conhecimento/categoria/' . $id);
      } elseif (Helpers::pode('projeto.conhecimento.atualizar')) {
        $novoId = (int) ($response->lastInsertId ?? 0);
        if ($novoId > 0) {
          $response->location = Helpers::url('projeto/conhecimento/categoria/' . $novoId);
        }
      } else {
        $response->location = Helpers::url('projeto/conhecimento/categoria');
      }
    }
    echo json_encode($response);
    exit;
  }

  public function pesquisarConhecimentoCategorias(): void
  {
    $this->validarPermissoes(['projeto.conhecimento.visualizar', 'projeto.conhecimento.atualizar']);
    echo json_encode((new ConhecimentoModel())->getCategorias());
    exit;
  }

  public function reordenarConhecimentoCategoria(): void
  {
    $this->validarPermissoes(['projeto.conhecimento.atualizar']);
    echo json_encode((new ConhecimentoModel())->reordenarCategoria(
      (int) ($_POST['cct_id'] ?? 0),
      (string) ($_POST['direcao'] ?? '')
    ));
    exit;
  }

  private function contarOrganizacoes(): int
  {
    $response = (new ProjetoModel())->rawQuery(
      'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . '_organizacao',
      [],
      true,
      'Total encontrado com sucesso||Erro ao contar organizações'
    );

    return $response->status ? (int) ($response->data[0]->total ?? 0) : 0;
  }

  private function buscarEnderecoProjeto(object $projeto): ?object
  {
    $response = (new EnderecoModel())->rawQuery(
      "SELECT * FROM " . DB_PREFIX . "_endereco
       WHERE end_idorganizacao = 0
       AND end_idempresa = 0
       AND end_idorigem = :end_idorigem
       AND end_origem = 'projeto'
       ORDER BY end_id DESC
       LIMIT 1",
      ['end_idorigem' => (int) ($projeto->prj_id ?? 1)],
      true,
      'EndereÃ§o encontrado com sucesso||Erro ao buscar endereÃ§o do projeto'
    );

    return $response->status && $response->total > 0 ? $response->data[0] : null;
  }

  private function montarEnderecoConfig(object $projeto): object
  {
    $config = (object) [
      'status' => true,
      'title' => 'Endereço do projeto',
      'text' => '',
      'nomeModulo' => 'projeto',
      'origem' => 'projeto',
      'idorigem' => (int) ($projeto->prj_id ?? 1),
      'idorganizacao' => 0,
      'idempresa' => 0,
      'limite' => 0,
      'titulo' => 'Endereço do projeto',
      'subtitulo' => 'O projeto possui um endereço institucional obrigatório.',
      'permitir_criar' => Helpers::pode('projeto.atualizar') && Helpers::pode('endereco.criar'),
      'permitir_editar' => Helpers::pode('projeto.atualizar') && Helpers::pode('endereco.atualizar'),
      'permitir_excluir' => false,
      'permitir_principal' => true,
      'obrigatorio' => true,
      'recarregar_ao_salvar' => true,
      'data' => [],
      'total' => 0,
      'atingiu_limite' => false,
      'pode_criar' => false,
      'componente_id' => ''
    ];

    $getEndereco = (new EnderecoModel())->getEnderecosByConfig($config);

    if (!$getEndereco->status) {
      $config->status = false;
      $config->title = 'Erro';
      $config->text = $getEndereco->text ?? 'Erro ao buscar o endereço do projeto.';
    } else {
      $config->data = $getEndereco->data ?? [];
      $config->total = (int) ($getEndereco->total ?? 0);
    }

    $config->atingiu_limite = $config->limite > 0 && $config->total >= $config->limite;
    $config->pode_criar = $config->permitir_criar && !$config->atingiu_limite;
    $config->componente_id = 'endereco_' . md5("projeto_{$projeto->prj_id}_0_0");

    return $config;
  }
}
