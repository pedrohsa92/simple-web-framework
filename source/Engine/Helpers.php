<?php

use Source\Core\SimpleSession;
use Source\Core\SimpleResponse;

class Helpers
{
  # Manipulador de URL
    public static function url(string $path = null): string
    {
      $host = $_SERVER['HTTP_HOST'] ?? '';

      $basePath = (strpos($host, 'localhost') !== false || $host === '127.0.0.1')
        ? rtrim(CONF_URL_LOCALHOST, '/')
        : rtrim(CONF_URL_PRODUCAO, '/');

      $finalPath = $path ? '/' . ltrim($path, '/') : '';

      return $basePath . $finalPath;
    }

    public static function storage($path, $pathAlternative = "storage/global/semimagem.jpg"): string
    {
      if (is_null($path) || trim((string) $path) === '') {
        return self::url($pathAlternative);
      }

      if (filter_var($path, FILTER_VALIDATE_URL)) {
        return $path;
      }

      return self::url($path);
    }

    public static function currentUrl(): string
    {
      $uri = $_SERVER['REQUEST_URI'] ?? '';

      return self::url($uri);
    }

    public static function redirect(string $path = ''): void
    {
      header("Location: " . self::url($path));
      exit;
    }
  # Manipulador de URL

  # Sessão
    public static function session(): SimpleSession
    {
      return new SimpleSession();
    }

    public static function projeto(): ?object
    {
      return self::session()->get('PROJETO') ?? null;
    }

    public static function usuarioLogado(): ?object
    {
      return self::session()->get('USUARIO') ?? null;
    }

    public static function organizacaoLogada(): ?object
    {
      $usuario = self::usuarioLogado();

      return $usuario->organizacao ?? null;
    }

    public static function empresaLogada(): ?object
    {
      $usuario = self::usuarioLogado();

      return $usuario->empresa ?? null;
    }

    public static function usuarioLogadoId(): ?int
    {
      $usuario = self::usuarioLogado();

      return isset($usuario->usu_id) ? (int) $usuario->usu_id : null;
    }

    public static function organizacaoLogadaId(): ?int
    {
      $usuario = self::usuarioLogado();

      return isset($usuario->usu_idorganizacao) ? (int) $usuario->usu_idorganizacao : null;
    }

    public static function empresaLogadaId(): ?int
    {
      $usuario = self::usuarioLogado();

      return isset($usuario->usu_idempresa) ? (int) $usuario->usu_idempresa : null;
    }
  # Sessão

  # Manipulador de string
    public static function removeCaracteresEspeciais($str, $exception = null): string
    {
      $str = self::removeAcentos((string) $str);

      if (isset($exception) && is_array($exception) && in_array('space', $exception)) {
        $str = preg_replace('/[^a-z0-9\s]/i', '', $str);
        $str = preg_replace('/\s+/', ' ', $str);

        return trim($str);
      }

      $str = preg_replace('/[^a-z0-9]/i', '', $str);
      $str = preg_replace('/_+/', '', $str);

      return $str;
    }

    public static function removeAcentos($str): string
    {
      $str = preg_replace('/[áàãâä]/ui', 'a', (string) $str);
      $str = preg_replace('/[éèêë]/ui', 'e', $str);
      $str = preg_replace('/[íìîï]/ui', 'i', $str);
      $str = preg_replace('/[óòõôö]/ui', 'o', $str);
      $str = preg_replace('/[úùûü]/ui', 'u', $str);
      $str = preg_replace('/[ç]/ui', 'c', $str);

      return $str;
    }

    public static function somenteNumeros($str): string
    {
      return preg_replace('/[^0-9]/', '', (string) $str);
    }

    public static function limitarString($str, $width = 10, $trimMarker = "...", $start = 0): string
    {
      return mb_strimwidth((string) $str, $start, $width, $trimMarker, "UTF-8");
    }

    public static function convertToFloat($number): float
    {
      $number = trim((string) $number);

      if (strpos($number, ',') !== false) {
        $number = str_replace('.', '', $number);
      }

      $number = str_replace(',', '.', $number);

      return floatval($number);
    }

