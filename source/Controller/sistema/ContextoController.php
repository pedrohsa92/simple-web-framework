<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;
use Source\Model\UsuarioModel;

class ContextoController extends SimpleController
{
  public function __construct()
  {
    parent::__construct();

    $this->validarUsuarioLogado(true);
  }

  public function listar()
  {
    $this->validarPermissoes(['contexto.trocar']);

    $conteudo = trim((string) ($_POST['conteudo'] ?? ''));
    $getContextos = $this->getContextosDisponiveis($conteudo);

    if ($getContextos->status && $getContextos->total > 0) {
      foreach ($getContextos->data as $item) {
        $item->org_cnpjcpf_formatado = !empty($item->org_cnpjcpf)
          ? Helpers::adicionaMascaraCnpjCPf($item->org_cnpjcpf)
          : '-';

        $item->emp_cnpjcpf_formatado = !empty($item->emp_cnpjcpf)
          ? Helpers::adicionaMascaraCnpjCPf($item->emp_cnpjcpf)
          : '-';

        $item->org_logo_url = Helpers::storage($item->org_logo ?? null, 'storage/global/semimagem.jpg');
        $item->emp_logo_url = Helpers::storage($item->emp_logo ?? null, 'storage/global/semimagem.jpg');
      }
    }

    $getContextos->title = 'Sucesso';
    $getContextos->text = $getContextos->total > 0
      ? 'Contextos encontrados'
      : 'Nenhum contexto encontrado';

    $getContextos->json();
  }

  public function trocar()
  {
    $this->validarPermissoes(['contexto.trocar']);

    $orgId = (int) ($_POST['org_id'] ?? 0);
    $empId = (int) ($_POST['emp_id'] ?? 0);

    if ($orgId <= 0 || $empId <= 0) {
      (new SimpleResponse("Erro", "Organização ou empresa não informada"))
        ->erro()
        ->json();
    }

    $getContextos = $this->getContextosDisponiveis('');

    if (!$getContextos->status) {
      $getContextos->json();
    }

    $selecionado = null;

    foreach (($getContextos->data ?? []) as $contexto) {
      if (
        (int) ($contexto->org_id ?? 0) === $orgId
        && (int) ($contexto->emp_id ?? 0) === $empId
      ) {
        $selecionado = $contexto;
        break;
      }
    }

    if (is_null($selecionado)) {
      (new SimpleResponse("Permissão Negada", "Você não tem acesso a essa organização/empresa"))
        ->erro()
        ->json();
    }

    $this->session->set('CONTEXTO', (object) [
      'origem' => 'selecionado',
      'organizacao' => (object) [
        'org_id' => (int) ($selecionado->org_id ?? 0),
        'org_status' => $selecionado->org_status ?? null,
        'org_statusclass' => $selecionado->org_statusclass ?? null,
        'org_idusuarioresponsavel' => $selecionado->org_idusuarioresponsavel ?? null,
        'org_cnpjcpf' => $selecionado->org_cnpjcpf ?? null,
        'org_razaosocial' => $selecionado->org_razaosocial ?? null,
        'org_nomefantasia' => $selecionado->org_nomefantasia ?? null,
        'org_ambiente' => $selecionado->org_ambiente ?? null,
        'org_url_dedicada' => $selecionado->org_url_dedicada ?? null,
        'org_banco_dedicado' => $selecionado->org_banco_dedicado ?? null,
        'org_statusambiente' => $selecionado->org_statusambiente ?? null,
        'org_logo' => $selecionado->org_logo ?? null,
        'org_favicon' => $selecionado->org_favicon ?? null,
        'org_planodefundo' => $selecionado->org_planodefundo ?? null,
        'org_cadastrobasico_preferencia' => $selecionado->org_cadastrobasico_preferencia ?? null
      ],
      'empresa' => (object) [
        'emp_id' => (int) ($selecionado->emp_id ?? 0),
        'emp_idorganizacao' => (int) ($selecionado->emp_idorganizacao ?? 0),
        'emp_tipo' => $selecionado->emp_tipo ?? null,
        'emp_status' => $selecionado->emp_status ?? null,
        'emp_statusclass' => $selecionado->emp_statusclass ?? null,
        'emp_cnpjcpf' => $selecionado->emp_cnpjcpf ?? null,
        'emp_razaosocial' => $selecionado->emp_razaosocial ?? null,
        'emp_nomefantasia' => $selecionado->emp_nomefantasia ?? null,
        'emp_observacao' => $selecionado->emp_observacao ?? null,
        'emp_logo' => $selecionado->emp_logo ?? null,
        'emp_planodefundo' => $selecionado->emp_planodefundo ?? null,
        'emp_cadastrobasico_preferencia' => $selecionado->emp_cadastrobasico_preferencia ?? null
      ]
    ]);

    $getContextos->title = 'Sucesso';
    $usuarioModel = new UsuarioModel();
    $salvarContextoPrincipal = $usuarioModel->rawQuery(
      "UPDATE " . DB_PREFIX . "_usuario
       SET usu_idorganizacao = :usu_idorganizacao,
           usu_idempresa = :usu_idempresa,
           usu_idorganizacaoprincipal = :usu_idorganizacaoprincipal,
           usu_idempresaprincipal = :usu_idempresaprincipal
       WHERE usu_id = :usu_id",
      [
        'usu_idorganizacao' => $orgId,
        'usu_idempresa' => $empId,
        'usu_idorganizacaoprincipal' => $orgId,
        'usu_idempresaprincipal' => $empId,
        'usu_id' => (int) ($this->usuarioLogado->usu_id ?? 0)
      ],
      false,
      "Contexto principal salvo||Erro ao salvar contexto principal"
    );

    if (!$salvarContextoPrincipal->status) {
      $salvarContextoPrincipal->json();
    }

    // O contexto também define os níveis e permissões efetivos da sessão.
    // Sem esta atualização, a interface mudaria de igreja, mas continuaria
    // autorizada pelo contexto anterior.
    $getUsuario = $usuarioModel->getUsuariosLogin(
      '',
      $empId,
      (int) ($this->usuarioLogado->usu_id ?? 0)
    );

    if (!$getUsuario->status || $getUsuario->total !== 1) {
      (new SimpleResponse("Erro", "NÃ£o foi possÃ­vel atualizar as permissÃµes do contexto"))
        ->erro()
        ->json();
    }

    $usuarioAtualizado = $usuarioModel->prepararUsuarioSessao($getUsuario->data[0]);
    $usuarioAtualizado->bloqueado = $this->usuarioLogado->bloqueado ?? 0;
    $this->session->set('USUARIO', $usuarioAtualizado);

    $getContextos->text = 'Contexto alterado com sucesso';
    $getContextos->total = 1;
    $getContextos->totalLinha = 1;
    $getContextos->data = [
      'contexto' => $this->session->get('CONTEXTO')
    ];

    $getContextos->json();
  }

