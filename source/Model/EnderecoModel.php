<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;

class EnderecoModel extends SimpleModel
{
  protected string $tabela = 'endereco';
  protected string $prefixo = 'end';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getEnderecosByConfig(object $config): object
  {
    return $this->rawQuery(
      "SELECT *
       FROM {$this->getTable()}
       WHERE end_idorganizacao = :end_idorganizacao
       AND end_idempresa IN (:end_idempresa, 0)
       AND end_idorigem = :end_idorigem
       AND end_origem = :end_origem
       ORDER BY end_principal DESC, end_id DESC",
      [
        'end_idorganizacao' => (int) $config->idorganizacao,
        'end_idempresa' => (int) $config->idempresa,
        'end_idorigem' => (int) $config->idorigem,
        'end_origem' => (string) $config->origem
      ],
      true,
      "Endereços encontrados com sucesso||Erro ao buscar endereços"
    );
  }

  public function getEnderecoById(int $id): object
  {
    $params = [
      'end_id' => $id
    ];

    $whereOrganizacao = "";
    $whereEmpresa = "";

    if (!$this->usuarioEscopoProjeto()) {
      $whereOrganizacao = "AND end_idorganizacao = :end_idorganizacao";
      $whereEmpresa = "AND end_idempresa IN (:end_idempresa, 0)";
      $params['end_idorganizacao'] = (int) ($this->contexto->organizacao->org_id ?? 0);
      $params['end_idempresa'] = (int) ($this->contexto->empresa->emp_id ?? 0);
    }

    return $this->rawQuery(
      "SELECT *
       FROM {$this->getTable()}
       WHERE end_id = :end_id
       {$whereOrganizacao}
       {$whereEmpresa}
       LIMIT 1",
      $params,
      true,
      "Endereço encontrado com sucesso||Erro ao buscar endereço"
    );
  }

