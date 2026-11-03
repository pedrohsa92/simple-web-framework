<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;
use Source\Service\LimitePlanoService;

class FornecedorModel extends SimpleModel
{
  protected string $tabela = 'fornecedor';
  protected string $prefixo = 'for';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'empresa';
  protected string $campoOrganizacao = 'for_idorganizacao';
  protected string $campoEmpresa = 'for_idempresa';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function contarFornecedoresContexto(bool $somenteAtivos = false): object
  {
    $where = '';
    $params = [];

    if ($somenteAtivos) {
      $where = " AND for_status = 'Ativo'";
    }

    return $this->rawQuery(
      "SELECT COUNT(*) AS total
       FROM {$this->getTable()}
       WHERE for_idorganizacao = :for_idorganizacao
       AND for_idempresa = :for_idempresa
       {$where}",
      [
        'for_idorganizacao' => $this->getOrganizacaoId(),
        'for_idempresa' => $this->getEmpresaId()
      ],
      true,
      'Total de fornecedores encontrado com sucesso||Erro ao contar fornecedores'
    );
  }

  public function getFornecedorById(int $id): object
  {
    if ($id <= 0) {
      return $this->erroFornecedor('Fornecedor nao informado.');
    }

    return $this->rawQuery(
      "SELECT *
       FROM {$this->getTable()}
       WHERE for_id = :for_id
       AND for_idorganizacao = :for_idorganizacao
       AND for_idempresa = :for_idempresa
       LIMIT 1",
      [
        'for_id' => $id,
        'for_idorganizacao' => $this->getOrganizacaoId(),
        'for_idempresa' => $this->getEmpresaId()
      ],
      true,
      'Fornecedor encontrado com sucesso||Erro ao buscar fornecedor'
    );
  }

  public function pesquisarFornecedor(array $filtros): object
  {
    $tipoPesquisa = trim((string) ($filtros['lbTipoPesquisa'] ?? 'inicial'));
    $conteudo = trim((string) ($filtros['lbLik'] ?? ''));
    $status = trim((string) ($filtros['for_status'] ?? ''));
    $tipoFiltro = trim((string) ($filtros['lbTpf'] ?? ''));
    $campoFiltro = trim((string) ($filtros['lbCmf'] ?? ''));
    $dataInicio = trim((string) ($filtros['lbIni'] ?? ''));
    $dataFim = trim((string) ($filtros['lbFim'] ?? ''));
    $campoOrdem = trim((string) ($filtros['lbOrd'] ?? ''));
    $ordem = strtoupper(trim((string) ($filtros['lbOby'] ?? 'ASC')));
    $limiteInformado = trim((string) ($filtros['lbLmt'] ?? '1000'));

    if ($tipoPesquisa === 'simples' && $conteudo !== '' && mb_strlen($conteudo) < 3) {
      return (new SimpleResponse('Atencao', 'Informe pelo menos 3 caracteres para pesquisar'))
        ->warning()
        ->setArrayParams(['data' => [], 'total' => 0])
        ->noDestroy();
    }

    $params = [
      'for_idorganizacao' => $this->getOrganizacaoId(),
      'for_idempresa' => $this->getEmpresaId()
    ];

    $where = "WHERE for_idorganizacao = :for_idorganizacao
      AND for_idempresa = :for_idempresa";

    if ($conteudo !== '') {
      $where .= "
        AND (
          for_id LIKE :for_id_busca
          OR for_chave LIKE :for_chave_busca
          OR for_cnpjcpf LIKE :for_cnpjcpf_busca
          OR for_razaosocial LIKE :for_razaosocial_busca
          OR for_nomefantasia LIKE :for_nomefantasia_busca
          OR for_email LIKE :for_email_busca
          OR for_celular LIKE :for_celular_busca
        )";

      $busca = "%{$conteudo}%";
      foreach ([
        'for_id_busca',
        'for_chave_busca',
        'for_cnpjcpf_busca',
        'for_razaosocial_busca',
        'for_nomefantasia_busca',
        'for_email_busca',
        'for_celular_busca'
      ] as $parametro) {
        $params[$parametro] = $busca;
      }
    }

    if (in_array($status, ['Ativo', 'Inativo'], true)) {
      $where .= ' AND for_status = :for_status';
      $params['for_status'] = $status;
    }

    $camposData = [
      'for_dtcad' => 'for_dtcad',
      'for_dtalter' => 'for_dtalter',
      'for_dtnasc' => 'for_dtnasc'
    ];

    if ($tipoFiltro !== '' && isset($camposData[$campoFiltro]) && $dataInicio !== '') {
      $campoData = $camposData[$campoFiltro];

      if ($tipoFiltro === 'Periodo' && $dataFim !== '') {
        $where .= " AND DATE({$campoData}) BETWEEN :for_data_inicio AND :for_data_fim";
        $params['for_data_inicio'] = $dataInicio;
        $params['for_data_fim'] = $dataFim;
      } elseif ($tipoFiltro === 'Exatidao') {
        $where .= " AND DATE({$campoData}) = :for_data_inicio";
        $params['for_data_inicio'] = $dataInicio;
      }
    }

    $camposOrdem = [
      'for_id',
      'for_chave',
      'for_cnpjcpf',
      'for_razaosocial',
      'for_nomefantasia',
      'for_status',
      'for_dtnasc',
      'for_dtcad',
      'for_dtalter'
    ];

    $orderBy = in_array($campoOrdem, $camposOrdem, true)
      ? $campoOrdem
      : 'for_razaosocial';
    $direcao = $ordem === 'DESC' ? 'DESC' : 'ASC';
    $limite = $limiteInformado === 'all' ? 5000 : (int) $limiteInformado;
    $limite = max(1, min($limite, 5000));

    return $this->rawQuery(
      "SELECT *
       FROM {$this->getTable()}
       {$where}
       ORDER BY {$orderBy} {$direcao}
       LIMIT {$limite}",
      $params,
      true,
      'Fornecedores encontrados com sucesso||Erro ao pesquisar fornecedores'
    );
  }

