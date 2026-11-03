<?php

namespace Source\Model;

use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;

class PalavraChaveModel extends SimpleModel
{
  protected string $tabela = 'palavra_chave';
  protected string $prefixo = 'pch';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'nenhum';
  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public static function getCatalogo(): array
  {
    return [
      'projeto' => [
        'label' => 'Projeto',
        'tabela' => 'lev_projeto',
        'colunas' => [
          'prj_nome' => ['palavra' => 'projeto.nome', 'descricao' => 'Nome técnico do projeto'],
          'prj_titulo' => ['palavra' => 'projeto.titulo', 'descricao' => 'Título público do projeto'],
          'prj_descricao' => ['palavra' => 'projeto.descricao', 'descricao' => 'Descrição do projeto'],
          'prj_dominio' => ['palavra' => 'projeto.dominio', 'descricao' => 'Domínio do projeto'],
          'prj_url_central' => ['palavra' => 'projeto.url', 'descricao' => 'URL principal do projeto'],
          'prj_status' => ['palavra' => 'projeto.status', 'descricao' => 'Status do projeto'],
          'prj_tipo' => ['palavra' => 'projeto.tipo', 'descricao' => 'Tipo de ambiente do projeto']
        ]
      ],
      'organizacao' => [
        'label' => 'Organização',
        'tabela' => 'lev_organizacao',
        'colunas' => [
          'org_cnpjcpf' => ['palavra' => 'organizacao.documento', 'descricao' => 'Documento da organização'],
          'org_razaosocial' => ['palavra' => 'organizacao.nome', 'descricao' => 'Razão social da organização'],
          'org_nomefantasia' => ['palavra' => 'organizacao.nomefantasia', 'descricao' => 'Nome fantasia da organização'],
          'org_descricao' => ['palavra' => 'organizacao.descricao', 'descricao' => 'Descrição da organização'],
          'org_status' => ['palavra' => 'organizacao.status', 'descricao' => 'Status da organização'],
          'org_ambiente' => ['palavra' => 'organizacao.ambiente', 'descricao' => 'Ambiente da organização'],
          'org_logo' => ['palavra' => 'organizacao.logo', 'descricao' => 'Logo da organização']
        ]
      ],
      'empresa' => [
        'label' => 'Empresa/Igreja',
        'tabela' => 'lev_empresa',
        'colunas' => [
          'emp_cnpjcpf' => ['palavra' => 'empresa.documento', 'descricao' => 'Documento da empresa ou igreja'],
          'emp_razaosocial' => ['palavra' => 'empresa.nome', 'descricao' => 'Razão social da empresa ou igreja'],
          'emp_nomefantasia' => ['palavra' => 'empresa.nomefantasia', 'descricao' => 'Nome fantasia da empresa ou igreja'],
          'emp_descricao' => ['palavra' => 'empresa.descricao', 'descricao' => 'Descrição da empresa ou igreja'],
          'emp_status' => ['palavra' => 'empresa.status', 'descricao' => 'Status da empresa ou igreja'],
          'emp_tipo' => ['palavra' => 'empresa.tipo', 'descricao' => 'Tipo da empresa ou igreja'],
          'emp_logo' => ['palavra' => 'empresa.logo', 'descricao' => 'Logo da empresa ou igreja']
        ]
      ],
      'usuario' => [
        'label' => 'Usuário',
        'tabela' => 'lev_usuario',
        'colunas' => [
          'usu_cnpjcpf' => ['palavra' => 'usuario.documento', 'descricao' => 'Documento do usuário'],
          'usu_razaosocial' => ['palavra' => 'usuario.nome', 'descricao' => 'Nome completo do usuário'],
          'usu_nomefantasia' => ['palavra' => 'usuario.nomefantasia', 'descricao' => 'Nome social do usuário'],
          'usu_email' => ['palavra' => 'usuario.email', 'descricao' => 'E-mail do usuário'],
          'usu_celular' => ['palavra' => 'usuario.celular', 'descricao' => 'Celular do usuário'],
          'usu_username' => ['palavra' => 'usuario.username', 'descricao' => 'Nome de usuário para acesso'],
          'usu_status' => ['palavra' => 'usuario.status', 'descricao' => 'Status do usuário'],
          'usu_newsletter' => ['palavra' => 'usuario.newsletter', 'descricao' => 'Aceite de comunicações do usuário'],
          'usu_idorganizacao' => ['palavra' => 'usuario.idorganizacao', 'descricao' => 'Identificador da organização do usuário'],
          'usu_idempresa' => ['palavra' => 'usuario.idempresa', 'descricao' => 'Identificador da empresa do usuário']
        ]
      ],
      'plano' => [
        'label' => 'Plano',
        'tabela' => 'lev_plano',
        'colunas' => [
          'pla_id' => ['palavra' => 'plano.id', 'descricao' => 'Identificador do plano'],
          'pla_nome' => ['palavra' => 'plano.nome', 'descricao' => 'Nome do plano'],
          'pla_tipo' => ['palavra' => 'plano.tipo', 'descricao' => 'Tipo do plano']
        ]
      ],
      'contratacao' => [
        'label' => 'Contratação',
        'tabela' => 'lev_plano_contratado',
        'colunas' => [
          'plc_id' => ['palavra' => 'contratacao.id', 'descricao' => 'Identificador da contratação'],
          'plc_status' => ['palavra' => 'contratacao.status', 'descricao' => 'Status da contratação'],
          'plc_statusfinanceiro' => ['palavra' => 'contratacao.statusfinanceiro', 'descricao' => 'Status financeiro da contratação'],
          'plc_ciclo' => ['palavra' => 'contratacao.ciclo', 'descricao' => 'Ciclo da contratação'],
          'plc_valor' => ['palavra' => 'contratacao.valor', 'descricao' => 'Valor da contratação'],
          'plc_dtpagamento' => ['palavra' => 'contratacao.dtpagamento', 'descricao' => 'Data do pagamento da contratação'],
          'plc_dtinicio' => ['palavra' => 'contratacao.dtinicio', 'descricao' => 'Início da vigência da contratação'],
          'plc_dtfim' => ['palavra' => 'contratacao.dtfim', 'descricao' => 'Fim da vigência da contratação'],
          'plc_dtvencimento' => ['palavra' => 'contratacao.dtvencimento', 'descricao' => 'Vencimento da contratação']
        ]
      ],
      'pagamento' => [
        'label' => 'Pagamento',
        'tabela' => 'lev_plano_contratado',
        'colunas' => [
          'plc_pagarme_status' => ['palavra' => 'pagamento.status', 'descricao' => 'Status do pagamento no gateway'],
          'plc_pagarme_ultimoevento' => ['palavra' => 'pagamento.evento', 'descricao' => 'Último evento de pagamento recebido']
        ]
      ]
    ];
  }

