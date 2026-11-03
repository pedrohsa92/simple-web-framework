<?php

namespace Source\Model;

use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;

class LancamentoModel extends SimpleModel
{
  protected string $tabela = 'lancamento';
  protected string $prefixo = 'lan';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'empresa';
  protected string $campoOrganizacao = 'lan_idorganizacao';
  protected string $campoEmpresa = 'lan_idempresa';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getPorTitulo(int $tituloId): object
  {
    if ($tituloId <= 0) {
      return (new SimpleResponse('Atenção', 'Título inválido.'))->warning()->noDestroy();
    }

    return $this->rawQuery(
      "SELECT
         lan.*,
         ctf.ctf_nome AS lan_ctf_nome,
         cdc.cdc_nome AS lan_cdc_nome,
         pct.pct_nome AS lan_pct_nome,
         fpg.fpg_nome AS lan_fpg_nome,
         cpg.cpg_nome AS lan_cpg_nome,
         cpg.cpg_qtdparcela AS lan_cpg_qtdparcela
       FROM {$this->getTable()} lan
       LEFT JOIN " . DB_PREFIX . "_conta_financeira ctf ON ctf.ctf_id = lan.lan_idcontafinanceira
       LEFT JOIN " . DB_PREFIX . "_centro_custo cdc ON cdc.cdc_id = lan.lan_idcentrocusto
       LEFT JOIN " . DB_PREFIX . "_plano_conta pct ON pct.pct_id = lan.lan_idplanoconta
       LEFT JOIN " . DB_PREFIX . "_forma_pagamento fpg ON fpg.fpg_id = lan.lan_idformapagamento
       LEFT JOIN " . DB_PREFIX . "_condicao_pgto cpg ON cpg.cpg_id = lan.lan_idcondicaopgto
       WHERE lan.lan_idtitulo = :lan_idtitulo
         AND lan.lan_idorganizacao = :lan_idorganizacao
         AND lan.lan_idempresa = :lan_idempresa
       ORDER BY lan.lan_dtvencimento ASC, lan.lan_id DESC",
      [
        'lan_idtitulo' => $tituloId,
        'lan_idorganizacao' => $this->contextoOrganizacaoId(),
        'lan_idempresa' => $this->contextoEmpresaId()
      ],
      true,
      'Lançamentos encontrados com sucesso||Erro ao buscar lançamentos'
    );
  }

  public function getDetalhadoPorId(int $lanId): object
  {
    if ($lanId <= 0) {
      return (new SimpleResponse('Atenção', 'Lançamento inválido.'))->warning()->noDestroy();
    }

    return $this->rawQuery(
      "SELECT
         lan.*,
         ctf.ctf_nome AS lan_ctf_nome,
         cdc.cdc_nome AS lan_cdc_nome,
         pct.pct_nome AS lan_pct_nome,
         fpg.fpg_nome AS lan_fpg_nome,
         cpg.cpg_nome AS lan_cpg_nome,
         cpg.cpg_qtdparcela AS lan_cpg_qtdparcela
       FROM {$this->getTable()} lan
       LEFT JOIN " . DB_PREFIX . "_conta_financeira ctf ON ctf.ctf_id = lan.lan_idcontafinanceira
       LEFT JOIN " . DB_PREFIX . "_centro_custo cdc ON cdc.cdc_id = lan.lan_idcentrocusto
       LEFT JOIN " . DB_PREFIX . "_plano_conta pct ON pct.pct_id = lan.lan_idplanoconta
       LEFT JOIN " . DB_PREFIX . "_forma_pagamento fpg ON fpg.fpg_id = lan.lan_idformapagamento
       LEFT JOIN " . DB_PREFIX . "_condicao_pgto cpg ON cpg.cpg_id = lan.lan_idcondicaopgto
       WHERE lan.lan_id = :lan_id
         AND lan.lan_idorganizacao = :lan_idorganizacao
         AND lan.lan_idempresa = :lan_idempresa
       LIMIT 1",
      [
        'lan_id' => $lanId,
        'lan_idorganizacao' => $this->contextoOrganizacaoId(),
        'lan_idempresa' => $this->contextoEmpresaId()
      ],
      true,
      'Lançamento encontrado com sucesso||Lançamento não encontrado'
    );
  }

