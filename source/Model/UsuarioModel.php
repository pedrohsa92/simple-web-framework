<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;
use Source\Core\SimpleUpload;
use Source\Service\LimitePlanoService;

class UsuarioModel extends SimpleModel
{
  protected string $tabela = 'usuario';
  protected string $prefixo = 'usu';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getUsuariosLogin(string $login = '', ?int $empId = null, ?int $usuId = null): object
  {
    $organizacaoTable = DB_PREFIX . '_organizacao';
    $empresaTable = DB_PREFIX . '_empresa';
    $usuarioOrganizacaoTable = DB_PREFIX . '_usuario_organizacao';
    $usuarioEmpresaTable = DB_PREFIX . '_usuario_empresa';
    $usuarioNivelTable = DB_PREFIX . '_usuario_nivelacesso';
    $nivelTable = DB_PREFIX . '_nivelacesso';

    $columns = "
      DISTINCT {$this->getTable()}.*,
      contexto.contexto_org_id,
      contexto.contexto_emp_id,

      {$organizacaoTable}.org_id,
      {$organizacaoTable}.org_status,
      {$organizacaoTable}.org_statusclass,
      {$organizacaoTable}.org_idusuarioresponsavel,
      {$organizacaoTable}.org_cnpjcpf,
      {$organizacaoTable}.org_razaosocial,
      {$organizacaoTable}.org_nomefantasia,
      {$organizacaoTable}.org_ambiente,
      {$organizacaoTable}.org_url_dedicada,
      {$organizacaoTable}.org_banco_dedicado,
      {$organizacaoTable}.org_statusambiente,
      {$organizacaoTable}.org_logo,
      {$organizacaoTable}.org_favicon,
      {$organizacaoTable}.org_planodefundo,
      {$organizacaoTable}.org_cadastrobasico_preferencia,

      {$empresaTable}.emp_id,
      {$empresaTable}.emp_chave,
      {$empresaTable}.emp_idorganizacao,
      {$empresaTable}.emp_idempresa_pai,
      {$empresaTable}.emp_tipo,
      {$empresaTable}.emp_status,
      {$empresaTable}.emp_statusclass,
      {$empresaTable}.emp_cnpjcpf,
      {$empresaTable}.emp_razaosocial,
      {$empresaTable}.emp_nomefantasia,
      {$empresaTable}.emp_observacao,
      {$empresaTable}.emp_logo,
      {$empresaTable}.emp_planodefundo,
      {$empresaTable}.emp_sidebar,
      {$empresaTable}.emp_home,
      {$empresaTable}.emp_paletacores,
      {$empresaTable}.emp_tema,
      {$empresaTable}.emp_parametro,
      {$empresaTable}.emp_cadastrobasico_preferencia
    ";

    $join = "
      INNER JOIN (
        SELECT
          uem.uem_idusuario AS contexto_usu_id,
          emp.emp_id AS contexto_emp_id,
          emp.emp_idorganizacao AS contexto_org_id
        FROM {$usuarioEmpresaTable} uem
        INNER JOIN {$empresaTable} emp ON emp.emp_id = uem.uem_idempresa
        WHERE uem.uem_status = 'Ativo'
          AND emp.emp_status = 'Ativo'

        UNION

        SELECT
          uor.uor_idusuario AS contexto_usu_id,
          emp.emp_id AS contexto_emp_id,
          emp.emp_idorganizacao AS contexto_org_id
        FROM {$usuarioOrganizacaoTable} uor
        INNER JOIN {$empresaTable} emp ON emp.emp_idorganizacao = uor.uor_idorganizacao
        WHERE uor.uor_status = 'Ativo'
          AND emp.emp_status = 'Ativo'
          AND EXISTS (
            SELECT 1
            FROM {$usuarioNivelTable} una
            INNER JOIN {$nivelTable} nva ON nva.nva_id = una.una_idnivelacesso
            WHERE una.una_idusuario = uor.uor_idusuario
              AND una.una_idorganizacao = uor.uor_idorganizacao
              AND una.una_idempresa IS NULL
              AND una.una_status = 'Ativo'
              AND nva.nva_escopo = 'Organizacao'
              AND nva.nva_status = 'Ativo'
          )

        UNION

        SELECT
          usu.usu_id AS contexto_usu_id,
          usu.usu_idempresa AS contexto_emp_id,
          usu.usu_idorganizacao AS contexto_org_id
        FROM {$this->getTable()} usu
        WHERE usu.usu_idorganizacao IS NOT NULL
          AND usu.usu_idempresa IS NOT NULL
      ) contexto ON contexto.contexto_usu_id = {$this->getTable()}.usu_id

      INNER JOIN {$organizacaoTable}
        ON {$organizacaoTable}.org_id = contexto.contexto_org_id

      INNER JOIN {$empresaTable}
        ON {$empresaTable}.emp_id = contexto.contexto_emp_id
    ";

    $where = "
      {$this->getTable()}.usu_status = 'Ativo'
      AND {$organizacaoTable}.org_status = 'Ativo'
      AND {$empresaTable}.emp_status = 'Ativo'
    ";

    $params = [];

    if (!is_null($usuId) && $usuId > 0) {
      $where .= "
        AND {$this->getTable()}.usu_id = :usu_id
      ";

      $params['usu_id'] = $usuId;
    } else {
      $where .= "
        AND (
          {$this->getTable()}.usu_email = :login_email
          OR {$this->getTable()}.usu_username = :login_username
        )
      ";

      $params['login_email'] = $login;
      $params['login_username'] = $login;
    }

    if (!is_null($empId) && $empId > 0) {
      $where .= "
        AND contexto.contexto_emp_id = :usu_idempresa
      ";

      $params['usu_idempresa'] = $empId;
    } else {
      // No login, o usuário entra diretamente no último contexto principal.
      // A seleção de contexto continua disponível somente após a autenticação.
      $where .= "
        AND contexto.contexto_org_id = COALESCE(
          {$this->getTable()}.usu_idorganizacaoprincipal,
          {$this->getTable()}.usu_idorganizacao,
          (
            SELECT emp_principal.emp_idorganizacao
            FROM {$usuarioEmpresaTable} uem_principal
            INNER JOIN {$empresaTable} emp_principal
              ON emp_principal.emp_id = uem_principal.uem_idempresa
            WHERE uem_principal.uem_idusuario = {$this->getTable()}.usu_id
              AND uem_principal.uem_status = 'Ativo'
            ORDER BY uem_principal.uem_principal DESC, uem_principal.uem_id ASC
            LIMIT 1
          )
        )
        AND contexto.contexto_emp_id = COALESCE(
          {$this->getTable()}.usu_idempresaprincipal,
          {$this->getTable()}.usu_idempresa,
          (
            SELECT uem_principal.uem_idempresa
            FROM {$usuarioEmpresaTable} uem_principal
            WHERE uem_principal.uem_idusuario = {$this->getTable()}.usu_id
              AND uem_principal.uem_status = 'Ativo'
            ORDER BY uem_principal.uem_principal DESC, uem_principal.uem_id ASC
            LIMIT 1
          )
        )
      ";
    }

    $where .= "
      ORDER BY
        {$organizacaoTable}.org_nomefantasia ASC,
        {$organizacaoTable}.org_razaosocial ASC,
        {$empresaTable}.emp_nomefantasia ASC,
        {$empresaTable}.emp_razaosocial ASC
    ";

    $response = $this->rawQuery(
      "SELECT {$columns}
       FROM {$this->getTable()}
       {$join}
       WHERE {$where}",
      $params,
      true,
      "Usuário encontrado com sucesso||Erro ao buscar usuário"
    );
    if ($response->status && !empty($response->data)) {
      foreach ($response->data as $usuario) {
        $contextoOrgId = (int) ($usuario->contexto_org_id ?? 0);
        $contextoEmpId = (int) ($usuario->contexto_emp_id ?? 0);

        // Campos legados continuam refletindo o contexto atualmente selecionado.
        $usuario->usu_idorganizacao = $contextoOrgId;
        $usuario->usu_idempresa = $contextoEmpId;

        $niveis = $this->getNiveisAcessoContexto(
          (int) $usuario->usu_id,
          $contextoOrgId,
          $contextoEmpId
        );

        if ($niveis->status && $niveis->total > 0) {
          $usuario->usu_niveisacesso = json_encode(
            $niveis->data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
          );
        }

        unset($usuario->contexto_org_id, $usuario->contexto_emp_id);
      }
    }

    return $response;
  }

