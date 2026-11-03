<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;
use Source\Core\SimpleSession;
use Source\Core\SimpleUpload;

class OrganizacaoModel extends SimpleModel
{
  protected string $tabela = 'organizacao';
  protected string $prefixo = 'org';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getById(int $orgId): object
  {
    return $this->getOrganizacaoById($orgId);
  }

  public function getOrganizacaoById(int $orgId, bool $getContexto = false): object
  {
    if ($orgId <= 0) {
      return (new SimpleResponse("Erro", "Organizacao nao informada"))
        ->erro()
        ->setArrayParams([
          'data' => [],
          'total' => 0
        ])
        ->noDestroy();
    }

    $validacaoAcesso = $this->validarAcessoOrganizacao($orgId);

    if (!$validacaoAcesso->status) {
      return $validacaoAcesso->setArrayParams([
        'data' => [],
        'total' => 0
      ]);
    }

    $getOrganizacao = $this->select(
      "{$this->getTable()}.org_id = :org_id",
      "{$this->getTable()}.*",
      "",
      [
        'org_id' => $orgId
      ],
      "Organizacao encontrada com sucesso||Erro ao buscar organizacao"
    );

    if ($getOrganizacao->status && $getOrganizacao->total > 0) {
      foreach ($getOrganizacao->data as $organizacao) {
        $this->normalizarObjetoOrganizacao($organizacao);

        if ($getContexto) {
          $organizacao->empresas = $this->getEmpresasByOrganizacao($orgId);
        }
      }
    }

    return $getOrganizacao;
  }

  public function getAtivas(): object
  {
    if ($this->usuarioEscopoProjeto()) {
      return $this
        ->where('org_status', 'Ativo')
        ->orderBy('org_nomefantasia')
        ->get();
    }

    $orgId = $this->getContextoOrganizacaoId();

    if ($orgId <= 0) {
      return (new SimpleResponse("Erro", "Organizacao do contexto nao definida"))
        ->erro()
        ->noDestroy();
    }

    return $this
      ->where('org_status', 'Ativo')
      ->where('org_id', $orgId)
      ->orderBy('org_nomefantasia')
      ->get();
  }

  public function getAtivaContexto(): object
  {
    $orgId = $this->getContextoOrganizacaoId();

    if ($orgId <= 0) {
      return (new SimpleResponse("Erro", "Organizacao do contexto nao definida"))
        ->erro()
        ->noDestroy();
    }

    return $this
      ->where('org_status', 'Ativo')
      ->where('org_id', $orgId)
      ->first();
  }

  public function contarOrganizacoesContexto(): object
  {
    $where = "";
    $params = [];

    if (!$this->usuarioEscopoProjeto()) {
      $orgId = $this->getContextoOrganizacaoId();

      if ($orgId <= 0) {
        return (new SimpleResponse("Erro", "Contexto de organizacao nao identificado"))
          ->erro()
          ->setArrayParams([
            'data' => [
              (object) [
                'total' => 0
              ]
            ],
            'total' => 0
          ])
          ->noDestroy();
      }

      $where = "WHERE org_id = :org_id";
      $params['org_id'] = $orgId;
    }

    return $this->rawQuery(
      "SELECT COUNT(*) AS total
       FROM {$this->getTable()}
       {$where}",
      $params,
      true,
      "Total de organizacoes encontrado com sucesso||Erro ao contar organizacoes"
    );
  }

