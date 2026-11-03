<?php

namespace Source\Core;

class SimpleSession
{
  private $prefix;

  public function __construct()
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    $this->prefix = SS_PREFIX ?: 'SW';
  }

  private function getPrefixedKey(string $key): string
  {
    return strtoupper($this->prefix . '_' . $key);
  }

  public function set(string $key, $value): void
  {
    $_SESSION[$this->getPrefixedKey($key)] = $value;
  }

  public function get(string $key)
  {
    $prefixedKey = $this->getPrefixedKey($key);
    return $_SESSION[$prefixedKey] ?? null;
  }

  public function has(string $key): bool
  {
    return isset($_SESSION[$this->getPrefixedKey($key)]);
  }

  public function remove(string $key): void
  {
    unset($_SESSION[$this->getPrefixedKey($key)]);
  }

  public function clear(): void
  {
    foreach ($_SESSION as $key => $value) {
      if (strpos($key, strtoupper($this->prefix . '_')) === 0) {
        unset($_SESSION[$key]);
      }
    }
  }
}