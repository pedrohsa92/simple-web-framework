<?php

namespace Source\Model;

class InfinitePayModel
{
  private object $projeto;

  public function __construct(object $projeto)
  {
    $this->projeto = $projeto;
  }
}
