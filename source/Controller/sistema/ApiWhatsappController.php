<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Model\ApiWhatsappModel;

class ApiWhatsappController extends SimpleController
{
  public function __construct()
  {
    parent::__construct();

    $this->validarUsuarioLogado();

    $this->setFramework();

    $this->setAssetsCss('phsa.css');
    $this->setAssetsCss('sistema.css');
    $this->setAssetsCss('global.css');
    $this->setAssetsJs('sistema.js'); 
    $this->setAssetsJs('simplemodal.js');
    $this->setAssetsJs('global.js');
  }

  public function gerenciar()
  {
    $this->validarPermissoes(['gerenciar_api_whatsapp']);

    $apiWhatsappModel = new ApiWhatsappModel();
    $getApiwhatsapp = $apiWhatsappModel->mySelect("ORDER BY ".$apiWhatsappModel->prefixedColumn('dtalter')." DESC");

    $this->setBreadcrumb([
      [
        'title' => 'Gerenciar API WhatsApp',
        'label' => 'API WhatsApp',
        'url' => Helpers::url('api_whatsapp/gerenciar')
      ]
    ]);

    $this->setMetaTags([
      'title' => 'Gerenciar API WhatsApp',
      'description' => 'Página para gerenciar API WhatsApp'
    ]);

    $this->render('sistema/api_whatsapp/gerenciar', [
      'getApiwhatsapp' => $getApiwhatsapp
    ]);
  }

  public function salvarApi()
  {
    if ( empty($_POST['wap_nome']) ) {
      echo json_encode([
        'status' => false,
        'text' => 'O nome da conexão é obrigatório.'
      ]);
      exit;
    }

    if ( empty($_POST['wap_token']) ) {
      echo json_encode([
        'status' => false,
        'text' => 'O token de acesso é obrigatório.'
      ]);
      exit;
    }

    $apiWhatsappModel = new ApiWhatsappModel();

    $resultado = $apiWhatsappModel->consumirApiWpp('getWid', trim($_POST['wap_token']));

    if ( !isset($resultado->response) || $resultado->response->error ) {
      ( new SimpleResponse("ATENÇÃO", $resultado->response->message ?? 'Erro desconhecido ao conectar com a API.') )
        ->setArrayParams([
          'resultado' => $resultado
        ])
        ->erro()
        ->json();
    }

    $resultado->data = date('d/m/Y H:i:s');

    $dadosApi = [
      'wap_nome' => trim($_POST['wap_nome']),
      'wap_token' => trim($_POST['wap_token']),
      'wap_status' => trim($_POST['wap_status']),
      'wap_device' => $resultado->response->device->device_name ?? 'Desconhecido',
      'wap_numero' => $resultado->response->response->number,
      'wap_status' => $resultado->response->response->result==200 ? 1:0,
      'wap_statusnome' => $resultado->response->device->status ?? 'Erro',
      'wap_statusclass' => $resultado->response->response->result==200 ? 'success':'danger',
      'wap_dtonline' => date('Y-m-d H:i:s'),
      'wap_log' => json_encode($resultado)
    ];

    if ( empty($_POST['wap_id']) ) {
      $getResultado = $apiWhatsappModel->insertApiWhatsapp($dadosApi);
    } else {
      unset($dadosApi['wap_token']);
      $getResultado = $apiWhatsappModel->updateApiWhatsapp($_POST['wap_id'], $dadosApi);
    }

    $getResultado->resultado = $resultado;

    echo json_encode($getResultado);
    exit;
  }

  public function getApiwhatsapp($url = null)
  {
    if (empty($url['id'])) {
      echo json_encode([
        'status' => false,
        'text' => 'ID da conexão é obrigatório.'
      ]);
      exit;
    }

    $apiWhatsappModel = new ApiWhatsappModel();
    $getApi = $apiWhatsappModel->findById($url['id']);

    if (!$getApi->status || empty($getApi->data)) {
      echo json_encode([
        'status' => false,
        'text' => 'Registro não encontrado.'
      ]);
      exit;
    }

    echo json_encode($getApi->data[0]);
    exit;
  }

