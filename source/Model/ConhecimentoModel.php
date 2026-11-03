<?php

namespace Source\Model;

use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;
use Source\Core\SimpleUpload;

class ConhecimentoModel extends SimpleModel
{
  protected string $tabela = 'conhecimento';
  protected string $prefixo = 'con';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'nenhum';
  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getConhecimentos(string $pesquisa = ''): object
  {
    $pesquisa = trim($pesquisa);
    $where = [];
    $params = [];

    if ($pesquisa !== '') {
      $where[] = '(
        con.con_titulo LIKE :pesquisa_titulo
        OR con.con_descricao_resumida LIKE :pesquisa_descricao
        OR CAST(con.con_tags AS CHAR) LIKE :pesquisa_tags
        OR cct.cct_nome LIKE :pesquisa_categoria
      )';
      $like = '%' . $pesquisa . '%';
      $params = [
        'pesquisa_titulo' => $like,
        'pesquisa_descricao' => $like,
        'pesquisa_tags' => $like,
        'pesquisa_categoria' => $like
      ];
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    return $this->rawQuery(
      "SELECT
         con.*,
         cct.cct_nome AS con_categoria_nome
       FROM {$this->getTable()} con
       INNER JOIN " . DB_PREFIX . "_conhecimento_categoria cct
         ON cct.cct_id = con.con_idcategoria
       {$whereSql}
       ORDER BY cct.cct_ordem ASC, cct.cct_nome ASC, con.con_ordem ASC, con.con_titulo ASC",
      $params,
      true,
      'Conhecimentos encontrados com sucesso||Erro ao buscar conhecimentos'
    );
  }


  public function contarConhecimentos(): object
  {
    return $this->rawQuery(
      "SELECT COUNT(*) AS total FROM {$this->getTable()}",
      [],
      true,
      'Total de conhecimentos consultado||Erro ao consultar o total de conhecimentos'
    );
  }

  public function pesquisarConhecimentos(array $filtros): object
  {
    $pesquisa = trim((string) ($filtros['lbLik'] ?? ''));
    $status = trim((string) ($filtros['con_status'] ?? ''));
    $tipoFiltro = trim((string) ($filtros['lbTpf'] ?? ''));
    $campoFiltro = trim((string) ($filtros['lbCmf'] ?? ''));
    $dataInicio = trim((string) ($filtros['lbIni'] ?? ''));
    $dataFim = trim((string) ($filtros['lbFim'] ?? ''));
    $campoOrdem = trim((string) ($filtros['lbOrd'] ?? ''));
    $direcao = strtoupper(trim((string) ($filtros['lbOby'] ?? 'ASC'))) === 'DESC' ? 'DESC' : 'ASC';
    $limiteInformado = trim((string) ($filtros['lbLmt'] ?? '100'));

    $params = [];
    $where = [];
    if ($pesquisa !== '') {
      $where[] = '(con.con_id LIKE :busca_id OR con.con_titulo LIKE :busca_titulo
        OR con.con_descricao_resumida LIKE :busca_descricao
        OR CAST(con.con_tags AS CHAR) LIKE :busca_tags OR categoria.cct_nome LIKE :busca_categoria)';
      $busca = '%' . $pesquisa . '%';
      $params += [
        'busca_id' => $busca,
        'busca_titulo' => $busca,
        'busca_descricao' => $busca,
        'busca_tags' => $busca,
        'busca_categoria' => $busca
      ];
    }

    if (in_array($status, ['Ativo', 'Inativo'], true)) {
      $where[] = 'con.con_status = :status';
      $params['status'] = $status;
    }

    $camposData = ['con_dtcad' => 'con.con_dtcad', 'con_dtalter' => 'con.con_dtalter'];
    if (isset($camposData[$campoFiltro]) && $dataInicio !== '') {
      $campoData = $camposData[$campoFiltro];
      if ($tipoFiltro === 'Periodo' && $dataFim !== '') {
        $where[] = "DATE({$campoData}) BETWEEN :data_inicio AND :data_fim";
        $params['data_inicio'] = $dataInicio;
        $params['data_fim'] = $dataFim;
      } elseif ($tipoFiltro === 'Exatidao') {
        $where[] = "DATE({$campoData}) = :data_inicio";
        $params['data_inicio'] = $dataInicio;
      }
    }

