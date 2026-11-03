<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;
use Source\Service\LimitePlanoService;

class TituloModel extends SimpleModel
{
  protected string $tabela = 'titulo';
  protected string $prefixo = 'tit';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getTituloById(int $id): object
  {
    $response = $this->rawQuery(
      "SELECT * FROM {$this->getTable()}
       WHERE tit_id = :tit_id
         AND tit_idorganizacao = :tit_idorganizacao
         AND tit_idempresa = :tit_idempresa
       LIMIT 1",
      [
        'tit_id' => $id,
        'tit_idorganizacao' => $this->getContextoOrganizacaoId(),
        'tit_idempresa' => $this->getContextoEmpresaId()
      ],
      true,
      'Título encontrado com sucesso||Título não encontrado'
    );

    if ($response->status && $response->total === 1) {
      $response->data[0]->origem_dados = $this->buscarOrigem(
        (string) ($response->data[0]->tit_origem ?? ''),
        (int) ($response->data[0]->tit_idorigem ?? 0)
      );
    }

    return $response;
  }

  public function contarTitulosContexto(): object
  {
    return $this->rawQuery(
      "SELECT COUNT(*) AS total
       FROM {$this->getTable()}
       WHERE tit_idorganizacao = :tit_idorganizacao
         AND tit_idempresa = :tit_idempresa",
      [
        'tit_idorganizacao' => $this->getContextoOrganizacaoId(),
        'tit_idempresa' => $this->getContextoEmpresaId()
      ],
      true,
      'Total de títulos encontrado com sucesso||Erro ao contar títulos'
    );
  }

