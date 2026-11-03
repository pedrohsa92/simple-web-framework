<?php

namespace Source\Core;

use PDO;
use PDOException;
use Source\Core\SimpleConnect;
use Source\Core\SimpleResponse;
use Source\Core\SimpleSession;

class SimpleModel
{
  protected string $tabela = '';
  protected string $prefixo = '';
  protected string $identificador = 'id';

  /*
    Escopo de CONTEXTO do model:

    nenhum:
      Não aplica filtro automático de CONTEXTO.
      Uso: tabelas globais, projeto, cadastros realmente públicos.

    organizacao:
      Aplica filtro pela organização ativa do CONTEXTO.
      Uso: tabelas cujo dado pertence à organização, mas não a uma empresa específica.

    empresa:
      Aplica filtro pela organização e empresa ativa do CONTEXTO.
      Uso: tabelas operacionais como serviço, pedido, financeiro, estoque ou outro
      domínio definido pela aplicação.

    hierarquico:
      Aplica regra por alcance Projeto / Organização / Empresa.
      Uso: tabelas como nível de acesso, permissão e usuários, quando o dado pode existir em mais de uma camada.

    manual:
      Não aplica filtro automático.
      Uso: consultas especiais, relatórios complexos e models que controlam o escopo dentro dos próprios métodos.
  */
  protected string $escopoContexto = 'nenhum';

  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';
  protected string $campoEscopoHierarquico = '';

  protected int $organizacaoCentralId = 1;
  protected int $empresaCentralId = 1;

  protected bool $usarLog = true;
  protected string $modoLog = 'simples';
  protected int $limiteLog = 50;

  protected PDO $pdo;
  protected string $table;

  protected SimpleSession $session;
  protected ?object $projeto = null;
  protected ?object $usuarioLogado = null;
  protected ?object $contexto = null;

  private array $queryColumns = ['*'];
  private array $queryJoins = [];
  private array $queryWheres = [];
  private array $queryParams = [];
  private array $queryOrderBy = [];
  private array $queryGroupBy = [];
  private ?int $queryLimit = null;
  private ?int $queryOffset = null;

  public function __construct(?string $tabela = null, ?string $prefixo = null, string $identificador = 'id')
  {
    if (!is_null($tabela)) {
      $this->tabela = $tabela;
    }

    if (!is_null($prefixo)) {
      $this->prefixo = $prefixo;
    }

    $this->identificador = $identificador;
    $this->table = DB_PREFIX . '_' . $this->tabela;
    $this->pdo = SimpleConnect::getInstance();

    $this->session = new SimpleSession();
    $this->projeto = $this->session->get('PROJETO') ?? null;
    $this->usuarioLogado = $this->session->get('USUARIO') ?? null;
    $this->contexto = $this->session->get('CONTEXTO') ?? null;
  }

  public function getTable(): string
  {
    return $this->table;
  }

  public function getPrefixo(): string
  {
    return $this->prefixo;
  }

  public function getIdentificador(): string
  {
    return $this->identificador;
  }

  public function coluna(string $campo): string
  {
    return $this->prefixo . '_' . $campo;
  }

  public function beginTransaction(): void
  {
    $this->pdo->beginTransaction();
  }

  public function commit(): void
  {
    $this->pdo->commit();
  }

  public function rollback(): void
  {
    $this->pdo->rollBack();
  }

  public function insert(array $data, string $text = "Informação salva com sucesso||Erro ao salvar informação"): object
  {
    $erroContexto = $this->validarContextoNoInsert($data);

    if (!is_null($erroContexto)) {
      return $erroContexto;
    }

    $data = $this->aplicarContextoNoInsert($data);
    $data = $this->aplicarLogInsert($data);

    $columns = array_keys($data);
    $placeholders = array_map(fn($col) => ':' . $col, $columns);

    $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

    return $this->execute($sql, $data, false, $text);
  }

