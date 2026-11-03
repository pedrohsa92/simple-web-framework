<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;

class ContatoModel extends SimpleModel
{
  protected string $tabela = 'contato';
  protected string $prefixo = 'ctt';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getContatosByConfig(object $config): object
  {
    return $this->rawQuery(
      "SELECT *
       FROM {$this->getTable()}
       WHERE ctt_idorganizacao = :ctt_idorganizacao
       AND ctt_idempresa IN (:ctt_idempresa, 0)
       AND ctt_idorigem = :ctt_idorigem
       AND ctt_origem = :ctt_origem
       ORDER BY ctt_ordem ASC, ctt_id DESC",
      [
        'ctt_idorganizacao' => (int) $config->idorganizacao,
        'ctt_idempresa' => (int) $config->idempresa,
        'ctt_idorigem' => (int) $config->idorigem,
        'ctt_origem' => (string) $config->origem
      ],
      true,
      "Contatos encontrados com sucesso||Erro ao buscar contatos"
    );
  }

  public function getContatoById(int $id): object
  {
    $params = [
      'ctt_id' => $id
    ];

    $whereOrganizacao = "";
    $whereEmpresa = "";

    if (!$this->usuarioEscopoProjeto()) {
      $whereOrganizacao = "AND ctt_idorganizacao = :ctt_idorganizacao";
      $whereEmpresa = "AND ctt_idempresa IN (:ctt_idempresa, 0)";
      $params['ctt_idorganizacao'] = (int) ($this->contexto->organizacao->org_id ?? 0);
      $params['ctt_idempresa'] = (int) ($this->contexto->empresa->emp_id ?? 0);
    }

    return $this->rawQuery(
      "SELECT *
       FROM {$this->getTable()}
       WHERE ctt_id = :ctt_id
       {$whereOrganizacao}
       {$whereEmpresa}
       LIMIT 1",
      $params,
      true,
      "Contato encontrado com sucesso||Erro ao buscar contato"
    );
  }