  public function pesquisarTitulo(array $filtros): object
  {
    $tipoPesquisa = trim((string) ($filtros['lbTipoPesquisa'] ?? 'inicial'));
    $conteudo = trim((string) ($filtros['lbLik'] ?? ''));
    $status = trim((string) ($filtros['tit_status'] ?? ''));
    $statusFinanceiro = trim((string) ($filtros['tit_statusfinanceiro'] ?? ''));
    $natureza = trim((string) ($filtros['tit_natureza'] ?? ''));
    $tipoFiltro = trim((string) ($filtros['lbTpf'] ?? ''));
    $campoFiltro = trim((string) ($filtros['lbCmf'] ?? ''));
    $dataInicio = trim((string) ($filtros['lbIni'] ?? ''));
    $dataFim = trim((string) ($filtros['lbFim'] ?? ''));
    $campoOrdem = trim((string) ($filtros['lbOrd'] ?? ''));
    $ordem = strtoupper(trim((string) ($filtros['lbOby'] ?? 'ASC')));
    $limiteInformado = trim((string) ($filtros['lbLmt'] ?? '1000'));

    if ($tipoPesquisa === 'simples' && $conteudo !== '' && mb_strlen($conteudo) < 4) {
      return (new SimpleResponse('Atenção', 'Informe pelo menos 4 caracteres para pesquisar.'))
        ->warning()
        ->setArrayParams(['data' => [], 'total' => 0])
        ->noDestroy();
    }

    $params = [
      'tit_idorganizacao' => $this->getContextoOrganizacaoId(),
      'tit_idempresa' => $this->getContextoEmpresaId()
    ];
    $where = "WHERE {$this->getTable()}.tit_idorganizacao = :tit_idorganizacao
              AND {$this->getTable()}.tit_idempresa = :tit_idempresa";

    if ($conteudo !== '') {
      $where .= "
        AND (
          {$this->getTable()}.tit_id LIKE :tit_id_busca
          OR {$this->getTable()}.tit_nome LIKE :tit_nome_busca
          OR {$this->getTable()}.tit_descricao LIKE :tit_descricao_busca
          OR {$this->getTable()}.tit_pct_nome LIKE :tit_pct_nome_busca
          OR {$this->getTable()}.tit_status LIKE :tit_status_busca
          OR {$this->getTable()}.tit_statusfinanceiro LIKE :tit_statusfinanceiro_busca
        )";
      $busca = "%{$conteudo}%";
      $params['tit_id_busca'] = $busca;
      $params['tit_nome_busca'] = $busca;
      $params['tit_descricao_busca'] = $busca;
      $params['tit_pct_nome_busca'] = $busca;
      $params['tit_status_busca'] = $busca;
      $params['tit_statusfinanceiro_busca'] = $busca;
    }

    if (in_array($status, ['Cadastrado', 'Confirmado', 'Cancelado'], true)) {
      $where .= " AND {$this->getTable()}.tit_status = :tit_status";
      $params['tit_status'] = $status;
    }
    if (in_array($statusFinanceiro, ['Aguardando confirmação', 'Aguardando lançamento', 'Lançamento registrado', 'Parcial', 'Pago', 'Recebido', 'Cancelado'], true)) {
      $where .= " AND {$this->getTable()}.tit_statusfinanceiro = :tit_statusfinanceiro";
      $params['tit_statusfinanceiro'] = $statusFinanceiro;
    }
    if (in_array($natureza, ['Entrada', 'Saida'], true)) {
      $where .= " AND {$this->getTable()}.tit_natureza = :tit_natureza";
      $params['tit_natureza'] = $natureza;
    }

    $camposData = [
      'tit_referencia' => 'tit_referencia',
      'tit_dtvencimento' => 'tit_dtvencimento',
      'tit_dtcad' => 'tit_dtcad',
      'tit_dtalter' => 'tit_dtalter'
    ];
    if ($tipoFiltro !== '' && isset($camposData[$campoFiltro]) && $dataInicio !== '') {
      $campoData = $camposData[$campoFiltro];
      if ($tipoFiltro === 'Periodo' && $dataFim !== '') {
        $where .= " AND DATE({$this->getTable()}.{$campoData}) BETWEEN :tit_data_inicio AND :tit_data_fim";
        $params['tit_data_inicio'] = $dataInicio;
        $params['tit_data_fim'] = $dataFim;
      } elseif ($tipoFiltro === 'Exatidao') {
        $where .= " AND DATE({$this->getTable()}.{$campoData}) = :tit_data_inicio";
        $params['tit_data_inicio'] = $dataInicio;
      }
    }

    $camposOrdem = [
      'tit_id', 'tit_nome', 'tit_pct_nome', 'tit_natureza', 'tit_referencia',
      'tit_dtvencimento', 'tit_valortotal', 'tit_valormovimentado',
      'tit_status', 'tit_statusfinanceiro', 'tit_dtcad', 'tit_dtalter'
    ];
    $orderBy = in_array($campoOrdem, $camposOrdem, true) ? $campoOrdem : 'tit_id';
    $direcao = $ordem === 'DESC' ? 'DESC' : 'ASC';
    $limite = $limiteInformado === 'all' ? 5000 : (int) $limiteInformado;
    $limite = max(1, min($limite, 5000));

    $response = $this->rawQuery(
      "SELECT *
       FROM {$this->getTable()}
       {$where}
       ORDER BY {$orderBy} {$direcao}
       LIMIT {$limite}",
      $params,
      true,
      'Títulos encontrados com sucesso||Erro ao pesquisar títulos'
    );

    if ($response->status && !empty($response->data)) {
      foreach ($response->data as $registro) {
        $origem = $this->buscarOrigem(
          (string) ($registro->tit_origem ?? ''),
          (int) ($registro->tit_idorigem ?? 0)
        );
        $registro->tit_responsavel = $origem ? (object) [
          'id' => (int) ($origem->id ?? 0),
          'origem' => (string) ($origem->origem ?? ''),
          'nome' => (string) ($origem->nome ?? ''),
          'documento' => (string) ($origem->documento ?? ''),
          'documentoMascara' => (string) ($origem->documentoMascara ?? ''),
          'celular' => (string) ($origem->celular ?? ''),
          'celularMascara' => (string) ($origem->celularMascara ?? '')
        ] : null;
      }
    }

    return $response;
  }

