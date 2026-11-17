<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleResponse;

class TransferenciaFinanceiraModel extends LancamentoModel
{
  public function contarTransferencias(): int
  {
    $response = $this->rawQuery(
      "SELECT COUNT(lan_id) AS total
       FROM {$this->getTable()}
       WHERE lan_idorganizacao = :org_id
         AND lan_idempresa = :emp_id
         AND lan_tipo = 'Transferencia'
         AND lan_natureza = 'Saida'",
      $this->parametrosContexto(),
      true,
      'Total de transferências encontrado||Erro ao consultar transferências'
    );

    return $response->status ? (int) ($response->data[0]->total ?? 0) : 0;
  }

  public function pesquisarTransferencias(array $filtros): object
  {
    $tipoPesquisa = trim((string) ($filtros['lbTipoPesquisa'] ?? 'inicial'));
    $conteudo = trim((string) ($filtros['lbLik'] ?? ''));
    $status = trim((string) ($filtros['trf_status'] ?? ''));
    $tipoFiltro = trim((string) ($filtros['lbTpf'] ?? ''));
    $campoFiltro = trim((string) ($filtros['lbCmf'] ?? ''));
    $dataInicio = trim((string) ($filtros['lbIni'] ?? ''));
    $dataFim = trim((string) ($filtros['lbFim'] ?? ''));
    $campoOrdem = trim((string) ($filtros['lbOrd'] ?? ''));
    $ordem = strtoupper(trim((string) ($filtros['lbOby'] ?? 'DESC')));
    $limiteInformado = trim((string) ($filtros['lbLmt'] ?? '1000'));

    if ($tipoPesquisa === 'simples' && $conteudo !== '' && mb_strlen($conteudo) < 3) {
      return (new SimpleResponse('Atenção', 'Informe pelo menos 3 caracteres para pesquisar.'))
        ->warning()
        ->setArrayParams(['data' => [], 'total' => 0])
        ->noDestroy();
    }

    $params = $this->parametrosContexto();
    $where = "WHERE origem.lan_idorganizacao = :org_id
                AND origem.lan_idempresa = :emp_id
                AND origem.lan_tipo = 'Transferencia'
                AND origem.lan_natureza = 'Saida'";

    if ($conteudo !== '') {
      $where .= " AND (
        origem.lan_id LIKE :busca_id
        OR origem.lan_descricao LIKE :busca_descricao
        OR origem.lan_observacao LIKE :busca_observacao
        OR conta_origem.ctf_nome LIKE :busca_origem
        OR conta_destino.ctf_nome LIKE :busca_destino
        OR origem.lan_grupo_transferencia LIKE :busca_grupo
      )";
      $busca = "%{$conteudo}%";
      $params += [
        'busca_id' => $busca,
        'busca_descricao' => $busca,
        'busca_observacao' => $busca,
        'busca_origem' => $busca,
        'busca_destino' => $busca,
        'busca_grupo' => $busca
      ];
    }

    if ($status === 'Concluida') {
      $where .= " AND origem.lan_status = 'Pago'";
    } elseif ($status === 'Estornada') {
      $where .= " AND origem.lan_status = 'Estornado'";
    }

    $camposData = [
      'lan_dtbaixa' => 'origem.lan_dtbaixa',
      'lan_dtcad' => 'origem.lan_dtcad',
      'lan_dtalter' => 'origem.lan_dtalter'
    ];
    if ($tipoFiltro !== '' && isset($camposData[$campoFiltro]) && $this->dataValida($dataInicio)) {
      $campo = $camposData[$campoFiltro];
      if ($tipoFiltro === 'Periodo' && $this->dataValida($dataFim)) {
        $where .= " AND DATE({$campo}) BETWEEN :data_inicio AND :data_fim";
        $params['data_inicio'] = $dataInicio;
        $params['data_fim'] = $dataFim;
      } elseif ($tipoFiltro === 'Exatidao') {
        $where .= " AND DATE({$campo}) = :data_inicio";
        $params['data_inicio'] = $dataInicio;
      }
    }