  public function pesquisarOrganizacao(array $filtros): object
  {
    $tipoPesquisa = trim((string) ($filtros['lbTipoPesquisa'] ?? 'inicial'));
    $conteudo = trim((string) ($filtros['lbLik'] ?? ''));
    $status = trim((string) ($filtros['org_status'] ?? ''));
    $ambiente = trim((string) ($filtros['org_ambiente'] ?? ''));
    $statusAmbiente = trim((string) ($filtros['org_statusambiente'] ?? ''));
    $tipoFiltro = trim((string) ($filtros['lbTpf'] ?? ''));
    $campoFiltro = trim((string) ($filtros['lbCmf'] ?? ''));
    $dataInicio = trim((string) ($filtros['lbIni'] ?? ''));
    $dataFim = trim((string) ($filtros['lbFim'] ?? ''));
    $campoOrdem = trim((string) ($filtros['lbOrd'] ?? ''));
    $ordem = strtoupper(trim((string) ($filtros['lbOby'] ?? 'ASC')));
    $limite = trim((string) ($filtros['lbLmt'] ?? '1000'));

    if ($tipoPesquisa === 'simples' && $conteudo !== '' && mb_strlen($conteudo) < 4) {
      return (new SimpleResponse("Atencao", "Informe pelo menos 4 caracteres para pesquisar"))
        ->warning()
        ->setArrayParams([
          'data' => [],
          'total' => 0
        ])
        ->noDestroy();
    }

    $params = [];
    $where = "WHERE 1 = 1";

    if (!$this->usuarioEscopoProjeto()) {
      $orgId = $this->getContextoOrganizacaoId();

      if ($orgId <= 0) {
        return (new SimpleResponse("Erro", "Contexto de organizacao nao identificado"))
          ->erro()
          ->setArrayParams([
            'data' => [],
            'total' => 0
          ])
          ->noDestroy();
      }

      $where .= "
        AND {$this->getTable()}.org_id = :org_idcontexto
      ";

      $params['org_idcontexto'] = $orgId;
    }

    if ($conteudo !== '') {
      $documento = Helpers::somenteNumeros($conteudo);

      $where .= "
        AND (
          {$this->getTable()}.org_id LIKE :conteudo_org_id
          OR {$this->getTable()}.org_chave LIKE :conteudo_org_chave
          OR {$this->getTable()}.org_cnpjcpf LIKE :conteudo_org_cnpjcpf
          OR {$this->getTable()}.org_razaosocial LIKE :conteudo_org_razaosocial
          OR {$this->getTable()}.org_nomefantasia LIKE :conteudo_org_nomefantasia
          OR {$this->getTable()}.org_observacao LIKE :conteudo_org_observacao
          OR {$this->getTable()}.org_descricao LIKE :conteudo_org_descricao
        )
      ";

      $params['conteudo_org_id'] = "%{$conteudo}%";
      $params['conteudo_org_chave'] = "%{$conteudo}%";
      $params['conteudo_org_cnpjcpf'] = '%' . ($documento !== '' ? $documento : $conteudo) . '%';
      $params['conteudo_org_razaosocial'] = "%{$conteudo}%";
      $params['conteudo_org_nomefantasia'] = "%{$conteudo}%";
      $params['conteudo_org_observacao'] = "%{$conteudo}%";
      $params['conteudo_org_descricao'] = "%{$conteudo}%";
    }

    if ($status !== '') {
      $where .= "
        AND {$this->getTable()}.org_status = :org_status
      ";

      $params['org_status'] = $status;
    }

    if ($ambiente !== '') {
      $where .= "
        AND {$this->getTable()}.org_ambiente = :org_ambiente
      ";

      $params['org_ambiente'] = $ambiente;
    }

    if ($statusAmbiente !== '') {
      $where .= "
        AND {$this->getTable()}.org_statusambiente = :org_statusambiente
      ";

      $params['org_statusambiente'] = $statusAmbiente;
    }

    $camposData = [
      'dtcad' => "{$this->getTable()}.org_dtcad",
      'dtalter' => "{$this->getTable()}.org_dtalter",
      'org_dtnasc' => "{$this->getTable()}.org_dtnasc",
      'org_dtmigracao' => "{$this->getTable()}.org_dtmigracao",
      'org_dtultimasincronizacao' => "{$this->getTable()}.org_dtultimasincronizacao",
      'org_dtcad' => "{$this->getTable()}.org_dtcad",
      'org_dtalter' => "{$this->getTable()}.org_dtalter"
    ];

    if ($tipoFiltro !== '' && isset($camposData[$campoFiltro]) && $dataInicio !== '') {
      if ($tipoFiltro === 'Periodo' && $dataFim !== '') {
        $where .= "
          AND DATE({$camposData[$campoFiltro]}) BETWEEN :data_inicio AND :data_fim
        ";

        $params['data_inicio'] = $dataInicio;
        $params['data_fim'] = $dataFim;
      } else {
        $where .= "
          AND DATE({$camposData[$campoFiltro]}) = :data_inicio
        ";

        $params['data_inicio'] = $dataInicio;
      }
    }

    $camposOrdem = [
      'org_id' => "{$this->getTable()}.org_id",
      'org_chave' => "{$this->getTable()}.org_chave",
      'org_status' => "{$this->getTable()}.org_status",
      'org_ambiente' => "{$this->getTable()}.org_ambiente",
      'org_statusambiente' => "{$this->getTable()}.org_statusambiente",
      'org_cnpjcpf' => "{$this->getTable()}.org_cnpjcpf",
      'org_razaosocial' => "{$this->getTable()}.org_razaosocial",
      'org_nomefantasia' => "{$this->getTable()}.org_nomefantasia",
      'org_dtnasc' => "{$this->getTable()}.org_dtnasc",
      'org_dtmigracao' => "{$this->getTable()}.org_dtmigracao",
      'org_dtultimasincronizacao' => "{$this->getTable()}.org_dtultimasincronizacao",
      'org_dtcad' => "{$this->getTable()}.org_dtcad",
      'org_dtalter' => "{$this->getTable()}.org_dtalter"
    ];

    if (!isset($camposOrdem[$campoOrdem])) {
      $campoOrdem = 'org_razaosocial';
    }

    if (!in_array($ordem, ['ASC', 'DESC'], true)) {
      $ordem = 'ASC';
    }

    $limitSql = "";

    if ($limite !== 'all') {
      $limite = (int) $limite;

      if ($limite <= 0) {
        $limite = 1000;
      }

      if ($limite > 5000) {
        $limite = 5000;
      }

      $limitSql = "LIMIT {$limite}";
    }

    $response = $this->rawQuery(
      "SELECT
         {$this->getTable()}.org_id,
         {$this->getTable()}.org_chave,
         {$this->getTable()}.org_status,
         {$this->getTable()}.org_statusclass,
         {$this->getTable()}.org_ambiente,
         {$this->getTable()}.org_url_dedicada,
         {$this->getTable()}.org_banco_dedicado,
         {$this->getTable()}.org_statusambiente,
         {$this->getTable()}.org_dtmigracao,
         {$this->getTable()}.org_dtultimasincronizacao,
         {$this->getTable()}.org_cnpjcpf,
         {$this->getTable()}.org_razaosocial,
         {$this->getTable()}.org_nomefantasia,
         {$this->getTable()}.org_observacao,
         {$this->getTable()}.org_im,
         {$this->getTable()}.org_ie,
         {$this->getTable()}.org_rg,
         {$this->getTable()}.org_dtnasc,
         {$this->getTable()}.org_idsexo,
         {$this->getTable()}.org_idestadocivil,
         {$this->getTable()}.org_idnaturalidade,
         {$this->getTable()}.org_idnaturezajuridica,
         {$this->getTable()}.org_googletag,
         {$this->getTable()}.org_descricao,
         {$this->getTable()}.org_favicon,
         {$this->getTable()}.org_logo,
         {$this->getTable()}.org_planodefundo,
         {$this->getTable()}.org_sidebar,
         {$this->getTable()}.org_home,
         {$this->getTable()}.org_paletacores,
         {$this->getTable()}.org_tema,
         {$this->getTable()}.org_dtcad,
         {$this->getTable()}.org_dtalter,
         (
           SELECT COUNT(*)
           FROM " . DB_PREFIX . "_empresa emp
           WHERE emp.emp_idorganizacao = {$this->getTable()}.org_id
         ) AS total_empresas
       FROM {$this->getTable()}
       {$where}
       ORDER BY {$camposOrdem[$campoOrdem]} {$ordem},
         {$this->getTable()}.org_id ASC
       {$limitSql}",
      $params,
      true,
      "Organizacoes encontradas com sucesso||Erro ao pesquisar organizacoes"
    );

    if ($response->status && !empty($response->data)) {
      foreach ($response->data as $organizacao) {
        $organizacao->org_cnpjcpf_mascara = Helpers::adicionaMascaraCnpjCPf($organizacao->org_cnpjcpf ?? '');
      }
    }

    return $response;
  }