  public function pesquisarRapida(string $conteudo): object
  {
    $conteudo = trim(rawurldecode($conteudo));
    $rotaLista = 'fornecedor/list';

    if ($conteudo === '') {
      return (new SimpleResponse('Atenção', 'Digite uma informação para pesquisar.'))
        ->warning()
        ->setArrayParams(['location' => Helpers::url($rotaLista)])
        ->noDestroy();
    }

    $params = [
      'for_idorganizacao' => $this->getOrganizacaoId(),
      'for_idempresa' => $this->getEmpresaId()
    ];
    $where = "WHERE {$this->getTable()}.for_idorganizacao = :for_idorganizacao
              AND {$this->getTable()}.for_idempresa = :for_idempresa";
    $wherePesquisa = [];
    $documento = Helpers::somenteNumeros($conteudo);

    if (ctype_digit($conteudo)) {
      $wherePesquisa[] = "{$this->getTable()}.for_id = :conteudo_id";
      $params['conteudo_id'] = (int) $conteudo;
    }

    if ($documento !== '') {
      $wherePesquisa[] = "{$this->getTable()}.for_cnpjcpf LIKE :conteudo_documento";
      $wherePesquisa[] = "{$this->getTable()}.for_celular LIKE :conteudo_celular";
      $params['conteudo_documento'] = "%{$documento}%";
      $params['conteudo_celular'] = "%{$documento}%";
    }

    foreach (['for_chave', 'for_razaosocial', 'for_nomefantasia', 'for_email', 'for_observacao'] as $index => $campo) {
      $parametro = "conteudo_texto_{$index}";
      $wherePesquisa[] = "{$this->getTable()}.{$campo} LIKE :{$parametro}";
      $params[$parametro] = "%{$conteudo}%";
    }

    $where .= " AND (" . implode(' OR ', $wherePesquisa) . ')';
    $orderBy = 'CASE';
    if (ctype_digit($conteudo)) {
      $orderBy .= " WHEN {$this->getTable()}.for_id = :ordem_id THEN 1";
      $params['ordem_id'] = (int) $conteudo;
    }
    if ($documento !== '') {
      $orderBy .= " WHEN {$this->getTable()}.for_cnpjcpf = :ordem_documento THEN 2";
      $params['ordem_documento'] = $documento;
    }
    $orderBy .= " WHEN {$this->getTable()}.for_chave = :ordem_chave THEN 3 ELSE 9 END,
      {$this->getTable()}.for_razaosocial ASC,
      {$this->getTable()}.for_id ASC";
    $params['ordem_chave'] = $conteudo;

    $response = $this->rawQuery(
      "SELECT {$this->getTable()}.for_id, {$this->getTable()}.for_razaosocial
       FROM {$this->getTable()}
       {$where}
       ORDER BY {$orderBy}
       LIMIT 2",
      $params,
      true,
      'Fornecedor encontrado com sucesso||Erro ao pesquisar fornecedor'
    );

    if (!$response->status) {
      return (new SimpleResponse($response))
        ->erro('Erro ao realizar pesquisa rápida de fornecedor.')
        ->setArrayParams(['location' => Helpers::url($rotaLista)])
        ->noDestroy();
    }

    if ($response->total <= 0) {
      return (new SimpleResponse('Atenção', 'Nenhum fornecedor encontrado para a pesquisa informada.'))
        ->warning()
        ->setArrayParams(['location' => Helpers::url($rotaLista)])
        ->noDestroy();
    }

    if ($response->total > 1) {
      return (new SimpleResponse('Atenção', 'Mais de um fornecedor foi encontrado. Use a pesquisa completa para refinar o resultado.'))
        ->warning()
        ->setArrayParams([
          'data' => $response->data,
          'total' => $response->total,
          'location' => Helpers::url($rotaLista . '?lbLik=' . rawurlencode($conteudo))
        ])
        ->noDestroy();
    }

    $fornecedor = $response->data[0];
    return (new SimpleResponse('Sucesso', 'Fornecedor encontrado.'))
      ->sucesso()
      ->setArrayParams([
        'data' => $fornecedor,
        'total' => 1,
        'location' => Helpers::url("fornecedor/form/{$fornecedor->for_id}")
      ])
      ->noDestroy();
  }

