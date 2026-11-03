<?php

namespace Source\Service;

final class OrdenacaoTexto
{
  public static function comparar(string $a, string $b): int
  {
    return strcmp(self::normalizar($a), self::normalizar($b));
  }

  private static function normalizar(string $texto): string
  {
    $ascii = function_exists('iconv')
      ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto)
      : false;

    $texto = $ascii !== false ? $ascii : $texto;
    return strtolower(trim($texto));
  }
}
