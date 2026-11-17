<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;

class CadastroBasicoModel extends SimpleModel
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
  private string $urlBase;

  public function __construct(string $cadastro, string $urlBase = 'cadastrobasico')
  {
    $config = self::getCadastro($cadastro);

    if (is_null($config)) {
      throw new \Exception("Cadastro basico '{$cadastro}' nao configurado.");
    }

    $this->config = $config;
    $this->urlBase = trim($urlBase, '/');
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

  public function getCadastroBasicoAtivo(string $moduloUso = ''): object
  {
    $params = [
      'status' => 'Ativo',
      'org_id' => $this->getContextoOrganizacaoId(),
      'org_id_empresa' => $this->getContextoOrganizacaoId(),
      'emp_id' => $this->getContextoEmpresaId()
    ];

    $preferencia = $this->getPreferenciaCadastroBasico();
    $ocultosWhere = $this->getOcultosWhere($preferencia->ocultar, $params);

    $response = $this->rawQuery(
      "SELECT
         {$this->getTable()}.*,
         {$this->coluna('id')} AS id
       FROM {$this->getTable()}
       WHERE {$this->coluna('status')} = :status
       {$ocultosWhere}
       AND {$this->getWhereEscopoContexto()}
       ORDER BY
         {$this->coluna('ordem')} ASC,
         {$this->coluna('padrao')} DESC,
         {$this->coluna('nome')} ASC",
      $params,
      true,
      "Cadastro basico encontrado com sucesso||Erro ao buscar cadastro basico"
    );

    return $this->aplicarPadraoPreferencia($response, $preferencia);
  }

  public function getRegistrosContexto(bool $ordenarAlfabeticamente = false): object
  {
    $orderBy = $ordenarAlfabeticamente
      ? "{$this->coluna('nome')} ASC, {$this->coluna('id')} ASC"
      : "FIELD({$this->coluna('tipocadastro')}, 'Organizacao', 'Empresa'), {$this->coluna('ordem')} ASC, {$this->coluna('padrao')} DESC, {$this->coluna('nome')} ASC";
    $response = $this->rawQuery(
      "SELECT
         {$this->getTable()}.*,
         {$this->coluna('id')} AS id
       FROM {$this->getTable()}
       WHERE {$this->getWhereEscopoContexto()}
       ORDER BY {$orderBy}",
      [
        'org_id' => $this->getContextoOrganizacaoId(),
        'org_id_empresa' => $this->getContextoOrganizacaoId(),
        'emp_id' => $this->getContextoEmpresaId()
      ],
      true,
      "Registros encontrados com sucesso||Erro ao buscar registros"
    );

    return $this->aplicarPadraoPreferencia($response, $this->getPreferenciaCadastroBasico());
  }

  public function getRegistroByIdContexto(int $id): object
  {
    return $this->rawQuery(
      "SELECT
         {$this->getTable()}.*,
         {$this->coluna('id')} AS id
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
      "Registro encontrado com sucesso||Registro nao encontrado"
    );
  }

  public function contarRegistrosContexto(): int
  {
    $getTotal = $this->rawQuery(
      "SELECT COUNT({$this->coluna('id')}) AS total
       FROM {$this->getTable()}
       WHERE {$this->getWhereEscopoContexto()}",
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

  public function contarRegistrosPorStatusContexto(): array
  {
    $result = $this->rawQuery(
      "SELECT {$this->coluna('status')} AS status, COUNT(*) AS total
       FROM {$this->getTable()}
       WHERE {$this->getWhereEscopoContexto()}
       GROUP BY {$this->coluna('status')}",
      [
        'org_id' => $this->getContextoOrganizacaoId(),
        'org_id_empresa' => $this->getContextoOrganizacaoId(),
        'emp_id' => $this->getContextoEmpresaId()
      ],
      true,
      'Totais encontrados||Erro ao contar registros por status'
    );
    $totais = ['Ativo' => 0, 'Inativo' => 0];
    foreach (($result->data ?? []) as $linha) {
      if (isset($totais[$linha->status ?? ''])) $totais[$linha->status] = (int) $linha->total;
    }
    return $totais;
  }

  public function alterarStatusCadastroBasico(int $id, string $status, string $motivo = ''): object
  {
    if (!in_array($status, ['Ativo', 'Inativo'], true)) {
      return $this->erro('O status informado é inválido.');
    }
    if ($status === 'Inativo' && mb_strlen(trim($motivo)) < 10) {
      return $this->erro('Informe um motivo com pelo menos 10 caracteres para inativar o registro.');
    }

    $registroResponse = $this->getRegistroByIdContexto($id);
    if (!$registroResponse->status || (int) ($registroResponse->total ?? 0) !== 1) {
      return $this->erro('Registro não encontrado no contexto ativo.');
    }
    $registro = $registroResponse->data[0];
    $statusAtual = (string) ($registro->{$this->coluna('status')} ?? '');
    if ($statusAtual === $status) {
      return $this->erro('O registro já está com esse status.');
    }

    $logs = json_decode((string) ($registro->{$this->coluna('log')} ?? '[]'), true);
    if (!is_array($logs)) $logs = [];
    $logs[] = [
      'usu_id' => (int) ($this->usuarioLogado->usu_id ?? 0),
      'usu_razaosocial' => (string) ($this->usuarioLogado->usu_razaosocial ?? $this->usuarioLogado->usu_nomefantasia ?? 'Usuário'),
      'acao' => $status === 'Inativo' ? 'Inativação' : 'Ativação',
      'data' => date('d/m/Y H:i:s'),
      'detalhe' => array_values(array_filter([
        "Status alterado de {$statusAtual} para {$status}.",
        trim($motivo) !== '' ? 'Motivo: ' . trim($motivo) : null
      ]))
    ];
    $logs = array_slice($logs, -$this->limiteLog);

    return $this->rawQuery(
      "UPDATE {$this->getTable()} SET
         {$this->coluna('status')} = :status,
         {$this->coluna('statusclass')} = :statusclass,
         {$this->coluna('log')} = :log,
         {$this->coluna('dtalter')} = NOW()
       WHERE {$this->coluna('id')} = :id
       AND {$this->getWhereEscopoContexto()}",
      [
        'status' => $status,
        'statusclass' => $status === 'Ativo' ? 'success' : 'danger',
        'log' => json_encode($logs, JSON_UNESCAPED_UNICODE),
        'id' => $id,
        'org_id' => $this->getContextoOrganizacaoId(),
        'org_id_empresa' => $this->getContextoOrganizacaoId(),
        'emp_id' => $this->getContextoEmpresaId()
      ],
      false,
      'Status atualizado com sucesso||Erro ao atualizar status'
    );
  }

  public function salvarCadastroBasico(array $data): object
  {
    $id = (int) ($data[$this->coluna('id')] ?? $data['id'] ?? 0);
    $registroAtual = null;
    $definirPadrao = !empty($data[$this->coluna('padrao')]);

    if ($id > 0) {
      $getRegistro = $this->getRegistroByIdContexto($id);

      if (!$getRegistro->status || $getRegistro->total != 1) {
        return $this->erro("Registro nao encontrado ou fora do contexto atual.");
      }

      $registroAtual = $getRegistro->data[0];

      if ($this->isAtualizacaoParcialPadrao($data)) {
        return $this->salvarPadraoParcial($id, $definirPadrao, $registroAtual);
      }
    }

    $data = $this->normalizarDados($data, $registroAtual);

    $validacao = $this->validarDados($data, $id);

    if (!$validacao->status) {
      return $validacao;
    }

    try {
      $transacaoPropria = !$this->pdo->inTransaction();
      if ($transacaoPropria) {
        $this->beginTransaction();
      }

      if ($id > 0) {
        $resultado = $this->update(
          $data,
          [$this->coluna('id') => $id],
          $this->config->textUpdate
        );
      } else {
        $resultado = $this->insert($data, $this->config->textInsert);

        if ($resultado->status) {
          $id = (int) ($resultado->lastInsertId ?? 0);
        }
      }

      if (!$resultado->status) {
        if ($transacaoPropria && $this->pdo->inTransaction()) {
          $this->rollback();
        }
        return $resultado;
      }

      $salvarPreferencia = $this->sincronizarPadraoPreferencia(
        $data[$this->coluna('tipocadastro')],
        $id,
        $definirPadrao
      );

      if (!$salvarPreferencia->status) {
        if ($transacaoPropria && $this->pdo->inTransaction()) {
          $this->rollback();
        }
        return $salvarPreferencia;
      }

      if ($transacaoPropria && $this->pdo->inTransaction()) {
        $this->commit();
      }

      return (new SimpleResponse($resultado))
        ->setArrayParams([
          'location' => Helpers::url("{$this->urlBase}/{$this->config->modulo}"),
          'data' => (object) [
            'id' => $id,
            'modulo' => $this->config->modulo
          ]
        ])
        ->noDestroy();
    } catch (\Throwable $e) {
      if (isset($transacaoPropria) && $transacaoPropria && $this->pdo->inTransaction()) {
        $this->rollback();
      }

      return $this->erro("Erro ao salvar cadastro basico: " . $e->getMessage());
    }
  }

  public function getTiposCadastroPermitidos(): array
  {
    $orgId = $this->getContextoOrganizacaoId();
    $empId = $this->getContextoEmpresaId();
    $escopo = $this->getEscopoMaximoUsuario();
    $tipos = [];

    if (in_array($escopo, ['Projeto', 'Organizacao'], true) && $orgId > 0) {
      $tipos[] = (object) [
        'value' => 'Organizacao',
        'label' => 'Organizacao'
      ];
    }

    if ($orgId > 0 && $empId > 0) {
      $tipos[] = (object) [
        'value' => 'Empresa',
        'label' => 'Empresa'
      ];
    }

    return $tipos;
  }

  public static function getCadastrosComTotais(): array
  {
    $cadastros = [];

    foreach (self::getCadastros() as $modulo => $config) {
      $model = new self($modulo);
      $config->total = $model->contarRegistrosContexto();
      $cadastros[] = $config;
    }

    return $cadastros;
  }

  public static function getCadastros(): array
  {
    return [
      'sexo' => (object) [
        'modulo' => 'sexo',
        'titulo' => 'Sexo',
        'tabela' => 'sexo',
        'prefixo' => 'sex',
        'required' => ['sex_nome'],
        'unique' => ['sex_nome'],
        'extras' => [],
        'textInsert' => 'Sexo inserido com sucesso||Erro ao salvar sexo',
        'textUpdate' => 'Sexo atualizado com sucesso||Erro ao atualizar sexo'
      ],

      'estadocivil' => (object) [
        'modulo' => 'estadocivil',
        'titulo' => 'Estado civil',
        'tabela' => 'estadocivil',
        'prefixo' => 'ecv',
        'required' => ['ecv_nome'],
        'unique' => ['ecv_nome'],
        'extras' => [],
        'textInsert' => 'Estado civil inserido com sucesso||Erro ao salvar estado civil',
        'textUpdate' => 'Estado civil atualizado com sucesso||Erro ao atualizar estado civil'
      ],

      'naturalidade' => (object) [
        'modulo' => 'naturalidade',
        'titulo' => 'Naturalidade',
        'tabela' => 'naturalidade',
        'prefixo' => 'nat',
        'required' => ['nat_nome'],
        'unique' => ['nat_nome', 'nat_uf', 'nat_pais'],
        'extras' => ['nat_uf', 'nat_pais'],
        'textInsert' => 'Naturalidade inserida com sucesso||Erro ao salvar naturalidade',
        'textUpdate' => 'Naturalidade atualizada com sucesso||Erro ao atualizar naturalidade'
      ],

      'naturezajuridica' => (object) [
        'modulo' => 'naturezajuridica',
        'titulo' => 'Natureza jurídica',
        'tabela' => 'naturezajuridica',
        'prefixo' => 'ntj',
        'required' => ['ntj_nome'],
        'unique' => ['ntj_nome'],
        'extras' => [],
        'textInsert' => 'Natureza jurídica inserida com sucesso||Erro ao salvar natureza jurídica',
        'textUpdate' => 'Natureza jurídica atualizada com sucesso||Erro ao atualizar natureza jurídica'
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
    $tipo = !is_null($registroAtual)
      ? (string) ($registroAtual->{$this->coluna('tipocadastro')} ?? '')
      : (string) ($data[$this->coluna('tipocadastro')] ?? $this->getTipoCadastroPadraoPermitido());

    $status = (string) ($data[$this->coluna('status')] ?? 'Ativo');
    $status = in_array($status, ['Ativo', 'Inativo'], true) ? $status : 'Ativo';
    $padrao = 0;

    $normalizado = [
      $this->coluna('tipocadastro') => $tipo,
      $this->coluna('status') => $status,
      $this->coluna('statusclass') => $status === 'Ativo' ? 'success' : 'danger',
      $this->coluna('nome') => trim((string) ($data[$this->coluna('nome')] ?? '')),
      $this->coluna('padrao') => $padrao,
      $this->coluna('ordem') => (int) ($data[$this->coluna('ordem')] ?? 0)
    ];

    foreach ($this->config->extras as $campo) {
      $valor = trim((string) ($data[$campo] ?? ''));

      if ($campo === 'nat_uf') {
        $valor = strtoupper(substr($valor, 0, 2));
      }

      if ($campo === 'nat_pais' && $valor === '') {
        $valor = 'Brasil';
      }

      $normalizado[$campo] = $valor === '' ? null : $valor;
    }

    return $this->aplicarEscopoCadastro($normalizado);
  }

  private function validarDados(array $data, int $id): object
  {
    foreach ($this->config->required as $campo) {
      if (trim((string) ($data[$campo] ?? '')) === '') {
        return $this->erro("Informe o campo obrigatorio.");
      }
    }

    $validarTipo = $this->validarTipoCadastroPermitido($data[$this->coluna('tipocadastro')] ?? '');

    if (!$validarTipo->status) {
      return $validarTipo;
    }

    $idOrganizacao = (int) ($data[$this->campoOrganizacao] ?? 0);
    $idEmpresa = (int) ($data[$this->campoEmpresa] ?? 0);

    if ($idOrganizacao <= 0 || $idEmpresa < 0) {
      return $this->erro("Cadastro basico deve pertencer a uma organizacao valida.");
    }

    if ($data[$this->coluna('tipocadastro')] === 'Organizacao' && $idEmpresa !== 0) {
      return $this->erro("Cadastro de organizacao nao pode possuir empresa vinculada.");
    }

    if ($data[$this->coluna('tipocadastro')] === 'Empresa' && $idEmpresa <= 0) {
      return $this->erro("Cadastro de empresa deve possuir uma empresa valida.");
    }

    return $this->validarUnicidade($data, $id);
  }

  private function isAtualizacaoParcialPadrao(array $data): bool
  {
    if (!array_key_exists($this->coluna('padrao'), $data)) {
      return false;
    }

    $camposEdicao = array_merge(
      [
        $this->coluna('tipocadastro'),
        $this->coluna('status'),
        $this->coluna('nome'),
        $this->coluna('ordem')
      ],
      $this->config->extras
    );

    foreach ($camposEdicao as $campo) {
      if (array_key_exists($campo, $data)) {
        return false;
      }
    }

    return true;
  }

  private function salvarPadraoParcial(int $id, bool $definirPadrao, object $registroAtual): object
  {
    $tipoReal = (string) ($registroAtual->{$this->coluna('tipocadastro')} ?? '');
    $podeEditarTipoReal = $this->validarTipoCadastroPermitido($tipoReal)->status;

    try {
      $this->beginTransaction();

      $tipoPreferencia = $podeEditarTipoReal && in_array($tipoReal, ['Organizacao', 'Empresa'], true)
        ? $tipoReal
        : $this->getTipoPreferenciaContextual();

      $resultado = $this->update(
        [
          $this->coluna('padrao') => 0
        ],
        [
          $this->coluna('id') => $id
        ],
        "Registro ajustado com sucesso||Erro ao ajustar registro"
      );

      if ($resultado->status) {
        $resultado = $this->sincronizarPadraoPreferencia($tipoPreferencia, $id, $definirPadrao);
      }

      if (!$resultado->status) {
        $this->rollback();
        return $resultado;
      }

      $this->commit();

      return (new SimpleResponse($resultado))
        ->setArrayParams([
          'location' => Helpers::url("{$this->urlBase}/{$this->config->modulo}"),
          'data' => (object) [
            'id' => $id,
            'modulo' => $this->config->modulo
          ]
        ])
        ->noDestroy();
    } catch (\Throwable $e) {
      if ($this->pdo->inTransaction()) {
        $this->rollback();
      }

      return $this->erro("Erro ao atualizar padrao: " . $e->getMessage());
    }
  }

  private function validarUnicidade(array $data, int $id): object
  {
    $conditions = [
      "{$this->campoOrganizacao} = :idorganizacao",
      "{$this->campoEmpresa} = :idempresa"
    ];

    $params = [
      'idorganizacao' => (int) ($data[$this->campoOrganizacao] ?? 0),
      'idempresa' => (int) ($data[$this->campoEmpresa] ?? 0)
    ];

    foreach ($this->config->unique as $campo) {
      $param = 'unique_' . $campo;

      if (is_null($data[$campo] ?? null)) {
        $conditions[] = "{$campo} IS NULL";
      } else {
        $conditions[] = "{$campo} = :{$param}";
        $params[$param] = $data[$campo];
      }
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

    if ($getDuplicado->status && $getDuplicado->total > 0) {
      return $this->erro("Ja existe um registro com estes dados neste escopo.");
    }

    return (new SimpleResponse("Validacao", "Dados validos"))
      ->sucesso()
      ->noDestroy();
  }

  private function aplicarEscopoCadastro(array $data): array
  {
    if (($data[$this->coluna('tipocadastro')] ?? '') === 'Organizacao') {
      $data[$this->campoOrganizacao] = $this->getContextoOrganizacaoId();
      $data[$this->campoEmpresa] = 0;
      return $data;
    }

    $data[$this->coluna('tipocadastro')] = 'Empresa';
    $data[$this->campoOrganizacao] = $this->getContextoOrganizacaoId();
    $data[$this->campoEmpresa] = $this->getContextoEmpresaId();

    return $data;
  }

  private function validarTipoCadastroPermitido(string $tipo): object
  {
    $permitidos = array_map(
      fn($item) => $item->value,
      $this->getTiposCadastroPermitidos()
    );

    if (!in_array($tipo, $permitidos, true)) {
      return $this->erro("Voce nao pode gerenciar cadastro basico no escopo {$tipo} pelo contexto atual.", "Permissao Negada");
    }

    return (new SimpleResponse("Validacao", "Tipo de cadastro permitido"))
      ->sucesso()
      ->noDestroy();
  }

  private function getTipoCadastroPadraoPermitido(): string
  {
    $tipos = $this->getTiposCadastroPermitidos();

    return $tipos[0]->value ?? 'Empresa';
  }

  private function getTipoPreferenciaContextual(): string
  {
    if (
      in_array($this->getEscopoMaximoUsuario(), ['Projeto', 'Organizacao'], true)
      && $this->getContextoOrganizacaoId() > 0
    ) {
      return 'Organizacao';
    }

    return 'Empresa';
  }

  private function getOcultosWhere(array $ocultos, array &$params): string
  {
    if (empty($ocultos)) {
      return '';
    }

    $placeholders = [];

    foreach ($ocultos as $index => $id) {
      $param = "oculto_{$index}";
      $placeholders[] = ':' . $param;
      $params[$param] = $id;
    }

    return "AND {$this->coluna('id')} NOT IN (" . implode(', ', $placeholders) . ")";
  }

  private function getPreferenciaCadastroBasico(): object
  {
    $organizacao = $this->extrairPreferencia($this->contexto->organizacao->org_cadastrobasico_preferencia ?? null);
    $empresa = $this->extrairPreferencia($this->contexto->empresa->emp_cadastrobasico_preferencia ?? null);

    $ocultos = array_merge($organizacao->ocultar, $empresa->ocultar);
    $ocultos = array_values(array_unique(array_filter(array_map('intval', $ocultos))));

    return (object) [
      'ocultar' => $ocultos,
      'padrao' => $empresa->padrao > 0 ? $empresa->padrao : $organizacao->padrao
    ];
  }

  private function extrairPreferencia($json): object
  {
    $preferencia = (object) [
      'ocultar' => [],
      'padrao' => 0
    ];

    if (is_null($json)) {
      return $preferencia;
    }

    if (is_object($json) || is_array($json)) {
      $dados = json_decode(json_encode($json), true);
    } elseif (is_string($json) && trim($json) !== '') {
      $dados = json_decode($json, true);
    } else {
      return $preferencia;
    }

    if (!is_array($dados)) {
      return $preferencia;
    }

    $cadastro = $this->config->modulo;
    $dadosModulo = $dados[$cadastro] ?? null;

    if (!is_array($dadosModulo)) {
      return $preferencia;
    }

    $preferencia->ocultar = isset($dadosModulo['ocultar']) && is_array($dadosModulo['ocultar'])
      ? array_values(array_filter(array_map('intval', $dadosModulo['ocultar'])))
      : [];

    $preferencia->padrao = (int) ($dadosModulo['padrao'] ?? 0);

    return $preferencia;
  }

  private function sincronizarPadraoPreferencia(string $tipo, int $id, bool $definirPadrao): object
  {
    if (!in_array($tipo, ['Organizacao', 'Empresa'], true) || $id <= 0) {
      return (new SimpleResponse("Validacao", "Preferencia nao aplicavel"))
        ->sucesso()
        ->noDestroy();
    }

    $campo = $tipo === 'Organizacao'
      ? 'org_cadastrobasico_preferencia'
      : 'emp_cadastrobasico_preferencia';

    $tabela = $tipo === 'Organizacao'
      ? DB_PREFIX . '_organizacao'
      : DB_PREFIX . '_empresa';

    $whereCampo = $tipo === 'Organizacao' ? 'org_id' : 'emp_id';
    $whereId = $tipo === 'Organizacao' ? $this->getContextoOrganizacaoId() : $this->getContextoEmpresaId();

    if ($whereId <= 0) {
      return $this->erro("Contexto invalido para atualizar preferencia de cadastro basico.");
    }

    $preferencias = $this->normalizarPreferenciasJson(
      $tipo === 'Organizacao'
        ? ($this->contexto->organizacao->{$campo} ?? null)
        : ($this->contexto->empresa->{$campo} ?? null)
    );

    $cadastro = $this->config->modulo;
    $preferencias[$cadastro] = $preferencias[$cadastro] ?? [];

    if ($definirPadrao) {
      $preferencias[$cadastro]['padrao'] = $id;
    } elseif ((int) ($preferencias[$cadastro]['padrao'] ?? 0) === $id) {
      unset($preferencias[$cadastro]['padrao']);
    }

    if (empty($preferencias[$cadastro])) {
      unset($preferencias[$cadastro]);
    }

    $json = empty($preferencias)
      ? null
      : json_encode($preferencias, JSON_UNESCAPED_UNICODE);

    $response = $this->rawQuery(
      "UPDATE {$tabela}
       SET {$campo} = :preferencia
       WHERE {$whereCampo} = :id",
      [
        'preferencia' => $json,
        'id' => $whereId
      ],
      false,
      "Preferencia atualizada com sucesso||Erro ao atualizar preferencia"
    );

    if ($response->status) {
      if ($tipo === 'Organizacao') {
        $this->contexto->organizacao->{$campo} = $json;
      } else {
        $this->contexto->empresa->{$campo} = $json;
      }

      $this->session->set('CONTEXTO', $this->contexto);
    }

    return $response;
  }

  private function normalizarPreferenciasJson($json): array
  {
    if (is_object($json) || is_array($json)) {
      $dados = json_decode(json_encode($json), true);
      return is_array($dados) ? $dados : [];
    }

    if (!is_string($json) || trim($json) === '') {
      return [];
    }

    $dados = json_decode($json, true);

    return is_array($dados) ? $dados : [];
  }

  private function aplicarPadraoPreferencia(object $response, object $preferencia): object
  {
    if (!$response->status || empty($response->data) || $preferencia->padrao <= 0) {
      return $response;
    }

    $idsVisiveis = array_map(
      fn($item) => (int) ($item->{$this->coluna('id')} ?? 0),
      $response->data
    );

    $padraoVisivel = in_array($preferencia->padrao, $idsVisiveis, true);

    foreach ($response->data as $item) {
      $item->{$this->coluna('padrao')} = $padraoVisivel && (int) ($item->{$this->coluna('id')} ?? 0) === $preferencia->padrao
        ? 1
        : 0;
    }

    return $response;
  }

  private function getWhereEscopoContexto(): string
  {
    return "(
      ({$this->campoOrganizacao} = :org_id AND {$this->campoEmpresa} = 0)
      OR ({$this->campoOrganizacao} = :org_id_empresa AND {$this->campoEmpresa} = :emp_id)
    )";
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
