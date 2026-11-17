<?php

namespace Source\Model;

use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;

class PermissaoModel extends SimpleModel
{
  protected string $tabela = 'permissao';
  protected string $prefixo = 'per';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getArrayByNivel(int $nvaId): array
  {
    $getPermissoes = $this
      ->where('per_idnivelacesso', $nvaId)
      ->where('per_status', 'Ativo')
      ->orderBy('per_nome', 'ASC')
      ->get('Permissões encontradas com sucesso||Erro ao buscar permissões');

    if (!$getPermissoes->status || $getPermissoes->total <= 0) {
      return [];
    }

    $retorno = [];

    foreach ($getPermissoes->data as $permissao) {
      $retorno[$permissao->per_nome] = $permissao;
    }

    return $retorno;
  }
}