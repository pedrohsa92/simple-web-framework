<?php

namespace Source\Controller\Site;

use Source\Model\ContratacaoModel;
use Source\Model\PagarmeModel;
use Source\Model\ProjetoModel;
use Source\Service\EmailService;

class WebhookController
{
  public function pagarme(): void
  {
    $rawBody = file_get_contents('php://input');
    $payload = json_decode((string) $rawBody, true);

    if (!is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
      $this->responder(400, false, 'Corpo do webhook inválido.');
      return;
    }

    $projetoResponse = (new ProjetoModel())->getProjeto();

    if (!$projetoResponse->status || empty($projetoResponse->data[0])) {
      $this->responder(503, false, 'Projeto não configurado para receber pagamentos.');
      return;
    }

    $projeto = $projetoResponse->data[0];
    $configuracao = json_decode((string) ($projeto->prj_gateway_configuracao ?? '{}'), true);
    $configuracao = is_array($configuracao) ? $configuracao : [];

    if (($projeto->prj_gateway ?? '') !== 'Pagar.me') {
      $this->responder(503, false, 'O gateway Pagar.me não está ativo neste projeto.');
      return;
    }

    $secretKey = trim((string) ($projeto->prj_gateway_sk ?? ''));
    $accountId = trim((string) ($configuracao['account_id'] ?? ''));

    if ($secretKey === '' || $accountId === '') {
      $this->responder(503, false, 'A configuração do Pagar.me está incompleta.');
      return;
    }

    if (!$this->assinaturaValida($rawBody, $secretKey)) {
      $this->responder(401, false, 'Assinatura do webhook inválida.');
      return;
    }

    $contaRecebida = trim((string) ($payload['account']['id'] ?? ''));

    if ($contaRecebida === '' || !hash_equals($accountId, $contaRecebida)) {
      $this->responder(403, false, 'A conta do webhook não corresponde ao projeto.');
      return;
    }

    $evento = strtolower(trim((string) ($payload['type'] ?? $payload['event'] ?? '')));

    if (!in_array($evento, ['order.paid', 'order.payment_failed'], true)) {
      $this->responder(200, true, 'Evento recebido e não utilizado nesta etapa.', [
        'evento' => $evento,
        'ignorado' => true
      ]);
      return;
    }

    $dadosEvento = is_array($payload['data'] ?? null) ? $payload['data'] : [];
    $pedidoId = trim((string) ($dadosEvento['id'] ?? ''));

    if ($pedidoId === '' || !str_starts_with($pedidoId, 'or_')) {
      $this->responder(422, false, 'O webhook não contém um pedido válido.');
      return;
    }

    $pedido = (new PagarmeModel($projeto))->consultarPedido($pedidoId);

    if (!$pedido->status || empty($pedido->data)) {
      $this->responder(503, false, 'Não foi possível confirmar o pedido no Pagar.me.');
      return;
    }

    $pedidoGateway = is_array($pedido->data) ? $pedido->data : (array) $pedido->data;
    $codigoWebhook = trim((string) ($dadosEvento['code'] ?? ''));
    $codigoGateway = trim((string) ($pedidoGateway['code'] ?? ''));

    if ($codigoWebhook === '' || $codigoGateway === '' || $codigoWebhook !== $codigoGateway) {
      $this->responder(422, false, 'O código do pedido não corresponde à contratação.');
      return;
    }

    $valorWebhook = (int) ($dadosEvento['amount'] ?? 0);
    $valorGateway = (int) ($pedidoGateway['amount'] ?? 0);

    if ($valorWebhook <= 0 || $valorGateway <= 0 || $valorWebhook !== $valorGateway) {
      $this->responder(422, false, 'O valor do pedido não pôde ser confirmado.');
      return;
    }

    $statusPedido = strtolower(trim((string) ($pedidoGateway['status'] ?? '')));
    $statusEsperado = $evento === 'order.paid' ? 'paid' : 'failed';

    if ($statusPedido !== $statusEsperado) {
      $this->responder(422, false, 'O status confirmado do pedido não corresponde ao evento recebido.');
      return;
    }

    $resultado = (new ContratacaoModel())->processarWebhookPagarme($payload);

    if (!$resultado->status) {
      $this->responder((int) ($resultado->http_code ?? 422), false, (string) ($resultado->text ?? 'Não foi possível processar o webhook.'));
      return;
    }

    $email = null;

    if (!empty($resultado->notificarPagamento)) {
      $email = $this->enviarEmailPagamentoConfirmado((int) ($resultado->plc_id ?? 0));
    }

    $this->responder(200, true, 'Webhook processado com sucesso.', [
      'plc_id' => $resultado->plc_id ?? null,
      'evento' => $evento,
      'repetido' => (bool) ($resultado->repetido ?? false),
      'email' => $email
    ]);
  }

