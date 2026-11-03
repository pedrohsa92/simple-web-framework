<?php

namespace Source\Core;

use Helpers;

class SimpleUpload
{
  // Uploads públicos, como o cadastro inicial, podem ocorrer sem sessão administrativa.
  private ?object $contexto = null;
  private string $origem;
  private int $idOrigem;
  private string $diretorio;
  private string $nomeArquivo;
  private ?int $idEmpresaStorage;
  private ?int $idOrganizacaoStorage;

  public function __construct(string $origem, int $idOrigem, ?int $idEmpresaStorage = null, ?int $idOrganizacaoStorage = null)
  {
    $this->contexto = Helpers::session()->get('CONTEXTO');
    $this->origem = $this->normalizarSegmento($origem);
    $this->idOrigem = $idOrigem;
    $this->idEmpresaStorage = $idEmpresaStorage;
    $this->idOrganizacaoStorage = $idOrganizacaoStorage;
    $this->diretorio = $this->montarDiretorio();
    $this->nomeArquivo = '';
  }

  public function salvarBase64(string $base64, string $prefixo, ?string $nomeOriginal = null): SimpleResponse
  {
    $arquivo = $this->prepararBase64($base64);

    if (!$arquivo->status) {
      return (new SimpleResponse('Erro', $arquivo->text))
        ->erro()
        ->setArrayParams([
          'debug' => $arquivo
        ])
        ->return();
    }

    $validarDestino = $this->validarDestino();

    if (!$validarDestino->status) {
      return $validarDestino;
    }

    $this->criarDiretorio();

    $prefixo = $this->normalizarSegmento($prefixo);
    $hash = hash('sha256', $arquivo->conteudo);
    $tamanho = $this->formatarTamanho(strlen($arquivo->conteudo));

    do {
      $this->nomeArquivo = $prefixo . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $arquivo->extensao;
      $caminhoCompleto = $this->diretorio . $this->nomeArquivo;
    } while (file_exists($caminhoCompleto));

    if (!file_put_contents($caminhoCompleto, $arquivo->conteudo)) {
      return (new SimpleResponse('Erro', 'Erro ao salvar o arquivo.'))
        ->erro()
        ->setArrayParams([
          'arq_diretorio' => $this->diretorio,
          'arq_nomearquivo' => $this->nomeArquivo,
          'arq_caminho' => $caminhoCompleto
        ])
        ->return();
    }

    return (new SimpleResponse('Sucesso', 'Arquivo salvo com sucesso.'))
      ->sucesso()
      ->setArrayParams([
        'arq_nome' => $nomeOriginal,
        'arq_nomeoriginal' => $nomeOriginal,
        'arq_tipo' => $arquivo->tipo,
        'arq_mime' => $arquivo->mime,
        'arq_extensao' => $arquivo->extensao,
        'arq_tamanho_bytes' => strlen($arquivo->conteudo),
        'arq_tamanho' => $tamanho->tamanho,
        'arq_medida' => $tamanho->medida,
        'arq_largura' => null,
        'arq_altura' => null,
        'arq_duracao' => null,
        'arq_storage' => 'local',
        'arq_diretorio' => $this->diretorio,
        'arq_nomearquivo' => $this->nomeArquivo,
        'arq_caminho' => $caminhoCompleto,
        'arq_hash' => $hash,
        'arq_idorganizacao' => $this->getOrganizacaoId(),
        'arq_idempresa' => $this->getEmpresaId(),
        'arq_idorigem' => $this->idOrigem,
        'arq_origem' => $this->origem
      ])
      ->return();
  }