  public static function getCampo(string $grupo, string $chave): ?array
  {
    $catalogo = self::getCatalogo();
    $grupo = strtolower(trim($grupo));
    $chave = trim($chave);

    return $catalogo[$grupo]['colunas'][$chave] ?? null;
  }

  public function getPalavrasChaves(string $pesquisa = ''): object
  {
    $where = 'WHERE 1 = 1';
    $params = [];
    $pesquisa = trim($pesquisa);

    if ($pesquisa !== '') {
      $where .= ' AND (pch_grupo LIKE :pesquisa_grupo OR pch_palavra LIKE :pesquisa_palavra OR pch_chave LIKE :pesquisa_chave OR pch_descricao LIKE :pesquisa_descricao)';
      $like = '%' . $pesquisa . '%';
      $params = [
        'pesquisa_grupo' => $like,
        'pesquisa_palavra' => $like,
        'pesquisa_chave' => $like,
        'pesquisa_descricao' => $like
      ];
    }

    return $this->rawQuery(
      "SELECT * FROM {$this->getTable()} {$where} ORDER BY pch_grupo ASC, pch_palavra ASC",
      $params,
      true,
      'Palavras-chave encontradas com sucesso||Erro ao buscar palavras-chave'
    );
  }

  public function getPalavrasChavesAtivas(): object
  {
    return $this->rawQuery(
      "SELECT * FROM {$this->getTable()} WHERE pch_status = 'Ativo' ORDER BY pch_grupo ASC, pch_palavra ASC",
      [],
      true,
      'Palavras-chave ativas encontradas com sucesso||Erro ao buscar palavras-chave ativas'
    );
  }

  public function salvarPalavraChave(array $data): object
  {
    $data = $this->filtrarCampos($data);
    $id = (int) ($data['pch_id'] ?? 0);
    $grupo = strtolower(trim((string) ($data['pch_grupo'] ?? '')));
    $chave = trim((string) ($data['pch_chave'] ?? ''));
    $campo = self::getCampo($grupo, $chave);

    if (!$campo) {
      return (new SimpleResponse('Atenção', 'O campo informado não está disponível no catálogo do módulo.'))
        ->warning()
        ->noDestroy();
    }

    $status = ($data['pch_status'] ?? 'Ativo') === 'Inativo' ? 'Inativo' : 'Ativo';
    $descricao = trim((string) ($data['pch_descricao'] ?? ''));
    $descricao = $descricao !== '' ? $descricao : $campo['descricao'];

    $duplicado = $this->rawQuery(
      "SELECT pch_id FROM {$this->getTable()}
       WHERE pch_grupo = :pch_grupo
       AND pch_palavra = :pch_palavra
       AND pch_id != :pch_id
       LIMIT 1",
      [
        'pch_grupo' => $grupo,
        'pch_palavra' => $campo['palavra'],
        'pch_id' => $id
      ],
      true,
      'Validação concluída||Erro ao validar palavra-chave'
    );

    if ($duplicado->status && $duplicado->total > 0) {
      return (new SimpleResponse('Atenção', 'Esta palavra-chave já está cadastrada.'))
        ->warning()
        ->noDestroy();
    }

    $registro = [
      'pch_grupo' => $grupo,
      'pch_palavra' => $campo['palavra'],
      'pch_chave' => $chave,
      'pch_descricao' => $descricao,
      'pch_status' => $status,
      'pch_statusclass' => $status === 'Ativo' ? 'success' : 'danger'
    ];

    if ($id > 0) {
      return $this->update(
        $registro,
        ['pch_id' => $id],
        'Palavra-chave atualizada com sucesso||Erro ao atualizar palavra-chave'
      );
    }

    return $this->insert(
      $registro,
      'Palavra-chave cadastrada com sucesso||Erro ao cadastrar palavra-chave'
    );
  }

  public function excluirPalavraChave(int $id): object
  {
    if ($id <= 0) {
      return (new SimpleResponse('Atenção', 'Palavra-chave não identificada.'))
        ->warning()
        ->noDestroy();
    }

    return $this->delete(
      ['pch_id' => $id],
      'Palavra-chave excluída com sucesso||Erro ao excluir palavra-chave'
    );
  }
}
