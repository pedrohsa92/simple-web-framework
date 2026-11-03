<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;
use Source\Service\OrdenacaoTexto;

class FinanceiroBasicoModel extends SimpleModel
{
  protected string $tabela = '';
  protected string $prefixo = '';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  private object $config;

  public function __construct(string $cadastro)
  {
    $config = self::getCadastro($cadastro);

    if (is_null($config)) {
      throw new \Exception("Cadastro financeiro '{$cadastro}' não configurado.");
    }

    $this->config = $config;
    $this->tabela = $config->tabela;
    $this->prefixo = $config->prefixo;
    $this->campoOrganizacao = "{$config->prefixo}_idorganizacao";
    $this->campoEmpresa = "{$config->prefixo}_idempresa";

    parent::__construct($this->tabela, $this->prefixo, 'id');
  }

  public function getConfig(): object
  {
    return $this->config;
  }

  /** Evita que dados financeiros e textos livres sejam copiados para o log detalhado. */
  protected function campoSensivelNoLog(string $campo): bool
  {
    $campo = strtolower($campo);

    return parent::campoSensivelNoLog($campo)
      || in_array($campo, ['ctf_saldo_inicial', 'ctf_dt_saldo_inicial'], true)
      || str_ends_with($campo, '_observacao')
      || str_ends_with($campo, '_descricao');
  }

  public function getRegistrosContexto(): object
  {
    return $this->rawQuery(
      "SELECT
         {$this->getTable()}.*,
         {$this->coluna('id')} AS id,
         {$this->coluna('tipocadastro')} AS escopo_financeiro
       FROM {$this->getTable()}
       WHERE {$this->getWhereEscopoContexto()}
       ORDER BY {$this->getOrderByNome()} ASC, {$this->coluna('id')} ASC",
      [
        'org_id' => $this->getContextoOrganizacaoId(),
        'org_id_empresa' => $this->getContextoOrganizacaoId(),
        'emp_id' => $this->getContextoEmpresaId()
      ],
      true,
      "Registros encontrados com sucesso||Erro ao buscar registros"
    );
  }

  public function getRegistroByIdContexto(int $id): object
  {
    return $this->rawQuery(
      "SELECT
         {$this->getTable()}.*,
         {$this->coluna('id')} AS id,
         {$this->coluna('tipocadastro')} AS escopo_financeiro
       FROM {$this->getTable()}
       WHERE {$this->coluna('id')} = :id
         AND {$this->getWhereEscopoContexto()}
       LIMIT 1",
      [
        'id' => $id,
        'org_id' => $this->getContextoOrganizacaoId(),
        'org_id_empresa' => $this->getContextoOrganizacaoId(),
        'emp_id' => $this->getContextoEmpresaId()
      ],
      true,
      "Registro encontrado com sucesso||Registro não encontrado"
    );
  }

  public function contarRegistrosContexto(bool $somenteAtivos = false): int
  {
    $whereStatus = $somenteAtivos ? " AND {$this->coluna('status')} = 'Ativo'" : '';
    $getTotal = $this->rawQuery(
      "SELECT COUNT({$this->coluna('id')}) AS total
       FROM {$this->getTable()}
       WHERE {$this->getWhereEscopoContexto()}{$whereStatus}",
      [
        'org_id' => $this->getContextoOrganizacaoId(),
        'org_id_empresa' => $this->getContextoOrganizacaoId(),
        'emp_id' => $this->getContextoEmpresaId()
      ],
      true,
      "Total encontrado com sucesso||Erro ao contar registros"
    );

    if (!$getTotal->status || $getTotal->total == 0) {
      return 0;
    }

    return (int) ($getTotal->data[0]->total ?? 0);
  }

