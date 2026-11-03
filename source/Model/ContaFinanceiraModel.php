<?php

namespace Source\Model;

use Source\Core\SimpleModel;

class ContaFinanceiraModel extends SimpleModel
{
  protected string $tabela = 'conta_financeira';
  protected string $prefixo = 'ctf';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;
}
