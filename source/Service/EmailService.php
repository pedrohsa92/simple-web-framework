<?php

namespace Source\Service;

use PHPMailer\PHPMailer\PHPMailer;
use Source\Model\EmailEnviadoModel;
use Source\Model\EmailTemplateModel;
use Source\Model\ProjetoModel;

class EmailService
{
  private ?object $projetoAtual = null;

  public function enviarTemplateProjeto(string $chave, string $destinatarioEmail, string $destinatarioNome, array $variaveis = []): object
  {
    $templateResponse = (new EmailTemplateModel())->getTemplateProjeto($chave);
    $template = $templateResponse->status && !empty($templateResponse->data[0])
      ? $templateResponse->data[0]
      : null;
    $email = trim($destinatarioEmail);
    $nome = trim($destinatarioNome);
    $tituloBase = 'Mensagem do LevHub';

    if (!$template) {
      return $this->registrarFalha(null, $email, $nome, $tituloBase, '', [
        'template' => $chave,
        'variaveis' => $variaveis
      ], 'Template de sistema não encontrado ou está inativo.');
    }

    $variaveis = $this->prepararVariaveisProjeto($variaveis);
    $titulo = $this->renderizar((string) ($template->emt_title ?? $tituloBase), $variaveis, false);
    $mensagem = $this->comporMensagem($template, $variaveis);
    $snapshot = $this->snapshot($template, $chave);
    $emailModel = new EmailEnviadoModel();

    $registro = $emailModel->registrarTentativa([
      'eme_idorganizacao' => 0,
      'eme_idempresa' => 0,
      'eme_idtemplate' => (int) ($template->emt_id ?? 0),
      'eme_tentativa' => 1,
      'eme_status' => 'Pendente',
      'eme_statusclass' => 'secondary',
      'eme_destinarionome' => $nome !== '' ? $nome : null,
      'eme_destinatarioemail' => $email,
      'eme_title' => $titulo !== '' ? $titulo : $tituloBase,
      'eme_mensagem' => $mensagem,
      'eme_snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      'eme_log' => '[]'
    ]);

    if (!$registro->status || (int) ($registro->lastInsertId ?? 0) <= 0) {
      return $this->resultado(false, 'E-mail não registrado', 'Não foi possível registrar a tentativa de envio.', [
        'template' => $chave
      ]);
    }

    $envioId = (int) $registro->lastInsertId;

    try {
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new \RuntimeException('Destinatário de e-mail inválido.');
      }

      $mail = $this->criarMailer($template);
      $mail->setFrom((string) $template->emt_remetenteemail, (string) ($template->emt_remetentenome ?: 'LevHub'));
      if (!empty($template->emt_replyto) && filter_var($template->emt_replyto, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo((string) $template->emt_replyto, (string) ($template->emt_remetentenome ?: 'LevHub'));
      }
      $mail->addAddress($email, $nome);
      $mail->Subject = $titulo !== '' ? $titulo : $tituloBase;
      $mail->isHTML(true);
      $mail->Body = $mensagem;
      $mail->AltBody = trim((string) preg_replace('/\s+/', ' ', strip_tags($mensagem)));
      $mail->send();

      $response = [
        'status' => true,
        'message' => 'E-mail enviado com sucesso.',
        'message_id' => $mail->getLastMessageID(),
        'sent_at' => date('Y-m-d H:i:s')
      ];
      $emailModel->atualizarTentativa($envioId, [
        'eme_status' => 'Enviado',
        'eme_statusclass' => 'success',
        'eme_dtenvio' => date('Y-m-d H:i:s'),
        'eme_response' => json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'eme_erro' => null
      ]);

      return $this->resultado(true, 'E-mail enviado', 'A mensagem foi enviada com sucesso.', [
        'eme_id' => $envioId,
        'template' => $chave
      ]);
    } catch (\Throwable $exception) {
      $erro = trim((string) $exception->getMessage());
      $response = [
        'status' => false,
        'message' => 'Não foi possível enviar o e-mail.',
        'error' => $erro
      ];
      $emailModel->atualizarTentativa($envioId, [
        'eme_status' => 'Falhou',
        'eme_statusclass' => 'danger',
        'eme_response' => json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'eme_erro' => $erro
      ]);

      return $this->resultado(false, 'E-mail não enviado', $erro, [
        'eme_id' => $envioId,
        'template' => $chave
      ]);
    }
  }

  private function prepararVariaveisProjeto(array $variaveis): array
  {
    $projetoResponse = (new ProjetoModel())->getProjeto();
    $projeto = $projetoResponse->status && !empty($projetoResponse->data[0])
      ? $projetoResponse->data[0]
      : null;
    $this->projetoAtual = $projeto;

    if (!$projeto) {
      return $variaveis;
    }

    $url = rtrim((string) ($variaveis['projeto.url'] ?? $projeto->prj_url_central ?? ''), '/');
    if ($url === '') {
      $url = rtrim(\Helpers::url(''), '/');
    }

    $logo = trim((string) ($projeto->prj_logo ?? ''));
    if ($logo !== '' && !preg_match('#^https?://#i', $logo)) {
      $logo = $url . '/' . ltrim($logo, '/');
    }

    $globais = [
      'projeto.nome' => $projeto->prj_nome ?? 'levhub',
      'projeto.titulo' => $projeto->prj_titulo ?? 'LevHub',
      'projeto.descricao' => $projeto->prj_descricao ?? '',
      'projeto.dominio' => $projeto->prj_dominio ?? '',
      'projeto.url' => $url,
      'projeto.logo' => $logo,
      'projeto.email' => 'contato@levhub.com.br',
      'projeto.status' => $projeto->prj_status ?? 'Ativo'
    ];

    return array_merge($globais, $variaveis);
  }

