<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Model\ArquivoModel;
use Source\Model\CadastroBasicoModel;
use Source\Model\ContatoModel;
use Source\Model\EmpresaModel;
use Source\Model\EnderecoModel;
use Source\Model\PwaModel;
use Source\Service\LimitePlanoService;

class EmpresaController extends SimpleController
{
  use CadastroBasicoModuloTrait;

  protected function moduloCadastroBasico(): string { return 'empresa'; }

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

  public function list(): void
  {
    $this->validarPermissoes(['empresa.visualizar']);

    $empresaModel = new EmpresaModel();
    $getTotalEmpresas = $empresaModel->contarEmpresasContexto();

    $this->setBreadcrumb([
      [
        'title' => 'Empresas',
        'label' => 'Empresas',
        'url' => Helpers::url('empresa/list')
      ]
    ]);

    $this->render('sistema/empresa/list', [
      'getInfo' => (object) [
        'status' => true,
        'data' => [],
        'total' => $getTotalEmpresas->status
          ? (int) ($getTotalEmpresas->data[0]->total ?? 0)
          : 0
      ],
      'titulo' => 'Empresas',
      'descricao' => 'Gerencie as empresas vinculadas ao contexto atual.',
      'urlCadastrar' => Helpers::pode('empresa.criar') ? Helpers::url('empresa/form') : '',
      'urlPesquisar' => Helpers::url('empresa/pesquisarEmpresa'),
      'ocultarCadastrar' => !Helpers::pode('empresa.criar'),
      'labelPesquisaSimples' => 'Pesquisar por nome, CPF/CNPJ, observação ou descrição',
      'labelTotal' => 'empresa(s)',
      'nomeArquivo' => 'empresas',
      'prefixo' => 'emp'
    ]);
  }