    $camposOrdem = [
      'lan_id' => 'origem.lan_id',
      'lan_dtbaixa' => 'origem.lan_dtbaixa',
      'lan_valor' => 'origem.lan_valor',
      'lan_status' => 'origem.lan_status',
      'lan_dtcad' => 'origem.lan_dtcad',
      'lan_dtalter' => 'origem.lan_dtalter'
    ];
    $orderBy = $camposOrdem[$campoOrdem] ?? 'origem.lan_dtbaixa';
    $direcao = $ordem === 'ASC' ? 'ASC' : 'DESC';
    $limite = $limiteInformado === 'all' ? 5000 : (int) $limiteInformado;
    $limite = max(1, min($limite, 5000));

    return $this->rawQuery(
      "SELECT
         origem.lan_id AS trf_id,
         origem.lan_status AS trf_status,
         origem.lan_statusclass AS trf_statusclass,
         origem.lan_grupo_transferencia AS trf_grupo,
         origem.lan_descricao AS trf_descricao,
         origem.lan_observacao AS trf_observacao,
         origem.lan_valor AS trf_valor,
         origem.lan_dtbaixa AS trf_data,
         origem.lan_dtcad AS trf_dtcad,
         origem.lan_dtalter AS trf_dtalter,
         conta_origem.ctf_nome AS trf_conta_origem,
         conta_destino.ctf_nome AS trf_conta_destino
       FROM {$this->getTable()} origem
       LEFT JOIN " . DB_PREFIX . "_conta_financeira conta_origem ON conta_origem.ctf_id = origem.lan_idcontafinanceira
       LEFT JOIN {$this->getTable()} destino ON destino.lan_id = origem.lan_idlancamento_vinculado
       LEFT JOIN " . DB_PREFIX . "_conta_financeira conta_destino ON conta_destino.ctf_id = destino.lan_idcontafinanceira
       {$where}
       ORDER BY {$orderBy} {$direcao}, origem.lan_id DESC
       LIMIT {$limite}",
      $params,
      true,
      'Transferências encontradas com sucesso||Erro ao pesquisar transferências'
    );
  }

  public function getTransferenciaById(int $id): object
  {
    if ($id <= 0) return $this->atencao('Transferência inválida.');

    return $this->rawQuery(
      "SELECT
         origem.*,
         conta_origem.ctf_nome AS trf_conta_origem,
         destino.lan_id AS trf_id_destino,
         destino.lan_status AS trf_status_destino,
         destino.lan_descricao AS trf_descricao_destino,
         conta_destino.ctf_nome AS trf_conta_destino
       FROM {$this->getTable()} origem
       LEFT JOIN " . DB_PREFIX . "_conta_financeira conta_origem ON conta_origem.ctf_id = origem.lan_idcontafinanceira
       LEFT JOIN {$this->getTable()} destino ON destino.lan_id = origem.lan_idlancamento_vinculado
       LEFT JOIN " . DB_PREFIX . "_conta_financeira conta_destino ON conta_destino.ctf_id = destino.lan_idcontafinanceira
       WHERE origem.lan_idorganizacao = :org_id
         AND origem.lan_idempresa = :emp_id
         AND origem.lan_tipo = 'Transferencia'
         AND origem.lan_natureza = 'Saida'
         AND (origem.lan_id = :transferencia_origem OR destino.lan_id = :transferencia_destino)
       LIMIT 1",
      $this->parametrosContexto([
        'transferencia_origem' => $id,
        'transferencia_destino' => $id
      ]),
      true,
      'Transferência encontrada com sucesso||Transferência não encontrada'
    );
  }

  public function consultarSaldoDisponivel(int $contaId, string $data, bool $validarOrigem = true): object
  {
    if (!$this->dataValida($data) || $data > date('Y-m-d')) {
      return $this->atencao('Informe uma data de transferência válida e não futura.');
    }

    $conta = $this->contaAtiva($contaId);
    if (!$conta) return $this->atencao('Conta financeira não encontrada, inativa ou fora do contexto atual.');
    if ($validarOrigem && ($conta->ctf_tipo ?? '') === 'Cartao') {
      return (new SimpleResponse('Atenção', 'Cartão de crédito não pode ser a conta de origem. O pagamento da fatura deve sair de uma conta disponível e entrar no cartão.'))
        ->warning()
        ->noDestroy();
    }

    try {
      $saldo = $this->saldoDisponivelNaData($conta, $data);
      return (new SimpleResponse('Sucesso', 'Saldo disponível consultado com sucesso.'))
        ->sucesso()
        ->setArrayParams([
          'data' => (object) [
            'saldo' => $saldo,
            'conta' => (string) ($conta->ctf_nome ?? '')
          ]
        ])
        ->noDestroy();
    } catch (\Throwable $e) {
      return $this->atencao($e->getMessage());
    }
  }

  public function registrarTransferencia(array $dados): object
  {
    $contaOrigemId = (int) ($dados['trf_idcontaorigem'] ?? 0);
    $contaDestinoId = (int) ($dados['trf_idcontadestino'] ?? 0);
    $data = trim((string) ($dados['trf_data'] ?? ''));
    $valor = $this->normalizarValor($dados['trf_valor'] ?? null);
    $observacao = $this->normalizarTexto($dados['trf_observacao'] ?? null, 255);

    if ($contaOrigemId <= 0 || $contaDestinoId <= 0 || $contaOrigemId === $contaDestinoId) {
      return $this->atencao('Selecione duas contas financeiras diferentes.');
    }
    if (!$this->dataValida($data) || $data > date('Y-m-d')) {
      return $this->atencao('Informe uma data de transferência válida e não futura.');
    }
    if ($valor === false || $valor <= 0) {
      return $this->atencao('Informe um valor de transferência maior que zero.');
    }

    $this->beginTransaction();

    try {
      $contas = [];
      $idsContas = array_values(array_unique([$contaOrigemId, $contaDestinoId]));
      sort($idsContas, SORT_NUMERIC);
      foreach ($idsContas as $contaId) {
        $conta = $this->contaAtiva($contaId, true);
        if (!$conta) throw new \RuntimeException('Uma das contas selecionadas está inativa ou fora do contexto atual.');
        $contas[$contaId] = $conta;
      }
      $contaOrigem = $contas[$contaOrigemId];
      $contaDestino = $contas[$contaDestinoId];

      if (($contaOrigem->ctf_tipo ?? '') === 'Cartao') {
        throw new \RuntimeException('Cartão de crédito não pode ser a conta de origem. Para pagar a fatura, use uma conta disponível como origem e o cartão como destino.');
      }

      $saldoDisponivel = $this->saldoDisponivelNaData($contaOrigem, $data);
      if ($saldoDisponivel + 0.00001 < $valor) {
        throw new \RuntimeException('Saldo insuficiente na conta de origem para esta transferência. Saldo disponível: R$ ' . Helpers::formataValor($saldoDisponivel) . '.');
      }

      $grupo = 'TRF-' . strtoupper(bin2hex(random_bytes(10)));
      $valorFormatado = number_format($valor, 2, '.', '');
      $saida = $this->insert([
        'lan_status' => 'Pago',
        'lan_statusclass' => 'success',
        'lan_natureza' => 'Saida',
        'lan_tipo' => 'Transferencia',
        'lan_idcontafinanceira' => $contaOrigemId,
        'lan_descricao' => "Transferência para {$contaDestino->ctf_nome}",
        'lan_observacao' => $observacao,
        'lan_valorinformado' => $valorFormatado,
        'lan_valor' => $valorFormatado,
        'lan_dtvencimento' => $data,
        'lan_dtcompetencia' => $data,
        'lan_dtbaixa' => $data,
        'lan_grupo_transferencia' => $grupo,
        'lan_origem' => 'Transferencia'
      ], 'Transferência de saída registrada com sucesso||Erro ao registrar saída da transferência');
      if (!$saida->status) throw new \RuntimeException($saida->text ?? 'Não foi possível registrar a saída da transferência.');

      $saidaId = (int) ($saida->lastInsertId ?? 0);
      if ($saidaId <= 0) throw new \RuntimeException('Não foi possível identificar a saída da transferência.');

      $entrada = $this->insert([
        'lan_status' => 'Recebido',
        'lan_statusclass' => 'success',
        'lan_natureza' => 'Entrada',
        'lan_tipo' => 'Transferencia',
        'lan_idcontafinanceira' => $contaDestinoId,
        'lan_descricao' => "Transferência de {$contaOrigem->ctf_nome}",
        'lan_observacao' => $observacao,
        'lan_valorinformado' => $valorFormatado,
        'lan_valor' => $valorFormatado,
        'lan_dtvencimento' => $data,
        'lan_dtcompetencia' => $data,
        'lan_dtbaixa' => $data,
        'lan_grupo_transferencia' => $grupo,
        'lan_idlancamento_vinculado' => $saidaId,
        'lan_origem' => 'Transferencia'
      ], 'Transferência de entrada registrada com sucesso||Erro ao registrar entrada da transferência');
      if (!$entrada->status) throw new \RuntimeException($entrada->text ?? 'Não foi possível registrar a entrada da transferência.');

      $entradaId = (int) ($entrada->lastInsertId ?? 0);
      if ($entradaId <= 0) throw new \RuntimeException('Não foi possível identificar a entrada da transferência.');

      $vinculo = $this->update([
        'lan_idlancamento_vinculado' => $entradaId
      ], ['lan_id' => $saidaId], 'Transferência vinculada com sucesso||Erro ao vincular transferências');
      if (!$vinculo->status) throw new \RuntimeException($vinculo->text ?? 'Não foi possível vincular os lançamentos da transferência.');

      $this->commit();

      return (new SimpleResponse('Sucesso', 'Transferência registrada com sucesso.'))
        ->sucesso()
        ->setArrayParams([
          'transferencia_id' => $saidaId,
          'location' => Helpers::url("financeiro/transferencia/form/{$saidaId}")
        ])
        ->noDestroy();
    } catch (\Throwable $e) {
      if ($this->pdo->inTransaction()) $this->rollback();
      return $this->atencao($e->getMessage());
    }
  }

  public function estornarTransferencia(int $id, string $motivo): object
  {
    $motivo = $this->normalizarTexto($motivo, 255);
    if (!$motivo) return $this->atencao('Informe o motivo do estorno.');

    $consulta = $this->getTransferenciaById($id);
    if (!$consulta->status || $consulta->total !== 1) return $this->atencao('Transferência não encontrada ou fora do contexto atual.');

    $transferencia = $consulta->data[0];
    $saidaId = (int) ($transferencia->lan_id ?? 0);
    $entradaId = (int) ($transferencia->trf_id_destino ?? 0);
    if ($saidaId <= 0 || $entradaId <= 0) return $this->atencao('Os lançamentos vinculados da transferência não foram encontrados.');
    if (($transferencia->lan_status ?? '') === 'Estornado') return $this->atencao('Esta transferência já foi estornada.');
    if (($transferencia->lan_status ?? '') !== 'Pago' || ($transferencia->trf_status_destino ?? '') !== 'Recebido') {
      return $this->atencao('Somente transferências concluídas podem ser estornadas.');
    }

    $this->beginTransaction();
    try {
      foreach ([$saidaId, $entradaId] as $lancamentoId) {
        $resultado = $this->update([
          'lan_status' => 'Estornado',
          'lan_statusclass' => 'danger',
          'lan_tipoacao' => 'Estorno',
          'lan_dtacao' => date('Y-m-d H:i:s'),
          'lan_motivoacao' => $motivo
        ], ['lan_id' => $lancamentoId], 'Transferência estornada com sucesso||Erro ao estornar transferência');
        if (!$resultado->status) throw new \RuntimeException($resultado->text ?? 'Não foi possível estornar a transferência.');
      }

      $this->commit();
      return (new SimpleResponse('Sucesso', 'Transferência estornada com sucesso.'))->sucesso()->noDestroy();
    } catch (\Throwable $e) {
      if ($this->pdo->inTransaction()) $this->rollback();
      return $this->atencao($e->getMessage());
    }
  }

  private function contaAtiva(int $id, bool $bloquear = false): ?object
  {
    if ($bloquear) {
      $response = $this->rawQuery(
        "SELECT *
         FROM " . DB_PREFIX . "_conta_financeira
         WHERE ctf_id = :conta_id
           AND ctf_idorganizacao = :org_id
           AND (ctf_idempresa = 0 OR ctf_idempresa = :emp_id)
           AND ctf_status = 'Ativo'
         LIMIT 1 FOR UPDATE",
        $this->parametrosContexto(['conta_id' => $id]),
        true,
        'Conta financeira encontrada||Conta financeira não encontrada'
      );
      return $response->status && $response->total === 1 ? $response->data[0] : null;
    }

    $response = (new FinanceiroBasicoModel('conta_financeira'))->getRegistroByIdContexto($id);
    if (!$response->status || $response->total !== 1) return null;

    $conta = $response->data[0];
    return ($conta->ctf_status ?? '') === 'Ativo' ? $conta : null;
  }

  private function saldoDisponivelNaData(object $conta, string $data): float
  {
    $dataSaldoInicial = trim((string) ($conta->ctf_dt_saldo_inicial ?? ''));
    if ($dataSaldoInicial !== '' && $data < $dataSaldoInicial) {
      throw new \RuntimeException('A data da transferência não pode ser anterior ao saldo inicial desta conta.');
    }

    $movimentos = $this->rawQuery(
      "SELECT COALESCE(SUM(
        CASE
          WHEN lan_status = 'Recebido' AND lan_natureza = 'Entrada' THEN lan_valor
          WHEN lan_status = 'Pago' AND lan_natureza = 'Saida' THEN -lan_valor
          ELSE 0
        END
      ), 0) AS saldo_movimentos
       FROM {$this->getTable()}
       WHERE lan_idorganizacao = :org_id
         AND lan_idempresa = :emp_id
         AND lan_idcontafinanceira = :conta_id
         AND lan_dtbaixa IS NOT NULL
         AND lan_dtbaixa <= :data_transferencia",
      $this->parametrosContexto([
        'conta_id' => (int) ($conta->ctf_id ?? 0),
        'data_transferencia' => $data
      ]),
      true,
      'Saldo da conta encontrado||Erro ao consultar saldo da conta'
    );
    if (!$movimentos->status) throw new \RuntimeException('Não foi possível consultar o saldo disponível da conta.');

    return round(
      (float) ($conta->ctf_saldo_inicial ?? 0) + (float) ($movimentos->data[0]->saldo_movimentos ?? 0),
      2
    );
  }

  private function parametrosContexto(array $adicionais = []): array
  {
    return $adicionais + [
      'org_id' => (int) ($this->contexto->organizacao->org_id ?? 0),
      'emp_id' => (int) ($this->contexto->empresa->emp_id ?? 0)
    ];
  }

  private function dataValida(string $data): bool
  {
    $objeto = \DateTime::createFromFormat('!Y-m-d', $data);
    return $objeto && $objeto->format('Y-m-d') === $data;
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

  private function normalizarTexto($valor, int $limite): ?string
  {
    $valor = trim((string) ($valor ?? ''));
    return $valor === '' ? null : mb_substr($valor, 0, $limite);
  }
}
