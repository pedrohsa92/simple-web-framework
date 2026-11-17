<?php

namespace Source\Model;

use Source\Core\SimpleModel;

class PlanoContratadoModel extends SimpleModel
{
  protected string $tabela = 'plano_contratado';
  protected string $prefixo = 'plc';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'manual';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getPlanoVigentePorOrganizacao(int $orgId): object
  {
    $this->sincronizarStatusPeriodo($orgId);
    $planoTable = DB_PREFIX . '_plano';

    $sql = "
      SELECT * FROM {$this->getTable()}
      INNER JOIN {$planoTable} ON pla_id = plc_idplano
      WHERE plc_idorganizacao = :org_id
        AND plc_status IN ('Teste', 'Ativo', 'Agendado')
        AND plc_statusfinanceiro IN ('Isento', 'Pago', 'Pago Manual')
        AND plc_dtinicio IS NOT NULL
        AND plc_dtfim IS NOT NULL
        AND NOW() >= plc_dtinicio
        AND NOW() < plc_dtfim
      ORDER BY plc_dtinicio DESC, plc_id DESC
    ";

    $response = $this->rawQuery($sql, [
      'org_id' => $orgId
    ]);

    if ($response->status && $response->total > 0) {
      foreach ($response->data as $plano) {
        $this->normalizarParametro($plano);
      }
    }

    return $response;
  }

  private function normalizarParametro(object $plano): void
  {
    $plano->pla_parametro = json_decode($plano->pla_parametro ?? '{}');
    $plano->plc_parametro = json_decode($plano->plc_parametro ?? '{}');
  }

  public function getContratacoes(string $pesquisa = ''): object
  {
    $organizacaoTable = DB_PREFIX . '_organizacao';
    $planoTable = DB_PREFIX . '_plano';

    $params = [];
    $where = 'WHERE 1 = 1';
    $pesquisa = trim($pesquisa);

    if ($pesquisa !== '') {
      $where .= "
        AND (
          CAST(plc.plc_id AS CHAR) LIKE :pesquisa_id
          OR org.org_razaosocial LIKE :pesquisa_organizacao
          OR pla.pla_nome LIKE :pesquisa_plano
          OR plc.plc_status LIKE :pesquisa_status
          OR plc.plc_statusfinanceiro LIKE :pesquisa_financeiro
        )
      ";

      $like = "%{$pesquisa}%";
      $params['pesquisa_id'] = $like;
      $params['pesquisa_organizacao'] = $like;
      $params['pesquisa_plano'] = $like;
      $params['pesquisa_status'] = $like;
      $params['pesquisa_financeiro'] = $like;
    }

    return $this->rawQuery(
      "SELECT
         plc.*,
         org.org_razaosocial,
         org.org_nomefantasia,
         pla.pla_nome,
         pla.pla_tipo,
         pla.pla_ambiente AS pla_ambiente
       FROM {$this->getTable()} plc
       INNER JOIN {$organizacaoTable} org ON org.org_id = plc.plc_idorganizacao
       INNER JOIN {$planoTable} pla ON pla.pla_id = plc.plc_idplano
       {$where}
       ORDER BY plc.plc_dtinicio DESC, plc.plc_id DESC",
      $params,
      true,
      'Contratações encontradas com sucesso||Erro ao buscar contratações'
    );
  }

  public function getContratacoesPorOrganizacao(int $orgId, array $filtros = []): object
  {
    $this->sincronizarStatusPeriodo($orgId);
    $planoTable = DB_PREFIX . '_plano';
    $params = ['org_id' => $orgId];
    $where = 'WHERE plc.plc_idorganizacao = :org_id';
    $pesquisa = trim((string) ($filtros['pesquisa'] ?? ''));

    if ($pesquisa !== '') {
      $where .= ' AND (
        CAST(plc.plc_id AS CHAR) LIKE :pesquisa_id
        OR pla.pla_nome LIKE :pesquisa_plano
        OR plc.plc_status LIKE :pesquisa_status
        OR plc.plc_statusfinanceiro LIKE :pesquisa_financeiro
        OR plc.plc_ciclo LIKE :pesquisa_ciclo
      )';
      $like = "%{$pesquisa}%";
      $params += [
        'pesquisa_id' => $like,
        'pesquisa_plano' => $like,
        'pesquisa_status' => $like,
        'pesquisa_financeiro' => $like,
        'pesquisa_ciclo' => $like
      ];
    }