  private function enviarEmailPagamentoConfirmado(int $contratacaoId): array
  {
    if ($contratacaoId <= 0) {
      return ['status' => false, 'text' => 'Contratação não identificada para o e-mail de confirmação.'];
    }

    $resumoResponse = (new ContratacaoModel())->getResumoPublico($contratacaoId);
    $resumo = $resumoResponse->status && !empty($resumoResponse->data[0])
      ? $resumoResponse->data[0]
      : null;

    if (!$resumo) {
      return ['status' => false, 'text' => 'Não foi possível carregar os dados da contratação para o e-mail.'];
    }

    $email = trim((string) ($resumo->usu_email ?? ''));
    $nome = trim((string) ($resumo->usu_razaosocial ?? ''));
    $formatarData = static function ($valor): string {
      if (empty($valor)) {
        return '-';
      }

      try {
        return (new \DateTimeImmutable((string) $valor))->format('d/m/Y H:i');
      } catch (\Throwable $e) {
        return (string) $valor;
      }
    };

    $envio = (new EmailService())->enviarTemplateProjeto(
      'pagamento_confirmado',
      $email,
      $nome,
      [
        'organizacao.documento' => $resumo->org_cnpjcpf ?? '',
        'organizacao.nome' => $resumo->org_razaosocial ?? '',
        'organizacao.nomefantasia' => $resumo->org_nomefantasia ?? '',
        'organizacao.descricao' => $resumo->org_descricao ?? '',
        'organizacao.status' => $resumo->org_status ?? 'Ativo',
        'organizacao.ambiente' => $resumo->org_ambiente ?? '',
        'organizacao.logo' => $resumo->org_logo ?? '',
        'empresa.documento' => $resumo->emp_cnpjcpf ?? '',
        'empresa.nome' => $resumo->emp_razaosocial ?? '',
        'empresa.nomefantasia' => $resumo->emp_nomefantasia ?? '',
        'empresa.descricao' => $resumo->emp_descricao ?? '',
        'empresa.status' => $resumo->emp_status ?? 'Ativo',
        'empresa.tipo' => $resumo->emp_tipo ?? 'Matriz',
        'empresa.logo' => $resumo->emp_logo ?? '',
        'usuario.documento' => $resumo->usu_cnpjcpf ?? '',
        'usuario.nome' => $nome,
        'usuario.nomefantasia' => $resumo->usu_nomefantasia ?? '',
        'usuario.email' => $email,
        'usuario.celular' => $resumo->usu_celular ?? '',
        'usuario.username' => $resumo->usu_username ?? '',
        'usuario.status' => $resumo->usu_status ?? 'Ativo',
        'plano.id' => $resumo->pla_id ?? '',
        'plano.nome' => $resumo->pla_nome ?? '',
        'plano.tipo' => $resumo->pla_tipo ?? '',
        'contratacao.id' => $resumo->plc_id ?? '',
        'contratacao.status' => $resumo->plc_status ?? '',
        'contratacao.statusfinanceiro' => $resumo->plc_statusfinanceiro ?? '',
        'contratacao.ciclo' => $resumo->plc_ciclo ?? '',
        'contratacao.valor' => number_format((float) ($resumo->plc_valor ?? 0), 2, ',', '.'),
        'contratacao.dtpagamento' => $formatarData($resumo->plc_dtpagamento),
        'contratacao.dtinicio' => $formatarData($resumo->plc_dtinicio),
        'contratacao.dtfim' => $formatarData($resumo->plc_dtfim),
        'contratacao.dtvencimento' => $formatarData($resumo->plc_dtvencimento),
        'pagamento.status' => 'Pago',
        'pagamento.evento' => 'order.paid'
      ]
    );

    return [
      'status' => (bool) ($envio->status ?? false),
      'eme_id' => (int) ($envio->data->eme_id ?? 0),
      'text' => (string) ($envio->text ?? '')
    ];
  }

  private function assinaturaValida(string $rawBody, string $secretKey): bool
  {
    $assinatura = trim((string) ($_SERVER['HTTP_X_HUB_SIGNATURE'] ?? ''));

    // A API v5 não documenta um header de assinatura no payload atual.
    // Quando o Pagar.me enviar o header legado, validamos o HMAC recebido.
    if ($assinatura === '') {
      return true;
    }

    $assinatura = preg_replace('/^sha1=/i', '', $assinatura);
    $esperada = hash_hmac('sha1', $rawBody, $secretKey);

    return is_string($assinatura) && hash_equals(strtolower($esperada), strtolower($assinatura));
  }

  private function responder(int $status, bool $sucesso, string $mensagem, array $dados = []): void
  {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
      'status' => $sucesso,
      'message' => $mensagem,
      ...$dados
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  }
}
