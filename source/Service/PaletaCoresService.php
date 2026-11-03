<?php

namespace Source\Service;

class PaletaCoresService
{
  public static function gerar(string $corPrincipal): array
  {
    $principal = self::normalizarHex($corPrincipal) ?: '#2d8a68';
    $hsl = self::rgbParaHsl(self::hexParaRgb($principal));
    $principalHover = self::hslParaHex(['h' => $hsl['h'], 's' => $hsl['s'], 'l' => max(0, $hsl['l'] - .05)]);
    $forte = self::hslParaHex(['h' => $hsl['h'], 's' => $hsl['s'], 'l' => max(0, $hsl['l'] - .105)]);
    $forteHover = self::hslParaHex([
      'h' => $hsl['h'],
      's' => max(.55, $hsl['s'] * .65),
      'l' => min(.48, max(.32, $hsl['l'] + .15))
    ]);
    $suave = self::hslParaHex([
      'h' => $hsl['h'],
      's' => min(.45, max(.18, $hsl['s'] * .35)),
      'l' => .92
    ]);
    $suaveHover = self::hslParaHex([
      'h' => $hsl['h'],
      's' => min(.55, max(.24, $hsl['s'] * .5)),
      'l' => .96
    ]);

    return [
      'softBg' => $suave,
      'softText' => $principal,
      'softHoverBg' => $suaveHover,
      'softHoverText' => self::ajustarCor($principal, -.14),
      'primaryBg' => $principal,
      'primaryText' => self::corDeTexto($principal),
      'primaryHoverBg' => $principalHover,
      'primaryHoverText' => self::corDeTexto($principalHover),
      'strongBg' => $forte,
      'strongText' => self::corDeTexto($forte),
      'strongHoverBg' => $forteHover,
      'strongHoverText' => self::corDeTexto($forteHover),
      'sidebarText' => '#868e96',
      'sidebarHoverBg' => $principal,
      'sidebarHoverText' => self::corDeTexto($principal),
      'sidebarSubmenuHoverBg' => $forteHover,
      'sidebarSubmenuHoverText' => self::corDeTexto($forteHover),
      'sidebarActiveBg' => $principalHover,
      'sidebarActiveText' => self::corDeTexto($principalHover),
      'sidebarSubmenuActiveBg' => $forteHover,
      'sidebarSubmenuActiveText' => self::corDeTexto($forteHover)
    ];
  }

  private static function normalizarHex(string $cor): ?string
  {
    return preg_match('/^#?([a-f0-9]{6})$/i', trim($cor), $matches) ? '#' . strtolower($matches[1]) : null;
  }

  private static function hexParaRgb(string $hex): array
  {
    $hex = ltrim($hex, '#');
    return ['r' => hexdec(substr($hex, 0, 2)), 'g' => hexdec(substr($hex, 2, 2)), 'b' => hexdec(substr($hex, 4, 2))];
  }

  private static function rgbParaHsl(array $rgb): array
  {
    $r = $rgb['r'] / 255;
    $g = $rgb['g'] / 255;
    $b = $rgb['b'] / 255;
    $max = max($r, $g, $b);
    $min = min($r, $g, $b);
    $delta = $max - $min;
    $l = ($max + $min) / 2;
    $h = 0;
    $s = 0;

    if ($delta > 0) {
      $s = $delta / (1 - abs(2 * $l - 1));
      if ($max === $r) $h = 60 * fmod(($g - $b) / $delta, 6);
      elseif ($max === $g) $h = 60 * ((($b - $r) / $delta) + 2);
      else $h = 60 * ((($r - $g) / $delta) + 4);
      if ($h < 0) $h += 360;
    }

    return ['h' => $h, 's' => $s, 'l' => $l];
  }

  private static function hslParaHex(array $hsl): string
  {
    $h = fmod((float) $hsl['h'], 360);
    if ($h < 0) $h += 360;
    $s = max(0, min(1, (float) $hsl['s']));
    $l = max(0, min(1, (float) $hsl['l']));
    $c = (1 - abs(2 * $l - 1)) * $s;
    $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
    $m = $l - $c / 2;
    if ($h < 60) $rgb = [$c, $x, 0];
    elseif ($h < 120) $rgb = [$x, $c, 0];
    elseif ($h < 180) $rgb = [0, $c, $x];
    elseif ($h < 240) $rgb = [0, $x, $c];
    elseif ($h < 300) $rgb = [$x, 0, $c];
    else $rgb = [$c, 0, $x];
    $rgb = array_map(static fn($channel): int => (int) round(($channel + $m) * 255), $rgb);

    return sprintf('#%02x%02x%02x', ...$rgb);
  }

  private static function ajustarCor(string $hex, float $ajuste): string
  {
    $hsl = self::rgbParaHsl(self::hexParaRgb($hex));
    $hsl['l'] = max(0, min(1, $hsl['l'] + $ajuste));
    return self::hslParaHex($hsl);
  }

  private static function corDeTexto(string $hex): string
  {
    $rgb = self::hexParaRgb($hex);
    $canais = array_map(static function (int $canal): float {
      $valor = $canal / 255;
      return $valor <= .03928 ? $valor / 12.92 : (($valor + .055) / 1.055) ** 2.4;
    }, [$rgb['r'], $rgb['g'], $rgb['b']]);
    $luminosidade = .2126 * $canais[0] + .7152 * $canais[1] + .0722 * $canais[2];
    return $luminosidade > .45 ? '#212529' : '#ffffff';
  }
}