  public function pesquisarRapida(string $conteudo): object
  {
    $conteudo = trim(rawurldecode($conteudo));
    $rotaLista = 'titulo/list';

    if ($conteudo === '') {
      return (new SimpleResponse('Atenção', 'Digite uma informação para pesquisar.'))
        ->warning()
        ->setArrayParams(['location' => Helpers::url($rotaLista)])
        ->noDestroy();
    }

    $params = [
      'tit_idorganizacao' => $this->getContextoOrganizacaoId(),
      'tit_idempresa' => $this->getContextoEmpresaId()
    ];
    $where = "WHERE {$this->getTable()}.tit_idorganizacao = :tit_idorganizacao
              AND {$this->getTable()}.tit_idempresa = :tit_idempresa";
    $wherePesquisa = [];

    if (ctype_digit($conteudo)) {
      $wherePesquisa[] = "{$this->getTable()}.tit_id = :conteudo_id";
      $params['conteudo_id'] = (int) $conteudo;
    }

    foreach (['tit_nome', 'tit_descricao', 'tit_pct_nome', 'tit_status', 'tit_statusfinanceiro'] as $index => $campo) {
      $parametro = "conteudo_texto_{$index}";
      $wherePesquisa[] = "{$this->getTable()}.{$campo} LIKE :{$parametro}";
      $params[$parametro] = "%{$conteudo}%";
    }

    $where .= " AND (" . implode(' OR ', $wherePesquisa) . ')';
    $orderBy = 'CASE';
    if (ctype_digit($conteudo)) {
      $orderBy .= " WHEN {$this->getTable()}.tit_id = :ordem_id THEN 1";
      $params['ordem_id'] = (int) $conteudo;
    }
    $orderBy .= " WHEN {$this->getTable()}.tit_nome = :ordem_nome THEN 2 ELSE 9 END,
      {$this->getTable()}.tit_nome ASC,
      {$this->getTable()}.tit_id ASC";
    $params['ordem_nome'] = $conteudo;

    $response = $this->rawQuery(
      "SELECT {$this->getTable()}.tit_id, {$this->getTable()}.tit_nome
       FROM {$this->getTable()}
       {$where}
       ORDER BY {$orderBy}
       LIMIT 2",
      $params,
      true,
      'Título encontrado com sucesso||Erro ao pesquisar título'
    );

    if (!$response->status) {
      return (new SimpleResponse($response))
        ->erro('Erro ao realizar pesquisa rápida de título.')
        ->setArrayParams(['location' => Helpers::url($rotaLista)])
        ->noDestroy();
    }

    if ($response->total <= 0) {
      return (new SimpleResponse('Atenção', 'Nenhum título encontrado para a pesquisa informada.'))
        ->warning()
        ->setArrayParams(['location' => Helpers::url($rotaLista)])
        ->noDestroy();
    }

    if ($response->total > 1) {
      return (new SimpleResponse('Atenção', 'Mais de um título foi encontrado. Use a pesquisa completa para refinar o resultado.'))
        ->warning()
        ->setArrayParams([
          'data' => $response->data,
          'total' => $response->total,
          'location' => Helpers::url($rotaLista . '?lbLik=' . rawurlencode($conteudo))
        ])
        ->noDestroy();
    }

    $titulo = $response->data[0];
    return (new SimpleResponse('Sucesso', 'Título encontrado.'))
      ->sucesso()
      ->setArrayParams([
        'data' => $titulo,
        'total' => 1,
        'location' => Helpers::url("titulo/form/{$titulo->tit_id}")
      ])
      ->noDestroy();
  }