  public function update(array $data, $where, string $text = "Informação atualizada com sucesso||Erro ao atualizar informação"): object
  {
    $registroAnterior = $this->select($where);

    if (!$registroAnterior->status || $registroAnterior->total == 0) {
      return (new SimpleResponse("Erro", explode('||', $text)[1] ?? "Erro ao atualizar informação"))
        ->erro()
        ->setArrayParams(['registroAnterior' => $registroAnterior])
        ->noDestroy();
    }

    $data = $this->aplicarLogUpdate($data, $registroAnterior->data[0]);

    $set = [];
    $params = [];

    foreach ($data as $key => $value) {
      $param = 'set_' . $key;
      $set[] = "{$key} = :{$param}";
      $params[$param] = $value;
    }

    [$whereSql, $whereParams] = $this->buildWhere($where, 'whr');
    [$contextoSql, $contextoParams] = $this->buildContextoWhere($whereSql === '' ? 'WHERE' : 'AND');

    $params = array_merge($params, $whereParams, $contextoParams);

    $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " {$whereSql} {$contextoSql}";

    return $this->execute($sql, $params, false, $text);
  }

  public function delete($where, string $text = "Informação deletada com sucesso||Erro ao deletar informação"): object
  {
    [$whereSql, $params] = $this->buildWhere($where, 'del');
    [$contextoSql, $contextoParams] = $this->buildContextoWhere($whereSql === '' ? 'WHERE' : 'AND');

    $params = array_merge($params, $contextoParams);

    $sql = "DELETE FROM {$this->table} {$whereSql} {$contextoSql}";

    return $this->execute($sql, $params, false, $text);
  }

  public function select($where = [], string $columns = '*', string $join = '', array $params = [], string $text = "Informação encontrada com sucesso||Erro ao encontrar informação"): object
  {
    [$whereSql, $whereParams] = $this->buildWhere($where, 'sel');
    [$contextoSql, $contextoParams] = $this->buildContextoWhere($whereSql === '' ? 'WHERE' : 'AND');

    $queryParams = array_merge($params, $whereParams, $contextoParams);

    $sql = "SELECT {$columns} FROM {$this->table} {$join} {$whereSql} {$contextoSql}";

    return $this->execute($sql, $queryParams, true, $text);
  }

  public function findById($id, string $text = "Informação encontrada com sucesso||Erro ao encontrar informação"): object
  {
    return $this->select([
      $this->prefixo . '_' . $this->identificador => $id
    ], '*', '', [], $text);
  }

  public function rawQuery(string $sql, array $params = [], bool $fetch = true, string $text = "Operação realizada com sucesso||Erro ao executar consulta"): object
  {
    return $this->execute($sql, $params, $fetch, $text);
  }

  public function columns($columns): self
  {
    $this->queryColumns = is_array($columns) ? $columns : [$columns];
    return $this;
  }

  public function where($column, $value = null, string $operator = '='): self
  {
    if (is_array($column)) {
      foreach ($column as $key => $item) {
        $this->where($key, $item);
      }

      return $this;
    }

    $param = $this->createParam($column);

    $this->queryWheres[] = [
      'type' => 'AND',
      'sql' => "{$column} {$operator} :{$param}"
    ];

    $this->queryParams[$param] = $value;

    return $this;
  }

  public function orWhere(string $column, $value = null, string $operator = '='): self
  {
    $param = $this->createParam($column);

    $this->queryWheres[] = [
      'type' => 'OR',
      'sql' => "{$column} {$operator} :{$param}"
    ];

    $this->queryParams[$param] = $value;

    return $this;
  }

  public function whereLike(string $column, string $value): self
  {
    return $this->where($column, "%{$value}%", 'LIKE');
  }

  public function whereIn(string $column, array $values): self
  {
    $params = [];

    foreach ($values as $value) {
      $param = $this->createParam($column);
      $params[] = ':' . $param;
      $this->queryParams[$param] = $value;
    }

    $this->queryWheres[] = [
      'type' => 'AND',
      'sql' => "{$column} IN (" . implode(', ', $params) . ")"
    ];

    return $this;
  }

  public function join(string $table, string $on): self
  {
    $this->queryJoins[] = "JOIN {$table} ON {$on}";
    return $this;
  }