  public function salvarFornecedor(array $data): object
  {
    $id = (int) ($data['for_id'] ?? 0);
    $camposPermitidos = [
      'for_id',
      'for_cnpjcpf',
      'for_razaosocial',
      'for_nomefantasia',
      'for_observacao',
      'for_im',
      'for_ie',
      'for_rg',
      'for_dtnasc',
      'for_idsexo',
      'for_idestadocivil',
      'for_celular',
      'for_email'
    ];

    $dados = [];
    foreach ($data as $campo => $valor) {
      if (!in_array($campo, $camposPermitidos, true)) {
        continue;
      }

      $dados[$campo] = is_string($valor) ? trim($valor) : $valor;
    }

    $documento = Helpers::somenteNumeros($dados['for_cnpjcpf'] ?? '');
    $nome = trim((string) ($dados['for_razaosocial'] ?? ''));
    $email = mb_strtolower(trim((string) ($dados['for_email'] ?? '')));
    $celular = Helpers::somenteNumeros($dados['for_celular'] ?? '');
    $status = 'Ativo';

    if ($documento === '') {
      return $this->atencao('Informe o CPF ou CNPJ do fornecedor.');
    }

    if (!Helpers::validarCnpjCpf($documento, false)) {
      return $this->atencao('Informe um CPF ou CNPJ valido.');
    }

    if ($nome === '') {
      return $this->atencao('Informe o nome ou razao social do fornecedor.');
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return $this->atencao('Informe um e-mail valido.');
    }

    if ($id <= 0 && $email === '') {
      return $this->atencao('Informe o e-mail do fornecedor para gerar o acesso.');
    }

    if ($celular !== '' && !in_array(strlen($celular), [10, 11], true)) {
      return $this->atencao('Informe um celular valido.');
    }

    $dados['for_cnpjcpf'] = $documento;
    $dados['for_razaosocial'] = $nome;
    $dados['for_nomefantasia'] = trim((string) ($dados['for_nomefantasia'] ?? '')) ?: null;
    $dados['for_observacao'] = trim((string) ($dados['for_observacao'] ?? '')) ?: null;
    $dados['for_im'] = trim((string) ($dados['for_im'] ?? '')) ?: null;
    $dados['for_ie'] = trim((string) ($dados['for_ie'] ?? '')) ?: null;
    $dados['for_rg'] = trim((string) ($dados['for_rg'] ?? '')) ?: null;
    $dados['for_dtnasc'] = trim((string) ($dados['for_dtnasc'] ?? '')) ?: null;
    $dados['for_idsexo'] = (int) ($dados['for_idsexo'] ?? 0) > 0 ? (int) $dados['for_idsexo'] : null;
    $dados['for_idestadocivil'] = (int) ($dados['for_idestadocivil'] ?? 0) > 0 ? (int) $dados['for_idestadocivil'] : null;
    if (array_key_exists('for_celular', $dados)) {
      $dados['for_celular'] = $celular !== '' ? $celular : null;
    }
    if (array_key_exists('for_email', $dados)) {
      $dados['for_email'] = $email !== '' ? $email : null;
    }
    if ($id > 0) {
      $atual = $this->getFornecedorById($id);

      if (!$atual->status || $atual->total !== 1) {
        return $this->atencao('Fornecedor nao encontrado ou fora do contexto atual.');
      }

      unset(
        $dados['for_id'],
        $dados['for_status'],
        $dados['for_celular'],
        $dados['for_email']
      );
      $response = $this->update(
        $dados,
        ['for_id' => $id],
        'Fornecedor atualizado com sucesso||Erro ao atualizar fornecedor'
      );

      if ($response->status) {
        $response->location = Helpers::url("fornecedor/form/{$id}");
      }

      return $response;
    }

    if ($status === 'Ativo') {
      $limite = (new LimitePlanoService($this->contexto))->verificar('fornecedor');

      if ($limite->status && !$limite->permitido) {
        return $this->atencao($limite->text ?: 'O limite de fornecedores ativos do plano foi atingido.');
      }
    }

    $dados['for_chave'] = 'FOR_' . date('YmdHis') . '_' . strtoupper(bin2hex(random_bytes(4)));
    $dados['for_status'] = 'Ativo';
    $dados['for_statusclass'] = 'success';
    $dados['for_username'] = $this->gerarUsernameFornecedor($documento);
    $senhaPadrao = $this->gerarSenhaPadraoFornecedor($documento, $celular);

    if (is_null($senhaPadrao)) {
      return $this->atencao('Nao foi possivel gerar a senha inicial do fornecedor.');
    }

    $dados['for_senha'] = password_hash($senhaPadrao, PASSWORD_DEFAULT);
    unset($dados['for_id']);

    $validarUnicidade = $this->validarUnicidadeAcesso([
      'for_email' => $dados['for_email'] ?? null,
      'for_celular' => $dados['for_celular'] ?? null,
      'for_username' => $dados['for_username']
    ]);

    if (!$validarUnicidade->status) {
      return $validarUnicidade;
    }

    $response = $this->insert(
      $dados,
      'Fornecedor cadastrado com sucesso||Erro ao cadastrar fornecedor'
    );

    if ($response->status && !empty($response->lastInsertId)) {
      $response->location = Helpers::url('fornecedor/form/' . (int) $response->lastInsertId);
    }

    return $response;
  }