  public function possuiLancamentoBaixado(int $tituloId): bool
  {
    if ($tituloId <= 0) {
      return false;
    }

    $response = $this->rawQuery(
      "SELECT lan_id
       FROM " . DB_PREFIX . "_lancamento
       WHERE lan_idtitulo = :lan_idtitulo
         AND lan_idorganizacao = :lan_idorganizacao
         AND lan_idempresa = :lan_idempresa
         AND lan_status IN ('Pago', 'Recebido')
       LIMIT 1",
      [
        'lan_idtitulo' => $tituloId,
        'lan_idorganizacao' => $this->getContextoOrganizacaoId(),
        'lan_idempresa' => $this->getContextoEmpresaId()
      ],
      true,
      'Lançamento baixado consultado com sucesso||Erro ao consultar lançamentos baixados'
    );

    return $response->status && $response->total > 0;
  }

  public function getPlanosContasAtivos(): array
  {
    $response = (new FinanceiroBasicoModel('plano_conta'))->getRegistrosContexto();

    if (!$response->status) {
      return [];
    }

    return array_values(array_filter(
      $response->data ?? [],
      static fn(object $plano): bool => ($plano->pct_status ?? '') === 'Ativo'
        && ($plano->pct_nivel ?? '') === 'Item'
    ));
  }

  public function salvarTitulo(array $data): object
  {
    $id = (int) ($data['tit_id'] ?? 0);
    $atual = null;

    if ($id > 0) {
      $encontrado = $this->getTituloById($id);
      if (!$encontrado->status || $encontrado->total !== 1) {
        return $this->atencao('Título não encontrado ou fora do contexto atual.');
      }
      $atual = $encontrado->data[0];

      if (($atual->tit_status ?? '') !== 'Cadastrado') {
        return $this->atencao('Somente títulos com status Cadastrado podem ser alterados.');
      }
    }

    $nome = trim((string) ($data['tit_nome'] ?? ($atual->tit_nome ?? '')));
    if ($nome === '' || mb_strlen($nome) > 100) {
      return $this->atencao('Informe um título com até 100 caracteres.');
    }

    $planoId = (int) ($data['tit_idplanoconta'] ?? ($atual->tit_idplanoconta ?? 0));
    $plano = (new FinanceiroBasicoModel('plano_conta'))->getRegistroByIdContexto($planoId);
    if (!$plano->status || $plano->total !== 1 || ($plano->data[0]->pct_status ?? '') !== 'Ativo' || ($plano->data[0]->pct_nivel ?? '') !== 'Item') {
      return $this->atencao('Selecione um plano de contas ativo e do nível Item.');
    }

    // A natureza pertence ao plano de contas. Nunca confie em uma natureza enviada pelo formulário.
    $natureza = trim((string) ($plano->data[0]->pct_natureza ?? ''));
    if (!in_array($natureza, ['Entrada', 'Saida'], true)) {
      return $this->atencao('O plano de contas selecionado não possui uma natureza válida.');
    }

    $referencia = $this->normalizarData($data['tit_referencia'] ?? ($atual->tit_referencia ?? null));
    $vencimento = $this->normalizarData($data['tit_dtvencimento'] ?? ($atual->tit_dtvencimento ?? null));
    if ($referencia === false || $vencimento === false) {
      return $this->atencao('Informe datas válidas para o título.');
    }
    if ($referencia === null || $vencimento === null) {
      return $this->atencao('Informe as datas de referência e vencimento do título.');
    }

    $valor = $this->normalizarValor($data['tit_valortotal'] ?? ($atual->tit_valortotal ?? 0));
    if ($valor === false || $valor <= 0) {
      return $this->atencao('Informe um valor maior que zero.');
    }

    $origem = strtolower(trim((string) ($data['tit_origem'] ?? ($atual->tit_origem ?? ''))));
    $origemId = (int) ($data['tit_idorigem'] ?? ($atual->tit_idorigem ?? 0));
    if ($origem !== '' && $origem !== 'fornecedor') {
      return $this->atencao('O vínculo informado é inválido.');
    }

    if ($natureza === 'Saida' && ($origemId <= 0 || $origem === '')) {
      return $this->atencao('É obrigatório vincular um fornecedor para títulos de saída.');
    }

    if ($origemId > 0) {
      $origemDados = $this->buscarOrigem($origem, $origemId);
      if (!$origemDados) {
        return $this->atencao('O fornecedor selecionado não foi encontrado ou está inativo.');
      }
    }

    $limite = (new LimitePlanoService($this->contexto))->verificarTitulo($vencimento ?: '', $id);
    if ($limite->status && !$limite->permitido) {
      return $this->atencao($limite->text ?: 'O limite de títulos do plano foi atingido.');
    }

    $registro = [
      'tit_idorganizacao' => $this->getContextoOrganizacaoId(),
      'tit_idempresa' => $this->getContextoEmpresaId(),
      'tit_natureza' => $natureza,
      'tit_nome' => $nome,
      'tit_descricao' => $this->normalizarTexto($data['tit_descricao'] ?? ($atual->tit_descricao ?? null), 255),
      'tit_idplanoconta' => $planoId,
      'tit_pct_nome' => $this->normalizarTexto($plano->data[0]->pct_nome ?? null, 80),
      'tit_referencia' => $referencia,
      'tit_dtvencimento' => $vencimento,
      'tit_valortotal' => number_format($valor, 2, '.', ''),
      'tit_origem' => $origem !== '' ? $origem : null,
      'tit_idorigem' => $origemId > 0 ? $origemId : null
    ];

    if ($id > 0) {
      $registro['tit_idorganizacao'] = (int) $atual->tit_idorganizacao;
      $registro['tit_idempresa'] = (int) $atual->tit_idempresa;
      $resultado = $this->update($registro, ['tit_id' => $id], 'Título atualizado com sucesso||Erro ao atualizar título');
    } else {
      $registro['tit_status'] = 'Cadastrado';
      $registro['tit_statusclass'] = 'secondary';
      $registro['tit_statusfinanceiro'] = 'Aguardando confirmação';
      $registro['tit_statusfinanceiroclass'] = 'secondary';
      $resultado = $this->insert($registro, 'Título cadastrado com sucesso||Erro ao cadastrar título');
      if ($resultado->status) {
        $id = (int) ($resultado->lastInsertId ?? 0);
      }
    }

    if (!$resultado->status) {
      return $resultado;
    }

    return (new SimpleResponse($resultado))
      ->setArrayParams([
        'location' => Helpers::url("titulo/form/{$id}"),
        'data' => (object) ['id' => $id]
      ])
      ->noDestroy();
  }

