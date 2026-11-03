<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Model\FornecedorModel;
use Source\Model\ArquivoModel;
use Source\Model\FinanceiroBasicoModel;
use Source\Model\LancamentoModel;
use Source\Model\TituloModel;
use Source\Service\LimitePlanoService;

class TituloController extends SimpleController
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
    $this->validarPermissoes(['titulo.visualizar']);

    $modelo = new TituloModel();
    $total = $modelo->contarTitulosContexto();

    $this->setBreadcrumb([
      [
        'title' => 'Títulos',
        'label' => 'Títulos',
        'url' => Helpers::url('titulo/list')
      ]
    ]);

    $this->render('sistema/financeiro/titulo/list', [
      'modo' => 'list',
      'getInfo' => (object) [
        'status' => true,
        'data' => [],
        'total' => $total->status ? (int) ($total->data[0]->total ?? 0) : 0
      ],
      'titulo' => 'Títulos',
      'descricao' => 'Pesquise e acompanhe os títulos financeiros do contexto ativo.',
      'urlCadastrar' => Helpers::pode('titulo.criar') ? Helpers::url('titulo/form') : '',
      'ocultarCadastrar' => !Helpers::pode('titulo.criar'),
      'urlPesquisar' => Helpers::url('titulo/pesquisarTitulo'),
      'labelPesquisaSimples' => 'Pesquisar por título, descrição, plano ou status',
      'labelTotal' => 'título(s)',
      'nomeArquivo' => 'titulos',
      'prefixo' => 'tit'
    ]);
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
      $this->validarPermissoes(['titulo.visualizar', 'titulo.atualizar']);
    } else {
      $this->validarPermissoes(['titulo.visualizar', 'titulo.criar']);

      $limite = (new LimitePlanoService($this->contexto))->verificar('titulo');
      if ($limite->status && !$limite->permitido) {
        $limite->url_voltar = Helpers::url('financeiro/dashboard');
        $this->render('template/sistema/limite', ['limite' => $limite]);
        return;
      }
    }

    $modelo = new TituloModel();
    $titulo = null;
    $isUpdate = $id > 0;

    if ($id > 0) {
      $getTitulo = $modelo->getTituloById($id);
      if (!$getTitulo->status || $getTitulo->total !== 1) {
        $this->render('template/sistema/erro', [
          'erro' => (object) [
            'status' => false,
            'title' => 'Título não encontrado',
            'text' => 'O título informado não foi localizado ou não pertence ao contexto atual.'
          ]
        ]);
        return;
      }

      $titulo = $getTitulo->data[0];
    }

    $statusTitulo = (string) ($titulo->tit_status ?? 'Cadastrado');
    $possuiLancamentoBaixado = $isUpdate && $modelo->possuiLancamentoBaixado($id);
    $podeEditar = !$isUpdate
      ? Helpers::validarPermissao('titulo.criar')->status
      : $statusTitulo === 'Cadastrado' && Helpers::validarPermissao('titulo.atualizar')->status;
    $podeCancelar = $isUpdate
      && in_array($statusTitulo, ['Cadastrado', 'Confirmado'], true)
      && Helpers::validarPermissao('titulo.cancelar')->status
      && ($statusTitulo === 'Cadastrado' || !$possuiLancamentoBaixado);
    $podeConfirmar = $isUpdate
      && $statusTitulo === 'Cadastrado'
      && Helpers::validarPermissao('titulo.confirmar')->status;
    $lancamentos = (object) ['status' => true, 'total' => 0, 'data' => []];
    $contasFinanceiras = [];
    $centrosCusto = [];
    $formasPagamento = [];
    $condicoesPagamento = [];
    $podeGerarLancamento = false;
    $podeBaixarLancamento = false;

    if ($isUpdate) {
      $lancamentos = (new LancamentoModel())->getPorTitulo($id);
      $contasFinanceiras = (new FinanceiroBasicoModel('conta_financeira'))->getRegistrosContexto()->data ?? [];
      $centrosCusto = (new FinanceiroBasicoModel('centro_custo'))->getRegistrosContexto()->data ?? [];
      $formasPagamento = (new FinanceiroBasicoModel('forma_pagamento'))->getRegistrosContexto()->data ?? [];
      $condicoesPagamento = (new FinanceiroBasicoModel('condicao_pgto'))->getRegistrosContexto()->data ?? [];
      $podeGerarLancamento = $statusTitulo === 'Confirmado'
        && (int) ($lancamentos->total ?? 0) === 0
        && Helpers::validarPermissao('financeiro.lancamento.criar')->status;
      $podeBaixarLancamento = Helpers::validarPermissao('financeiro.lancamento.baixar')->status;
    }

    $this->setBreadcrumb([
      [
        'title' => 'Títulos',
        'label' => 'Títulos',
        'url' => Helpers::url('titulo/list'),
        'active' => false
      ],
      [
        'title' => $id > 0 ? (string) ($titulo->tit_nome ?? 'Título') : 'Novo título',
        'label' => $id > 0 ? (string) ($titulo->tit_nome ?? 'Título') : 'Novo título',
        'url' => '#'
      ]
    ]);

    $this->render('sistema/financeiro/titulo/form', [
      'modo' => 'form',
      'id' => $id,
      'titulo' => $titulo,
      'planosContas' => $modelo->getPlanosContasAtivos(),
      'podeEditar' => $podeEditar,
      'podeCancelar' => $podeCancelar,
      'podeConfirmar' => $podeConfirmar,
      'possuiLancamentoBaixado' => $possuiLancamentoBaixado,
      'lancamentos' => $lancamentos,
      'contasFinanceiras' => $contasFinanceiras,
      'centrosCusto' => $centrosCusto,
      'formasPagamento' => $formasPagamento,
      'condicoesPagamento' => $condicoesPagamento,
      'podeGerarLancamento' => $podeGerarLancamento,
      'podeBaixarLancamento' => $podeBaixarLancamento
    ]);
  }

  public function salvarTitulo(): void
  {
    $id = (int) ($_POST['tit_id'] ?? 0);
    $this->validarPermissoes([$id > 0 ? 'titulo.atualizar' : 'titulo.criar']);

    (new TituloModel())->salvarTitulo($_POST)->json();
    exit;
  }

  public function confirmarTitulo(): void
  {
    $this->validarPermissoes(['titulo.confirmar']);

    $tituloId = (int) ($_POST['tit_id'] ?? 0);
    (new TituloModel())->confirmarTitulo($tituloId)->json();
    exit;
  }

  public function cancelarTitulo(): void
  {
    $this->validarPermissoes(['titulo.cancelar']);

    $tituloId = (int) ($_POST['tit_id'] ?? 0);
    $motivo = trim((string) ($_POST['motivo'] ?? ''));
    (new TituloModel())->cancelarTitulo($tituloId, $motivo)->json();
    exit;
  }

  public function gerarLancamentoTitulo(): void
  {
    $this->validarPermissoes(['financeiro.lancamento.criar']);

    $tituloId = (int) ($_POST['tit_id'] ?? 0);
    $lancamentos = $_POST['lancamentos'] ?? '[]';
    $lancamentos = is_string($lancamentos) ? json_decode($lancamentos, true) : $lancamentos;
    if (!is_array($lancamentos)) {
      $lancamentos = [];
    }

    (new LancamentoModel())->gerarPorTitulo($tituloId, $lancamentos)->json();
    exit;
  }

  public function pagarReceberLancamento(): void
  {
    $this->validarPermissoes(['financeiro.lancamento.baixar']);

    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids)) {
      $ids = [$ids];
    }

    (new LancamentoModel())->pagarReceber($ids, trim((string) ($_POST['lan_dtbaixa'] ?? '')))->json();
    exit;
  }

  public function modalDetalhesLancamento(): void
  {
    $this->validarPermissoes(['financeiro.lancamento.visualizar']);

    $lanId = (int) ($_POST['lan_id'] ?? 0);
    $resposta = (new LancamentoModel())->getDetalhadoPorId($lanId);
    if (!$resposta->status || $resposta->total !== 1) {
      echo '<div class="alert alert-danger mb-0">Lançamento não encontrado ou fora do contexto atual.</div>';
      exit;
    }

    $lancamento = $resposta->data[0];
    $titulo = null;
    $tituloId = (int) ($lancamento->lan_idtitulo ?? 0);
    if ($tituloId > 0) {
      $getTitulo = (new TituloModel())->getTituloById($tituloId);
      if ($getTitulo->status && $getTitulo->total === 1) $titulo = $getTitulo->data[0];
    }

    $status = (string) ($lancamento->lan_status ?? '');
    $arquivos = (new ArquivoModel())->getArquivosByConfig((object) [
      'idorganizacao' => (int) ($this->contexto->organizacao->org_id ?? 0),
      'idempresa' => (int) ($this->contexto->empresa->emp_id ?? 0),
      'idorigem' => $lanId,
      'origem' => 'lancamento'
    ]);
    $this->render('sistema/financeiro/titulo/modalDetalhesLancamento', [
      'lancamento' => $lancamento,
      'titulo' => $titulo,
      'arquivos' => $arquivos->status ? ($arquivos->data ?? []) : [],
      'podeBaixar' => Helpers::validarPermissao('financeiro.lancamento.baixar')->status && in_array($status, ['A pagar', 'A receber'], true),
      'podeCorrigir' => Helpers::validarPermissao('financeiro.lancamento.atualizar')->status && in_array($status, ['Pago', 'Recebido'], true),
      'podeEstornar' => Helpers::validarPermissao('financeiro.lancamento.estornar')->status && in_array($status, ['Pago', 'Recebido'], true)
    ]);
  }

  public function modalCorrecaoLancamento(): void
  {
    $this->validarPermissoes(['financeiro.lancamento.atualizar']);

    $lanId = (int) ($_POST['lan_id'] ?? 0);
    $resposta = (new LancamentoModel())->getDetalhadoPorId($lanId);
    if (!$resposta->status || $resposta->total !== 1 || !in_array(($resposta->data[0]->lan_status ?? ''), ['Pago', 'Recebido'], true)) {
      echo '<div class="alert alert-warning mb-0">Este lançamento não está disponível para correção.</div>';
      exit;
    }

    $lancamento = $resposta->data[0];
    $titulo = null;
    $tituloId = (int) ($lancamento->lan_idtitulo ?? 0);
    if ($tituloId > 0) {
      $getTitulo = (new TituloModel())->getTituloById($tituloId);
      if ($getTitulo->status && $getTitulo->total === 1) {
        $titulo = $getTitulo->data[0];
      }
    }

    $this->render('sistema/financeiro/titulo/modalCorrecaoLancamento', [
      'lancamento' => $lancamento,
      'titulo' => $titulo,
      'contasFinanceiras' => (new FinanceiroBasicoModel('conta_financeira'))->getRegistrosContexto()->data ?? [],
      'centrosCusto' => (new FinanceiroBasicoModel('centro_custo'))->getRegistrosContexto()->data ?? [],
      'formasPagamento' => (new FinanceiroBasicoModel('forma_pagamento'))->getRegistrosContexto()->data ?? [],
      'condicoesPagamento' => (new FinanceiroBasicoModel('condicao_pgto'))->getRegistrosContexto()->data ?? []
    ]);
  }

  public function corrigirLancamento(): void
  {
    $this->validarPermissoes(['financeiro.lancamento.atualizar']);
    (new LancamentoModel())->corrigirLancamento((int) ($_POST['lan_id'] ?? 0), $_POST)->json();
    exit;
  }

  public function estornarLancamento(): void
  {
    $this->validarPermissoes(['financeiro.lancamento.estornar']);
    (new LancamentoModel())->estornarLancamento((int) ($_POST['lan_id'] ?? 0), trim((string) ($_POST['motivo'] ?? '')))->json();
    exit;
  }

  public function pesquisarVinculo(): void
  {
    $this->validarPermissoes(['titulo.visualizar']);

    $tipo = strtolower(trim((string) ($_POST['tipo'] ?? 'fornecedor')));
    $pesquisa = trim((string) ($_POST['pesquisa'] ?? $_POST['lbLik'] ?? ''));
    if ($tipo !== 'fornecedor') {
      (new SimpleResponse('Atenção', 'No ICLIV, os títulos podem ser vinculados a fornecedores.'))->warning()->setArrayParams(['data' => [], 'total' => 0])->json();
      exit;
    }

    $filtros = [
      'lbTipoPesquisa' => 'simples',
      'lbLik' => $pesquisa,
      'lbLmt' => 20
    ];
    $resposta = (new FornecedorModel())->pesquisarFornecedor($filtros);

    if (!$resposta->status) {
      $resposta->json();
      exit;
    }

    $dados = array_map(static function (object $registro): object {
      return (object) [
        'id' => (int) ($registro->for_id ?? 0),
        'origem' => 'fornecedor',
        'tipo' => 'Fornecedor',
        'nome' => (string) ($registro->for_razaosocial ?: $registro->for_nomefantasia),
        'razaoSocial' => (string) ($registro->for_nomefantasia ?? ''),
        'documento' => (string) ($registro->for_cnpjcpf ?? ''),
        'documentoMascara' => (string) Helpers::adicionaMascaraCnpjCPf($registro->for_cnpjcpf ?? ''),
        'celular' => (string) ($registro->for_celular ?? ''),
        'celularMascara' => (string) Helpers::adicionaMascaraContato($registro->for_celular ?? ''),
        'email' => (string) ($registro->for_email ?? ''),
        'logo' => (string) ($registro->for_logo ?? ''),
        'ministerios' => []
      ];
    }, $resposta->data ?? []);

    (new SimpleResponse($resposta))
      ->setArrayParams(['data' => $dados, 'total' => count($dados)])
      ->noDestroy()
      ->json();
    exit;
  }

  public function dadosLancamentoRapido(): void
  {
    $this->validarPermissoes(['titulo.criar', 'financeiro.lancamento.criar']);

    $planos = array_map(static fn(object $plano): array => [
      'id' => (int) ($plano->pct_id ?? 0),
      'nome' => (string) ($plano->pct_nome ?? ''),
      'natureza' => (string) ($plano->pct_natureza ?? ''),
      'codigo' => (string) ($plano->pct_codigo ?? $plano->pct_id ?? '')
    ], (new TituloModel())->getPlanosContasAtivos());

    $centrosCusto = array_map(static fn(object $centro): array => [
      'id' => (int) ($centro->id ?? $centro->cdc_id ?? 0),
      'nome' => (string) ($centro->nome ?? $centro->cdc_nome ?? '')
    ], (new FinanceiroBasicoModel('centro_custo'))->getOpcoesAtivas());

    $contasFinanceiras = array_map(static fn(object $conta): array => [
      'id' => (int) ($conta->id ?? $conta->ctf_id ?? 0),
      'nome' => (string) ($conta->nome ?? $conta->ctf_nome ?? '')
    ], (new FinanceiroBasicoModel('conta_financeira'))->getOpcoesAtivas());

    $formasPagamento = array_map(static fn(object $forma): array => [
      'id' => (int) ($forma->id ?? $forma->fpg_id ?? 0),
      'nome' => (string) ($forma->nome ?? $forma->fpg_nome ?? '')
    ], (new FinanceiroBasicoModel('forma_pagamento'))->getOpcoesAtivas());

    $condicoesPagamento = array_values(array_map(static fn(object $condicao): array => [
      'id' => (int) ($condicao->cpg_id ?? 0),
      'formaId' => (int) ($condicao->cpg_idformapagamento ?? 0),
      'nome' => (string) ($condicao->cpg_nome ?? '')
    ], array_filter(
      (new FinanceiroBasicoModel('condicao_pgto'))->getRegistrosContexto()->data ?? [],
      static fn(object $condicao): bool => ($condicao->cpg_status ?? '') === 'Ativo'
    )));

    (new SimpleResponse('Sucesso', 'Opções carregadas com sucesso.'))
      ->sucesso()
      ->setArrayParams(['data' => [
        'planos' => $planos,
        'centrosCusto' => $centrosCusto,
        'contasFinanceiras' => $contasFinanceiras,
        'formasPagamento' => $formasPagamento,
        'condicoesPagamento' => $condicoesPagamento
      ]])
      ->json();
    exit;
  }

  public function registrarLancamentoRapido(): void
  {
    $this->validarPermissoes([
      'titulo.criar',
      'titulo.confirmar',
      'financeiro.lancamento.criar',
      'financeiro.lancamento.baixar'
    ]);

    (new TituloModel())->registrarLancamentoRapido($_POST)->json();
    exit;
  }

  public function pesquisarTitulo(): void
  {
    $this->validarPermissoes(['titulo.visualizar']);
    (new TituloModel())->pesquisarTitulo($_POST)->json();
    exit;
  }

  public function pesquisarRapida(): void
  {
    $this->validarPermissoes(['titulo.visualizar']);
    (new TituloModel())->pesquisarRapida((string) ($_POST['conteudo'] ?? ''))->json();
    exit;
  }
}