  public function leftJoin(string $table, string $on): self
  {
    $this->queryJoins[] = "LEFT JOIN {$table} ON {$on}";
    return $this;
  }

  public function orderBy(string $column, string $direction = 'ASC'): self
  {
    $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
    $this->queryOrderBy[] = "{$column} {$direction}";
    return $this;
  }

  public function orderByRaw(string $sql): self
  {
    $this->queryOrderBy[] = $sql;
    return $this;
  }

  public function groupBy(string $column): self
  {
    $this->queryGroupBy[] = $column;
    return $this;
  }

  public function limit(int $limit): self
  {
    $this->queryLimit = $limit;
    return $this;
  }

  public function offset(int $offset): self
  {
    $this->queryOffset = $offset;
    return $this;
  }

  public function get(string $text = "Informação encontrada com sucesso||Erro ao encontrar informação"): object
  {
    $sql = $this->buildSelectSql();
    $params = $this->queryParams;

    $this->resetQuery();

    return $this->execute($sql, $params, true, $text);
  }

  public function first(string $text = "Informação encontrada com sucesso||Erro ao encontrar informação"): object
  {
    $this->limit(1);
    return $this->get($text);
  }

  public function count(string $column = '*', string $alias = 'total'): object
  {
    $this->columns("COUNT({$column}) AS {$alias}");
    return $this->first();
  }

  public function filtrarCampos(array $data): array
  {
    $filtrado = [];

    foreach ($data as $key => $value) {
      if (strpos($key, $this->prefixo . '_') === 0) {
        $filtrado[$key] = $value;
      }
    }

    return $filtrado;
  }

