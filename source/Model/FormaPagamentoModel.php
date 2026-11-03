<?php

namespace Source\Model;

use Source\Core\SimpleModel;

class FormaPagamentoModel extends SimpleModel
{
  protected string $tabela = 'forma_pagamento';
  protected string $prefixo = 'fpg';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;
}