  public function getEmpresasByOrganizacao(int $orgId): object
  {
    $validacaoAcesso = $this->validarAcessoOrganizacao($orgId);

    if (!$validacaoAcesso->status) {
      return $validacaoAcesso->setArrayParams([
        'data' => [],
        'total' => 0
      ]);
    }

    $response = $this->rawQuery(
      "SELECT
         emp_id,
         emp_chave,
         emp_idorganizacao,
         emp_idempresa_pai,
         emp_tipo,
         emp_status,
         emp_statusclass,
         emp_cnpjcpf,
         emp_razaosocial,
         emp_nomefantasia,
         emp_observacao,
         emp_logo,
         emp_dtcad,
         emp_dtalter
       FROM " . DB_PREFIX . "_empresa
       WHERE emp_idorganizacao = :emp_idorganizacao
       ORDER BY
         CASE emp_tipo
           WHEN 'Matriz' THEN 1
           ELSE 2
         END,
         emp_razaosocial ASC,
         emp_id ASC",
      [
        'emp_idorganizacao' => $orgId
      ],
      true,
      "Empresas encontradas com sucesso||Erro ao buscar empresas da organizacao"
    );

    if ($response->status && !empty($response->data)) {
      foreach ($response->data as $empresa) {
        $empresa->emp_cnpjcpf_mascara = Helpers::adicionaMascaraCnpjCPf($empresa->emp_cnpjcpf ?? '');
      }
    }

    return $response;
  }

