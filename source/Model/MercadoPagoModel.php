<?php

namespace Source\Model;

class MercadoPagoModel
{
  private object $projeto;

  public function __construct(object $projeto)
  {
    $this->projeto = $projeto;
  }
}