  private function getContextosDisponiveis(string $conteudo = '')
  {
    $escopo = $this->getEscopoMaximoUsuario();

    if ($escopo === 'Projeto') {
      return $this->getContextosProjeto($conteudo);
    }

    if ($escopo === 'Organizacao') {
      return $this->getContextosOrganizacao($conteudo);
    }

    return $this->getContextosEmpresa($conteudo);
  }

  private function getEscopoMaximoUsuario()
  {
    $niveis = $this->usuarioLogado->niveisacesso ?? [];

    if (empty($niveis)) {
      return 'Empresa';
    }

    $placeholders = [];
    $params = [];

    foreach ($niveis as $index => $nivel) {
      $nvaId = is_object($nivel)
        ? (int) ($nivel->nva_id ?? 0)
        : (int) $nivel;

      if ($nvaId <= 0) {
        continue;
      }

      $param = "nva_id_{$index}";
      $placeholders[] = ":{$param}";
      $params[$param] = (int) $nvaId;
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

  private function getFiltroConteudo(string $conteudo, array &$params)
  {
    $conteudo = trim($conteudo);

    if ($conteudo === '') {
      return '';
    }

    $conteudoTexto = mb_strtolower($conteudo, 'UTF-8');

    $params['conteudo_org_nomefantasia'] = '%' . $conteudoTexto . '%';
    $params['conteudo_org_razaosocial'] = '%' . $conteudoTexto . '%';
    $params['conteudo_emp_nomefantasia'] = '%' . $conteudoTexto . '%';
    $params['conteudo_emp_razaosocial'] = '%' . $conteudoTexto . '%';

    $whereDocumento = '';

    if (preg_match('/^[0-9.\-\/\s]+$/', $conteudo)) {
      $conteudoDocumento = preg_replace('/\D/', '', $conteudo);

      if ($conteudoDocumento !== '') {
        $params['conteudo_org_cnpjcpf'] = '%' . $conteudoDocumento . '%';
        $params['conteudo_emp_cnpjcpf'] = '%' . $conteudoDocumento . '%';

        $whereDocumento = "
          OR org.org_cnpjcpf LIKE :conteudo_org_cnpjcpf
          OR emp.emp_cnpjcpf LIKE :conteudo_emp_cnpjcpf
        ";
      }
    }

    return "
      AND (
        LOWER(org.org_nomefantasia) LIKE :conteudo_org_nomefantasia
        OR LOWER(org.org_razaosocial) LIKE :conteudo_org_razaosocial
        OR LOWER(emp.emp_nomefantasia) LIKE :conteudo_emp_nomefantasia
        OR LOWER(emp.emp_razaosocial) LIKE :conteudo_emp_razaosocial
        {$whereDocumento}
      )
    ";
  }

  private function getContextosProjeto(string $conteudo = '')
  {
    $organizacaoModel = new SimpleModel('organizacao', 'org');
    $empresaModel = new SimpleModel('empresa', 'emp');

    $params = [];
    $whereDedicado = '';

    if (defined('CONF_ORG_ID') && trim((string) CONF_ORG_ID) !== '') {
      $whereDedicado = 'AND org.org_id = :org_id';
      $params['org_id'] = (int) CONF_ORG_ID;
    }

    $whereConteudo = $this->getFiltroConteudo($conteudo, $params);

    return $empresaModel->rawQuery(
      "SELECT
        org.org_id,
        org.org_status,
        org.org_statusclass,
        org.org_idusuarioresponsavel,
        org.org_cnpjcpf,
        org.org_razaosocial,
        org.org_nomefantasia,
        org.org_ambiente,
        org.org_url_dedicada,
        org.org_banco_dedicado,
        org.org_statusambiente,
        org.org_logo,
        org.org_favicon,
        org.org_planodefundo,
        org.org_cadastrobasico_preferencia,

        emp.emp_id,
        emp.emp_idorganizacao,
        emp.emp_tipo,
        emp.emp_status,
        emp.emp_statusclass,
        emp.emp_cnpjcpf,
        emp.emp_razaosocial,
        emp.emp_nomefantasia,
        emp.emp_observacao,
        emp.emp_logo,
        emp.emp_planodefundo,
        emp.emp_cadastrobasico_preferencia

       FROM {$organizacaoModel->getTable()} org
       INNER JOIN {$empresaModel->getTable()} emp ON emp.emp_idorganizacao = org.org_id
       WHERE org.org_status = 'Ativo'
       AND org.org_statusambiente = 'Ativo'
       AND emp.emp_status = 'Ativo'
       {$whereDedicado}
       {$whereConteudo}
       ORDER BY org.org_nomefantasia ASC, emp.emp_tipo ASC, emp.emp_nomefantasia ASC
       LIMIT 10",
      $params
    );
  }

  private function getContextosOrganizacao(string $conteudo = '')
  {
    $orgId = (int) (
      $this->contexto->organizacao->org_id
      ?? $this->usuarioLogado->organizacao->org_id
      ?? $this->usuarioLogado->usu_idorganizacao
      ?? 0
    );

    if ($orgId <= 0) {
      return (new SimpleResponse("Erro", "Usuário sem organização definida"))
        ->erro()
        ->noDestroy();
    }

    $organizacaoModel = new SimpleModel('organizacao', 'org');
    $empresaModel = new SimpleModel('empresa', 'emp');
    $usuarioOrganizacaoModel = new SimpleModel('usuario_organizacao', 'uor');
    $usuarioNivelModel = new SimpleModel('usuario_nivelacesso', 'una');
    $nivelModel = new SimpleModel('nivelacesso', 'nva');

    $params = [
      'usu_id' => (int) ($this->usuarioLogado->usu_id ?? 0)
    ];

    $whereConteudo = $this->getFiltroConteudo($conteudo, $params);

    return $empresaModel->rawQuery(
      "SELECT
        org.org_id,
        org.org_status,
        org.org_statusclass,
        org.org_idusuarioresponsavel,
        org.org_cnpjcpf,
        org.org_razaosocial,
        org.org_nomefantasia,
        org.org_ambiente,
        org.org_url_dedicada,
        org.org_banco_dedicado,
        org.org_statusambiente,
        org.org_logo,
        org.org_favicon,
        org.org_planodefundo,
        org.org_cadastrobasico_preferencia,

        emp.emp_id,
        emp.emp_idorganizacao,
        emp.emp_tipo,
        emp.emp_status,
        emp.emp_statusclass,
        emp.emp_cnpjcpf,
        emp.emp_razaosocial,
        emp.emp_nomefantasia,
        emp.emp_observacao,
        emp.emp_logo,
        emp.emp_planodefundo,
        emp.emp_cadastrobasico_preferencia

       FROM {$organizacaoModel->getTable()} org
       INNER JOIN {$empresaModel->getTable()} emp ON emp.emp_idorganizacao = org.org_id
       INNER JOIN {$usuarioOrganizacaoModel->getTable()} uor
         ON uor.uor_idorganizacao = org.org_id
         AND uor.uor_idusuario = :usu_id
         AND uor.uor_status = 'Ativo'
       WHERE EXISTS (
         SELECT 1
         FROM {$usuarioNivelModel->getTable()} una
         INNER JOIN {$nivelModel->getTable()} nva ON nva.nva_id = una.una_idnivelacesso
         WHERE una.una_idusuario = uor.uor_idusuario
         AND una.una_idorganizacao = org.org_id
         AND una.una_idempresa IS NULL
         AND una.una_status = 'Ativo'
         AND nva.nva_escopo = 'Organizacao'
         AND nva.nva_status = 'Ativo'
       )
       AND org.org_status = 'Ativo'
       AND org.org_statusambiente = 'Ativo'
       AND emp.emp_status = 'Ativo'
       {$whereConteudo}
       ORDER BY emp.emp_tipo ASC, emp.emp_nomefantasia ASC
       LIMIT 10",
      $params
    );
  }

  private function getContextosEmpresa(string $conteudo = '')
  {
    $usuId = (int) ($this->usuarioLogado->usu_id ?? 0);

    if ($usuId <= 0) {
      return (new SimpleResponse("Erro", "Usuário não identificado"))
        ->erro()
        ->noDestroy();
    }

    $usuarioEmpresaModel = new SimpleModel('usuario_empresa', 'uem');
    $organizacaoModel = new SimpleModel('organizacao', 'org');
    $empresaModel = new SimpleModel('empresa', 'emp');

    $params = [
      'usu_id' => $usuId
    ];

    $whereConteudo = $this->getFiltroConteudo($conteudo, $params);

    return $usuarioEmpresaModel->rawQuery(
      "SELECT
        org.org_id,
        org.org_status,
        org.org_statusclass,
        org.org_idusuarioresponsavel,
        org.org_cnpjcpf,
        org.org_razaosocial,
        org.org_nomefantasia,
        org.org_ambiente,
        org.org_url_dedicada,
        org.org_banco_dedicado,
        org.org_statusambiente,
        org.org_logo,
        org.org_favicon,
        org.org_planodefundo,
        org.org_cadastrobasico_preferencia,

        emp.emp_id,
        emp.emp_idorganizacao,
        emp.emp_tipo,
        emp.emp_status,
        emp.emp_statusclass,
        emp.emp_cnpjcpf,
        emp.emp_razaosocial,
        emp.emp_nomefantasia,
        emp.emp_observacao,
        emp.emp_logo,
        emp.emp_planodefundo,
        emp.emp_cadastrobasico_preferencia

       FROM {$usuarioEmpresaModel->getTable()} uem
       INNER JOIN {$empresaModel->getTable()} emp ON emp.emp_id = uem.uem_idempresa
       INNER JOIN {$organizacaoModel->getTable()} org ON org.org_id = emp.emp_idorganizacao
       WHERE uem.uem_idusuario = :usu_id
       AND uem.uem_status = 'Ativo'
       AND org.org_status = 'Ativo'
       AND org.org_statusambiente = 'Ativo'
       AND emp.emp_status = 'Ativo'
       {$whereConteudo}
       ORDER BY uem.uem_principal DESC, org.org_nomefantasia ASC, emp.emp_nomefantasia ASC
       LIMIT 10",
      $params
    );
  }
}
