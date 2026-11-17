<?php

namespace Source\Model;

use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;
use Source\Core\SimpleUpload;

class ArquivoModel extends SimpleModel
{
  protected string $tabela = 'arquivo';
  protected string $prefixo = 'arq';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getArquivosByConfig(object $config): object
  {
    return $this->rawQuery(
      "SELECT *
       FROM {$this->getTable()}
       WHERE arq_idorganizacao = :arq_idorganizacao
       AND arq_idempresa IN (:arq_idempresa, 0)
       AND arq_idorigem = :arq_idorigem
       AND arq_origem = :arq_origem
       AND arq_status = 'Ativo'
       ORDER BY arq_id ASC",
      [
        'arq_idorganizacao' => (int) $config->idorganizacao,
        'arq_idempresa' => (int) $config->idempresa,
        'arq_idorigem' => (int) $config->idorigem,
        'arq_origem' => (string) $config->origem
      ],
      true,
      "Arquivos encontrados com sucesso||Erro ao buscar arquivos"
    );
  }

  public function getArquivoById(int $id): object
  {
    $params = [
      'arq_id' => $id
    ];

    $whereOrganizacao = "";
    $whereEmpresa = "";

    if (!$this->usuarioEscopoProjeto()) {
      $whereOrganizacao = "AND arq_idorganizacao = :arq_idorganizacao";
      $whereEmpresa = "AND arq_idempresa IN (:arq_idempresa, 0)";
      $params['arq_idorganizacao'] = (int) ($this->contexto->organizacao->org_id ?? 0);
      $params['arq_idempresa'] = (int) ($this->contexto->empresa->emp_id ?? 0);
    }

    return $this->rawQuery(
      "SELECT *
       FROM {$this->getTable()}
       WHERE arq_id = :arq_id
       {$whereOrganizacao}
       {$whereEmpresa}
       LIMIT 1",
      $params,
      true,
      "Arquivo encontrado com sucesso||Erro ao buscar arquivo"
    );
  }