  public function moverParaLixeira(string $caminho): SimpleResponse
  {
    $caminho = trim($caminho);

    if ($caminho === '') {
      return (new SimpleResponse('Atenção', 'Arquivo não informado.'))
        ->warning()
        ->return();
    }

    if (!file_exists($caminho)) {
      return (new SimpleResponse('Atenção', 'Arquivo não encontrado.'))
        ->warning()
        ->setArrayParams([
          'arq_caminho' => $caminho
        ])
        ->return();
    }

    if (strpos($caminho, 'storage/global') === 0) {
      return (new SimpleResponse('Atenção', 'Arquivo global não pode ser movido para lixeira.'))
        ->warning()
        ->setArrayParams([
          'arq_caminho' => $caminho
        ])
        ->return();
    }

    $diretorioLixeira = 'storage/lixeira/';

    if (!is_dir($diretorioLixeira)) {
      mkdir($diretorioLixeira, 0777, true);
    }

    $nomeArquivo = basename($caminho);
    $origemArquivo = str_replace('/', '--', dirname($caminho));
    $novoCaminho = $diretorioLixeira . date('YmdHis_') . $origemArquivo . '--' . $nomeArquivo;

    if (!rename($caminho, $novoCaminho)) {
      return (new SimpleResponse('Erro', 'Erro ao mover arquivo para lixeira.'))
        ->erro()
        ->setArrayParams([
          'arq_caminho' => $caminho
        ])
        ->return();
    }

    return (new SimpleResponse('Sucesso', 'Arquivo movido para lixeira.'))
      ->sucesso()
      ->setArrayParams([
        'arq_caminho_original' => $caminho,
        'arq_caminho_lixeira' => $novoCaminho
      ])
      ->return();
  }

  public function deletarArquivo(string $caminho): SimpleResponse
  {
    $caminho = trim($caminho);

    if ($caminho === '') {
      return (new SimpleResponse('Atenção', 'Arquivo não informado.'))
        ->warning()
        ->return();
    }

    if (!file_exists($caminho)) {
      return (new SimpleResponse('Atenção', 'Arquivo não encontrado.'))
        ->warning()
        ->setArrayParams([
          'arq_caminho' => $caminho
        ])
        ->return();
    }

    if (strpos($caminho, 'storage/global') === 0) {
      return (new SimpleResponse('Atenção', 'Arquivo global não pode ser deletado.'))
        ->warning()
        ->setArrayParams([
          'arq_caminho' => $caminho
        ])
        ->return();
    }

    if (!unlink($caminho)) {
      return (new SimpleResponse('Erro', 'Erro ao deletar arquivo.'))
        ->erro()
        ->setArrayParams([
          'arq_caminho' => $caminho
        ])
        ->return();
    }

    return (new SimpleResponse('Sucesso', 'Arquivo deletado com sucesso.'))
      ->sucesso()
      ->setArrayParams([
        'arq_caminho' => $caminho
      ])
      ->return();
  }

  private function prepararBase64(string $base64): object
  {
    $partes = explode(',', $base64, 2);
    $meta = $partes[0] ?? '';
    $conteudo = $partes[1] ?? '';

    if ($meta === '' || $conteudo === '') {
      return (object) [
        'status' => false,
        'text' => 'Base64 incompleto.'
      ];
    }

    if (!preg_match('/^data:([a-zA-Z0-9\.\+\-]+\/[a-zA-Z0-9\.\+\-]+);base64$/', $meta, $matches)) {
      return (object) [
        'status' => false,
        'text' => 'Formato base64 inválido.'
      ];
    }

    $conteudoDecodificado = base64_decode($conteudo, true);

    if ($conteudoDecodificado === false) {
      return (object) [
        'status' => false,
        'text' => 'Conteúdo base64 inválido.'
      ];
    }

    $mime = strtolower($matches[1]);

    return (object) [
      'status' => true,
      'text' => 'Base64 válido.',
      'mime' => $mime,
      'tipo' => explode('/', $mime)[0] ?? null,
      'extensao' => $this->getExtensaoByMime($mime),
      'conteudo' => $conteudoDecodificado
    ];
  }

  private function montarDiretorio(): string
  {
    if ($this->origem === 'projeto') {
      return 'storage/projeto/' . $this->idOrigem . '/';
    }

    $base = 'storage/' . $this->getOrganizacaoId() . '_organizacao/';

    if ($this->origem === 'organizacao') {
      return $base . 'organizacao/';
    }

    $baseEmpresa = $base . $this->getEmpresaId() . '_empresa/';

    if ($this->origem === 'empresa') {
      return $baseEmpresa . 'empresa/';
    }

    if ($this->origem === 'usuario') {
      return $baseEmpresa . $this->idOrigem . '_usuario/';
    }

    return $baseEmpresa . $this->origem . '/' . $this->idOrigem . '/';
  }