    public static function formataValor($valor): string
    {
      return number_format((float) $valor, 2, ',', '.');
    }

    public static function classSaldo($valor): string
    {
      $valor = (float) $valor;

      if ($valor > 0) {
        return 'text-success';
      }

      if ($valor < 0) {
        return 'text-danger';
      }

      return 'text-phsa';
    }

    public static function gerarAssinatura(string $nome, array $modo = []): string
    {
      $partes = preg_split('/\s+/', trim($nome));
      $total = count($partes);

      if (empty($modo)) {
        return implode(' ', array_slice($partes, 0, min(2, $total)));
      }

      if (in_array('abreviado', $modo)) {
        if ($total === 1) {
          return $partes[0];
        }

        $primeiro = $partes[0];
        $iniciais = '';

        for ($i = 1; $i < $total; $i++) {
          $iniciais .= strtoupper(substr($partes[$i], 0, 1));
        }

        return "{$primeiro} {$iniciais}";
      }

      $saida = [];

      if (in_array('primeiro', $modo)) {
        $saida[] = $partes[0];
      }

      if (in_array('segundo', $modo) && isset($partes[1])) {
        $saida[] = $partes[1];
      }

      if (in_array('ultimo', $modo) && $total > 1) {
        $saida[] = $partes[$total - 1];
      }

      return implode(' ', $saida) ?: $nome;
    }
  # Manipulador de string

  private static function retornoValidacao(SimpleResponse $response, bool $validate, bool $status)
  {
    if (!$validate) {
      $response->noDestroy();
      return $status;
    }

    return $response->validate();
  }

  # Validate CNPJ CPF
    public static function validarCpf($cpf, $validate = true)
    {
      $cpf = self::somenteNumeros($cpf);

      if (strlen($cpf) != 11) {
        $response = (new SimpleResponse("ATENÇÃO", "CPF deve ter 11 caracteres"))->erro();
        return self::retornoValidacao($response, $validate, false);
      }

      if (preg_match('/^(.)\1{10}$/', $cpf)) {
        $response = (new SimpleResponse("ATENÇÃO", "CPF não pode ter todos os números iguais"))->erro();
        return self::retornoValidacao($response, $validate, false);
      }

      $soma = 0;

      for ($i = 0; $i < 9; $i++) {
        $soma += $cpf[$i] * (10 - $i);
      }

      $resto = $soma % 11;
      $digito1 = $resto < 2 ? 0 : 11 - $resto;

      $soma = 0;

      for ($i = 0; $i < 10; $i++) {
        $soma += $cpf[$i] * (11 - $i);
      }

      $resto = $soma % 11;
      $digito2 = $resto < 2 ? 0 : 11 - $resto;

      if ($cpf[9] == $digito1 && $cpf[10] == $digito2) {
        $response = (new SimpleResponse("ATENÇÃO", "CPF válido: $cpf"))->sucesso();
        return self::retornoValidacao($response, $validate, true);
      }

      $response = (new SimpleResponse("ATENÇÃO", "CPF inválido: $cpf"))->erro();
      return self::retornoValidacao($response, $validate, false);
    }

    public static function validarCnpj($cnpj, $validate = true)
    {
      $cnpj = self::somenteNumeros($cnpj);

      if (strlen($cnpj) != 14) {
        $response = (new SimpleResponse("ATENÇÃO", "CNPJ deve ter 14 caracteres"))->erro();
        return self::retornoValidacao($response, $validate, false);
      }

      if (preg_match('/^(.)\1{13}$/', $cnpj)) {
        $response = (new SimpleResponse("ATENÇÃO", "CNPJ não pode ter todos os números iguais"))->erro();
        return self::retornoValidacao($response, $validate, false);
      }

      $soma = 0;
      $peso = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

      for ($i = 0; $i < 12; $i++) {
        $soma += $cnpj[$i] * $peso[$i];
      }

      $resto = $soma % 11;
      $digito1 = $resto < 2 ? 0 : 11 - $resto;

      $soma = 0;
      $peso = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

      for ($i = 0; $i < 13; $i++) {
        $soma += $cnpj[$i] * $peso[$i];
      }

      $resto = $soma % 11;
      $digito2 = $resto < 2 ? 0 : 11 - $resto;

      if ($cnpj[12] == $digito1 && $cnpj[13] == $digito2) {
        $response = (new SimpleResponse("ATENÇÃO", "CNPJ válido: $cnpj"))->sucesso();
        return self::retornoValidacao($response, $validate, true);
      }

      $response = (new SimpleResponse("ATENÇÃO", "CNPJ inválido: $cnpj"))->erro();
      return self::retornoValidacao($response, $validate, false);
    }

