<?php

namespace Source\Controller\Sistema;

use Source\Core\SimpleController;
use Source\Model\EmpresaModel;
use Source\Model\PwaModel;

class PwaController extends SimpleController
{
  private PwaModel $pwaModel;

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

    $this->pwaModel = new PwaModel();
  }

  public function salvar(): void
  {
    $this->validarPermissoes(['empresa.atualizar']);

    $empresaId = (int) ($_POST['pwa_idempresa'] ?? 0);
    $getEmpresa = (new EmpresaModel())->getEmpresaById($empresaId, true);

    if (!$getEmpresa->status || empty($getEmpresa->data)) {
      (new \Source\Core\SimpleResponse('Empresa não encontrada', 'A empresa do PWA não pertence ao contexto atual.'))
        ->warning()
        ->json();
      exit;
    }

    $empresa = $getEmpresa->data[0];
    $resultado = $this->pwaModel->salvarPwa(
      $_POST,
      (int) $empresa->emp_idorganizacao,
      (int) $empresa->emp_id
    );

    $resultado->json();
    exit;
  }
}
