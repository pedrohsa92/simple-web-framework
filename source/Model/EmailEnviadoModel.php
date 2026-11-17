<?php

namespace Source\Model;

use Source\Core\SimpleModel;

class EmailEnviadoModel extends SimpleModel
{
  protected string $tabela = 'email_enviado';
  protected string $prefixo = 'eme';
  protected string $identificador = 'id';
  protected string $escopoContexto = 'nenhum';
  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function registrarTentativa(array $dados): object
  {
    return $this->insert($dados, 'Registro de e-mail criado||Erro ao registrar e-mail');
  }

  public function atualizarTentativa(int $id, array $dados): object
  {
    if ($id <= 0) {
      return $this->warning('Registro de e-mail não identificado.');
    }

    return $this->update($dados, ['eme_id' => $id], 'Registro de e-mail atualizado||Erro ao atualizar registro de e-mail');
  }

  public function getIndicadoresProjeto(): object
  {
    return $this->rawQuery(
      "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(eme_status = 'Pendente'), 0) AS pendentes,
        COALESCE(SUM(eme_status = 'Agendado'), 0) AS agendados,
        COALESCE(SUM(eme_status = 'Enviado'), 0) AS enviados,
        COALESCE(SUM(eme_status = 'Falhou'), 0) AS falhos,
        COALESCE(SUM(eme_status = 'Cancelado'), 0) AS cancelados
       FROM {$this->getTable()}
       WHERE eme_idorganizacao = 0 AND eme_idempresa = 0",
      [],
      true,
      'Indicadores de e-mail encontrados com sucesso||Erro ao buscar indicadores de e-mail'
    );
  }

  public function getUltimosProjeto(int $limite = 5): object
  {
    $limite = max(1, min(20, $limite));

    return $this->rawQuery(
      "SELECT eme_id, eme_status, eme_statusclass, eme_destinarionome,
              eme_destinatarioemail, eme_title, eme_dtenvio, eme_dtcad
       FROM {$this->getTable()}
       WHERE eme_idorganizacao = 0 AND eme_idempresa = 0
       ORDER BY eme_dtcad DESC, eme_id DESC
       LIMIT {$limite}",
      [],
      true,
      'Últimos e-mails encontrados com sucesso||Erro ao buscar últimos e-mails'
    );
  }

  public function getEnviosProjeto(string $pesquisa = ''): object
  {
    $params = [];
    $where = 'WHERE eme.eme_idorganizacao = 0 AND eme.eme_idempresa = 0';
    $pesquisa = trim($pesquisa);

    if ($pesquisa !== '') {
      $where .= " AND (
        eme.eme_id = :pesquisa_id
        OR eme.eme_title LIKE :pesquisa_title
        OR eme.eme_destinarionome LIKE :pesquisa_nome
        OR eme.eme_destinatarioemail LIKE :pesquisa_email
        OR eme.eme_status LIKE :pesquisa_status
        OR emt.emt_nome LIKE :pesquisa_template
      )";
      $params = [
        'pesquisa_id' => ctype_digit($pesquisa) ? (int) $pesquisa : -1,
        'pesquisa_title' => '%' . $pesquisa . '%',
        'pesquisa_nome' => '%' . $pesquisa . '%',
        'pesquisa_email' => '%' . $pesquisa . '%',
        'pesquisa_status' => '%' . $pesquisa . '%',
        'pesquisa_template' => '%' . $pesquisa . '%'
      ];
    }

    return $this->rawQuery(
      "SELECT eme.eme_id, eme.eme_idtemplate, eme.eme_tentativa,
              eme.eme_status, eme.eme_statusclass, eme.eme_dtagendada,
              eme.eme_dtenvio, eme.eme_destinarionome, eme.eme_destinatarioemail,
              eme.eme_title, eme.eme_dtcad, eme.eme_dtalter,
              emt.emt_nome, emt.emt_templateprojeto
       FROM {$this->getTable()} eme
       LEFT JOIN " . DB_PREFIX . "_email_template emt ON emt.emt_id = eme.eme_idtemplate
       {$where}
       ORDER BY eme.eme_dtcad DESC, eme.eme_id DESC",
      $params,
      true,
      'Envios de e-mail encontrados com sucesso||Erro ao buscar envios de e-mail'
    );
  }

  public function getEnvioProjetoById(int $id): object
  {
    return $this->rawQuery(
      "SELECT eme.*, emt.emt_nome, emt.emt_templateprojeto
       FROM {$this->getTable()} eme
       LEFT JOIN " . DB_PREFIX . "_email_template emt ON emt.emt_id = eme.eme_idtemplate
       WHERE eme.eme_id = :eme_id
         AND eme.eme_idorganizacao = 0
         AND eme.eme_idempresa = 0
       LIMIT 1",
      ['eme_id' => $id],
      true,
      'Envio de e-mail encontrado com sucesso||Envio de e-mail nÃ£o encontrado'
    );
  }
}
