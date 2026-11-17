<?php

namespace Source\Core;

use PDO;
use PDOException;
use Source\Core\SimpleResponse;

class SimpleConnect
{
  private static ?PDO $instance = null;
  private static array $config = [];

  public static function getInstance(): PDO
  {
    if (self::$instance instanceof PDO) {
      return self::$instance;
    }

    try {
      self::setConfig();

      self::$instance = new PDO(
        self::getDsn(),
        self::$config['username'],
        self::$config['password'],
        self::getOptions()
      );

      return self::$instance;
    } catch (PDOException $exception) {
      self::renderConnectionError($exception);
    }
  }

  public static function getConfig(): array
  {
    if (empty(self::$config)) {
      self::setConfig();
    }

    return self::$config;
  }

  public static function isLocalhost(): bool
  {
    $host = $_SERVER['HTTP_HOST'] ?? '';

    return strpos($host, 'localhost') !== false || $host === '127.0.0.1';
  }

  private static function setConfig(): void
  {
    $isLocalhost = self::isLocalhost();

    self::$config = [
      'environment' => $isLocalhost ? 'local' : 'production',
      'driver' => $isLocalhost ? CONF_DB_DRIVE_DEV : CONF_DB_DRIVE,
      'host' => $isLocalhost ? CONF_DB_HOST_DEV : CONF_DB_HOST,
      'port' => $isLocalhost ? CONF_DB_PORT_DEV : CONF_DB_PORT,
      'dbname' => $isLocalhost ? CONF_DB_NAME_DEV : CONF_DB_NAME,
      'username' => $isLocalhost ? CONF_DB_USER_DEV : CONF_DB_USER,
      'password' => $isLocalhost ? CONF_DB_KEY_DEV : CONF_DB_KEY
    ];
  }

  private static function getDsn(): string
  {
    return self::$config['driver']
      . ':host=' . self::$config['host']
      . ';dbname=' . self::$config['dbname']
      . ';port=' . self::$config['port']
      . ';charset=utf8mb4';
  }

  private static function getOptions(): array
  {
    return [
      PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
      PDO::ATTR_CASE => PDO::CASE_NATURAL,
      PDO::ATTR_EMULATE_PREPARES => false
    ];
  }

  private static function renderConnectionError(PDOException $exception): void
  {
    $message = $exception->getMessage();

    if (strpos($message, 'Unknown database') !== false) {
      $message = "Banco de dados não encontrado: <strong>" . self::$config['dbname'] . "</strong>";
    }

    $response = new SimpleResponse("Erro de Conexão com o Banco de Dados", $message);
    $response->erro()->html();
    exit;
  }
}