  public function getOpcoesAtivas(): array
  {
    $response = $this->rawQuery(
      "SELECT
         {$this->coluna('id')} AS id,
         {$this->getOrderByNome()} AS nome,
         {$this->coluna('tipocadastro')} AS escopo_financeiro
       FROM {$this->getTable()}
       WHERE {$this->coluna('status')} = 'Ativo'
         AND {$this->getWhereEscopoContexto()}
       ORDER BY FIELD({$this->coluna('tipocadastro')}, 'Organizacao', 'Empresa'), {$this->getOrderByNome()} ASC",
      [
        'org_id' => $this->getContextoOrganizacaoId(),
        'org_id_empresa' => $this->getContextoOrganizacaoId(),
        'emp_id' => $this->getContextoEmpresaId()
      ],
      true,
      "Opções encontradas com sucesso||Erro ao buscar opções"
    );

    return $response->status ? ($response->data ?? []) : [];
  }

  public function getOpcoesPaisAtivas(): array
  {
    if ($this->config->modulo !== 'plano_conta') {
      return [];
    }

    $response = $this->rawQuery(
      "SELECT
         {$this->coluna('id')} AS id,
         {$this->coluna('nome')} AS nome,
         {$this->coluna('natureza')} AS natureza,
         {$this->coluna('tipocadastro')} AS escopo_financeiro
       FROM {$this->getTable()}
       WHERE {$this->coluna('status')} = 'Ativo'
         AND {$this->coluna('nivel')} = 'Grupo'
         AND {$this->getWhereEscopoContexto()}
       ORDER BY FIELD({$this->coluna('tipocadastro')}, 'Organizacao', 'Empresa'), {$this->getOrderByNome()} ASC",
      [
        'org_id' => $this->getContextoOrganizacaoId(),
        'org_id_empresa' => $this->getContextoOrganizacaoId(),
        'emp_id' => $this->getContextoEmpresaId()
      ],
      true,
      "Grupos encontrados com sucesso||Erro ao buscar grupos"
    );

    return $response->status ? ($response->data ?? []) : [];
  }

  public function salvarFinanceiroBasico(array $data, bool $gerenciarTransacao = true): object
  {
    $id = (int) ($data[$this->coluna('id')] ?? $data['id'] ?? 0);
    $registroAtual = null;

    if ($id > 0) {
      $getRegistro = $this->getRegistroByIdContexto($id);

      if (!$getRegistro->status || $getRegistro->total != 1) {
        return $this->erro("Registro não encontrado ou fora do contexto atual.");
      }

      $registroAtual = $getRegistro->data[0];
    }

    $data = $this->normalizarDados($data, $registroAtual);

    $validacao = $this->validarDados($data, $id);

    if (!$validacao->status) {
      return $validacao;
    }

    try {
      if ($gerenciarTransacao) {
        $this->beginTransaction();
      }

      if ((int) ($data[$this->coluna('padrao')] ?? 0) === 1) {
        $reset = $this->resetarPadraoEscopo(
          $id,
          (int) $data[$this->campoOrganizacao],
          (int) $data[$this->campoEmpresa],
          (string) $data[$this->coluna('tipocadastro')]
        );

        if (!$reset->status) {
          if ($gerenciarTransacao) {
            $this->rollback();
          }
          return $reset;
        }
      }

      if ($id > 0) {
        $resultado = $this->update($data, [$this->coluna('id') => $id], $this->config->textUpdate);
      } else {
        $resultado = $this->insert($data, $this->config->textInsert);

        if ($resultado->status) {
          $id = (int) ($resultado->lastInsertId ?? 0);
        }
      }

      if (!$resultado->status) {
        if ($gerenciarTransacao) {
          $this->rollback();
        }
        return $resultado;
      }

      if ($gerenciarTransacao) {
        $this->commit();
      }

      return (new SimpleResponse($resultado))
        ->setArrayParams([
          'location' => Helpers::url("financeiro/basico/{$this->config->modulo}/{$id}"),
          'data' => (object) [
            'id' => $id,
            'modulo' => $this->config->modulo
          ]
        ])
        ->noDestroy();
    } catch (\Throwable $e) {
      if ($gerenciarTransacao && $this->pdo->inTransaction()) {
        $this->rollback();
      }

      return $this->erro("Erro ao salvar cadastro financeiro: " . $e->getMessage());
    }
  }