  /**
   * Recupera os níveis efetivos do usuário no contexto selecionado.
   * Níveis de projeto valem para todos os contextos; níveis de organização
   * valem para todas as igrejas da organização; níveis de empresa valem
   * somente para a igreja informada.
   */
  private function getNiveisAcessoContexto(int $usuId, int $orgId, int $empId): object
  {
    $usuarioNivelTable = DB_PREFIX . '_usuario_nivelacesso';
    $nivelTable = DB_PREFIX . '_nivelacesso';

    return $this->rawQuery(
      "SELECT DISTINCT
         nva.nva_id,
         nva.nva_nome
       FROM {$usuarioNivelTable} una
       INNER JOIN {$nivelTable} nva ON nva.nva_id = una.una_idnivelacesso
       WHERE una.una_idusuario = :una_idusuario
       AND una.una_status = 'Ativo'
       AND nva.nva_status = 'Ativo'
       AND (
         (una.una_idorganizacao IS NULL AND una.una_idempresa IS NULL)
         OR (una.una_idorganizacao = :una_idorganizacao AND una.una_idempresa IS NULL)
         OR (una.una_idorganizacao = :una_idorganizacao_empresa AND una.una_idempresa = :una_idempresa)
       )
       ORDER BY nva.nva_escopo DESC, nva.nva_nome ASC",
      [
        'una_idusuario' => $usuId,
        'una_idorganizacao' => $orgId,
        'una_idorganizacao_empresa' => $orgId,
        'una_idempresa' => $empId
      ],
      true,
      "NÃ­veis de acesso encontrados com sucesso||Erro ao buscar nÃ­veis de acesso"
    );
  }

  public function prepararUsuarioSessao(object $usuario): object
  {
    $organizacao = (object) [
      'org_id' => $usuario->org_id ?? null,
      'org_status' => $usuario->org_status ?? null,
      'org_statusclass' => $usuario->org_statusclass ?? null,
      'org_idusuarioresponsavel' => $usuario->org_idusuarioresponsavel ?? null,
      'org_cnpjcpf' => $usuario->org_cnpjcpf ?? null,
      'org_razaosocial' => $usuario->org_razaosocial ?? null,
      'org_nomefantasia' => $usuario->org_nomefantasia ?? null,
      'org_ambiente' => $usuario->org_ambiente ?? null,
      'org_url_dedicada' => $usuario->org_url_dedicada ?? null,
      'org_banco_dedicado' => $usuario->org_banco_dedicado ?? null,
      'org_statusambiente' => $usuario->org_statusambiente ?? null,
      'org_logo' => $usuario->org_logo ?? null,
      'org_favicon' => $usuario->org_favicon ?? null,
      'org_planodefundo' => $usuario->org_planodefundo ?? null,
      'org_cadastrobasico_preferencia' => $usuario->org_cadastrobasico_preferencia ?? null
    ];

    $empresa = (object) [
      'emp_id' => $usuario->emp_id ?? null,
      'emp_chave' => $usuario->emp_chave ?? null,
      'emp_idorganizacao' => $usuario->emp_idorganizacao ?? null,
      'emp_idempresa_pai' => $usuario->emp_idempresa_pai ?? null,
      'emp_tipo' => $usuario->emp_tipo ?? null,
      'emp_status' => $usuario->emp_status ?? null,
      'emp_statusclass' => $usuario->emp_statusclass ?? null,
      'emp_cnpjcpf' => $usuario->emp_cnpjcpf ?? null,
      'emp_razaosocial' => $usuario->emp_razaosocial ?? null,
      'emp_nomefantasia' => $usuario->emp_nomefantasia ?? null,
      'emp_observacao' => $usuario->emp_observacao ?? null,
      'emp_logo' => $usuario->emp_logo ?? null,
      'emp_planodefundo' => $usuario->emp_planodefundo ?? null,
      'emp_sidebar' => $usuario->emp_sidebar ?? null,
      'emp_home' => $usuario->emp_home ?? null,
      'emp_paletacores' => json_decode($usuario->emp_paletacores ?? 'null'),
      'emp_tema' => $usuario->emp_tema ?? null,
      'emp_parametro' => json_decode($usuario->emp_parametro ?? 'null'),
      'emp_cadastrobasico_preferencia' => $usuario->emp_cadastrobasico_preferencia ?? null
    ];

    unset(
      $usuario->usu_senha,
      $usuario->usu_codigo,
      $usuario->usu_log,
      $usuario->usu_dtexpiracaosenha,
      $usuario->usu_http_user_agent,
      $usuario->usu_remote_addr,

      $usuario->org_id,
      $usuario->org_status,
      $usuario->org_statusclass,
      $usuario->org_cnpjcpf,
      $usuario->org_razaosocial,
      $usuario->org_nomefantasia,
      $usuario->org_ambiente,
      $usuario->org_url_dedicada,
      $usuario->org_banco_dedicado,
      $usuario->org_statusambiente,
      $usuario->org_logo,
      $usuario->org_favicon,
      $usuario->org_planodefundo,
      $usuario->org_cadastrobasico_preferencia,

      $usuario->emp_id,
      $usuario->emp_chave,
      $usuario->emp_idorganizacao,
      $usuario->emp_idempresa_pai,
      $usuario->emp_tipo,
      $usuario->emp_status,
      $usuario->emp_statusclass,
      $usuario->emp_cnpjcpf,
      $usuario->emp_razaosocial,
      $usuario->emp_nomefantasia,
      $usuario->emp_observacao,
      $usuario->emp_logo,
      $usuario->emp_planodefundo,
      $usuario->emp_sidebar,
      $usuario->emp_home,
      $usuario->emp_paletacores,
      $usuario->emp_tema,
      $usuario->emp_parametro,
      $usuario->emp_cadastrobasico_preferencia
    );

    $usuario->niveisacesso = json_decode($usuario->usu_niveisacesso ?? '[]');

    if (!is_array($usuario->niveisacesso)) {
      $usuario->niveisacesso = [];
    }

    $usuario->organizacao = $organizacao;
    $usuario->empresa = $empresa;
    $usuario->escopo_maximo = $this->getEscopoMaximoByNiveis($usuario->niveisacesso);
    $usuario->perfil_resumo = 'Usuário ' . $usuario->escopo_maximo;
    $usuario->permissoes = $this->getPermissoesByNiveis($usuario->niveisacesso);
    $usuario->bloqueado = $usuario->bloqueado ?? 0;

    return $usuario;
  }

  public function atualizarUsuarioLogado(): ?object
  {
    $usuarioLogado = $this->session->get('USUARIO');

    if (is_null($usuarioLogado) || empty($usuarioLogado->usu_id)) {
      return null;
    }

    $contexto = $this->session->get('CONTEXTO');
    $empId = (int) (
      $contexto->empresa->emp_id
      ?? $usuarioLogado->usu_idempresa
      ?? 0
    );

    $getUsuario = $this->getUsuariosLogin(
      '',
      $empId > 0 ? $empId : null,
      (int) $usuarioLogado->usu_id
    );

    if (!$getUsuario->status || $getUsuario->total != 1) {
      return null;
    }

    $usuario = $this->prepararUsuarioSessao($getUsuario->data[0]);
    $usuario->bloqueado = $usuarioLogado->bloqueado ?? 0;

    $this->session->set('USUARIO', $usuario);

    $ultimaAtualizacao = !empty($usuarioLogado->usu_dtonline)
      ? strtotime($usuarioLogado->usu_dtonline)
      : 0;

    if ($ultimaAtualizacao <= 0 || (time() - $ultimaAtualizacao) >= 300) {
      $this->rawQuery(
        "UPDATE {$this->getTable()}
         SET usu_http_user_agent = :usu_http_user_agent,
             usu_remote_addr = :usu_remote_addr,
             usu_online = 1,
             usu_dtonline = :usu_dtonline
         WHERE usu_id = :usu_id",
        [
          'usu_http_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
          'usu_remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
          'usu_dtonline' => date('Y-m-d H:i:s'),
          'usu_id' => (int) $usuario->usu_id
        ],
        false
      );

      $this->setUsuariosOffline();
    }

    return $usuario;
  }

  public function registrarLogin(int $usuId): object
  {
    return $this->rawQuery(
      "UPDATE {$this->getTable()}
       SET usu_http_user_agent = :usu_http_user_agent,
           usu_remote_addr = :usu_remote_addr,
           usu_online = 1,
           usu_dtonline = :usu_dtonline,
           usu_dtlogin = :usu_dtlogin
       WHERE usu_id = :usu_id",
      [
        'usu_http_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'usu_remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
        'usu_dtonline' => date('Y-m-d H:i:s'),
        'usu_dtlogin' => date('Y-m-d H:i:s'),
        'usu_id' => $usuId
      ],
      false,
      "Login registrado com sucesso||Erro ao registrar login"
    );
  }

  public function setUsuariosOffline(): void
  {
    $this->rawQuery(
      "UPDATE {$this->getTable()}
       SET usu_online = 0
       WHERE usu_dtonline IS NOT NULL
       AND usu_dtonline < DATE_SUB(NOW(), INTERVAL 10 MINUTE)",
      [],
      false
    );
  }

