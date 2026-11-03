<?php

namespace Source\Model;

use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;
use PHPMailer\PHPMailer\PHPMailer;

class EmailTemplateModel extends SimpleModel
{
  protected string $tabela = 'email_template';
  protected string $prefixo = 'emt';
  protected string $identificador = 'id';
  protected string $escopoContexto = 'nenhum';
  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getEmails(string $pesquisa = ''): object
  {
    $params = [];
    $where = 'WHERE emt_idorganizacao = 0 AND emt_idempresa = 0';
    $pesquisa = trim($pesquisa);

    if ($pesquisa !== '') {
      $where .= ' AND (emt_nome LIKE :pesquisa_nome OR emt_title LIKE :pesquisa_title OR emt_templateprojeto LIKE :pesquisa_template OR emt_status LIKE :pesquisa_status)';
      $like = '%' . $pesquisa . '%';
      $params = [
        'pesquisa_nome' => $like,
        'pesquisa_title' => $like,
        'pesquisa_template' => $like,
        'pesquisa_status' => $like
      ];
    }

    return $this->rawQuery(
      "SELECT emt_id, emt_idorganizacao, emt_idempresa, emt_templateprojeto, emt_palavrachave,
              emt_nome, emt_status, emt_statusclass, emt_host, emt_user, emt_port,
              emt_remetentenome, emt_remetenteemail, emt_replyto, emt_title,
              emt_dtcad, emt_dtalter
       FROM {$this->getTable()} {$where}
       ORDER BY emt_nome ASC, emt_id ASC",
      $params,
      true,
      'E-mails encontrados com sucesso||Erro ao buscar e-mails'
    );
  }

  public function getEmailById(int $id): object
  {
    return $this->rawQuery(
      "SELECT * FROM {$this->getTable()}
       WHERE emt_id = :emt_id AND emt_idorganizacao = 0 AND emt_idempresa = 0
       LIMIT 1",
      ['emt_id' => $id],
      true,
      'Template encontrado com sucesso||Erro ao buscar template'
    );
  }

  public function getTemplateProjeto(string $chave): object
  {
    return $this->rawQuery(
      "SELECT * FROM {$this->getTable()}
       WHERE emt_idorganizacao = 0
         AND emt_idempresa = 0
         AND emt_templateprojeto = :emt_templateprojeto
         AND emt_status = 'Ativo'
       LIMIT 1",
      ['emt_templateprojeto' => trim($chave)],
      true,
      'Template de sistema encontrado com sucesso||Template de sistema não encontrado'
    );
  }

