<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleResponse;
use Source\Core\SimpleSession;
use Source\Core\SimpleModel;

class ApiWhatsappModel extends SimpleModel
{
  protected $table;
  protected $prefix;

  public function __construct(string $tabela = 'api_whatsapp')
  {
    if ($tabela === 'info') {
      $this->table = 'api_whatsapp_info';
      $this->prefix = 'wpi';
    } else {
      $this->table = 'api_whatsapp';
      $this->prefix = 'wap';
    }

    parent::__construct($this->table, $this->prefix, 'id');
  }

  public function consumirApiWpp(string $endPoint, string $deviceToken, ?array $postFields = null, int $delay = 2): object
  {
    if ($delay > 0) {
      sleep($delay);
    }

    $curl = curl_init();

    $url = 'https://cluster.apigratis.com/api/v2/whatsapp/' . $endPoint;

    $headers = [
      'Content-Type: application/json',
      'Authorization: Bearer ' . API_WHATSAPP_TOKEN,
      'DeviceToken: ' . $deviceToken
    ];

    $postFieldsJson = null;

    $curlOptions = [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => false,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_CONNECTTIMEOUT => 15,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_HTTPHEADER => $headers,
    ];

    if (!is_null($postFields)) {
      $postFieldsJson = json_encode($postFields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      $curlOptions[CURLOPT_POSTFIELDS] = $postFieldsJson;
    }

    curl_setopt_array($curl, $curlOptions);

    $inicio = microtime(true);
    $rawResponse = curl_exec($curl);
    $tempoExecucao = microtime(true) - $inicio;

    $curlErrorNo = curl_errno($curl);
    $curlError = curl_error($curl);
    $curlInfo = curl_getinfo($curl);

    curl_close($curl);

    $response = null;
    $jsonError = null;
    $jsonErrorMessage = null;

    if ($rawResponse !== false && trim($rawResponse) !== '') {
      $response = json_decode($rawResponse);
      $jsonError = json_last_error();
      $jsonErrorMessage = json_last_error_msg();
    }

    return (object) [
      'url' => $url,
      'endPoint' => $endPoint,
      'deviceToken' => $deviceToken,
      'postFields' => $postFields,
      'postFieldsJson' => $postFieldsJson,
      'response' => $response ?? (object) [],
      'rawResponse' => $rawResponse,
      'httpCode' => $curlInfo['http_code'] ?? null,
      'totalTime' => $curlInfo['total_time'] ?? null,
      'connectTime' => $curlInfo['connect_time'] ?? null,
      'namelookupTime' => $curlInfo['namelookup_time'] ?? null,
      'sizeDownload' => $curlInfo['size_download'] ?? null,
      'curlErrorNo' => $curlErrorNo,
      'curlError' => $curlError,
      'jsonError' => $jsonError,
      'jsonErrorMessage' => $jsonErrorMessage,
      'tempoExecucao' => $tempoExecucao,
      'delay' => $delay,
      'curlInfo' => $curlInfo,
      'headers' => [
        'Content-Type: application/json',
        'Authorization: Bearer ***',
        'DeviceToken: ' . $deviceToken
      ]
    ];
  }

  public function prepare(string $number, string $text, int $wap_id): object
  {
    $number = $this->sanitizeNumber($number);

    if (!$this->validateNumber($number)) {
      return (object)[
        'status' => false,
        'text' => 'Número de telefone inválido. O formato deve ser DDD + número com 8/9 dígitos!',
        'number' => $number
      ];
    }

    if (!$this->validateText($text)) {
      return (object)[
        'status' => false,
        'text' => 'A mensagem de texto não pode estar vazia!',
      ];
    }

    $apiData = $this->findById($wap_id);
    
    if (!$apiData->status || empty($apiData->data)) {
      return (object)[
        'status' => false,
        'text' => 'API WhatsApp não encontrada ou inativa!',
      ];
    }

    $apiInfo = $apiData->data[0];

    return (object)[
      'status' => true,
      'text' => 'Tudo certo!',
      'number' => $number,
      'deviceToken' => $apiInfo->wap_token,
      'message' => addslashes($text)
    ];
  }

  private function sanitizeNumber(string $number): string
  {
    $number = Helpers::removeCaracteresEspeciais($number);
    if (substr($number, 0, 2) === '55') {
      $number = substr($number, 2);
    }
    return $number;
  }

  public function validateNumber(string $number): bool
  {
    return preg_match('/^[0-9]{2}(9?[0-9]{8})$/', $number);
  }

  public function validateText(string $text): bool
  {
    return !empty(trim($text));
  }

  public function registrarEnvio(array $data): object
  {
    $usuNome = (new SimpleSession())->get('USUARIO')->usu_nome ?? 'Sistema';

    if (!isset($data['wpi_id_api_whatsapp'])) {
      return (new SimpleResponse("ATENÇÃO", "A conexão WhatsApp informada é inválida."))
        ->erro()
        ->return();
    }

    if (empty($data['wpi_descricao']) || strlen($data['wpi_descricao']) > 255) {
      return (new SimpleResponse("ATENÇÃO", "A descrição deve ter no máximo 255 caracteres e não pode estar vazia."))
        ->erro()
        ->return();
    }

    if (empty($data['wpi_origem']) || strlen($data['wpi_origem']) > 255) {
      return (new SimpleResponse("ATENÇÃO", "A origem do envio deve ter no máximo 255 caracteres e não pode estar vazia."))
        ->erro()
        ->return();
    }

    if (empty($data['wpi_endpoint']) || strlen($data['wpi_endpoint']) > 255) {
      return (new SimpleResponse("ATENÇÃO", "O endpoint deve ter no máximo 255 caracteres e não pode estar vazio."))
        ->erro()
        ->return();
    }

    if (empty($data['wpi_devicetoken']) || strlen($data['wpi_devicetoken']) > 255) {
      return (new SimpleResponse("ATENÇÃO", "O token do dispositivo deve ter no máximo 255 caracteres e não pode estar vazio."))
        ->erro()
        ->return();
    }

    if (!isset($data['wpi_envioimediato'])) $data['wpi_envioimediato'] = 1;
    if (!isset($data['wpi_tentativas'])) $data['wpi_tentativas'] = 0;

    if (!isset($data['wpi_postfields'])) {
      return (new SimpleResponse("ATENÇÃO", "Os dados do envio não foram identificados!"))
        ->erro()
        ->return();
    }

    if (empty($data['wpi_number'])) {
      return (new SimpleResponse("ATENÇÃO", "O número de telefone é inválido ou vazio."))
        ->erro()
        ->return();
    }

    if (empty($data['wpi_message'])) {
      return (new SimpleResponse("ATENÇÃO", "A mensagem do envio não pode estar vazia."))
        ->erro()
        ->return();
    }

    $data['wpi_status'] = $data['wpi_status'] ?? 0;
    $data['wpi_statusnome'] = $data['wpi_statusnome'] ?? 'Cadastrado';
    $data['wpi_statusclass'] = $data['wpi_statusclass'] ?? 'secondary';
    $data['wpi_log'] = date('d/m/Y H:i:s') . " - $usuNome: Mensagem registrada.";

    $data['wpi_postfields'] = json_encode($data['wpi_postfields']);

    return $this->myInsert($data, 'Registro de envio');
  }

  public function atualizarStatusEnvio(int $wpi_id, array $data): object
  {
    $usuNome = (new SimpleSession())->get('USUARIO')->usu_nome ?? 'Sistema';

    $envioAtual = $this->findById($wpi_id);
    if (!$envioAtual->status || empty($envioAtual->data)) {
      return (new SimpleResponse("ATENÇÃO", "Registro de envio não encontrado para atualização."))
        ->erro()
        ->return();
    }

    if (!isset($data['wpi_status'])) {
      return (new SimpleResponse("ATENÇÃO", "O status do envio é obrigatório."))
        ->erro()
        ->return();
    }

    if (empty($data['wpi_statusnome'])) {
      return (new SimpleResponse("ATENÇÃO", "O nome do status deve ter no máximo 255 caracteres e não pode estar vazio."))
        ->erro()
        ->return();
    }

    if (empty($data['wpi_statusclass'])) {
      return (new SimpleResponse("ATENÇÃO", "A classe do status deve ter no máximo 255 caracteres e não pode estar vazia."))
        ->erro()
        ->return();
    }

    $data['wpi_tentativas'] = $envioAtual->data[0]->wpi_tentativas + 1;
    $data['wpi_response'] = json_encode($data['wpi_response'] ?? []);
    $data['wpi_dtalter'] = date('Y-m-d H:i:s');
    $data['wpi_log'] = date('d/m/Y H:i:s') . " - $usuNome: " . ($data['wpi_status'] ? 'Enviado com sucesso.' : 'Falha no envio.') . "\n" . ($envioAtual->data[0]->wpi_log ?? '');

    return $this->myUpdate($data, ['wpi_id' => $wpi_id], 'Atualização de envio');
  }

	public function insertApiWhatsapp(array $data): object
	{
	  $response = $this->myInsert($data, "Conexão WhatsApp adicionada com sucesso||Erro ao adicionar conexão");

	  if ($response->status) $response->location = "reload";

	  return $response;
	}

	public function updateApiWhatsapp(int $wap_id, array $data): object
	{
	  $response = $this->myUpdate($data, ['wap_id' => $wap_id], "Conexão WhatsApp atualizada com sucesso||Erro ao atualizar conexão");

	  if ($response->status) $response->location = "reload";

	  return $response;
	}
}