  public function getEscoposFinanceirosPermitidos(?object $registro = null): array
  {
    if (!is_null($registro)) {
      $escopo = (string) ($registro->{$this->coluna('tipocadastro')} ?? '');
      return $this->tipoCadastroPermitido($escopo)
        ? [(object) ['value' => $escopo, 'label' => $this->rotuloEscopo($escopo)]]
        : [];
    }

    $escopoUsuario = $this->getEscopoMaximoUsuario();
    $escopos = [];
    $tipos = $this->config->tiposCadastro ?? ['Organizacao', 'Empresa'];

    if (
      in_array('Organizacao', $tipos, true)
      && in_array($escopoUsuario, ['Projeto', 'Organizacao'], true)
      && $this->getContextoOrganizacaoId() > 0
    ) {
      $escopos[] = (object) ['value' => 'Organizacao', 'label' => 'Organização'];
    }

    if (
      in_array('Empresa', $tipos, true)
      && $this->getContextoOrganizacaoId() > 0
      && $this->getContextoEmpresaId() > 0
    ) {
      $escopos[] = (object) ['value' => 'Empresa', 'label' => 'Empresa'];
    }

    return $escopos;
  }

  public function podeEditarRegistro(object $registro): bool
  {
    return $this->tipoCadastroPermitido((string) ($registro->{$this->coluna('tipocadastro')} ?? ''));
  }

  public function alterarStatusFinanceiroBasico(int $id, string $status, string $motivo = ''): object
  {
    $status = trim($status);
    $motivo = trim($motivo);
    if ($id <= 0 || !in_array($status, ['Ativo', 'Inativo'], true)) {
      return (new SimpleResponse('Atenção', 'Registro ou status inválido.'))->warning()->noDestroy();
    }

    $getRegistro = $this->getRegistroByIdContexto($id);
    if (!$getRegistro->status || empty($getRegistro->data[0])) {
      return $this->erro('Registro não encontrado ou fora do contexto financeiro ativo.', 'Atenção');
    }
    $registro = $getRegistro->data[0];
    if (!$this->podeEditarRegistro($registro)) {
      return $this->erro('Você não pode alterar este cadastro no escopo financeiro atual.', 'Permissão negada');
    }
    if (($registro->{$this->coluna('status')} ?? '') === $status) {
      return (new SimpleResponse('Sem alteração', 'O registro já está com este status.'))->warning()->noDestroy();
    }
    if ($status === 'Inativo' && mb_strlen($motivo) < 10) {
      return (new SimpleResponse('Motivo obrigatório', 'Informe um motivo com pelo menos 10 caracteres para inativar o registro.'))->warning()->noDestroy();
    }

    $log = json_decode((string) ($registro->{$this->coluna('log')} ?? '[]'), true);
    $log = is_array($log) ? $log : [];
    $detalhes = ['Trocou status de "' . (string) ($registro->{$this->coluna('status')} ?? '') . '" para "' . $status . '"'];
    if ($status === 'Inativo') {
      $detalhes[] = 'Motivo da inativação: ' . $motivo;
    }
    array_unshift($log, [
      'usu_id' => $this->usuarioLogado->usu_id ?? null,
      'usu_razaosocial' => $this->usuarioLogado->usu_razaosocial ?? 'Sistema',
      'acao' => 'atualizou o registro',
      'data' => date('d/m/Y H:i:s'),
      'detalhe' => $detalhes
    ]);
    $log = array_slice($log, 0, $this->limiteLog);

    $response = $this->rawQuery(
      "UPDATE {$this->getTable()}
       SET {$this->coluna('status')} = :status,
           {$this->coluna('statusclass')} = :statusclass,
           {$this->coluna('log')} = :log
       WHERE {$this->coluna('id')} = :id
         AND {$this->getWhereEscopoContexto()}",
      [
        'status' => $status,
        'statusclass' => $status === 'Ativo' ? 'success' : 'danger',
        'log' => json_encode($log, JSON_UNESCAPED_UNICODE),
        'id' => $id,
        'org_id' => $this->getContextoOrganizacaoId(),
        'org_id_empresa' => $this->getContextoOrganizacaoId(),
        'emp_id' => $this->getContextoEmpresaId()
      ],
      false,
      'Status atualizado com sucesso||Erro ao atualizar status'
    );
    if ($response->status) {
      $response->location = Helpers::url("financeiro/basico/{$this->config->modulo}");
    }
    return $response;
  }

