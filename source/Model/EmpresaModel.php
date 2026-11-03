<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;
use Source\Core\SimpleUpload;
use Source\Service\LimitePlanoService;

class EmpresaModel extends SimpleModel
{
  protected string $tabela = 'empresa';
  protected string $prefixo = 'emp';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getEmpresaPrincipalAtivaPorOrganizacao(int $orgId): object
  {
    return $this->rawQuery(
      "SELECT *
       FROM {$this->getTable()}
       WHERE emp_idorganizacao = :emp_idorganizacao
       AND emp_status = 'Ativo'
       ORDER BY
         CASE emp_tipo
           WHEN 'Matriz' THEN 1
           ELSE 2
         END,
         emp_id ASC
       LIMIT 1",
      [
        'emp_idorganizacao' => $orgId
      ],
      true,
      "Empresa encontrada com sucesso||Erro ao buscar empresa principal"
    );
  }

  public function getEmpresaPaginaPublicaPorSlug(string $slug): object
  {
    $slug = strtolower(trim($slug));
    if ($slug === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
      return (new SimpleResponse('Atenção', 'Endereço da página inválido.'))
        ->warning()
        ->setArrayParams(['data' => [], 'total' => 0])
        ->noDestroy();
    }

    $organizacaoTable = DB_PREFIX . '_organizacao';
    return $this->rawQuery(
      "SELECT empresa.*, organizacao.org_nomefantasia, organizacao.org_razaosocial
       FROM {$this->getTable()} empresa
       INNER JOIN {$organizacaoTable} organizacao
         ON organizacao.org_id = empresa.emp_idorganizacao
       WHERE empresa.emp_urlamigavel = :empresa_slug_publico
         AND empresa.emp_status = 'Ativo'
         AND empresa.emp_webapp_status = 'Publica'
         AND organizacao.org_status = 'Ativo'
       LIMIT 1",
      ['empresa_slug_publico' => $slug],
      true,
      'Empresa pública encontrada com sucesso||Erro ao buscar página pública da empresa'
    );
  }

