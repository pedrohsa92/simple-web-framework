<?php

namespace Source\Core;

use Source\Core\SimpleResponse;

class SimpleValidator
{
  private static array $requiredConstants = [
    'CONF_DB_DRIVE',
    'CONF_DB_HOST',
    'CONF_DB_PORT',
    'CONF_DB_NAME',
    'CONF_DB_USER',
    'CONF_DB_KEY',

    'CONF_DB_DRIVE_DEV',
    'CONF_DB_HOST_DEV',
    'CONF_DB_PORT_DEV',
    'CONF_DB_NAME_DEV',
    'CONF_DB_USER_DEV',
    'CONF_DB_KEY_DEV',

    'CONF_URL_LOCALHOST',
    'CONF_URL_PRODUCAO',

    'DB_PREFIX',
    'SS_PREFIX'
  ];

  public static function validate(): void
  {
    self::validateRequiredConstants();
    self::validateDatabaseConstants();
    self::validateStartConstants();
  }

  private static function validateRequiredConstants(): void
  {
    foreach (self::$requiredConstants as $constant) {
      if (!defined($constant)) {
        self::throwError("Constante obrigatória não definida", $constant);
      }
    }
  }

  private static function validateDatabaseConstants(): void
  {
    $isLocalhost = self::isLocalhost();

    $drive = $isLocalhost ? CONF_DB_DRIVE_DEV : CONF_DB_DRIVE;
    $host = $isLocalhost ? CONF_DB_HOST_DEV : CONF_DB_HOST;
    $port = $isLocalhost ? CONF_DB_PORT_DEV : CONF_DB_PORT;
    $name = $isLocalhost ? CONF_DB_NAME_DEV : CONF_DB_NAME;
    $user = $isLocalhost ? CONF_DB_USER_DEV : CONF_DB_USER;

    if (trim((string) $drive) === '') {
      self::throwError("Drive do banco não informado", $isLocalhost ? 'CONF_DB_DRIVE_DEV' : 'CONF_DB_DRIVE');
    }

    if (trim((string) $host) === '') {
      self::throwError("Host do banco não informado", $isLocalhost ? 'CONF_DB_HOST_DEV' : 'CONF_DB_HOST');
    }

    if (trim((string) $port) === '') {
      self::throwError("Porta do banco não informada", $isLocalhost ? 'CONF_DB_PORT_DEV' : 'CONF_DB_PORT');
    }

    if (trim((string) $name) === '') {
      self::throwError("Nome do banco não informado", $isLocalhost ? 'CONF_DB_NAME_DEV' : 'CONF_DB_NAME');
    }

    if (trim((string) $user) === '') {
      self::throwError("Usuário do banco não informado", $isLocalhost ? 'CONF_DB_USER_DEV' : 'CONF_DB_USER');
    }
  }

  private static function validateStartConstants(): void
  {
    if (trim((string) CONF_URL_LOCALHOST) === '') {
      self::throwError("URL local não informada", 'CONF_URL_LOCALHOST');
    }

    if (trim((string) CONF_URL_PRODUCAO) === '') {
      self::throwError("URL de produção não informada", 'CONF_URL_PRODUCAO');
    }

    if (defined('CONF_EMP_ID') && trim((string) CONF_EMP_ID) !== '' && (int) CONF_EMP_ID <= 0) {
      self::throwError("Empresa padrão inválida", 'CONF_EMP_ID');
    }

    if (trim((string) DB_PREFIX) === '') {
      self::throwError("Prefixo do banco não informado", 'DB_PREFIX');
    }

    if (trim((string) SS_PREFIX) === '') {
      self::throwError("Prefixo da sessão não informado", 'SS_PREFIX');
    }
  }

  private static function isLocalhost(): bool
  {
    $host = $_SERVER['HTTP_HOST'] ?? '';

    return strpos($host, 'localhost') !== false || $host === '127.0.0.1';
  }

  private static function throwError(string $message, string $constant): void
  {
    $response = new SimpleResponse(
      "Erro de Configuração",
      "{$message}! <br><strong>{$constant}</strong>"
    );

    $response->erro()->page();
    exit;
  }
}