  public function gerarPorTitulo(int $tituloId, array $lancamentos, bool $gerenciarTransacao = true): object
  {
    if ($tituloId <= 0 || empty($lancamentos)) {
      return $this->atencao('Informe ao menos um lançamento válido.');
    }

    $titulo = (new TituloModel())->getTituloById($tituloId);
    if (!$titulo->status || $titulo->total !== 1) {
      return $this->atencao('Título não encontrado ou fora do contexto atual.');
    }

    $registroTitulo = $titulo->data[0];
    if (($registroTitulo->tit_status ?? '') !== 'Confirmado') {
      return $this->atencao('Somente títulos confirmados podem gerar lançamentos.');
    }

    $existentes = $this->rawQuery(
      "SELECT lan_id FROM {$this->getTable()}
       WHERE lan_idtitulo = :lan_idtitulo
         AND lan_idorganizacao = :lan_idorganizacao
         AND lan_idempresa = :lan_idempresa
       LIMIT 1",
      [
        'lan_idtitulo' => $tituloId,
        'lan_idorganizacao' => $this->contextoOrganizacaoId(),
        'lan_idempresa' => $this->contextoEmpresaId()
      ],
      true,
      'Lançamentos consultados com sucesso||Erro ao consultar lançamentos'
    );
    if ($existentes->status && $existentes->total > 0) {
      return $this->atencao('Este título já possui lançamentos gerados.');
    }

    $iniciouTransacao = $gerenciarTransacao && !$this->pdoInTransaction();
    if ($iniciouTransacao) {
      $this->beginTransaction();
    }
    try {
      foreach ($lancamentos as $indice => $lan) {
        $dados = is_object($lan) ? get_object_vars($lan) : (array) $lan;
        $contaId = (int) ($dados['lan_idcontafinanceira'] ?? 0);
        $centroId = (int) ($dados['lan_idcentrocusto'] ?? 0);
        $formaId = (int) ($dados['lan_idformapagamento'] ?? 0);
        $condicaoId = (int) ($dados['lan_idcondicaopgto'] ?? 0);
        $vencimento = trim((string) ($dados['lan_dtvencimento'] ?? ''));
        $valorInformado = $this->normalizarValor($dados['lan_valorinformado'] ?? 0);
        $valor = $this->normalizarValor($dados['lan_valor'] ?? $valorInformado);

        if ($contaId <= 0 || $centroId <= 0 || $formaId <= 0 || $condicaoId <= 0) {
          throw new \RuntimeException('Conta financeira, centro de custo, forma e condição de pagamento são obrigatórios.');
        }
        if (!$this->opcaoPertenceAoContexto('conta_financeira', 'ctf', $contaId)
          || !$this->opcaoPertenceAoContexto('centro_custo', 'cdc', $centroId)
          || !$this->opcaoPertenceAoContexto('forma_pagamento', 'fpg', $formaId)
          || !$this->opcaoPertenceAoContexto('condicao_pgto', 'cpg', $condicaoId)) {
          throw new \RuntimeException('Um dos cadastros financeiros selecionados não está disponível no contexto atual.');
        }
        if (!$this->dataValida($vencimento) || $valorInformado <= 0 || $valor <= 0) {
          throw new \RuntimeException('Informe uma data de vencimento e valores válidos.');
        }

        $parcela = max(1, (int) ($dados['lan_parcela'] ?? $indice + 1));
        $totalParcela = max(1, (int) ($dados['lan_totalparcela'] ?? count($lancamentos)));
        $status = ($registroTitulo->tit_natureza ?? '') === 'Entrada' ? 'A receber' : 'A pagar';
        $tipoDesconto = $this->normalizarTipoAjuste($dados['lan_tipodesconto'] ?? null);
        $tipoAcrescimo = $this->normalizarTipoAjuste($dados['lan_tipoacrescimo'] ?? null);

        $resultado = $this->insert([
          'lan_status' => $status,
          'lan_statusclass' => 'warning',
          'lan_natureza' => (string) ($registroTitulo->tit_natureza ?? 'Saida'),
          'lan_tipo' => 'Titulo',
          'lan_idtitulo' => $tituloId,
          'lan_idcontafinanceira' => $contaId,
          'lan_idcentrocusto' => $centroId,
          'lan_idplanoconta' => (int) ($registroTitulo->tit_idplanoconta ?? 0),
          'lan_idformapagamento' => $formaId,
          'lan_idcondicaopgto' => $condicaoId,
          'lan_descricao' => $this->normalizarTexto($dados['lan_descricao'] ?? $registroTitulo->tit_nome ?? "Lançamento do título #{$tituloId}", 255),
          'lan_observacao' => $this->normalizarTexto($dados['lan_observacao'] ?? null, 255),
          'lan_valorinformado' => number_format($valorInformado, 2, '.', ''),
          'lan_tipodesconto' => $tipoDesconto,
          'lan_valordesconto' => $this->normalizarDecimalNulo($dados['lan_valordesconto'] ?? null),
          'lan_desconto' => $this->normalizarDecimalNulo($dados['lan_desconto'] ?? null),
          'lan_tipoacrescimo' => $tipoAcrescimo,
          'lan_valoracrescimo' => $this->normalizarDecimalNulo($dados['lan_valoracrescimo'] ?? null),
          'lan_acrescimo' => $this->normalizarDecimalNulo($dados['lan_acrescimo'] ?? null),
          'lan_valor' => number_format($valor, 2, '.', ''),
          'lan_dtvencimento' => $vencimento,
          'lan_dtcompetencia' => $this->normalizarData($dados['lan_dtcompetencia'] ?? null),
          'lan_grupo_parcelamento' => $this->normalizarTexto($dados['lan_grupo_parcelamento'] ?? null, 50),
          'lan_parcela' => $parcela,
          'lan_totalparcela' => $totalParcela
        ], 'Lançamento gerado com sucesso||Erro ao gerar lançamento');

        if (!$resultado->status) {
          throw new \RuntimeException($resultado->text ?? 'Não foi possível gerar o lançamento.');
        }
      }

      $atualizado = $this->atualizarFinanceiroTitulo($tituloId);
      if (!$atualizado->status) {
        throw new \RuntimeException($atualizado->text ?? 'Não foi possível atualizar o status financeiro do título.');
      }
      if ($iniciouTransacao) {
        $this->commit();
      }
      return (new SimpleResponse('Sucesso', 'Lançamentos gerados com sucesso.'))->sucesso()->noDestroy();
    } catch (\Throwable $e) {
      if ($iniciouTransacao && $this->pdoInTransaction()) {
        $this->rollback();
      }
      return $this->atencao($e->getMessage());
    }
  }