  public function salvarEndereco(array $data): object
  {
    $endId = (int) ($data['end_id'] ?? 0);
    $endOrigem = trim((string) ($data['end_origem'] ?? ''));
    $endIdOrigem = (int) ($data['end_idorigem'] ?? 0);
    $idEmpresaEscopo = $this->getIdEmpresaEscopo($endOrigem);
    $idOrganizacaoEscopo = $this->getIdOrganizacaoEscopo($endOrigem, $endIdOrigem);

    if (
      $endOrigem !== 'projeto' &&
      (
        empty($this->contexto->organizacao->org_id) ||
        ($endOrigem !== 'organizacao' && empty($this->contexto->empresa->emp_id))
      )
    ) {
      return (new SimpleResponse("Erro", "Contexto de organização ou empresa não identificado."))
        ->erro()
        ->noDestroy();
    }

    if ($endOrigem === '') {
      return (new SimpleResponse("Atenção", "Origem do endereço não informada."))
        ->warning()
        ->noDestroy();
    }

    if ($endIdOrigem <= 0) {
      return (new SimpleResponse("Atenção", "Registro de origem do endereço não informado."))
        ->warning()
        ->noDestroy();
    }

    $validacaoAcesso = $this->validarAcessoOrigem($endOrigem, $endIdOrigem);

    if (!$validacaoAcesso->status) {
      return $validacaoAcesso;
    }

    $data['end_idorganizacao'] = $idOrganizacaoEscopo;
    $data['end_idempresa'] = $idEmpresaEscopo;
    $data['end_idorigem'] = $endIdOrigem;
    $data['end_origem'] = $endOrigem;

    $data['end_status'] = in_array(($data['end_status'] ?? 'Ativo'), ['Ativo', 'Inativo'], true)
      ? $data['end_status']
      : 'Ativo';

    $data['end_statusclass'] = $data['end_status'] === 'Ativo' ? 'success' : 'danger';

    $data['end_cep'] = Helpers::somenteNumeros($data['end_cep'] ?? '');

    if ($data['end_cep'] === '') {
      return (new SimpleResponse("Atenção", "Informe o CEP do endereço."))
        ->warning()
        ->noDestroy();
    }

    if (strlen($data['end_cep']) !== 8) {
      return (new SimpleResponse("Atenção", "Informe um CEP válido."))
        ->warning()
        ->noDestroy();
    }

    $data['end_logradouro'] = trim((string) ($data['end_logradouro'] ?? ''));
    $data['end_bairro'] = trim((string) ($data['end_bairro'] ?? ''));
    $data['end_cidade'] = trim((string) ($data['end_cidade'] ?? ''));
    $data['end_uf'] = strtoupper(trim((string) ($data['end_uf'] ?? '')));
    $data['end_estado'] = trim((string) ($data['end_estado'] ?? ''));

    if ($data['end_logradouro'] === '') {
      return (new SimpleResponse("Atenção", "Informe o logradouro do endereço."))
        ->warning()
        ->noDestroy();
    }

    if ($data['end_bairro'] === '') {
      return (new SimpleResponse("Atenção", "Informe o bairro do endereço."))
        ->warning()
        ->noDestroy();
    }

    if ($data['end_cidade'] === '') {
      return (new SimpleResponse("Atenção", "Informe a cidade do endereço."))
        ->warning()
        ->noDestroy();
    }

    if ($data['end_uf'] === '') {
      return (new SimpleResponse("Atenção", "Informe a UF do endereço."))
        ->warning()
        ->noDestroy();
    }

    $data['end_principal'] = isset($data['end_principal']) ? 1 : 0;
    $data['end_pais'] = trim((string) ($data['end_pais'] ?? 'Brasil'));

    $getTotal = $this->getTotalEnderecosOrigem($endOrigem, $endIdOrigem, $idOrganizacaoEscopo, $idEmpresaEscopo, $endId);

    if (!$getTotal->status) {
      return $getTotal;
    }

    $totalOutros = (int) ($getTotal->data[0]->total ?? 0);

    if ($endId <= 0) {
      $limite = $this->getLimiteEndereco($endOrigem);

      if ($limite > 0 && $totalOutros >= $limite) {
        return (new SimpleResponse("Atenção", "O limite de endereços permitido pelo plano foi atingido."))
          ->warning()
          ->noDestroy();
      }
    }

    if ($totalOutros <= 0) {
      $data['end_principal'] = 1;
    }

    if ($data['end_principal'] === 1) {
      $this->removerPrincipalDosOutrosEnderecos($endOrigem, $endIdOrigem, $idOrganizacaoEscopo, $idEmpresaEscopo, $endId);
    }

    $camposPermitidos = [
      'end_id',
      'end_status',
      'end_statusclass',
      'end_principal',
      'end_nome',
      'end_cep',
      'end_logradouro',
      'end_numero',
      'end_complemento',
      'end_bairro',
      'end_cidade',
      'end_estado',
      'end_uf',
      'end_pais',
      'end_referencia',
      'end_googlemaps',
      'end_idorganizacao',
      'end_idempresa',
      'end_idorigem',
      'end_origem',
      'end_log'
    ];

    foreach ($data as $key => $value) {
      if (!in_array($key, $camposPermitidos, true)) {
        unset($data[$key]);
        continue;
      }

      if (is_string($value)) {
        $data[$key] = trim($value);
      }

      if ($data[$key] === '') {
        $data[$key] = null;
      }
    }

    if ($endId > 0) {
      unset(
        $data['end_id'],
        $data['end_idorganizacao'],
        $data['end_idempresa'],
        $data['end_idorigem'],
        $data['end_origem'],
        $data['end_log']
      );

      $response = $this->update(
        $data,
        [
          'end_id' => $endId,
          'end_idorganizacao' => $idOrganizacaoEscopo,
          'end_idempresa' => $idEmpresaEscopo,
          'end_idorigem' => $endIdOrigem,
          'end_origem' => $endOrigem
        ],
        "Endereço atualizado com sucesso||Erro ao atualizar endereço"
      );

      if ($response->status) {
        $response->location = $_SERVER['HTTP_REFERER'] ?? null;
      }

      return $response;
    }

    unset($data['end_id']);

    $response = $this->insert(
      $data,
      "Endereço cadastrado com sucesso||Erro ao cadastrar endereço"
    );

    if ($response->status) {
      $response->location = $_SERVER['HTTP_REFERER'] ?? null;
    }

    return $response;
  }