  public function getEscopoMaximoByNiveis(array $niveis): string
  {
    if (empty($niveis)) {
      return 'Empresa';
    }

    $niveisIds = [];

    foreach ($niveis as $nivel) {
      if (is_object($nivel)) {
        $niveisIds[] = (int) ($nivel->nva_id ?? 0);
        continue;
      }

      if (is_array($nivel)) {
        $niveisIds[] = (int) ($nivel['nva_id'] ?? 0);
        continue;
      }

      $niveisIds[] = (int) $nivel;
    }

    $niveisIds = array_values(array_unique(array_filter($niveisIds)));

    if (empty($niveisIds)) {
      return 'Empresa';
    }

    $placeholders = [];
    $params = [];

    foreach ($niveisIds as $index => $nvaId) {
      $param = "nva_id_{$index}";
      $placeholders[] = ":{$param}";
      $params[$param] = $nvaId;
    }

    $nivelModel = new SimpleModel('nivelacesso', 'nva');

    $getNiveis = $nivelModel->rawQuery(
      "SELECT nva_escopo
       FROM {$nivelModel->getTable()}
       WHERE nva_id IN (" . implode(',', $placeholders) . ")
       AND nva_status = 'Ativo'",
      $params
    );

    if (!$getNiveis->status || $getNiveis->total == 0) {
      return 'Empresa';
    }

    $prioridade = [
      'Empresa' => 1,
      'Organizacao' => 2,
      'Projeto' => 3
    ];

    $escopoMaximo = 'Empresa';
    $maiorPrioridade = 1;

    foreach ($getNiveis->data as $nivel) {
      $escopo = $nivel->nva_escopo ?? 'Empresa';
      $peso = $prioridade[$escopo] ?? 1;

      if ($peso > $maiorPrioridade) {
        $maiorPrioridade = $peso;
        $escopoMaximo = $escopo;
      }
    }

    return $escopoMaximo;
  }

  public function getPermissoesByNvaId(int $nvaId): array
  {
    $permissaoModel = new SimpleModel('permissao', 'per');

    $getPermissoes = $permissaoModel->rawQuery(
      "SELECT per_nome, per_nivel
       FROM {$permissaoModel->getTable()}
       WHERE per_idnivelacesso = :per_idnivelacesso
       AND per_status = 'Ativo'",
      [
        'per_idnivelacesso' => $nvaId
      ]
    );

    if (!$getPermissoes->status || $getPermissoes->total == 0) {
      return [];
    }

    $permissoes = [];

    foreach ($getPermissoes->data as $permissao) {
      $permissoes[$permissao->per_nome] = (int) $permissao->per_nivel;
    }

    return $permissoes;
  }

  public function getPermissoesByNiveis(array $niveis): object
  {
    $permissoes = [];

    foreach ($niveis as $nivel) {
      if (is_object($nivel)) {
        $nvaId = (int) ($nivel->nva_id ?? 0);
      } elseif (is_array($nivel)) {
        $nvaId = (int) ($nivel['nva_id'] ?? 0);
      } else {
        $nvaId = (int) $nivel;
      }

      if ($nvaId <= 0) {
        continue;
      }

      $permissoes = array_merge($permissoes, $this->getPermissoesByNvaId((int) $nvaId));
    }

    return (object) $permissoes;
  }

  public function bloquearSessaoUsuario(): ?object
  {
    $usuario = $this->session->get('USUARIO');

    if (is_null($usuario) || empty($usuario->usu_id)) {
      return null;
    }

    $usuario->bloqueado = 1;

    $this->session->set('USUARIO', $usuario);

    return $usuario;
  }

  public function desbloquearSessaoUsuario(string $senha): object
  {
    $usuario = $this->session->get('USUARIO');

    if (is_null($usuario) || empty($usuario->usu_id)) {
      return (new SimpleResponse("Erro", "Sessão de usuário não encontrada"))
        ->erro()
        ->noDestroy();
    }

    $getUsuario = $this->rawQuery(
      "SELECT usu_id, usu_senha
       FROM {$this->getTable()}
       WHERE usu_id = :usu_id
       AND usu_status = 'Ativo'
       LIMIT 1",
      [
        'usu_id' => (int) $usuario->usu_id
      ],
      true,
      "Usuário encontrado com sucesso||Erro ao buscar usuário"
    );

    if (!$getUsuario->status || $getUsuario->total != 1) {
      return (new SimpleResponse("Erro", "Usuário não encontrado ou inativo"))
        ->erro()
        ->noDestroy();
    }

    if (empty($getUsuario->data[0]->usu_senha) || !password_verify($senha, $getUsuario->data[0]->usu_senha)) {
      return (new SimpleResponse("Erro", "Senha incorreta"))
        ->erro()
        ->noDestroy();
    }

    $usuario->bloqueado = 0;

    $this->session->set('USUARIO', $usuario);

    return (new SimpleResponse("Sucesso", "Sessão desbloqueada com sucesso"))
      ->sucesso()
      ->noDestroy();
  }

  public function getUsuarioAtivo(): object
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

    $empresaTable = DB_PREFIX . '_empresa';
    $usuarioEmpresaTable = DB_PREFIX . '_usuario_empresa';
    $usuarioOrganizacaoTable = DB_PREFIX . '_usuario_organizacao';

    $whereEmpresa = "";
    $params = [
      'usu_idorganizacao' => (int) $this->contexto->organizacao->org_id,
      'uem_idorganizacao' => (int) $this->contexto->organizacao->org_id,
      'uor_idorganizacao' => (int) $this->contexto->organizacao->org_id
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

      $whereEmpresa = "
        AND (
          {$this->getTable()}.usu_idempresa = :usu_idempresa
          OR {$usuarioEmpresaTable}.uem_idempresa = :uem_idempresa
        )
      ";

      $params['usu_idempresa'] = (int) $this->contexto->empresa->emp_id;
      $params['uem_idempresa'] = (int) $this->contexto->empresa->emp_id;
    }

