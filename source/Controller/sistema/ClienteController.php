<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Core\SimpleSession;
use Source\Model\EnderecoModel;
use Source\Model\ClienteModel;
use Source\Model\TipoContatoModel;

class ClienteController extends SimpleController
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

  public function list()
  {
    $this->validarPermissoes(['visualizar_cliente']);

    $clienteModel = new ClienteModel();
    $getCliente = $clienteModel->mySelect();

    $this->setBreadcrumb([
      [
        'title' => 'Pesquisar cliente',
        'label' => 'Cliente',
        'url' => Helpers::url('cliente/list')
      ]
    ]);

    $this->setMetaTags([
      'title' => 'Pesquisa de clientes',
      'description' => 'Página para pesquisar clientes'
    ]);

    $this->render('sistema/cliente/list', [
      'getCliente' => $getCliente
    ]);
  }

  public function form($url = null)
  {
    $this->validarPermissoes(['criar_cliente', 'atualizar_cliente']);

    $this->setTabLink($url['tabLink']);
    
    $this->validarUsuarioLogado();

    $cliente = null;
    $getTipoContato = null;

    $breadcrumb = [
      [
        'title' => 'Pesquisar cliente',
        'label' => 'Cliente',
        'url' => Helpers::url('cliente/list')
      ]
    ];

    if ( is_null($url['id']) ) {
      $breadcrumb[] = [
        'title' => 'Cadastrar cliente',
        'label' => 'Cadastrar',
        'url' => Helpers::url('cliente/list')
      ];

      $this->setMetaTags([
        'title' => 'Novo Cliente',
        'description' => 'Página para cadastrar um novo cliente'
      ]);
    } else {
      $clienteModel = new ClienteModel();
      $getCliente = $clienteModel->getClienteById($url['id']);

      if ( !$getCliente->status ) {
        $response = new SimpleResponse("Erro", $getCliente->errorInfo);
        $response->erro()->page();
        exit;
      }

      if ( $getCliente->total!=1 ) {
        $response = new SimpleResponse("Erro", "Cliente {$url['id']} não encontrado");
        $response->erro()->page();
        exit;
      }

      $cliente = $getCliente->data[0];

      $breadcrumb[] = [
        'title' => "Editar cliente $cliente->cli_id",
        'label' => mb_strimwidth($cliente->cli_razaosocial, 0, 25, '...'),
        'url' => Helpers::url('cliente/list')
      ];

      $this->setMetaTags([
        'title' => "Editar Cliente #{$url['id']}",
        'description' => "Página para editar o cliente de ID {$url['id']}"
      ]);
    }

    $this->setBreadcrumb($breadcrumb);

    $tipoContatoModel = new TipoContatoModel();
    $getTipoContato = $tipoContatoModel->getTipoContatoByStatus(true);

    $this->render('sistema/cliente/form', [
      'cliente' => $cliente,
      'getTipoContato' => $getTipoContato,
    ]);
  }

  public function pesquisarRapida()
  {
    $this->validarUsuarioLogado();
    $this->validarPermissao(['visualizar_cliente']);

    $clienteModel = new ClienteModel();

    if (empty($_POST['conteudo'])) {
      return (new SimpleResponse('Erro', 'Nenhum termo de pesquisa informado!'))->erro()->json();
    }

    $getCliente = $clienteModel->myList(
      urldecode($_POST['conteudo']),
      [
        $clienteModel->prefixedColumn('id'),
        $clienteModel->prefixedColumn('cnpjcpf'),
        $clienteModel->prefixedColumn('razaosocial'),
        $clienteModel->prefixedColumn('nomefantasia')
      ]
    );

    if ($getCliente->total == 1) {
      $getCliente->location = Helpers::url('cliente/form/' . $getCliente->data[0]->cli_id);
    } else {
      if ($getCliente->status) {
        $getCliente->text = 'Nenhum cliente encontrado!';
      }
      $getCliente->status = false;
      $getCliente->icon = 'info';
      $getCliente->location = Helpers::url('cliente/list?conteudo=' . $_POST['conteudo']);
    }

    echo json_encode($getCliente);
    exit;
  }

  public function salvarCliente()
  {
    $this->validarPermissoes(['criar_cliente', 'atualizar_cliente']);
    
    $this->validarUsuarioLogado(true);

    $this->validarPermissao(['criar_cliente', 'atualizar_cliente']);

    $clienteModel = new ClienteModel();

    if (isset($_POST['cli_email'])) {
      if ($_POST['cli_senha'] == "" && is_null($clienteModel->getClienteById($_POST['cli_id'])->data[0]->cli_senha)) {
        $simpleResponse = new SimpleResponse("Atenção");
        return $simpleResponse->warning("A senha na primeira configuração é obrigatória");
      }
      $clienteModel->validarDuplicidade(
        'cli_email', 
        $_POST['cli_email'],
        "O email informado já está cadastrado",
        ['cli_id' => $_POST['cli_id']]
      );
    }

    if (isset($_POST['cli_username'])) {
      $clienteModel->validarDuplicidade(
        'cli_username', 
        $_POST['cli_username'],
        "O username informado já está cadastrado",
        ['cli_id' => $_POST['cli_id']]
      );
    }

    if ( empty($_POST['cli_nomefantasia']) ) {
      $_POST['cli_nomefantasia'] = $_POST['cli_razaosocial'];
    }

    if (isset($_POST['cli_cnpjcpf'])) {
      $_POST['cli_cnpjcpf'] = Helpers::removeCaracteresEspeciais($_POST['cli_cnpjcpf']);

      Helpers::validarCnpjCpf($_POST['cli_cnpjcpf']);
  
      $varDocumento = strlen($_POST['cli_cnpjcpf']) == 11 ? "CPF" : (strlen($_POST['cli_cnpjcpf']) == 14 ? "CNPJ" : "Documento");
  
      $clienteModel->validarDuplicidade(
        'cli_cnpjcpf', 
        $_POST['cli_cnpjcpf'],
        "O $varDocumento informado já está cadastrado",
        ['cli_id' => $_POST['cli_id']]
      );
    }

    echo json_encode( $clienteModel->{empty($_POST['cli_id'])?'insertCliente':'updateCliente'}($_POST) );exit;
  }

  public function pesquisarCliente()
  {
    $postData = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    $clienteModel = new ClienteModel();
    $getCliente = null;

    if (isset($postData['conteudo']) && !empty($postData['conteudo'])) {
      $getCliente = $clienteModel->myList(
        $postData['conteudo'],
        [
          $clienteModel->prefixedColumn('id'),
          $clienteModel->prefixedColumn('cnpjcpf'),
          $clienteModel->prefixedColumn('razaosocial'),
          $clienteModel->prefixedColumn('nomefantasia')
        ]
      );
    } else {
      $stringWhere = '1=1';

      if ( $postData['status']!='' ) {
        $stringWhere .= " AND {$clienteModel->prefixedColumn('status')} = {$postData['status']}";
      }

      if (!empty($postData['campoFiltro']) && !empty($postData['tipoFiltro'])) {
        if ($postData['tipoFiltro'] === 'Exatidao' && !empty($postData['iniFiltro'])) {
          $stringWhere .= " AND DATE({$postData['campoFiltro']}) = '{$postData['iniFiltro']}'";
        } elseif ($postData['tipoFiltro'] === 'Periodo' && !empty($postData['iniFiltro']) && !empty($postData['fimFiltro'])) {
          $stringWhere .= " AND DATE({$postData['campoFiltro']}) BETWEEN '{$postData['iniFiltro']}' AND '{$postData['fimFiltro']}'";
        }
      }

      $orderBy = !empty($postData['campoOrder']) ? $postData['campoOrder'] : $clienteModel->prefixedColumn('id');
      $direction = !empty($postData['orderBy']) ? $postData['orderBy'] : 'ASC';

      $stringWhere .= " ORDER BY $orderBy $direction";

      $getCliente = $clienteModel->mySelect($stringWhere);
    }

    echo json_encode($getCliente);
  }
}