  public function salvarArquivo(array $data): object
  {
    $arqId = (int) ($data['arq_id'] ?? 0);
    $arqOrigem = trim((string) ($data['arq_origem'] ?? ''));
    $arqIdOrigem = (int) ($data['arq_idorigem'] ?? 0);
    $storageEscopo = $this->getStorageEscopo($arqOrigem, $arqIdOrigem, $data);
    $idEmpresaEscopo = $storageEscopo->idempresa;
    $idOrganizacaoEscopo = $storageEscopo->idorganizacao;

    if (
      $idOrganizacaoEscopo <= 0 ||
      ($arqOrigem !== 'organizacao' && $idEmpresaEscopo <= 0)
    ) {
      return (new SimpleResponse("Erro", "Contexto de organização ou empresa não identificado."))
        ->erro()
        ->noDestroy();
    }

    if ($arqOrigem === '') {
      return (new SimpleResponse("Atenção", "Origem do arquivo não informada."))
        ->warning()
        ->noDestroy();
    }

    if ($arqIdOrigem <= 0) {
      return (new SimpleResponse("Atenção", "Registro de origem do arquivo não informado."))
        ->warning()
        ->noDestroy();
    }

    if ($arqOrigem === 'lancamento') {
      $lancamento = $this->rawQuery(
        "SELECT lan_status, lan_tipoacao, lan_tipo, lan_natureza
         FROM " . DB_PREFIX . "_lancamento
         WHERE lan_id = :lan_id
           AND lan_idorganizacao = :lan_idorganizacao
           AND lan_idempresa = :lan_idempresa
         LIMIT 1",
        [
          'lan_id' => $arqIdOrigem,
          'lan_idorganizacao' => (int) ($this->contexto->organizacao->org_id ?? 0),
          'lan_idempresa' => (int) ($this->contexto->empresa->emp_id ?? 0)
        ],
        true,
        'Lançamento encontrado com sucesso||Lançamento não encontrado'
      );
      $statusLancamento = trim((string) ($lancamento->data[0]->lan_status ?? ''));
      $tipoAcaoLancamento = trim((string) ($lancamento->data[0]->lan_tipoacao ?? ''));
      if ($lancamento->total !== 1 || in_array($statusLancamento, ['Estornado', 'Corrigido'], true) || ($statusLancamento === '' && $tipoAcaoLancamento === 'Correcao')) {
        return (new SimpleResponse("Atenção", "Lançamentos estornados ou corrigidos permitem apenas a visualização dos anexos."))
          ->warning()
          ->noDestroy();
      }
      if (($lancamento->data[0]->lan_tipo ?? '') === 'Transferencia' && ($lancamento->data[0]->lan_natureza ?? '') === 'Entrada') {
        return (new SimpleResponse("Atenção", "O comprovante da transferência deve ser anexado somente ao lançamento de saída, na conta de origem."))
          ->warning()
          ->noDestroy();
      }
    }

    if ($arqOrigem === 'transferencia') {
      $validacaoTransferencia = $this->validarTransferenciaOrigem($arqIdOrigem);
      if (!$validacaoTransferencia->status) {
        return $validacaoTransferencia;
      }
    }

    $validacaoAcesso = $this->validarAcessoOrigem($arqOrigem, $arqIdOrigem);

    if (!$validacaoAcesso->status) {
      return $validacaoAcesso;
    }

    $data['arq_idorganizacao'] = $idOrganizacaoEscopo;
    $data['arq_idempresa'] = $idEmpresaEscopo;
    $data['arq_idorigem'] = $arqIdOrigem;
    $data['arq_origem'] = $arqOrigem;

    $data['arq_status'] = in_array(($data['arq_status'] ?? 'Ativo'), ['Ativo', 'Inativo'], true)
      ? $data['arq_status']
      : 'Ativo';

    $data['arq_statusclass'] = $data['arq_status'] === 'Ativo' ? 'success' : 'danger';

    if ($arqId <= 0) {
      $base64 = trim((string) ($data['arq_base64'] ?? ''));

      if ($base64 === '') {
        return (new SimpleResponse("Atenção", "Arquivo não informado."))
          ->warning()
          ->noDestroy();
      }

      $totalArquivos = $this->totalArquivosOrigem($arqOrigem, $arqIdOrigem, $idOrganizacaoEscopo, $idEmpresaEscopo);
      $limite = $this->limiteArquivo($arqOrigem);

      if ($limite > 0 && $totalArquivos >= $limite) {
        return (new SimpleResponse("Atenção", "O limite de arquivos permitido pelo plano foi atingido."))
          ->warning()
          ->noDestroy();
      }

      $prefixo = trim((string) ($data['arq_tipo'] ?? 'arquivo'));
      $nomeOriginal = trim((string) ($data['arq_nomeoriginal'] ?? ($data['arq_nome'] ?? '')));

      $upload = new SimpleUpload($arqOrigem, $arqIdOrigem, $idEmpresaEscopo, $idOrganizacaoEscopo);
      $uploadResponse = $upload->salvarBase64($base64, $prefixo, $nomeOriginal);

      if (!$uploadResponse->status) {
        return $uploadResponse;
      }

      $data['arq_nome'] = $data['arq_nome'] ?? $uploadResponse->arq_nomeoriginal;
      $data['arq_nomeoriginal'] = $uploadResponse->arq_nomeoriginal;
      $data['arq_tipo'] = $uploadResponse->arq_tipo;
      $data['arq_mime'] = $uploadResponse->arq_mime;
      $data['arq_extensao'] = $uploadResponse->arq_extensao;
      $data['arq_tamanho_bytes'] = $uploadResponse->arq_tamanho_bytes;
      $data['arq_tamanho'] = $uploadResponse->arq_tamanho;
      $data['arq_medida'] = $uploadResponse->arq_medida;
      $data['arq_largura'] = $uploadResponse->arq_largura;
      $data['arq_altura'] = $uploadResponse->arq_altura;
      $data['arq_duracao'] = $uploadResponse->arq_duracao;
      $data['arq_storage'] = $uploadResponse->arq_storage;
      $data['arq_diretorio'] = $uploadResponse->arq_diretorio;
      $data['arq_nomearquivo'] = $uploadResponse->arq_nomearquivo;
      $data['arq_caminho'] = $uploadResponse->arq_caminho;
      $data['arq_hash'] = $uploadResponse->arq_hash;
    }

    $camposPermitidos = [
      'arq_id',
      'arq_status',
      'arq_statusclass',
      'arq_nome',
      'arq_nomeoriginal',
      'arq_descricao',
      'arq_tipo',
      'arq_mime',
      'arq_extensao',
      'arq_tamanho_bytes',
      'arq_tamanho',
      'arq_medida',
      'arq_largura',
      'arq_altura',
      'arq_duracao',
      'arq_storage',
      'arq_diretorio',
      'arq_nomearquivo',
      'arq_caminho',
      'arq_hash',
      'arq_idorganizacao',
      'arq_idempresa',
      'arq_idorigem',
      'arq_origem',
      'arq_log'
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

    if ($arqId > 0) {
      unset(
        $data['arq_id'],
        $data['arq_idorganizacao'],
        $data['arq_idempresa'],
        $data['arq_idorigem'],
        $data['arq_origem'],
        $data['arq_nomeoriginal'],
        $data['arq_tipo'],
        $data['arq_mime'],
        $data['arq_extensao'],
        $data['arq_tamanho_bytes'],
        $data['arq_tamanho'],
        $data['arq_medida'],
        $data['arq_largura'],
        $data['arq_altura'],
        $data['arq_duracao'],
        $data['arq_storage'],
        $data['arq_diretorio'],
        $data['arq_nomearquivo'],
        $data['arq_caminho'],
        $data['arq_hash'],
        $data['arq_log']
      );

      return $this->update(
        $data,
        [
          'arq_id' => $arqId,
          'arq_idorganizacao' => $idOrganizacaoEscopo,
          'arq_idempresa' => $idEmpresaEscopo,
          'arq_idorigem' => $arqIdOrigem,
          'arq_origem' => $arqOrigem
        ],
        "Arquivo atualizado com sucesso||Erro ao atualizar arquivo"
      );
    }

    unset($data['arq_id']);

    return $this->insert(
      $data,
      "Arquivo cadastrado com sucesso||Erro ao cadastrar arquivo"
    );
  }

  public function deleteArquivo(int $id, array $data = []): object
  {
    $arqOrigem = trim((string) ($data['arq_origem'] ?? ''));
    $arqIdOrigem = (int) ($data['arq_idorigem'] ?? 0);

    if ($id <= 0 || $arqOrigem === '' || $arqIdOrigem <= 0) {
      return (new SimpleResponse("Atenção", "Arquivo não informado."))
        ->warning()
        ->noDestroy();
    }

    if ($arqOrigem === 'lancamento') {
      $lancamento = $this->rawQuery(
        "SELECT lan_status, lan_tipoacao, lan_tipo, lan_natureza
         FROM " . DB_PREFIX . "_lancamento
         WHERE lan_id = :lan_id
           AND lan_idorganizacao = :lan_idorganizacao
           AND lan_idempresa = :lan_idempresa
         LIMIT 1",
        [
          'lan_id' => $arqIdOrigem,
          'lan_idorganizacao' => (int) ($this->contexto->organizacao->org_id ?? 0),
          'lan_idempresa' => (int) ($this->contexto->empresa->emp_id ?? 0)
        ],
        true,
        'Lançamento encontrado com sucesso||Lançamento não encontrado'
      );
      $statusLancamento = trim((string) ($lancamento->data[0]->lan_status ?? ''));
      $tipoAcaoLancamento = trim((string) ($lancamento->data[0]->lan_tipoacao ?? ''));
      if ($lancamento->total !== 1 || in_array($statusLancamento, ['Estornado', 'Corrigido'], true) || ($statusLancamento === '' && $tipoAcaoLancamento === 'Correcao')) {
        return (new SimpleResponse("Atenção", "Lançamentos estornados ou corrigidos permitem apenas a visualização dos anexos."))
          ->warning()
          ->noDestroy();
      }
      if (($lancamento->data[0]->lan_tipo ?? '') === 'Transferencia' && ($lancamento->data[0]->lan_natureza ?? '') === 'Entrada') {
        return (new SimpleResponse("Atenção", "O comprovante da transferência deve ser anexado somente ao lançamento de saída, na conta de origem."))
          ->warning()
          ->noDestroy();
      }
    }

    if ($arqOrigem === 'transferencia') {
      $validacaoTransferencia = $this->validarTransferenciaOrigem($arqIdOrigem);
      if (!$validacaoTransferencia->status) {
        return $validacaoTransferencia;
      }
    }

    $storageEscopo = $this->getStorageEscopo($arqOrigem, $arqIdOrigem, $data);
    $idEmpresaEscopo = $storageEscopo->idempresa;
    $idOrganizacaoEscopo = $storageEscopo->idorganizacao;

    $validacaoAcesso = $this->validarAcessoOrigem($arqOrigem, $arqIdOrigem);

    if (!$validacaoAcesso->status) {
      return $validacaoAcesso;
    }

    $getArquivo = $this->rawQuery(
      "SELECT *
       FROM {$this->getTable()}
       WHERE arq_id = :arq_id
       AND arq_idorganizacao = :arq_idorganizacao
       AND arq_idempresa = :arq_idempresa
       AND arq_idorigem = :arq_idorigem
       AND arq_origem = :arq_origem
       LIMIT 1",
      [
        'arq_id' => $id,
        'arq_idorganizacao' => $idOrganizacaoEscopo,
        'arq_idempresa' => $idEmpresaEscopo,
        'arq_idorigem' => $arqIdOrigem,
        'arq_origem' => $arqOrigem
      ],
      true,
      "Arquivo encontrado com sucesso||Erro ao buscar arquivo"
    );

    if (!$getArquivo->status || $getArquivo->total != 1) {
      return (new SimpleResponse("Atenção", "Arquivo não encontrado."))
        ->warning()
        ->noDestroy();
    }

    $arquivo = $getArquivo->data[0];

    $upload = new SimpleUpload($arqOrigem, $arqIdOrigem, $idEmpresaEscopo, $idOrganizacaoEscopo);
    $moverArquivo = $upload->moverParaLixeira($arquivo->arq_caminho);

    if (!$moverArquivo->status) {
      return $moverArquivo;
    }

    return $this->delete(
      [
        'arq_id' => $id,
        'arq_idorganizacao' => $idOrganizacaoEscopo,
        'arq_idempresa' => $idEmpresaEscopo,
        'arq_idorigem' => $arqIdOrigem,
        'arq_origem' => $arqOrigem
      ],
      "Arquivo excluído com sucesso||Erro ao excluir arquivo"
    );
  }

  public function substituirArquivo(int $id, array $data): object
  {
    $arquivoAtual = $this->getArquivoById($id);
    if (!$arquivoAtual->status || $arquivoAtual->total !== 1) {
      return (new SimpleResponse('Atenção', 'Arquivo não encontrado.'))->warning()->noDestroy();
    }

    $arquivo = $arquivoAtual->data[0];
    $origem = trim((string) ($data['arq_origem'] ?? ''));
    $idOrigem = (int) ($data['arq_idorigem'] ?? 0);
    if ($origem === '' || $idOrigem <= 0 || $origem !== ($arquivo->arq_origem ?? '') || $idOrigem !== (int) ($arquivo->arq_idorigem ?? 0)) {
      return (new SimpleResponse('Atenção', 'O arquivo não pertence ao lançamento informado.'))->warning()->noDestroy();
    }
    if (($arquivo->arq_status ?? '') !== 'Ativo') {
      return (new SimpleResponse('Atenção', 'Somente arquivos ativos podem ser substituídos.'))->warning()->noDestroy();
    }
    if ($origem === 'transferencia') {
      $validacaoTransferencia = $this->validarTransferenciaOrigem($idOrigem);
      if (!$validacaoTransferencia->status) {
        return $validacaoTransferencia;
      }
    }

    $this->beginTransaction();
    try {
      $inativado = $this->update([
        'arq_status' => 'Inativo',
        'arq_statusclass' => 'danger'
      ], ['arq_id' => $id], 'Arquivo anterior substituído||Erro ao preparar substituição do arquivo');
      if (!$inativado->status) {
        throw new \RuntimeException($inativado->text ?? 'Não foi possível preparar a substituição do arquivo.');
      }

      $data['arq_id'] = 0;
      $novo = $this->salvarArquivo($data);
      if (!$novo->status) {
        throw new \RuntimeException($novo->text ?? 'Não foi possível salvar o novo arquivo.');
      }

      $this->commit();
      return (new SimpleResponse('Sucesso', 'Comprovante substituído com sucesso.'))->sucesso()->noDestroy();
    } catch (\Throwable $e) {
      $this->rollback();
      return (new SimpleResponse('Erro', $e->getMessage()))->erro()->noDestroy();
    }
  }

  private function totalArquivosOrigem(string $origem, int $idOrigem, int $idOrganizacaoEscopo, int $idEmpresaEscopo): int
  {
    $getTotal = $this->rawQuery(
      "SELECT COUNT(*) AS total
       FROM {$this->getTable()}
       WHERE arq_idorganizacao = :arq_idorganizacao
       AND arq_idempresa = :arq_idempresa
       AND arq_idorigem = :arq_idorigem
       AND arq_origem = :arq_origem
       AND arq_status = 'Ativo'",
      [
        'arq_idorganizacao' => $idOrganizacaoEscopo,
        'arq_idempresa' => $idEmpresaEscopo,
        'arq_idorigem' => $idOrigem,
        'arq_origem' => $origem
      ],
      true,
      "Arquivos encontrados com sucesso||Erro ao buscar arquivos"
    );

    return $getTotal->status ? (int) ($getTotal->data[0]->total ?? 0) : 0;
  }

  private function limiteArquivo(string $origem): int
  {
    if ($origem === 'organizacao') {
      return 0;
    }

    if ($origem === 'usuario') {
      return (int) ($this->contexto->plano->plc_parametro->usuario->arquivo->limite ?? 1);
    }

    if ($origem === 'empresa') {
      return (int) ($this->contexto->plano->plc_parametro->empresa->arquivo->limite ?? 1);
    }

    if ($origem === 'fornecedor') {
      return (int) ($this->contexto->plano->plc_parametro->fornecedor->arquivo->limite ?? 0);
    }

    return 1;
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

  private function validarTransferenciaOrigem(int $idOrigem): object
  {
    $transferencia = $this->rawQuery(
      "SELECT lan_status
       FROM " . DB_PREFIX . "_lancamento
       WHERE lan_id = :lan_id
         AND lan_idorganizacao = :lan_idorganizacao
         AND lan_idempresa = :lan_idempresa
         AND lan_tipo = 'Transferencia'
         AND lan_natureza = 'Saida'
       LIMIT 1",
      [
        'lan_id' => $idOrigem,
        'lan_idorganizacao' => (int) ($this->contexto->organizacao->org_id ?? 0),
        'lan_idempresa' => (int) ($this->contexto->empresa->emp_id ?? 0)
      ],
      true,
      'Transferência encontrada com sucesso||Transferência não encontrada'
    );

    if (!$transferencia->status || $transferencia->total !== 1) {
      return (new SimpleResponse('Atenção', 'Transferência não encontrada ou fora do contexto atual.'))
        ->warning()
        ->noDestroy();
    }

    if (($transferencia->data[0]->lan_status ?? '') === 'Estornado') {
      return (new SimpleResponse('Atenção', 'Transferências estornadas permitem apenas a visualização dos anexos.'))
        ->warning()
        ->noDestroy();
    }

    return (new SimpleResponse('Validação', 'Transferência disponível para alteração do anexo.'))
      ->sucesso()
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

  private function getStorageEscopo(string $origem, int $idOrigem, array $data = []): object
  {
    $idOrganizacao = (int) ($data['arq_idorganizacao'] ?? $this->contexto->organizacao->org_id ?? 0);
    $idEmpresa = (int) ($data['arq_idempresa'] ?? $this->contexto->empresa->emp_id ?? 0);

    if ($origem === 'organizacao') {
      return (object) [
        'idorganizacao' => $idOrigem,
        'idempresa' => 0
      ];
    }

    if ($origem === 'empresa') {
      $empresa = $this->getEmpresaStorage($idOrigem);

      return (object) [
        'idorganizacao' => $empresa->idorganizacao ?: $idOrganizacao,
        'idempresa' => $idOrigem
      ];
    }

    if ($origem === 'usuario') {
      $usuario = $this->getUsuarioStorage($idOrigem);

      return (object) [
        'idorganizacao' => $usuario->idorganizacao ?: $idOrganizacao,
        'idempresa' => $usuario->idempresa ?: $idEmpresa
      ];
    }

    return (object) [
      'idorganizacao' => $idOrganizacao,
      'idempresa' => $idEmpresa
    ];
  }

  private function getEmpresaStorage(int $empId): object
  {
    $response = $this->rawQuery(
      "SELECT emp_idorganizacao
       FROM " . DB_PREFIX . "_empresa
       WHERE emp_id = :emp_id
       LIMIT 1",
      [
        'emp_id' => $empId
      ],
      true,
      "Empresa encontrada com sucesso||Erro ao buscar empresa do arquivo"
    );

    return (object) [
      'idorganizacao' => $response->status && $response->total > 0
        ? (int) ($response->data[0]->emp_idorganizacao ?? 0)
        : 0
    ];
  }

  private function getUsuarioStorage(int $usuId): object
  {
    $contextoOrgId = (int) ($this->contexto->organizacao->org_id ?? 0);
    $contextoEmpId = (int) ($this->contexto->empresa->emp_id ?? 0);

    if ($contextoOrgId > 0 && $contextoEmpId > 0) {
      return (object) [
        'idorganizacao' => $contextoOrgId,
        'idempresa' => $contextoEmpId
      ];
    }

    $response = $this->rawQuery(
      "SELECT usu_idorganizacao, usu_idempresa
       FROM " . DB_PREFIX . "_usuario
       WHERE usu_id = :usu_id
       LIMIT 1",
      [
        'usu_id' => $usuId
      ],
      true,
      "Usuário encontrado com sucesso||Erro ao buscar usuário do arquivo"
    );

    return (object) [
      'idorganizacao' => $response->status && $response->total > 0
        ? (int) ($response->data[0]->usu_idorganizacao ?? 0)
        : 0,
      'idempresa' => $response->status && $response->total > 0
        ? (int) ($response->data[0]->usu_idempresa ?? 0)
        : 0
    ];
  }
}
