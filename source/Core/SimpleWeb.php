<?php

namespace Source\Core;

use Source\Core\SimpleResponse;
use Source\Core\SimpleSession;
use Source\Model\ProjetoModel;
use Source\Model\PlanoContratadoModel;
use Source\Model\UsuarioModel;
use Source\Model\EmpresaModel;

class SimpleWeb
{
  private SimpleSession $session;

  public function __construct()
  {
    $this->session = new SimpleSession();

    $this->carregarProjeto();
    $this->atualizarUsuarioLogado();
    $this->carregarContexto();
  }

  private function carregarProjeto(): void
  {
    $projetoModel = new ProjetoModel();
    $getProjeto = $projetoModel->getProjeto();

    if (!$getProjeto->status) {
      $this->erroConfiguracao("Erro ao buscar dados do projeto: " . $getProjeto->errorInfo);
    }

    if ($getProjeto->total != 1) {
      $this->erroConfiguracao("Projeto não configurado corretamente.");
    }

    $this->session->set('PROJETO', $getProjeto->data[0]);
  }

  private function atualizarUsuarioLogado(): void
  {
    $usuario = $this->session->get('USUARIO');

    if (is_null($usuario) || empty($usuario->usu_id)) {
      return;
    }

    $usuarioAtualizado = ( new UsuarioModel() )->atualizarUsuarioLogado();

    if (is_null($usuarioAtualizado)) {
      $this->session->remove('USUARIO');
      $this->session->remove('CONTEXTO');
    }
  }

  private function carregarContexto(): void
  {
    if ($this->usarContextoDedicado()) {
      $this->definirContextoDedicado();
      return;
    }

    $usuario = $this->session->get('USUARIO');

    if (is_null($usuario) || empty($usuario->usu_id)) {
      $this->session->remove('CONTEXTO');
      return;
    }

    $contexto = $this->session->get('CONTEXTO');

    if ($this->contextoValido($contexto) && ($contexto->origem ?? '') === 'selecionado') {
      $this->atualizarPlanoDoContexto($contexto);
      return;
    }

    $this->definirContextoPrincipal($usuario);
  }

  private function definirContextoDedicado(): void
  {
    $orgId = (int) CONF_ORG_ID;

    if ($orgId <= 0) {
      $this->erroConfiguracao("CONF_ORG_ID inválido para ambiente dedicado.");
    }

    $organizacaoModel = new SimpleModel('organizacao', 'org');

    $getOrganizacao = $organizacaoModel->rawQuery(
      "SELECT *
       FROM {$organizacaoModel->getTable()}
       WHERE org_id = :org_id
       AND org_status = 'Ativo'
       LIMIT 1",
      [
        'org_id' => $orgId
      ],
      true,
      "Organização encontrada com sucesso||Erro ao buscar organização dedicada"
    );

    if (!$getOrganizacao->status) {
      $this->erroConfiguracao("Erro ao buscar organização dedicada: " . $getOrganizacao->errorInfo);
    }

    if ($getOrganizacao->total != 1) {
      $this->erroConfiguracao("Organização dedicada não encontrada ou inativa.");
    }

    $getEmpresa = (new EmpresaModel())->getEmpresaPrincipalAtivaPorOrganizacao($orgId);

    if (!$getEmpresa->status) {
      $this->erroConfiguracao("Erro ao buscar empresa dedicada: " . $getEmpresa->errorInfo);
    }

    if ($getEmpresa->total != 1) {
      $this->erroConfiguracao("Empresa dedicada não encontrada ou inativa.");
    }

    $contexto = (object) [
      'origem' => 'dedicado',
      'organizacao' => $getOrganizacao->data[0],
      'empresa' => $getEmpresa->data[0],
      'plano' => null
    ];

    $this->atualizarPlanoDoContexto($contexto);
  }

  private function definirContextoPrincipal(object $usuario, string $origem = 'principal'): void
  {
    if (empty($usuario->organizacao) || empty($usuario->empresa)) {
      $this->erroConfiguracao("Usuário logado sem organização ou empresa definida.");
    }

    $contexto = (object) [
      'origem' => $origem,
      'organizacao' => $usuario->organizacao,
      'empresa' => $usuario->empresa,
      'plano' => null
    ];

    $this->atualizarPlanoDoContexto($contexto);
  }

  private function atualizarPlanoDoContexto(object $contexto): void
  {
    $orgId = (int) ($contexto->organizacao->org_id ?? 0);

    if ($orgId <= 0) {
      $contexto->plano = null;
      $this->session->set('CONTEXTO', $contexto);
      return;
    }

    $contexto->plano = $this->buscarPlanoVigente($orgId);
    $this->session->set('CONTEXTO', $contexto);
  }

  private function buscarPlanoVigente(int $orgId): ?object
  {
    $planoContratadoModel = new PlanoContratadoModel();
    $getPlano = $planoContratadoModel->getPlanoVigentePorOrganizacao($orgId);

    if (!$getPlano->status) {
      $this->erroConfiguracao("Erro ao buscar plano vigente: " . $getPlano->errorInfo);
    }

    if ($getPlano->total > 1) {
      $this->erroConfiguracao("Erro grave de integridade contratual: organização com mais de um plano vigente.");
    }

    if ($getPlano->total == 0) {
      return null;
    }

    return $getPlano->data[0];
  }

  private function contextoValido($contexto): bool
  {
    if (!is_object($contexto)) {
      return false;
    }

    if (empty($contexto->origem)) {
      return false;
    }

    if (empty($contexto->organizacao->org_id)) {
      return false;
    }

    if (empty($contexto->empresa->emp_id)) {
      return false;
    }

    return true;
  }

  private function usarContextoDedicado(): bool
  {
    return defined('CONF_ORG_ID') && trim((string) CONF_ORG_ID) !== '';
  }

  private function erroConfiguracao(string $message): void
  {
    $response = new SimpleResponse("Erro de Configuração", $message);
    $response->erro()->html();
    exit;
  }
}