    return $this->rawQuery(
      "SELECT DISTINCT
         {$this->getTable()}.*,

         {$empresaTable}.emp_nomefantasia,
         {$empresaTable}.emp_razaosocial,
         {$empresaTable}.emp_cnpjcpf,

         CASE
           WHEN {$this->getTable()}.usu_online = 1 THEN 'Online'
           ELSE 'Offline'
         END AS usu_online_texto
       FROM {$this->getTable()}

       LEFT JOIN {$empresaTable}
         ON {$empresaTable}.emp_id = {$this->getTable()}.usu_idempresa

       LEFT JOIN {$usuarioEmpresaTable}
         ON {$usuarioEmpresaTable}.uem_idusuario = {$this->getTable()}.usu_id
         AND {$usuarioEmpresaTable}.uem_status = 'Ativo'

       LEFT JOIN {$usuarioOrganizacaoTable}
         ON {$usuarioOrganizacaoTable}.uor_idusuario = {$this->getTable()}.usu_id
         AND {$usuarioOrganizacaoTable}.uor_status = 'Ativo'

       WHERE {$this->getTable()}.usu_status = 'Ativo'
       AND (
         {$this->getTable()}.usu_idorganizacao = :usu_idorganizacao
         OR {$usuarioEmpresaTable}.uem_idorganizacao = :uem_idorganizacao
         OR {$usuarioOrganizacaoTable}.uor_idorganizacao = :uor_idorganizacao
       )
       {$whereEmpresa}

       ORDER BY
         {$this->getTable()}.usu_razaosocial ASC,
         {$this->getTable()}.usu_nomefantasia ASC,
         {$this->getTable()}.usu_id ASC",
      $params,
      true,
      "Usuários encontrados com sucesso||Erro ao buscar usuários"
    );
  }

  public function pesquisarUsuario(array $filtros): object
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

    $empresaTable = DB_PREFIX . '_empresa';
    $usuarioEmpresaTable = DB_PREFIX . '_usuario_empresa';
    $usuarioOrganizacaoTable = DB_PREFIX . '_usuario_organizacao';

    $tipoPesquisa = trim((string) ($filtros['lbTipoPesquisa'] ?? 'inicial'));
    $conteudo = trim((string) ($filtros['lbLik'] ?? ''));
    $status = trim((string) ($filtros['usu_status'] ?? ''));
    $online = trim((string) ($filtros['usu_online'] ?? ''));
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
      'usu_idorganizacao' => (int) $this->contexto->organizacao->org_id,
      'uem_idorganizacao' => (int) $this->contexto->organizacao->org_id,
      'uor_idorganizacao' => (int) $this->contexto->organizacao->org_id
    ];

    $where = "
      WHERE (
        {$this->getTable()}.usu_idorganizacao = :usu_idorganizacao
        OR {$usuarioEmpresaTable}.uem_idorganizacao = :uem_idorganizacao
        OR {$usuarioOrganizacaoTable}.uor_idorganizacao = :uor_idorganizacao
      )
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
        AND (
          {$this->getTable()}.usu_idempresa = :usu_idempresa
          OR {$usuarioEmpresaTable}.uem_idempresa = :uem_idempresa
        )
      ";

      $params['usu_idempresa'] = (int) $this->contexto->empresa->emp_id;
      $params['uem_idempresa'] = (int) $this->contexto->empresa->emp_id;
    }

    if ($conteudo !== '') {
      $where .= "
        AND (
          {$this->getTable()}.usu_id LIKE :conteudo_usu_id
          OR {$this->getTable()}.usu_chave LIKE :conteudo_usu_chave
          OR {$this->getTable()}.usu_cnpjcpf LIKE :conteudo_usu_cnpjcpf
          OR {$this->getTable()}.usu_razaosocial LIKE :conteudo_usu_razaosocial
          OR {$this->getTable()}.usu_nomefantasia LIKE :conteudo_usu_nomefantasia
          OR {$this->getTable()}.usu_email LIKE :conteudo_usu_email
          OR {$this->getTable()}.usu_username LIKE :conteudo_usu_username
          OR {$this->getTable()}.usu_celular LIKE :conteudo_usu_celular
          OR {$empresaTable}.emp_razaosocial LIKE :conteudo_emp_razaosocial
          OR {$empresaTable}.emp_nomefantasia LIKE :conteudo_emp_nomefantasia
          OR {$empresaTable}.emp_cnpjcpf LIKE :conteudo_emp_cnpjcpf
        )
      ";

      $params['conteudo_usu_id'] = "%{$conteudo}%";
      $params['conteudo_usu_chave'] = "%{$conteudo}%";
      $params['conteudo_usu_cnpjcpf'] = "%{$conteudo}%";
      $params['conteudo_usu_razaosocial'] = "%{$conteudo}%";
      $params['conteudo_usu_nomefantasia'] = "%{$conteudo}%";
      $params['conteudo_usu_email'] = "%{$conteudo}%";
      $params['conteudo_usu_username'] = "%{$conteudo}%";
      $params['conteudo_usu_celular'] = "%{$conteudo}%";
      $params['conteudo_emp_razaosocial'] = "%{$conteudo}%";
      $params['conteudo_emp_nomefantasia'] = "%{$conteudo}%";
      $params['conteudo_emp_cnpjcpf'] = "%{$conteudo}%";
    }

    if ($status !== '') {
      $where .= "
        AND {$this->getTable()}.usu_status = :usu_status
      ";

      $params['usu_status'] = $status;
    }

    if ($online !== '') {
      $where .= "
        AND {$this->getTable()}.usu_online = :usu_online
      ";

      $params['usu_online'] = (int) $online;
    }

    $camposData = [
      'dtcad' => "{$this->getTable()}.usu_dtcad",
      'dtalter' => "{$this->getTable()}.usu_dtalter",
      'dtonline' => "{$this->getTable()}.usu_dtonline",
      'dtlogin' => "{$this->getTable()}.usu_dtlogin",
      'usu_dtcad' => "{$this->getTable()}.usu_dtcad",
      'usu_dtalter' => "{$this->getTable()}.usu_dtalter",
      'usu_dtonline' => "{$this->getTable()}.usu_dtonline",
      'usu_dtlogin' => "{$this->getTable()}.usu_dtlogin"
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
      'usu_id' => "{$this->getTable()}.usu_id",
      'usu_chave' => "{$this->getTable()}.usu_chave",
      'usu_razaosocial' => "{$this->getTable()}.usu_razaosocial",
      'usu_nomefantasia' => "{$this->getTable()}.usu_nomefantasia",
      'usu_cnpjcpf' => "{$this->getTable()}.usu_cnpjcpf",
      'usu_email' => "{$this->getTable()}.usu_email",
      'usu_username' => "{$this->getTable()}.usu_username",
      'usu_celular' => "{$this->getTable()}.usu_celular",
      'usu_status' => "{$this->getTable()}.usu_status",
      'usu_online' => "{$this->getTable()}.usu_online",
      'usu_dtonline' => "{$this->getTable()}.usu_dtonline",
      'usu_dtlogin' => "{$this->getTable()}.usu_dtlogin",
      'usu_dtcad' => "{$this->getTable()}.usu_dtcad",
      'usu_dtalter' => "{$this->getTable()}.usu_dtalter"
    ];

    if (!isset($camposOrdem[$campoOrdem])) {
      $campoOrdem = 'usu_razaosocial';
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
      "SELECT DISTINCT
         {$this->getTable()}.usu_id,
         {$this->getTable()}.usu_chave,
         {$this->getTable()}.usu_idorganizacao,
         {$this->getTable()}.usu_idempresa,
         {$this->getTable()}.usu_status,
         {$this->getTable()}.usu_statusclass,
         {$this->getTable()}.usu_status_motivo,
         {$this->getTable()}.usu_cnpjcpf,
         {$this->getTable()}.usu_razaosocial,
         {$this->getTable()}.usu_nomefantasia,
         {$this->getTable()}.usu_observacao,
         {$this->getTable()}.usu_rg,
         {$this->getTable()}.usu_dtnasc,
         {$this->getTable()}.usu_idsexo,
         {$this->getTable()}.usu_idestadocivil,
         {$this->getTable()}.usu_idnaturalidade,
         {$this->getTable()}.usu_email,
         {$this->getTable()}.usu_celular,
         {$this->getTable()}.usu_username,
         {$this->getTable()}.usu_logo,
         {$this->getTable()}.usu_niveisacesso,
         {$this->getTable()}.usu_online,
         {$this->getTable()}.usu_dtonline,
         {$this->getTable()}.usu_dtlogin,
         {$this->getTable()}.usu_dtcad,
         {$this->getTable()}.usu_dtalter,

         {$empresaTable}.emp_id,
         {$empresaTable}.emp_cnpjcpf,
         {$empresaTable}.emp_razaosocial,
         {$empresaTable}.emp_nomefantasia,
         {$empresaTable}.emp_status,
         {$empresaTable}.emp_statusclass

       FROM {$this->getTable()}

       LEFT JOIN {$empresaTable}
         ON {$empresaTable}.emp_id = {$this->getTable()}.usu_idempresa

       LEFT JOIN {$usuarioEmpresaTable}
         ON {$usuarioEmpresaTable}.uem_idusuario = {$this->getTable()}.usu_id
         AND {$usuarioEmpresaTable}.uem_status = 'Ativo'

       LEFT JOIN {$usuarioOrganizacaoTable}
         ON {$usuarioOrganizacaoTable}.uor_idusuario = {$this->getTable()}.usu_id
         AND {$usuarioOrganizacaoTable}.uor_status = 'Ativo'

       {$where}

       ORDER BY {$camposOrdem[$campoOrdem]} {$ordem},
         {$this->getTable()}.usu_id ASC

       {$limitSql}",
      $params,
      true,
      "Usuários encontrados com sucesso||Erro ao pesquisar usuários"
    );

    if ($response->status && !empty($response->data)) {
      foreach ($response->data as $usuario) {
        $niveis = $this->getNiveisAcessoContexto(
          (int) $usuario->usu_id,
          (int) $this->contexto->organizacao->org_id,
          (int) ($this->contexto->empresa->emp_id ?? 0)
        );

        if ($niveis->status && $niveis->total > 0) {
          $usuario->usu_niveisacesso = json_encode(
            $niveis->data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
          );
        }

        $usuario->usu_cnpjcpf_mascara = Helpers::adicionaMascaraCnpjCPf($usuario->usu_cnpjcpf ?? '');
        $usuario->emp_cnpjcpf_mascara = Helpers::adicionaMascaraCnpjCPf($usuario->emp_cnpjcpf ?? '');
        $usuario->usu_celular_mascara = Helpers::adicionaMascaraContato($usuario->usu_celular ?? '');
      }
    }

    return $response;
  }

  public function getUsuarioById(int $usuId): object
  {
    $escopoUsuario = $this->usuarioLogado->escopo_maximo ?? 'Empresa';

    if ($usuId <= 0) {
      return (new SimpleResponse("Erro", "Usuário não informado"))
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
      {$this->getTable()}.usu_id = :usu_id
      AND (
        {$this->getTable()}.usu_idorganizacao = :usu_idorganizacao
        OR uem.uem_idorganizacao = :uem_idorganizacao
        OR uor.uor_idorganizacao = :uor_idorganizacao
      )
    ";

    $params = [
      'usu_id' => $usuId,
      'usu_idorganizacao' => (int) $this->contexto->organizacao->org_id,
      'uem_idorganizacao' => (int) $this->contexto->organizacao->org_id,
      'uor_idorganizacao' => (int) $this->contexto->organizacao->org_id
    ];

    $join = "
      LEFT JOIN " . DB_PREFIX . "_usuario_empresa uem
        ON uem.uem_idusuario = {$this->getTable()}.usu_id
        AND uem.uem_status = 'Ativo'
      LEFT JOIN " . DB_PREFIX . "_usuario_organizacao uor
        ON uor.uor_idusuario = {$this->getTable()}.usu_id
        AND uor.uor_status = 'Ativo'
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
        AND (
          {$this->getTable()}.usu_idempresa = :usu_idempresa
          OR uem.uem_idempresa = :uem_idempresa
        )
      ";

      $params['usu_idempresa'] = (int) $this->contexto->empresa->emp_id;
      $params['uem_idempresa'] = (int) $this->contexto->empresa->emp_id;
    }

    $response = $this->select(
      $where,
      "{$this->getTable()}.*",
      $join,
      $params,
      "Usuário encontrado com sucesso||Erro ao buscar usuário"
    );

    if ($response->status && $response->total === 1) {
      $usuario = $response->data[0];
      $orgId = (int) $this->contexto->organizacao->org_id;
      $empId = (int) ($this->contexto->empresa->emp_id ?? 0);
      $niveis = $this->getNiveisAcessoContexto(
        (int) $usuario->usu_id,
        $orgId,
        $empId
      );

      $usuario->usu_idorganizacao = $orgId;
      $usuario->usu_idempresa = $empId;

      if ($niveis->status && $niveis->total > 0) {
        $usuario->usu_niveisacesso = json_encode(
          $niveis->data,
          JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
      }
    }

    return $response;
  }

  public function salvarUsuario(array $data): object
  {
    $location = trim((string) ($data['location'] ?? ''));
    unset($data['location']);

    $usuId = (int) ($data['usu_id'] ?? 0);

    $camposPermitidos = [
      'usu_id',
      'usu_chave',
      'usu_idorganizacao',
      'usu_idempresa',
      'usu_idorganizacaoprincipal',
      'usu_idempresaprincipal',
      'usu_status',
      'usu_statusclass',
      'usu_status_motivo',
      'usu_cnpjcpf',
      'usu_razaosocial',
      'usu_nomefantasia',
      'usu_observacao',
      'usu_im',
      'usu_ie',
      'usu_rg',
      'usu_dtnasc',
      'usu_dtnasc_notificacao_wpp',
      'usu_dtnasc_notificacao_email',
      'usu_nomemae',
      'usu_nomepai',
      'usu_idsexo',
      'usu_idestadocivil',
      'usu_idnaturalidade',
      'usu_logo',
      'usu_planodefundo',
      'usu_sidebar',
      'usu_home',
      'usu_paletacores',
      'usu_tema',
      'usu_niveisacesso',
      'usu_email',
      'usu_celular',
      'usu_username',
      'usu_senha',
      'usu_dtexpiracaosenha',
      'usu_codigo',
      'usu_http_user_agent',
      'usu_remote_addr',
      'usu_online',
      'usu_dtonline',
      'usu_dtlogin',
      'usu_log'
    ];

    foreach ($data as $key => $value) {
      if (!in_array($key, $camposPermitidos, true)) {
        unset($data[$key]);
      }
    }

    if ($usuId > 0) {
      $getUsuario = $this->getUsuarioById($usuId);

      if (!$getUsuario->status || $getUsuario->total != 1) {
        return (new SimpleResponse("Atenção", "Usuário não encontrado."))
          ->warning()
          ->noDestroy();
      }

      $usuarioAtual = $getUsuario->data[0];
      $usuarioEmpresaStorage = (int) ($usuarioAtual->usu_idempresa ?? 0);
      $usuarioOrganizacaoStorage = (int) ($usuarioAtual->usu_idorganizacao ?? $this->contexto->organizacao->org_id ?? 0);

      if (!empty($data['usu_logo']) && preg_match('/^data:[a-zA-Z0-9\.\+\-]+\/[a-zA-Z0-9\.\+\-]+;base64,/', $data['usu_logo'])) {
        $upload = new SimpleUpload('usuario', $usuId, $usuarioEmpresaStorage, $usuarioOrganizacaoStorage);
        $salvarLogo = $upload->salvarBase64($data['usu_logo'], 'logo', 'logo');

        if (!$salvarLogo->status) {
          return $salvarLogo;
        }

        if (!empty($usuarioAtual->usu_logo) && strpos($usuarioAtual->usu_logo, 'storage/global') !== 0) {
          $upload->moverParaLixeira($usuarioAtual->usu_logo);
        }

        $data['usu_logo'] = $salvarLogo->arq_caminho;
      }

      if (!empty($data['usu_planodefundo']) && preg_match('/^data:[a-zA-Z0-9\.\+\-]+\/[a-zA-Z0-9\.\+\-]+;base64,/', $data['usu_planodefundo'])) {
        $upload = new SimpleUpload('usuario', $usuId, $usuarioEmpresaStorage, $usuarioOrganizacaoStorage);
        $salvarPlanoDeFundo = $upload->salvarBase64($data['usu_planodefundo'], 'planodefundo', 'planodefundo');

        if (!$salvarPlanoDeFundo->status) {
          return $salvarPlanoDeFundo;
        }

        if (!empty($usuarioAtual->usu_planodefundo) && strpos($usuarioAtual->usu_planodefundo, 'storage/global') !== 0) {
          $upload->moverParaLixeira($usuarioAtual->usu_planodefundo);
        }

        $data['usu_planodefundo'] = $salvarPlanoDeFundo->arq_caminho;
      }

      if (isset($data['usu_cnpjcpf'])) {
        $documento = Helpers::somenteNumeros($data['usu_cnpjcpf']);

        if ($documento === '') {
          return (new SimpleResponse("Atenção", "Informe o documento do usuário."))
            ->warning()
            ->noDestroy();
        }

        if (!in_array(strlen($documento), [11, 14], true)) {
          return (new SimpleResponse("Atenção", "Informe um CPF ou CNPJ válido."))
            ->warning()
            ->noDestroy();
        }

        $data['usu_cnpjcpf'] = $documento;

        if (strlen($documento) === 11) {
          $data['usu_im'] = null;
          $data['usu_ie'] = null;
        }

        if (strlen($documento) === 14) {
          $data['usu_rg'] = null;
          $data['usu_nomemae'] = null;
          $data['usu_nomepai'] = null;
          $data['usu_idsexo'] = null;
          $data['usu_idestadocivil'] = null;
          $data['usu_idnaturalidade'] = null;
        }
      }

      if (isset($data['usu_razaosocial']) && trim((string) $data['usu_razaosocial']) === '') {
        return (new SimpleResponse("Atenção", "Informe o nome ou razão social do usuário."))
          ->warning()
          ->noDestroy();
      }

      if (isset($data['usu_email']) && trim((string) $data['usu_email']) === '') {
        return (new SimpleResponse("Atenção", "Informe o e-mail do usuário."))
          ->warning()
          ->noDestroy();
      }

      if (isset($data['usu_niveisacesso']) && trim((string) $data['usu_niveisacesso']) === '') {
        return (new SimpleResponse("Atenção", "Informe pelo menos um nível de acesso."))
          ->warning()
          ->noDestroy();
      }

      if (array_key_exists('usu_status', $data)) {
        $statusUsuario = trim((string) ($data['usu_status'] ?? ''));
        $data['usu_status'] = in_array($statusUsuario, ['Ativo', 'Inativo'], true) ? $statusUsuario : 'Ativo';
        $data['usu_statusclass'] = $data['usu_status'] === 'Ativo' ? 'success' : 'danger';
      }

      $motivoStatusInformado = trim((string) ($data['usu_status_motivo'] ?? ''));

      if (!empty($data['usu_celular'])) {
        $data['usu_celular'] = Helpers::somenteNumeros($data['usu_celular']);
      }

      if (!empty($data['usu_username'])) {
        $data['usu_username'] = strtolower(trim((string) $data['usu_username']));
        $data['usu_username'] = preg_replace('/[^a-z0-9_]/', '', $data['usu_username']);
      }

      if (!empty($data['usu_senha'])) {
        $data['usu_senha'] = password_hash($data['usu_senha'], PASSWORD_DEFAULT);
      } else {
        unset($data['usu_senha']);
      }

      unset(
        $data['usu_id'],
        $data['usu_chave'],
        $data['usu_idorganizacao'],
        $data['usu_idempresa'],
        $data['usu_codigo'],
        $data['usu_http_user_agent'],
        $data['usu_remote_addr'],
        $data['usu_online'],
        $data['usu_dtonline'],
        $data['usu_dtlogin'],
        $data['usu_log']
      );

      foreach ($data as $key => $value) {
        if (is_string($value)) {
          $data[$key] = trim($value);
        }

        if ($data[$key] === '') {
          $data[$key] = null;
        }
      }

      $statusAtual = trim((string) ($usuarioAtual->usu_status ?? ''));
      $statusNovo = array_key_exists('usu_status', $data)
        ? trim((string) ($data['usu_status'] ?? ''))
        : $statusAtual;

      if ($statusAtual !== $statusNovo) {
        if ($statusNovo === 'Inativo') {
          if (mb_strlen($motivoStatusInformado) < 10) {
            return (new SimpleResponse(
              'Motivo obrigatÃ³rio',
              'Informe um motivo com pelo menos 10 caracteres para inativar o usuÃ¡rio.'
            ))
              ->warning()
              ->noDestroy();
          }

          $data['usu_status_motivo'] = json_encode([
            'motivo' => $motivoStatusInformado,
            'data' => date('d/m/Y H:i:s'),
            'responsavel' => $this->usuarioLogado->usu_razaosocial ?? 'Sistema',
            'usu_id' => (int) ($this->usuarioLogado->usu_id ?? 0)
          ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif ($statusNovo === 'Ativo') {
          if ($motivoStatusInformado !== '') {
            $data['usu_status_motivo'] = json_encode([
              'motivo' => $motivoStatusInformado,
              'data' => date('d/m/Y H:i:s'),
              'responsavel' => $this->usuarioLogado->usu_razaosocial ?? 'Sistema',
              'usu_id' => (int) ($this->usuarioLogado->usu_id ?? 0)
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
          } else {
            unset($data['usu_status_motivo']);
          }
        }
      } else {
        unset($data['usu_status_motivo']);
      }

      if ($statusAtual !== 'Ativo' && $statusNovo === 'Ativo') {
        $limiteReativacao = (new LimitePlanoService($this->contexto))->verificar('usuario');

        if ($limiteReativacao->status && !$limiteReativacao->permitido) {
          return (new SimpleResponse(
            'Limite do plano',
            'Não é possível reativar este usuário porque o limite de usuários ativos do plano foi atingido.'
          ))
            ->warning()
            ->setArrayParams(['data' => $limiteReativacao])
            ->noDestroy();
        }
      }

      if ($statusAtual === 'Ativo' && $statusNovo === 'Inativo') {
        $responsavel = $this->rawQuery(
          "SELECT org_id
           FROM " . DB_PREFIX . "_organizacao
           WHERE org_id = :org_id
           AND org_idusuarioresponsavel = :usu_id
           LIMIT 1",
          [
            'org_id' => (int) ($usuarioAtual->usu_idorganizacao ?? $this->contexto->organizacao->org_id ?? 0),
            'usu_id' => $usuId
          ],
          true
        );

        if ($responsavel->status && $responsavel->total > 0) {
          return (new SimpleResponse(
            'Usuário responsável',
            'Este usuário é o responsável pela organização e não pode ser inativado. Transfira a responsabilidade para outro administrador ativo antes de continuar.'
          ))
            ->warning()
            ->noDestroy();
        }
      }

      $validarUnicidade = $this->validarUnicidadeUsuario(
        $data,
        (int) ($this->contexto->organizacao->org_id ?? 0),
        $usuId
      );

      if (!$validarUnicidade->status) {
        return $validarUnicidade;
      }

      $response = $this->update(
        $data,
        [
          'usu_id' => $usuId
        ],
        "Usuário atualizado com sucesso||Erro ao atualizar usuário"
      );

      if ($response->status) {
        $sincronizar = $this->sincronizarVinculosUsuario(
          $usuId,
          $data['usu_niveisacesso'] ?? null
        );

        if (!$sincronizar->status) {
          return $sincronizar;
        }

        $response->location = Helpers::url("usuario/form/{$usuId}{$location}");
      }

      return $response;
    }

    $documento = Helpers::somenteNumeros($data['usu_cnpjcpf'] ?? '');

    if ($documento === '') {
      return (new SimpleResponse("Atenção", "Informe o documento do usuário."))
        ->warning()
        ->noDestroy();
    }

    if (!in_array(strlen($documento), [11, 14], true)) {
      return (new SimpleResponse("Atenção", "Informe um CPF ou CNPJ válido."))
        ->warning()
        ->noDestroy();
    }

    if (empty(trim((string) ($data['usu_razaosocial'] ?? '')))) {
      return (new SimpleResponse("Atenção", "Informe o nome ou razão social do usuário."))
        ->warning()
        ->noDestroy();
    }

    if (empty(trim((string) ($data['usu_niveisacesso'] ?? '')))) {
      return (new SimpleResponse("Atenção", "Informe pelo menos um nível de acesso."))
        ->warning()
        ->noDestroy();
    }

    if (empty(trim((string) ($data['usu_email'] ?? '')))) {
      return (new SimpleResponse("Atenção", "Informe o e-mail do usuário."))
        ->warning()
        ->noDestroy();
    }

    if (empty(trim((string) ($data['usu_senha'] ?? '')))) {
      return (new SimpleResponse("Atenção", "Informe a senha do usuário."))
        ->warning()
        ->noDestroy();
    }

    if (
      empty($this->contexto->organizacao->org_id) ||
      empty($this->contexto->empresa->emp_id)
    ) {
      return (new SimpleResponse("Erro", "Contexto de organização ou empresa não identificado."))
        ->erro()
        ->noDestroy();
    }

    $limiteUsuario = (new LimitePlanoService($this->contexto))->verificar('usuario');

    if ($limiteUsuario->status && !$limiteUsuario->permitido) {
      return (new SimpleResponse('Limite do plano', $limiteUsuario->text))
        ->warning()
        ->setArrayParams(['data' => $limiteUsuario])
        ->noDestroy();
    }

    $data['usu_idorganizacao'] = (int) $this->contexto->organizacao->org_id;
    $data['usu_idempresa'] = (int) $this->contexto->empresa->emp_id;
    $data['usu_idorganizacaoprincipal'] = (int) $this->contexto->organizacao->org_id;
    $data['usu_idempresaprincipal'] = (int) $this->contexto->empresa->emp_id;
    $data['usu_cnpjcpf'] = $documento;

    if (strlen($documento) === 11) {
      $data['usu_im'] = null;
      $data['usu_ie'] = null;
    }

    if (strlen($documento) === 14) {
      $data['usu_rg'] = null;
      $data['usu_nomemae'] = null;
      $data['usu_nomepai'] = null;
      $data['usu_idsexo'] = null;
      $data['usu_idestadocivil'] = null;
      $data['usu_idnaturalidade'] = null;
    }

    $data['usu_chave'] = 'USU_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
    $data['usu_logo'] = $data['usu_logo'] ?? 'storage/global/semperfil.png';
    $data['usu_planodefundo'] = $data['usu_planodefundo'] ?? 'storage/global/planodefundo.jpg';
    $data['usu_sidebar'] = $data['usu_sidebar'] ?? 1;
    $data['usu_home'] = $data['usu_home'] ?? 'dashboard';
    $data['usu_tema'] = $data['usu_tema'] ?? 'light';
    $data['usu_senha'] = password_hash($data['usu_senha'], PASSWORD_DEFAULT);

    if (!empty($data['usu_celular'])) {
      $data['usu_celular'] = Helpers::somenteNumeros($data['usu_celular']);
    }

    if (!empty($data['usu_username'])) {
      $data['usu_username'] = strtolower(trim((string) $data['usu_username']));
      $data['usu_username'] = preg_replace('/[^a-z0-9_]/', '', $data['usu_username']);
    }

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

    $statusUsuario = trim((string) ($data['usu_status'] ?? ''));
    $data['usu_status'] = in_array($statusUsuario, ['Ativo', 'Inativo'], true) ? $statusUsuario : 'Ativo';
    $data['usu_statusclass'] = $data['usu_status'] === 'Ativo' ? 'success' : 'danger';

    if ($data['usu_status'] === 'Inativo') {
      $motivoStatusInformado = trim((string) ($data['usu_status_motivo'] ?? ''));

      if (mb_strlen($motivoStatusInformado) < 10) {
        return (new SimpleResponse(
          'Motivo obrigatÃ³rio',
          'Informe um motivo com pelo menos 10 caracteres para cadastrar o usuÃ¡rio como inativo.'
        ))
          ->warning()
          ->noDestroy();
      }

      $data['usu_status_motivo'] = json_encode([
        'motivo' => $motivoStatusInformado,
        'data' => date('d/m/Y H:i:s'),
        'responsavel' => $this->usuarioLogado->usu_razaosocial ?? 'Sistema',
        'usu_id' => (int) ($this->usuarioLogado->usu_id ?? 0)
      ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    unset($data['usu_id']);

    $validarUnicidade = $this->validarUnicidadeUsuario(
      $data,
      (int) ($this->contexto->organizacao->org_id ?? 0)
    );

    if (!$validarUnicidade->status) {
      return $validarUnicidade;
    }

    $response = $this->insert(
      $data,
      "Usuário cadastrado com sucesso||Erro ao cadastrar usuário"
    );

    if ($response->status) {
      $sincronizar = $this->sincronizarVinculosUsuario(
        (int) $response->lastInsertId,
        $data['usu_niveisacesso'] ?? null
      );

      if (!$sincronizar->status) {
        return $sincronizar;
      }

      $response->location = Helpers::url("usuario/form/{$response->lastInsertId}{$location}");
    }

    return $response;
  }

  /**
   * Mantém os vínculos relacionais do usuário sincronizados com o formulário
   * legado. O JSON continua sendo armazenado como compatibilidade, mas não é
   * mais a fonte de autorização.
   */
  private function sincronizarVinculosUsuario(int $usuId, ?string $niveisJson): object
  {
    $orgId = (int) ($this->contexto->organizacao->org_id ?? 0);
    $empId = (int) ($this->contexto->empresa->emp_id ?? 0);

    if ($usuId <= 0 || $orgId <= 0 || $empId <= 0) {
      return (new SimpleResponse("Erro", "Contexto invÃ¡lido para vincular o usuÃ¡rio"))
        ->erro()
        ->noDestroy();
    }

    $usuarioOrganizacaoTable = DB_PREFIX . '_usuario_organizacao';
    $usuarioEmpresaTable = DB_PREFIX . '_usuario_empresa';
    $usuarioNivelTable = DB_PREFIX . '_usuario_nivelacesso';
    $nivelTable = DB_PREFIX . '_nivelacesso';

    $vinculoOrganizacao = $this->rawQuery(
      "INSERT INTO {$usuarioOrganizacaoTable}
       (uor_idusuario, uor_idorganizacao, uor_status, uor_statusclass, uor_principal, uor_log)
       VALUES (:uor_idusuario, :uor_idorganizacao, 'Ativo', 'success', 0, '[]')
       ON DUPLICATE KEY UPDATE
         uor_status = 'Ativo',
         uor_statusclass = 'success'",
      [
        'uor_idusuario' => $usuId,
        'uor_idorganizacao' => $orgId
      ],
      false,
      "VÃ­nculo organizacional salvo||Erro ao salvar vÃ­nculo organizacional"
    );

    if (!$vinculoOrganizacao->status) {
      return $vinculoOrganizacao;
    }

    $vinculoEmpresa = $this->rawQuery(
      "INSERT INTO {$usuarioEmpresaTable}
       (uem_idorganizacao, uem_idusuario, uem_idempresa, uem_status, uem_statusclass, uem_principal, uem_log)
       VALUES (:uem_idorganizacao, :uem_idusuario, :uem_idempresa, 'Ativo', 'success', 0, '[]')
       ON DUPLICATE KEY UPDATE
         uem_status = 'Ativo',
         uem_statusclass = 'success'",
      [
        'uem_idorganizacao' => $orgId,
        'uem_idusuario' => $usuId,
        'uem_idempresa' => $empId
      ],
      false,
      "VÃ­nculo com a igreja salvo||Erro ao salvar vÃ­nculo com a igreja"
    );

    if (!$vinculoEmpresa->status) {
      return $vinculoEmpresa;
    }

    if (is_null($niveisJson)) {
      return (new SimpleResponse("Sucesso", "VÃ­nculos do usuÃ¡rio sincronizados"))
        ->sucesso()
        ->noDestroy();
    }

    $niveis = $this->normalizarNiveisAcessoUsuario($niveisJson);

    $desativar = $this->rawQuery(
      "UPDATE {$usuarioNivelTable}
       SET una_status = 'Inativo', una_statusclass = 'danger'
       WHERE una_idusuario = :una_idusuario
       AND (
         (una_idorganizacao IS NULL AND una_idempresa IS NULL)
         OR (una_idorganizacao = :una_idorganizacao AND una_idempresa IS NULL)
         OR (una_idorganizacao = :una_idorganizacao_empresa AND una_idempresa = :una_idempresa)
       )",
      [
        'una_idusuario' => $usuId,
        'una_idorganizacao' => $orgId,
        'una_idorganizacao_empresa' => $orgId,
        'una_idempresa' => $empId
      ],
      false,
      "NÃ­veis anteriores atualizados||Erro ao atualizar nÃ­veis anteriores"
    );

    if (!$desativar->status) {
      return $desativar;
    }

    foreach ($niveis as $nivelInformado) {
      $nivelId = (int) ($nivelInformado->nva_id ?? 0);

      if ($nivelId <= 0) {
        continue;
      }

      $getNivel = $this->rawQuery(
        "SELECT nva_id, nva_idorganizacao, nva_idempresa, nva_escopo
         FROM {$nivelTable}
         WHERE nva_id = :nva_id
         AND nva_status = 'Ativo'
         LIMIT 1",
        ['nva_id' => $nivelId]
      );

      if (!$getNivel->status || $getNivel->total !== 1) {
        continue;
      }

      $nivel = $getNivel->data[0];
      $nivelOrgId = null;
      $nivelEmpId = null;
      $escopo = $nivel->nva_escopo ?? 'Empresa';

      if ($escopo === 'Organizacao') {
        if ((int) $nivel->nva_idorganizacao !== $orgId) {
          continue;
        }

        $nivelOrgId = $orgId;
      } elseif ($escopo === 'Empresa') {
        if (
          (int) $nivel->nva_idorganizacao !== $orgId
          || (int) $nivel->nva_idempresa !== $empId
        ) {
          continue;
        }

        $nivelOrgId = $orgId;
        $nivelEmpId = $empId;
      }

      $condicaoOrganizacao = is_null($nivelOrgId)
        ? 'una_idorganizacao IS NULL'
        : 'una_idorganizacao = :nivel_org_id';
      $condicaoEmpresa = is_null($nivelEmpId)
        ? 'una_idempresa IS NULL'
        : 'una_idempresa = :nivel_emp_id';

      $parametrosNivel = [
        'una_idusuario' => $usuId,
        'una_idnivelacesso' => $nivelId
      ];

      if (!is_null($nivelOrgId)) {
        $parametrosNivel['nivel_org_id'] = $nivelOrgId;
      }

      if (!is_null($nivelEmpId)) {
        $parametrosNivel['nivel_emp_id'] = $nivelEmpId;
      }

      $removerNivelDuplicado = $this->rawQuery(
        "DELETE FROM {$usuarioNivelTable}
         WHERE una_idusuario = :una_idusuario
         AND una_idnivelacesso = :una_idnivelacesso
         AND {$condicaoOrganizacao}
         AND {$condicaoEmpresa}",
        $parametrosNivel,
        false,
        "NÃ­vel anterior removido||Erro ao remover nÃ­vel anterior"
      );

      if (!$removerNivelDuplicado->status) {
        return $removerNivelDuplicado;
      }

      $salvarNivel = $this->rawQuery(
        "INSERT INTO {$usuarioNivelTable}
         (una_idusuario, una_idorganizacao, una_idempresa, una_idnivelacesso, una_status, una_statusclass, una_principal, una_log)
         VALUES (:una_idusuario, :una_idorganizacao, :una_idempresa, :una_idnivelacesso, 'Ativo', 'success', 0, '[]')
         ON DUPLICATE KEY UPDATE
           una_status = 'Ativo',
           una_statusclass = 'success'",
        [
          'una_idusuario' => $usuId,
          'una_idorganizacao' => $nivelOrgId,
          'una_idempresa' => $nivelEmpId,
          'una_idnivelacesso' => $nivelId
        ],
        false,
        "NÃ­vel de acesso salvo||Erro ao salvar nÃ­vel de acesso"
      );

      if (!$salvarNivel->status) {
        return $salvarNivel;
      }
    }

    return (new SimpleResponse("Sucesso", "VÃ­nculos do usuÃ¡rio sincronizados"))
      ->sucesso()
      ->noDestroy();
  }

  private function normalizarNiveisAcessoUsuario(string $json): array
  {
    $niveis = json_decode($json);

    if (!is_array($niveis)) {
      return [];
    }

    $normalizados = [];

    foreach ($niveis as $nivel) {
      $nvaId = (int) ($nivel->nva_id ?? 0);
      $nvaNome = trim((string) ($nivel->nva_nome ?? ''));

      if ($nvaId <= 0 || $nvaNome === '') {
        continue;
      }

      $normalizados[$nvaId] = (object) [
        'nva_id' => $nvaId,
        'nva_nome' => $nvaNome
      ];
    }

    return array_values($normalizados);
  }

  private function validarForcaSenhaUsuario(string $senha): bool
  {
    if (strlen($senha) < 8) {
      return false;
    }

    if (!preg_match('/[A-Za-z]/', $senha)) {
      return false;
    }

    if (!preg_match('/[0-9]/', $senha)) {
      return false;
    }

    if (!preg_match('/[^A-Za-z0-9]/', $senha)) {
      return false;
    }

    return true;
  }

  private function validarUnicidadeUsuario(array $data, int $orgId, int $usuId = 0): object
  {
    $campos = [
      'usu_cnpjcpf' => 'Já existe um usuário com este documento nesta organização.',
      'usu_email' => 'Já existe um usuário com este e-mail nesta organização.',
      'usu_celular' => 'Já existe um usuário com este celular nesta organização.',
      'usu_username' => 'Já existe um usuário com este nome de usuário nesta organização.'
    ];

    foreach ($campos as $campo => $mensagem) {
      if (!array_key_exists($campo, $data) || is_null($data[$campo]) || $data[$campo] === '') {
        continue;
      }

      $params = [
        $campo => $data[$campo]
      ];

      $whereIgnorar = '';

      if ($usuId > 0) {
        $whereIgnorar = "AND usu_id != :usu_id";
        $params['usu_id'] = $usuId;
      }

      $getUsuario = $this->rawQuery(
        "SELECT usu_id
         FROM {$this->getTable()}
         WHERE {$campo} = :{$campo}
         {$whereIgnorar}
         LIMIT 1",
        $params,
        true,
        "Usuário validado com sucesso||Erro ao validar usuário"
      );

      if ($getUsuario->status && $getUsuario->total > 0) {
        return (new SimpleResponse("Atenção", $mensagem))
          ->warning()
          ->setArrayParams([
            'campo' => $campo
          ])
          ->noDestroy();
      }
    }

    return (new SimpleResponse("Validação", "Usuário validado com sucesso"))
      ->sucesso()
      ->noDestroy();
  }

  public function contarUsuariosContexto(bool $ignorarEscopo = false): object
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

    $usuarioEmpresaTable = DB_PREFIX . '_usuario_empresa';
    $usuarioOrganizacaoTable = DB_PREFIX . '_usuario_organizacao';

    $params = [
      'usu_idorganizacao' => (int) $this->contexto->organizacao->org_id,
      'uem_idorganizacao' => (int) $this->contexto->organizacao->org_id,
      'uor_idorganizacao' => (int) $this->contexto->organizacao->org_id
    ];

    $whereEmpresa = "";

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

      $whereEmpresa = "
        AND (
          {$this->getTable()}.usu_idempresa = :usu_idempresa
          OR {$usuarioEmpresaTable}.uem_idempresa = :uem_idempresa
        )
      ";

      $params['usu_idempresa'] = (int) $this->contexto->empresa->emp_id;
      $params['uem_idempresa'] = (int) $this->contexto->empresa->emp_id;
    }

    return $this->rawQuery(
      "SELECT COUNT(DISTINCT {$this->getTable()}.usu_id) AS total
       FROM {$this->getTable()}

       LEFT JOIN {$usuarioEmpresaTable}
         ON {$usuarioEmpresaTable}.uem_idusuario = {$this->getTable()}.usu_id
         AND {$usuarioEmpresaTable}.uem_status = 'Ativo'

       LEFT JOIN {$usuarioOrganizacaoTable}
         ON {$usuarioOrganizacaoTable}.uor_idusuario = {$this->getTable()}.usu_id
         AND {$usuarioOrganizacaoTable}.uor_status = 'Ativo'

       WHERE (
         {$this->getTable()}.usu_idorganizacao = :usu_idorganizacao
         OR {$usuarioEmpresaTable}.uem_idorganizacao = :uem_idorganizacao
         OR {$usuarioOrganizacaoTable}.uor_idorganizacao = :uor_idorganizacao
       )
       AND {$this->getTable()}.usu_status = 'Ativo'
       {$whereEmpresa}",
      $params,
      true,
      "Total de usuários encontrado com sucesso||Erro ao contar usuários"
    );
  }

  public function pesquisarRapida(string $conteudo): object
  {
    $conteudo = trim(rawurldecode($conteudo));

    if ($conteudo === '') {
      return (new SimpleResponse("Atenção", "Digite uma informação para pesquisar"))
        ->warning()
        ->setArrayParams([
          'location' => Helpers::url('usuario/list')
        ])
        ->noDestroy();
    }

    $escopoUsuario = $this->usuarioLogado->escopo_maximo ?? 'Empresa';

    if (empty($this->contexto->organizacao->org_id)) {
      return (new SimpleResponse("Erro", "Contexto de organização não identificado"))
        ->erro()
        ->setArrayParams([
          'location' => Helpers::url('usuario/list')
        ])
        ->noDestroy();
    }

    $empresaTable = DB_PREFIX . '_empresa';
    $usuarioEmpresaTable = DB_PREFIX . '_usuario_empresa';
    $usuarioOrganizacaoTable = DB_PREFIX . '_usuario_organizacao';
    $documento = Helpers::somenteNumeros($conteudo);

    $params = [
      'usu_idorganizacao' => (int) $this->contexto->organizacao->org_id,
      'uem_idorganizacao' => (int) $this->contexto->organizacao->org_id,
      'uor_idorganizacao' => (int) $this->contexto->organizacao->org_id
    ];

    $where = "
      WHERE (
        {$this->getTable()}.usu_idorganizacao = :usu_idorganizacao
        OR {$usuarioEmpresaTable}.uem_idorganizacao = :uem_idorganizacao
        OR {$usuarioOrganizacaoTable}.uor_idorganizacao = :uor_idorganizacao
      )
    ";

    if ($escopoUsuario === 'Empresa') {
      if (empty($this->contexto->empresa->emp_id)) {
        return (new SimpleResponse("Erro", "Contexto de empresa não identificado"))
          ->erro()
          ->setArrayParams([
            'location' => Helpers::url('usuario/list')
          ])
          ->noDestroy();
      }

      $where .= "
        AND (
          {$this->getTable()}.usu_idempresa = :usu_idempresa
          OR {$usuarioEmpresaTable}.uem_idempresa = :uem_idempresa
        )
      ";

      $params['usu_idempresa'] = (int) $this->contexto->empresa->emp_id;
      $params['uem_idempresa'] = (int) $this->contexto->empresa->emp_id;
    }

    $wherePesquisa = [];

    if (ctype_digit($conteudo)) {
      $wherePesquisa[] = "{$this->getTable()}.usu_id = :conteudo_id";
      $params['conteudo_id'] = (int) $conteudo;
    }

    if ($documento !== '') {
      $wherePesquisa[] = "{$this->getTable()}.usu_cnpjcpf LIKE :conteudo_documento";
      $wherePesquisa[] = "{$this->getTable()}.usu_celular LIKE :conteudo_celular";

      $params['conteudo_documento'] = "%{$documento}%";
      $params['conteudo_celular'] = "%{$documento}%";
    }

    $camposTexto = [
      "{$this->getTable()}.usu_razaosocial",
      "{$this->getTable()}.usu_nomefantasia",
      "{$this->getTable()}.usu_email",
      "{$this->getTable()}.usu_username",
      "{$empresaTable}.emp_razaosocial",
      "{$empresaTable}.emp_nomefantasia",
      "{$empresaTable}.emp_cnpjcpf"
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
        WHEN {$this->getTable()}.usu_id = :ordem_id THEN 1
      ";

      $params['ordem_id'] = (int) $conteudo;
    }

    if ($documento !== '') {
      $orderByPrioridade .= "
        WHEN {$this->getTable()}.usu_cnpjcpf = :ordem_documento THEN 2
        WHEN {$this->getTable()}.usu_celular = :ordem_celular THEN 3
      ";

      $params['ordem_documento'] = $documento;
      $params['ordem_celular'] = $documento;
    }

    $orderByPrioridade .= "
        WHEN {$this->getTable()}.usu_email = :ordem_email THEN 4
        WHEN {$this->getTable()}.usu_username = :ordem_username THEN 5
        ELSE 9
      END
    ";

    $params['ordem_email'] = $conteudo;
    $params['ordem_username'] = $conteudo;

    $getUsuario = $this->rawQuery(
      "SELECT DISTINCT
         {$this->getTable()}.usu_id,
         {$this->getTable()}.usu_razaosocial,
         {$this->getTable()}.usu_nomefantasia,
         {$this->getTable()}.usu_cnpjcpf,
         {$this->getTable()}.usu_email,
         {$this->getTable()}.usu_username,
         {$this->getTable()}.usu_status,

         {$empresaTable}.emp_nomefantasia,
         {$empresaTable}.emp_razaosocial

       FROM {$this->getTable()}

       LEFT JOIN {$empresaTable}
         ON {$empresaTable}.emp_id = {$this->getTable()}.usu_idempresa

       LEFT JOIN {$usuarioEmpresaTable}
         ON {$usuarioEmpresaTable}.uem_idusuario = {$this->getTable()}.usu_id
         AND {$usuarioEmpresaTable}.uem_status = 'Ativo'

       LEFT JOIN {$usuarioOrganizacaoTable}
         ON {$usuarioOrganizacaoTable}.uor_idusuario = {$this->getTable()}.usu_id
         AND {$usuarioOrganizacaoTable}.uor_status = 'Ativo'

       {$where}

       ORDER BY
         {$orderByPrioridade},
         {$this->getTable()}.usu_razaosocial ASC,
         {$this->getTable()}.usu_id ASC

       LIMIT 2",
      $params,
      true,
      "Usuário encontrado com sucesso||Erro ao pesquisar usuário"
    );

    if (!$getUsuario->status) {
      return (new SimpleResponse($getUsuario))
        ->erro("Erro ao realizar pesquisa rápida de usuário")
        ->setArrayParams([
          'location' => Helpers::url('usuario/list')
        ])
        ->noDestroy();
    }

    if ($getUsuario->total <= 0) {
      return (new SimpleResponse("Atenção", "Nenhum usuário encontrado para a pesquisa informada"))
        ->warning()
        ->setArrayParams([
          'location' => Helpers::url('usuario/list')
        ])
        ->noDestroy();
    }

    if ($getUsuario->total > 1) {
      return (new SimpleResponse("Atenção", "Mais de um usuário foi encontrado. Use a pesquisa completa para refinar o resultado"))
        ->warning()
        ->setArrayParams([
          'data' => $getUsuario->data,
          'total' => $getUsuario->total,
          'location' => Helpers::url('usuario/list')
        ])
        ->noDestroy();
    }

    $usuario = $getUsuario->data[0];

    return (new SimpleResponse("Sucesso", "Usuário encontrado"))
      ->sucesso()
      ->setArrayParams([
        'data' => $usuario,
        'total' => 1,
        'location' => Helpers::url("usuario/form/{$usuario->usu_id}")
      ])
      ->noDestroy();
  }
}