  public function salvarOrganizacao(array $data): object
  {
    $location = trim((string) ($data['location'] ?? ''));
    unset($data['location']);

    $orgId = (int) ($data['org_id'] ?? 0);
    $contextoOrgId = $this->getContextoOrganizacaoId();

    if ($contextoOrgId <= 0 || $orgId !== $contextoOrgId) {
      return (new SimpleResponse("Permissao Negada", "A organizacao informada nao pertence ao contexto ativo."))
        ->erro()
        ->noDestroy();
    }

    $camposPermitidos = [
      'org_id',
      'org_chave',
      'org_status',
      'org_statusclass',
      'org_cnpjcpf',
      'org_razaosocial',
      'org_nomefantasia',
      'org_observacao',
      'org_im',
      'org_ie',
      'org_rg',
      'org_dtnasc',
      'org_dtnasc_notificacao_wpp',
      'org_dtnasc_notificacao_email',
      'org_nomemae',
      'org_nomepai',
      'org_idsexo',
      'org_idestadocivil',
      'org_idnaturalidade',
      'org_idnaturezajuridica',
      'org_googletag',
      'org_descricao',
      'org_urlamigavel',
      'org_webapp_status',
      'org_webapp_conteudo',
      'org_favicon',
      'org_logo',
      'org_planodefundo',
      'org_sidebar',
      'org_home',
      'org_paletacores',
      'org_tema',
      'org_parametro',
      'org_cadastrobasico_preferencia',
      'org_log'
    ];

    foreach ($data as $key => $value) {
      if (!in_array($key, $camposPermitidos, true)) {
        unset($data[$key]);
        continue;
      }

      if (is_string($value)) {
        $data[$key] = trim($value);
      }
    }

    if ($orgId > 0) {
      $getOrganizacao = $this->getOrganizacaoById($orgId);

      if (!$getOrganizacao->status || $getOrganizacao->total != 1) {
        return (new SimpleResponse("Atencao", "Organizacao nao encontrada."))
          ->warning()
          ->noDestroy();
      }

      $organizacaoAtual = $getOrganizacao->data[0];

      if (array_key_exists('org_urlamigavel', $data) || array_key_exists('org_webapp_status', $data)) {
        $slug = strtolower(trim((string) ($data['org_urlamigavel'] ?? $organizacaoAtual->org_urlamigavel ?? '')));
        $statusWebapp = (string) ($data['org_webapp_status'] ?? $organizacaoAtual->org_webapp_status ?? 'Offline');

        if (!in_array($statusWebapp, ['Offline', 'Publica', 'Privada'], true)) {
          return (new SimpleResponse('Atenção', 'A disponibilidade informada para o Webapp é inválida.'))
            ->warning()
            ->setArrayParams(['campo' => 'org_webapp_status'])
            ->noDestroy();
        }

        if ($slug !== '' && (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || strlen($slug) > 50)) {
          return (new SimpleResponse('Atenção', 'Use uma URL amigável com letras minúsculas, números e hífens, sem espaços.'))
            ->warning()
            ->setArrayParams(['campo' => 'org_urlamigavel'])
            ->noDestroy();
        }

        if ($statusWebapp !== 'Offline' && $slug === '') {
          return (new SimpleResponse('Atenção', 'Informe a URL amigável para publicar a página da organização.'))
            ->warning()
            ->setArrayParams(['campo' => 'org_urlamigavel'])
            ->noDestroy();
        }

        if ($slug !== '') {
          $slugExistente = $this->rawQuery(
            "SELECT org_id FROM {$this->getTable()} WHERE org_urlamigavel = :org_urlamigavel AND org_id <> :org_id LIMIT 1",
            ['org_urlamigavel' => $slug, 'org_id' => $orgId],
            true,
            'Validação da URL concluída||Erro ao validar URL da organização'
          );

          if (!$slugExistente->status) {
            return $slugExistente;
          }

          if (!empty($slugExistente->data)) {
            return (new SimpleResponse('Atenção', 'Esta URL amigável já está sendo usada por outra organização.'))
              ->warning()
              ->setArrayParams(['campo' => 'org_urlamigavel'])
              ->noDestroy();
          }
        }

        $data['org_urlamigavel'] = $slug !== '' ? $slug : null;
      }

      $validacao = $this->validarOrganizacao($data, false, $orgId);

      if (!$validacao->status) {
        return $validacao;
      }

      foreach (['org_logo' => 'logo', 'org_planodefundo' => 'planodefundo', 'org_favicon' => 'favicon'] as $campo => $prefixoUpload) {
        if (!empty($data[$campo]) && preg_match('/^data:[a-zA-Z0-9\.\+\-]+\/[a-zA-Z0-9\.\+\-]+;base64,/', $data[$campo])) {
          $upload = new SimpleUpload('organizacao', $orgId, 0, $orgId);
          $salvarArquivo = $upload->salvarBase64($data[$campo], $prefixoUpload, $prefixoUpload);

          if (!$salvarArquivo->status) {
            return $salvarArquivo;
          }

          if (!empty($organizacaoAtual->{$campo}) && strpos($organizacaoAtual->{$campo}, 'storage/global') !== 0) {
            $upload->moverParaLixeira($organizacaoAtual->{$campo});
          }

          $data[$campo] = $salvarArquivo->arq_caminho;
        }
      }

      $data = $this->normalizarDadosOrganizacao($data);

      unset(
        $data['org_id'],
        $data['org_chave'],
        $data['org_log']
      );

      foreach ($data as $key => $value) {
        if (is_string($value)) {
          $data[$key] = trim($value);
        }

        if ($data[$key] === '') {
          $data[$key] = null;
        }
      }

      if (empty($data)) {
        return (new SimpleResponse("Atencao", "Nenhuma informacao foi enviada para atualizacao."))
          ->warning()
          ->noDestroy();
      }

      $response = $this->update(
        $data,
        [
          'org_id' => $orgId
        ],
        "Organizacao atualizada com sucesso||Erro ao atualizar organizacao"
      );

      if ($response->status) {
        $response->location = Helpers::url("organizacao{$location}");
      }

      return $response;
    }

    return (new SimpleResponse("Permissao Negada", "Organizacao deve ser alterada a partir do contexto ativo."))
      ->erro()
      ->noDestroy();
  }

