<?php

namespace Source\Controller\sistema;

use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Model\ContatoModel;

class ContatoController extends SimpleController
{
  private ContatoModel $contatoModel;

  public function __construct()
  {
    parent::__construct();

    $this->contatoModel = new ContatoModel();
  }

  public function getContato($url = null): void
  {
    $this->validarPermissoes(['contato.visualizar']);

    $id = 0;

    if (is_array($url)) {
      $id = (int) ($url['id'] ?? 0);
    } elseif (!is_null($url)) {
      $id = (int) $url;
    }

    if ($id <= 0) {
      echo json_encode(
        (new SimpleResponse("Atenção", "Contato não informado."))
          ->warning()
          ->noDestroy()
      );
      exit;
    }

    $getContato = $this->contatoModel->getContatoById($id);

    if (!$getContato->status || $getContato->total != 1) {
      echo json_encode(
        (new SimpleResponse("Atenção", "Contato não encontrado."))
          ->warning()
          ->noDestroy()
      );
      exit;
    }

    echo json_encode($getContato->data[0]);
    exit;
  }

  public function salvarContato(): void
  {
    if ($_POST['ctt_id'] > 0) {
      $this->validarPermissoes(['contato.atualizar']);
    } else {
      $this->validarPermissoes(['contato.criar']);
    }

    echo json_encode($this->contatoModel->salvarContato($_POST));
    exit;
  }

  public function deleteContato($url = null): void
  {
    $this->validarPermissoes(['contato.excluir']);

    $id = 0;

    if (is_array($url)) {
      $id = (int) ($url['id'] ?? 0);
    } elseif (!is_null($url)) {
      $id = (int) $url;
    }

    if ($id <= 0) {
      echo json_encode(
        (new SimpleResponse("Atenção", "Contato não informado."))
          ->warning()
          ->noDestroy()
      );
      exit;
    }

    echo json_encode($this->contatoModel->deleteContato($id, $_POST));
    exit;
  }
}