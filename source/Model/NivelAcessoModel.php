<?php

namespace Source\Model;

use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;

class NivelAcessoModel extends SimpleModel
{
  protected string $tabela = 'nivelacesso';
  protected string $prefixo = 'nva';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'hierarquico';
  protected string $campoOrganizacao = 'nva_idorganizacao';
  protected string $campoEmpresa = 'nva_idempresa';
  protected string $campoEscopoHierarquico = 'nva_escopo';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getAtivos(): object
  {
    return $this->getNiveisAtivos();
  }

  public function getNiveisAtivos(): object
  {
    return $this
      ->where('nva_status', 'Ativo')
      ->orderByRaw("
        CASE nva_escopo
          WHEN 'Projeto' THEN 1
          WHEN 'Organizacao' THEN 2
          WHEN 'Empresa' THEN 3
          ELSE 4
        END
      ")
      ->orderBy('nva_nome', 'ASC')
      ->get("Níveis de acesso encontrados com sucesso||Erro ao buscar níveis de acesso");
  }

  public function getById(int $nvaId): object
  {
    return $this->getNivelAcessoById($nvaId);
  }

  public function getNivelAcessoById(int $nvaId): object
  {
    return $this
      ->where('nva_id', $nvaId)
      ->first("Nível de acesso encontrado com sucesso||Erro ao buscar nível de acesso");
  }

  public function getPorEscopo(string $escopo): object
  {
    return $this
      ->where('nva_status', 'Ativo')
      ->where('nva_escopo', $escopo)
      ->orderBy('nva_nome', 'ASC')
      ->get("Níveis de acesso encontrados com sucesso||Erro ao buscar níveis de acesso");
  }

  public function getPadraoInicialOrganizacao(int $orgId): object
  {
    return $this
      ->where('nva_status', 'Ativo')
      ->where('nva_idorganizacao', $orgId)
      ->where('nva_idempresa', 0)
      ->where('nva_escopo', 'Organizacao')
      ->where('nva_padrao_inicial', 1)
      ->first("Nível padrão inicial encontrado com sucesso||Erro ao buscar nível padrão inicial");
  }

  public function contarAtivosDaOrganizacaoContexto(): object
  {
    $orgId = (int) ($this->contexto->organizacao->org_id ?? 0);

    if ($orgId <= 0) {
      return (new SimpleResponse("Erro", "Contexto de organização não identificado"))
        ->erro()
        ->setArrayParams([
          'total' => 0
        ])
        ->noDestroy();
    }

    return $this->rawQuery(
      "SELECT COUNT(*) AS total
       FROM {$this->getTable()}
       WHERE nva_status = 'Ativo'
       AND nva_idorganizacao = :nva_idorganizacao",
      [
        'nva_idorganizacao' => $orgId
      ],
      true,
      "Total encontrado com sucesso||Erro ao contar níveis de acesso"
    );
  }

  public function getEscoposPermitidosParaContexto(): array
  {
    $escopoUsuario = $this->usuarioLogado->escopo_maximo
      ?? $this->usuarioLogado->nva_escopo_maximo
      ?? 'Empresa';

    $orgId = (int) ($this->contexto->organizacao->org_id ?? 0);
    $empId = (int) ($this->contexto->empresa->emp_id ?? 0);

    if ($escopoUsuario === 'Projeto' && $orgId === $this->organizacaoCentralId && $empId === $this->empresaCentralId) {
      return [
        (object) ['value' => 'Projeto', 'label' => 'Projeto'],
        (object) ['value' => 'Organizacao', 'label' => 'Organização'],
        (object) ['value' => 'Empresa', 'label' => 'Empresa']
      ];
    }

    if ($escopoUsuario === 'Projeto' || $escopoUsuario === 'Organizacao') {
      return [
        (object) ['value' => 'Organizacao', 'label' => 'Organização'],
        (object) ['value' => 'Empresa', 'label' => 'Empresa']
      ];
    }

    return [
      (object) ['value' => 'Empresa', 'label' => 'Empresa']
    ];
  }

  public function getEscopoPadraoParaNovoNivel(): string
  {
    $escopos = $this->getEscoposPermitidosParaContexto();

    return $escopos[0]->value ?? 'Empresa';
  }

  public function getPermissaoSistema(?string $escopo = null): array
  {
    $escopo = $escopo ?: $this->getEscopoPadraoParaNovoNivel();

    $grupos = [
      (object) [
        'titulo' => 'Contexto',
        'modulo' => 'contexto',
        'selecionarTodos' => true,
        'permissoes' => [
          $this->permissao('contexto.trocar', 'Permite trocar o contexto ativo')
        ]
      ],
      (object) [
        'titulo' => 'Projeto',
        'modulo' => 'projeto',
        'selecionarTodos' => true,
        'permissoes' => [
          $this->permissao('projeto.visualizar', 'Permite visualizar dados do projeto'),
          $this->permissao('projeto.atualizar', 'Permite atualizar dados do projeto'),
          $this->permissao('projeto.plano.visualizar', 'Permite visualizar os planos do projeto'),
          $this->permissao('projeto.plano.atualizar', 'Permite atualizar os planos do projeto'),
          $this->permissao('projeto.contratacao.visualizar', 'Permite visualizar as contratações do projeto'),
          $this->permissao('projeto.contratacao.atualizar', 'Permite atualizar as contratações do projeto'),
          $this->permissao('projeto.palavrachave.visualizar', 'Permite visualizar palavras-chave do projeto'),
          $this->permissao('projeto.palavrachave.atualizar', 'Permite atualizar palavras-chave do projeto'),
          $this->permissao('projeto.email.visualizar', 'Permite visualizar templates de e-mail do projeto'),
          $this->permissao('projeto.email.atualizar', 'Permite atualizar templates de e-mail do projeto')
        ]
      ],
      (object) [
        'titulo' => 'Organização',
        'modulo' => 'organizacao',
        'selecionarTodos' => true,
        'permissoes' => [
          $this->permissao('organizacao.visualizar', 'Permite visualizar organização'),
          $this->permissao('organizacao.criar', 'Permite criar organização'),
          $this->permissao('organizacao.atualizar', 'Permite atualizar organização'),
          $this->permissao('organizacao.plano.contratar', 'Permite consultar e renovar planos contratados da organização')
        ]
      ],
      (object) [
        'titulo' => 'Empresa',
        'modulo' => 'empresa',
        'selecionarTodos' => true,
        'permissoes' => [
          $this->permissao('empresa.visualizar', 'Permite visualizar empresa'),
          $this->permissao('empresa.criar', 'Permite criar empresa'),
          $this->permissao('empresa.atualizar', 'Permite atualizar empresa')
        ]
      ],
      (object) [
        'titulo' => 'Usuário',
        'modulo' => 'usuario',
        'selecionarTodos' => true,
        'permissoes' => [
          $this->permissao('usuario.visualizar', 'Permite visualizar usuário'),
          $this->permissao('usuario.criar', 'Permite criar usuário'),
          $this->permissao('usuario.atualizar', 'Permite atualizar usuário')
        ]
      ],
      (object) [
        'titulo' => 'Nível de acesso',
        'modulo' => 'nivelacesso',
        'selecionarTodos' => true,
        'permissoes' => [
          $this->permissao('nivelacesso.visualizar', 'Permite visualizar nível de acesso'),
          $this->permissao('nivelacesso.criar', 'Permite criar nível de acesso'),
          $this->permissao('nivelacesso.atualizar', 'Permite atualizar nível de acesso')
        ]
      ],
      (object) [
        'titulo' => 'Endereço',
        'modulo' => 'endereco',
        'selecionarTodos' => true,
        'permissoes' => [
          $this->permissao('endereco.visualizar', 'Permite visualizar endereço'),
          $this->permissao('endereco.criar', 'Permite criar endereço'),
          $this->permissao('endereco.atualizar', 'Permite atualizar endereço'),
          $this->permissao('endereco.excluir', 'Permite excluir endereço')
        ]
      ],
      (object) [
        'titulo' => 'Financeiro',
        'modulo' => 'financeiro',
        'selecionarTodos' => true,
        'permissoes' => [
          $this->permissao('financeiro.visualizar', 'Permite visualizar financeiro'),
          $this->permissao('financeiro.dashboard', 'Permite visualizar dashboard financeiro'),
          $this->permissao('financeiro.relatorio', 'Permite visualizar relatorios financeiros'),
          $this->permissao('financeiro.lancamento.visualizar', 'Permite visualizar lancamentos financeiros'),
          $this->permissao('financeiro.lancamento.criar', 'Permite criar lancamentos financeiros'),
          $this->permissao('financeiro.lancamento.atualizar', 'Permite atualizar lancamentos financeiros'),
          $this->permissao('financeiro.lancamento.cancelar', 'Permite cancelar lancamentos financeiros'),
          $this->permissao('financeiro.lancamento.baixar', 'Permite baixar lancamentos financeiros'),
          $this->permissao('financeiro.lancamento.estornar', 'Permite estornar lancamentos financeiros'),
          $this->permissao('financeiro.transferencia.visualizar', 'Permite visualizar transferencias financeiras'),
          $this->permissao('financeiro.transferencia.criar', 'Permite criar transferencias financeiras'),
          $this->permissao('financeiro.transferencia.cancelar', 'Permite cancelar transferencias financeiras'),
          $this->permissao('financeiro.basico.visualizar', 'Permite visualizar cadastros financeiros basicos'),
          $this->permissao('financeiro.basico.criar', 'Permite criar cadastros financeiros basicos'),
          $this->permissao('financeiro.basico.atualizar', 'Permite atualizar cadastros financeiros basicos')
        ]
      ],
      (object) [
        'titulo' => 'Titulo',
        'modulo' => 'titulo',
        'selecionarTodos' => true,
        'permissoes' => [
          $this->permissao('titulo.visualizar', 'Permite visualizar titulos financeiros'),
          $this->permissao('titulo.criar', 'Permite criar titulos financeiros'),
          $this->permissao('titulo.atualizar', 'Permite atualizar titulos financeiros'),
          $this->permissao('titulo.cancelar', 'Permite cancelar titulos financeiros'),
          $this->permissao('titulo.confirmar', 'Permite confirmar titulos financeiros')
        ]
      ],
      (object) [
        'titulo' => 'Fornecedor',
        'modulo' => 'fornecedor',
        'selecionarTodos' => true,
        'permissoes' => [
          $this->permissao('fornecedor.visualizar', 'Permite visualizar fornecedores'),
          $this->permissao('fornecedor.criar', 'Permite criar fornecedores'),
          $this->permissao('fornecedor.atualizar', 'Permite atualizar fornecedores')
        ]
      ],
      (object) [
        'titulo' => 'Contato',
        'modulo' => 'contato',
        'selecionarTodos' => true,
        'permissoes' => [
          $this->permissao('contato.visualizar', 'Permite visualizar contato'),
          $this->permissao('contato.criar', 'Permite criar contato'),
          $this->permissao('contato.atualizar', 'Permite atualizar contato'),
          $this->permissao('contato.excluir', 'Permite excluir contato')
        ]
      ],
    ];

    $retorno = [];

    foreach ($grupos as $grupo) {
      $permissoes = [];

      foreach ($grupo->permissoes as $permissao) {
        if ($this->permissaoCompativelComEscopo($permissao->per_nome, $escopo)) {
          $permissoes[] = $permissao;
        }
      }

      if (!empty($permissoes)) {
        $grupo->permissoes = $permissoes;
        $retorno[] = $grupo;
      }
    }

    return $retorno;
  }

  public function salvarNivelAcesso(array $data): object
  {
    $permissoes = $data['permissoes'] ?? [];
    $data = $this->filtrarCampos($data);
    $nvaId = (int) ($data['nva_id'] ?? 0);

    if (empty($permissoes) || !is_array($permissoes)) {
      return (new SimpleResponse("Atenção", "Selecione pelo menos uma permissão para salvar o nível de acesso"))
        ->warning()
        ->noDestroy();
    }

    $permissoes = array_values(array_unique(array_filter(array_map('trim', $permissoes))));

    if (empty($permissoes)) {
      return (new SimpleResponse("Atenção", "Selecione pelo menos uma permissão para salvar o nível de acesso"))
        ->warning()
        ->noDestroy();
    }

    $this->beginTransaction();

    if ($nvaId > 0) {
      $response = $this->atualizarNivelAcesso($nvaId, $data, $permissoes);
    } else {
      $response = $this->inserirNivelAcesso($data, $permissoes);
    }

    if (!$response->status) {
      $this->rollback();
      return $response;
    }

    $this->commit();

    return $response;
  }

  public function validarPermissoesDoNivel(array $permissoes, string $escopo): object
  {
    $invalidas = [];

    foreach ($permissoes as $permissao) {
      if (!$this->permissaoExisteNoSistema($permissao) || !$this->permissaoCompativelComEscopo($permissao, $escopo)) {
        $invalidas[] = $permissao;
      }
    }

    if (!empty($invalidas)) {
      return (new SimpleResponse("Erro", "Permissões incompatíveis com o escopo do nível de acesso"))
        ->erro()
        ->setArrayParams([
          'permissoes_invalidas' => $invalidas
        ])
        ->noDestroy();
    }

    return (new SimpleResponse("Validação", "Permissões válidas para o escopo"))
      ->sucesso()
      ->noDestroy();
  }

  public function permissaoCompativelComEscopo(string $permissao, string $escopo): bool
  {
    $recurso = explode('.', $permissao)[0] ?? '';

    $mapa = $this->getMapaPermissoesEscopo();

    return in_array($recurso, $mapa[$escopo] ?? [], true);
  }

  public function getMapaPermissoesEscopo(): array
  {
    return [
      'Projeto' => [
        'projeto',
        'organizacao',
        'empresa',
        'usuario',
        'nivelacesso',
        'endereco',
        'financeiro',
        'titulo',
        'fornecedor',
        'contexto'
      ],
      'Organizacao' => [
        'organizacao',
        'empresa',
        'usuario',
        'nivelacesso',
        'endereco',
        'financeiro',
        'titulo',
        'fornecedor',
        'contexto'
      ],
      'Empresa' => [
        'empresa',
        'usuario',
        'endereco',
        'financeiro',
        'titulo',
        'fornecedor',
        'contexto'
      ]
    ];
  }

  public function permissaoExisteNoSistema(string $perNome): bool
  {
    foreach ($this->getPermissaoSistema('Projeto') as $grupo) {
      foreach ($grupo->permissoes as $permissao) {
        if ($permissao->per_nome === $perNome) {
          return true;
        }
      }
    }

    return false;
  }

  public function getDescricaoPermissao(string $perNome): ?string
  {
    foreach ($this->getPermissaoSistema('Projeto') as $grupo) {
      foreach ($grupo->permissoes as $permissao) {
        if ($permissao->per_nome === $perNome) {
          return $permissao->per_descricao;
        }
      }
    }

    return null;
  }

  private function inserirNivelAcesso(array $data, array $permissoes): object
  {
    unset($data['nva_id']);

    $data = $this->normalizarDadosNivel($data);

    $validacaoEscopo = $this->validarEscopoPermitido($data['nva_escopo']);

    if (!$validacaoEscopo->status) {
      return $validacaoEscopo;
    }

    $data = $this->aplicarContextoNivel($data);

    $validacao = $this->validarNivelAcesso($data);

    if (!$validacao->status) {
      return $validacao;
    }

    $validacaoPermissoes = $this->validarPermissoesDoNivel($permissoes, $data['nva_escopo']);

    if (!$validacaoPermissoes->status) {
      return $validacaoPermissoes;
    }

    if ((int) ($data['nva_padrao_inicial'] ?? 0) === 1) {
      $validacaoPadrao = $this->validarPadraoInicialUnico($data);

      if (!$validacaoPadrao->status) {
        return $validacaoPadrao;
      }
    }

    $insert = $this->insert(
      $data,
      "Nível de acesso inserido com sucesso||Erro ao inserir nível de acesso"
    );

    if (!$insert->status) {
      return $insert;
    }

    $nvaId = (int) ($insert->lastInsertId ?? 0);

    if ($nvaId <= 0) {
      return (new SimpleResponse("Erro", "Erro ao identificar o nível de acesso inserido"))
        ->erro()
        ->setArrayParams([
          'resultado' => $insert
        ])
        ->noDestroy();
    }

    $salvarPermissoes = $this->salvarPermissoesDoNivel($nvaId, $permissoes);

    if (!$salvarPermissoes->status) {
      return $salvarPermissoes;
    }

    $insert->data = (object) [
      'nva_id' => $nvaId,
      'total_permissoes' => count($permissoes)
    ];

    return $insert;
  }

  private function atualizarNivelAcesso(int $nvaId, array $data, array $permissoes): object
  {
    $getNivel = $this->getNivelAcessoById($nvaId);

    if (!$getNivel->status || $getNivel->total <= 0) {
      return (new SimpleResponse("Erro", "Nível de acesso não encontrado ou fora do contexto atual"))
        ->erro()
        ->setArrayParams([
          'nva_id' => $nvaId
        ])
        ->noDestroy();
    }

    $nivel = $getNivel->data[0];

    if ((int) ($nivel->nva_imutavel ?? 0) === 1) {
      return (new SimpleResponse("Atenção", "Este nível de acesso é imutável e não pode ser alterado"))
        ->warning()
        ->noDestroy();
    }

    if (isset($data['nva_escopo']) && $data['nva_escopo'] !== $nivel->nva_escopo) {
      return (new SimpleResponse("Erro", "O escopo do nível de acesso não pode ser alterado"))
        ->erro()
        ->setArrayParams([
          'escopo_atual' => $nivel->nva_escopo,
          'escopo_recebido' => $data['nva_escopo']
        ])
        ->noDestroy();
    }

    unset(
      $data['nva_id'],
      $data['nva_idorganizacao'],
      $data['nva_idempresa'],
      $data['nva_escopo']
    );

    $data = $this->normalizarDadosNivel($data, $nivel->nva_escopo);

    $dataCompleta = array_merge((array) $nivel, $data);
    $dataCompleta['nva_escopo'] = $nivel->nva_escopo;
    $dataCompleta['nva_idorganizacao'] = (int) $nivel->nva_idorganizacao;
    $dataCompleta['nva_idempresa'] = (int) $nivel->nva_idempresa;

    $validacaoEscopo = $this->validarEscopoPermitido($nivel->nva_escopo);

    if (!$validacaoEscopo->status) {
      return $validacaoEscopo;
    }

    $validacao = $this->validarNivelAcesso($dataCompleta, false);

    if (!$validacao->status) {
      return $validacao;
    }

    $validacaoPermissoes = $this->validarPermissoesDoNivel($permissoes, $nivel->nva_escopo);

    if (!$validacaoPermissoes->status) {
      return $validacaoPermissoes;
    }

    if ((int) ($dataCompleta['nva_padrao_inicial'] ?? 0) === 1) {
      $validacaoPadrao = $this->validarPadraoInicialUnico($dataCompleta, $nvaId);

      if (!$validacaoPadrao->status) {
        return $validacaoPadrao;
      }
    }

    $update = $this->update(
      $data,
      [
        'nva_id' => $nvaId
      ],
      "Nível de acesso atualizado com sucesso||Erro ao atualizar nível de acesso"
    );

    if (!$update->status) {
      return $update;
    }

    $salvarPermissoes = $this->salvarPermissoesDoNivel($nvaId, $permissoes);

    if (!$salvarPermissoes->status) {
      return $salvarPermissoes;
    }

    $update->data = (object) [
      'nva_id' => $nvaId,
      'total_permissoes' => count($permissoes)
    ];

    return $update;
  }

  private function normalizarDadosNivel(array $data, ?string $escopoReferencia = null): array
  {
    if (isset($data['nva_nome'])) {
      $data['nva_nome'] = trim((string) $data['nva_nome']);
    }

    if (isset($data['nva_descricao'])) {
      $data['nva_descricao'] = trim((string) $data['nva_descricao']);
    }

    $data['nva_status'] = $data['nva_status'] ?? 'Ativo';
    $data['nva_statusclass'] = $data['nva_status'] === 'Ativo' ? 'success' : 'danger';
    $data['nva_escopo'] = $data['nva_escopo'] ?? $escopoReferencia ?? $this->getEscopoPadraoParaNovoNivel();
    $data['nva_padrao_inicial'] = (int) ($data['nva_padrao_inicial'] ?? 0);

    if ($data['nva_escopo'] !== 'Organizacao') {
      $data['nva_padrao_inicial'] = 0;
    }

    if (($this->usuarioLogado->escopo_maximo ?? '') !== 'Projeto') {
      $data['nva_padrao_inicial'] = 0;
    }

    return $data;
  }

  private function aplicarContextoNivel(array $data): array
  {
    if ($data['nva_escopo'] === 'Projeto') {
      $data['nva_idorganizacao'] = 0;
      $data['nva_idempresa'] = 0;
      return $data;
    }

    if ($data['nva_escopo'] === 'Organizacao') {
      $data['nva_idorganizacao'] = (int) ($this->contexto->organizacao->org_id ?? 0);
      $data['nva_idempresa'] = 0;
      return $data;
    }

    if ($data['nva_escopo'] === 'Empresa') {
      $data['nva_idorganizacao'] = (int) ($this->contexto->organizacao->org_id ?? 0);
      $data['nva_idempresa'] = (int) ($this->contexto->empresa->emp_id ?? 0);
      return $data;
    }

    return $data;
  }

  private function validarEscopoPermitido(string $escopo): object
  {
    $permitidos = array_map(
      fn($item) => $item->value,
      $this->getEscoposPermitidosParaContexto()
    );

    if (!in_array($escopo, $permitidos, true)) {
      return (new SimpleResponse("Permissão Negada", "Você não pode gerenciar nível de acesso neste escopo pelo contexto atual"))
        ->erro()
        ->setArrayParams([
          'escopo' => $escopo,
          'permitidos' => $permitidos
        ])
        ->noDestroy();
    }

    return (new SimpleResponse("Validação", "Escopo permitido"))
      ->sucesso()
      ->noDestroy();
  }

  private function validarNivelAcesso(array $data, bool $validarObrigatorios = true): object
  {
    if ($validarObrigatorios && trim((string) ($data['nva_nome'] ?? '')) === '') {
      return (new SimpleResponse("Erro", "Informe o nome do nível de acesso"))
        ->erro()
        ->setArrayParams([
          'campo' => 'nva_nome'
        ])
        ->noDestroy();
    }

    if (!in_array($data['nva_escopo'] ?? '', ['Projeto', 'Organizacao', 'Empresa'], true)) {
      return (new SimpleResponse("Erro", "Escopo do nível de acesso inválido"))
        ->erro()
        ->setArrayParams([
          'campo' => 'nva_escopo'
        ])
        ->noDestroy();
    }

    if (!in_array($data['nva_status'] ?? 'Ativo', ['Ativo', 'Inativo'], true)) {
      return (new SimpleResponse("Erro", "Status do nível de acesso inválido"))
        ->erro()
        ->setArrayParams([
          'campo' => 'nva_status'
        ])
        ->noDestroy();
    }

    if ($data['nva_escopo'] === 'Projeto') {
      if ((int) ($data['nva_idorganizacao'] ?? 0) !== 0 || (int) ($data['nva_idempresa'] ?? 0) !== 0) {
        return (new SimpleResponse("Erro", "Nível de escopo Projeto não deve possuir organização ou empresa"))
          ->erro()
          ->noDestroy();
      }
    }

    if ($data['nva_escopo'] === 'Organizacao') {
      if ((int) ($data['nva_idorganizacao'] ?? 0) <= 0 || (int) ($data['nva_idempresa'] ?? 0) !== 0) {
        return (new SimpleResponse("Erro", "Nível de escopo Organização deve possuir organização e empresa igual a zero"))
          ->erro()
          ->noDestroy();
      }
    }

    if ($data['nva_escopo'] === 'Empresa') {
      if ((int) ($data['nva_idorganizacao'] ?? 0) <= 0 || (int) ($data['nva_idempresa'] ?? 0) <= 0) {
        return (new SimpleResponse("Erro", "Nível de escopo Empresa deve possuir organização e empresa"))
          ->erro()
          ->noDestroy();
      }
    }

    return (new SimpleResponse("Validação", "Dados válidos"))
      ->sucesso()
      ->noDestroy();
  }

  private function validarPadraoInicialUnico(array $data, ?int $ignorarNvaId = null): object
  {
    if (($data['nva_escopo'] ?? '') !== 'Organizacao') {
      return (new SimpleResponse("Erro", "O nível padrão inicial deve ter escopo Organização"))
        ->erro()
        ->noDestroy();
    }

    if (($this->usuarioLogado->escopo_maximo ?? '') !== 'Projeto') {
      return (new SimpleResponse("Permissão Negada", "Somente usuário de escopo Projeto pode definir o nível padrão inicial"))
        ->erro()
        ->noDestroy();
    }

    $params = [
      'nva_idorganizacao' => (int) ($data['nva_idorganizacao'] ?? 0)
    ];

    $whereIgnorar = '';

    if (!empty($ignorarNvaId)) {
      $whereIgnorar = "AND nva_id != :nva_id";
      $params['nva_id'] = $ignorarNvaId;
    }

    $getNivel = $this->rawQuery(
      "SELECT nva_id
       FROM {$this->getTable()}
       WHERE nva_status = 'Ativo'
       AND nva_idorganizacao = :nva_idorganizacao
       AND nva_idempresa = 0
       AND nva_escopo = 'Organizacao'
       AND nva_padrao_inicial = 1
       {$whereIgnorar}
       LIMIT 1",
      $params,
      true,
      "Nível padrão inicial validado com sucesso||Erro ao validar nível padrão inicial"
    );

    if ($getNivel->status && $getNivel->total > 0) {
      return (new SimpleResponse("Erro", "Já existe um nível padrão inicial ativo para esta organização"))
        ->erro()
        ->setArrayParams([
          'nva_idorganizacao' => $params['nva_idorganizacao']
        ])
        ->noDestroy();
    }

    return (new SimpleResponse("Validação", "Nível padrão inicial válido"))
      ->sucesso()
      ->noDestroy();
  }

  private function salvarPermissoesDoNivel(int $nvaId, array $permissoes): object
  {
    $tabelaPermissao = DB_PREFIX . '_permissao';

    $delete = $this->rawQuery(
      "DELETE FROM {$tabelaPermissao}
       WHERE per_idnivelacesso = :per_idnivelacesso",
      [
        'per_idnivelacesso' => $nvaId
      ],
      false,
      "Permissões removidas com sucesso||Erro ao remover permissões"
    );

    if (!$delete->status) {
      return $delete;
    }

    foreach ($permissoes as $perNome) {
      $insert = $this->rawQuery(
        "INSERT INTO {$tabelaPermissao}
         (
           per_idnivelacesso,
           per_status,
           per_statusclass,
           per_nome,
           per_descricao,
           per_dtcad,
           per_dtalter,
           per_log
         )
         VALUES
         (
           :per_idnivelacesso,
           'Ativo',
           'success',
           :per_nome,
           :per_descricao,
           NOW(),
           NOW(),
           '[]'
         )",
        [
          'per_idnivelacesso' => $nvaId,
          'per_nome' => $perNome,
          'per_descricao' => $this->getDescricaoPermissao($perNome) ?? $perNome
        ],
        false,
        "Permissão inserida com sucesso||Erro ao inserir permissão"
      );

      if (!$insert->status) {
        return $insert;
      }
    }

    return (new SimpleResponse("Sucesso", "Permissões atualizadas com sucesso"))
      ->sucesso()
      ->setArrayParams([
        'data' => (object) [
          'nva_id' => $nvaId,
          'total_permissoes' => count($permissoes)
        ]
      ])
      ->noDestroy();
  }

  private function permissao(string $nome, string $descricao, string $typeHtml = 'switch'): object
  {
    return (object) [
      'per_nome' => $nome,
      'per_descricao' => $descricao,
      'typeHtml' => $typeHtml
    ];
  }

  public function pesquisarNivelAcesso(array $filtros): object
  {
    $orgId = (int) ($this->contexto->organizacao->org_id ?? 0);
    $empId = (int) ($this->contexto->empresa->emp_id ?? 0);

    $tipoPesquisa = trim((string) ($filtros['lbTipoPesquisa'] ?? 'inicial'));
    $conteudo = trim((string) ($filtros['lbLik'] ?? ''));

    $escopo = trim((string) ($filtros['nva_escopo'] ?? ''));
    $status = trim((string) ($filtros['nva_status'] ?? ''));
    $imutavel = trim((string) ($filtros['nva_imutavel'] ?? ''));
    $padraoInicial = trim((string) ($filtros['nva_padrao_inicial'] ?? ''));

    $tipoFiltro = trim((string) ($filtros['lbTpf'] ?? ''));
    $campoFiltro = trim((string) ($filtros['lbCmf'] ?? ''));
    $dataInicio = trim((string) ($filtros['lbIni'] ?? ''));
    $dataFim = trim((string) ($filtros['lbFim'] ?? ''));

    $campoOrdem = trim((string) ($filtros['lbOrd'] ?? ''));
    $ordem = strtoupper(trim((string) ($filtros['lbOby'] ?? 'ASC')));
    $limite = trim((string) ($filtros['lbLmt'] ?? '1000'));

    if ($tipoPesquisa === 'simples' && $conteudo !== '' && mb_strlen($conteudo) < 4) {
      return (new SimpleResponse("Atenção", "Informe pelo menos 4 caracteres para pesquisar"))
        ->warning()
        ->setArrayParams([
          'data' => [],
          'total' => 0
        ])
        ->noDestroy();
    }

    $nivelTable = $this->getTable();
    $usuarioTable = DB_PREFIX . '_usuario';
    $usuarioNivelTable = DB_PREFIX . '_usuario_nivelacesso';

    $params = [];
    $where = "WHERE 1 = 1";

    if ($orgId > 0) {
      $where .= "
        AND (
          {$nivelTable}.nva_escopo = 'Projeto'
          OR {$nivelTable}.nva_idorganizacao = :nva_idorganizacao
        )
      ";

      $params['nva_idorganizacao'] = $orgId;
    }

    if ($empId > 0) {
      $where .= "
        AND (
          {$nivelTable}.nva_escopo <> 'Empresa'
          OR {$nivelTable}.nva_idempresa = :nva_idempresa
        )
      ";

      $params['nva_idempresa'] = $empId;
    }

    if ($conteudo !== '') {
      $where .= "
        AND (
          CAST({$nivelTable}.nva_id AS CHAR) LIKE :conteudo_nva_id
          OR {$nivelTable}.nva_nome LIKE :conteudo_nva_nome
          OR {$nivelTable}.nva_descricao LIKE :conteudo_nva_descricao
          OR {$nivelTable}.nva_escopo LIKE :conteudo_nva_escopo
          OR {$nivelTable}.nva_status LIKE :conteudo_nva_status
        )
      ";

      $params['conteudo_nva_id'] = "%{$conteudo}%";
      $params['conteudo_nva_nome'] = "%{$conteudo}%";
      $params['conteudo_nva_descricao'] = "%{$conteudo}%";
      $params['conteudo_nva_escopo'] = "%{$conteudo}%";
      $params['conteudo_nva_status'] = "%{$conteudo}%";
    }

    if ($escopo !== '') {
      $where .= " AND {$nivelTable}.nva_escopo = :nva_escopo";
      $params['nva_escopo'] = $escopo;
    }

    if ($status !== '') {
      $where .= " AND {$nivelTable}.nva_status = :nva_status";
      $params['nva_status'] = $status;
    }

    if ($imutavel !== '') {
      $where .= " AND {$nivelTable}.nva_imutavel = :nva_imutavel";
      $params['nva_imutavel'] = (int) $imutavel;
    }

    if ($padraoInicial !== '') {
      $where .= " AND {$nivelTable}.nva_padrao_inicial = :nva_padrao_inicial";
      $params['nva_padrao_inicial'] = (int) $padraoInicial;
    }

    $camposData = [
      'nva_dtcad' => "{$nivelTable}.nva_dtcad",
      'nva_dtalter' => "{$nivelTable}.nva_dtalter",
      'dtcad' => "{$nivelTable}.nva_dtcad",
      'dtalter' => "{$nivelTable}.nva_dtalter"
    ];

    if ($tipoFiltro !== '' && isset($camposData[$campoFiltro]) && $dataInicio !== '') {
      if ($tipoFiltro === 'Periodo' && $dataFim !== '') {
        $where .= " AND DATE({$camposData[$campoFiltro]}) BETWEEN :data_inicio AND :data_fim";
        $params['data_inicio'] = $dataInicio;
        $params['data_fim'] = $dataFim;
      } else {
        $where .= " AND DATE({$camposData[$campoFiltro]}) = :data_inicio";
        $params['data_inicio'] = $dataInicio;
      }
    }

    $camposOrdem = [
      'nva_id' => "{$nivelTable}.nva_id",
      'nva_nome' => "{$nivelTable}.nva_nome",
      'nva_escopo' => "{$nivelTable}.nva_escopo",
      'nva_status' => "{$nivelTable}.nva_status",
      'nva_qtdusuario' => "nva_qtdusuario",
      'nva_dtcad' => "{$nivelTable}.nva_dtcad",
      'nva_dtalter' => "{$nivelTable}.nva_dtalter",
      'dtcad' => "{$nivelTable}.nva_dtcad",
      'dtalter' => "{$nivelTable}.nva_dtalter"
    ];

    if (!isset($camposOrdem[$campoOrdem])) {
      $campoOrdem = 'nva_nome';
    }

    if (!in_array($ordem, ['ASC', 'DESC'], true)) {
      $ordem = 'ASC';
    }

    $limitSql = "";

    if ($limite !== 'all') {
      $limite = (int) $limite;

      if ($limite <= 0) {
        $limite = 1000;
      }

      if ($limite > 5000) {
        $limite = 5000;
      }

      $limitSql = "LIMIT {$limite}";
    }

    return $this->rawQuery(
      "SELECT
         {$nivelTable}.nva_id,
         {$nivelTable}.nva_nome,
         {$nivelTable}.nva_descricao,
         {$nivelTable}.nva_escopo,
         {$nivelTable}.nva_status,
         {$nivelTable}.nva_statusclass,
         {$nivelTable}.nva_imutavel,
         {$nivelTable}.nva_padrao_inicial,
         {$nivelTable}.nva_dtcad,
         {$nivelTable}.nva_dtalter,

          (
            SELECT COUNT(DISTINCT usu.usu_id)
            FROM {$usuarioNivelTable} una
            INNER JOIN {$usuarioTable} usu ON usu.usu_id = una.una_idusuario
            WHERE usu.usu_status = 'Ativo'
            AND una.una_status = 'Ativo'
            AND una.una_idnivelacesso = {$nivelTable}.nva_id
          ) AS nva_qtdusuario

       FROM {$nivelTable}

       {$where}

       ORDER BY {$camposOrdem[$campoOrdem]} {$ordem},
         {$nivelTable}.nva_id ASC

       {$limitSql}",
      $params,
      true,
      "Níveis de acesso encontrados com sucesso||Erro ao pesquisar níveis de acesso"
    );
  }
}