    $camposOrdem = [
      'con_id' => 'con.con_id',
      'con_titulo' => 'con.con_titulo',
      'con_status' => 'con.con_status',
      'con_ordem' => 'con.con_ordem',
      'con_dtcad' => 'con.con_dtcad',
      'con_dtalter' => 'con.con_dtalter',
      'cct_nome' => 'categoria.cct_nome'
    ];
    $ordenacao = $camposOrdem[$campoOrdem] ?? 'con.con_titulo';
    $limite = $limiteInformado === 'all' ? 5000 : (int) $limiteInformado;
    $limite = max(1, min($limite, 5000));
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    return $this->rawQuery(
      "SELECT con.*, categoria.cct_nome AS con_categoria_nome
       FROM {$this->getTable()} con
       INNER JOIN " . DB_PREFIX . "_conhecimento_categoria categoria
         ON categoria.cct_id = con.con_idcategoria
       {$whereSql}
       ORDER BY {$ordenacao} {$direcao}, con.con_id ASC
       LIMIT {$limite}",
      $params,
      true,
      'Conhecimentos encontrados com sucesso||Erro ao pesquisar conhecimentos'
    );
  }


  /** Conteúdos publicados para consulta na Central de ajuda. */
  public function getConteudosAjuda(string $pesquisa = '', int $categoriaId = 0, int $conhecimentoId = 0): object
  {
    $where = [
      "con.con_status = 'Ativo'",
      "categoria.cct_status = 'Ativo'"
    ];
    $params = [];

    if ($pesquisa !== '') {
      $where[] = '(con.con_titulo LIKE :titulo OR con.con_descricao_resumida LIKE :descricao OR CAST(con.con_tags AS CHAR) LIKE :tags OR categoria.cct_nome LIKE :categoria)';
      $like = '%' . trim($pesquisa) . '%';
      $params += [
        'titulo' => $like,
        'descricao' => $like,
        'tags' => $like,
        'categoria' => $like
      ];
    }

    if ($categoriaId > 0) {
      $where[] = 'categoria.cct_id = :categoria_id';
      $params['categoria_id'] = $categoriaId;
    }

    if ($conhecimentoId > 0) {
      $where[] = 'con.con_id = :con_id';
      $params['con_id'] = $conhecimentoId;
    }

    return $this->rawQuery(
      "SELECT con.*, categoria.cct_nome AS con_categoria_nome,
         categoria.cct_id AS con_categoria_idcategoria
       FROM {$this->getTable()} con
       INNER JOIN " . DB_PREFIX . "_conhecimento_categoria categoria ON categoria.cct_id = con.con_idcategoria
       WHERE " . implode(' AND ', $where) . "
       ORDER BY categoria.cct_ordem ASC, categoria.cct_nome ASC, con.con_ordem ASC, con.con_titulo ASC, con.con_id ASC",
      $params,
      true,
      'Conteúdos da Central de ajuda encontrados||Erro ao buscar conteúdos da Central de ajuda'
    );
  }

  public function getTopicosNavegacaoAjuda(): object
  {
    return $this->rawQuery(
      "SELECT con.con_id, con.con_titulo, con.con_idcategoria, con.con_ordem
       FROM {$this->getTable()} con
       INNER JOIN " . DB_PREFIX . "_conhecimento_categoria categoria
         ON categoria.cct_id = con.con_idcategoria
       WHERE con.con_status = 'Ativo'
         AND categoria.cct_status = 'Ativo'
       ORDER BY categoria.cct_ordem ASC, categoria.cct_nome ASC,
         con.con_ordem ASC, con.con_titulo ASC, con.con_id ASC",
      [],
      true,
      'Sequência de conhecimentos encontrada||Erro ao buscar sequência de conhecimentos'
    );
  }

  public function registrarLeituraAjuda(int $conhecimentoId, bool $incrementar = true): object
  {
    $categoriaTabela = DB_PREFIX . '_conhecimento_categoria';
    $tabela = $this->getTable();

    if ($incrementar) {
      $atualizacao = $this->rawQuery(
        "UPDATE {$tabela} con
         INNER JOIN {$categoriaTabela} categoria ON categoria.cct_id = con.con_idcategoria
         SET con.con_visualizacoes = con.con_visualizacoes + 1,
             con.con_dtalter = con.con_dtalter
         WHERE con.con_id = :con_id
           AND con.con_status = 'Ativo'
           AND categoria.cct_status = 'Ativo'",
        ['con_id' => $conhecimentoId],
        false,
        'Leitura registrada||Erro ao registrar leitura'
      );

      if (!$atualizacao->status) {
        return $atualizacao;
      }
    }

    $resultado = $this->rawQuery(
      "SELECT con.con_visualizacoes AS visualizacoes,
              con.con_dtalter AS atualizado_em
       FROM {$tabela} con
       INNER JOIN {$categoriaTabela} categoria ON categoria.cct_id = con.con_idcategoria
       WHERE con.con_id = :con_id
         AND con.con_status = 'Ativo'
         AND categoria.cct_status = 'Ativo'
       LIMIT 1",
      ['con_id' => $conhecimentoId],
      true,
      'Contagem de leitura consultada||Erro ao consultar contagem de leitura'
    );

    if (!$resultado->status || empty($resultado->data[0])) {
      return (new SimpleResponse('Conhecimento indisponível', 'Este conteúdo não está disponível.'))
        ->warning()
        ->noDestroy();
    }

    return (new SimpleResponse('Leitura registrada', 'A contagem de leitura foi atualizada.'))
      ->sucesso()
      ->setArrayParams(['data' => $resultado->data[0], 'total' => 1])
      ->noDestroy();
  }

  public function getConhecimentoById(int $id): object
  {
    return $this->rawQuery(
      "SELECT
         con.*,
         cct.cct_nome AS con_categoria_nome
       FROM {$this->getTable()} con
       INNER JOIN " . DB_PREFIX . "_conhecimento_categoria cct
         ON cct.cct_id = con.con_idcategoria
       WHERE con.con_id = :con_id
       LIMIT 1",
      ['con_id' => $id],
      true,
      'Conhecimento encontrado com sucesso||Conhecimento não encontrado'
    );
  }

  public function getMidiasByConhecimentoId(int $conhecimentoId, bool $somenteAtivas = false): object
  {
    $filtroStatus = $somenteAtivas ? "AND cmi_status = 'Ativo'" : '';
    $response = $this->rawQuery(
      "SELECT *
       FROM " . DB_PREFIX . "_conhecimento_midia
       WHERE cmi_idconhecimento = :cmi_idconhecimento
         {$filtroStatus}
       ORDER BY cmi_ordem ASC, cmi_id ASC",
      ['cmi_idconhecimento' => $conhecimentoId],
      true,
      'Mídias encontradas com sucesso||Erro ao buscar mídias'
    );

    if ($response->status) {
      foreach ($response->data ?? [] as $midia) {
        $midia->cmi_url_publica = $this->urlMidia($midia);
      }
    }

    return $response;
  }

  public function salvarMidia(array $data): object
  {
    $conhecimentoId = (int) ($data['cmi_idconhecimento'] ?? 0);
    $tipo = trim((string) ($data['cmi_tipo'] ?? ''));
    $nome = trim((string) ($data['cmi_nome'] ?? ''));
    $url = trim((string) ($data['cmi_url'] ?? ''));
    $ordem = max(0, (int) ($data['cmi_ordem'] ?? 0));

    if ($conhecimentoId <= 0 || !$this->conhecimentoExiste($conhecimentoId)) {
      return $this->atencao('Conhecimento não encontrado.');
    }

    if (!in_array($tipo, ['Upload', 'Link'], true)) {
      return $this->atencao('Selecione se a mídia será um upload ou um link.');
    }

    if ($nome === '') {
      return $this->atencao('Informe o nome da mídia.');
    }

    if ($tipo === 'Link' && mb_strlen($nome) < 5) {
      return $this->atencao('O título do link deve ter pelo menos 5 caracteres.');
    }

    $snapshot = $this->normalizarSnapshotMidia($data['cmi_json'] ?? null);

    if ($tipo === 'Link') {
      if (!preg_match('/^https?:\/\/[^\s]+$/i', $url)) {
        return $this->atencao('Informe um link válido para a mídia.');
      }
    } else {
      $upload = new SimpleUpload(
        'projeto',
        1,
        null,
        null,
        'conhecimento/' . $conhecimentoId . '_conhecimento'
      );

      $arquivoUpload = $data['cmi_arquivo'] ?? null;
      if (is_array($arquivoUpload) && !empty($arquivoUpload['tmp_name'])) {
        $nomeArquivoOriginal = trim((string) ($arquivoUpload['name'] ?? ''));
        $uploadResponse = $upload->salvarUpload($arquivoUpload, 'midia', $nomeArquivoOriginal ?: $nome);
      } else {
        // Compatibilidade temporária com chamadas antigas que ainda enviem Base64.
        $base64 = trim((string) ($data['cmi_base64'] ?? ''));
        if (!preg_match('/^data:video\/mp4;base64,/i', $base64)) {
          return $this->atencao('Selecione um vídeo MP4 válido.');
        }
        $uploadResponse = $upload->salvarBase64($base64, 'midia', $nome);
      }

      if (!$uploadResponse->status) {
        return $uploadResponse;
      }

      $url = \Helpers::url((string) ($uploadResponse->arq_caminho ?? ''));
    }

    if ($ordem <= 0) {
      $ordemResponse = $this->rawQuery(
        "SELECT COALESCE(MAX(cmi_ordem), 0) + 1 AS proxima_ordem
         FROM " . DB_PREFIX . "_conhecimento_midia
         WHERE cmi_idconhecimento = :cmi_idconhecimento",
        ['cmi_idconhecimento' => $conhecimentoId],
        true,
        'Ordem calculada com sucesso||Erro ao calcular ordem da mídia'
      );
      $ordem = (int) ($ordemResponse->data[0]->proxima_ordem ?? 1);
    }

    $registro = [
      'cmi_idconhecimento' => $conhecimentoId,
      'cmi_tipo' => $tipo,
      'cmi_status' => 'Ativo',
      'cmi_statusclass' => 'success',
      'cmi_nome' => $nome,
      'cmi_url' => $tipo === 'Link' ? $url : $url,
      'cmi_json' => $snapshot,
      'cmi_ordem' => $ordem
    ];

    $response = $this->rawQuery(
      "INSERT INTO " . DB_PREFIX . "_conhecimento_midia
        (cmi_idconhecimento, cmi_tipo, cmi_status, cmi_statusclass, cmi_nome, cmi_url, cmi_json, cmi_ordem)
       VALUES
        (:cmi_idconhecimento, :cmi_tipo, :cmi_status, :cmi_statusclass, :cmi_nome, :cmi_url, :cmi_json, :cmi_ordem)",
      $registro,
      false,
      'Mídia cadastrada com sucesso||Erro ao cadastrar mídia'
    );

    if ($response->status && !empty($response->lastInsertId)) {
      $midia = $this->getMidiaById((int) $response->lastInsertId, $conhecimentoId);
      if ($midia) {
        $response->data = [$midia];
        $response->total = 1;
      }
    }

    return $response;
  }

  public function salvarMidiaChunk(array $data): object
  {
    $conhecimentoId = (int) ($data['cmi_idconhecimento'] ?? 0);
    $uploadId = strtolower(trim((string) ($data['cmi_chunk_id'] ?? '')));
    $indice = filter_var($data['cmi_chunk_index'] ?? null, FILTER_VALIDATE_INT);
    $totalPartes = filter_var($data['cmi_chunk_total'] ?? null, FILTER_VALIDATE_INT);
    $arquivo = $data['cmi_arquivo'] ?? null;

    if ($conhecimentoId <= 0 || !$this->conhecimentoExiste($conhecimentoId)) {
      return $this->atencao('Conhecimento não encontrado.');
    }

    if (!preg_match('/^[a-f0-9]{32}$/', $uploadId)
      || $indice === false
      || $totalPartes === false
      || $totalPartes < 1
      || $totalPartes > 512
      || $indice < 0
      || $indice >= $totalPartes
    ) {
      return $this->atencao('Dados inválidos para receber o vídeo.');
    }

    if (!is_array($arquivo) || (int) ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      $codigoErro = is_array($arquivo) ? (int) ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) : UPLOAD_ERR_NO_FILE;
      $mensagem = $codigoErro === UPLOAD_ERR_INI_SIZE || $codigoErro === UPLOAD_ERR_FORM_SIZE
        ? 'Uma parte do vídeo excedeu o limite de upload do servidor.'
        : 'Não foi possível receber uma parte do vídeo.';
      return (new SimpleResponse('Falha ao receber o vídeo', $mensagem))
        ->warning()
        ->setArrayParams([
          'debug' => (object) [
            'uploadError' => $codigoErro,
            'contentLength' => (int) ($_SERVER['CONTENT_LENGTH'] ?? 0),
            'contentType' => $_SERVER['CONTENT_TYPE'] ?? null,
            'postMaxSize' => ini_get('post_max_size'),
            'uploadMaxFilesize' => ini_get('upload_max_filesize'),
            'postFieldsReceived' => array_keys($_POST),
            'filesReceived' => array_keys($_FILES)
          ]
        ])
        ->noDestroy();
    }

    $temporario = trim((string) ($arquivo['tmp_name'] ?? ''));
    if ($temporario === '' || !is_file($temporario)) {
      return $this->atencao('A parte recebida do vídeo não foi encontrada.');
    }

    $diretorio = rtrim(sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR
      . 'icliv-conhecimento-uploads' . DIRECTORY_SEPARATOR
      . hash('sha256', session_id() . ':' . $conhecimentoId);

    if (!is_dir($diretorio) && !mkdir($diretorio, 0700, true) && !is_dir($diretorio)) {
      return $this->atencao('Não foi possível preparar o recebimento do vídeo.');
    }

    foreach (glob($diretorio . DIRECTORY_SEPARATOR . '*.part') ?: [] as $parteAntiga) {
      if (is_file($parteAntiga) && filemtime($parteAntiga) < time() - 86400) {
        unlink($parteAntiga);
      }
    }

    $caminhoParte = $diretorio . DIRECTORY_SEPARATOR . $uploadId . '_' . $indice . '.part';
    if (!move_uploaded_file($temporario, $caminhoParte)) {
      return $this->atencao('Não foi possível armazenar uma parte do vídeo.');
    }

    if ($indice < $totalPartes - 1) {
      return (new SimpleResponse('Sucesso', 'Parte do vídeo recebida.'))
        ->sucesso()
        ->setArrayParams([
          'chunk_received' => $indice,
          'chunk_total' => $totalPartes
        ])
        ->return()
        ->noDestroy();
    }

    $caminhoMontado = $diretorio . DIRECTORY_SEPARATOR . $uploadId . '_montado.mp4';
    $destino = fopen($caminhoMontado, 'wb');

    if (!$destino) {
      return $this->atencao('Não foi possível preparar o vídeo recebido.');
    }

    $montagemCompleta = true;
    for ($parte = 0; $parte < $totalPartes; $parte++) {
      $caminhoAtual = $diretorio . DIRECTORY_SEPARATOR . $uploadId . '_' . $parte . '.part';
      $origem = is_file($caminhoAtual) ? fopen($caminhoAtual, 'rb') : false;

      if (!$origem || stream_copy_to_stream($origem, $destino) === false) {
        $montagemCompleta = false;
        if ($origem) fclose($origem);
        break;
      }

      fclose($origem);
    }

    fclose($destino);

    if (!$montagemCompleta || !is_file($caminhoMontado) || filesize($caminhoMontado) <= 0) {
      @unlink($caminhoMontado);
      return $this->atencao('Não foi possível montar todas as partes do vídeo. Reenvie o arquivo.');
    }

    $data['cmi_arquivo'] = [
      'error' => UPLOAD_ERR_OK,
      'tmp_name' => $caminhoMontado,
      'name' => trim((string) ($data['cmi_nomearquivo'] ?? 'video.mp4'))
    ];
    $resposta = $this->salvarMidia($data);

    @unlink($caminhoMontado);
    for ($parte = 0; $parte < $totalPartes; $parte++) {
      @unlink($diretorio . DIRECTORY_SEPARATOR . $uploadId . '_' . $parte . '.part');
    }

    return $resposta;
  }

  public function alterarStatusMidia(array $data): object
  {
    $midiaId = (int) ($data['cmi_id'] ?? 0);
    $conhecimentoId = (int) ($data['cmi_idconhecimento'] ?? 0);
    $status = (string) ($data['cmi_status'] ?? '');

    if ($midiaId <= 0 || $conhecimentoId <= 0 || !in_array($status, ['Ativo', 'Inativo'], true)) {
      return $this->atencao('Dados inválidos para alterar o status da mídia.');
    }

    $midia = $this->getMidiaById($midiaId, $conhecimentoId);
    if (!$midia) {
      return $this->atencao('Mídia não encontrada.');
    }

    $response = $this->rawQuery(
      "UPDATE " . DB_PREFIX . "_conhecimento_midia
       SET cmi_status = :cmi_status,
           cmi_statusclass = :cmi_statusclass
       WHERE cmi_id = :cmi_id
         AND cmi_idconhecimento = :cmi_idconhecimento",
      [
        'cmi_status' => $status,
        'cmi_statusclass' => $status === 'Ativo' ? 'success' : 'danger',
        'cmi_id' => $midiaId,
        'cmi_idconhecimento' => $conhecimentoId
      ],
      false,
      'Status da mídia atualizado com sucesso||Erro ao atualizar status da mídia'
    );

    if ($response->status) {
      $midiaAtualizada = $this->getMidiaById($midiaId, $conhecimentoId);
      if ($midiaAtualizada) {
        $response->data = [$midiaAtualizada];
        $response->total = 1;
      }
    }

    return $response;
  }

  private function conhecimentoExiste(int $id): bool
  {
    $response = $this->rawQuery(
      "SELECT con_id
       FROM {$this->getTable()}
       WHERE con_id = :con_id
       LIMIT 1",
      ['con_id' => $id],
      true,
      'Conhecimento encontrado com sucesso||Conhecimento não encontrado'
    );

    return $response->status && $response->total === 1;
  }

  private function getMidiaById(int $midiaId, int $conhecimentoId): ?object
  {
    $response = $this->rawQuery(
      "SELECT *
       FROM " . DB_PREFIX . "_conhecimento_midia
       WHERE cmi_id = :cmi_id
         AND cmi_idconhecimento = :cmi_idconhecimento
       LIMIT 1",
      [
        'cmi_id' => $midiaId,
        'cmi_idconhecimento' => $conhecimentoId
      ],
      true,
      'Mídia encontrada com sucesso||Mídia não encontrada'
    );

    if (!$response->status || $response->total !== 1) {
      return null;
    }

    $midia = $response->data[0];
    $midia->cmi_url_publica = $this->urlMidia($midia);
    return $midia;
  }

  private function urlMidia(object $midia): string
  {
    $url = trim((string) ($midia->cmi_url ?? ''));
    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL)) {
      return $url;
    }

    return \Helpers::url($url);
  }

  private function normalizarSnapshotMidia($valor): ?string
  {
    if (is_string($valor)) {
      $snapshot = json_decode($valor, true);
    } elseif (is_array($valor)) {
      $snapshot = $valor;
    } else {
      $snapshot = null;
    }

    if (!is_array($snapshot)) {
      return null;
    }

    // O objeto retornado por gerarObjetoArquivo contém o conteúdo em Base64
    // na propriedade data e o File original em arquivo. O snapshot deve guardar
    // apenas os metadados.
    unset($snapshot['data']);
    unset($snapshot['arquivo']);

    return json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
  }

  public function getCategorias(bool $somenteAtivas = false): object
  {
    $where = $somenteAtivas ? "WHERE cct_status = 'Ativo'" : '';

    return $this->rawQuery(
      "SELECT
         cct.*,
         (
           SELECT COUNT(*)
           FROM " . DB_PREFIX . "_conhecimento conhecimento
           WHERE conhecimento.con_idcategoria = cct.cct_id
         ) AS cct_totalconhecimentos
       FROM " . DB_PREFIX . "_conhecimento_categoria cct
       {$where}
       ORDER BY cct.cct_ordem ASC, cct.cct_nome ASC, cct.cct_id ASC",
      [],
      true,
      'Categorias encontradas com sucesso||Erro ao buscar categorias'
    );
  }

  public function getCategoriaById(int $id): object
  {
    return $this->rawQuery(
      "SELECT *
       FROM " . DB_PREFIX . "_conhecimento_categoria
       WHERE cct_id = :cct_id
       LIMIT 1",
      ['cct_id' => $id],
      true,
      'Categoria encontrada com sucesso||Categoria não encontrada'
    );
  }

  public function getCategoriasAjuda(): object
  {
    return $this->rawQuery(
      "SELECT categoria.cct_id, categoria.cct_nome, categoria.cct_ordem,
         (
           SELECT COUNT(*)
           FROM " . DB_PREFIX . "_conhecimento conhecimento
           WHERE conhecimento.con_idcategoria = categoria.cct_id
             AND conhecimento.con_status = 'Ativo'
         ) AS cct_totalconhecimentos
       FROM " . DB_PREFIX . "_conhecimento_categoria categoria
       WHERE categoria.cct_status = 'Ativo'
       ORDER BY categoria.cct_ordem ASC, categoria.cct_nome ASC, categoria.cct_id ASC",
      [],
      true,
      'Categorias de ajuda encontradas||Erro ao buscar categorias de ajuda'
    );
  }

  public function getCategoriasAtivas(): array
  {
    $response = $this->rawQuery(
      "SELECT cct_id, cct_nome, cct_ordem
       FROM " . DB_PREFIX . "_conhecimento_categoria
       WHERE cct_status = 'Ativo'
       ORDER BY cct_ordem ASC, cct_nome ASC, cct_id ASC",
      [],
      true,
      'Categorias de conhecimento encontradas com sucesso||Erro ao buscar categorias de conhecimento'
    );

    return $response->status ? ($response->data ?? []) : [];
  }

  public function salvarConhecimento(array $data): object
  {
    $id = (int) ($data['con_id'] ?? 0);

    // A aba de descrição salva somente seu próprio conteúdo. Assim, uma
    // alteração nela não pode substituir os dados da aba Cadastro.
    $salvarSomenteDescricao = $id > 0
      && array_key_exists('con_conteudo', $data)
      && !array_key_exists('con_titulo', $data);

    if ($salvarSomenteDescricao) {
      $existente = $this->getConhecimentoById($id);
      if (!$existente->status || $existente->total !== 1) {
        return $this->atencao('Conhecimento não encontrado.');
      }

      $conteudo = trim((string) ($data['con_conteudo'] ?? ''));
      $conteudo = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $conteudo) ?? $conteudo;
      $response = $this->update(
        ['con_conteudo' => $conteudo !== '' ? $conteudo : null],
        ['con_id' => $id],
        'Descrição personalizada atualizada com sucesso||Erro ao atualizar descrição personalizada'
      );

      if ($response->status) {
        $response->location = \Helpers::url('projeto/conhecimento/form/' . $id);
      }

      return $response;
    }

    $titulo = trim((string) ($data['con_titulo'] ?? ''));
    $descricao = trim((string) ($data['con_descricao_resumida'] ?? ''));
    $conteudo = trim((string) ($data['con_conteudo'] ?? ''));
    $categoriaId = (int) ($data['con_idcategoria'] ?? 0);
    $ordem = max(0, (int) ($data['con_ordem'] ?? 0));
    $status = (string) ($data['con_status'] ?? 'Ativo');
    $status = in_array($status, ['Ativo', 'Inativo'], true) ? $status : 'Ativo';

    if ($titulo === '') {
      return $this->atencao('Informe o título do conhecimento.');
    }

    if ($categoriaId <= 0 || !$this->categoriaAtiva($categoriaId)) {
      return $this->atencao('Selecione uma categoria ativa para o conhecimento.');
    }

    $duplicado = $this->rawQuery(
      "SELECT con_id
       FROM {$this->getTable()}
       WHERE con_idcategoria = :categoria
         AND con_titulo = :titulo
         AND con_id <> :id
       LIMIT 1",
      ['categoria' => $categoriaId, 'titulo' => $titulo, 'id' => $id],
      true,
      'Validação de título concluída||Erro ao validar título'
    );

    if (!$duplicado->status) {
      return $this->atencao('Não foi possível validar o título do conhecimento. Tente novamente.');
    }
    if ($duplicado->total > 0) {
      return $this->atencao('Já existe um conhecimento com este título nesta categoria.');
    }

    $tags = $this->normalizarTags($data);
    $conteudo = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $conteudo) ?? $conteudo;

    $registro = [
      'con_idcategoria' => $categoriaId,
      'con_status' => $status,
      'con_statusclass' => $status === 'Ativo' ? 'success' : 'danger',
      'con_titulo' => $titulo,
      'con_descricao_resumida' => $descricao !== '' ? $descricao : null,
      'con_conteudo' => $conteudo !== '' ? $conteudo : null,
      'con_tags' => json_encode($tags, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      'con_ordem' => $ordem
    ];

    if ($id > 0) {
      $existente = $this->getConhecimentoById($id);
      if (!$existente->status || $existente->total !== 1) {
        return $this->atencao('Conhecimento não encontrado.');
      }

      $response = $this->update(
        $registro,
        ['con_id' => $id],
        'Conhecimento atualizado com sucesso||Erro ao atualizar conhecimento'
      );
    } else {
      $response = $this->insert(
        $registro,
        'Conhecimento cadastrado com sucesso||Erro ao cadastrar conhecimento'
      );
    }

    if ($response->status) {
      if ($id > 0) {
        // Atualizações não possuem um novo lastInsertId; preserve o ID editado.
        $response->location = \Helpers::url('projeto/conhecimento/form/' . $id);
      } elseif (!empty($response->lastInsertId)) {
        $response->location = \Helpers::url('projeto/conhecimento/form/' . (int) $response->lastInsertId);
      }
    }

    return $response;
  }

  public function salvarCategoria(array $data): object
  {
    $id = (int) ($data['cct_id'] ?? 0);
    $nome = trim((string) ($data['cct_nome'] ?? ''));
    $ordem = max(0, (int) ($data['cct_ordem'] ?? 0));
    $status = (string) ($data['cct_status'] ?? 'Ativo');
    $status = in_array($status, ['Ativo', 'Inativo'], true) ? $status : 'Ativo';

    if ($nome === '') {
      return $this->atencao('Informe o nome da categoria.');
    }

    if ($id > 0) {
      $existente = $this->rawQuery(
        "SELECT cct_id
         FROM " . DB_PREFIX . "_conhecimento_categoria
         WHERE cct_id = :cct_id
         LIMIT 1",
        ['cct_id' => $id],
        true,
        'Categoria encontrada com sucesso||Categoria não encontrada'
      );

      if (!$existente->status || $existente->total !== 1) {
        return $this->atencao('Categoria não encontrada.');
      }
    }

    $duplicada = $this->rawQuery(
      "SELECT cct_id
       FROM " . DB_PREFIX . "_conhecimento_categoria
       WHERE cct_nome = :cct_nome
         AND cct_id != :cct_id
       LIMIT 1",
      [
        'cct_nome' => $nome,
        'cct_id' => $id
      ],
      true,
      'Validação concluída||Erro ao validar categoria'
    );

    if ($duplicada->status && $duplicada->total > 0) {
      return $this->atencao('Esta categoria já está cadastrada.');
    }

    $registro = [
      'cct_status' => $status,
      'cct_statusclass' => $status === 'Ativo' ? 'success' : 'danger',
      'cct_nome' => $nome,
      'cct_ordem' => $ordem
    ];

    if ($id > 0) {
      $response = $this->atualizarCategoria($registro, $id);
    } else {
      $response = $this->inserirCategoria($registro);
    }

    return $response;
  }

  private function atualizarCategoria(array $data, int $id): object
  {
    $respostaAtual = $this->getCategoriaById($id);
    if (!$respostaAtual->status || $respostaAtual->total !== 1) {
      return $this->atencao('Categoria não encontrada.');
    }

    $atual = $respostaAtual->data[0];
    $detalhes = [];
    foreach ([
      'cct_nome' => 'Nome',
      'cct_status' => 'Status',
      'cct_ordem' => 'Ordem de exibição'
    ] as $campo => $rotulo) {
      $valorAnterior = (string) ($atual->{$campo} ?? '');
      $valorNovo = (string) ($data[$campo] ?? '');
      if ($valorAnterior !== $valorNovo) {
        $detalhes[] = $rotulo . ': ' . ($valorAnterior !== '' ? $valorAnterior : '—') . ' → ' . ($valorNovo !== '' ? $valorNovo : '—');
      }
    }

    $logs = json_decode((string) ($atual->cct_log ?? '[]'), true);
    $logs = is_array($logs) ? array_values($logs) : [];
    $log = $this->montarLog('atualizou a categoria');
    if ($detalhes) {
      $log['detalhe'] = $detalhes;
    }
    $logs[] = $log;
    $data['cct_log'] = json_encode(array_slice($logs, -50), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    return $this->rawQuery(
      "UPDATE " . DB_PREFIX . "_conhecimento_categoria
       SET cct_status = :cct_status,
           cct_statusclass = :cct_statusclass,
           cct_nome = :cct_nome,
           cct_ordem = :cct_ordem,
           cct_log = :cct_log
       WHERE cct_id = :cct_id",
      $data + ['cct_id' => $id],
      false,
      'Categoria atualizada com sucesso||Erro ao atualizar categoria'
    );
  }

  private function inserirCategoria(array $data): object
  {
    $data['cct_log'] = json_encode([$this->montarLog('inseriu nova categoria')], JSON_UNESCAPED_UNICODE);

    return $this->rawQuery(
      "INSERT INTO " . DB_PREFIX . "_conhecimento_categoria
       (cct_status, cct_statusclass, cct_nome, cct_ordem, cct_log)
       VALUES (:cct_status, :cct_statusclass, :cct_nome, :cct_ordem, :cct_log)",
      $data,
      false,
      'Categoria cadastrada com sucesso||Erro ao cadastrar categoria'
    );
  }

  private function categoriaAtiva(int $id): bool
  {
    $response = $this->rawQuery(
      "SELECT cct_id
       FROM " . DB_PREFIX . "_conhecimento_categoria categoria
       WHERE cct_id = :cct_id
         AND cct_status = 'Ativo'
       LIMIT 1",
      ['cct_id' => $id],
      true,
      'Categoria encontrada com sucesso||Erro ao validar categoria'
    );

    return $response->status && $response->total === 1;
  }

  public function reordenarCategoria(int $id, string $direcao): object
  {
    $direcao = strtolower(trim($direcao));

    if (!in_array($direcao, ['cima', 'baixo'], true)) {
      return $this->atencao('Direção de ordenação inválida.');
    }

    $atualResponse = $this->rawQuery(
      "SELECT cct_id, cct_ordem
       FROM " . DB_PREFIX . "_conhecimento_categoria
       WHERE cct_id = :cct_id
       LIMIT 1",
      ['cct_id' => $id],
      true,
      'Categoria encontrada com sucesso||Categoria não encontrada'
    );

    if (!$atualResponse->status || $atualResponse->total !== 1) {
      return $this->atencao('Categoria não encontrada.');
    }

    $atual = $atualResponse->data[0];
    $ordem = (int) ($atual->cct_ordem ?? 0);
    $parametros = ['cct_ordem' => $ordem];

    $comparador = $direcao === 'cima' ? '<' : '>';
    $ordenacao = $direcao === 'cima' ? 'DESC' : 'ASC';
    $vizinhoResponse = $this->rawQuery(
      "SELECT cct_id, cct_ordem
       FROM " . DB_PREFIX . "_conhecimento_categoria
       WHERE cct_ordem {$comparador} :cct_ordem
       ORDER BY cct_ordem {$ordenacao}, cct_id {$ordenacao}
       LIMIT 1",
      $parametros,
      true,
      'Vizinho encontrado com sucesso||Erro ao buscar categoria vizinha'
    );

    if (!$vizinhoResponse->status || $vizinhoResponse->total !== 1) {
      return (new SimpleResponse('Ordem', 'Esta categoria já está no limite da lista.'))
        ->info()
        ->noDestroy();
    }

    $vizinho = $vizinhoResponse->data[0];
    $trocaAtual = $this->rawQuery(
      "UPDATE " . DB_PREFIX . "_conhecimento_categoria
       SET cct_ordem = :cct_ordem
       WHERE cct_id = :cct_id",
      [
        'cct_ordem' => (int) ($vizinho->cct_ordem ?? 0),
        'cct_id' => $id
      ],
      false,
      'Ordem atualizada||Erro ao atualizar ordem'
    );

    if (!$trocaAtual->status) {
      return $trocaAtual;
    }

    $trocaVizinho = $this->rawQuery(
      "UPDATE " . DB_PREFIX . "_conhecimento_categoria
       SET cct_ordem = :cct_ordem
       WHERE cct_id = :cct_id",
      [
        'cct_ordem' => $ordem,
        'cct_id' => (int) ($vizinho->cct_id ?? 0)
      ],
      false,
      'Ordem atualizada||Erro ao atualizar ordem'
    );

    if (!$trocaVizinho->status) {
      return $trocaVizinho;
    }

    return (new SimpleResponse('Ordem atualizada', 'A ordem das categorias foi atualizada.'))
      ->sucesso()
      ->noDestroy();
  }

  private function normalizarTags(array $data): array
  {
    $tags = [];
    $json = trim((string) ($data['con_tags'] ?? ''));

    if ($json !== '') {
      $decodificado = json_decode($json, true);
      if (is_array($decodificado)) {
        $tags = $decodificado;
      }
    }

    if (!$tags) {
      for ($indice = 1; $indice <= 3; $indice++) {
        $valor = trim((string) ($data['con_tag_' . $indice] ?? ''));
        if ($valor !== '') {
          $tags[] = $valor;
        }
      }
    }

    $tags = array_map(static fn ($tag): string => trim((string) $tag), $tags);
    $tags = array_values(array_unique(array_filter($tags, static fn ($tag): bool => $tag !== '')));

    return array_slice($tags, 0, 3);
  }

  private function montarLog(string $acao): array
  {
    return [
      'usu_id' => $this->usuarioLogado->usu_id ?? null,
      'usu_razaosocial' => $this->usuarioLogado->usu_razaosocial ?? 'Sistema',
      'acao' => $acao,
      'data' => date('d/m/Y H:i:s')
    ];
  }

  private function atencao(string $texto): object
  {
    return (new SimpleResponse('Atenção', $texto))
      ->warning()
      ->noDestroy();
  }
}


