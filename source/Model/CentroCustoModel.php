<?php

namespace Source\Model;

use Source\Core\SimpleModel;

class CentroCustoModel extends SimpleModel
{
  protected string $tabela = 'centro_custo';
  protected string $prefixo = 'cdc';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;
}