  public function salvarEmail(array $data): object
  {
    $data = $this->filtrarCampos($data);
    $id = (int) ($data['emt_id'] ?? 0);
    $atual = null;

    if ($id > 0) {
      $getAtual = $this->getEmailById($id);
      if (!$getAtual->status || $getAtual->total !== 1) {
        return (new SimpleResponse('Atenção', 'O template de e-mail informado não foi localizado.'))->warning()->noDestroy();
      }
      $atual = $getAtual->data[0];
    }

    $campos = [
      'emt_nome', 'emt_status', 'emt_host', 'emt_user', 'emt_pass', 'emt_port',
      'emt_remetentenome', 'emt_remetenteemail', 'emt_replyto', 'emt_title',
      'emt_conteudo', 'emt_palavrachave'
    ];
    $dados = [];
    foreach ($campos as $campo) {
      if (array_key_exists($campo, $data)) $dados[$campo] = $data[$campo];
      elseif ($atual) $dados[$campo] = $atual->$campo ?? null;
    }

    $dados['emt_idorganizacao'] = 0;
    $dados['emt_idempresa'] = 0;
    if ($id > 0 && array_key_exists('emt_pass', $dados) && trim((string) ($data['emt_pass'] ?? '')) === '') {
      $dados['emt_pass'] = $atual->emt_pass ?? null;
    }

    foreach (['emt_nome', 'emt_title', 'emt_conteudo'] as $campo) {
      if (array_key_exists($campo, $dados)) $dados[$campo] = trim((string) $dados[$campo]);
    }
    if (($dados['emt_nome'] ?? '') === '' || ($dados['emt_title'] ?? '') === '' || ($dados['emt_conteudo'] ?? '') === '') {
      return (new SimpleResponse('Atenção', 'Informe o nome, o assunto e o conteúdo do template.'))->warning()->noDestroy();
    }

    $status = ($dados['emt_status'] ?? 'Ativo') === 'Inativo' ? 'Inativo' : 'Ativo';
    $dados['emt_status'] = $status;
    $dados['emt_statusclass'] = $status === 'Ativo' ? 'success' : 'danger';
    $dados['emt_port'] = max(1, min(65535, (int) ($dados['emt_port'] ?? 587)));

    foreach (['emt_host', 'emt_user', 'emt_remetentenome', 'emt_remetenteemail', 'emt_replyto'] as $campo) {
      if (array_key_exists($campo, $dados)) {
        $dados[$campo] = trim((string) $dados[$campo]);
        if ($dados[$campo] === '') $dados[$campo] = null;
      }
    }

    $grupos = $this->normalizarGrupos($dados['emt_palavrachave'] ?? null);
    $dados['emt_palavrachave'] = empty($grupos) ? null : json_encode($grupos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($id > 0) {
      unset($dados['emt_idorganizacao'], $dados['emt_idempresa']);
      return $this->update($dados, ['emt_id' => $id], 'Template atualizado com sucesso||Erro ao atualizar template');
    }

    $dados['emt_templateprojeto'] = null;
    $dados['emt_lang'] = 'pt_br';
    $dados['emt_charset'] = 'UTF-8';
    return $this->insert($dados, 'Template cadastrado com sucesso||Erro ao cadastrar template');
  }

  public function testarConexao(int $id): object
  {
    $getEmail = $this->getEmailById($id);

    if (!$getEmail->status || $getEmail->total !== 1) {
      return (new SimpleResponse('Atenção', 'O template de e-mail informado não foi localizado.'))->warning()->noDestroy();
    }

    $email = $getEmail->data[0];
    $host = trim((string) ($email->emt_host ?? ''));
    $usuario = trim((string) ($email->emt_user ?? ''));
    $senha = (string) ($email->emt_pass ?? '');
    $porta = (int) ($email->emt_port ?? 587);

    if ($host === '' || $usuario === '' || $senha === '') {
      return (new SimpleResponse('Configuração incompleta', 'Informe host, usuário e senha SMTP antes de testar a conexão.'))->warning()->noDestroy();
    }

    try {
      $mail = new PHPMailer(true);
      $mail->isSMTP();
      $mail->Host = $host;
      $mail->Port = $porta > 0 ? $porta : 587;
      $mail->SMTPAuth = true;
      $mail->Username = $usuario;
      $mail->Password = $senha;
      $mail->Timeout = 15;
      $mail->SMTPDebug = 0;
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

      $conectou = $mail->smtpConnect();
      $mail->smtpClose();

      if (!$conectou) {
        return (new SimpleResponse('Falha na conexão', 'Não foi possível estabelecer a conexão SMTP.'))->warning()->noDestroy();
      }

      return (new SimpleResponse('Conexão realizada', "A conexão SMTP com {$host}:{$mail->Port} foi validada sem enviar e-mail."))
        ->sucesso()
        ->setArrayParams(['data' => ['host' => $host, 'port' => $mail->Port, 'authentication' => true, 'sent' => false]])
        ->noDestroy();
    } catch (\Throwable $erro) {
      $mensagem = trim((string) $erro->getMessage());
      $mensagem = $mensagem !== '' ? $mensagem : 'Servidor ou credenciais recusados.';

      return (new SimpleResponse('Falha na conexão', $mensagem))
        ->warning()
        ->setArrayParams(['data' => ['host' => $host, 'port' => $porta, 'sent' => false]])
        ->noDestroy();
    }
  }

  private function normalizarGrupos($grupos): array
  {
    if (is_string($grupos)) $grupos = json_decode($grupos, true);
    if (!is_array($grupos)) return [];
    $permitidos = array_keys(PalavraChaveModel::getCatalogo());
    $grupos = array_map(static fn($grupo) => strtolower(trim((string) $grupo)), $grupos);
    $grupos = array_values(array_unique(array_intersect($grupos, $permitidos)));
    sort($grupos);
    return $grupos;
  }
}
