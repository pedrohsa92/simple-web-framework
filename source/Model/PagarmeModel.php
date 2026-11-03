<?php

namespace Source\Model;

use Source\Core\SimpleResponse;

class PagarmeModel
{
  private const API_URL = 'https://api.pagar.me/core/v5/recipients';

  private object $projeto;

  public function __construct(object $projeto)
  {
    $this->projeto = $projeto;
  }

  public function consultar(): object
  {
    $secretKey = trim((string) ($this->projeto->prj_gateway_sk ?? ''));
    $configuracao = json_decode((string) ($this->projeto->prj_gateway_configuracao ?? '{}'), true);
    $configuracao = is_array($configuracao) ? $configuracao : [];

    if ($secretKey === '') {
      return (new SimpleResponse('Gateway não configurado', 'Informe a Secret Key do Pagar.me antes de consultar.'))
        ->warning()
        ->noDestroy();
    }

    if (!function_exists('curl_init')) {
      return (new SimpleResponse('Erro de configuração', 'A extensão cURL do PHP não está habilitada.'))
        ->erro()
        ->noDestroy();
    }

    $url = self::API_URL . '?page=1&size=100';
    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => false,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 5,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_USERPWD => $secretKey . ':',
      CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
      CURLOPT_HTTPHEADER => [
        'Accept: application/json'
      ]
    ]);

    $rawResponse = curl_exec($curl);
    $curlError = curl_error($curl);
    $curlErrorNo = curl_errno($curl);
    $curlInfo = curl_getinfo($curl);
    curl_close($curl);

    $httpCode = (int) ($curlInfo['http_code'] ?? 0);
    $response = null;

    if ($rawResponse !== false && trim((string) $rawResponse) !== '') {
      $response = json_decode((string) $rawResponse, true);
    }

    $resultado = [
      'gateway' => 'Pagar.me',
      'endpoint' => $url,
      'http_code' => $httpCode,
      'account_id' => (string) ($configuracao['account_id'] ?? ''),
      'environment' => (string) ($configuracao['environment'] ?? 'test'),
      'split_rules_configuradas' => count((array) ($configuracao['rules'] ?? [])),
      'response' => is_array($response) ? $response : (string) ($rawResponse ?: '')
    ];

    if ($rawResponse === false || $curlErrorNo !== 0) {
      return (new SimpleResponse('Falha na consulta', 'Não foi possível consultar o Pagar.me.'))
        ->erro()
        ->setArrayParams([
          'data' => (object) [
            ...$resultado,
            'curl_error' => $curlError,
            'curl_error_no' => $curlErrorNo
          ]
        ])
        ->noDestroy();
    }

    if ($httpCode < 200 || $httpCode >= 300) {
      return (new SimpleResponse('Falha na consulta', 'O Pagar.me recusou a consulta. Verifique as credenciais e o ambiente.'))
        ->erro()
        ->setArrayParams([
          'data' => (object) $resultado
        ])
        ->noDestroy();
    }

    return (new SimpleResponse('Consulta realizada', 'A conexão com o Pagar.me foi consultada com sucesso.'))
      ->sucesso()
      ->setArrayParams([
        'data' => (object) $resultado,
        'total' => 1
      ])
      ->noDestroy();
  }

  public function consultarPedido(string $pedidoId): object
  {
    $secretKey = trim((string) ($this->projeto->prj_gateway_sk ?? ''));
    $configuracao = json_decode((string) ($this->projeto->prj_gateway_configuracao ?? '{}'), true);
    $configuracao = is_array($configuracao) ? $configuracao : [];

    if ($secretKey === '' || $pedidoId === '') {
      return (new SimpleResponse('Pedido não confirmado', 'Não foi possível validar o pedido no Pagar.me.'))
        ->warning()
        ->noDestroy();
    }

    if (!function_exists('curl_init')) {
      return (new SimpleResponse('Erro de configuração', 'A extensão cURL do PHP não está habilitada.'))
        ->erro()
        ->noDestroy();
    }

    // A consulta de pedido usa a API Core principal. A documentação do
    // endpoint não aponta o host sdx utilizado especificamente por links.
    $url = 'https://api.pagar.me/core/v5/orders/' . rawurlencode($pedidoId);
    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => false,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 5,
      CURLOPT_TIMEOUT => 15,
      CURLOPT_CONNECTTIMEOUT => 5,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_USERPWD => $secretKey . ':',
      CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
      CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'User-Agent: LevHub/1.0'
      ]
    ]);

    $rawResponse = curl_exec($curl);
    $curlErrorNo = curl_errno($curl);
    $curlInfo = curl_getinfo($curl);
    curl_close($curl);

    $httpCode = (int) ($curlInfo['http_code'] ?? 0);
    $response = is_string($rawResponse) ? json_decode($rawResponse, true) : null;

    if ($rawResponse === false || $curlErrorNo !== 0 || $httpCode < 200 || $httpCode >= 300 || !is_array($response)) {
      return (new SimpleResponse('Pedido não confirmado', 'O Pagar.me não confirmou o pedido informado.'))
        ->warning()
        ->setArrayParams([
          'http_code' => $httpCode,
          'data' => null
        ])
        ->noDestroy();
    }

    return (new SimpleResponse('Pedido confirmado', 'Pedido consultado no Pagar.me.'))
      ->sucesso()
      ->setArrayParams([
        'data' => $response
      ])
      ->noDestroy();
  }

  public function criarLinkPagamento(array $dados): object
  {
    $secretKey = trim((string) ($this->projeto->prj_gateway_sk ?? ''));
    $configuracao = json_decode((string) ($this->projeto->prj_gateway_configuracao ?? '{}'), true);
    $configuracao = is_array($configuracao) ? $configuracao : [];

    if ($secretKey === '') {
      return (new SimpleResponse('Pagamento pendente', 'Não foi possível gerar o link de pagamento porque o gateway ainda não está configurado.'))
        ->warning()
        ->setArrayParams(['data' => (object) ['request' => null, 'response' => null]])
        ->noDestroy();
    }

    if (!function_exists('curl_init')) {
      return (new SimpleResponse('Pagamento pendente', 'A integração de pagamento não está disponível neste momento.'))
        ->warning()
        ->setArrayParams(['data' => (object) ['request' => null, 'response' => null]])
        ->noDestroy();
    }

    $ambiente = strtolower(trim((string) ($configuracao['environment'] ?? 'test')));
    $baseUrl = $ambiente === 'production' || $ambiente === 'live'
      ? 'https://api.pagar.me/core/v5'
      : 'https://sdx-api.pagar.me/core/v5';
    $valorCentavos = max(1, (int) round(((float) ($dados['valor'] ?? 0)) * 100));
    $request = [
      'is_building' => false,
      'name' => mb_substr('LevHub - ' . ($dados['plano_nome'] ?? 'Plano'), 0, 64),
      'order_code' => 'LEVHUB-' . (int) ($dados['plc_id'] ?? 0),
      'type' => 'order',
      'max_paid_sessions' => 1,
      'expires_in' => 60 * 24 * 7,
      'payment_settings' => [
        'accepted_payment_methods' => ['credit_card', 'pix', 'boleto'],
        'credit_card_settings' => [
          'operation_type' => 'auth_and_capture',
          'installments' => [[
            'number' => 1,
            'total' => $valorCentavos
          ]]
        ],
        'boleto_settings' => [
          'due_in' => 7
        ],
        'pix_settings' => [
          'expires_in' => 60 * 60 * 24 * 7
        ]
      ],
      'cart_settings' => [
        'items' => [[
          'amount' => $valorCentavos,
          'name' => mb_substr('LevHub - ' . ($dados['plano_nome'] ?? 'Plano'), 0, 64),
          'default_quantity' => 1
        ]]
      ]
    ];

    $url = $baseUrl . '/paymentlinks';
    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => false,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 5,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => json_encode($request, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      CURLOPT_USERPWD => $secretKey . ':',
      CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
      CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/json',
        'User-Agent: LevHub/1.0'
      ]
    ]);

    $rawResponse = curl_exec($curl);
    $curlError = curl_error($curl);
    $curlErrorNo = curl_errno($curl);
    $curlInfo = curl_getinfo($curl);
    curl_close($curl);

    $httpCode = (int) ($curlInfo['http_code'] ?? 0);
    $response = is_string($rawResponse) ? json_decode($rawResponse, true) : null;
    $response = is_array($response) ? $response : ['raw' => (string) ($rawResponse ?: '')];
    $ok = $rawResponse !== false && $curlErrorNo === 0 && $httpCode >= 200 && $httpCode < 300 && !empty($response['id']);

    $resultado = (object) [
      'request' => $request,
      'response' => $response,
      'http_code' => $httpCode,
      'curl_error' => $curlError,
      'curl_error_no' => $curlErrorNo,
      'id' => $response['id'] ?? null,
      'url' => $response['url'] ?? null,
      'status' => $response['status'] ?? ($ok ? 'active' : 'failed')
    ];

    if (!$ok) {
      return (new SimpleResponse('Pagamento pendente', 'Não foi possível gerar o link de pagamento agora. A contratação foi preservada e poderá ser regularizada após uma nova tentativa.'))
        ->warning()
        ->setArrayParams(['data' => $resultado])
        ->noDestroy();
    }

    return (new SimpleResponse('Link de pagamento criado', 'O link de pagamento está pronto.'))
      ->sucesso()
      ->setArrayParams(['data' => $resultado])
      ->noDestroy();
  }
}