  public function getOrganizacaoPaginaPublicaPorSlug(string $slug): object
  {
    $slug = strtolower(trim($slug));
    if ($slug === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
      return (new SimpleResponse('Atenção', 'Endereço da página inválido.'))
        ->warning()
        ->setArrayParams(['data' => [], 'total' => 0])
        ->noDestroy();
    }

    return $this->rawQuery(
      "SELECT * FROM {$this->getTable()}
       WHERE org_urlamigavel = :org_urlamigavel
         AND org_status = 'Ativo'
         AND org_webapp_status = 'Publica'
       LIMIT 1",
      ['org_urlamigavel' => $slug],
      true,
      'Organização pública encontrada com sucesso||Erro ao buscar página pública da organização'
    );
  }

  private function validarOrganizacao(array $data, bool $validarObrigatorios = true, int $orgId = 0): object
  {
    if ($validarObrigatorios || array_key_exists('org_cnpjcpf', $data)) {
      $documento = Helpers::somenteNumeros($data['org_cnpjcpf'] ?? '');

      if ($documento === '') {
        return (new SimpleResponse("Atencao", "Informe o documento da organizacao."))
          ->warning()
          ->setArrayParams(['campo' => 'org_cnpjcpf'])
          ->noDestroy();
      }

      if (!in_array(strlen($documento), [11, 14], true)) {
        return (new SimpleResponse("Atencao", "Informe um CPF ou CNPJ valido."))
          ->warning()
          ->setArrayParams(['campo' => 'org_cnpjcpf'])
          ->noDestroy();
      }

      $validarUnicidade = $this->validarUnicidadeOrganizacao($documento, $orgId);

      if (!$validarUnicidade->status) {
        return $validarUnicidade;
      }
    }

    if ($validarObrigatorios || array_key_exists('org_razaosocial', $data)) {
      if (trim((string) ($data['org_razaosocial'] ?? '')) === '') {
        return (new SimpleResponse("Atencao", "Informe a razao social ou nome da organizacao."))
          ->warning()
          ->setArrayParams(['campo' => 'org_razaosocial'])
          ->noDestroy();
      }
    }

    if (isset($data['org_status']) && !in_array($data['org_status'], ['Ativo', 'Inativo', 'Bloqueado', 'Manutencao'], true)) {
      return (new SimpleResponse("Erro", "Status da organizacao invalido"))
        ->erro()
        ->setArrayParams(['campo' => 'org_status'])
        ->noDestroy();
    }

    if (isset($data['org_tema']) && !in_array($data['org_tema'], ['light', 'dark'], true)) {
      return (new SimpleResponse("Erro", "Tema da organizacao invalido"))
        ->erro()
        ->setArrayParams(['campo' => 'org_tema'])
        ->noDestroy();
    }

    return (new SimpleResponse("Validacao", "Dados validos"))
      ->sucesso()
      ->noDestroy();
  }