  public static function getCadastrosComTotais(): array
  {
    $cadastros = [];

    foreach (self::getCadastros() as $modulo => $config) {
      $model = new self($modulo);
      $config->total = $model->contarRegistrosContexto();
      $config->totalAtivos = $model->contarRegistrosContexto(true);
      $config->totalInativos = max(0, $config->total - $config->totalAtivos);
      $cadastros[] = $config;
    }

    usort($cadastros, fn($a, $b) => OrdenacaoTexto::comparar($a->titulo, $b->titulo));

    return $cadastros;
  }

  public static function getCadastros(): array
  {
    return [
      'conta_financeira' => (object) [
        'modulo' => 'conta_financeira',
        'titulo' => 'Conta financeira',
        'descricao' => 'Caixa, conta bancária, cartão ou carteira digital.',
        'tabela' => 'conta_financeira',
        'prefixo' => 'ctf',
        'tiposCadastro' => ['Organizacao', 'Empresa'],
        'required' => ['ctf_nome', 'ctf_tipo'],
        'unique' => [['ctf_nome']],
        'fields' => ['ctf_status', 'ctf_nome', 'ctf_tipo', 'ctf_padrao', 'ctf_saldo_inicial', 'ctf_dt_saldo_inicial', 'ctf_observacao'],
        'textInsert' => 'Conta financeira inserida com sucesso||Erro ao salvar conta financeira',
        'textUpdate' => 'Conta financeira atualizada com sucesso||Erro ao atualizar conta financeira'
      ],
      'forma_pagamento' => (object) [
        'modulo' => 'forma_pagamento',
        'titulo' => 'Forma de pagamento',
        'descricao' => 'Dinheiro, Pix, cartão, boleto e outras formas.',
        'tabela' => 'forma_pagamento',
        'prefixo' => 'fpg',
        'tiposCadastro' => ['Organizacao', 'Empresa'],
        'required' => ['fpg_nome'],
        'unique' => [['fpg_nome']],
        'fields' => ['fpg_status', 'fpg_nome', 'fpg_padrao', 'fpg_aceita_parcela', 'fpg_observacao'],
        'textInsert' => 'Forma de pagamento inserida com sucesso||Erro ao salvar forma de pagamento',
        'textUpdate' => 'Forma de pagamento atualizada com sucesso||Erro ao atualizar forma de pagamento'
      ],
      'condicao_pgto' => (object) [
        'modulo' => 'condicao_pgto',
        'titulo' => 'Condição de pagamento',
        'descricao' => 'Parcelas, vencimentos e regras de pagamento.',
        'tabela' => 'condicao_pgto',
        'prefixo' => 'cpg',
        'tiposCadastro' => ['Organizacao', 'Empresa'],
        'required' => ['cpg_idformapagamento', 'cpg_nome', 'cpg_qtdparcela', 'cpg_intervalodias'],
        'unique' => [['cpg_idformapagamento', 'cpg_nome', 'cpg_qtdparcela']],
        'fields' => ['cpg_status', 'cpg_idformapagamento', 'cpg_nome', 'cpg_qtdparcela', 'cpg_intervalodias', 'cpg_padrao', 'cpg_observacao'],
        'textInsert' => 'Condição de pagamento inserida com sucesso||Erro ao salvar condição de pagamento',
        'textUpdate' => 'Condição de pagamento atualizada com sucesso||Erro ao atualizar condição de pagamento'
      ],
      'plano_conta' => (object) [
        'modulo' => 'plano_conta',
        'titulo' => 'Plano de contas',
        'descricao' => 'Classificação hierárquica de entradas e saídas.',
        'tabela' => 'plano_conta',
        'prefixo' => 'pct',
        'tiposCadastro' => ['Organizacao', 'Empresa'],
        'required' => ['pct_natureza', 'pct_nivel', 'pct_nome'],
        'unique' => [['pct_idpai', 'pct_nome'], ['pct_codigo']],
        'fields' => ['pct_status', 'pct_natureza', 'pct_nivel', 'pct_idpai', 'pct_codigo', 'pct_nome', 'pct_descricao', 'pct_padrao'],
        'textInsert' => 'Plano de contas inserido com sucesso||Erro ao salvar plano de contas',
        'textUpdate' => 'Plano de contas atualizado com sucesso||Erro ao atualizar plano de contas'
      ],
      'centro_custo' => (object) [
        'modulo' => 'centro_custo',
        'titulo' => 'Centro de custo',
        'descricao' => 'Apropriação operacional dos lançamentos.',
        'tabela' => 'centro_custo',
        'prefixo' => 'cdc',
        'tiposCadastro' => ['Organizacao', 'Empresa'],
        'required' => ['cdc_nome'],
        'unique' => [['cdc_nome'], ['cdc_codigo']],
        'fields' => ['cdc_status', 'cdc_nome', 'cdc_codigo', 'cdc_descricao', 'cdc_padrao'],
        'textInsert' => 'Centro de custo inserido com sucesso||Erro ao salvar centro de custo',
        'textUpdate' => 'Centro de custo atualizado com sucesso||Erro ao atualizar centro de custo'
      ]
    ];
  }