    public static function validarCnpjCpf($cpfcnpj, $validate = true)
    {
      $cpfcnpj = self::somenteNumeros($cpfcnpj);

      if (strlen($cpfcnpj) == 11) {
        return self::validarCpf($cpfcnpj, $validate);
      }

      if (strlen($cpfcnpj) == 14) {
        return self::validarCnpj($cpfcnpj, $validate);
      }

      $response = (new SimpleResponse("ATENÇÃO", "Documento CNPJ/CPF inválido: $cpfcnpj"))->erro();
      return $validate ? $response->validate() : false;
    }
  # Validate CNPJ CPF

  # MASK
    public static function mask($val, $mask)
    {
      if( is_null($val) || empty($val) ) return $val;

      $val = (string) $val;
      $maskared = '';
      $k = 0;

      for ($i = 0; $i <= strlen($mask) - 1; $i++) {
        if ($mask[$i] == '#') {
          if (isset($val[$k])) {
            $maskared .= $val[$k++];
          }
        } else {
          if (isset($mask[$i])) {
            $maskared .= $mask[$i];
          }
        }
      }

      return $maskared;
    }

    public static function adicionaMascaraCPF($cpfSemMascara)
    {
      return self::mask(self::somenteNumeros($cpfSemMascara), "###.###.###-##");
    }

    public static function adicionaMascaraCNPJ($cnpjSemMascara)
    {
      return self::mask(self::somenteNumeros($cnpjSemMascara), "##.###.###/####-##");
    }

    public static function adicionaMascaraCEP($cepMascara)
    {
      return self::mask(self::somenteNumeros($cepMascara), "#####-###");
    }

    public static function adicionaMascaraCnpjCPf($cnpjcpfInformado)
    {
      if( is_null($cnpjcpfInformado) || empty($cnpjcpfInformado) ) return $cnpjcpfInformado;

      $cnpjcpf = self::somenteNumeros($cnpjcpfInformado);

      if (strlen($cnpjcpf) == 11) {
        return self::adicionaMascaraCPF($cnpjcpf);
      }

      if (strlen($cnpjcpf) == 14) {
        return self::adicionaMascaraCNPJ($cnpjcpf);
      }

      return $cnpjcpf;
    }

    public static function adicionaMascaraContato($contato)
    {
      if( is_null($contato) || empty($contato) ) return $contato;

      $contato = self::somenteNumeros($contato);

      switch (strlen($contato)) {
        case '11':
          return self::mask($contato, "(##) # ####-####");

        case '10':
          return self::mask($contato, "(##) ####-####");

        default:
          return $contato;
      }
    }
  # MASK

