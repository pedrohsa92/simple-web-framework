<?php

namespace Source\Controller\Sistema;

use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Model\MensagemModel;

class MensagemController extends SimpleController
{
  public function __construct()
  {
    parent::__construct();

    $this->setFramework();

    $this->setAssetsCss('phsa.css');
    $this->setAssetsCss('sistema.css');
    $this->setAssetsCss('global.css');
    $this->setAssetsJs('sistema.js'); 
    $this->setAssetsJs('simplemodal.js'); 
    $this->setAssetsJs('global.js');
  }

  public function list($url=null)
  {
    $this->validarUsuarioLogado();
    // $this->validarPermissoes(['visualizar_mensagem']);

    $mensagemModel = new MensagemModel($url['modulo']);

    $this->setMetaTags([
      'title' => 'Pesquisa de mensagens',
      'description' => 'Página para pesquisar mensagens'
    ]);

    $this->render('sistema/mensagem/list', [
      'origem' => $mensagemModel->origem
    ]);
  }
}