  public function confirmarTitulo(int $tituloId): object
  {
    if ($tituloId <= 0) {
      return $this->atencao('ID do título inválido.');
    }

    $response = $this->getTituloById($tituloId);
    if (!$response->status || $response->total !== 1) {
      return $this->atencao('Título não encontrado ou fora do contexto atual.');
    }

    $titulo = $response->data[0];
    if (($titulo->tit_status ?? '') === 'Confirmado') {
      return (new SimpleResponse('Atenção', 'O título já está confirmado.'))->sucesso()->noDestroy();
    }
    if (($titulo->tit_status ?? '') === 'Cancelado') {
      return $this->atencao('Título cancelado não pode ser confirmado.');
    }
    if (($titulo->tit_status ?? '') !== 'Cadastrado') {
      return $this->atencao('O título não está disponível para confirmação.');
    }

    $usuario = $this->usuarioLogado;
    return $this->update(
      [
        'tit_status' => 'Confirmado',
        'tit_statusclass' => 'success',
        'tit_statusfinanceiro' => 'Aguardando lançamento',
        'tit_statusfinanceiroclass' => 'secondary',
        'tit_dtconfirmacao' => date('Y-m-d H:i:s'),
        'tit_idusu_confirmacao' => (int) ($usuario->usu_id ?? 0),
        'tit_usu_nome_confirmacao' => (string) ($usuario->usu_razaosocial ?? $usuario->usu_nome ?? '')
      ],
      ['tit_id' => $tituloId],
      'Título confirmado com sucesso||Erro ao confirmar título'
    );
  }