  public static function getCadastro(string $cadastro): ?object
  {
    $cadastros = self::getCadastros();

    return $cadastros[$cadastro] ?? null;
  }

  private function normalizarDados(array $data, ?object $registroAtual): array
  {
    $normalizado = [];
    $ehAtualizacao = !is_null($registroAtual);

    foreach ($this->config->fields as $campo) {
      if ($campo === $this->coluna('status')) {
        $status = (string) ($data[$campo] ?? ($registroAtual->{$campo} ?? 'Ativo'));
        $normalizado[$campo] = in_array($status, ['Ativo', 'Inativo'], true) ? $status : 'Ativo';
        $normalizado[$this->coluna('statusclass')] = $normalizado[$campo] === 'Ativo' ? 'success' : 'danger';
        continue;
      }

      if ($campo === $this->coluna('padrao')) {
        $normalizado[$campo] = array_key_exists($campo, $data)
          ? (!empty($data[$campo]) ? 1 : 0)
          : (int) ($registroAtual->{$campo} ?? 0);
        continue;
      }

      if (in_array($campo, ['fpg_aceita_parcela'], true)) {
        $normalizado[$campo] = !empty($data[$campo]) ? 1 : 0;
        continue;
      }

      $valor = array_key_exists($campo, $data)
        ? $data[$campo]
        : ($registroAtual->{$campo} ?? null);

      $normalizado[$campo] = $this->normalizarValorCampo($campo, $valor);
    }

    if ($ehAtualizacao) {
      $normalizado[$this->coluna('tipocadastro')] = (string) ($registroAtual->{$this->coluna('tipocadastro')} ?? '');
      $normalizado[$this->campoOrganizacao] = (int) ($registroAtual->{$this->campoOrganizacao} ?? 0);
      $normalizado[$this->campoEmpresa] = (int) ($registroAtual->{$this->campoEmpresa} ?? 0);

      // O saldo inicial e a hierarquia do plano representam a origem do cadastro
      // e não podem ser reescritos em uma edição comum.
      if ($this->config->modulo === 'conta_financeira') {
        $normalizado['ctf_saldo_inicial'] = (float) ($registroAtual->ctf_saldo_inicial ?? 0);
        $normalizado['ctf_dt_saldo_inicial'] = $registroAtual->ctf_dt_saldo_inicial ?? null;
      }

      if ($this->config->modulo === 'plano_conta') {
        $normalizado['pct_natureza'] = $registroAtual->pct_natureza ?? null;
        $normalizado['pct_nivel'] = $registroAtual->pct_nivel ?? null;
        $normalizado['pct_idpai'] = $registroAtual->pct_idpai ?? null;
      }

      return $normalizado;
    }

    $escopo = (string) ($data['escopo_financeiro'] ?? $this->getEscopoFinanceiroPadraoPermitido());
    $normalizado[$this->coluna('tipocadastro')] = $escopo;

    if ($escopo === 'Organizacao') {
      $normalizado[$this->campoOrganizacao] = $this->getContextoOrganizacaoId();
      $normalizado[$this->campoEmpresa] = 0;
    } else {
      $normalizado[$this->campoOrganizacao] = $this->getContextoOrganizacaoId();
      $normalizado[$this->campoEmpresa] = $this->getContextoEmpresaId();
    }

    return $normalizado;
  }

