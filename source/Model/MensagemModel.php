<?php

namespace Source\Model;

use Source\Core\SimpleModel;
use Source\Core\SimpleSession;
use Source\Core\SimpleResponse;

class MensagemModel extends SimpleModel
{
  protected $table = 'mensagem';
  protected $prefix = 'msg';
  public $modulo;
  public $origem;

  public function __construct($modulo)
  {
    parent::__construct($this->table, $this->prefix, 'id');

    $modulosPermitidos = ['usuario', 'cliente', 'empresa'];
    if (!in_array($modulo, $modulosPermitidos)) {
      $response = new SimpleResponse("Erro", "Módulo inválido: {$modulo}");
      $response->erro()->page();
      exit;
    }

    $this->modulo = $modulo;
    $this->getOrigem();
  }

  private function getOrigem()
  {
    switch ( $this->modulo ) {
    	case 'usuario':
    		$this->origem = (new SimpleSession())->get('USUARIO');
    		$this->origem->cnpjcpf = $this->origem->usu_cnpjcpf;
    		$this->origem->razaosocial = $this->origem->usu_razaosocial;
    		$this->origem->nomefantasia = $this->origem->usu_nomefantasia;
    		$this->origem->logo = $this->origem->usu_logo;
    		$this->origem->planodefundo = $this->origem->usu_planodefundo;
  		break;
    	
    	default:
    		$response = new SimpleResponse("Erro", "Módulo inválido: {$modulo}");
	      $response->erro()->page();
	      exit;
  		break;
    }
  }
}