  public function pagarReceber(array $ids, string $dataBaixa): object
  {
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0)));
    if (!$ids || !$this->dataValida($dataBaixa) || $dataBaixa > date('Y-m-d')) {
      return $this->atencao('Informe lançamentos válidos e uma data de operação que não seja futura.');
    }

    $titulos = [];
    foreach ($ids as $id) {
      $registro = $this->findById($id);
      if (!$registro->status || $registro->total !== 1) {
        return $this->atencao('Lançamento não encontrado ou fora do contexto atual.');
      }
      $lan = $registro->data[0];
      $status = (string) ($lan->lan_status ?? '');
      $natureza = (string) ($lan->lan_natureza ?? '');
      if (!in_array($status, ['A pagar', 'A receber'], true)) {
        return $this->atencao('Somente lançamentos pendentes podem ser pagos ou recebidos.');
      }
      $novoStatus = $natureza === 'Entrada' ? 'Recebido' : 'Pago';
      $resultado = $this->update([
        'lan_status' => $novoStatus,
        'lan_statusclass' => 'success',
        'lan_dtbaixa' => $dataBaixa
      ], ['lan_id' => $id], 'Baixa registrada com sucesso||Erro ao registrar baixa');
      if (!$resultado->status) {
        return $resultado;
      }
      $tituloId = (int) ($lan->lan_idtitulo ?? 0);
      if ($tituloId > 0) $titulos[$tituloId] = true;
    }

    foreach (array_keys($titulos) as $tituloId) {
      $resultado = $this->atualizarFinanceiroTitulo((int) $tituloId);
      if (!$resultado->status) return $resultado;
    }

    return (new SimpleResponse('Sucesso', 'Pagamento/recebimento registrado com sucesso.'))->sucesso()->noDestroy();
  }

  public function estornarLancamento(int $lanId, string $motivo): object
  {
    $motivo = trim($motivo);
    if ($lanId <= 0) return $this->atencao('Lançamento inválido.');
    if (mb_strlen($motivo) < 10) return $this->atencao('Informe um motivo com no mínimo 10 caracteres.');

    $resposta = $this->findById($lanId);
    if (!$resposta->status || $resposta->total !== 1) return $this->atencao('Lançamento não encontrado ou fora do contexto atual.');
    $origem = $resposta->data[0];
    $statusAtual = (string) ($origem->lan_status ?? '');
    if (!in_array($statusAtual, ['Pago', 'Recebido'], true)) return $this->atencao('Somente lançamentos pagos ou recebidos podem ser estornados.');

    $statusNovo = ($origem->lan_natureza ?? '') === 'Entrada' ? 'A receber' : 'A pagar';
    $this->beginTransaction();
    try {
      $atualizado = $this->update([
        'lan_status' => 'Estornado',
        'lan_statusclass' => 'danger',
        'lan_tipoacao' => 'Estorno',
        'lan_dtacao' => date('Y-m-d H:i:s'),
        'lan_motivoacao' => mb_substr($motivo, 0, 255)
      ], ['lan_id' => $lanId], 'Lançamento estornado com sucesso||Erro ao estornar lançamento');
      if (!$atualizado->status) throw new \RuntimeException($atualizado->text ?? 'Não foi possível estornar o lançamento.');

      $novo = $this->insert([
        'lan_status' => $statusNovo,
        'lan_statusclass' => 'warning',
        'lan_natureza' => $origem->lan_natureza,
        'lan_tipo' => $origem->lan_tipo ?: 'Titulo',
        'lan_idtitulo' => (int) ($origem->lan_idtitulo ?? 0) ?: null,
        'lan_idcontafinanceira' => (int) ($origem->lan_idcontafinanceira ?? 0) ?: null,
        'lan_idcentrocusto' => (int) ($origem->lan_idcentrocusto ?? 0) ?: null,
        'lan_idplanoconta' => (int) ($origem->lan_idplanoconta ?? 0) ?: null,
        'lan_idformapagamento' => (int) ($origem->lan_idformapagamento ?? 0) ?: null,
        'lan_idcondicaopgto' => (int) ($origem->lan_idcondicaopgto ?? 0) ?: null,
        'lan_descricao' => $origem->lan_descricao,
        'lan_observacao' => $origem->lan_observacao,
        'lan_valorinformado' => $origem->lan_valorinformado,
        'lan_tipodesconto' => $origem->lan_tipodesconto,
        'lan_valordesconto' => $origem->lan_valordesconto,
        'lan_desconto' => $origem->lan_desconto,
        'lan_tipoacrescimo' => $origem->lan_tipoacrescimo,
        'lan_valoracrescimo' => $origem->lan_valoracrescimo,
        'lan_acrescimo' => $origem->lan_acrescimo,
        'lan_valor' => $origem->lan_valor,
        'lan_dtvencimento' => $origem->lan_dtvencimento,
        'lan_dtcompetencia' => $origem->lan_dtcompetencia,
        'lan_parcela' => (int) ($origem->lan_parcela ?? 1),
        'lan_totalparcela' => (int) ($origem->lan_totalparcela ?? 1),
        'lan_idlancamento_origem' => $lanId
      ], 'Novo lançamento gerado com sucesso||Erro ao gerar novo lançamento');
      if (!$novo->status) throw new \RuntimeException($novo->text ?? 'Não foi possível gerar o lançamento de estorno.');

      $tituloId = (int) ($origem->lan_idtitulo ?? 0);
      if ($tituloId > 0) {
        $financeiro = $this->atualizarFinanceiroTitulo($tituloId);
        if (!$financeiro->status) throw new \RuntimeException($financeiro->text ?? 'Não foi possível atualizar o título.');
      }
      $this->commit();
      return (new SimpleResponse('Sucesso', 'Lançamento estornado com sucesso.'))->sucesso()->noDestroy();
    } catch (\Throwable $e) {
      if ($this->pdoInTransaction()) $this->rollback();
      return $this->atencao($e->getMessage());
    }
  }

  public function corrigirLancamento(int $lanId, array $dados): object
  {
    $motivo = trim((string) ($dados['motivo'] ?? ''));
    if ($lanId <= 0) return $this->atencao('Lançamento inválido.');
    if (mb_strlen($motivo) < 10) return $this->atencao('Informe um motivo com no mínimo 10 caracteres.');

    $resposta = $this->findById($lanId);
    if (!$resposta->status || $resposta->total !== 1) return $this->atencao('Lançamento não encontrado ou fora do contexto atual.');
    $origem = $resposta->data[0];
    if (!in_array(($origem->lan_status ?? ''), ['Pago', 'Recebido'], true)) return $this->atencao('Somente lançamentos pagos ou recebidos podem ser corrigidos.');

    $contaId = (int) ($dados['lan_idcontafinanceira'] ?? 0);
    $centroId = (int) ($dados['lan_idcentrocusto'] ?? 0);
    $formaId = (int) ($dados['lan_idformapagamento'] ?? 0);
    $condicaoId = (int) ($dados['lan_idcondicaopgto'] ?? 0);
    $vencimento = trim((string) ($dados['lan_dtvencimento'] ?? ''));
    $base = $this->normalizarValor($dados['lan_valorinformado'] ?? 0);
    $valorDesconto = $this->normalizarValor($dados['lan_valordesconto'] ?? 0);
    $valorAcrescimo = $this->normalizarValor($dados['lan_valoracrescimo'] ?? 0);
    if (!$this->opcaoPertenceAoContexto('conta_financeira', 'ctf', $contaId)
      || !$this->opcaoPertenceAoContexto('centro_custo', 'cdc', $centroId)
      || !$this->opcaoPertenceAoContexto('forma_pagamento', 'fpg', $formaId)
      || !$this->opcaoPertenceAoContexto('condicao_pgto', 'cpg', $condicaoId)) {
      return $this->atencao('Um dos cadastros financeiros selecionados não está disponível no contexto atual.');
    }
    if (!$this->dataValida($vencimento) || $base === false || $base <= 0) return $this->atencao('Informe data e valor informado válidos.');

    $tipoDesconto = $this->normalizarTipoAjuste($dados['lan_tipodesconto'] ?? null);
    $tipoAcrescimo = $this->normalizarTipoAjuste($dados['lan_tipoacrescimo'] ?? null);
    $desconto = $tipoDesconto === 'Percentual' ? $base * (float) $valorDesconto / 100 : ($tipoDesconto === 'Valor' ? (float) $valorDesconto : 0);
    $acrescimo = $tipoAcrescimo === 'Percentual' ? $base * (float) $valorAcrescimo / 100 : ($tipoAcrescimo === 'Valor' ? (float) $valorAcrescimo : 0);
    $valorFinal = round(max(0, $base - $desconto + $acrescimo), 2);

    $this->beginTransaction();
    try {
      $atualizado = $this->update([
        'lan_status' => 'Corrigido',
        'lan_statusclass' => 'secondary',
        'lan_tipoacao' => 'Correcao',
        'lan_dtacao' => date('Y-m-d H:i:s'),
        'lan_motivoacao' => mb_substr($motivo, 0, 255)
      ], ['lan_id' => $lanId], 'Lançamento corrigido com sucesso||Erro ao corrigir lançamento');
      if (!$atualizado->status) throw new \RuntimeException($atualizado->text ?? 'Não foi possível corrigir o lançamento.');

      $novo = $this->insert([
        'lan_status' => $origem->lan_status,
        'lan_statusclass' => 'success',
        'lan_natureza' => $origem->lan_natureza,
        'lan_tipo' => $origem->lan_tipo ?: 'Titulo',
        'lan_idtitulo' => (int) ($origem->lan_idtitulo ?? 0) ?: null,
        'lan_idcontafinanceira' => $contaId,
        'lan_idcentrocusto' => $centroId,
        'lan_idplanoconta' => (int) ($origem->lan_idplanoconta ?? 0) ?: null,
        'lan_idformapagamento' => $formaId,
        'lan_idcondicaopgto' => $condicaoId,
        'lan_descricao' => $origem->lan_descricao,
        'lan_observacao' => $this->normalizarTexto($dados['lan_observacao'] ?? $origem->lan_observacao, 255),
        'lan_valorinformado' => number_format($base, 2, '.', ''),
        'lan_tipodesconto' => $tipoDesconto,
        'lan_valordesconto' => $this->normalizarDecimalNulo($valorDesconto),
        'lan_desconto' => number_format($desconto, 2, '.', ''),
        'lan_tipoacrescimo' => $tipoAcrescimo,
        'lan_valoracrescimo' => $this->normalizarDecimalNulo($valorAcrescimo),
        'lan_acrescimo' => number_format($acrescimo, 2, '.', ''),
        'lan_valor' => number_format($valorFinal, 2, '.', ''),
        'lan_dtvencimento' => $vencimento,
        'lan_dtcompetencia' => $origem->lan_dtcompetencia,
        'lan_dtbaixa' => $origem->lan_dtbaixa,
        'lan_parcela' => (int) ($origem->lan_parcela ?? 1),
        'lan_totalparcela' => (int) ($origem->lan_totalparcela ?? 1),
        'lan_idlancamento_origem' => $lanId
      ], 'Novo lançamento corrigido com sucesso||Erro ao gerar lançamento corrigido');
      if (!$novo->status) throw new \RuntimeException($novo->text ?? 'Não foi possível gerar o lançamento corrigido.');

      $tituloId = (int) ($origem->lan_idtitulo ?? 0);
      if ($tituloId > 0) {
        $financeiro = $this->atualizarFinanceiroTitulo($tituloId);
        if (!$financeiro->status) throw new \RuntimeException($financeiro->text ?? 'Não foi possível atualizar o título.');
      }
      $this->commit();
      return (new SimpleResponse('Sucesso', 'Correção registrada com sucesso.'))->sucesso()->noDestroy();
    } catch (\Throwable $e) {
      if ($this->pdoInTransaction()) $this->rollback();
      return $this->atencao($e->getMessage());
    }
  }

  public function atualizarFinanceiroTitulo(int $tituloId): object
  {
    $titulo = (new TituloModel())->getTituloById($tituloId);
    if (!$titulo->status || $titulo->total !== 1) {
      return $this->atencao('Título não encontrado ou fora do contexto atual.');
    }

    $lancamentos = $this->getPorTitulo($tituloId);
    $movimentado = 0.0;
    $baixados = 0;
    $pendentes = 0;
    foreach (($lancamentos->data ?? []) as $lan) {
      $statusLancamento = (string) ($lan->lan_status ?? '');
      if (in_array($statusLancamento, ['Pago', 'Recebido'], true)) {
        $baixados++;
        $movimentado += (float) ($lan->lan_valor ?? 0);
      }
      if (in_array($statusLancamento, ['A pagar', 'A receber'], true)) $pendentes++;
    }
    $registroTitulo = $titulo->data[0];
    $status = 'Aguardando lançamento';
    $classe = 'secondary';
    if ($lancamentos->total > 0) {
      $status = 'Lançamento registrado';
      $classe = 'primary';
    }
    if ($baixados > 0 && $pendentes > 0) {
      $status = 'Parcial';
      $classe = 'warning';
    }
    if ($baixados > 0 && $pendentes === 0) {
      $status = ($registroTitulo->tit_natureza ?? '') === 'Entrada' ? 'Recebido' : 'Pago';
      $classe = 'success';
    }

    return (new TituloModel())->update([
      'tit_valormovimentado' => number_format($movimentado, 2, '.', ''),
      'tit_statusfinanceiro' => $status,
      'tit_statusfinanceiroclass' => $classe
    ], ['tit_id' => $tituloId], 'Título atualizado com sucesso||Erro ao atualizar status financeiro');
  }

  private function opcaoPertenceAoContexto(string $tabela, string $prefixo, int $id): bool
  {
    $where = "{$prefixo}_id = :id AND {$prefixo}_idorganizacao = :org_id";
    $params = ['id' => $id, 'org_id' => $this->contextoOrganizacaoId()];
    $where .= " AND {$prefixo}_status = 'Ativo' AND ({$prefixo}_idempresa = 0 OR {$prefixo}_idempresa = :emp_id)";
    $params['emp_id'] = $this->contextoEmpresaId();
    $response = $this->rawQuery("SELECT {$prefixo}_id FROM " . DB_PREFIX . "_{$tabela} WHERE {$where} LIMIT 1", $params, true);
    return $response->status && $response->total === 1;
  }

  private function contextoOrganizacaoId(): int
  {
    return (int) ($this->contexto->organizacao->org_id ?? $this->usuarioLogado->usu_idorganizacao ?? 0);
  }

  private function contextoEmpresaId(): int
  {
    return (int) ($this->contexto->empresa->emp_id ?? $this->usuarioLogado->usu_idempresa ?? 0);
  }

  private function dataValida($valor): bool
  {
    $data = trim((string) ($valor ?? ''));
    $objeto = \DateTime::createFromFormat('!Y-m-d', $data);
    return $data !== '' && $objeto && $objeto->format('Y-m-d') === $data;
  }

  private function normalizarValor($valor): float|false
  {
    if (is_string($valor)) {
      $valor = trim(str_replace(['R$', ' '], '', $valor));
      if (str_contains($valor, ',')) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
      }
    }
    return is_numeric($valor) ? round((float) $valor, 2) : false;
  }

  private function normalizarDecimalNulo($valor): ?string
  {
    $valor = $this->normalizarValor($valor);
    return $valor !== false && $valor > 0 ? number_format($valor, 2, '.', '') : null;
  }

  private function normalizarTipoAjuste($valor): ?string
  {
    $valor = trim((string) ($valor ?? ''));
    return in_array($valor, ['Valor', 'Percentual'], true) ? $valor : null;
  }

  private function normalizarTexto($valor, int $limite): ?string
  {
    $valor = trim((string) ($valor ?? ''));
    return $valor === '' ? null : mb_substr($valor, 0, $limite);
  }

  private function normalizarData($valor): ?string
  {
    $valor = trim((string) ($valor ?? ''));
    return $this->dataValida($valor) ? $valor : null;
  }

  private function pdoInTransaction(): bool
  {
    return method_exists($this->pdo, 'inTransaction') && $this->pdo->inTransaction();
  }

  private function atencao(string $texto): object
  {
    return (new SimpleResponse('Atenção', $texto))->warning()->noDestroy();
  }
}