  private function normalizarValorCampo(string $campo, $valor)
  {
    if (in_array($campo, [
      'ctf_saldo_inicial'
    ], true)) {
      return $valor === '' || is_null($valor) ? 0 : Helpers::convertToFloat($valor);
    }

    if (in_array($campo, [
      'cpg_qtdparcela',
      'cpg_intervalodias'
    ], true)) {
      return $valor === '' || is_null($valor) ? 0 : $valor;
    }

    if (strpos($campo, '_id') !== false) {
      $valor = (int) ($valor ?? 0);
      return $valor > 0 ? $valor : null;
    }

    $valor = trim((string) ($valor ?? ''));

    return $valor === '' ? null : $valor;
  }

  private function validarDados(array &$data, int $id): object
  {
    foreach ($this->config->required as $campo) {
      if (trim((string) ($data[$campo] ?? '')) === '') {
        return $this->erro("Informe os campos obrigatórios.");
      }
    }

    $tipoCadastro = (string) ($data[$this->coluna('tipocadastro')] ?? '');
    if (!$this->tipoCadastroPermitido($tipoCadastro)) {
      return $this->erro("Você não pode gerenciar financeiro no escopo {$tipoCadastro}.", "Permissão negada");
    }

    if ($tipoCadastro === 'Organizacao' && ((int) $data[$this->campoOrganizacao] <= 0 || (int) $data[$this->campoEmpresa] !== 0)) {
      return $this->erro("Organização ativa não encontrada no contexto.");
    }

    if ($tipoCadastro === 'Empresa' && ((int) $data[$this->campoOrganizacao] <= 0 || (int) $data[$this->campoEmpresa] <= 0)) {
      return $this->erro("Empresa ativa não encontrada no contexto.");
    }

    if ($this->config->modulo === 'conta_financeira') {
      if (!in_array((string) ($data['ctf_tipo'] ?? ''), ['Caixa', 'Conta corrente', 'Conta poupanca', 'Cartao', 'Carteira digital', 'Outra'], true)) {
        return $this->erro("Tipo de conta financeira inválido.");
      }

      if (!is_numeric($data['ctf_saldo_inicial'] ?? null)) {
        return $this->erro("Saldo inicial inválido.");
      }

      if (!empty($data['ctf_dt_saldo_inicial']) && !$this->dataValida((string) $data['ctf_dt_saldo_inicial'])) {
        return $this->erro("Data do saldo inicial inválida.");
      }
    }

    if ($this->config->modulo === 'condicao_pgto') {
      if ((int) ($data['cpg_idformapagamento'] ?? 0) <= 0) {
        return $this->erro("Selecione uma forma de pagamento.");
      }

      if ((int) ($data['cpg_qtdparcela'] ?? 0) < 1) {
        return $this->erro("A quantidade de parcelas deve ser maior que zero.");
      }

      if ((int) ($data['cpg_intervalodias'] ?? -1) < 0) {
        return $this->erro("O intervalo entre parcelas não pode ser negativo.");
      }

      $formaPagamento = new self('forma_pagamento');
      $getFormaPagamento = $formaPagamento->getRegistroByIdContexto((int) $data['cpg_idformapagamento']);
      if (!$getFormaPagamento->status || $getFormaPagamento->total !== 1 || ($getFormaPagamento->data[0]->fpg_status ?? '') !== 'Ativo') {
        return $this->erro("A forma de pagamento informada está inválida ou inativa.");
      }
    }

    if ($this->config->modulo === 'plano_conta') {
      $nivel = (string) ($data['pct_nivel'] ?? '');
      $paiId = (int) ($data['pct_idpai'] ?? 0);

      if (!in_array($nivel, ['Grupo', 'Item'], true)) {
        return $this->erro("Nível do plano de contas inválido.");
      }

      if ($nivel === 'Grupo') {
        $data['pct_idpai'] = null;
      } elseif ($paiId <= 0) {
        return $this->erro("Para o nível Item, selecione um grupo pai.");
      }

      if ($paiId > 0) {
        if ($id > 0 && $paiId === $id) {
          return $this->erro("O plano de contas não pode ser pai dele mesmo.");
        }

        $escoposPaiPermitidos = $tipoCadastro === 'Organizacao'
          ? "'Organizacao'"
          : "'Organizacao', 'Empresa'";

        $getPai = $this->rawQuery(
          "SELECT {$this->coluna('id')}, {$this->coluna('natureza')}
           FROM {$this->getTable()}
           WHERE {$this->coluna('id')} = :id
             AND {$this->coluna('status')} = 'Ativo'
             AND {$this->coluna('nivel')} = 'Grupo'
             AND {$this->coluna('tipocadastro')} IN ({$escoposPaiPermitidos})
             AND {$this->getWhereEscopoContexto()}
           LIMIT 1",
          [
            'id' => $paiId,
            'org_id' => $this->getContextoOrganizacaoId(),
            'org_id_empresa' => $this->getContextoOrganizacaoId(),
            'emp_id' => $this->getContextoEmpresaId()
          ],
          true,
          "Grupo encontrado com sucesso||Grupo pai inválido ou inativo"
        );

        if (!$getPai->status || $getPai->total !== 1) {
          return $this->erro("Grupo pai inválido ou inativo.");
        }

        $data['pct_natureza'] = $getPai->data[0]->pct_natureza;
      }
    }

    return $this->validarUnicidade($data, $id);
  }

