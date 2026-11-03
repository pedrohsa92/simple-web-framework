<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Model\FinanceiroBasicoModel;
use Source\Model\ArquivoModel;
use Source\Model\LancamentoModel;
use Source\Model\TituloModel;
use Source\Model\TransferenciaFinanceiraModel;

class FinanceiroController extends SimpleController
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

  public function dashboard(): void
  {
    $this->validarPermissoes(['financeiro.visualizar', 'financeiro.dashboard']);

    $organizacaoId = (int) ($this->contexto->organizacao->org_id ?? 0);
    $empresaId = (int) ($this->contexto->empresa->emp_id ?? 0);
    $contaModel = new FinanceiroBasicoModel('conta_financeira');
    $lancamentoModel = new LancamentoModel();
    $contasResponse = $contaModel->getRegistrosContexto();
    $contas = $contasResponse->status ? ($contasResponse->data ?? []) : [];
    $tabelaLancamento = DB_PREFIX . '_lancamento';
    $tabelaTitulo = DB_PREFIX . '_titulo';
    $tabelaConta = DB_PREFIX . '_conta_financeira';

    $dashboard = (object) [
      'getSaldoPorConta' => [],
      'resumoGeral' => (object) [
        'saldoAtual' => 0.00,
        'saldoProjetado' => 0.00,
        'aReceber' => 0.00,
        'aPagar' => 0.00
      ],
      'centralDeContas' => (object) [
        'resumo' => (object) [
          'qntEmAberto' => 0,
          'qntAReceber' => 0,
          'qntAPagar' => 0,
          'qntEmRecebimento' => 0,
          'qntEmPagamento' => 0
        ],
        'data' => []
      ]
    ];

    foreach ($contas as $conta) {
      $resumo = $lancamentoModel->rawQuery(
        "SELECT
           COALESCE(SUM(CASE WHEN lan_status = 'Recebido' AND lan_natureza = 'Entrada' AND lan_tipo <> 'Transferencia' THEN lan_valor ELSE 0 END), 0) AS recebimentos,
           COALESCE(SUM(CASE WHEN lan_status = 'Pago' AND lan_natureza = 'Saida' AND lan_tipo <> 'Transferencia' THEN lan_valor ELSE 0 END), 0) AS saidas,
           COALESCE(SUM(CASE WHEN lan_status = 'Recebido' AND lan_natureza = 'Entrada' THEN lan_valor ELSE 0 END), 0) AS saldo_entradas,
           COALESCE(SUM(CASE WHEN lan_status = 'Pago' AND lan_natureza = 'Saida' THEN lan_valor ELSE 0 END), 0) AS saldo_saidas,
           COALESCE(SUM(CASE WHEN lan_status = 'A receber' AND lan_natureza = 'Entrada' THEN lan_valor ELSE 0 END), 0) AS a_receber,
           COALESCE(SUM(CASE WHEN lan_status = 'A pagar' AND lan_natureza = 'Saida' THEN lan_valor ELSE 0 END), 0) AS a_pagar,
           COUNT(CASE WHEN lan_status IN ('A receber', 'A pagar') THEN lan_id END) AS qnt_pendente,
           MAX(lan_dtalter) AS ultima_transacao
         FROM {$tabelaLancamento}
         WHERE lan_idorganizacao = :lan_idorganizacao
           AND lan_idempresa = :lan_idempresa
           AND lan_idcontafinanceira = :lan_idcontafinanceira",
        [
          'lan_idorganizacao' => $organizacaoId,
          'lan_idempresa' => $empresaId,
          'lan_idcontafinanceira' => (int) ($conta->ctf_id ?? 0)
        ],
        true,
        'Resumo financeiro encontrado||Erro ao buscar resumo financeiro'
      );

      $dadosResumo = $resumo->status && !empty($resumo->data) ? $resumo->data[0] : (object) [];
      $saldoInicial = round((float) ($conta->ctf_saldo_inicial ?? 0), 2);
      $recebimentos = round((float) ($dadosResumo->recebimentos ?? 0), 2);
      $saidas = round((float) ($dadosResumo->saidas ?? 0), 2);
      $saldoEntradas = round((float) ($dadosResumo->saldo_entradas ?? 0), 2);
      $saldoSaidas = round((float) ($dadosResumo->saldo_saidas ?? 0), 2);
      $aReceber = round((float) ($dadosResumo->a_receber ?? 0), 2);
      $aPagar = round((float) ($dadosResumo->a_pagar ?? 0), 2);

      $conta->ultimaTransacao = $dadosResumo->ultima_transacao ?? null;
      $conta->saldoInicial = $saldoInicial;
      $conta->recebimentos = $recebimentos;
      $conta->saidas = $saidas;
      $conta->aReceber = $aReceber;
      $conta->aPagar = $aPagar;
      $conta->saldoAtual = round($saldoInicial + $saldoEntradas - $saldoSaidas, 2);
      $conta->saldoProjetado = round($conta->saldoAtual + $aReceber - $aPagar, 2);
      $dashboard->getSaldoPorConta[] = $conta;

      $dashboard->resumoGeral->saldoAtual += $conta->saldoAtual;
      $dashboard->resumoGeral->saldoProjetado += $conta->saldoProjetado;
      $dashboard->resumoGeral->aReceber += $aReceber;
      $dashboard->resumoGeral->aPagar += $aPagar;
    }

    $dashboard->resumoGeral->saldoAtual = round($dashboard->resumoGeral->saldoAtual, 2);
    $dashboard->resumoGeral->saldoProjetado = round($dashboard->resumoGeral->saldoProjetado, 2);
    $dashboard->resumoGeral->aReceber = round($dashboard->resumoGeral->aReceber, 2);
    $dashboard->resumoGeral->aPagar = round($dashboard->resumoGeral->aPagar, 2);

    $central = (new TituloModel())->rawQuery(
      "SELECT
         tit.tit_id,
         tit.tit_nome,
         tit.tit_descricao,
         tit.tit_status,
         tit.tit_statusclass,
         tit.tit_statusfinanceiro,
         tit.tit_statusfinanceiroclass,
         tit.tit_natureza,
         tit.tit_pct_nome,
         GROUP_CONCAT(DISTINCT ctf.ctf_nome ORDER BY ctf.ctf_nome SEPARATOR ' | ') AS tit_ctf_nome,
         tit.tit_dtvencimento,
         tit.tit_valortotal,
         tit.tit_valormovimentado,
         COALESCE(SUM(lan.lan_valor), 0) AS tit_valor_lancado,
         COALESCE(SUM(CASE WHEN lan.lan_status IN ('Pago', 'Recebido') THEN lan.lan_valor ELSE 0 END), 0) AS tit_valor_baixado,
         COUNT(DISTINCT lan.lan_id) AS qnt_lancamento,
         COUNT(DISTINCT CASE WHEN lan.lan_status IN ('Pago', 'Recebido') THEN lan.lan_id END) AS qnt_lancamento_baixado,
         MAX(lan.lan_dtalter) AS ultima_transacao
       FROM {$tabelaTitulo} tit
       LEFT JOIN {$tabelaLancamento} lan
         ON lan.lan_idtitulo = tit.tit_id
        AND lan.lan_idorganizacao = :central_lan_organizacao
        AND lan.lan_idempresa = :central_lan_empresa
       LEFT JOIN {$tabelaConta} ctf
         ON ctf.ctf_id = lan.lan_idcontafinanceira
       WHERE tit.tit_idorganizacao = :central_tit_organizacao
         AND tit.tit_idempresa = :central_tit_empresa
         AND tit.tit_status = 'Confirmado'
         AND tit.tit_statusfinanceiro IN ('Aguardando lançamento', 'Lançamento registrado', 'Parcial')
       GROUP BY
         tit.tit_id, tit.tit_nome, tit.tit_descricao, tit.tit_status,
         tit.tit_statusclass, tit.tit_statusfinanceiro, tit.tit_statusfinanceiroclass,
         tit.tit_natureza, tit.tit_pct_nome, tit.tit_dtvencimento,
         tit.tit_valortotal, tit.tit_valormovimentado
       ORDER BY tit.tit_dtvencimento ASC, tit.tit_natureza ASC, tit.tit_id ASC",
      [
        'central_lan_organizacao' => $organizacaoId,
        'central_lan_empresa' => $empresaId,
        'central_tit_organizacao' => $organizacaoId,
        'central_tit_empresa' => $empresaId
      ],
      true,
      'Central de contas encontrada||Erro ao buscar central de contas'
    );

    if ($central->status && !empty($central->data)) {
      foreach ($central->data as $registro) {
        $registro->tit_valor_lancado = round((float) ($registro->tit_valor_lancado ?? 0), 2);
        $registro->tit_valor_baixado = round((float) ($registro->tit_valor_baixado ?? 0), 2);
        $registro->tit_valorrestante = round(max(0, (float) ($registro->tit_valortotal ?? 0) - (float) ($registro->tit_valormovimentado ?? 0)), 2);
        $registro->qnt_lancamento = (int) ($registro->qnt_lancamento ?? 0);
        $registro->qnt_lancamento_baixado = (int) ($registro->qnt_lancamento_baixado ?? 0);
        $dashboard->centralDeContas->data[] = $registro;

        if (($registro->tit_statusfinanceiro ?? '') === 'Aguardando lançamento') $dashboard->centralDeContas->resumo->qntEmAberto++;
        if (($registro->tit_statusfinanceiro ?? '') === 'Lançamento registrado') $dashboard->centralDeContas->resumo->qntAReceber++;
        if (($registro->tit_statusfinanceiro ?? '') === 'Parcial') $dashboard->centralDeContas->resumo->qntEmRecebimento++;
      }
    }

    $this->setBreadcrumb([
      [
        'title' => 'Dashboard financeiro',
        'label' => 'Financeiro',
        'url' => Helpers::url('financeiro/dashboard')
      ]
    ]);
    $this->setMetaTags([
      'title' => 'Dashboard financeiro',
      'description' => 'Visão resumida do financeiro geral.'
    ]);

    $this->render('sistema/financeiro/dashboard', [
      'dashboard' => $dashboard
    ]);
  }

  public function relatorios(): void
  {
    $this->validarPermissoes(['financeiro.visualizar', 'financeiro.relatorio']);

    $this->setBreadcrumb([
      [
        'title' => 'Financeiro',
        'label' => 'Financeiro',
        'url' => Helpers::url('financeiro/dashboard'),
        'active' => false
      ],
      [
        'title' => 'Relatórios',
        'label' => 'Relatórios',
        'url' => Helpers::url('financeiro/relatorio')
      ]
    ]);

    $this->render('sistema/financeiro/relatorio/arquivos', [
      'titulo' => 'Relatórios financeiros',
      'descricao' => 'Consulte extratos e lançamentos financeiros do contexto ativo.'
    ]);
  }

  public function relatorioDetalhe(array $url): void
  {
    $this->validarPermissoes(['financeiro.visualizar', 'financeiro.relatorio']);

    $tipo = (string) ($url['tipo'] ?? '');
    $configuracoes = [
      'extratoMovimentacao' => ['titulo' => 'Extrato de movimentação', 'descricao' => 'Saldo anterior, movimentações baixadas e saldo final do período.'],
      'lancamentosAnalitico' => ['titulo' => 'Lançamentos analíticos', 'descricao' => 'Consulta detalhada dos lançamentos financeiros e seus respectivos status.']
    ];

    if (!isset($configuracoes[$tipo])) {
      $this->render('template/sistema/erro', ['erro' => (object) ['status' => false, 'title' => 'Relatório não encontrado', 'text' => 'O relatório informado não está disponível.']]);
      return;
    }

    $dados = $this->consultarRelatorioFinanceiro($tipo);
    $this->setBreadcrumb([
      ['title' => 'Financeiro', 'label' => 'Financeiro', 'url' => Helpers::url('financeiro/dashboard'), 'active' => false],
      ['title' => 'Relatórios', 'label' => 'Relatórios', 'url' => Helpers::url('financeiro/relatorio'), 'active' => false],
      ['title' => $configuracoes[$tipo]['titulo'], 'label' => $configuracoes[$tipo]['titulo'], 'url' => Helpers::url("financeiro/relatorio/{$tipo}")]
    ]);

    $this->render('sistema/financeiro/relatorio/form', $dados + [
      'tipoRelatorio' => $tipo,
      'titulo' => $configuracoes[$tipo]['titulo'],
      'descricao' => $configuracoes[$tipo]['descricao'],
      'podeAnexarLancamento' => Helpers::validarPermissao('financeiro.lancamento.atualizar')->status,
      'podeTrocarAnexoLancamento' => Helpers::validarPermissao('financeiro.lancamento.atualizar')->status
    ]);
  }

  private function consultarRelatorioFinanceiro(string $tipo): array
  {
    $normalizarIds = static function ($valor): array {
      $itens = is_array($valor) ? $valor : [];
      return array_values(array_unique(array_filter(array_map('intval', $itens), static fn(int $id): bool => $id > 0)));
    };
    $normalizarTextos = static function ($valor, array $permitidos): array {
      $itens = is_array($valor) ? $valor : [];
      return array_values(array_unique(array_filter(array_map(static fn($item): string => trim((string) $item), $itens), static fn(string $item): bool => in_array($item, $permitidos, true))));
    };
    $dataValida = static function ($valor): string {
      $valor = trim((string) $valor);
      $data = \DateTime::createFromFormat('!Y-m-d', $valor);
      return $data && $data->format('Y-m-d') === $valor ? $valor : '';
    };

    $campoData = (string) ($_GET['campoData'] ?? ($tipo === 'extratoMovimentacao' ? 'lan_dtbaixa' : 'lan_dtvencimento'));
    $camposData = [
      'lan_dtvencimento' => 'DATE(lan.lan_dtvencimento)',
      'lan_dtbaixa' => 'DATE(lan.lan_dtbaixa)',
      'lan_dtcad' => 'DATE(lan.lan_dtcad)',
      'tit_referencia' => 'DATE(tit.tit_referencia)',
      'tit_dtvencimento' => 'DATE(tit.tit_dtvencimento)'
    ];
    if (!isset($camposData[$campoData])) $campoData = $tipo === 'extratoMovimentacao' ? 'lan_dtbaixa' : 'lan_dtvencimento';
    $tipoFiltro = (string) ($_GET['tipoFiltro'] ?? 'Periodo');
    if (!in_array($tipoFiltro, ['Exatidao', 'Periodo'], true)) $tipoFiltro = 'Periodo';
    $inicio = $dataValida($_GET['iniFiltro'] ?? '');
    $fim = $dataValida($_GET['fimFiltro'] ?? '');
    if ($inicio !== '' && $fim !== '' && $inicio > $fim) [$inicio, $fim] = [$fim, $inicio];
    $filtros = [
      'idcentrocusto' => $normalizarIds($_GET['idcentrocusto'] ?? []),
      'idplanoconta' => $normalizarIds($_GET['idplanoconta'] ?? []),
      'idcontafinanceira' => $normalizarIds($_GET['idcontafinanceira'] ?? []),
      'natureza' => $normalizarTextos($_GET['natureza'] ?? [], ['Entrada', 'Saida']),
      'status' => $normalizarTextos($_GET['status'] ?? [], ['A pagar', 'A receber', 'Pago', 'Recebido', 'Estornado']),
      'busca' => trim((string) ($_GET['busca'] ?? ''))
    ];
    $limite = trim((string) ($_GET['limit'] ?? ($tipo === 'extratoMovimentacao' ? '5000' : '1000')));
    $limitesPermitidos = ['50', '100', '150', '250', '500', '1000', '1500', '2000', '2500', '5000', '10000', 'sem_limite'];
    if (!in_array($limite, $limitesPermitidos, true)) $limite = $tipo === 'extratoMovimentacao' ? '5000' : '1000';
    $sqlLimit = $limite === 'sem_limite' ? '' : ' LIMIT ' . (int) $limite;
    $ordem = (string) ($_GET['op'] ?? ($tipo === 'extratoMovimentacao' ? 'ordem_extrato' : 'padrao'));
    $direcao = strtolower((string) ($_GET['df'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
    $ordenacoes = [
      'ordem_extrato' => 'lan.lan_dtbaixa',
      'padrao' => 'COALESCE(lan.lan_dtvencimento, lan.lan_dtcad)',
      'numero_identificacao' => 'lan.lan_id',
      'mais_recentes' => 'COALESCE(lan.lan_dtbaixa, lan.lan_dtvencimento, lan.lan_dtcad)',
      'maior_valor' => 'lan.lan_valor',
      'menor_valor' => 'lan.lan_valor',
      'conta_financeira' => 'ctf.ctf_nome',
      'plano_conta' => 'pct.pct_nome',
      'centro_custo' => 'cdc.cdc_nome',
      'natureza' => 'lan.lan_natureza',
      'status_lancamento' => 'lan.lan_status',
      'titulo_financeiro' => 'tit.tit_nome'
    ];
    if (!isset($ordenacoes[$ordem])) $ordem = $tipo === 'extratoMovimentacao' ? 'ordem_extrato' : 'padrao';
    if (in_array($ordem, ['mais_recentes', 'maior_valor'], true)) $direcao = 'DESC';
    if ($ordem === 'menor_valor') $direcao = 'ASC';
    $campoDataSql = $camposData[$campoData];
    $tabelaLancamento = DB_PREFIX . '_lancamento';
    $tabelaTitulo = DB_PREFIX . '_titulo';
    $tabelaConta = DB_PREFIX . '_conta_financeira';
    $tabelaCentro = DB_PREFIX . '_centro_custo';
    $tabelaPlano = DB_PREFIX . '_plano_conta';
    $tabelaForma = DB_PREFIX . '_forma_pagamento';
    $tabelaCondicao = DB_PREFIX . '_condicao_pgto';
    $tabelaArquivo = DB_PREFIX . '_arquivo';
    $params = ['org_id' => (int) ($this->contexto->organizacao->org_id ?? 0), 'emp_id' => (int) ($this->contexto->empresa->emp_id ?? 0)];
    $where = ['lan.lan_idorganizacao = :org_id', 'lan.lan_idempresa = :emp_id'];
    if ($tipo === 'extratoMovimentacao') $where[] = "lan.lan_status IN ('Pago', 'Recebido')";
    foreach (['idcentrocusto' => 'lan.lan_idcentrocusto', 'idplanoconta' => 'lan.lan_idplanoconta', 'idcontafinanceira' => 'lan.lan_idcontafinanceira'] as $chave => $campo) {
      if (!$filtros[$chave]) continue;
      $nomes = [];
      foreach ($filtros[$chave] as $indice => $valor) { $nome = "{$chave}_{$indice}"; $nomes[] = ':' . $nome; $params[$nome] = $valor; }
      $where[] = "{$campo} IN (" . implode(', ', $nomes) . ')';
    }
    foreach (['natureza' => 'lan.lan_natureza', 'status' => 'lan.lan_status'] as $chave => $campo) {
      if (!$filtros[$chave]) continue;
      $nomes = [];
      foreach ($filtros[$chave] as $indice => $valor) { $nome = "{$chave}_{$indice}"; $nomes[] = ':' . $nome; $params[$nome] = $valor; }
      $where[] = "{$campo} IN (" . implode(', ', $nomes) . ')';
    }
    if ($filtros['busca'] !== '') { $where[] = '(lan.lan_descricao LIKE :busca_descricao OR lan.lan_observacao LIKE :busca_observacao OR tit.tit_nome LIKE :busca_titulo)'; $params['busca_descricao'] = '%' . $filtros['busca'] . '%'; $params['busca_observacao'] = '%' . $filtros['busca'] . '%'; $params['busca_titulo'] = '%' . $filtros['busca'] . '%'; }
    if ($tipoFiltro === 'Exatidao' && $inicio !== '') { $where[] = "{$campoDataSql} = :data_exata"; $params['data_exata'] = $inicio; }
    if ($tipoFiltro === 'Periodo') {
      if ($inicio !== '') { $where[] = "{$campoDataSql} >= :data_inicio"; $params['data_inicio'] = $inicio; }
      if ($fim !== '') { $where[] = "{$campoDataSql} <= :data_fim"; $params['data_fim'] = $fim; }
    }
    $sqlWhere = implode(' AND ', $where);
    $modelo = new LancamentoModel();
    $resultado = $modelo->rawQuery(
      "SELECT lan.*, ctf.ctf_nome AS lan_ctf_nome, cdc.cdc_nome AS lan_cdc_nome, pct.pct_nome AS lan_pct_nome, fpg.fpg_nome AS lan_fpg_nome, cpg.cpg_nome AS lan_cpg_nome, tit.tit_nome, tit.tit_referencia, tit.tit_dtvencimento AS tit_dtvencimento_titulo, arq.arq_id, arq.arq_nome, arq.arq_nomeoriginal, arq.arq_descricao, arq.arq_mime, arq.arq_extensao, arq.arq_tamanho, arq.arq_medida, arq.arq_largura, arq.arq_altura, arq.arq_caminho, arq.arq_dtcad
       FROM {$tabelaLancamento} lan
       LEFT JOIN {$tabelaTitulo} tit ON tit.tit_id = lan.lan_idtitulo
       LEFT JOIN {$tabelaConta} ctf ON ctf.ctf_id = lan.lan_idcontafinanceira
       LEFT JOIN {$tabelaCentro} cdc ON cdc.cdc_id = lan.lan_idcentrocusto
       LEFT JOIN {$tabelaPlano} pct ON pct.pct_id = lan.lan_idplanoconta
       LEFT JOIN {$tabelaForma} fpg ON fpg.fpg_id = lan.lan_idformapagamento
       LEFT JOIN {$tabelaCondicao} cpg ON cpg.cpg_id = lan.lan_idcondicaopgto
       LEFT JOIN {$tabelaArquivo} arq ON arq.arq_idorigem = lan.lan_id AND arq.arq_origem = 'lancamento' AND arq.arq_status = 'Ativo'
       WHERE {$sqlWhere}
       ORDER BY {$ordenacoes[$ordem]} {$direcao}, lan.lan_id {$direcao}{$sqlLimit}",
      $params, true, 'Relatório encontrado||Erro ao consultar relatório'
    );
    $linhas = $resultado->status ? ($resultado->data ?? []) : [];
    $totais = $modelo->rawQuery(
      "SELECT COUNT(lan.lan_id) AS quantidade,
              COALESCE(SUM(CASE WHEN lan.lan_natureza = 'Entrada' THEN lan.lan_valor ELSE 0 END), 0) AS entradas,
              COALESCE(SUM(CASE WHEN lan.lan_natureza = 'Saida' THEN lan.lan_valor ELSE 0 END), 0) AS saidas
       FROM {$tabelaLancamento} lan
       LEFT JOIN {$tabelaTitulo} tit ON tit.tit_id = lan.lan_idtitulo
       WHERE {$sqlWhere}",
      $params, true, 'Totais do relatório encontrados||Erro ao consultar totais do relatório'
    );
    $dadosTotais = $totais->status ? ($totais->data[0] ?? null) : null;
    $resumo = (object) [
      'quantidade' => (int) ($dadosTotais->quantidade ?? count($linhas)),
      'quantidadeExibida' => count($linhas),
      'entradas' => (float) ($dadosTotais->entradas ?? 0),
      'saidas' => (float) ($dadosTotais->saidas ?? 0),
      'saldo' => 0.0,
      'saldoAnterior' => 0.0,
      'saldoFinal' => 0.0
    ];
    if (!$dadosTotais) foreach ($linhas as $linha) { $valor = (float) ($linha->lan_valor ?? 0); if (($linha->lan_natureza ?? '') === 'Entrada') $resumo->entradas += $valor; else $resumo->saidas += $valor; }
    $resumo->saldo = round($resumo->entradas - $resumo->saidas, 2);
    $resumoAnalitico = (object) [
      'quantidade' => 0,
      'entradas' => 0.0,
      'saidas' => 0.0,
      'saldo' => 0.0
    ];
    $resumoPorConta = [];
    if ($tipo !== 'extratoMovimentacao') {
      foreach ($linhas as $linha) {
        $valorLinha = round((float) ($linha->lan_valor ?? 0), 2);
        $entradaLinha = ($linha->lan_natureza ?? '') === 'Entrada';
        $contaId = (int) ($linha->lan_idcontafinanceira ?? 0);
        $contaNome = trim((string) ($linha->lan_ctf_nome ?? '')) ?: 'Sem conta';
        if (!isset($resumoPorConta[$contaId])) {
          $resumoPorConta[$contaId] = (object) [
            'lan_ctf_nome' => $contaNome,
            'qtd_lancamento' => 0,
            'entradas' => 0.0,
            'saidas' => 0.0,
            'saldo' => 0.0
          ];
        }
        $resumoAnalitico->quantidade++;
        $resumoPorConta[$contaId]->qtd_lancamento++;
        if ($entradaLinha) {
          $resumoAnalitico->entradas += $valorLinha;
          $resumoPorConta[$contaId]->entradas += $valorLinha;
        } else {
          $resumoAnalitico->saidas += $valorLinha;
          $resumoPorConta[$contaId]->saidas += $valorLinha;
        }
      }
      $resumoAnalitico->entradas = round($resumoAnalitico->entradas, 2);
      $resumoAnalitico->saidas = round($resumoAnalitico->saidas, 2);
      $resumoAnalitico->saldo = round($resumoAnalitico->entradas - $resumoAnalitico->saidas, 2);
      foreach ($resumoPorConta as $item) {
        $item->entradas = round($item->entradas, 2);
        $item->saidas = round($item->saidas, 2);
        $item->saldo = round($item->entradas - $item->saidas, 2);
      }
      usort($resumoPorConta, static fn($a, $b): int => strcmp((string) $a->lan_ctf_nome, (string) $b->lan_ctf_nome));
    }
    if ($tipo === 'extratoMovimentacao') {
      $contaParams = ['saldo_org' => (int) ($this->contexto->organizacao->org_id ?? 0), 'saldo_emp' => (int) ($this->contexto->empresa->emp_id ?? 0)];
      $contaWhere = ['ctf_idorganizacao = :saldo_org', 'ctf_idempresa = :saldo_emp'];
      if ($filtros['idcontafinanceira']) { $nomes = []; foreach ($filtros['idcontafinanceira'] as $indice => $valor) { $nome = "saldo_conta_{$indice}"; $nomes[] = ':' . $nome; $contaParams[$nome] = $valor; } $contaWhere[] = 'ctf_id IN (' . implode(', ', $nomes) . ')'; }
      $saldoInicial = $modelo->rawQuery("SELECT COALESCE(SUM(ctf_saldo_inicial), 0) AS saldo FROM {$tabelaConta} WHERE " . implode(' AND ', $contaWhere), $contaParams, true, 'Saldo inicial encontrado||Erro ao consultar saldo inicial');
      $resumo->saldoAnterior = (float) ($saldoInicial->data[0]->saldo ?? 0);
      if ($inicio !== '') {
        $whereAnterior = ["lan.lan_idorganizacao = :anterior_org", "lan.lan_idempresa = :anterior_emp", "lan.lan_status IN ('Pago', 'Recebido')", 'lan.lan_dtbaixa < :anterior_data'];
        $paramsAnterior = ['anterior_org' => (int) ($this->contexto->organizacao->org_id ?? 0), 'anterior_emp' => (int) ($this->contexto->empresa->emp_id ?? 0), 'anterior_data' => $inicio];
        if ($filtros['idcontafinanceira']) {
          $nomes = [];
          foreach ($filtros['idcontafinanceira'] as $indice => $valor) { $nome = "anterior_conta_{$indice}"; $nomes[] = ':' . $nome; $paramsAnterior[$nome] = $valor; }
          $whereAnterior[] = 'lan.lan_idcontafinanceira IN (' . implode(', ', $nomes) . ')';
        }
        $anteriores = $modelo->rawQuery("SELECT COALESCE(SUM(CASE WHEN lan_natureza = 'Entrada' THEN lan_valor ELSE -lan_valor END), 0) AS saldo FROM {$tabelaLancamento} lan WHERE " . implode(' AND ', $whereAnterior), $paramsAnterior, true, 'Saldo anterior encontrado||Erro ao consultar saldo anterior');
        $resumo->saldoAnterior += (float) ($anteriores->data[0]->saldo ?? 0);
      }
      $resumo->saldoAnterior = round($resumo->saldoAnterior, 2);
      $resumo->saldoFinal = round($resumo->saldoAnterior + $resumo->saldo, 2);
      $acumulado = $resumo->saldoAnterior;
      foreach ($linhas as $linha) { $acumulado += ($linha->lan_natureza ?? '') === 'Entrada' ? (float) $linha->lan_valor : -(float) $linha->lan_valor; $linha->saldo_acumulado = round($acumulado, 2); }
    }
    $buscarAtivos = static fn(string $cadastro): array => (new FinanceiroBasicoModel($cadastro))->getRegistrosContexto()->data ?? [];
    return ['linhas' => $linhas, 'resumo' => $resumo, 'resumoAnalitico' => $resumoAnalitico, 'resumoPorConta' => $resumoPorConta, 'filtros' => (object) ($filtros + ['tipoFiltro' => $tipoFiltro, 'campoData' => $campoData, 'inicio' => $inicio, 'fim' => $fim, 'limit' => $limite, 'op' => $ordem, 'df' => strtolower($direcao)]), 'centrosCusto' => $buscarAtivos('centro_custo'), 'planosConta' => $buscarAtivos('plano_conta'), 'contasFinanceiras' => $buscarAtivos('conta_financeira')];
  }

  public function transferenciaList(): void
  {
    $this->validarPermissoes(['financeiro.visualizar', 'financeiro.transferencia.visualizar']);

    $modelo = new TransferenciaFinanceiraModel();
    $this->setBreadcrumb([
      [
        'title' => 'Financeiro',
        'label' => 'Financeiro',
        'url' => Helpers::url('financeiro/dashboard'),
        'active' => false
      ],
      [
        'title' => 'Transferências',
        'label' => 'Transferências',
        'url' => Helpers::url('financeiro/transferencia/list')
      ]
    ]);

    $this->render('sistema/financeiro/transferencia/list', [
      'getInfo' => (object) ['status' => true, 'data' => [], 'total' => $modelo->contarTransferencias()],
      'titulo' => 'Transferências financeiras',
      'descricao' => 'Acompanhe as movimentações entre contas sem alterar receitas ou despesas.',
      'urlCadastrar' => Helpers::pode('financeiro.transferencia.criar') ? Helpers::url('financeiro/transferencia/form') : '',
      'ocultarCadastrar' => !Helpers::pode('financeiro.transferencia.criar'),
      'urlPesquisar' => Helpers::url('financeiro/pesquisarTransferencia'),
      'labelPesquisaSimples' => 'Pesquisar por conta, observação ou número',
      'labelTotal' => 'transferência(s)',
      'nomeArquivo' => 'transferencias',
      'prefixo' => 'trf'
    ]);
  }

  public function transferenciaForm($url = null): void
  {
    $id = is_array($url) ? (int) ($url['id'] ?? 0) : (int) $url;
    $modelo = new TransferenciaFinanceiraModel();
    $transferencia = null;

    if ($id > 0) {
      $this->validarPermissoes(['financeiro.visualizar', 'financeiro.transferencia.visualizar']);
      $resultado = $modelo->getTransferenciaById($id);
      if (!$resultado->status || $resultado->total !== 1) {
        $this->render('template/sistema/erro', [
          'erro' => (object) [
            'status' => false,
            'title' => 'Transferência não encontrada',
            'text' => 'A transferência informada não foi localizada ou não pertence ao contexto atual.'
          ]
        ]);
        return;
      }
      $transferencia = $resultado->data[0];
    } else {
      $this->validarPermissoes(['financeiro.visualizar', 'financeiro.transferencia.criar']);
    }

    $contas = (new FinanceiroBasicoModel('conta_financeira'))->getRegistrosContexto();
    $contasAtivas = $contas->status
      ? array_values(array_filter($contas->data ?? [], static fn($conta): bool => ($conta->ctf_status ?? '') === 'Ativo'))
      : [];

    $anexoTransferencia = null;
    if ($id > 0) {
      $arquivos = (new ArquivoModel())->getArquivosByConfig((object) [
        'idorganizacao' => (int) ($this->contexto->organizacao->org_id ?? 0),
        'idempresa' => (int) ($this->contexto->empresa->emp_id ?? 0),
        'idorigem' => $id,
        'origem' => 'transferencia'
      ]);
      if ($arquivos->status && $arquivos->total > 0) {
        $anexoTransferencia = $arquivos->data[0] ?? null;
      }
    }

    $this->setBreadcrumb([
      [
        'title' => 'Financeiro',
        'label' => 'Financeiro',
        'url' => Helpers::url('financeiro/dashboard'),
        'active' => false
      ],
      [
        'title' => 'Transferências',
        'label' => 'Transferências',
        'url' => Helpers::url('financeiro/transferencia/list'),
        'active' => false
      ],
      [
        'title' => $id > 0 ? "Transferência #{$id}" : 'Nova transferência',
        'label' => $id > 0 ? "Transferência #{$id}" : 'Nova transferência',
        'url' => '#'
      ]
    ]);

    $this->render('sistema/financeiro/transferencia/form', [
      'id' => $id,
      'transferencia' => $transferencia,
      'contasAtivas' => $contasAtivas,
      'anexoTransferencia' => $anexoTransferencia,
      'podeGerenciarAnexoTransferencia' => Helpers::pode('financeiro.transferencia.criar')
        && (!$transferencia || ($transferencia->lan_status ?? '') !== 'Estornado'),
      'podeEstornar' => $id > 0
        && ($transferencia->lan_status ?? '') === 'Pago'
        && ($transferencia->trf_status_destino ?? '') === 'Recebido'
        && Helpers::pode('financeiro.transferencia.cancelar')
    ]);
  }

  public function pesquisarTransferencia(): void
  {
    $this->validarPermissoes(['financeiro.visualizar', 'financeiro.transferencia.visualizar']);
    (new TransferenciaFinanceiraModel())->pesquisarTransferencias($_POST)->json();
    exit;
  }

  public function salvarTransferencia(): void
  {
    $this->validarPermissoes(['financeiro.visualizar', 'financeiro.transferencia.criar']);
    (new TransferenciaFinanceiraModel())->registrarTransferencia($_POST)->json();
    exit;
  }

  public function saldoDisponivelTransferencia(): void
  {
    $this->validarPermissoes(['financeiro.visualizar', 'financeiro.transferencia.criar']);
    (new TransferenciaFinanceiraModel())->consultarSaldoDisponivel(
      (int) ($_POST['trf_idconta'] ?? $_POST['trf_idcontaorigem'] ?? 0),
      (string) ($_POST['trf_data'] ?? ''),
      (string) ($_POST['trf_tipoConta'] ?? 'origem') === 'origem'
    )->json();
    exit;
  }

  public function estornarTransferencia(): void
  {
    $this->validarPermissoes(['financeiro.visualizar', 'financeiro.transferencia.cancelar']);
    (new TransferenciaFinanceiraModel())->estornarTransferencia(
      (int) ($_POST['trf_id'] ?? 0),
      (string) ($_POST['trf_motivo'] ?? '')
    )->json();
    exit;
  }

  public function basico(): void
  {
    $this->validarPermissoes(['financeiro.visualizar', 'financeiro.basico.visualizar']);

    $this->setBreadcrumb([
      [
        'title' => 'Financeiro',
        'label' => 'Financeiro',
        'url' => Helpers::url('financeiro/dashboard'),
        'active' => false
      ],
      [
        'title' => 'Básico',
        'label' => 'Básico',
        'url' => Helpers::url('financeiro/basico')
      ]
    ]);

    $this->render('sistema/financeiro/basico/list', [
      'titulo' => 'Cadastros financeiros básicos',
      'descricao' => 'Configure contas, formas de pagamento, condições, plano de contas e centros de custo.',
      'cadastros' => FinanceiroBasicoModel::getCadastrosComTotais()
    ]);
  }

  public function basicoForm(array $url): void
  {
    $modulo = (string) ($url['modulo'] ?? '');
    $config = FinanceiroBasicoModel::getCadastro($modulo);

    if (is_null($config)) {
      $this->render('template/sistema/erro', [
        'erro' => (object) [
          'status' => false,
          'title' => 'Cadastro financeiro inválido',
          'text' => 'O cadastro financeiro informado não existe ou ainda não foi configurado.'
        ]
      ]);
      return;
    }

    $model = new FinanceiroBasicoModel($modulo);
    $idRota = $url['id'] ?? null;
    $ehNovo = $idRota === 'novo';
    $idRegistro = is_numeric($idRota) ? (int) $idRota : 0;
    if (!is_null($idRota) && !$ehNovo && $idRegistro <= 0) {
      $this->session->set('FLASH_FINANCEIRO_BASICO_ERRO', [
        'titulo' => 'Registro não encontrado',
        'mensagem' => 'O identificador informado é inválido.',
        'cadastro' => $modulo
      ]);
      header('Location: ' . Helpers::url("financeiro/basico/{$modulo}"), true, 302);
      exit;
    }
    $formularioEmRota = $ehNovo || $idRegistro > 0;
    $this->validarPermissoes(['financeiro.visualizar', 'financeiro.basico.visualizar']);
    if ($ehNovo) {
      $this->validarPermissoes(['financeiro.basico.criar']);
    } elseif ($idRegistro > 0) {
      $this->validarPermissoes(['financeiro.basico.atualizar']);
    }

    $registroAtual = null;
    $registros = [];
    if ($idRegistro > 0) {
      $getRegistro = $model->getRegistroByIdContexto($idRegistro);
      if (!$getRegistro->status || empty($getRegistro->data[0])) {
        $this->session->set('FLASH_FINANCEIRO_BASICO_ERRO', [
          'titulo' => 'Registro não encontrado',
          'mensagem' => 'O registro solicitado não existe ou não pertence ao contexto financeiro ativo.',
          'cadastro' => $modulo
        ]);
        header('Location: ' . Helpers::url("financeiro/basico/{$modulo}"), true, 302);
        exit;
      }
      $registroAtual = $getRegistro->data[0];
      if (!$model->podeEditarRegistro($registroAtual)) {
        $this->render('template/sistema/erro', ['erro' => (object) [
          'status' => false,
          'title' => 'Acesso negado',
          'text' => 'Você não possui permissão para editar este cadastro no escopo financeiro atual.'
        ]]);
        return;
      }
      $registros = [$registroAtual];
    } elseif (!$formularioEmRota) {
      $getRegistros = $model->getRegistrosContexto();
      $registros = $getRegistros->status ? ($getRegistros->data ?? []) : [];
      foreach ($registros as $registro) {
        $registro->pode_editar_escopo = $model->podeEditarRegistro($registro);
      }
    }

    $alertaFlash = null;
    if (!$formularioEmRota) {
      $alertaFlash = $this->session->get('FLASH_FINANCEIRO_BASICO_ERRO');
      if (($alertaFlash['cadastro'] ?? null) === $modulo) {
        $this->session->remove('FLASH_FINANCEIRO_BASICO_ERRO');
      } else {
        $alertaFlash = null;
      }
    }

    $this->setBreadcrumb([
      [
        'title' => 'Financeiro',
        'label' => 'Financeiro',
        'url' => Helpers::url('financeiro/dashboard'),
        'active' => false
      ],
      [
        'title' => 'Básico',
        'label' => 'Básico',
        'url' => Helpers::url('financeiro/basico'),
        'active' => false
      ],
      [
        'title' => $formularioEmRota ? ($idRegistro > 0 ? 'Editar' : 'Novo') : $config->titulo,
        'label' => $formularioEmRota ? ($idRegistro > 0 ? 'Editar' : 'Novo') : $config->titulo,
        'url' => '#'
      ]
    ]);

    $urlLista = Helpers::url("financeiro/basico/{$modulo}");
    $this->render("sistema/financeiro/basico/{$modulo}", [
      'modulo' => $modulo,
      'config' => $config,
      'registros' => $registros,
      'registroAtual' => $registroAtual,
      'registroAtualId' => $idRegistro,
      'formularioEmRota' => $formularioEmRota,
      'urlListaCadastro' => $urlLista,
      'urlNovoCadastro' => $urlLista . '/novo',
      'urlEditarCadastroBase' => $urlLista,
      'urlVoltarCadastro' => Helpers::url('financeiro/basico'),
      'urlSalvarCadastro' => Helpers::url('financeiro/salvarBasico'),
      'alertaFlash' => $alertaFlash,
      'escoposFinanceiros' => $model->getEscoposFinanceirosPermitidos($registroAtual),
      'opcoes' => $this->getOpcoesBasico($modulo)
    ]);
  }

  public function salvarBasico(): void
  {
    $modulo = (string) ($_POST['cadastro'] ?? '');
    $config = FinanceiroBasicoModel::getCadastro($modulo);

    if (is_null($config)) {
      echo json_encode((object) [
        'status' => false,
        'title' => 'Erro',
        'text' => 'Cadastro financeiro inválido.',
        'icon' => 'error'
      ]);
      exit;
    }

    $id = (int) ($_POST["{$config->prefixo}_id"] ?? 0);
    $permissoes = [
      'financeiro.visualizar',
      $id > 0 ? 'financeiro.basico.atualizar' : 'financeiro.basico.criar'
    ];
    $this->validarPermissoes($permissoes, '&&');

    $model = new FinanceiroBasicoModel($modulo);
    if ($id > 0) {
      $atual = $model->getRegistroByIdContexto($id);
      if (!$atual->status || empty($atual->data[0]) || !$model->podeEditarRegistro($atual->data[0])) {
        echo json_encode((object) ['status' => false, 'title' => 'Atenção', 'text' => 'Registro fora do contexto financeiro ativo.', 'icon' => 'error']);
        exit;
      }
      $statusEnviado = (string) ($_POST["{$config->prefixo}_status"] ?? ($atual->data[0]->{"{$config->prefixo}_status"} ?? 'Ativo'));
      if ($statusEnviado !== (string) ($atual->data[0]->{"{$config->prefixo}_status"} ?? 'Ativo')) {
        echo json_encode((object) ['status' => false, 'title' => 'Ação necessária', 'text' => 'Altere o status pela ação de status da listagem para que a justificativa seja registrada.', 'icon' => 'warning']);
        exit;
      }
    } else {
      $_POST["{$config->prefixo}_status"] = 'Ativo';
    }

    $response = $model->salvarFinanceiroBasico($_POST);

    echo json_encode($response);
    exit;
  }

  public function alterarStatusBasico(): void
  {
    $modulo = (string) ($_POST['cadastro'] ?? '');
    $config = FinanceiroBasicoModel::getCadastro($modulo);
    if (!$config) {
      echo json_encode((object) ['status' => false, 'title' => 'Atenção', 'text' => 'Cadastro financeiro inválido.', 'icon' => 'error']);
      exit;
    }
    $id = (int) ($_POST["{$config->prefixo}_id"] ?? 0);
    $status = (string) ($_POST["{$config->prefixo}_status"] ?? '');
    $permissoes = ['financeiro.visualizar', 'financeiro.basico.atualizar'];
    $this->validarPermissoes($permissoes, '&&');

    $response = (new FinanceiroBasicoModel($modulo))->alterarStatusFinanceiroBasico(
      $id,
      $status,
      (string) ($_POST['motivo'] ?? '')
    );
    echo json_encode($response);
    exit;
  }

  private function getOpcoesBasico(string $modulo): array
  {
    $opcoes = [];

    if ($modulo === 'condicao_pgto') {
      $opcoes['forma_pagamento'] = (new FinanceiroBasicoModel('forma_pagamento'))->getOpcoesAtivas();
    }

    if ($modulo === 'plano_conta') {
      $opcoes['plano_conta'] = (new FinanceiroBasicoModel('plano_conta'))->getOpcoesPaisAtivas();
    }

    return $opcoes;
  }

}