  public function alterarStatusFornecedor(array $data): object
  {
    $id = (int) ($data['for_id'] ?? 0);
    $statusNovo = trim((string) ($data['for_status'] ?? ''));
    $motivo = trim((string) ($data['for_status_motivo'] ?? ''));

    if ($id <= 0) {
      return $this->atencao('Fornecedor nao informado.');
    }

    if (!in_array($statusNovo, ['Ativo', 'Inativo'], true)) {
      return $this->atencao('Status de fornecedor invalido.');
    }

    $getFornecedor = $this->getFornecedorById($id);
    if (!$getFornecedor->status || $getFornecedor->total !== 1) {
      return $this->atencao('Fornecedor nao encontrado ou fora do contexto atual.');
    }

    $fornecedorAtual = $getFornecedor->data[0];
    $statusAtual = trim((string) ($fornecedorAtual->for_status ?? ''));

    if ($statusAtual === $statusNovo) {
      return $this->atencao('O fornecedor ja possui este status.');
    }

    if ($statusNovo === 'Inativo' && mb_strlen($motivo) < 10) {
      return $this->atencao('Informe um motivo com pelo menos 10 caracteres para inativar o fornecedor.');
    }

    if ($statusAtual !== 'Ativo' && $statusNovo === 'Ativo') {
      $limite = (new LimitePlanoService($this->contexto))->verificar('fornecedor');

      if ($limite->status && !$limite->permitido) {
        return $this->atencao($limite->text ?: 'O limite de fornecedores ativos do plano foi atingido.');
      }
    }

    $dados = [
      'for_status' => $statusNovo,
      'for_statusclass' => $statusNovo === 'Ativo' ? 'success' : 'danger'
    ];

    if ($motivo !== '') {
      $dados['for_status_motivo'] = json_encode([
        'motivo' => $motivo,
        'data' => date('d/m/Y H:i:s'),
        'responsavel' => $this->usuarioLogado->usu_razaosocial ?? 'Sistema',
        'usu_id' => (int) ($this->usuarioLogado->usu_id ?? 0)
      ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    return $this->update(
      $dados,
      ['for_id' => $id],
      'Status do fornecedor atualizado com sucesso||Erro ao atualizar status do fornecedor'
    );
  }

  public function salvarAcessoFornecedor(array $data): object
  {
    $id = (int) ($data['for_id'] ?? 0);

    if ($id <= 0) {
      return $this->atencao('Fornecedor nao informado.');
    }

    $atual = $this->getFornecedorById($id);
    if (!$atual->status || $atual->total !== 1) {
      return $this->atencao('Fornecedor nao encontrado ou fora do contexto atual.');
    }

    $email = mb_strtolower(trim((string) ($data['for_email'] ?? '')));
    $celular = Helpers::somenteNumeros($data['for_celular'] ?? '');
    $username = strtolower(trim((string) ($data['for_username'] ?? '')));
    $senha = trim((string) ($data['for_senha'] ?? ''));
    $confirmacao = trim((string) ($data['for_senha_confirmacao'] ?? ''));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return $this->atencao('Informe um e-mail valido.');
    }

    if ($celular !== '' && !in_array(strlen($celular), [10, 11], true)) {
      return $this->atencao('Informe um celular valido.');
    }

    if ($username === '' || !preg_match('/^[a-z0-9_]+$/', $username)) {
      return $this->atencao('O nome de usuario deve conter apenas letras minusculas, numeros e underline.');
    }

    if ($senha !== '' || $confirmacao !== '') {
      if ($senha !== $confirmacao) {
        return $this->atencao('As senhas nao conferem.');
      }

      if (
        strlen($senha) < 8
        || !preg_match('/[A-Za-z]/', $senha)
        || !preg_match('/[0-9]/', $senha)
        || !preg_match('/[^A-Za-z0-9]/', $senha)
      ) {
        return $this->atencao('A nova senha deve ter pelo menos 8 caracteres, letras, numeros e simbolos.');
      }
    }

    $validarUnicidade = $this->validarUnicidadeAcesso([
      'for_email' => $email,
      'for_celular' => $celular !== '' ? $celular : null,
      'for_username' => $username
    ], $id);

    if (!$validarUnicidade->status) {
      return $validarUnicidade;
    }

    $dados = [
      'for_email' => $email,
      'for_celular' => $celular !== '' ? $celular : null,
      'for_username' => $username
    ];

    if ($senha !== '') {
      $dados['for_senha'] = password_hash($senha, PASSWORD_DEFAULT);
    }

    $response = $this->update(
      $dados,
      ['for_id' => $id],
      'Dados de acesso atualizados com sucesso||Erro ao atualizar dados de acesso'
    );

    if ($response->status) {
      $response->location = Helpers::url("fornecedor/form/{$id}");
    }

    return $response;
  }

  private function validarUnicidadeAcesso(array $dados, int $id = 0): object
  {
    $campos = [
      'for_email' => 'Ja existe um fornecedor com este e-mail neste contexto.',
      'for_celular' => 'Ja existe um fornecedor com este celular neste contexto.',
      'for_username' => 'Ja existe um fornecedor com este usuario neste contexto.'
    ];

    foreach ($campos as $campo => $mensagem) {
      $valor = $dados[$campo] ?? null;
      if (is_null($valor) || trim((string) $valor) === '') {
        continue;
      }

      $whereId = '';
      $params = [
        'valor' => $valor,
        'for_idorganizacao' => $this->getOrganizacaoId(),
        'for_idempresa' => $this->getEmpresaId()
      ];

      if ($id > 0) {
        $whereId = ' AND for_id <> :for_id';
        $params['for_id'] = $id;
      }

      $response = $this->rawQuery(
        "SELECT for_id
         FROM {$this->getTable()}
         WHERE {$campo} = :valor
         AND for_idorganizacao = :for_idorganizacao
         AND for_idempresa = :for_idempresa
         {$whereId}
         LIMIT 1",
        $params,
        true,
        'Validacao de acesso||Erro ao validar dados de acesso'
      );

      if (!$response->status) {
        return $response;
      }

      if ($response->total > 0) {
        return $this->atencao($mensagem);
      }
    }

    return (new SimpleResponse('Tudo Certo', 'Dados de acesso disponiveis'))
      ->sucesso()
      ->noDestroy();
  }

  private function gerarUsernameFornecedor(string $documento): string
  {
    $base = 'fornecedor_' . substr($documento, 0, 8);
    $username = $base;
    $sufixo = 1;

    do {
      $response = $this->rawQuery(
        "SELECT for_id
         FROM {$this->getTable()}
         WHERE for_username = :for_username
         AND for_idorganizacao = :for_idorganizacao
         AND for_idempresa = :for_idempresa
         LIMIT 1",
        [
          'for_username' => $username,
          'for_idorganizacao' => $this->getOrganizacaoId(),
          'for_idempresa' => $this->getEmpresaId()
        ],
        true,
        'Geracao de usuario||Erro ao gerar usuario do fornecedor'
      );

      if (!$response->status || $response->total === 0) {
        return $username;
      }

      $sufixo++;
      $username = substr($base, 0, 50 - strlen((string) $sufixo) - 1) . '_' . $sufixo;
    } while ($sufixo <= 9999);

    return $username;
  }

  private function gerarSenhaPadraoFornecedor(string $documento, string $celular): ?string
  {
    $documentoNumeros = Helpers::somenteNumeros($documento);
    if (strlen($documentoNumeros) >= 6) {
      return substr($documentoNumeros, 0, 6);
    }

    $celularNumeros = Helpers::somenteNumeros($celular);
    if (strlen($celularNumeros) >= 6) {
      return substr($celularNumeros, 0, 6);
    }

    return null;
  }

  private function getOrganizacaoId(): int
  {
    return (int) (
      $this->contexto->organizacao->org_id
      ?? $this->usuarioLogado->organizacao->org_id
      ?? $this->usuarioLogado->usu_idorganizacao
      ?? 0
    );
  }

  private function getEmpresaId(): int
  {
    return (int) (
      $this->contexto->empresa->emp_id
      ?? $this->usuarioLogado->empresa->emp_id
      ?? $this->usuarioLogado->usu_idempresa
      ?? 0
    );
  }

  private function atencao(string $texto): object
  {
    return (new SimpleResponse('Atencao', $texto))
      ->warning()
      ->noDestroy();
  }

  private function erroFornecedor(string $texto): object
  {
    return (new SimpleResponse('Erro', $texto))
      ->erro()
      ->setArrayParams(['data' => [], 'total' => 0])
      ->noDestroy();
  }
}
