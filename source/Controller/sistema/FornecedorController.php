<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Model\ArquivoModel;
use Source\Model\CadastroBasicoModel;
use Source\Model\ContatoModel;
use Source\Model\EnderecoModel;
use Source\Model\FornecedorModel;
use Source\Service\LimitePlanoService;

class FornecedorController extends SimpleController
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

  public function list(): void
  {
    $this->validarPermissoes(['fornecedor.visualizar']);

    $modelo = new FornecedorModel();
    $total = $modelo->contarFornecedoresContexto();

    $this->setBreadcrumb([
      [
        'title' => 'Fornecedores',
        'label' => 'Fornecedores',
        'url' => Helpers::url('fornecedor/list')
      ]
    ]);

    $this->render('sistema/fornecedor/list', [
      'getInfo' => (object) [
        'status' => true,
        'data' => [],
        'total' => $total->status ? (int) ($total->data[0]->total ?? 0) : 0
      ],
      'titulo' => 'Fornecedores',
      'descricao' => 'Gerencie os fornecedores financeiros da empresa no contexto atual.',
      'urlCadastrar' => Helpers::pode('fornecedor.criar') ? Helpers::url('fornecedor/form') : '',
      'urlPesquisar' => Helpers::url('fornecedor/pesquisarFornecedor'),
      'ocultarCadastrar' => !Helpers::pode('fornecedor.criar'),
      'labelPesquisaSimples' => 'Pesquisar por chave, documento, nome, e-mail ou celular',
      'labelTotal' => 'fornecedor(es)',
      'nomeArquivo' => 'fornecedores',
      'prefixo' => 'for'
    ]);
  }

  public function pesquisarFornecedor(): void
  {
    $this->validarPermissoes(['fornecedor.visualizar']);

    (new FornecedorModel())->pesquisarFornecedor($_POST)->json();
    exit;
  }

  public function pesquisarRapida(): void
  {
    $this->validarPermissoes(['fornecedor.visualizar']);

    (new FornecedorModel())->pesquisarRapida((string) ($_POST['conteudo'] ?? ''))->json();
    exit;
  }

  public function form($url = null): void
  {
    $id = is_array($url) ? (int) ($url['id'] ?? 0) : (int) ($url ?? 0);

    if ($id > 0) {
      $this->validarPermissoes(['fornecedor.visualizar', 'fornecedor.atualizar']);
    } else {
      $this->validarPermissoes(['fornecedor.visualizar', 'fornecedor.criar']);

      $limite = (new LimitePlanoService($this->contexto))->verificar('fornecedor');
      if ($limite->status && !$limite->permitido) {
        $limite->url_voltar = Helpers::url('fornecedor/list');
        $this->render('template/sistema/limite', ['limite' => $limite]);
        return;
      }
    }

    $modelo = new FornecedorModel();
    $fornecedor = null;

    if ($id > 0) {
      $getFornecedor = $modelo->getFornecedorById($id);

      if (!$getFornecedor->status || $getFornecedor->total !== 1) {
        $this->render('template/sistema/erro', [
          'erro' => (object) [
            'status' => false,
            'title' => 'Fornecedor nao encontrado',
            'text' => 'O fornecedor informado nao foi localizado ou nao pertence ao contexto atual.'
          ]
        ]);
        return;
      }

      $fornecedor = $getFornecedor->data[0];
    }

    $this->setBreadcrumb([
      [
        'title' => 'Pesquisar fornecedor',
        'label' => 'Fornecedores',
        'url' => Helpers::url('fornecedor/list'),
        'active' => false
      ],
      [
        'title' => $fornecedor ? 'Editando fornecedor' : 'Novo fornecedor',
        'label' => $fornecedor
          ? Helpers::gerarAssinatura((string) ($fornecedor->for_razaosocial ?? 'Fornecedor'))
          : 'Novo',
        'url' => '#'
      ]
    ]);

    $cadastroBasico = new CadastroBasicoModel('sexo');
    $getSexo = $cadastroBasico->getCadastroBasicoAtivo('usuario');
    $getEstadoCivil = (new CadastroBasicoModel('estadocivil'))->getCadastroBasicoAtivo('usuario');

    $componentes = [
      'enderecoConfig' => null,
      'contatoConfig' => null,
      'arquivoConfig' => null
    ];

    if ($fornecedor) {
      $componentes = $this->montarComponentes($fornecedor);
    }

    $this->render('sistema/fornecedor/form', [
      'fornecedor' => $fornecedor,
      'getSexo' => $getSexo,
      'getEstadoCivil' => $getEstadoCivil,
      'enderecoConfig' => $componentes['enderecoConfig'],
      'contatoConfig' => $componentes['contatoConfig'],
      'arquivoConfig' => $componentes['arquivoConfig']
    ]);
  }

  public function salvarFornecedor(): void
  {
    $id = (int) ($_POST['for_id'] ?? 0);
    $this->validarPermissoes([$id > 0 ? 'fornecedor.atualizar' : 'fornecedor.criar']);

    (new FornecedorModel())->salvarFornecedor($_POST)->json();
    exit;
  }

  public function salvarAcessoFornecedor(): void
  {
    $this->validarPermissoes(['fornecedor.atualizar']);

    (new FornecedorModel())->salvarAcessoFornecedor($_POST)->json();
    exit;
  }

  public function alterarStatusFornecedor(): void
  {
    $this->validarPermissoes(['fornecedor.atualizar']);

    (new FornecedorModel())->alterarStatusFornecedor($_POST)->json();
    exit;
  }

  private function montarComponentes(object $fornecedor): array
  {
    $orgId = (int) ($this->contexto->organizacao->org_id ?? 0);
    $empId = (int) ($this->contexto->empresa->emp_id ?? 0);
    $fornecedorId = (int) ($fornecedor->for_id ?? 0);
    $parametroFornecedor = $this->contexto->plano->plc_parametro->fornecedor ?? (object) [];

    $getLimite = static function ($origem, string $campo, int $padrao = 0): int {
      if (is_object($origem)) {
        $origem = $origem->{$campo} ?? null;
      } elseif (is_array($origem)) {
        $origem = $origem[$campo] ?? null;
      } else {
        $origem = null;
      }

      if (is_object($origem)) {
        $origem = $origem->limite ?? $padrao;
      } elseif (is_array($origem)) {
        $origem = $origem['limite'] ?? $padrao;
      }

      return max(0, (int) ($origem ?? $padrao));
    };

    $montarBase = static function (string $tipo, int $limite) use ($orgId, $empId, $fornecedorId): object {
      return (object) [
        'status' => true,
        'title' => ucfirst($tipo),
        'text' => '',
        'nomeModulo' => 'fornecedor',
        'origem' => 'fornecedor',
        'idorigem' => $fornecedorId,
        'idorganizacao' => $orgId,
        'idempresa' => $empId,
        'limite' => $limite,
        'data' => [],
        'total' => 0,
        'atingiu_limite' => false,
        'pode_criar' => false,
        'obrigatorio' => false,
        'recarregar_ao_salvar' => true,
        'permitir_criar' => Helpers::pode($tipo . '.criar'),
        'permitir_editar' => Helpers::pode($tipo . '.atualizar'),
        'permitir_excluir' => Helpers::pode($tipo . '.excluir'),
        'permitir_principal' => $tipo === 'endereco',
        'componente_id' => $tipo . '_fornecedor_' . $fornecedorId . '_' . $orgId . '_' . $empId
      ];
    };

    $enderecoConfig = $montarBase('endereco', $getLimite($parametroFornecedor, 'endereco', 0));
    $enderecoConfig->titulo = 'Endereços';
    $enderecoConfig->subtitulo = 'Gerencie os endereços vinculados a este fornecedor.';
    $getEndereco = (new EnderecoModel())->getEnderecosByConfig($enderecoConfig);
    if ($getEndereco->status) {
      $enderecoConfig->data = $getEndereco->data ?? [];
      $enderecoConfig->total = (int) $getEndereco->total;
    }
    $enderecoConfig->atingiu_limite = $enderecoConfig->limite > 0 && $enderecoConfig->total >= $enderecoConfig->limite;
    $enderecoConfig->pode_criar = $enderecoConfig->permitir_criar && !$enderecoConfig->atingiu_limite;

    $contatoConfig = $montarBase('contato', $getLimite($parametroFornecedor, 'contato', 0));
    $contatoConfig->titulo = 'Contatos';
    $contatoConfig->subtitulo = 'Gerencie os contatos complementares vinculados a este fornecedor.';
    $getContato = (new ContatoModel())->getContatosByConfig($contatoConfig);
    if ($getContato->status) {
      $contatoConfig->data = $getContato->data ?? [];
      $contatoConfig->total = (int) $getContato->total;
    }
    $contatoConfig->atingiu_limite = $contatoConfig->limite > 0 && $contatoConfig->total >= $contatoConfig->limite;
    $contatoConfig->pode_criar = $contatoConfig->permitir_criar && !$contatoConfig->atingiu_limite;

    $arquivoConfig = $montarBase('arquivo', $getLimite($parametroFornecedor, 'arquivo', 0));
    $arquivoConfig->titulo = 'Arquivos';
    $arquivoConfig->subtitulo = 'Gerencie os arquivos vinculados a este fornecedor.';
    $arquivoConfig->accept = '.pdf,.txt,.doc,.docx,.xls,.xlsx,.json,.xml,.jpg,.jpeg,.png,.webp';
    $arquivoConfig->multiple = true;
    $getArquivo = (new ArquivoModel())->getArquivosByConfig($arquivoConfig);
    if ($getArquivo->status) {
      $arquivoConfig->data = $getArquivo->data ?? [];
      $arquivoConfig->total = (int) $getArquivo->total;
    }
    $arquivoConfig->atingiu_limite = $arquivoConfig->limite > 0 && $arquivoConfig->total >= $arquivoConfig->limite;
    $arquivoConfig->pode_criar = $arquivoConfig->permitir_criar && !$arquivoConfig->atingiu_limite;

    return [
      'enderecoConfig' => $enderecoConfig,
      'contatoConfig' => $contatoConfig,
      'arquivoConfig' => $arquivoConfig
    ];
  }
}