  private function validarUnicidadeOrganizacao(string $documento, int $orgId = 0): object
  {
    $params = [
      'org_cnpjcpf' => $documento
    ];

    $whereIgnorar = '';

    if ($orgId > 0) {
      $whereIgnorar = "AND org_id != :org_id";
      $params['org_id'] = $orgId;
    }

    $getOrganizacao = $this->rawQuery(
      "SELECT org_id
       FROM {$this->getTable()}
       WHERE org_cnpjcpf = :org_cnpjcpf
       {$whereIgnorar}
       LIMIT 1",
      $params,
      true,
      "Organizacao validada com sucesso||Erro ao validar organizacao"
    );

    if ($getOrganizacao->status && $getOrganizacao->total > 0) {
      return (new SimpleResponse("Atencao", "Ja existe uma organizacao com este documento."))
        ->warning()
        ->setArrayParams([
          'campo' => 'org_cnpjcpf'
        ])
        ->noDestroy();
    }

    return (new SimpleResponse("Validacao", "Organizacao validada com sucesso"))
      ->sucesso()
      ->noDestroy();
  }

  private function normalizarDadosOrganizacao(array $data): array
  {
    if (isset($data['org_cnpjcpf'])) {
      $data['org_cnpjcpf'] = Helpers::somenteNumeros($data['org_cnpjcpf']);
    }

    if (isset($data['org_status'])) {
      $data['org_status'] = in_array(($data['org_status'] ?? 'Ativo'), ['Ativo', 'Inativo', 'Bloqueado', 'Manutencao'], true) ? $data['org_status'] : 'Ativo';
      $data['org_statusclass'] = [
        'Ativo' => 'success',
        'Inativo' => 'danger',
        'Bloqueado' => 'dark',
        'Manutencao' => 'warning'
      ][$data['org_status']];
    }

    if (isset($data['org_tema'])) {
      $data['org_tema'] = in_array(($data['org_tema'] ?? 'light'), ['light', 'dark'], true) ? $data['org_tema'] : 'light';
    }

    if (isset($data['org_sidebar'])) {
      $data['org_sidebar'] = (int) $data['org_sidebar'];
    }

    if (isset($data['org_paletacores']) && is_array($data['org_paletacores'])) {
      $data['org_paletacores'] = json_encode($data['org_paletacores'], JSON_UNESCAPED_UNICODE);
    }

    if (isset($data['org_parametro']) && is_array($data['org_parametro'])) {
      $data['org_parametro'] = json_encode($data['org_parametro'], JSON_UNESCAPED_UNICODE);
    }

    if (isset($data['org_cadastrobasico_preferencia']) && is_array($data['org_cadastrobasico_preferencia'])) {
      $data['org_cadastrobasico_preferencia'] = json_encode($data['org_cadastrobasico_preferencia'], JSON_UNESCAPED_UNICODE);
    }

    if (isset($data['org_cnpjcpf'])) {
      if (strlen($data['org_cnpjcpf']) === 11) {
        $data['org_im'] = null;
        $data['org_ie'] = null;
        $data['org_idnaturezajuridica'] = null;
      }

      if (strlen($data['org_cnpjcpf']) === 14) {
        $data['org_rg'] = null;
        $data['org_nomemae'] = null;
        $data['org_nomepai'] = null;
        $data['org_idsexo'] = null;
        $data['org_idestadocivil'] = null;
        $data['org_idnaturalidade'] = null;
      }
    }

    return $data;
  }