  private function validarUnicidade(array $data, int $id): object
  {
    foreach ($this->config->unique as $indice => $grupo) {
      $conditions = [
        "{$this->campoOrganizacao} = :idorganizacao",
        "{$this->campoEmpresa} = :idempresa"
      ];

      $params = [
        'idorganizacao' => (int) ($data[$this->campoOrganizacao] ?? 0),
        'idempresa' => (int) ($data[$this->campoEmpresa] ?? 0)
      ];

      $temValor = false;
      foreach ($grupo as $campo) {
        $param = 'unique_' . $indice . '_' . $campo;

        if (is_null($data[$campo] ?? null)) {
          $conditions[] = "{$campo} IS NULL";
        } else {
          $conditions[] = "{$campo} = :{$param}";
          $params[$param] = $data[$campo];
          $temValor = true;
        }
      }

      // Índices UNIQUE do MySQL permitem múltiplos NULL. A validação só
      // precisa consultar a constraint quando existe algum valor informado.
      if (!$temValor) {
        continue;
      }

      if ($id > 0) {
        $conditions[] = "{$this->coluna('id')} != :id";
        $params['id'] = $id;
      }

      $getDuplicado = $this->rawQuery(
        "SELECT {$this->coluna('id')}
         FROM {$this->getTable()}
         WHERE " . implode(' AND ', $conditions) . "
         LIMIT 1",
        $params,
        true,
        "Unicidade validada com sucesso||Erro ao validar unicidade"
      );

      if (!$getDuplicado->status) {
        return $getDuplicado;
      }

      if ($getDuplicado->total > 0) {
        return $this->erro("Já existe um registro com estes dados neste escopo.");
      }
    }

    return (new SimpleResponse("Validacao", "Dados validos"))
      ->sucesso()
      ->noDestroy();
  }

  private function resetarPadraoEscopo(int $id, int $orgId, int $empId, string $tipoCadastro): object
  {
    $params = [
      'org_id' => $orgId,
      'emp_id' => $empId
    ];

    $whereId = '';

    if ($id > 0) {
      $whereId = "AND {$this->coluna('id')} != :id";
      $params['id'] = $id;
    }

    return $this->rawQuery(
      "UPDATE {$this->getTable()}
       SET {$this->coluna('padrao')} = 0
       WHERE {$this->campoOrganizacao} = :org_id
         AND {$this->campoEmpresa} = :emp_id
         AND {$this->coluna('tipocadastro')} = :tipocadastro
         {$whereId}",
      $params + ['tipocadastro' => $tipoCadastro],
      false,
      "Padrão resetado com sucesso||Erro ao resetar padrão"
    );
  }

