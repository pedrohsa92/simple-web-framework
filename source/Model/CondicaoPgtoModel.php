<?php

namespace Source\Model;

use Source\Core\SimpleModel;

class CondicaoPgtoModel extends SimpleModel
{
  protected string $tabela = 'condicao_pgto';
  protected string $prefixo = 'cpg';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;
}