  # PERMISSOES
    public static function validarPermissao(string|array $permissao, int $perNivel = 1, ?int $usuId = null): object
    {
      $usuarioLogado = self::usuarioLogado();

      if (!$usuarioLogado) {
        return (new SimpleResponse("Erro", "Sessão do usuário não encontrada"))->erro()->noDestroy();
      }

      if (!is_null($usuId) && (int) $usuId !== (int) ($usuarioLogado->usu_id ?? 0)) {
        return (new SimpleResponse("Erro", "Não foi possível validar permissão de outro usuário pelo contexto atual"))->erro()->noDestroy();
      }

      $arrPermissao = (array) ($usuarioLogado->permissoes ?? []);
      $permissoes = (array) $permissao;

      foreach ($permissoes as $per) {
        if (array_key_exists($per, $arrPermissao) && (int) $arrPermissao[$per] >= $perNivel) {
          return (object) [
            'status' => true,
            'text' => "Permissão concedida!",
            'per_nome' => $per,
            'per_descricao' => "Permissão concedida pelo usuário logado.",
            'per_nivel' => $perNivel,
            'usu_id' => (int) $usuarioLogado->usu_id
          ];
        }
      }

      return (object) [
        'status' => false,
        'text' => "Permissão não encontrada ou insuficiente!",
        'per_nome' => implode(',', $permissoes),
        'per_descricao' => "Nenhuma permissão informada foi concedida no nível exigido.",
        'per_nivel' => $perNivel,
        'usu_id' => (int) ($usuarioLogado->usu_id ?? 0)
      ];
    }

    public static function getPermissao(string $perNome, int $perNivel = 1, ?int $usuId = null): object
    {
      $usuarioLogado = self::usuarioLogado();

      if (!$usuarioLogado) {
        return (new SimpleResponse("Erro", "Sessão do usuário não encontrada"))->erro()->noDestroy();
      }

      if (!is_null($usuId) && (int) $usuId !== (int) ($usuarioLogado->usu_id ?? 0)) {
        return (new SimpleResponse("Erro", "Não foi possível buscar permissão de outro usuário pelo contexto atual"))->erro()->noDestroy();
      }

      $arrPermissao = (array) ($usuarioLogado->permissoes ?? []);
      $nivel = array_key_exists($perNome, $arrPermissao) ? (int) $arrPermissao[$perNome] : 0;

      return (object) [
        'status' => $nivel >= $perNivel,
        'text' => $nivel >= $perNivel ? "Permissão encontrada!" : "Permissão não encontrada ou insuficiente!",
        'per_nome' => $perNome,
        'per_descricao' => $nivel >= $perNivel ? "Permissão concedida pelo usuário logado." : "Permissão não concedida no nível exigido.",
        'per_nivel' => $nivel,
        'usu_id' => (int) ($usuarioLogado->usu_id ?? 0)
      ];
    }

    public static function pode(string|array $permissao, int $perNivel = 1): bool
    {
      return self::validarPermissao($permissao, $perNivel)->status;
    }
  # PERMISSOES

  # Pegar dia da semana
    public static function getDiaSemana($data = null): string
    {
      if (is_null($data)) {
        $data = date('Y-m-d');
      }

      $timestamp = !is_numeric($data) ? strtotime($data) : $data;

      if (!$timestamp) {
        return '';
      }

      $diasDaSemana = [
        'Domingo',
        'Segunda-feira',
        'Terça-feira',
        'Quarta-feira',
        'Quinta-feira',
        'Sexta-feira',
        'Sábado'
      ];

      $numeroDiaSemana = date('w', $timestamp);

      return $diasDaSemana[$numeroDiaSemana];
    }
  # Pegar dia da semana

  # Usuário
    public static function getFotoPerfil(?int $usu_id = null): string
    {
      $usuario = self::usuarioLogado();

      if (!$usuario) {
        return self::url('storage/global/semperfil.png');
      }

      if (!is_null($usu_id) && (int) $usu_id !== (int) $usuario->usu_id) {
        return self::url('storage/global/semperfil.png');
      }

      return self::storage($usuario->usu_logo ?? 'storage/global/semperfil.png', 'storage/global/semperfil.png');
    }

    public static function getUsuarioNome(?int $usu_id = null): string
    {
      $usuario = self::usuarioLogado();

      if (!$usuario) {
        return 'Usuário não informado';
      }

      if (!is_null($usu_id) && (int) $usu_id !== (int) $usuario->usu_id) {
        return 'Usuário não encontrado';
      }

      return $usuario->usu_nomefantasia ?: ($usuario->usu_razaosocial ?: 'Usuário não encontrado');
    }
  # Usuário

