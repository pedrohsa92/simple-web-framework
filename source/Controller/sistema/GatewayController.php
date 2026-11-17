<?php

namespace Source\Controller\Sistema;

use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Model\InfinitePayModel;
use Source\Model\MercadoPagoModel;
use Source\Model\PagarmeModel;
use Source\Model\ProjetoModel;

class GatewayController extends SimpleController
{
  public function __construct()
  {
    parent::__construct();
  }

  public function consultar(): void
  {
    $this->validarPermissoes(['projeto.visualizar']);

    $getProjeto = (new ProjetoModel())->getProjeto();

    if (!$getProjeto->status || $getProjeto->total !== 1) {
      echo json_encode(
        (new SimpleResponse('Projeto não encontrado', 'Não foi possível localizar o projeto para consultar o gateway.'))
          ->erro()
          ->noDestroy()
      );
      exit;
    }

    $projeto = $getProjeto->data[0];
    $gateway = trim((string) ($projeto->prj_gateway ?? ''));

    if ($gateway === 'Pagar.me') {
      $response = (new PagarmeModel($projeto))->consultar();
    } elseif ($gateway === 'MercadoPago') {
      new MercadoPagoModel($projeto);
      $response = (new SimpleResponse('Gateway não configurado', 'O gateway Mercado Pago ainda não possui configuração implementada no painel.'))
        ->warning()
        ->noDestroy();
    } elseif ($gateway === 'InfinitePay') {
      new InfinitePayModel($projeto);
      $response = (new SimpleResponse('Gateway não configurado', 'O gateway Infinite Pay ainda não possui configuração implementada no painel.'))
        ->warning()
        ->noDestroy();
    } else {
      $response = (new SimpleResponse('Gateway não configurado', 'Selecione um gateway válido para realizar a consulta.'))
        ->warning()
        ->noDestroy();
    }

    echo json_encode($response);
    exit;
  }
}
