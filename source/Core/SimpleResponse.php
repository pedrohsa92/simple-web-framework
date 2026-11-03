<?php

namespace Source\Core;

class SimpleResponse {
  public $status = false;
  public $title = 'Atenção';
  public $text = 'Erro ao processar esta mensagem';
  public $icon = 'error';
  public $class = 'danger';
  public $total = 0;
  public $totalLinha = 0;
  public $data = null;
  public $lastInsertId = null;
  public $error = null;
  public $errorInfo = null;
  public $debug = null;
  public $responseRendered = false;
  public $html;
  public $alert;
  public $return = false;
  public $validate = null;

  public function __construct($objetoOrTitle, $text = '', array $params = []) {
    if (is_object($objetoOrTitle)) {
      if ($text !== '') {
        $objetoOrTitle->text = $text;
      }
      $this->fromModelResult($objetoOrTitle);
    } else {
      $this->title = $objetoOrTitle;
      $this->text = $text;
    }

    if ( !empty($params) && is_array($params) ) {
      $this->setArrayParams($params);
    }

    $this->renderHtml();
  }

  public function __destruct() {
    if (!$this->responseRendered) {
      $this->json();
    }
  }

  private function fromModelResult(object $result) {
    $this->status = $result->status ?? false;
    $this->title = $result->title ?? 'Atenção';
    $this->text = $result->text ?? 'Erro ao processar esta mensagem';
    $this->icon = $result->icon ?? 'error';
    $this->total = $result->total ?? 0;
    $this->totalLinha = $result->totalLinha ?? 0;
    $this->data = $result->data ?? null;
    $this->lastInsertId = $result->lastInsertId ?? null;
    $this->error = $result->error ?? null;
    $this->errorInfo = $result->errorInfo ?? null;
    $this->debug = $result->debug ?? null;
  }

  public function setArrayParams(array $params): object {
    if ( !empty($params) && is_array($params) ) {
      foreach ($params as $key => $value) {
        if ( $value !== '' && !is_null($value) ) {
          $this->{$key} = $value;
        }
      }
    }

    return $this;
  }

  public function setTitle($title): self {
    $this->title = $title;
    return $this;
  }

  public function erro($text=null): self {
    $this->status = false;
    $this->icon   = 'error';
    $this->class  = 'danger';
    if ( !is_null($text) ) {
      $this->text  = $text;
    }
    return $this;
  }

  public function sucesso($text=null): self {
    $this->status = true;
    $this->icon   = 'success';
    $this->class  = 'success';
    if ( !is_null($text) ) {
      $this->text  = $text;
    }
    return $this;
  }

  public function info($text=null): self {
    $this->status = false;
    $this->icon   = 'info';
    $this->class  = 'info';
    if ( !is_null($text) ) {
      $this->text  = $text;
    }
    return $this;
  }

  public function warning($text=null): self {
    $this->status = false;
    $this->icon   = 'warning';
    $this->class  = 'warning';
    if ( !is_null($text) ) {
      $this->text  = $text;
    }
    return $this;
  }

