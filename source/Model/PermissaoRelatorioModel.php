<?php

namespace Source\Model;

use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;

class PermissaoRelatorioModel extends SimpleModel
{
  protected string $tabela = 'permissaorelatorio';
  protected string $prefixo = 'pmr';
  protected string $identificador = 'id';
  protected string $campoEmpresa = 'pmr_idempresa';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function salvarPermissaoRelatorio(array $data): object
  {
    $data = $this->filtrarCampos($data);

    if (!empty($data['pmr_id'])) {
      return $this->updatePermissaoRelatorio($data);
    }

    return $this->insertPermissaoRelatorio($data);
  }

  public function insertPermissaoRelatorio(array $data): object
  {
    $data = $this->filtrarCampos($data);

    $validacao = $this->validarPermissaoRelatorio($data);

    if (!$validacao->status) {
      return $validacao;
    }

    return $this->insert($data, "Permissão de relatório inserida com sucesso||Erro ao inserir permissão de relatório");
  }

  public function updatePermissaoRelatorio(array $data): object
  {
    $data = $this->filtrarCampos($data);

    if (empty($data['pmr_id'])) {
      return (new SimpleResponse("Erro", "Erro ao identificar a permissão de relatório"))
        ->erro()
        ->setArrayParams(['campo' => 'pmr_id'])
        ->noDestroy();
    }

    $pmrId = (int) $data['pmr_id'];
    unset($data['pmr_id']);

    $validacao = $this->validarPermissaoRelatorio($data, false);

    if (!$validacao->status) {
      return $validacao;
    }

    return $this->update($data, ['pmr_id' => $pmrId], "Permissão de relatório atualizada com sucesso||Erro ao atualizar permissão de relatório");
  }

  public function getByNivel(int $nvaId): object
  {
    return $this
      ->where('pmr_idnivelacesso', $nvaId)
      ->where('pmr_status', 'Ativo')
      ->orderBy('pmr_nome')
      ->get();
  }

  public function getArrayByNivel(int $nvaId): array
  {
    $getRelatorios = $this->getByNivel($nvaId);

    if (!$getRelatorios->status || $getRelatorios->total == 0) {
      return [];
    }

    $relatorios = [];

    foreach ($getRelatorios->data as $relatorio) {
      $relatorios[$relatorio->pmr_dir] = 1;
    }

    return $relatorios;
  }

  public function getArrayByNiveis(array $niveis): array
  {
    $relatorios = [];

    foreach ($niveis as $nvaId) {
      $relatorios = array_merge($relatorios, $this->getArrayByNivel((int) $nvaId));
    }

    return $relatorios;
  }

  private function validarPermissaoRelatorio(array $data, bool $validarObrigatorios = true): object
  {
    if ($validarObrigatorios) {
      $required = [
        'pmr_idnivelacesso',
        'pmr_dir',
        'pmr_nome'
      ];

      foreach ($required as $field) {
        if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
          return (new SimpleResponse("Erro", "O campo {$field} é obrigatório"))
            ->erro()
            ->setArrayParams(['campo' => $field])
            ->noDestroy();
        }
      }
    }

    return (new SimpleResponse("Validação", "Dados válidos"))
      ->sucesso()
      ->noDestroy();
  }
}