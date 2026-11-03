<?php

namespace Source\Controller\sistema;

use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Model\ArquivoModel;

class ArquivoController extends SimpleController
{
  private ArquivoModel $arquivoModel;

  public function __construct()
  {
    parent::__construct();

    $this->arquivoModel = new ArquivoModel();
  }

  public function getArquivo($url = null): void
  {
    $this->validarPermissoes(['arquivo.visualizar']);

    $id = 0;

    if (is_array($url)) {
      $id = (int) ($url['id'] ?? 0);
    } elseif (!is_null($url)) {
      $id = (int) $url;
    }

    if ($id <= 0) {
      echo json_encode(
        (new SimpleResponse("Atenção", "Arquivo não informado."))
          ->warning()
          ->noDestroy()
      );
      exit;
    }

    $getArquivo = $this->arquivoModel->getArquivoById($id);

    if (!$getArquivo->status || $getArquivo->total != 1) {
      echo json_encode(
        (new SimpleResponse("Atenção", "Arquivo não encontrado."))
          ->warning()
          ->noDestroy()
      );
      exit;
    }

    echo json_encode($getArquivo->data[0]);
    exit;
  }

  public function salvarArquivo(): void
  {
    if ($_POST['arq_id'] > 0) {
      $this->validarPermissoes(['arquivo.atualizar']);
    } else {
      $this->validarPermissoes(['arquivo.criar']);
    }

    echo json_encode($this->arquivoModel->salvarArquivo($_POST));
    exit;
  }

  public function deleteArquivo($url = null): void
  {
    $this->validarPermissoes(['arquivo.excluir']);

    $id = 0;

    if (is_array($url)) {
      $id = (int) ($url['id'] ?? 0);
    } elseif (!is_null($url)) {
      $id = (int) $url;
    }

    if ($id <= 0) {
      echo json_encode(
        (new SimpleResponse("Atenção", "Arquivo não informado."))
          ->warning()
          ->noDestroy()
      );
      exit;
    }

    echo json_encode($this->arquivoModel->deleteArquivo($id, $_POST));
    exit;
  }
}