  public function registrarLancamentoRapido(array $data): object
  {
    $dataBaixa = trim((string) ($data['lan_dtbaixa'] ?? $data['tit_referencia'] ?? ''));
    if ($dataBaixa === '' || $dataBaixa > date('Y-m-d')) {
      return $this->atencao('Informe uma data de recebimento ou pagamento válida, que não seja futura.');
    }

    $this->beginTransaction();
    try {
      $titulo = $this->salvarTitulo([
        'tit_nome' => $data['tit_nome'] ?? '',
        'tit_descricao' => $data['tit_descricao'] ?? null,
        'tit_idplanoconta' => $data['tit_idplanoconta'] ?? 0,
        'tit_referencia' => $data['tit_referencia'] ?? null,
        'tit_dtvencimento' => $data['tit_dtvencimento'] ?? $data['tit_referencia'] ?? null,
        'tit_valortotal' => $data['tit_valortotal'] ?? 0,
        'tit_origem' => $data['tit_origem'] ?? '',
        'tit_idorigem' => $data['tit_idorigem'] ?? 0
      ]);
      if (!$titulo->status) {
        throw new \RuntimeException($titulo->text ?? 'Não foi possível criar o título.');
      }

      $tituloId = (int) ($titulo->data->id ?? 0);
      if ($tituloId <= 0) {
        throw new \RuntimeException('Não foi possível identificar o título criado.');
      }

      $confirmacao = $this->confirmarTitulo($tituloId);
      if (!$confirmacao->status) {
        throw new \RuntimeException($confirmacao->text ?? 'Não foi possível confirmar o título.');
      }

      $lancamentoModel = new LancamentoModel();
      $geracao = $lancamentoModel->gerarPorTitulo($tituloId, [[
        'lan_idcontafinanceira' => $data['lan_idcontafinanceira'] ?? 0,
        'lan_idcentrocusto' => $data['lan_idcentrocusto'] ?? 0,
        'lan_idformapagamento' => $data['lan_idformapagamento'] ?? 0,
        'lan_idcondicaopgto' => $data['lan_idcondicaopgto'] ?? 0,
        'lan_dtvencimento' => $data['lan_dtvencimento'] ?? $dataBaixa,
        'lan_dtcompetencia' => $data['tit_referencia'] ?? $dataBaixa,
        'lan_valorinformado' => $data['lan_valorinformado'] ?? $data['tit_valortotal'] ?? 0,
        'lan_valor' => $data['lan_valor'] ?? $data['tit_valortotal'] ?? 0,
        'lan_descricao' => $data['lan_descricao'] ?? $data['tit_nome'] ?? '',
        'lan_observacao' => $data['lan_observacao'] ?? $data['tit_descricao'] ?? null,
        'lan_parcela' => 1,
        'lan_totalparcela' => 1
      ]], false);
      if (!$geracao->status) {
        throw new \RuntimeException($geracao->text ?? 'Não foi possível gerar o lançamento.');
      }

      $lancamentos = $lancamentoModel->getPorTitulo($tituloId);
      $lancamentoId = (int) ($lancamentos->data[0]->lan_id ?? 0);
      if (!$lancamentos->status || $lancamentoId <= 0) {
        throw new \RuntimeException('Não foi possível identificar o lançamento gerado.');
      }

      $baixa = $lancamentoModel->pagarReceber([$lancamentoId], $dataBaixa);
      if (!$baixa->status) {
        throw new \RuntimeException($baixa->text ?? 'Não foi possível registrar o recebimento ou pagamento.');
      }

      $this->commit();
      return (new SimpleResponse('Sucesso', 'Lançamento rápido registrado com sucesso.'))
        ->sucesso()
        ->setArrayParams([
          'data' => (object) ['tituloId' => $tituloId, 'lancamentoId' => $lancamentoId],
          'location' => Helpers::url("titulo/form/{$tituloId}")
        ])
        ->noDestroy();
    } catch (\Throwable $e) {
      if ($this->pdoInTransaction()) {
        $this->rollback();
      }
      return $this->atencao($e->getMessage());
    }
  }