  private function renderHtml(): self {
    $this->html = htmlspecialchars("
      <main class='px-3'>
        <i class='fas fa-exclamation-circle fa-5x text-{$this->class} mb-4'></i>
        <h1 class='display-4'>{$this->title}</h1>
        <p class='lead'>{$this->text}</p>
      </main>", ENT_QUOTES, 'UTF-8');

    $this->alert = htmlspecialchars("
      <div class='alert alert-{$this->class} alert-dismissible fade show' role='alert'>
        <strong>{$this->title}</strong> {$this->text}
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
     </div>", ENT_QUOTES, 'UTF-8');

    return $this;
  }

  public function return($value=true): self {
    $this->return = $value;
    return $this;
  }

  public function validate($value=true): self {
    $this->validate = $value;
    return $this;
  }

  private function applyValidate() {
    if (!is_null($this->validate)) {
      if (
        ($this->validate === true && $this->status === true) || 
        ($this->validate === false && $this->status === false)
      ) {
        $this->return = true;
      }
    }
  }

  private function toArray(): array {
    return get_object_vars($this);
  }

  private function toObject(): object {
    return (object) $this->toArray();
  }

  public function noDestroy(): object {
    $this->responseRendered = true;
    return $this;
  }

  public function json() {
    $this->applyValidate();
    $this->responseRendered = true;
    header('Content-Type: application/json');
    if ($this->return) {
      return $this->toObject();
    } else {
      echo json_encode($this);
      exit;
    }
  }

  public function xml() {
    $this->applyValidate();
    $this->responseRendered = true;
    header('Content-Type: application/xml; charset=utf-8');
    $xmlResponse = "<?xml version='1.0' encoding='UTF-8'?>
    <response>
      <status>{$this->status}</status>
      <title>{$this->title}</title>
      <text>{$this->text}</text>
      <icon>{$this->icon}</icon>
    </response>";

    if ($this->return) {
      return $xmlResponse;
    } else {
      echo $xmlResponse;
      exit;
    }
  }

  public function txt() {
    $this->applyValidate();
    $this->responseRendered = true;
    header('Content-Type: text/plain; charset=utf-8');
    $response = [
      "Status: " . ($this->status ? 'Sucesso' : 'Erro'),
      "Título: {$this->title}",
      "Mensagem: {$this->text}",
      "Ícone: {$this->icon}",
      "Total: {$this->total}",
      "Total de Linhas: {$this->totalLinha}",
      "Último ID Inserido: " . ($this->lastInsertId ?? 'N/A')
    ];

    if ($this->error) {
      $response[] = "Erro: {$this->error->getMessage()}";
    }

    if ($this->errorInfo) {
      $response[] = "Detalhes do Erro: {$this->errorInfo}";
    }

    if ($this->debug) {
      $response[] = "Depuração: " . json_encode($this->debug, JSON_PRETTY_PRINT);
    }

    $output = implode("\n", $response);

    if ($this->return) {
      return $output;
    } else {
      echo $output;
      exit;
    }
  }

  public function page(bool $mostrarLogin = false, ?string $loginUrl = null)
  {
    $this->applyValidate();
    $this->responseRendered = true;

    if (!headers_sent()) {
      header('Content-Type: text/html; charset=utf-8');
    }

    $loginUrl = $loginUrl ?? (defined('CONF_URL') ? CONF_URL . '/login' : 'login');

    $botaoLogin = '';

    if ($mostrarLogin) {
      $botaoLogin = "
        <div class='mt-4'>
          <a href='{$loginUrl}' class='btn btn-primary'>
            <i class='fa-solid fa-right-to-bracket'></i>
            Ir para o login
          </a>
        </div>
      ";
    }

    $htmlResponse = "<!DOCTYPE html>
      <html lang='pt-BR'>
      <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>{$this->title}</title>
        <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        <style>
          html,
          body {
            height: 100%;
            margin: 0;
          }

          body {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
          }

          .simple-response-page {
            width: 100%;
            max-width: 520px;
            padding: 30px;
            text-align: center;
          }

          .simple-response-icon {
            font-size: 64px;
          }
        </style>
      </head>
      <body>
        <main class='simple-response-page'>
          <i class='fa-solid fa-circle-exclamation simple-response-icon text-{$this->class} mb-4'></i>

          <h1 class='h3 mb-3'>
            {$this->title}
          </h1>

          <p class='text-muted mb-0'>
            {$this->text}
          </p>

          {$botaoLogin}
        </main>

        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
      </body>
      </html>";

    if ($this->return) {
      return $htmlResponse;
    }

    echo $htmlResponse;
    exit;
  }

  public function html()
  {
    $this->applyValidate();
    $this->responseRendered = true;

    if (!headers_sent()) {
      header('Content-Type: text/html; charset=utf-8');
    }

    $html = "
      <link rel='stylesheet' href='" . CONF_URL_LOCALHOST . "/framework/bootstrap/css/bootstrap.min.css'>
      " . html_entity_decode($this->html, ENT_QUOTES, 'UTF-8');

    if ($this->return) {
      return $html;
    }

    echo $html;
    exit;
  }

  public function alert()
  {
    $this->applyValidate();
    $this->responseRendered = true;

    if (!headers_sent()) {
      header('Content-Type: text/html; charset=utf-8');
    }

    $html = "
      <link rel='stylesheet' href='" . CONF_URL_LOCALHOST . "/framework/bootstrap/css/bootstrap.min.css'>
      " . html_entity_decode($this->alert, ENT_QUOTES, 'UTF-8');

    if ($this->return) {
      return $html;
    }

    echo $html;
    exit;
  }
}