  public function salvarContato(array $data): object
  {
    $cttId = (int) ($data['ctt_id'] ?? 0);
    $cttOrigem = trim((string) ($data['ctt_origem'] ?? ''));
    $cttIdOrigem = (int) ($data['ctt_idorigem'] ?? 0);
    $idEmpresaEscopo = $this->getIdEmpresaEscopo($cttOrigem);
    $idOrganizacaoEscopo = $this->getIdOrganizacaoEscopo($cttOrigem, $cttIdOrigem);

    if (
      empty($this->contexto->organizacao->org_id) ||
      ($cttOrigem !== 'organizacao' && empty($this->contexto->empresa->emp_id))
    ) {
      return (new SimpleResponse("Erro", "Contexto de organização ou empresa não identificado."))
        ->erro()
        ->noDestroy();
    }

    if ($cttOrigem === '') {
      return (new SimpleResponse("Atenção", "Origem do contato não informada."))
        ->warning()
        ->noDestroy();
    }

    if ($cttIdOrigem <= 0) {
      return (new SimpleResponse("Atenção", "Registro de origem do contato não informado."))
        ->warning()
        ->noDestroy();
    }

    $validacaoAcesso = $this->validarAcessoOrigem($cttOrigem, $cttIdOrigem);

    if (!$validacaoAcesso->status) {
      return $validacaoAcesso;
    }

    $data['ctt_idorganizacao'] = $idOrganizacaoEscopo;
    $data['ctt_idempresa'] = $idEmpresaEscopo;
    $data['ctt_idorigem'] = $cttIdOrigem;
    $data['ctt_origem'] = $cttOrigem;

    $data['ctt_status'] = in_array(($data['ctt_status'] ?? 'Ativo'), ['Ativo', 'Inativo'], true)
      ? $data['ctt_status']
      : 'Ativo';

    $data['ctt_statusclass'] = $data['ctt_status'] === 'Ativo' ? 'success' : 'danger';

    $data['ctt_tipo'] = $this->normalizarTipoContato($data['ctt_tipo'] ?? 'Outro');
    $data['ctt_identificacao'] = trim((string) ($data['ctt_identificacao'] ?? ''));
    $data['ctt_conteudo'] = trim((string) ($data['ctt_conteudo'] ?? ''));
    $data['ctt_observacao'] = trim((string) ($data['ctt_observacao'] ?? ''));
    $data['ctt_exibiremrelatorio'] = isset($data['ctt_exibiremrelatorio']) ? 1 : 0;
    $data['ctt_ordem'] = (int) ($data['ctt_ordem'] ?? 0);

    if ($data['ctt_conteudo'] === '') {
      return (new SimpleResponse("Atenção", "Informe o conteúdo do contato."))
        ->warning()
        ->noDestroy();
    }

    $validacao = $this->validarConteudoPorTipo($data['ctt_tipo'], $data['ctt_conteudo']);

    if (!$validacao->status) {
      return $validacao;
    }

    $data['ctt_conteudo'] = $this->normalizarConteudoPorTipo($data['ctt_tipo'], $data['ctt_conteudo']);

    $getTotal = $this->getTotalContatosOrigem($cttOrigem, $cttIdOrigem, $idOrganizacaoEscopo, $idEmpresaEscopo, $cttId);

    if (!$getTotal->status) {
      return $getTotal;
    }

    $totalOutros = (int) ($getTotal->data[0]->total ?? 0);

    if ($cttId <= 0) {
      $limite = $this->getLimiteContato($cttOrigem);

      if ($limite > 0 && $totalOutros >= $limite) {
        return (new SimpleResponse("Atenção", "O limite de contatos permitido pelo plano foi atingido."))
          ->warning()
          ->noDestroy();
      }
    }

    $camposPermitidos = [
      'ctt_id',
      'ctt_status',
      'ctt_statusclass',
      'ctt_tipo',
      'ctt_identificacao',
      'ctt_conteudo',
      'ctt_observacao',
      'ctt_exibiremrelatorio',
      'ctt_ordem',
      'ctt_idorganizacao',
      'ctt_idempresa',
      'ctt_idorigem',
      'ctt_origem',
      'ctt_log'
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

    if ($cttId > 0) {
      unset(
        $data['ctt_id'],
        $data['ctt_idorganizacao'],
        $data['ctt_idempresa'],
        $data['ctt_idorigem'],
        $data['ctt_origem'],
        $data['ctt_log']
      );

      $response = $this->update(
        $data,
        [
          'ctt_id' => $cttId,
          'ctt_idorganizacao' => $idOrganizacaoEscopo,
          'ctt_idempresa' => $idEmpresaEscopo,
          'ctt_idorigem' => $cttIdOrigem,
          'ctt_origem' => $cttOrigem
        ],
        "Contato atualizado com sucesso||Erro ao atualizar contato"
      );

      if ($response->status) {
        $response->location = $_SERVER['HTTP_REFERER'] ?? null;
      }

      return $response;
    }

    unset($data['ctt_id']);

    $response = $this->insert(
      $data,
      "Contato cadastrado com sucesso||Erro ao cadastrar contato"
    );

    if ($response->status) {
      $response->location = $_SERVER['HTTP_REFERER'] ?? null;
    }

    return $response;
  }

  public function deleteContato(int $id, array $data = []): object
  {
    $cttOrigem = trim((string) ($data['ctt_origem'] ?? ''));
    $cttIdOrigem = (int) ($data['ctt_idorigem'] ?? 0);

    if ($id <= 0) {
      return (new SimpleResponse("Atenção", "Contato não informado."))
        ->warning()
        ->noDestroy();
    }

    if ($cttOrigem === '' || $cttIdOrigem <= 0) {
      return (new SimpleResponse("Atenção", "Origem do contato não informada."))
        ->warning()
        ->noDestroy();
    }

    $idEmpresaEscopo = $this->getIdEmpresaEscopo($cttOrigem);
    $idOrganizacaoEscopo = $this->getIdOrganizacaoEscopo($cttOrigem, $cttIdOrigem);

    $validacaoAcesso = $this->validarAcessoOrigem($cttOrigem, $cttIdOrigem);

    if (!$validacaoAcesso->status) {
      return $validacaoAcesso;
    }

    $getContato = $this->rawQuery(
      "SELECT *
       FROM {$this->getTable()}
       WHERE ctt_id = :ctt_id
       AND ctt_idorganizacao = :ctt_idorganizacao
       AND ctt_idempresa = :ctt_idempresa
       AND ctt_idorigem = :ctt_idorigem
       AND ctt_origem = :ctt_origem
       LIMIT 1",
      [
        'ctt_id' => $id,
        'ctt_idorganizacao' => $idOrganizacaoEscopo,
        'ctt_idempresa' => $idEmpresaEscopo,
        'ctt_idorigem' => $cttIdOrigem,
        'ctt_origem' => $cttOrigem
      ],
      true,
      "Contato encontrado com sucesso||Erro ao buscar contato"
    );

    if (!$getContato->status || $getContato->total != 1) {
      return (new SimpleResponse("Atenção", "Contato não encontrado."))
        ->warning()
        ->noDestroy();
    }

    $response = $this->delete(
      [
        'ctt_id' => $id,
        'ctt_idorganizacao' => $idOrganizacaoEscopo,
        'ctt_idempresa' => $idEmpresaEscopo,
        'ctt_idorigem' => $cttIdOrigem,
        'ctt_origem' => $cttOrigem
      ],
      "Contato excluído com sucesso||Erro ao excluir contato"
    );

    if ($response->status) {
      $response->location = $_SERVER['HTTP_REFERER'] ?? null;
    }

    return $response;
  }

  public function getTiposContato(): array
  {
    return [
      (object) [
        'value' => 'Celular',
        'label' => 'Celular',
        'icone' => '<i class="fa fa-mobile-alt"></i>'
      ],
      (object) [
        'value' => 'Telefone',
        'label' => 'Telefone',
        'icone' => '<i class="fa fa-phone"></i>'
      ],
      (object) [
        'value' => 'Email',
        'label' => 'E-mail',
        'icone' => '<i class="fa fa-envelope"></i>'
      ],
      (object) [
        'value' => 'WhatsApp',
        'label' => 'WhatsApp',
        'icone' => '<i class="fa-brands fa-whatsapp"></i>'
      ],
      (object) [
        'value' => 'Instagram',
        'label' => 'Instagram',
        'icone' => '<i class="fa-brands fa-instagram"></i>'
      ],
      (object) [
        'value' => 'Facebook',
        'label' => 'Facebook',
        'icone' => '<i class="fa-brands fa-facebook"></i>'
      ],
      (object) [
        'value' => 'X',
        'label' => 'X',
        'icone' => '<i class="fa-brands fa-x-twitter"></i>'
      ],
      (object) [
        'value' => 'LinkedIn',
        'label' => 'LinkedIn',
        'icone' => '<i class="fa-brands fa-linkedin"></i>'
      ],
      (object) [
        'value' => 'Site',
        'label' => 'Site',
        'icone' => '<i class="fa fa-globe"></i>'
      ],
      (object) [
        'value' => 'Outro',
        'label' => 'Outro',
        'icone' => '<i class="fa fa-question-circle"></i>'
      ]
    ];
  }

  private function getTotalContatosOrigem(string $origem, int $idOrigem, int $idOrganizacaoEscopo, int $idEmpresaEscopo, int $ignorarId = 0): object
  {
    $params = [
      'ctt_idorganizacao' => $idOrganizacaoEscopo,
      'ctt_idempresa' => $idEmpresaEscopo,
      'ctt_idorigem' => $idOrigem,
      'ctt_origem' => $origem
    ];

    $whereIgnorar = '';

    if ($ignorarId > 0) {
      $whereIgnorar = "AND ctt_id != :ctt_id";
      $params['ctt_id'] = $ignorarId;
    }

    return $this->rawQuery(
      "SELECT COUNT(*) AS total
       FROM {$this->getTable()}
       WHERE ctt_idorganizacao = :ctt_idorganizacao
       AND ctt_idempresa = :ctt_idempresa
       AND ctt_idorigem = :ctt_idorigem
       AND ctt_origem = :ctt_origem
       {$whereIgnorar}",
      $params,
      true,
      "Contatos validados com sucesso||Erro ao validar contatos"
    );
  }

  private function getLimiteContato(string $origem): int
  {
    if ($origem === 'organizacao') {
      return 0;
    }

    if ($origem === 'usuario') {
      return (int) ($this->contexto->plano->plc_parametro->usuario->contato->limite ?? 0);
    }

    if ($origem === 'empresa') {
      return (int) ($this->contexto->plano->plc_parametro->empresa->contato->limite ?? 0);
    }

    if ($origem === 'membro') {
      return (int) ($this->contexto->plano->plc_parametro->usuario->contato->limite ?? 0);
    }

    if ($origem === 'fornecedor') {
      return (int) ($this->contexto->plano->plc_parametro->fornecedor->contato->limite ?? 0);
    }

    return 0;
  }

  private function getIdEmpresaEscopo(string $origem): int
  {
    return $origem === 'organizacao'
      ? 0
      : (int) ($this->contexto->empresa->emp_id ?? 0);
  }

  private function getIdOrganizacaoEscopo(string $origem, int $idOrigem): int
  {
    return $origem === 'organizacao'
      ? $idOrigem
      : (int) ($this->contexto->organizacao->org_id ?? 0);
  }

  private function validarAcessoOrigem(string $origem, int $idOrigem): object
  {
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

  private function normalizarTipoContato(string $tipo): string
  {
    $tipos = [
      'Celular',
      'Telefone',
      'Email',
      'WhatsApp',
      'Instagram',
      'Facebook',
      'X',
      'LinkedIn',
      'Site',
      'Outro'
    ];

    $tipo = trim($tipo);

    return in_array($tipo, $tipos, true) ? $tipo : 'Outro';
  }

  private function validarConteudoPorTipo(string $tipo, string $conteudo): object
  {
    if (in_array($tipo, ['Celular', 'Telefone', 'WhatsApp'], true)) {
      $numero = Helpers::somenteNumeros($conteudo);

      if ($numero === '') {
        return (new SimpleResponse("Atenção", "Informe um número válido para o contato."))
          ->warning()
          ->noDestroy();
      }

      if (!in_array(strlen($numero), [10, 11], true)) {
        return (new SimpleResponse("Atenção", "Informe um telefone ou celular válido."))
          ->warning()
          ->noDestroy();
      }
    }

    if ($tipo === 'Email' && !filter_var($conteudo, FILTER_VALIDATE_EMAIL)) {
      return (new SimpleResponse("Atenção", "Informe um e-mail válido."))
        ->warning()
        ->noDestroy();
    }

    if ($tipo === 'Site' && !filter_var($conteudo, FILTER_VALIDATE_URL)) {
      return (new SimpleResponse("Atenção", "Informe uma URL válida."))
        ->warning()
        ->noDestroy();
    }

    return (new SimpleResponse("Validação", "Contato válido"))
      ->sucesso()
      ->noDestroy();
  }

  private function normalizarConteudoPorTipo(string $tipo, string $conteudo): string
  {
    $conteudo = trim($conteudo);

    if (in_array($tipo, ['Celular', 'Telefone', 'WhatsApp'], true)) {
      return Helpers::somenteNumeros($conteudo);
    }

    if ($tipo === 'Email') {
      return mb_strtolower($conteudo);
    }

    return $conteudo;
  }
}