  private function criarDiretorio(): void
  {
    if (!is_dir($this->diretorio)) {
      mkdir($this->diretorio, 0777, true);
    }
  }

  private function getOrganizacaoId(): int
  {
    if (!is_null($this->idOrganizacaoStorage)) {
      return (int) $this->idOrganizacaoStorage;
    }

    return (int) ($this->contexto->organizacao->org_id ?? 0);
  }

  private function getEmpresaId(): int
  {
    if ($this->origem === 'organizacao') {
      return 0;
    }

    if ($this->origem === 'empresa') {
      return !is_null($this->idEmpresaStorage)
        ? (int) $this->idEmpresaStorage
        : $this->idOrigem;
    }

    if (!is_null($this->idEmpresaStorage)) {
      return (int) $this->idEmpresaStorage;
    }

    return (int) ($this->contexto->empresa->emp_id ?? 0);
  }

  private function validarDestino(): SimpleResponse
  {
    if ($this->origem === 'projeto') {
      if ($this->idOrigem <= 0) {
        return (new SimpleResponse('Erro', 'Registro de projeto de destino nÃ£o identificado.'))
          ->erro()
          ->return();
      }

      return (new SimpleResponse('ValidaÃ§Ã£o', 'Destino de arquivo vÃ¡lido.'))
        ->sucesso()
        ->return();
    }

    if ($this->getOrganizacaoId() <= 0) {
      return (new SimpleResponse('Erro', 'Organização de destino do arquivo não identificada.'))
        ->erro()
        ->return();
    }

    if ($this->origem !== 'organizacao' && $this->getEmpresaId() <= 0) {
      return (new SimpleResponse('Erro', 'Empresa de destino do arquivo não identificada.'))
        ->erro()
        ->return();
    }

    if ($this->origem !== 'organizacao' && $this->idOrigem <= 0) {
      return (new SimpleResponse('Erro', 'Registro de origem do arquivo não identificado.'))
        ->erro()
        ->return();
    }

    return (new SimpleResponse('Validação', 'Destino de arquivo válido.'))
      ->sucesso()
      ->return();
  }

  private function normalizarSegmento(string $valor): string
  {
    $valor = strtolower(trim($valor));
    $valor = preg_replace('/[^a-z0-9_-]/', '', $valor);

    return $valor ?: 'arquivo';
  }

  private function getExtensaoByMime(string $mime): string
  {
    $mapa = [
      'image/jpeg' => 'jpg',
      'image/jpg' => 'jpg',
      'image/png' => 'png',
      'image/gif' => 'gif',
      'image/webp' => 'webp',
      'image/svg+xml' => 'svg',
      'application/pdf' => 'pdf',
      'text/plain' => 'txt',
      'text/csv' => 'csv',
      'application/json' => 'json',
      'application/xml' => 'xml',
      'text/xml' => 'xml',
      'application/vnd.ms-excel' => 'xls',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
      'application/msword' => 'doc',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
      'application/vnd.ms-powerpoint' => 'ppt',
      'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
      'application/zip' => 'zip',
      'application/x-zip-compressed' => 'zip',
      'video/mp4' => 'mp4',
      'video/webm' => 'webm',
      'audio/mpeg' => 'mp3',
      'audio/wav' => 'wav'
    ];

    if (isset($mapa[$mime])) {
      return $mapa[$mime];
    }

    $partes = explode('/', $mime);
    $extensao = end($partes);

    return preg_replace('/[^a-z0-9]/', '', strtolower($extensao ?: 'bin'));
  }

  private function formatarTamanho(int $bytes): object
  {
    $unidades = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

    if ($bytes <= 0) {
      return (object) [
        'tamanho' => '0',
        'medida' => 'Bytes'
      ];
    }

    $indice = (int) floor(log($bytes, 1024));
    $indice = min($indice, count($unidades) - 1);

    return (object) [
      'tamanho' => number_format($bytes / pow(1024, $indice), 2, '.', ''),
      'medida' => $unidades[$indice]
    ];
  }
}