  public function deleteEndereco(int $id, array $data = []): object
  {
    $endOrigem = trim((string) ($data['end_origem'] ?? ''));
    $endIdOrigem = (int) ($data['end_idorigem'] ?? 0);

    if ($id <= 0) {
      return (new SimpleResponse("Atenção", "Endereço não informado."))
        ->warning()
        ->noDestroy();
    }

    if ($endOrigem === '' || $endIdOrigem <= 0) {
      return (new SimpleResponse("Atenção", "Origem do endereço não informada."))
        ->warning()
        ->noDestroy();
    }

    $idEmpresaEscopo = $this->getIdEmpresaEscopo($endOrigem);
    $idOrganizacaoEscopo = $this->getIdOrganizacaoEscopo($endOrigem, $endIdOrigem);

    $validacaoAcesso = $this->validarAcessoOrigem($endOrigem, $endIdOrigem);

    if (!$validacaoAcesso->status) {
      return $validacaoAcesso;
    }

    $getEndereco = $this->rawQuery(
      "SELECT *
       FROM {$this->getTable()}
       WHERE end_id = :end_id
       AND end_idorganizacao = :end_idorganizacao
       AND end_idempresa = :end_idempresa
       AND end_idorigem = :end_idorigem
       AND end_origem = :end_origem
       LIMIT 1",
      [
        'end_id' => $id,
        'end_idorganizacao' => $idOrganizacaoEscopo,
        'end_idempresa' => $idEmpresaEscopo,
        'end_idorigem' => $endIdOrigem,
        'end_origem' => $endOrigem
      ],
      true,
      "Endereço encontrado com sucesso||Erro ao buscar endereço"
    );

    if (!$getEndereco->status || $getEndereco->total != 1) {
      return (new SimpleResponse("Atenção", "Endereço não encontrado."))
        ->warning()
        ->noDestroy();
    }

    $endereco = $getEndereco->data[0];

    $response = $this->delete(
      [
        'end_id' => $id,
        'end_idorganizacao' => $idOrganizacaoEscopo,
        'end_idempresa' => $idEmpresaEscopo,
        'end_idorigem' => $endIdOrigem,
        'end_origem' => $endOrigem
      ],
      "Endereço excluído com sucesso||Erro ao excluir endereço"
    );

    if ($response->status && (int) $endereco->end_principal === 1) {
      $this->definirNovoPrincipal($endOrigem, $endIdOrigem, $idOrganizacaoEscopo, $idEmpresaEscopo);
    }

    if ($response->status) {
      $response->location = $_SERVER['HTTP_REFERER'] ?? null;
    }

    return $response;
  }

  private function getTotalEnderecosOrigem(string $origem, int $idOrigem, int $idOrganizacaoEscopo, int $idEmpresaEscopo, int $ignorarId = 0): object
  {
    $params = [
      'end_idorganizacao' => $idOrganizacaoEscopo,
      'end_idempresa' => $idEmpresaEscopo,
      'end_idorigem' => $idOrigem,
      'end_origem' => $origem
    ];

    $whereIgnorar = '';

    if ($ignorarId > 0) {
      $whereIgnorar = "AND end_id != :end_id";
      $params['end_id'] = $ignorarId;
    }

    return $this->rawQuery(
      "SELECT COUNT(*) AS total
       FROM {$this->getTable()}
       WHERE end_idorganizacao = :end_idorganizacao
       AND end_idempresa = :end_idempresa
       AND end_idorigem = :end_idorigem
       AND end_origem = :end_origem
       {$whereIgnorar}",
      $params,
      true,
      "Endereços validados com sucesso||Erro ao validar endereços"
    );
  }

  private function removerPrincipalDosOutrosEnderecos(string $origem, int $idOrigem, int $idOrganizacaoEscopo, int $idEmpresaEscopo, int $ignorarId = 0): object
  {
    $params = [
      'end_idorganizacao' => $idOrganizacaoEscopo,
      'end_idempresa' => $idEmpresaEscopo,
      'end_idorigem' => $idOrigem,
      'end_origem' => $origem
    ];

    $whereIgnorar = '';

    if ($ignorarId > 0) {
      $whereIgnorar = "AND end_id != :end_id";
      $params['end_id'] = $ignorarId;
    }

    return $this->rawQuery(
      "UPDATE {$this->getTable()}
       SET end_principal = 0
       WHERE end_idorganizacao = :end_idorganizacao
       AND end_idempresa = :end_idempresa
       AND end_idorigem = :end_idorigem
       AND end_origem = :end_origem
       {$whereIgnorar}",
      $params,
      false,
      "Endereço principal atualizado com sucesso||Erro ao atualizar endereço principal"
    );
  }