  private function normalizarObjetoOrganizacao(object $organizacao): void
  {
    if (!is_null($organizacao->org_parametro ?? null) && is_string($organizacao->org_parametro)) {
      $organizacao->org_parametro = json_decode($organizacao->org_parametro);
    }

    if (!is_null($organizacao->org_cadastrobasico_preferencia ?? null) && is_string($organizacao->org_cadastrobasico_preferencia)) {
      $organizacao->org_cadastrobasico_preferencia = json_decode($organizacao->org_cadastrobasico_preferencia);
    }

    $organizacao->org_cnpjcpf_mascara = Helpers::adicionaMascaraCnpjCPf($organizacao->org_cnpjcpf ?? '');
  }

  private function validarAcessoOrganizacao(int $orgId): object
  {
    $contextoOrgId = $this->getContextoOrganizacaoId();

    if ($contextoOrgId > 0 && $contextoOrgId === $orgId) {
      return (new SimpleResponse("Validacao", "Acesso permitido"))
        ->sucesso()
        ->noDestroy();
    }

    return (new SimpleResponse("Permissao Negada", "Voce nao tem acesso a esta organizacao"))
      ->erro()
      ->setArrayParams([
        'org_id' => $orgId,
        'contexto_org_id' => $contextoOrgId
      ])
      ->noDestroy();
  }

  private function getContextoOrganizacaoId(): int
  {
    $contexto = (new SimpleSession())->get('CONTEXTO') ?? null;

    if (!is_null($contexto) && isset($contexto->organizacao->org_id)) {
      return (int) $contexto->organizacao->org_id;
    }

    $usuario = (new SimpleSession())->get('USUARIO') ?? null;

    if (!is_null($usuario) && isset($usuario->organizacao->org_id)) {
      return (int) $usuario->organizacao->org_id;
    }

    if (!is_null($usuario) && isset($usuario->usu_idorganizacao)) {
      return (int) $usuario->usu_idorganizacao;
    }

    if (defined('CONF_ORG_ID') && trim((string) CONF_ORG_ID) !== '') {
      return (int) CONF_ORG_ID;
    }

    return 0;
  }

  private function usuarioEscopoProjeto(): bool
  {
    $usuario = (new SimpleSession())->get('USUARIO') ?? null;

    if (is_null($usuario)) {
      return false;
    }

    if (($usuario->escopo_maximo ?? '') === 'Projeto') {
      return true;
    }

    if (($usuario->nva_escopo_maximo ?? '') === 'Projeto') {
      return true;
    }

    $permissoes = (array) ($usuario->permissoes ?? []);

    return isset($permissoes['projeto.visualizar'])
      || isset($permissoes['projeto.atualizar']);
  }
}
