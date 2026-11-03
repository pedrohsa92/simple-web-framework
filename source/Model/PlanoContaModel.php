<?php

namespace Source\Model;

use Source\Core\SimpleModel;

class PlanoContaModel extends SimpleModel
{
  protected string $tabela = 'plano_conta';
  protected string $prefixo = 'pct';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;
}
