<?php

namespace Source\Controller\sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Model\EnderecoModel;

class EnderecoController extends SimpleController
{
  private EnderecoModel $enderecoModel;

  public function __construct()
  {
    parent::__construct();

    $this->enderecoModel = new EnderecoModel();
  }

  public function getEndereco($url = null): void
  {
    $this->validarPermissoes(['endereco.visualizar']);

    $id = 0;

    if (is_array($url)) {
      $id = (int) ($url['id'] ?? 0);
    } elseif (!is_null($url)) {
      $id = (int) $url;
    }

    if ($id <= 0) {
      echo json_encode(
        (new SimpleResponse("Atenção", "Endereço não informado."))
          ->warning()
          ->noDestroy()
      );
      exit;
    }

    $getEndereco = $this->enderecoModel->getEnderecoById($id);

    if (!$getEndereco->status || $getEndereco->total != 1) {
      echo json_encode(
        (new SimpleResponse("Atenção", "Endereço não encontrado."))
          ->warning()
          ->noDestroy()
      );
      exit;
    }

    echo json_encode($getEndereco->data[0]);
    exit;
  }

  public function salvarEndereco(): void
  {
    if ($_POST['end_id'] > 0) {
      $this->validarPermissoes(['endereco.atualizar']);
    } else {
      $this->validarPermissoes(['endereco.criar']);
    }

    echo json_encode($this->enderecoModel->salvarEndereco($_POST));
    exit;
  }

  public function deleteEndereco($url = null): void
  {
    $this->validarPermissoes(['endereco.excluir']);

    $id = 0;

    if (is_array($url)) {
      $id = (int) ($url['id'] ?? 0);
    } elseif (!is_null($url)) {
      $id = (int) $url;
    }

    if ($id <= 0) {
      echo json_encode(
        (new SimpleResponse("Atenção", "Endereço não informado."))
          ->warning()
          ->noDestroy()
      );
      exit;
    }

    echo json_encode($this->enderecoModel->deleteEndereco($id, $_POST));
    exit;
  }
}