  private function validarEscopoFinanceiroPermitido(string $escopo): object
  {
    if ($this->tipoCadastroPermitido($escopo)) {
      return (new SimpleResponse("Validacao", "Escopo permitido"))
        ->sucesso()
        ->noDestroy();
    }

    return $this->erro("Você não pode gerenciar financeiro no escopo {$escopo}.", "Permissão negada");
  }

  private function getEscopoFinanceiroPadraoPermitido(): string
  {
    $escopos = $this->getEscoposFinanceirosPermitidos();

    return $escopos[0]->value ?? ($this->config->tiposCadastro[0] ?? 'Empresa');
  }

  private function getOrderByNome(): string
  {
    return $this->coluna('nome');
  }

  private function getWhereEscopoContexto(): string
  {
    $tipos = $this->config->tiposCadastro ?? ['Organizacao', 'Empresa'];
    $condicoes = [];

    if ($this->getContextoOrganizacaoId() > 0 && in_array('Organizacao', $tipos, true)) {
      $condicoes[] = "(
        {$this->campoOrganizacao} = :org_id
        AND {$this->campoEmpresa} = 0
        AND {$this->coluna('tipocadastro')} = 'Organizacao'
      )";
    }

    if ($this->getContextoOrganizacaoId() > 0 && $this->getContextoEmpresaId() > 0 && in_array('Empresa', $tipos, true)) {
      $condicoes[] = "(
        {$this->campoOrganizacao} = :org_id_empresa
        AND {$this->campoEmpresa} = :emp_id
        AND {$this->coluna('tipocadastro')} = 'Empresa'
      )";
    }

    return empty($condicoes) ? '1 = 0' : '(' . implode(' OR ', $condicoes) . ')';
  }

  private function tipoCadastroPermitido(string $tipo): bool
  {
    if (!in_array($tipo, $this->config->tiposCadastro ?? [], true)) {
      return false;
    }

    if ($tipo === 'Organizacao') {
      return in_array($this->getEscopoMaximoUsuario(), ['Projeto', 'Organizacao'], true)
        && $this->getContextoOrganizacaoId() > 0;
    }

    return $tipo === 'Empresa'
      && $this->getContextoOrganizacaoId() > 0
      && $this->getContextoEmpresaId() > 0;
  }

  private function rotuloEscopo(string $tipo): string
  {
    return match ($tipo) {
      'Organizacao' => 'Organização',
      'Empresa' => 'Empresa',
      default => $tipo
    };
  }

  private function dataValida(string $valor): bool
  {
    $data = \DateTime::createFromFormat('!Y-m-d', $valor);
    $erros = \DateTime::getLastErrors();

    return $data !== false
      && ($erros === false || ($erros['warning_count'] === 0 && $erros['error_count'] === 0))
      && $data->format('Y-m-d') === $valor;
  }

  private function getContextoOrganizacaoId(): int
  {
    return (int) ($this->contexto->organizacao->org_id ?? 0);
  }

  private function getContextoEmpresaId(): int
  {
    return (int) ($this->contexto->empresa->emp_id ?? 0);
  }

  private function getEscopoMaximoUsuario(): string
  {
    return (string) (
      $this->usuarioLogado->escopo_maximo
      ?? $this->usuarioLogado->nva_escopo_maximo
      ?? 'Empresa'
    );
  }

  private function isContextoCentral(): bool
  {
    return $this->getContextoOrganizacaoId() === $this->organizacaoCentralId
      && $this->getContextoEmpresaId() === $this->empresaCentralId;
  }

  private function erro(string $text, string $title = "Erro"): object
  {
    return (new SimpleResponse($title, $text))
      ->erro()
      ->noDestroy();
  }
}