  public function pesquisarEnvio()
  {
    $this->validarPermissoes(['consultar_api_whatsapp']);

    $apiWhatsappModel = new ApiWhatsappModel('info');
    $postData = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    $where = '1=1';

    if (!empty($postData['conteudo'])) {
      $where .= " AND (wpi_number LIKE '%{$postData['conteudo']}%' OR wpi_message LIKE '%{$postData['conteudo']}%')";
    }

    if (isset($postData['status']) && $postData['status'] !== '') {
      $where .= " AND wpi_status = {$postData['status']}";
    }

    if (!empty($postData['tipoFiltro']) && !empty($postData['campoFiltro'])) {
      if ($postData['tipoFiltro'] === 'Exatidao' && !empty($postData['iniFiltro'])) {
        $where .= " AND DATE({$postData['campoFiltro']}) = '{$postData['iniFiltro']}'";
      } elseif ($postData['tipoFiltro'] === 'Periodo' && !empty($postData['iniFiltro']) && !empty($postData['fimFiltro'])) {
        $where .= " AND DATE({$postData['campoFiltro']}) BETWEEN '{$postData['iniFiltro']}' AND '{$postData['fimFiltro']}'";
      }
    }

    $orderBy = !empty($postData['campoOrder']) ? $postData['campoOrder'] : 'wpi_dtcad';
    $direction = !empty($postData['orderBy']) ? $postData['orderBy'] : 'DESC';

    $where .= " ORDER BY $orderBy $direction";

    $getEnvios = $apiWhatsappModel->mySelect($where);

    if ( $getEnvios->status && $getEnvios->total>0 ) {
      foreach ($getEnvios->data as $key => $envio) {
        $envio->wpi_number_mask = Helpers::adicionaMascaraContato($envio->wpi_number);
        $envio->wpi_dtcad_br = date('d/m/Y H:i', strtotime($envio->wpi_dtcad));
        $envio->wpi_dtalter_br = date('d/m/Y H:i', strtotime($envio->wpi_dtalter));
      }
    }

    echo json_encode($getEnvios);
  }

  public function consumirApiWpp()
  {
    $wap_id   = trim($_POST['wap_id']);
    $endPoint = trim($_POST['endPoint']);
    $text     = trim($_POST['text']);
    $number   = trim($_POST['number']);
    $number = Helpers::removeCaracteresEspeciais($number);

    if (empty($wap_id)) {
      (new SimpleResponse("ATENÇÃO", "A conexão informada é obrigatória."))
        ->erro()
        ->json();
    }

    if (empty($endPoint)) {
      (new SimpleResponse("ATENÇÃO", "O tipo de operação é obrigatório."))
        ->erro()
        ->json();
    }

    if (empty($text)) {
      (new SimpleResponse("ATENÇÃO", "A mensagem não pode estar vazia."))
        ->erro()
        ->json();
    }

    if (empty($number)) {
      (new SimpleResponse("ATENÇÃO", "O número de telefone é obrigatório."))
        ->erro()
        ->json();
    }

    if (!preg_match('/^[0-9]{10,11}$/', $number)) {
      (new SimpleResponse("ATENÇÃO", "O número de telefone informado não é válido. Certifique-se de que incluiu o DDD e o número correto."))
        ->erro()
        ->json();
    }

    $apiWhatsappModel = new ApiWhatsappModel();
    $apiConexao = $apiWhatsappModel->findById($wap_id);

    if ( !$apiConexao->status || empty($apiConexao->data) ) {
      (new SimpleResponse("ATENÇÃO", "A conexão informada não foi encontrada ou está inativa."))
        ->setArrayParams([
          'apiConexao' => $apiConexao
        ])
        ->erro()
        ->json();
    }

    $apiInfo = $apiConexao->data[0];
    
    $apiWhatsappInfoModel = new ApiWhatsappModel('info');

    $registroEnvio = $apiWhatsappInfoModel->registrarEnvio([
      'wpi_id_api_whatsapp' => $wap_id,
      'wpi_descricao' => "Envio de mensagem",
      'wpi_origem' => 'api_whatsapp',
      'wpi_endpoint' => $endPoint,
      'wpi_devicetoken' => $apiInfo->wap_token,
      'wpi_postfields' => [
        'number' => $number,
        'text' => $text
      ],
      'wpi_number' => $number,
      'wpi_message' => $text,
    ]);

    if ( !$registroEnvio->status ) {
      (new SimpleResponse("ATENÇÃO", "Erro ao registrar envio na base de dados."))
        ->setArrayParams([
          'registroEnvio' => $registroEnvio
        ])
        ->erro()
        ->json();
    }

    $resultado = $apiWhatsappModel->consumirApiWpp(
      $endPoint,
      $apiInfo->wap_token,
      [
        'number' => $number,
        'text' => $text
      ]
    );

    $sucesso = isset($resultado->response->error) && !$resultado->response->error;

    if ($sucesso) {
      $apiWhatsappInfoModel->atualizarStatusEnvio($registroEnvio->lastModifyId, [
        'wpi_status' => 1,
        'wpi_statusnome' => 'Enviado',
        'wpi_statusclass' => 'success',
        'wpi_response' => $resultado
      ]);

      (new SimpleResponse("Sucesso", "Mensagem enviada com sucesso!"))
        ->setArrayParams(['response' => $resultado])
        ->sucesso()
        ->json();
    } else {
      (new SimpleResponse("Erro", $resultado->response->message ?? "Falha ao enviar a mensagem."))
        ->setArrayParams(['response' => $resultado])
        ->erro()
        ->json();
    }
  }