  public function pesquisarEmpresa(): void
  {
    $this->validarPermissoes(['empresa.visualizar']);

    $response = (new EmpresaModel())->pesquisarEmpresa($_POST);

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
        'empresa.visualizar',
        'empresa.atualizar'
      ]);
    } else {
      $this->validarPermissoes([
        'empresa.visualizar',
        'empresa.criar'
      ]);

      $limiteEmpresa = (new LimitePlanoService($this->contexto))->verificar('empresa');

      if ($limiteEmpresa->status && !$limiteEmpresa->permitido) {
        $limiteEmpresa->url_voltar = Helpers::url('empresa/list');
        $this->render('template/sistema/limite', [
          'limite' => $limiteEmpresa
        ]);
        return;
      }
    }

    $empresaModel = new EmpresaModel();

    if ($id > 0) {
      $getEmpresa = $empresaModel->getEmpresaById($id, true);

      if (!$getEmpresa->status || $getEmpresa->total != 1) {
        $this->render('template/sistema/erro', [
          'erro' => (object) [
            'status' => false,
            'title' => 'Empresa não encontrada',
            'text' => 'A empresa informada não foi localizada ou não pertence ao contexto atual.'
          ]
        ]);
        return;
      }

      $empresa = $getEmpresa->data[0];
    } else {
      $empresa = null;
    }

    $pwaConfig = null;
    $pwaImagens = ['icones' => [], 'capturas' => []];

    if ($empresa) {
      $pwaModel = new PwaModel();
      $getPwa = $pwaModel->getPwaByEmpresa((int) $empresa->emp_id, (int) $empresa->emp_idorganizacao);
      $pwaConfig = $getPwa->status && !empty($getPwa->data) ? $getPwa->data[0] : null;
      $pwaImagens = $pwaModel->getImagensByEmpresa((int) $empresa->emp_id);
    }

    $this->setBreadcrumb([
      [
        'title' => 'Pesquisar empresa',
        'label' => 'Empresas',
        'url' => Helpers::url('empresa/list'),
        'active' => false
      ],
      [
        'title' => !is_null($empresa) && $empresa->emp_id > 0 ? 'Editando empresa' : 'Nova empresa',
        'label' => !is_null($empresa) && $empresa->emp_id > 0 ? Helpers::gerarAssinatura($empresa->emp_razaosocial) : 'Nova',
        'url' => '#'
      ]
    ]);

    $dadosComplementares = (object) [
      'status' => false,
      'alert' => []
    ];

    $arquivoConfig = null;
    if (!is_null($empresa)) {
      $arquivoConfig = (object) [
        'status' => true,
        'title' => 'Arquivos',
        'text' => '',
        'nomeModulo' => 'empresa',
        'origem' => 'empresa',
        'idorigem' => $empresa->emp_id,
        'idorganizacao' => $empresa->emp_idorganizacao,
        'idempresa' => $empresa->emp_id,
        'limite' => $this->contexto->plano->plc_parametro->empresa->arquivo->limite,
        'titulo' => 'Arquivos',
        'subtitulo' => 'Gerencie os arquivos vinculados a esta empresa.',
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
    if (!is_null($empresa)) {
      $contatoConfig = (object) [
        'status' => true,
        'title' => 'Contatos',
        'text' => '',
        'nomeModulo' => 'empresa',
        'origem' => 'empresa',
        'idorigem' => $empresa->emp_id,
        'idorganizacao' => $this->contexto->organizacao->org_id,
        'idempresa' => $this->contexto->empresa->emp_id,
        'limite' => $this->contexto->plano->plc_parametro->empresa->contato->limite,
        'titulo' => 'Contatos',
        'subtitulo' => 'Gerencie os contatos complementares vinculados a esta empresa.',
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
    if (!is_null($empresa)) {
      $enderecoConfig = (object) [
        'status' => true,
        'title' => 'Endereços',
        'text' => '',
        'nomeModulo' => 'empresa',
        'origem' => 'empresa',
        'idorigem' => $empresa->emp_id,
        'idorganizacao' => $this->contexto->organizacao->org_id,
        'idempresa' => $this->contexto->empresa->emp_id,
        'limite' => $this->contexto->plano->plc_parametro->empresa->endereco->limite,
        'titulo' => 'Endereços',
        'subtitulo' => 'Gerencie os endereços vinculados a esta empresa.',
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

    $parametrosData = (object) [
      'usuario' => (object) [
        'titulo' => 'Usuário',
        'icone' => 'fa-solid fa-user-gear',
        'descricao' => 'Configure regras comportamentais relacionadas ao cadastro e edição de usuários desta empresa.',
        'booleanos' => [
          'herdarPreferenciasEmpresa' => 'Usuários criados nesta empresa devem herdar suas preferências visuais',
          'notificarAniversarianteWpp' => 'Notificar aniversariante por WhatsApp',
          'notificarAniversarianteEmail' => 'Notificar aniversariante por e-mail'
        ],
        'camposObrigatoriosCadastro' => [
          'usu_cnpjcpf' => 'CPF/CNPJ',
          'usu_nomefantasia' => 'Nome social / Fantasia',
          'usu_observacao' => 'Observação',
          'usu_im' => 'Inscrição municipal',
          'usu_ie' => 'Inscrição estadual',
          'usu_rg' => 'RG',
          'usu_dtnasc' => 'Data de nascimento / fundação',
          'usu_nomemae' => 'Nome da mãe',
          'usu_nomepai' => 'Nome do pai',
          'usu_idsexo' => 'Sexo',
          'usu_idestadocivil' => 'Estado civil',
          'usu_idnaturalidade' => 'Naturalidade',
          'usu_niveisacesso' => 'Níveis de acesso',
          'usu_email' => 'E-mail',
          'usu_celular' => 'Celular'
        ],
        'camposObrigatoriosEdicao' => [
          'usu_cnpjcpf' => 'CPF/CNPJ',
          'usu_nomefantasia' => 'Nome social / Fantasia',
          'usu_observacao' => 'Observação',
          'usu_im' => 'Inscrição municipal',
          'usu_ie' => 'Inscrição estadual',
          'usu_rg' => 'RG',
          'usu_dtnasc' => 'Data de nascimento / fundação',
          'usu_nomemae' => 'Nome da mãe',
          'usu_nomepai' => 'Nome do pai',
          'usu_idsexo' => 'Sexo',
          'usu_idestadocivil' => 'Estado civil',
          'usu_idnaturalidade' => 'Naturalidade',
          'usu_niveisacesso' => 'Níveis de acesso',
          'usu_email' => 'E-mail',
          'usu_celular' => 'Celular'
        ]
      ]
    ];

    $this->render('sistema/empresa/form', [
      'empresa' => $empresa,
      'getSexo' => (new CadastroBasicoModel('sexo'))->getCadastroBasicoAtivo('empresa'),
      'getEstadoCivil' => (new CadastroBasicoModel('estadocivil'))->getCadastroBasicoAtivo('empresa'),
      'getNaturezaJuridica' => (new CadastroBasicoModel('naturezajuridica'))->getCadastroBasicoAtivo('empresa'),
      'getNaturalidade' => (new CadastroBasicoModel('naturalidade'))->getCadastroBasicoAtivo('empresa'),
      'dadosComplementares' => $dadosComplementares,
      'arquivoConfig' => $arquivoConfig,
      'contatoConfig' => $contatoConfig,
      'enderecoConfig' => $enderecoConfig,
      'pwaConfig' => $pwaConfig,
      'pwaImagens' => $pwaImagens,
      'parametrosData' => $parametrosData
    ]);
  }

  public function salvarEmpresa(): void
  {
    if ((int) ($_POST['emp_id'] ?? 0) > 0) {
      $this->validarPermissoes(['empresa.atualizar']);
    } else {
      $this->validarPermissoes(['empresa.criar']);

      if (empty($this->contexto->empresa->emp_id)) {
        echo json_encode(
          (new SimpleResponse("Erro", "Empresa principal do contexto não identificada."))
            ->erro()
            ->noDestroy()
        );
        exit;
      }

      $_POST['emp_tipo'] = 'Filial';
      $_POST['emp_idempresa_pai'] = (int) $this->contexto->empresa->emp_id;
    }

    echo json_encode((new EmpresaModel())->salvarEmpresa($_POST));
    exit;
  }

  public function pesquisarRapida(): void
  {
    $this->validarPermissoes(['empresa.visualizar']);

    $conteudo = $_POST['conteudo'] ?? '';

    $empresaModel = new EmpresaModel();
    $response = $empresaModel->pesquisarRapida($conteudo);

    $response->json();
  }
}