  public function contarEmpresasContexto(bool $ignorarEscopo = false): object
  {
    $escopoUsuario = $this->usuarioLogado->escopo_maximo ?? 'Empresa';

    if (empty($this->contexto->organizacao->org_id)) {
      return (new SimpleResponse("Erro", "Contexto de organização não identificado"))
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

    $where = "
      WHERE emp_idorganizacao = :emp_idorganizacao
      AND emp_status = 'Ativo'
    ";

    $params = [
      'emp_idorganizacao' => (int) $this->contexto->organizacao->org_id
    ];

    if (!$ignorarEscopo && $escopoUsuario === 'Empresa') {
      if (empty($this->contexto->empresa->emp_id)) {
        return (new SimpleResponse("Erro", "Contexto de empresa não identificado"))
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

      $where .= "
        AND emp_id = :emp_id
      ";

      $params['emp_id'] = (int) $this->contexto->empresa->emp_id;
    }

    return $this->rawQuery(
      "SELECT COUNT(*) AS total
       FROM {$this->getTable()}
       {$where}",
      $params,
      true,
      "Total de empresas encontrado com sucesso||Erro ao contar empresas"
    );
  }

  public function pesquisarEmpresa(array $filtros): object
  {
    $escopoUsuario = $this->usuarioLogado->escopo_maximo ?? 'Empresa';

    if (empty($this->contexto->organizacao->org_id)) {
      return (new SimpleResponse("Erro", "Contexto de organização não identificado"))
        ->erro()
        ->setArrayParams([
          'data' => [],
          'total' => 0
        ])
        ->noDestroy();
    }

    $tipoPesquisa = trim((string) ($filtros['lbTipoPesquisa'] ?? 'inicial'));
    $conteudo = trim((string) ($filtros['lbLik'] ?? ''));
    $status = trim((string) ($filtros['emp_status'] ?? ''));
    $tipo = trim((string) ($filtros['emp_tipo'] ?? ''));
    $tipoFiltro = trim((string) ($filtros['lbTpf'] ?? ''));
    $campoFiltro = trim((string) ($filtros['lbCmf'] ?? ''));
    $dataInicio = trim((string) ($filtros['lbIni'] ?? ''));
    $dataFim = trim((string) ($filtros['lbFim'] ?? ''));
    $campoOrdem = trim((string) ($filtros['lbOrd'] ?? ''));
    $ordem = strtoupper(trim((string) ($filtros['lbOby'] ?? 'ASC')));
    $limite = trim((string) ($filtros['lbLmt'] ?? '1000'));

    if ($tipoPesquisa === 'simples' && $conteudo !== '' && mb_strlen($conteudo) < 4) {
      return (new SimpleResponse("Atenção", "Informe pelo menos 4 caracteres para pesquisar"))
        ->warning()
        ->setArrayParams([
          'data' => [],
          'total' => 0
        ])
        ->noDestroy();
    }

    $params = [
      'emp_idorganizacao' => (int) $this->contexto->organizacao->org_id
    ];

    $where = "
      WHERE {$this->getTable()}.emp_idorganizacao = :emp_idorganizacao
    ";

    if ($escopoUsuario === 'Empresa') {
      if (empty($this->contexto->empresa->emp_id)) {
        return (new SimpleResponse("Erro", "Contexto de empresa não identificado"))
          ->erro()
          ->setArrayParams([
            'data' => [],
            'total' => 0
          ])
          ->noDestroy();
      }

      $where .= "
        AND {$this->getTable()}.emp_id = :emp_id
      ";

      $params['emp_id'] = (int) $this->contexto->empresa->emp_id;
    }

    if ($conteudo !== '') {
      $where .= "
        AND (
          {$this->getTable()}.emp_id LIKE :conteudo_emp_id
          OR {$this->getTable()}.emp_chave LIKE :conteudo_emp_chave
          OR {$this->getTable()}.emp_cnpjcpf LIKE :conteudo_emp_cnpjcpf
          OR {$this->getTable()}.emp_razaosocial LIKE :conteudo_emp_razaosocial
          OR {$this->getTable()}.emp_nomefantasia LIKE :conteudo_emp_nomefantasia
          OR {$this->getTable()}.emp_observacao LIKE :conteudo_emp_observacao
          OR {$this->getTable()}.emp_descricao LIKE :conteudo_emp_descricao
        )
      ";

      $params['conteudo_emp_id'] = "%{$conteudo}%";
      $params['conteudo_emp_chave'] = "%{$conteudo}%";
      $params['conteudo_emp_cnpjcpf'] = "%{$conteudo}%";
      $params['conteudo_emp_razaosocial'] = "%{$conteudo}%";
      $params['conteudo_emp_nomefantasia'] = "%{$conteudo}%";
      $params['conteudo_emp_observacao'] = "%{$conteudo}%";
      $params['conteudo_emp_descricao'] = "%{$conteudo}%";
    }

    if ($status !== '') {
      $where .= "
        AND {$this->getTable()}.emp_status = :emp_status
      ";

      $params['emp_status'] = $status;
    }

    if ($tipo !== '') {
      $where .= "
        AND {$this->getTable()}.emp_tipo = :emp_tipo
      ";

      $params['emp_tipo'] = $tipo;
    }

    $camposData = [
      'dtcad' => "{$this->getTable()}.emp_dtcad",
      'dtalter' => "{$this->getTable()}.emp_dtalter",
      'emp_dtnasc' => "{$this->getTable()}.emp_dtnasc",
      'emp_dtcad' => "{$this->getTable()}.emp_dtcad",
      'emp_dtalter' => "{$this->getTable()}.emp_dtalter"
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
      'emp_id' => "{$this->getTable()}.emp_id",
      'emp_chave' => "{$this->getTable()}.emp_chave",
      'emp_tipo' => "{$this->getTable()}.emp_tipo",
      'emp_status' => "{$this->getTable()}.emp_status",
      'emp_cnpjcpf' => "{$this->getTable()}.emp_cnpjcpf",
      'emp_razaosocial' => "{$this->getTable()}.emp_razaosocial",
      'emp_nomefantasia' => "{$this->getTable()}.emp_nomefantasia",
      'emp_dtnasc' => "{$this->getTable()}.emp_dtnasc",
      'emp_dtcad' => "{$this->getTable()}.emp_dtcad",
      'emp_dtalter' => "{$this->getTable()}.emp_dtalter"
    ];

    if (!isset($camposOrdem[$campoOrdem])) {
      $campoOrdem = 'emp_razaosocial';
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
         {$this->getTable()}.emp_id,
         {$this->getTable()}.emp_chave,
         {$this->getTable()}.emp_idorganizacao,
         {$this->getTable()}.emp_idempresa_pai,
         {$this->getTable()}.emp_tipo,
         {$this->getTable()}.emp_status,
         {$this->getTable()}.emp_statusclass,
         {$this->getTable()}.emp_status_motivo,
         {$this->getTable()}.emp_cnpjcpf,
         {$this->getTable()}.emp_razaosocial,
         {$this->getTable()}.emp_nomefantasia,
         {$this->getTable()}.emp_observacao,
         {$this->getTable()}.emp_im,
         {$this->getTable()}.emp_ie,
         {$this->getTable()}.emp_rg,
         {$this->getTable()}.emp_dtnasc,
         {$this->getTable()}.emp_dtnasc_notificacao_wpp,
         {$this->getTable()}.emp_dtnasc_notificacao_email,
         {$this->getTable()}.emp_nomemae,
         {$this->getTable()}.emp_nomepai,
         {$this->getTable()}.emp_idsexo,
         {$this->getTable()}.emp_idestadocivil,
         {$this->getTable()}.emp_idnaturalidade,
         {$this->getTable()}.emp_idnaturezajuridica,
         {$this->getTable()}.emp_googletag,
         {$this->getTable()}.emp_descricao,
         {$this->getTable()}.emp_logo,
         {$this->getTable()}.emp_planodefundo,
         {$this->getTable()}.emp_sidebar,
         {$this->getTable()}.emp_home,
         {$this->getTable()}.emp_paletacores,
         {$this->getTable()}.emp_tema,
         {$this->getTable()}.emp_dtcad,
         {$this->getTable()}.emp_dtalter
       FROM {$this->getTable()}
       {$where}
       ORDER BY {$camposOrdem[$campoOrdem]} {$ordem},
         {$this->getTable()}.emp_id ASC
       {$limitSql}",
      $params,
      true,
      "Empresas encontradas com sucesso||Erro ao pesquisar empresas"
    );

    if ($response->status && !empty($response->data)) {
      foreach ($response->data as $empresa) {
        $empresa->emp_cnpjcpf_mascara = Helpers::adicionaMascaraCnpjCPf($empresa->emp_cnpjcpf ?? '');
      }
    }

    return $response;
  }

  public function getEmpresaById(int $empId, bool $getContexto = false): object
  {
    $escopoUsuario = $this->usuarioLogado->escopo_maximo ?? 'Empresa';

    if ($empId <= 0) {
      return (new SimpleResponse("Erro", "Empresa não informada"))
        ->erro()
        ->setArrayParams([
          'data' => [],
          'total' => 0
        ])
        ->noDestroy();
    }

    if (empty($this->contexto->organizacao->org_id)) {
      return (new SimpleResponse("Erro", "Contexto de organização não identificado"))
        ->erro()
        ->setArrayParams([
          'data' => [],
          'total' => 0
        ])
        ->noDestroy();
    }

    $where = "
      {$this->getTable()}.emp_id = :emp_id
      AND {$this->getTable()}.emp_idorganizacao = :emp_idorganizacao
    ";

    $params = [
      'emp_id' => $empId,
      'emp_idorganizacao' => (int) $this->contexto->organizacao->org_id
    ];

    if ($escopoUsuario === 'Empresa') {
      if (empty($this->contexto->empresa->emp_id)) {
        return (new SimpleResponse("Erro", "Contexto de empresa não identificado"))
          ->erro()
          ->setArrayParams([
            'data' => [],
            'total' => 0
          ])
          ->noDestroy();
      }

      $where .= "
        AND (
          {$this->getTable()}.emp_id = :emp_idcontexto
          OR {$this->getTable()}.emp_idempresa_pai = :emp_idempresa_pai_contexto
        )
      ";

      $params['emp_idcontexto'] = (int) $this->contexto->empresa->emp_id;
      $params['emp_idempresa_pai_contexto'] = (int) $this->contexto->empresa->emp_id;
    }

    $getEmpresa = $this->select(
      $where,
      "{$this->getTable()}.*",
      "",
      $params,
      "Empresa encontrada com sucesso||Erro ao buscar empresa"
    );

    if ( $getEmpresa->status && $getEmpresa->total>0 ) {
      if ( $getContexto ) {
        $organizacaoTable = DB_PREFIX . '_organizacao';

        foreach ($getEmpresa->data as $empresa) {
          if( !is_null($empresa->emp_parametro) ) $empresa->emp_parametro = json_decode($empresa->emp_parametro);
          if( !is_null($empresa->emp_cadastrobasico_preferencia) ) $empresa->emp_cadastrobasico_preferencia = json_decode($empresa->emp_cadastrobasico_preferencia);
          $getOrganizacao = $this->rawQuery(
            "SELECT *
             FROM {$organizacaoTable}
             WHERE org_id = :org_id
             LIMIT 1",
            [
              'org_id' => (int) $empresa->emp_idorganizacao
            ],
            true,
            "Organização encontrada com sucesso||Erro ao buscar organização da empresa"
          );

          $empresa->organizacao = $getOrganizacao->status && $getOrganizacao->total > 0
            ? $getOrganizacao->data[0]
            : null;

          $empresa->matriz = null;

          if ($empresa->emp_tipo === 'Filial' && !empty($empresa->emp_idempresa_pai)) {
            $getMatriz = $this->rawQuery(
              "SELECT *
               FROM {$this->getTable()}
               WHERE emp_id = :emp_id
               AND emp_idorganizacao = :emp_idorganizacao
               LIMIT 1",
              [
                'emp_id' => (int) $empresa->emp_idempresa_pai,
                'emp_idorganizacao' => (int) $empresa->emp_idorganizacao
              ],
              true,
              "Matriz encontrada com sucesso||Erro ao buscar matriz da empresa"
            );

            $empresa->matriz = $getMatriz->status && $getMatriz->total > 0
              ? $getMatriz->data[0]
              : null;
          }
        }
      } else {
        foreach ($getEmpresa->data as $empresa) {
          if( !is_null($empresa->emp_parametro) ) $empresa->emp_parametro = json_decode($empresa->emp_parametro);
          if( !is_null($empresa->emp_cadastrobasico_preferencia) ) $empresa->emp_cadastrobasico_preferencia = json_decode($empresa->emp_cadastrobasico_preferencia);
        }
      }
    }

    return $getEmpresa;
  }

  public function salvarEmpresa(array $data): object
  {
    $location = trim((string) ($data['location'] ?? ''));
    unset($data['location']);

    $empId = (int) ($data['emp_id'] ?? 0);

    $camposPermitidos = [
      'emp_id',
      'emp_chave',
      'emp_idorganizacao',
      'emp_idempresa_pai',
      'emp_tipo',
      'emp_status',
      'emp_statusclass',
      'emp_status_motivo',
      'emp_cnpjcpf',
      'emp_razaosocial',
      'emp_nomefantasia',
      'emp_observacao',
      'emp_im',
      'emp_ie',
      'emp_rg',
      'emp_dtnasc',
      'emp_dtnasc_notificacao_wpp',
      'emp_dtnasc_notificacao_email',
      'emp_nomemae',
      'emp_nomepai',
      'emp_idsexo',
      'emp_idestadocivil',
      'emp_idnaturalidade',
      'emp_idnaturezajuridica',
      'emp_googletag',
      'emp_descricao',
      'emp_logo',
      'emp_planodefundo',
      'emp_sidebar',
      'emp_home',
      'emp_paletacores',
      'emp_tema',
      'emp_urlamigavel',
      'emp_webapp_status',
      'emp_webapp_conteudo',
      'emp_parametro',
      'emp_cadastrobasico_preferencia',
      'emp_log'
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

    if ($empId > 0) {
      $getEmpresa = $this->getEmpresaById($empId);

      if (!$getEmpresa->status || $getEmpresa->total != 1) {
        return (new SimpleResponse("Atenção", "Empresa não encontrada."))
          ->warning()
          ->noDestroy();
      }

      $empresaAtual = $getEmpresa->data[0];

      if (array_key_exists('emp_cnpjcpf', $data)) {
        $data['emp_cnpjcpf'] = Helpers::somenteNumeros($data['emp_cnpjcpf']);

        if ($data['emp_cnpjcpf'] === '') {
          return (new SimpleResponse("Atenção", "Informe o documento da empresa."))
            ->warning()
            ->noDestroy();
        }

        if (!in_array(strlen($data['emp_cnpjcpf']), [11, 14], true)) {
          return (new SimpleResponse("Atenção", "Informe um CPF ou CNPJ válido."))
            ->warning()
            ->noDestroy();
        }
      }

      if (array_key_exists('emp_razaosocial', $data) && trim((string) $data['emp_razaosocial']) === '') {
        return (new SimpleResponse("Atenção", "Informe a razão social ou nome da empresa."))
          ->warning()
          ->noDestroy();
      }

      $validarUnicidade = $this->validarUnicidadeEmpresa($data, (int) $this->contexto->organizacao->org_id, $empId);

      if (!$validarUnicidade->status) {
        return $validarUnicidade;
      }

      if (isset($data['emp_webapp_status']) && !in_array($data['emp_webapp_status'], ['Offline', 'Publica', 'Privada'], true)) {
        return (new SimpleResponse('Atenção', 'Selecione uma disponibilidade válida para a página da empresa.'))
          ->warning()
          ->setArrayParams(['campo' => 'emp_webapp_status'])
          ->noDestroy();
      }

      if (array_key_exists('emp_urlamigavel', $data)) {
        $urlAmigavel = strtolower(trim((string) ($data['emp_urlamigavel'] ?? '')));
        $statusWebapp = $data['emp_webapp_status'] ?? ($empresaAtual->emp_webapp_status ?? 'Offline');

        if ($urlAmigavel !== '' && (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $urlAmigavel) || mb_strlen($urlAmigavel) > 50)) {
          return (new SimpleResponse('Atenção', 'A URL amigável deve conter até 50 caracteres, usando apenas letras minúsculas, números e hífens.'))
            ->warning()
            ->setArrayParams(['campo' => 'emp_urlamigavel'])
            ->noDestroy();
        }

        if (in_array($statusWebapp, ['Publica', 'Privada'], true) && $urlAmigavel === '') {
          return (new SimpleResponse('Atenção', 'Informe a URL amigável para publicar ou restringir a página da empresa.'))
            ->warning()
            ->setArrayParams(['campo' => 'emp_urlamigavel'])
            ->noDestroy();
        }

        if ($urlAmigavel !== '') {
          $validarUrl = $this->rawQuery(
            "SELECT emp_id FROM {$this->getTable()} WHERE emp_urlamigavel = :emp_urlamigavel AND emp_id <> :emp_id LIMIT 1",
            ['emp_urlamigavel' => $urlAmigavel, 'emp_id' => $empId],
            true,
            'URL amigável validada com sucesso||Erro ao validar URL amigável'
          );

          if (!$validarUrl->status) {
            return $validarUrl;
          }

          if ($validarUrl->total > 0) {
            return (new SimpleResponse('Atenção', 'Esta URL amigável já está sendo utilizada por outra empresa.'))
              ->warning()
              ->setArrayParams(['campo' => 'emp_urlamigavel'])
              ->noDestroy();
          }
        }

        $data['emp_urlamigavel'] = $urlAmigavel !== '' ? $urlAmigavel : null;
      }

      if (!empty($data['emp_logo']) && preg_match('/^data:[a-zA-Z0-9\.\+\-]+\/[a-zA-Z0-9\.\+\-]+;base64,/', $data['emp_logo'])) {
        $upload = new SimpleUpload('empresa', $empId, $empId, (int) $empresaAtual->emp_idorganizacao);
        $salvarLogo = $upload->salvarBase64($data['emp_logo'], 'logo', 'logo');

        if (!$salvarLogo->status) {
          return $salvarLogo;
        }

        if (!empty($empresaAtual->emp_logo) && strpos($empresaAtual->emp_logo, 'storage/global') !== 0) {
          $upload->moverParaLixeira($empresaAtual->emp_logo);
        }

        $data['emp_logo'] = $salvarLogo->arq_caminho;
      }

      if (!empty($data['emp_planodefundo']) && preg_match('/^data:[a-zA-Z0-9\.\+\-]+\/[a-zA-Z0-9\.\+\-]+;base64,/', $data['emp_planodefundo'])) {
        $upload = new SimpleUpload('empresa', $empId, $empId, (int) $empresaAtual->emp_idorganizacao);
        $salvarPlanoDeFundo = $upload->salvarBase64($data['emp_planodefundo'], 'planodefundo', 'planodefundo');

        if (!$salvarPlanoDeFundo->status) {
          return $salvarPlanoDeFundo;
        }

        if (!empty($empresaAtual->emp_planodefundo) && strpos($empresaAtual->emp_planodefundo, 'storage/global') !== 0) {
          $upload->moverParaLixeira($empresaAtual->emp_planodefundo);
        }

        $data['emp_planodefundo'] = $salvarPlanoDeFundo->arq_caminho;
      }

      $data = $this->normalizarDadosEmpresa($data);

      unset(
        $data['emp_id'],
        $data['emp_chave'],
        $data['emp_idorganizacao'],
        $data['emp_log']
      );

      foreach ($data as $key => $value) {
        if (is_string($value)) {
          $data[$key] = trim($value);
        }

        if ($data[$key] === '') {
          $data[$key] = null;
        }
      }

      $statusAtual = trim((string) ($empresaAtual->emp_status ?? ''));
      $statusNovo = array_key_exists('emp_status', $data)
        ? trim((string) ($data['emp_status'] ?? ''))
        : $statusAtual;

      $motivoStatusInformado = trim((string) ($data['emp_status_motivo'] ?? ''));

      if ($statusAtual !== $statusNovo) {
        if ($statusNovo === 'Inativo') {
          if (mb_strlen($motivoStatusInformado) < 10) {
            return (new SimpleResponse(
              'Motivo obrigatÃ³rio',
              'Informe um motivo com pelo menos 10 caracteres para inativar a igreja.'
            ))
              ->warning()
              ->noDestroy();
          }

          $data['emp_status_motivo'] = json_encode([
            'motivo' => $motivoStatusInformado,
            'data' => date('d/m/Y H:i:s'),
            'responsavel' => $this->usuarioLogado->usu_razaosocial ?? 'Sistema',
            'usu_id' => (int) ($this->usuarioLogado->usu_id ?? 0)
          ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif ($statusNovo === 'Ativo') {
          if ($motivoStatusInformado !== '') {
            $data['emp_status_motivo'] = json_encode([
              'motivo' => $motivoStatusInformado,
              'data' => date('d/m/Y H:i:s'),
              'responsavel' => $this->usuarioLogado->usu_razaosocial ?? 'Sistema',
              'usu_id' => (int) ($this->usuarioLogado->usu_id ?? 0)
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
          } else {
            unset($data['emp_status_motivo']);
          }
        }
      } else {
        unset($data['emp_status_motivo']);
      }

      if ($statusAtual !== 'Ativo' && $statusNovo === 'Ativo') {
        $limiteReativacao = (new LimitePlanoService($this->contexto))->verificar('empresa');

        if ($limiteReativacao->status && !$limiteReativacao->permitido) {
          return (new SimpleResponse(
            'Limite do plano',
            'Não é possível reativar esta igreja porque o limite de igrejas ativas do plano foi atingido.'
          ))
            ->warning()
            ->setArrayParams(['data' => $limiteReativacao])
            ->noDestroy();
        }
      }

      if (empty($data)) {
        return (new SimpleResponse("Atenção", "Nenhuma informação foi enviada para atualização."))
          ->warning()
          ->noDestroy();
      }

      $response = $this->update(
        $data,
        [
          'emp_id' => $empId,
          'emp_idorganizacao' => (int) $this->contexto->organizacao->org_id
        ],
        "Empresa atualizada com sucesso||Erro ao atualizar empresa"
      );

      if ($response->status) {
        $response->location = Helpers::url("empresa/form/{$empId}{$location}");
      }

      return $response;
    }

    if (empty($this->contexto->organizacao->org_id)) {
      return (new SimpleResponse("Erro", "Contexto de organização não identificado."))
        ->erro()
        ->noDestroy();
    }

    $limiteEmpresa = (new LimitePlanoService($this->contexto))->verificar('empresa');

    if ($limiteEmpresa->status && !$limiteEmpresa->permitido) {
      return (new SimpleResponse('Limite do plano', $limiteEmpresa->text))
        ->warning()
        ->setArrayParams(['data' => $limiteEmpresa])
        ->noDestroy();
    }

    $documento = Helpers::somenteNumeros($data['emp_cnpjcpf'] ?? '');

    if ($documento === '') {
      return (new SimpleResponse("Atenção", "Informe o documento da empresa."))
        ->warning()
        ->noDestroy();
    }

    if (!in_array(strlen($documento), [11, 14], true)) {
      return (new SimpleResponse("Atenção", "Informe um CPF ou CNPJ válido."))
        ->warning()
        ->noDestroy();
    }

    if (empty(trim((string) ($data['emp_razaosocial'] ?? '')))) {
      return (new SimpleResponse("Atenção", "Informe a razão social ou nome da empresa."))
        ->warning()
        ->noDestroy();
    }

    $data['emp_idorganizacao'] = (int) $this->contexto->organizacao->org_id;
    $data['emp_chave'] = 'EMP_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
    $data['emp_cnpjcpf'] = $documento;

    $validarUnicidade = $this->validarUnicidadeEmpresa($data, (int) $this->contexto->organizacao->org_id);

    if (!$validarUnicidade->status) {
      return $validarUnicidade;
    }

    $data = $this->normalizarDadosEmpresa($data);

    foreach ($data as $key => $value) {
      if (!in_array($key, $camposPermitidos, true)) {
        unset($data[$key]);
        continue;
      }

      if (is_string($value)) {
        $data[$key] = trim($value);
      }

      if ($data[$key] === '' || is_null($data[$key])) {
        unset($data[$key]);
      }
    }

    $statusEmpresa = trim((string) ($data['emp_status'] ?? ''));
    $data['emp_status'] = in_array($statusEmpresa, ['Ativo', 'Inativo'], true) ? $statusEmpresa : 'Ativo';
    $data['emp_statusclass'] = $data['emp_status'] === 'Ativo' ? 'success' : 'danger';

    if ($data['emp_status'] === 'Inativo') {
      $motivoStatusInformado = trim((string) ($data['emp_status_motivo'] ?? ''));

      if (mb_strlen($motivoStatusInformado) < 10) {
        return (new SimpleResponse(
          'Motivo obrigatÃ³rio',
          'Informe um motivo com pelo menos 10 caracteres para cadastrar a igreja como inativa.'
        ))
          ->warning()
          ->noDestroy();
      }

      $data['emp_status_motivo'] = json_encode([
        'motivo' => $motivoStatusInformado,
        'data' => date('d/m/Y H:i:s'),
        'responsavel' => $this->usuarioLogado->usu_razaosocial ?? 'Sistema',
        'usu_id' => (int) ($this->usuarioLogado->usu_id ?? 0)
      ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    unset($data['emp_id']);

    $response = $this->insert(
      $data,
      "Empresa cadastrada com sucesso||Erro ao cadastrar empresa"
    );

    if ($response->status) {
      $response->location = Helpers::url("empresa/form/{$response->lastInsertId}{$location}");
    }

    return $response;
  }

  public function pesquisarRapida(string $conteudo): object
  {
    $conteudo = trim(rawurldecode($conteudo));

    if ($conteudo === '') {
      return (new SimpleResponse("Atenção", "Digite uma informação para pesquisar"))
        ->warning()
        ->setArrayParams([
          'location' => Helpers::url('empresa/list')
        ])
        ->noDestroy();
    }

    $escopoUsuario = $this->usuarioLogado->escopo_maximo ?? 'Empresa';

    if (empty($this->contexto->organizacao->org_id)) {
      return (new SimpleResponse("Erro", "Contexto de organização não identificado"))
        ->erro()
        ->setArrayParams([
          'location' => Helpers::url('empresa/list')
        ])
        ->noDestroy();
    }

    $documento = Helpers::somenteNumeros($conteudo);

    $params = [
      'emp_idorganizacao' => (int) $this->contexto->organizacao->org_id
    ];

    $where = "
      WHERE {$this->getTable()}.emp_idorganizacao = :emp_idorganizacao
    ";

    if ($escopoUsuario === 'Empresa') {
      if (empty($this->contexto->empresa->emp_id)) {
        return (new SimpleResponse("Erro", "Contexto de empresa não identificado"))
          ->erro()
          ->setArrayParams([
            'location' => Helpers::url('empresa/list')
          ])
          ->noDestroy();
      }

      $where .= "
        AND {$this->getTable()}.emp_id = :emp_id
      ";

      $params['emp_id'] = (int) $this->contexto->empresa->emp_id;
    }

    $wherePesquisa = [];

    if (ctype_digit($conteudo)) {
      $wherePesquisa[] = "{$this->getTable()}.emp_id = :conteudo_id";
      $params['conteudo_id'] = (int) $conteudo;
    }

    if ($documento !== '') {
      $wherePesquisa[] = "{$this->getTable()}.emp_cnpjcpf LIKE :conteudo_documento";
      $params['conteudo_documento'] = "%{$documento}%";
    }

    $camposTexto = [
      "{$this->getTable()}.emp_chave",
      "{$this->getTable()}.emp_razaosocial",
      "{$this->getTable()}.emp_nomefantasia",
      "{$this->getTable()}.emp_observacao",
      "{$this->getTable()}.emp_descricao"
    ];

    foreach ($camposTexto as $index => $campo) {
      $param = "conteudo_texto_{$index}";
      $wherePesquisa[] = "{$campo} LIKE :{$param}";
      $params[$param] = "%{$conteudo}%";
    }

    $where .= "
      AND (
        " . implode("
        OR ", $wherePesquisa) . "
      )
    ";

    $orderByPrioridade = "
      CASE
    ";

    if (ctype_digit($conteudo)) {
      $orderByPrioridade .= "
        WHEN {$this->getTable()}.emp_id = :ordem_id THEN 1
      ";

      $params['ordem_id'] = (int) $conteudo;
    }

    if ($documento !== '') {
      $orderByPrioridade .= "
        WHEN {$this->getTable()}.emp_cnpjcpf = :ordem_documento THEN 2
      ";

      $params['ordem_documento'] = $documento;
    }

    $orderByPrioridade .= "
        WHEN {$this->getTable()}.emp_chave = :ordem_chave THEN 3
        ELSE 9
      END
    ";

    $params['ordem_chave'] = $conteudo;

    $getEmpresa = $this->rawQuery(
      "SELECT
         {$this->getTable()}.emp_id,
         {$this->getTable()}.emp_chave,
         {$this->getTable()}.emp_tipo,
         {$this->getTable()}.emp_status,
         {$this->getTable()}.emp_statusclass,
         {$this->getTable()}.emp_cnpjcpf,
         {$this->getTable()}.emp_razaosocial,
         {$this->getTable()}.emp_nomefantasia
       FROM {$this->getTable()}
       {$where}
       ORDER BY
         {$orderByPrioridade},
         {$this->getTable()}.emp_razaosocial ASC,
         {$this->getTable()}.emp_id ASC
       LIMIT 2",
      $params,
      true,
      "Empresa encontrada com sucesso||Erro ao pesquisar empresa"
    );

    if (!$getEmpresa->status) {
      return (new SimpleResponse($getEmpresa))
        ->erro("Erro ao realizar pesquisa rápida de empresa")
        ->setArrayParams([
          'location' => Helpers::url('empresa/list')
        ])
        ->noDestroy();
    }

    if ($getEmpresa->total <= 0) {
      return (new SimpleResponse("Atenção", "Nenhuma empresa encontrada para a pesquisa informada"))
        ->warning()
        ->setArrayParams([
          'location' => Helpers::url('empresa/list')
        ])
        ->noDestroy();
    }

    if ($getEmpresa->total > 1) {
      return (new SimpleResponse("Atenção", "Mais de uma empresa foi encontrada. Use a pesquisa completa para refinar o resultado"))
        ->warning()
        ->setArrayParams([
          'data' => $getEmpresa->data,
          'total' => $getEmpresa->total,
          'location' => Helpers::url('empresa/list')
        ])
        ->noDestroy();
    }

    $empresa = $getEmpresa->data[0];

    return (new SimpleResponse("Sucesso", "Empresa encontrada"))
      ->sucesso()
      ->setArrayParams([
        'data' => $empresa,
        'total' => 1,
        'location' => Helpers::url("empresa/form/{$empresa->emp_id}")
      ])
      ->noDestroy();
  }

  private function normalizarDadosEmpresa(array $data): array
  {
    if (isset($data['emp_cnpjcpf'])) {
      $data['emp_cnpjcpf'] = Helpers::somenteNumeros($data['emp_cnpjcpf']);
    }

    if (isset($data['emp_tipo'])) {
      $data['emp_tipo'] = in_array(($data['emp_tipo'] ?? 'Matriz'), ['Matriz', 'Filial'], true) ? $data['emp_tipo'] : 'Matriz';
    }

    if (isset($data['emp_status'])) {
      $data['emp_status'] = in_array(($data['emp_status'] ?? 'Ativo'), ['Ativo', 'Inativo'], true) ? $data['emp_status'] : 'Ativo';
      $data['emp_statusclass'] = $data['emp_status'] === 'Ativo' ? 'success' : 'danger';
    }

    if (isset($data['emp_tema'])) {
      $data['emp_tema'] = in_array(($data['emp_tema'] ?? 'light'), ['light', 'dark'], true) ? $data['emp_tema'] : 'light';
    }

    if (isset($data['emp_sidebar'])) {
      $data['emp_sidebar'] = (int) $data['emp_sidebar'];
    }

    if (isset($data['emp_paletacores']) && is_array($data['emp_paletacores'])) {
      $data['emp_paletacores'] = json_encode($data['emp_paletacores'], JSON_UNESCAPED_UNICODE);
    }

    if (isset($data['emp_parametro']) && is_array($data['emp_parametro'])) {
      $data['emp_parametro'] = json_encode($data['emp_parametro'], JSON_UNESCAPED_UNICODE);
    }

    if (isset($data['emp_cadastrobasico_preferencia']) && is_array($data['emp_cadastrobasico_preferencia'])) {
      $data['emp_cadastrobasico_preferencia'] = json_encode($data['emp_cadastrobasico_preferencia'], JSON_UNESCAPED_UNICODE);
    }

    if (isset($data['emp_cnpjcpf'])) {
      if (strlen($data['emp_cnpjcpf']) === 11) {
        $data['emp_im'] = null;
        $data['emp_ie'] = null;
        $data['emp_idnaturezajuridica'] = null;
      }

      if (strlen($data['emp_cnpjcpf']) === 14) {
        $data['emp_rg'] = null;
        $data['emp_nomemae'] = null;
        $data['emp_nomepai'] = null;
        $data['emp_idsexo'] = null;
        $data['emp_idestadocivil'] = null;
        $data['emp_idnaturalidade'] = null;
      }
    }

    return $data;
  }

  private function validarUnicidadeEmpresa(array $data, int $orgId, int $empId = 0): object
  {
    $campos = [
      'emp_cnpjcpf' => 'Já existe uma empresa com este documento nesta organização.'
    ];

    foreach ($campos as $campo => $mensagem) {
      if (!array_key_exists($campo, $data) || is_null($data[$campo]) || $data[$campo] === '') {
        continue;
      }

      $params = [
        'emp_idorganizacao' => $orgId,
        $campo => $data[$campo]
      ];

      $whereIgnorar = '';

      if ($empId > 0) {
        $whereIgnorar = "AND emp_id != :emp_id";
        $params['emp_id'] = $empId;
      }

      $getEmpresa = $this->rawQuery(
        "SELECT emp_id
         FROM {$this->getTable()}
         WHERE emp_idorganizacao = :emp_idorganizacao
         AND {$campo} = :{$campo}
         {$whereIgnorar}
         LIMIT 1",
        $params,
        true,
        "Empresa validada com sucesso||Erro ao validar empresa"
      );

      if ($getEmpresa->status && $getEmpresa->total > 0) {
        return (new SimpleResponse("Atenção", $mensagem))
          ->warning()
          ->setArrayParams([
            'campo' => $campo
          ])
          ->noDestroy();
      }
    }

    return (new SimpleResponse("Validação", "Empresa validada com sucesso"))
      ->sucesso()
      ->noDestroy();
  }
}