  # Organização
    public static function getOrganizacaoNome(): string
    {
      $organizacao = self::organizacaoLogada();

      if (!$organizacao) {
        return 'Organização não informada';
      }

      return $organizacao->org_nomefantasia ?: ($organizacao->org_razaosocial ?: 'Organização não encontrada');
    }

    public static function getOrganizacaoLogo(): string
    {
      $organizacao = self::organizacaoLogada();

      return self::storage($organizacao->org_logo ?? null, 'storage/global/semimagem.jpg');
    }
  # Organização

  # Empresa
    public static function getEmpresaNome(): string
    {
      $empresa = self::empresaLogada();

      if (!$empresa) {
        return 'Empresa não informada';
      }

      return $empresa->emp_nomefantasia ?: ($empresa->emp_razaosocial ?: 'Empresa não encontrada');
    }

    public static function getEmpresaLogo(): string
    {
      $empresa = self::empresaLogada();

      return self::storage($empresa->emp_logo ?? null, 'storage/global/semimagem.jpg');
    }
  # Empresa

  # Date
    public static function calcularQuantoTempo($dateTime = null, $negativarFuturo = true)
    {
      if (is_null($dateTime)) {
        return '';
      }

      try {
        $dataAlvo = new \DateTime($dateTime);
        $agora = new \DateTime();
        $diffSegundos = $agora->getTimestamp() - $dataAlvo->getTimestamp();

        if (!$negativarFuturo && $diffSegundos < 0) {
          return 'hoje';
        }

        $segundos = max(60, abs($diffSegundos));
        $minutos = floor($segundos / 60);
        $horas = floor($minutos / 60);
        $dias = floor($horas / 24);

        if ($minutos < 60) {
          return '1 minuto';
        }

        if ($horas < 12) {
          return "{$horas} hora" . ($horas > 1 ? 's' : '');
        }

        if ($horas < 24) {
          return 'hoje';
        }

        if ($horas < 36) {
          return 'ontem';
        }

        if ($horas < 48) {
          return '1 dia';
        }

        if ($dias < 7) {
          return "{$dias} dia" . ($dias > 1 ? 's' : '');
        }

        $semanas = floor($dias / 7);

        return "{$semanas} semana" . ($semanas > 1 ? 's' : '');
      } catch (\Exception $e) {
        return '';
      }
    }

    public static function calcularIdade(?string $dataNasc): object
    {
      $ret = (object) [
        'idade' => null,
        'anos' => null,
        'meses' => null,
        'dias' => null,
        'aniversarioHoje' => false,
        'dataFormatada' => null
      ];

      if (empty($dataNasc) || $dataNasc === '0000-00-00') {
        return $ret;
      }

      try {
        $nasc = new \DateTime($dataNasc);
        $hoje = new \DateTime('today');

        if ($nasc > $hoje) {
          $ret->idade = $ret->anos = 0;
          $ret->meses = $ret->dias = 0;
          $ret->aniversarioHoje = false;
          $ret->dataFormatada = $nasc->format('d/m/Y');

          return $ret;
        }

        $diff = $nasc->diff($hoje);
        $ret->anos = (int) $diff->y;
        $ret->meses = (int) $diff->m;
        $ret->dias = (int) $diff->d;
        $ret->idade = $ret->anos;
        $ret->aniversarioHoje = ($diff->m === 0 && $diff->d === 0);
        $ret->dataFormatada = $nasc->format('d/m/Y');

        return $ret;
      } catch (\Exception $e) {
        return $ret;
      }
    }

    public static function formataDataHora($data): string
    {
      if (empty($data)) {
        return '—';
      }

      $ts = strtotime((string) $data);

      if (!$ts) {
        return '—';
      }

      return date('d/m/Y H:i', $ts);
    }

    public static function formataData($data): string
    {
      if (empty($data)) {
        return '—';
      }

      $ts = strtotime((string) $data);

      if (!$ts) {
        return '—';
      }

      return date('d/m/Y', $ts);
    }
  # Date
}