  private function definirNovoPrincipal(string $origem, int $idOrigem, int $idOrganizacaoEscopo, int $idEmpresaEscopo): object
  {
    return $this->rawQuery(
      "UPDATE {$this->getTable()}
       SET end_principal = 1
       WHERE end_id = (
         SELECT end_id FROM (
           SELECT end_id
           FROM {$this->getTable()}
           WHERE end_idorganizacao = :end_idorganizacao
           AND end_idempresa = :end_idempresa
           AND end_idorigem = :end_idorigem
           AND end_origem = :end_origem
           ORDER BY end_id DESC
           LIMIT 1
         ) AS tmp
       )",
      [
        'end_idorganizacao' => $idOrganizacaoEscopo,
        'end_idempresa' => $idEmpresaEscopo,
        'end_idorigem' => $idOrigem,
        'end_origem' => $origem
      ],
      false,
      "Endereço principal definido com sucesso||Erro ao definir endereço principal"
    );
  }

  private function getLimiteEndereco(string $origem): int
  {
    if ($origem === 'projeto') {
      return 0;
    }

    if ($origem === 'organizacao') {
      return 0;
    }

    if ($origem === 'usuario') {
      return (int) ($this->contexto->plano->plc_parametro->usuario->endereco->limite ?? 1);
    }

    if ($origem === 'empresa') {
      return (int) ($this->contexto->plano->plc_parametro->empresa->endereco->limite ?? 1);
    }

    if ($origem === 'membro') {
      return (int) ($this->contexto->plano->plc_parametro->usuario->endereco->limite ?? 0);
    }

    if ($origem === 'fornecedor') {
      return (int) ($this->contexto->plano->plc_parametro->fornecedor->endereco->limite ?? 0);
    }

    return 1;
  }

  private function getIdEmpresaEscopo(string $origem): int
  {
    return in_array($origem, ['organizacao', 'projeto'], true)
      ? 0
      : (int) ($this->contexto->empresa->emp_id ?? 0);
  }

  private function getIdOrganizacaoEscopo(string $origem, int $idOrigem): int
  {
    return $origem === 'projeto'
      ? 0
      : ($origem === 'organizacao' ? $idOrigem : (int) ($this->contexto->organizacao->org_id ?? 0));
  }

  private function validarAcessoOrigem(string $origem, int $idOrigem): object
  {
    if ($origem === 'projeto') {
      if ($this->usuarioEscopoProjeto()) {
        return (new SimpleResponse("Validação", "Acesso permitido"))
          ->sucesso()
          ->noDestroy();
      }

      return (new SimpleResponse("Permissão Negada", "Você não tem acesso ao endereço do projeto."))
        ->erro()
        ->noDestroy();
    }

    if ($origem !== 'organizacao' || $this->usuarioEscopoProjeto()) {
      return (new SimpleResponse("Validação", "Acesso permitido"))
        ->sucesso()
        ->noDestroy();
    }

    if ((int) ($this->contexto->organizacao->org_id ?? 0) === $idOrigem) {
      return (new SimpleResponse("Validação", "Acesso permitido"))
        ->sucesso()
        ->noDestroy();
    }

    return (new SimpleResponse("Permissão Negada", "Você não tem acesso a esta organização."))
      ->erro()
      ->noDestroy();
  }

  private function usuarioEscopoProjeto(): bool
  {
    if (is_null($this->usuarioLogado)) {
      return false;
    }

    if (($this->usuarioLogado->escopo_maximo ?? '') === 'Projeto') {
      return true;
    }

    if (($this->usuarioLogado->nva_escopo_maximo ?? '') === 'Projeto') {
      return true;
    }

    $permissoes = (array) ($this->usuarioLogado->permissoes ?? []);

    return isset($permissoes['projeto.visualizar'])
      || isset($permissoes['projeto.atualizar']);
  }
}