  private function execute(string $sql, array $params = [], bool $fetch = false, ?string $text = null): object
  {
    try {
      $stmt = $this->pdo->prepare($sql);

      foreach ($params as $key => $value) {
        $stmt->bindValue(':' . ltrim((string) $key, ':'), $value);
      }

      $stmt->execute();

      $data = $fetch ? $stmt->fetchAll(PDO::FETCH_OBJ) : null;
      $text = $this->resolveText($text, true);

      return (new SimpleResponse("ATENÇÃO", $text))
        ->setArrayParams([
          'data' => $data,
          'total' => count($data ?? []),
          'totalLinha' => $stmt->rowCount(),
          'lastInsertId' => (int) $this->pdo->lastInsertId(),
          'error' => null,
          'errorInfo' => null,
          'debug' => (object) [
            'query' => $sql,
            'params' => $params,
            'fetch' => $fetch,
            'executionTime' => microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true)),
            'timestamp' => date('Y-m-d H:i:s')
          ]
        ])
        ->sucesso()
        ->noDestroy();
    } catch (PDOException $e) {
      $text = $this->resolveText($text, false);

      return (new SimpleResponse("ATENÇÃO", $text))
        ->setArrayParams([
          'data' => null,
          'total' => 0,
          'totalLinha' => 0,
          'lastInsertId' => null,
          'error' => $e,
          'errorInfo' => $e->getMessage(),
          'debug' => (object) [
            'query' => $sql,
            'params' => $params,
            'fetch' => $fetch,
            'executionTime' => microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true)),
            'timestamp' => date('Y-m-d H:i:s')
          ]
        ])
        ->erro()
        ->noDestroy();
    }
  }

  private function buildSelectSql(): string
  {
    $columns = implode(', ', $this->queryColumns);
    $joins = implode(' ', $this->queryJoins);
    $where = $this->buildChainWhere();

    [$contextoSql, $contextoParams] = $this->buildContextoWhere($where === '' ? 'WHERE' : 'AND');
    $this->queryParams = array_merge($this->queryParams, $contextoParams);

    $groupBy = !empty($this->queryGroupBy) ? 'GROUP BY ' . implode(', ', $this->queryGroupBy) : '';
    $orderBy = !empty($this->queryOrderBy) ? 'ORDER BY ' . implode(', ', $this->queryOrderBy) : '';
    $limit = !is_null($this->queryLimit) ? 'LIMIT ' . $this->queryLimit : '';
    $offset = !is_null($this->queryOffset) ? 'OFFSET ' . $this->queryOffset : '';

    return "SELECT {$columns} FROM {$this->table} {$joins} {$where} {$contextoSql} {$groupBy} {$orderBy} {$limit} {$offset}";
  }

  private function buildChainWhere(): string
  {
    if (empty($this->queryWheres)) {
      return '';
    }

    $sql = 'WHERE ';

    foreach ($this->queryWheres as $index => $where) {
      $prefix = $index === 0 ? '' : $where['type'] . ' ';
      $sql .= $prefix . $where['sql'] . ' ';
    }

    return trim($sql);
  }

  private function buildWhere($where, string $prefix): array
  {
    if (is_string($where) && trim($where) !== '') {
      return ['WHERE ' . trim($where), []];
    }

    if (!is_array($where) || empty($where)) {
      return ['', []];
    }

    $conditions = [];
    $params = [];

    foreach ($where as $column => $value) {
      $param = $prefix . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $column);
      $conditions[] = "{$column} = :{$param}";
      $params[$param] = $value;
    }

    return ['WHERE ' . implode(' AND ', $conditions), $params];
  }

  private function buildContextoWhere(string $prefixSql = 'AND'): array
  {
    $escopo = $this->getEscopoContextoEfetivo();

    if (in_array($escopo, ['nenhum', 'manual'], true)) {
      return ['', []];
    }

    if ($escopo === 'organizacao') {
      return $this->buildContextoOrganizacaoWhere($prefixSql);
    }

    if ($escopo === 'empresa') {
      return $this->buildContextoEmpresaWhere($prefixSql);
    }

    if ($escopo === 'hierarquico') {
      return $this->buildContextoHierarquicoWhere($prefixSql);
    }

    return ["{$prefixSql} 1 = 0", []];
  }

  private function buildContextoOrganizacaoWhere(string $prefixSql): array
  {
    if ($this->campoOrganizacao === '') {
      return ["{$prefixSql} 1 = 0", []];
    }

    $orgId = $this->getContextoOrganizacaoId();

    if ($orgId <= 0) {
      return ["{$prefixSql} 1 = 0", []];
    }

    return [
      "{$prefixSql} {$this->campoOrganizacao} = :__ctx_org_id",
      [
        '__ctx_org_id' => $orgId
      ]
    ];
  }

  private function buildContextoEmpresaWhere(string $prefixSql): array
  {
    $params = [];
    $conditions = [];

    if ($this->campoOrganizacao !== '') {
      $orgId = $this->getContextoOrganizacaoId();

      if ($orgId <= 0) {
        return ["{$prefixSql} 1 = 0", []];
      }

      $conditions[] = "{$this->campoOrganizacao} = :__ctx_org_id";
      $params['__ctx_org_id'] = $orgId;
    }

    if ($this->campoEmpresa === '') {
      return ["{$prefixSql} 1 = 0", []];
    }

    $empId = $this->getContextoEmpresaId();

    if ($empId <= 0) {
      return ["{$prefixSql} 1 = 0", []];
    }

    $conditions[] = "{$this->campoEmpresa} = :__ctx_emp_id";
    $params['__ctx_emp_id'] = $empId;

    return [
      "{$prefixSql} " . implode(' AND ', $conditions),
      $params
    ];
  }

  private function buildContextoHierarquicoWhere(string $prefixSql): array
  {
    if (
      $this->campoOrganizacao === ''
      || $this->campoEmpresa === ''
      || $this->campoEscopoHierarquico === ''
    ) {
      return ["{$prefixSql} 1 = 0", []];
    }

    $orgId = $this->getContextoOrganizacaoId();
    $empId = $this->getContextoEmpresaId();
    $escopoUsuario = $this->getEscopoMaximoUsuario();

    $conditions = [];
    $params = [];

    if ($escopoUsuario === 'Projeto' && $this->isContextoCentral()) {
      $conditions[] = "(
        {$this->campoEscopoHierarquico} = 'Projeto'
        AND {$this->campoOrganizacao} = 0
        AND {$this->campoEmpresa} = 0
      )";
    }

    if (in_array($escopoUsuario, ['Projeto', 'Organizacao'], true) && $orgId > 0) {
      $conditions[] = "(
        {$this->campoEscopoHierarquico} = 'Organizacao'
        AND {$this->campoOrganizacao} = :__ctx_org_id_hier
        AND {$this->campoEmpresa} = 0
      )";

      $params['__ctx_org_id_hier'] = $orgId;
    }

    if ($orgId > 0 && $empId > 0) {
      $conditions[] = "(
        {$this->campoEscopoHierarquico} = 'Empresa'
        AND {$this->campoOrganizacao} = :__ctx_org_id_emp_hier
        AND {$this->campoEmpresa} = :__ctx_emp_id_hier
      )";

      $params['__ctx_org_id_emp_hier'] = $orgId;
      $params['__ctx_emp_id_hier'] = $empId;
    }

    if (empty($conditions)) {
      return ["{$prefixSql} 1 = 0", []];
    }

    return [
      "{$prefixSql} (" . implode(' OR ', $conditions) . ")",
      $params
    ];
  }

  private function getEscopoContextoEfetivo(): string
  {
    if ($this->escopoContexto !== 'nenhum') {
      return $this->escopoContexto;
    }

    if ($this->campoEmpresa !== '') {
      return 'empresa';
    }

    return 'nenhum';
  }

  private function getContextoOrganizacaoId(): int
  {
    if (!empty($this->contexto->organizacao->org_id)) {
      return (int) $this->contexto->organizacao->org_id;
    }

    if (!empty($this->usuarioLogado->organizacao->org_id)) {
      return (int) $this->usuarioLogado->organizacao->org_id;
    }

    if (!empty($this->usuarioLogado->usu_idorganizacao)) {
      return (int) $this->usuarioLogado->usu_idorganizacao;
    }

    if (defined('CONF_ORG_ID') && trim((string) CONF_ORG_ID) !== '') {
      return (int) CONF_ORG_ID;
    }

    return 0;
  }

  private function getContextoEmpresaId(): int
  {
    if (!empty($this->contexto->empresa->emp_id)) {
      return (int) $this->contexto->empresa->emp_id;
    }

    if (!empty($this->usuarioLogado->empresa->emp_id)) {
      return (int) $this->usuarioLogado->empresa->emp_id;
    }

    if (!empty($this->usuarioLogado->usu_idempresa)) {
      return (int) $this->usuarioLogado->usu_idempresa;
    }

    return 0;
  }

  private function getEscopoMaximoUsuario(): string
  {
    if (is_null($this->usuarioLogado)) {
      return 'Empresa';
    }

    if (!empty($this->usuarioLogado->escopo_maximo)) {
      return $this->usuarioLogado->escopo_maximo;
    }

    if (!empty($this->usuarioLogado->nva_escopo_maximo)) {
      return $this->usuarioLogado->nva_escopo_maximo;
    }

    $permissoes = (array) ($this->usuarioLogado->permissoes ?? []);

    if (
      isset($permissoes['projeto.visualizar'])
      || isset($permissoes['projeto.atualizar'])
    ) {
      return 'Projeto';
    }

    if (
      isset($permissoes['organizacao.visualizar'])
      || isset($permissoes['organizacao.criar'])
      || isset($permissoes['organizacao.atualizar'])
    ) {
      return 'Organizacao';
    }

    return 'Empresa';
  }

  private function isContextoCentral(): bool
  {
    return $this->getContextoOrganizacaoId() === $this->organizacaoCentralId
      && $this->getContextoEmpresaId() === $this->empresaCentralId;
  }

  private function validarContextoNoInsert(array $data): ?object
  {
    $escopo = $this->getEscopoContextoEfetivo();

    if ($escopo !== 'hierarquico') {
      return null;
    }

    return $this->validarContextoHierarquicoNoInsert($data);
  }

  private function validarContextoHierarquicoNoInsert(array $data): ?object
  {
    if (
      $this->campoOrganizacao === ''
      || $this->campoEmpresa === ''
      || $this->campoEscopoHierarquico === ''
    ) {
      return $this->erroModel("Model hierárquico configurado incorretamente.");
    }

    $escopoRegistro = $data[$this->campoEscopoHierarquico] ?? '';

    if (!in_array($escopoRegistro, ['Projeto', 'Organizacao', 'Empresa'], true)) {
      return $this->erroModel("Escopo hierárquico inválido ou não informado.");
    }

    $orgId = $this->getContextoOrganizacaoId();
    $empId = $this->getContextoEmpresaId();
    $escopoUsuario = $this->getEscopoMaximoUsuario();

    if ($escopoRegistro === 'Projeto') {
      if ($escopoUsuario !== 'Projeto') {
        return $this->erroModel("Usuário sem alcance para criar registro de escopo Projeto.");
      }

      if (!$this->isContextoCentral()) {
        return $this->erroModel("Registro de escopo Projeto só pode ser criado no contexto central.");
      }

      return null;
    }

    if ($escopoRegistro === 'Organizacao') {
      if (!in_array($escopoUsuario, ['Projeto', 'Organizacao'], true)) {
        return $this->erroModel("Usuário sem alcance para criar registro de escopo Organização.");
      }

      if ($orgId <= 0) {
        return $this->erroModel("Organização ativa não encontrada no contexto.");
      }

      return null;
    }

    if ($escopoRegistro === 'Empresa') {
      if ($orgId <= 0 || $empId <= 0) {
        return $this->erroModel("Organização ou empresa ativa não encontrada no contexto.");
      }

      return null;
    }

    return $this->erroModel("Escopo hierárquico inválido.");
  }

  private function aplicarContextoNoInsert(array $data): array
  {
    $escopo = $this->getEscopoContextoEfetivo();

    if (in_array($escopo, ['nenhum', 'manual'], true)) {
      return $data;
    }

    if ($escopo === 'organizacao') {
      return $this->aplicarContextoOrganizacaoNoInsert($data);
    }

    if ($escopo === 'empresa') {
      return $this->aplicarContextoEmpresaNoInsert($data);
    }

    if ($escopo === 'hierarquico') {
      return $this->aplicarContextoHierarquicoNoInsert($data);
    }

    return $data;
  }

  private function aplicarContextoOrganizacaoNoInsert(array $data): array
  {
    if ($this->campoOrganizacao === '' || isset($data[$this->campoOrganizacao])) {
      return $data;
    }

    $orgId = $this->getContextoOrganizacaoId();

    if ($orgId > 0) {
      $data[$this->campoOrganizacao] = $orgId;
    }

    return $data;
  }

  private function aplicarContextoEmpresaNoInsert(array $data): array
  {
    if ($this->campoOrganizacao !== '' && !isset($data[$this->campoOrganizacao])) {
      $orgId = $this->getContextoOrganizacaoId();

      if ($orgId > 0) {
        $data[$this->campoOrganizacao] = $orgId;
      }
    }

    if ($this->campoEmpresa !== '' && !isset($data[$this->campoEmpresa])) {
      $empId = $this->getContextoEmpresaId();

      if ($empId > 0) {
        $data[$this->campoEmpresa] = $empId;
      }
    }

    return $data;
  }

  private function aplicarContextoHierarquicoNoInsert(array $data): array
  {
    if (
      $this->campoOrganizacao === ''
      || $this->campoEmpresa === ''
      || $this->campoEscopoHierarquico === ''
    ) {
      return $data;
    }

    $escopoRegistro = $data[$this->campoEscopoHierarquico] ?? '';

    if ($escopoRegistro === 'Projeto') {
      $data[$this->campoOrganizacao] = 0;
      $data[$this->campoEmpresa] = 0;
      return $data;
    }

    if ($escopoRegistro === 'Organizacao') {
      $data[$this->campoOrganizacao] = $this->getContextoOrganizacaoId();
      $data[$this->campoEmpresa] = 0;
      return $data;
    }

    if ($escopoRegistro === 'Empresa') {
      $data[$this->campoOrganizacao] = $this->getContextoOrganizacaoId();
      $data[$this->campoEmpresa] = $this->getContextoEmpresaId();
      return $data;
    }

    return $data;
  }

  private function aplicarLogInsert(array $data): array
  {
    if (!$this->usarLog) {
      return $data;
    }

    $logColumn = $this->coluna('log');

    if (array_key_exists($logColumn, $data)) {
      return $data;
    }

    $data[$logColumn] = json_encode([
      $this->criarLog('inseriu novo registro')
    ], JSON_UNESCAPED_UNICODE);

    return $data;
  }

  private function aplicarLogUpdate(array $data, object $registroAnterior): array
  {
    if (!$this->usarLog) {
      return $data;
    }

    $logColumn = $this->coluna('log');
    $logAntigo = [];

    if (isset($registroAnterior->$logColumn)) {
      $logAntigo = json_decode($registroAnterior->$logColumn, true) ?? [];
    }

    $detalhes = [];

    if ($this->modoLog === 'detalhado') {
      foreach ($data as $campo => $valorNovo) {
        if ($campo === $logColumn) {
          continue;
        }

        $valorAntigo = $registroAnterior->$campo ?? null;

        if ((string) $valorAntigo !== (string) $valorNovo) {
          $detalhes[] = "Trocou {$campo} de \"" . (string) $valorAntigo . "\" para \"" . (string) $valorNovo . "\"";
        }
      }
    }

    array_unshift($logAntigo, $this->criarLog('atualizou o registro', $detalhes));
    $logAntigo = array_slice($logAntigo, 0, $this->limiteLog);

    $data[$logColumn] = json_encode($logAntigo, JSON_UNESCAPED_UNICODE);

    return $data;
  }

  private function criarLog(string $acao, array $detalhes = []): array
  {
    $log = [
      'usu_id' => $this->usuarioLogado->usu_id ?? null,
      'usu_razaosocial' => $this->usuarioLogado->usu_razaosocial ?? 'Sistema',
      'acao' => $acao,
      'data' => date('d/m/Y H:i:s')
    ];

    if ($this->modoLog === 'detalhado' && !empty($detalhes)) {
      $log['detalhe'] = $detalhes;
    }

    return $log;
  }

  private function erroModel(string $text): object
  {
    return (new SimpleResponse("ATENÇÃO", $text))
      ->erro()
      ->setArrayParams([
        'data' => null,
        'total' => 0,
        'totalLinha' => 0,
        'lastInsertId' => null,
        'error' => null,
        'errorInfo' => $text,
        'debug' => (object) [
          'table' => $this->table,
          'escopoContexto' => $this->escopoContexto,
          'campoOrganizacao' => $this->campoOrganizacao,
          'campoEmpresa' => $this->campoEmpresa,
          'campoEscopoHierarquico' => $this->campoEscopoHierarquico,
          'timestamp' => date('Y-m-d H:i:s')
        ]
      ])
      ->noDestroy();
  }

  private function resolveText(?string $text, bool $success): string
  {
    if (is_null($text)) {
      return $success ? 'Operação realizada com sucesso' : 'Falha ao executar a operação';
    }

    if (strpos($text, '||') === false) {
      return $text;
    }

    $parts = explode('||', $text);

    return $success ? ($parts[0] ?? $text) : ($parts[1] ?? $text);
  }

  private function createParam(string $column): string
  {
    return preg_replace('/[^a-zA-Z0-9_]/', '_', $column) . '_' . count($this->queryParams);
  }

  private function resetQuery(): void
  {
    $this->queryColumns = ['*'];
    $this->queryJoins = [];
    $this->queryWheres = [];
    $this->queryParams = [];
    $this->queryOrderBy = [];
    $this->queryGroupBy = [];
    $this->queryLimit = null;
    $this->queryOffset = null;
  }
}
