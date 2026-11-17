<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Model\NivelAcessoModel;
use Source\Model\PermissaoModel;

class NivelAcessoController extends SimpleController
{
  private NivelAcessoModel $nivelAcessoModel;

  public function __construct()
  {
    parent::__construct();

    $this->validarUsuarioLogado();

    $this->nivelAcessoModel = new NivelAcessoModel();

    $this->setFramework();

    $this->setAssetsCss([
      'phsa.css',
      'sistema.css',
      'global.css'
    ]);

    $this->setAssetsJs([
      'sistema.js',
      'simplemodal.js',
      'global.js'
    ]);
  }

  public function list(): void
  {
    $this->validarPermissoes(['nivelacesso.visualizar']);

    $nivelAcessoModel = new NivelAcessoModel();
    $getTotal = $nivelAcessoModel->count('nva_id');

    $this->setBreadcrumb([
      [
        'title' => 'Níveis de acesso',
        'label' => 'Níveis de acesso',
        'url' => Helpers::url('nivelacesso/list')
      ]
    ]);

    $this->render('sistema/nivelacesso/list', [
      'getInfo' => (object) [
        'status' => true,
        'data' => [],
        'total' => $getTotal->status
          ? (int) ($getTotal->data[0]->total ?? 0)
          : 0
      ],
      'titulo' => 'Pesquisa de níveis de acesso',
      'descricao' => 'Gerencie permissões e acessos',
      'urlCadastrar' => Helpers::pode('nivelacesso.criar') ? Helpers::url('nivelacesso/form') : '',
      'urlPesquisar' => Helpers::url('nivelacesso/pesquisarNivelAcesso'),
      'ocultarCadastrar' => !Helpers::pode('nivelacesso.criar'),
      'labelPesquisaSimples' => 'Pesquisar por nome, descrição ou escopo',
      'labelTotal' => 'nível(is)',
      'nomeArquivo' => 'pesquisaDeNivelAcesso',
      'prefixo' => 'nva'
    ]);
  }

  public function pesquisarNivelAcesso(): void
  {
    $this->validarPermissoes(['nivelacesso.visualizar']);

    $response = (new NivelAcessoModel())->pesquisarNivelAcesso($_POST);

    $response->json();
    exit;
  }

  public function form(array $url): void
  {
    if ($url['id'] > 0) {
      $this->validarPermissoes([
        'nivelacesso.visualizar',
        'nivelacesso.atualizar'
      ]);
    } else {
      $this->validarPermissoes([
        'nivelacesso.visualizar',
        'nivelacesso.criar'
      ]);
    }

    $nivelAcesso = null;
    $permissoesNivel = [];

    if ($url['id'] > 0) {
      $getNivelAcesso = $this->nivelAcessoModel->getNivelAcessoById($url['id']);

      if (!$getNivelAcesso->status || $getNivelAcesso->total == 0) {
        $this->render('template/sistema/erro', [
          'erro' => (object) [
            'status' => false,
            'title' => 'Nível de acesso não encontrado',
            'text' => 'O nível de acesso informado não foi localizado ou não pertence ao contexto atual.'
          ]
        ]);
        return;
      }

      $nivelAcesso = $getNivelAcesso->data[0];
      $permissoesNivel = (new PermissaoModel())->getArrayByNivel($url['id']);
    }

    $this->setBreadcrumb([
      [
        'title' => 'Níveis de acesso',
        'label' => 'Níveis de acesso',
        'url' => Helpers::url('nivelacesso/list'),
        'active' => false
      ],
      [
        'title' => $nivelAcesso->nva_id > 0 ? 'Editando '.$nivelAcesso->nva_nome : 'Novo nível de acesso',
        'label' => $nivelAcesso->nva_id > 0 ? $nivelAcesso->nva_nome : 'Novo',
        'url' => '#'
      ]
    ]);

    $this->render('sistema/nivelacesso/form', [
      'contexto' => $this->contexto,
      'nivelAcesso' => $nivelAcesso,
      'permissoesNivel' => $permissoesNivel,
      'escoposPermitidos' => $this->nivelAcessoModel->getEscoposPermitidosParaContexto(),
      'escopoPadrao' => $this->nivelAcessoModel->getEscopoPadraoParaNovoNivel(),
      'permissoesSistema' => $this->nivelAcessoModel->getPermissaoSistema($nivelAcesso->nva_escopo ?? null),
      'mapaPermissoesEscopo' => $this->nivelAcessoModel->getMapaPermissoesEscopo()
    ]);
  }

  public function salvarNivelAcesso(): void
  {
    if ($_POST['nva_id'] > 0) {
      $this->validarPermissoes(['nivelacesso.atualizar']);
    } else {
      $this->validarPermissoes(['nivelacesso.criar']);
    }

    echo json_encode($this->nivelAcessoModel->salvarNivelAcesso($_POST));
    exit;
  }
}