  private function comporMensagem(object $template, array $variaveis): string
  {
    $conteudo = $this->renderizar((string) ($template->emt_conteudo ?? ''), $variaveis, true);
    $header = $this->renderizar((string) ($this->projetoAtual->prj_email_header ?? ''), $variaveis, true);
    $footer = $this->renderizar((string) ($this->projetoAtual->prj_email_footer ?? ''), $variaveis, true);

    if ($header === '' && $footer === '') {
      return $conteudo;
    }

    // Templates legados que ja possuem o documento HTML completo continuam compativeis.
    if (preg_match('/<\s*(?:!doctype|html|body)\b/i', $conteudo)) {
      return $conteudo;
    }

    return '<!doctype html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head><body style="margin:0;background:#f4f7fb;font-family:Arial,Helvetica,sans-serif;color:#24344d;padding:32px 12px;"><div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #dbe4f0;border-radius:14px;overflow:hidden;">' . $header . '<div style="padding:32px;">' . $conteudo . '</div>' . $footer . '</div></body></html>';
  }

  private function criarMailer(object $template): PHPMailer
  {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = trim((string) ($template->emt_host ?? ''));
    $mail->Port = max(1, (int) ($template->emt_port ?? 587));
    $mail->SMTPAuth = true;
    $mail->Username = (string) ($template->emt_user ?? '');
    $mail->Password = (string) ($template->emt_pass ?? '');
    $mail->CharSet = (string) ($template->emt_charset ?: 'UTF-8');
    $mail->setLanguage((string) ($template->emt_lang ?: 'pt_br'));
    $mail->SMTPDebug = 0;
    $mail->Timeout = 20;
    $mail->SMTPOptions = [
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
      ]
    ];

    if ($mail->Port === 465) {
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->SMTPAutoTLS = false;
    } elseif ($mail->Port === 587) {
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } else {
      $mail->SMTPSecure = '';
      $mail->SMTPAutoTLS = false;
    }

    return $mail;
  }

  private function renderizar(string $conteudo, array $variaveis, bool $html): string
  {
    foreach ($variaveis as $chave => $valor) {
      $valor = is_scalar($valor) || $valor === null ? (string) ($valor ?? '') : json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      $conteudo = str_replace(
        '{' . $chave . '}',
        $html ? htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $valor,
        $conteudo
      );
    }

    return $conteudo;
  }

  private function snapshot(object $template, string $chave): array
  {
    return [
      'emt_id' => (int) ($template->emt_id ?? 0),
      'emt_templateprojeto' => $chave,
      'emt_palavrachave' => $template->emt_palavrachave ?? null,
      'emt_nome' => $template->emt_nome ?? null,
      'emt_status' => $template->emt_status ?? null,
      'emt_host' => $template->emt_host ?? null,
      'emt_user' => $template->emt_user ?? null,
      'emt_pass' => '[REDACTED]',
      'emt_port' => (int) ($template->emt_port ?? 587),
      'emt_remetentenome' => $template->emt_remetentenome ?? null,
      'emt_remetenteemail' => $template->emt_remetenteemail ?? null,
      'emt_replyto' => $template->emt_replyto ?? null,
      'emt_title' => $template->emt_title ?? null,
      'emt_lang' => $template->emt_lang ?? 'pt_br',
      'emt_charset' => $template->emt_charset ?? 'UTF-8',
      'emt_conteudo' => $template->emt_conteudo ?? null,
      'prj_email_header' => $this->projetoAtual->prj_email_header ?? null,
      'prj_email_footer' => $this->projetoAtual->prj_email_footer ?? null,
      'variaveis' => $template->emt_palavrachave ?? null,
      'registrado_em' => date('Y-m-d H:i:s')
    ];
  }

  private function registrarFalha(?object $template, string $email, string $nome, string $titulo, string $mensagem, array $snapshot, string $erro): object
  {
    $registro = (new EmailEnviadoModel())->registrarTentativa([
      'eme_idorganizacao' => 0,
      'eme_idempresa' => 0,
      'eme_idtemplate' => $template ? (int) ($template->emt_id ?? 0) : null,
      'eme_tentativa' => 1,
      'eme_status' => 'Falhou',
      'eme_statusclass' => 'danger',
      'eme_destinarionome' => $nome !== '' ? $nome : null,
      'eme_destinatarioemail' => $email !== '' ? $email : 'nao-informado@levhub.invalid',
      'eme_title' => $titulo,
      'eme_mensagem' => $mensagem,
      'eme_snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      'eme_erro' => $erro,
      'eme_response' => json_encode(['status' => false, 'error' => $erro], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      'eme_log' => '[]'
    ]);

    return $this->resultado(false, 'E-mail não enviado', $erro, [
      'eme_id' => (int) ($registro->lastInsertId ?? 0),
      'template' => $snapshot['template'] ?? null
    ]);
  }

  private function resultado(bool $sucesso, string $titulo, string $texto, array $dados = []): object
  {
    $response = (object) ['status' => $sucesso, 'title' => $titulo, 'text' => $texto, 'data' => (object) $dados];
    return $response;
  }
}