    foreach (['status' => 'plc.plc_status', 'financeiro' => 'plc.plc_statusfinanceiro'] as $campo => $coluna) {
      $valor = trim((string) ($filtros[$campo] ?? ''));
      if ($valor !== '') {
        $where .= " AND {$coluna} = :filtro_{$campo}";
        $params["filtro_{$campo}"] = $valor;
      }
    }

    $dataInicio = trim((string) ($filtros['data_inicio'] ?? ''));
    if ($dataInicio !== '') {
      $where .= ' AND DATE(plc.plc_dtcad) >= :data_inicio';
      $params['data_inicio'] = $dataInicio;
    }

    $dataFim = trim((string) ($filtros['data_fim'] ?? ''));
    if ($dataFim !== '') {
      $where .= ' AND DATE(plc.plc_dtcad) <= :data_fim';
      $params['data_fim'] = $dataFim;
    }

    return $this->rawQuery(
      "SELECT plc.*, pla.pla_nome, pla.pla_tipo, pla.pla_ambiente,
              pla.pla_descricaoresumida,
              anterior.plc_id AS plc_idcontratacaoanterior_real
       FROM {$this->getTable()} plc
       INNER JOIN {$planoTable} pla ON pla.pla_id = plc.plc_idplano
       LEFT JOIN {$this->getTable()} anterior ON anterior.plc_id = plc.plc_idcontratacaoanterior
       {$where}
       ORDER BY COALESCE(plc.plc_dtinicio, plc.plc_dtcad) DESC, plc.plc_id DESC",
      $params,
      true,
      'Contratações encontradas com sucesso||Erro ao buscar contratações'
    );
  }

  public function getContratacaoPorOrganizacao(int $plcId, int $orgId): object
  {
    $response = $this->rawQuery(
      "SELECT plc.*, org.org_razaosocial, org.org_nomefantasia, org.org_logo,
              pla.pla_nome, pla.pla_tipo, pla.pla_descricao, pla.pla_descricaoresumida,
              pla.pla_ambiente, pla.pla_valormensal, pla.pla_valoranual,
              anterior.plc_id AS anterior_id, anterior.plc_dtfim AS anterior_dtfim,
              anterior.plc_status AS anterior_status
       FROM {$this->getTable()} plc
       INNER JOIN " . DB_PREFIX . "_organizacao org ON org.org_id = plc.plc_idorganizacao
       INNER JOIN " . DB_PREFIX . "_plano pla ON pla.pla_id = plc.plc_idplano
       LEFT JOIN {$this->getTable()} anterior ON anterior.plc_id = plc.plc_idcontratacaoanterior
       WHERE plc.plc_id = :plc_id AND plc.plc_idorganizacao = :org_id
       LIMIT 1",
      ['plc_id' => $plcId, 'org_id' => $orgId],
      true,
      'Contratação encontrada com sucesso||Erro ao buscar contratação'
    );

    if ($response->status && !empty($response->data[0])) {
      $this->normalizarParametro($response->data[0]);
    }

    return $response;
  }

  public function getSituacaoAcessoPorOrganizacao(int $orgId): object
  {
    if ($orgId <= 0) {
      return (object) [
        'modo' => 'liberado',
        'tipo' => 'sem_contexto',
        'contrato' => null,
        'vigente' => null
      ];
    }

    $vigente = $this->getPlanoVigentePorOrganizacao($orgId);

    if ($vigente->status && !empty($vigente->data[0])) {
      return (object) [
        'modo' => 'liberado',
        'tipo' => 'vigente',
        'contrato' => $vigente->data[0],
        'vigente' => $vigente->data[0]
      ];
    }

    $planoTable = DB_PREFIX . '_plano';
    $ultimo = $this->rawQuery(
      "SELECT plc.*, pla.pla_nome, pla.pla_tipo, pla.pla_ambiente,
              pla.pla_descricaoresumida, pla.pla_valormensal, pla.pla_valoranual
       FROM {$this->getTable()} plc
       INNER JOIN {$planoTable} pla ON pla.pla_id = plc.plc_idplano
       WHERE plc.plc_idorganizacao = :org_id
       ORDER BY COALESCE(plc.plc_dtinicio, plc.plc_dtcad) DESC, plc.plc_id DESC
       LIMIT 1",
      ['org_id' => $orgId],
      true,
      'ContrataÃ§Ã£o encontrada||Erro ao buscar situaÃ§Ã£o da contrataÃ§Ã£o'
    );

    $contrato = $ultimo->status && !empty($ultimo->data[0]) ? $ultimo->data[0] : null;

    if (!$contrato) {
      return (object) [
        'modo' => 'liberado',
        'tipo' => 'sem_contratacao',
        'contrato' => null,
        'vigente' => null
      ];
    }

    $tipo = in_array($contrato->plc_statusfinanceiro ?? '', ['Em aberto', 'Falhou'], true)
      && in_array($contrato->plc_status ?? '', ['Pendente', 'Agendado'], true)
      ? 'pagamento'
      : (($contrato->plc_status ?? '') === 'Cancelado' ? 'cancelado' : 'expirado');

    return (object) [
      'modo' => 'regularizacao',
      'tipo' => $tipo,
      'contrato' => $contrato,
      'vigente' => null
    ];
  }

  public function getRenovacaoPendente(int $orgId): object
  {
    $this->sincronizarStatusPeriodo($orgId);
    return $this->rawQuery(
      "SELECT plc.*, pla.pla_nome, pla.pla_tipo
       FROM {$this->getTable()} plc
       INNER JOIN " . DB_PREFIX . "_plano pla ON pla.pla_id = plc.plc_idplano
       WHERE plc.plc_idorganizacao = :org_id
         AND plc.plc_origem = 'Renovacao'
         AND plc.plc_status IN ('Pendente', 'Agendado')
         AND plc.plc_statusfinanceiro IN ('Em aberto', 'Falhou')
       ORDER BY plc.plc_id DESC
       LIMIT 1",
      ['org_id' => $orgId],
      true,
      'Renovação encontrada||Erro ao buscar renovação'
    );
  }

  public function getRenovacaoProgramada(int $orgId): object
  {
    $this->sincronizarStatusPeriodo($orgId);
    return $this->rawQuery(
      "SELECT plc.*, pla.pla_nome, pla.pla_tipo
       FROM {$this->getTable()} plc
       INNER JOIN " . DB_PREFIX . "_plano pla ON pla.pla_id = plc.plc_idplano
       WHERE plc.plc_idorganizacao = :org_id
         AND plc.plc_origem = 'Renovacao'
         AND plc.plc_status = 'Agendado'
         AND plc.plc_statusfinanceiro IN ('Pago', 'Pago Manual')
         AND plc.plc_dtinicio IS NOT NULL
         AND plc.plc_dtinicio > NOW()
       ORDER BY plc.plc_id DESC
       LIMIT 1",
      ['org_id' => $orgId],
      true,
      'Renovação programada encontrada||Erro ao buscar renovação programada'
    );
  }

  public function sincronizarStatusPeriodo(int $orgId): void
  {
    if ($orgId <= 0) {
      return;
    }

    $this->rawQuery(
      "UPDATE {$this->getTable()}
       SET plc_status = 'Ativo', plc_statusclass = 'success'
       WHERE plc_idorganizacao = :org_id
         AND plc_status = 'Agendado'
         AND plc_statusfinanceiro IN ('Pago', 'Pago Manual')
         AND plc_dtinicio IS NOT NULL
         AND plc_dtfim IS NOT NULL
         AND NOW() >= plc_dtinicio
         AND NOW() < plc_dtfim",
      ['org_id' => $orgId],
      false
    );

    $this->rawQuery(
      "UPDATE {$this->getTable()}
       SET plc_status = 'Expirado', plc_statusclass = 'secondary'
       WHERE plc_idorganizacao = :org_id
         AND plc_status IN ('Teste', 'Ativo')
         AND plc_dtfim IS NOT NULL
         AND NOW() >= plc_dtfim",
      ['org_id' => $orgId],
      false
    );
  }

  public function getContratacaoById(int $plcId): object
  {
    $organizacaoTable = DB_PREFIX . '_organizacao';
    $planoTable = DB_PREFIX . '_plano';

    return $this->rawQuery(
      "SELECT
         plc.*,
         org.org_razaosocial,
         org.org_nomefantasia,
         org.org_logo,
         pla.pla_nome,
         pla.pla_tipo,
         pla.pla_descricao,
         pla.pla_ambiente
       FROM {$this->getTable()} plc
       INNER JOIN {$organizacaoTable} org ON org.org_id = plc.plc_idorganizacao
       INNER JOIN {$planoTable} pla ON pla.pla_id = plc.plc_idplano
       WHERE plc.plc_id = :plc_id
       LIMIT 1",
      ['plc_id' => $plcId],
      true,
      'Contratação encontrada com sucesso||Erro ao buscar contratação'
    );
  }
}