  public function cancelarTitulo(int $tituloId, string $motivo): object
  {
    if ($tituloId <= 0) {
      return $this->atencao('ID do título inválido.');
    }

    $motivo = trim($motivo);
    if (mb_strlen($motivo) < 10) {
      return $this->atencao('Informe um motivo com no mínimo 10 caracteres.');
    }
    if (mb_strlen($motivo) > 255) {
      return $this->atencao('O motivo do cancelamento deve ter até 255 caracteres.');
    }

    $response = $this->getTituloById($tituloId);
    if (!$response->status || $response->total !== 1) {
      return $this->atencao('Título não encontrado ou fora do contexto atual.');
    }

    $titulo = $response->data[0];
    $status = (string) ($titulo->tit_status ?? '');
    if ($status === 'Cancelado') {
      return (new SimpleResponse('Atenção', 'O título já está cancelado.'))->sucesso()->noDestroy();
    }
    if (!in_array($status, ['Cadastrado', 'Confirmado'], true)) {
      return $this->atencao('O título não pode mais ser cancelado.');
    }
    if ($status === 'Confirmado' && $this->possuiLancamentoBaixado($tituloId)) {
      return $this->atencao('Este título possui lançamento baixado e não pode ser cancelado.');
    }

    $usuario = $this->usuarioLogado;
    return $this->update(
      [
        'tit_status' => 'Cancelado',
        'tit_statusclass' => 'danger',
        'tit_statusfinanceiro' => 'Cancelado',
        'tit_statusfinanceiroclass' => 'danger',
        'tit_dtcancelamento' => date('Y-m-d H:i:s'),
        'tit_idusu_cancelamento' => (int) ($usuario->usu_id ?? 0),
        'tit_usu_nome_cancelamento' => (string) ($usuario->usu_razaosocial ?? $usuario->usu_nome ?? ''),
        'tit_motivo_cancelamento' => $motivo
      ],
      ['tit_id' => $tituloId],
      'Título cancelado com sucesso||Erro ao cancelar título'
    );
  }

  private function buscarOrigem(string $tipo, int $id): ?object
  {
    if ($id <= 0) {
      return null;
    }

    if ($tipo === 'fornecedor') {
      $response = (new FornecedorModel())->getFornecedorById($id);
      if (!$response->status || $response->total !== 1 || ($response->data[0]->for_status ?? 'Ativo') !== 'Ativo') {
        return null;
      }

      $registro = $response->data[0];
      return (object) [
        'id' => $id,
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
    }

    return null;
  }

  private function normalizarData($valor)
  {
    $valor = trim((string) ($valor ?? ''));
    if ($valor === '') {
      return null;
    }

    $data = \DateTime::createFromFormat('!Y-m-d', $valor);
    return $data && $data->format('Y-m-d') === $valor ? $valor : false;
  }

  private function normalizarValor($valor)
  {
    if (is_string($valor)) {
      $valor = trim(str_replace(['R$', ' '], '', $valor));
      if (str_contains($valor, ',')) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
      }
    }

    return is_numeric($valor) ? (float) $valor : false;
  }

  private function normalizarTexto($valor, int $limite): ?string
  {
    $valor = trim((string) ($valor ?? ''));
    return $valor === '' ? null : mb_substr($valor, 0, $limite);
  }

  private function getContextoOrganizacaoId(): int
  {
    return (int) ($this->contexto->organizacao->org_id ?? $this->usuarioLogado->usu_idorganizacao ?? 0);
  }

  private function getContextoEmpresaId(): int
  {
    return (int) ($this->contexto->empresa->emp_id ?? $this->usuarioLogado->usu_idempresa ?? 0);
  }

  private function pdoInTransaction(): bool
  {
    return method_exists($this->pdo, 'inTransaction') && $this->pdo->inTransaction();
  }

  private function atencao(string $texto): object
  {
    return (new SimpleResponse('Atenção', $texto))
      ->warning()
      ->noDestroy();
  }
}