  public function consultar()
  {
    $this->validarPermissoes(['consultar_api_whatsapp']);

    $apiWhatsappModel = new ApiWhatsappModel('info');
    $getApiwhatsappInfo = $apiWhatsappModel->mySelect("ORDER BY ".$apiWhatsappModel->prefixedColumn('dtalter')." DESC");

    $this->setBreadcrumb([
      [
        'title' => 'Consultar API WhatsApp',
        'label' => 'API WhatsApp',
        'url' => Helpers::url('api_whatsapp/consultar')
      ]
    ]);

    $this->setMetaTags([
      'title' => 'Consultar API WhatsApp',
      'description' => 'Página para consultar envios da API WhatsApp'
    ]);

    $this->render('sistema/api_whatsapp/consultar', [
      'getApiwhatsappInfo' => $getApiwhatsappInfo
    ]);
  }

  public function reenviarMensagem($url = null)
  {
    $wpi_id = $url['id'] ?? null;

    if (empty($wpi_id)) {
      (new SimpleResponse("ATENÇÃO", "O ID da mensagem é obrigatório."))
        ->erro()
        ->json();
    }

    $apiWhatsappInfoModel = new ApiWhatsappModel('info');
    $mensagem = $apiWhatsappInfoModel->findById($wpi_id);

    if (!$mensagem->status || empty($mensagem->data)) {
      (new SimpleResponse("ATENÇÃO", "A mensagem informada não foi encontrada."))
        ->erro()
        ->json();
    }

    $dadosMensagem = $mensagem->data[0];

    if ($dadosMensagem->wpi_status == 1) {
      (new SimpleResponse("ATENÇÃO", "Esta mensagem já foi enviada com sucesso e não pode ser reenviada."))
        ->erro()
        ->json();
    }

    $apiWhatsappModel = new ApiWhatsappModel();
    $apiConexao = $apiWhatsappModel->findById($dadosMensagem->wpi_id_api_whatsapp);

    if (!$apiConexao->status || empty($apiConexao->data)) {
      (new SimpleResponse("ATENÇÃO", "A conexão WhatsApp vinculada a essa mensagem não foi encontrada ou está inativa."))
        ->erro()
        ->json();
    }

    $apiInfo = $apiConexao->data[0];

    $resultado = $apiWhatsappModel->consumirApiWpp(
      $dadosMensagem->wpi_endpoint,
      $apiInfo->wap_token,
      ['number' => $dadosMensagem->wpi_number, 'text' => $dadosMensagem->wpi_message]
    );

    $sucesso = isset($resultado->response->error) && !$resultado->response->error;

    $apiWhatsappInfoModel->atualizarStatusEnvio($wpi_id, [
      'wpi_status' => $sucesso ? 1 : 0,
      'wpi_statusnome' => $sucesso ? 'Enviado' : 'Erro',
      'wpi_statusclass' => $sucesso ? 'success' : 'danger',
      'wpi_tentativas' => $dadosMensagem->wpi_tentativas + 1,
      'wpi_response' => $resultado
    ]);

    if ($sucesso) {
      (new SimpleResponse("Sucesso", "Mensagem reenviada com sucesso!"))
        ->setArrayParams(['response' => $resultado])
        ->sucesso()
        ->json();
    } else {
      (new SimpleResponse("Erro", $resultado->response->message ?? "Falha ao reenviar a mensagem."))
        ->setArrayParams(['response' => $resultado])
        ->erro()
        ->json();
    }
  }

  public function rotina()
  {
    $this->validarPermissoes(['rotina_api_whatsapp']);

    $apiWhatsappModel = new ApiWhatsappModel();
    $rotinas = $apiWhatsappModel->mySelect("ORDER BY ".$apiWhatsappModel->prefixedColumn('wap_dtalter')." DESC");

    $this->setBreadcrumb([
      [
        'title' => 'Rotinas API WhatsApp',
        'label' => 'API WhatsApp',
        'url' => Helpers::url('api_whatsapp/rotina')
      ]
    ]);

    $this->setMetaTags([
      'title' => 'Rotinas API WhatsApp',
      'description' => 'Página para gerenciar rotinas da API WhatsApp'
    ]);

    $this->render('sistema/api_whatsapp/rotina', [
      'rotinas' => $rotinas
    ]);
  }
}
