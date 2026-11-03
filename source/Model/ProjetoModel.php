<?php

namespace Source\Model;

use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;
use Source\Core\SimpleUpload;

class ProjetoModel extends SimpleModel
{
  protected string $tabela = 'projeto';
  protected string $prefixo = 'prj';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'nenhum';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getProjeto(): object
  {
    return $this->findById(1);
  }

  public function getOrganizacoesResumo(string $pesquisa = ''): object
  {
    $organizacaoTable = DB_PREFIX . '_organizacao';
    $empresaTable = DB_PREFIX . '_empresa';
    $contratadoTable = DB_PREFIX . '_plano_contratado';
    $planoTable = DB_PREFIX . '_plano';

    $params = [];
    $where = 'WHERE 1 = 1';

    $pesquisa = trim($pesquisa);

    if ($pesquisa !== '') {
      $where .= "
        AND (
          org.org_razaosocial LIKE :pesquisa_razao
          OR org.org_nomefantasia LIKE :pesquisa_fantasia
          OR org.org_cnpjcpf LIKE :pesquisa_documento
          OR pla.pla_nome LIKE :pesquisa_plano
        )
      ";

      $like = "%{$pesquisa}%";
      $params['pesquisa_razao'] = $like;
      $params['pesquisa_fantasia'] = $like;
      $params['pesquisa_documento'] = $like;
      $params['pesquisa_plano'] = $like;
    }

    $sql = "
      SELECT
        org.org_id,
        org.org_chave,
        org.org_cnpjcpf,
        org.org_razaosocial,
        org.org_nomefantasia,
        org.org_logo,
        org.org_status,
        org.org_statusclass,
        org.org_dtcad,
        pla.pla_id,
        pla.pla_nome,
        plc.plc_id,
        plc.plc_status,
        plc.plc_statusclass,
        plc.plc_statusfinanceiro,
        plc.plc_ciclo,
        plc.plc_ambiente,
        plc.plc_valor,
        plc.plc_dtinicio,
        plc.plc_dtfim,
        plc.plc_dtvencimento,
        (
          SELECT COUNT(*)
          FROM {$empresaTable} emp_count
          WHERE emp_count.emp_idorganizacao = org.org_id
        ) AS total_empresas
      FROM {$organizacaoTable} org
      LEFT JOIN {$contratadoTable} plc
        ON plc.plc_id = (
          SELECT plc_atual.plc_id
          FROM {$contratadoTable} plc_atual
          WHERE plc_atual.plc_idorganizacao = org.org_id
          ORDER BY
            CASE
              WHEN plc_atual.plc_status IN ('Teste', 'Ativo')
                AND plc_atual.plc_statusfinanceiro IN ('Isento', 'Pago', 'Pago Manual')
                AND plc_atual.plc_dtinicio IS NOT NULL
                AND plc_atual.plc_dtfim IS NOT NULL
                AND NOW() BETWEEN plc_atual.plc_dtinicio AND plc_atual.plc_dtfim THEN 0
              ELSE 1
            END,
            plc_atual.plc_dtinicio DESC,
            plc_atual.plc_id DESC
          LIMIT 1
        )
      LEFT JOIN {$planoTable} pla ON pla.pla_id = plc.plc_idplano
      {$where}
      ORDER BY org.org_razaosocial ASC, org.org_id ASC
    ";

    $response = $this->rawQuery(
      $sql,
      $params,
      true,
      'Organizações encontradas com sucesso||Erro ao buscar organizações'
    );

    if (!$response->status || empty($response->data)) {
      return $response;
    }

    foreach ($response->data as $organizacao) {
      $empresas = $this->rawQuery(
        "SELECT emp_id, emp_razaosocial, emp_cnpjcpf, emp_logo, emp_status, emp_statusclass
         FROM {$empresaTable}
         WHERE emp_idorganizacao = :emp_idorganizacao
         ORDER BY emp_razaosocial ASC, emp_id ASC",
        [
          'emp_idorganizacao' => (int) $organizacao->org_id
        ],
        true,
        'Igrejas encontradas com sucesso||Erro ao buscar igrejas'
      );

      $organizacao->empresas = $empresas->status ? ($empresas->data ?? []) : [];
    }

    return $response;
  }

  public function salvarProjeto(array $data): object
  {
    $data = $this->filtrarCampos($data);

    $camposPermitidos = [
      'prj_id',
      'prj_nome',
      'prj_titulo',
      'prj_descricao'
    ];

    $data = array_intersect_key($data, array_flip($camposPermitidos));

    if (!empty($data['prj_id'])) {
      return $this->updateProjeto($data);
    }

    return $this->insertProjeto($data);
  }

  public function salvarDocumento(array $data): object
  {
    $data = $this->filtrarCampos($data);
    $prjId = (int) ($data['prj_id'] ?? 0);
    $documento = strtolower(trim((string) ($data['prj_documento'] ?? '')));
    $conteudo = trim((string) ($data['prj_conteudo'] ?? ''));

    if ($prjId !== 1) {
      return (new SimpleResponse('Erro', 'Erro ao identificar o projeto'))
        ->erro()
        ->noDestroy();
    }

    $documentos = [
      'termosuso' => [
        'campo' => 'prj_termosuso',
        'versao' => 'prj_termosusoversao',
        'titulo' => 'Termos de uso'
      ],
      'avisoprivacidade' => [
        'campo' => 'prj_avisoprivacidade',
        'versao' => 'prj_avisoprivacidadeversao',
        'titulo' => 'Aviso de privacidade'
      ]
    ];

    if (!isset($documentos[$documento])) {
      return (new SimpleResponse('Atenção', 'Documento inválido.'))
        ->warning()
        ->setArrayParams(['campo' => 'prj_documento'])
        ->noDestroy();
    }

    if ($conteudo === '' || trim(strip_tags($conteudo)) === '') {
      return (new SimpleResponse('Atenção', 'Informe o conteúdo do documento.'))
        ->warning()
        ->setArrayParams(['campo' => 'prj_conteudo'])
        ->noDestroy();
    }

    $config = $documentos[$documento];
    $versao = date('dmYHis') . '-' . hash(
      'sha256',
      $config['campo'] . '|' . $conteudo . '|' . microtime(true) . '|' . bin2hex(random_bytes(16))
    );

    return $this->update(
      [
        $config['campo'] => $conteudo,
        $config['versao'] => $versao
      ],
      ['prj_id' => $prjId],
      $config['titulo'] . ' atualizado com sucesso||Erro ao atualizar ' . strtolower($config['titulo'])
    );
  }

  public function salvarGateway(array $data): object
  {
    $data = $this->filtrarCampos($data);
    $prjId = (int) ($data['prj_id'] ?? 0);

    if ($prjId !== 1) {
      return (new SimpleResponse('Erro', 'Erro ao identificar o projeto'))
        ->erro()
        ->noDestroy();
    }

    $gateway = trim((string) ($data['prj_gateway'] ?? ''));
    if (!in_array($gateway, ['Pagar.me', 'MercadoPago', 'InfinitePay'], true)) {
      return (new SimpleResponse('AtenÃ§Ã£o', 'Gateway invÃ¡lido.'))
        ->warning()
        ->setArrayParams(['campo' => 'prj_gateway'])
        ->noDestroy();
    }

    $publicKey = trim((string) ($data['prj_gateway_pk'] ?? ''));
    $secretKey = trim((string) ($data['prj_gateway_sk'] ?? ''));

    if ($gateway === 'Pagar.me' && ($publicKey === '' || $secretKey === '')) {
      return (new SimpleResponse('AtenÃ§Ã£o', 'Informe a Public Key e a Secret Key.'))
        ->warning()
        ->noDestroy();
    }

    $configuracao = json_decode((string) ($data['prj_gateway_configuracao'] ?? '{}'), true);
    $configuracao = is_array($configuracao) ? $configuracao : [];
    $configuracao['enabled'] = isset($configuracao['enabled']) ? (bool) $configuracao['enabled'] : true;
    $configuracao['account_id'] = trim((string) ($configuracao['account_id'] ?? ''));
    $configuracao['environment'] = in_array($configuracao['environment'] ?? '', ['test', 'live'], true)
      ? $configuracao['environment']
      : 'test';

    $regras = [];
    $totalPercentual = 0;

    foreach ($gateway === 'Pagar.me' ? (array) ($configuracao['rules'] ?? []) : [] as $regra) {
      if (!is_array($regra)) {
        continue;
      }

      $tipo = ($regra['type'] ?? '') === 'flat' ? 'flat' : 'percentage';
      $recebedor = trim((string) ($regra['recipient_id'] ?? ''));
      $valor = (float) ($regra['value'] ?? 0);

      if ($recebedor === '' || $valor < 0 || ($tipo === 'percentage' && $valor > 100)) {
        return (new SimpleResponse('AtenÃ§Ã£o', 'Existe uma regra de split invÃ¡lida.'))
          ->warning()
          ->noDestroy();
      }

      if ($tipo === 'percentage') {
        $totalPercentual += $valor;
      }

      $opcoes = is_array($regra['options'] ?? null) ? $regra['options'] : [];
      $regras[] = [
        'type' => $tipo,
        'value' => $tipo === 'flat' ? (int) round($valor) : round($valor, 2),
        'options' => [
          'liable' => !empty($opcoes['liable']),
          'charge_remainder_fee' => !empty($opcoes['charge_remainder_fee']),
          'charge_processing_fee' => !empty($opcoes['charge_processing_fee'])
        ],
        'recipient_id' => $recebedor
      ];
    }

    if ($totalPercentual > 100) {
      return (new SimpleResponse('AtenÃ§Ã£o', 'A soma das regras percentuais nÃ£o pode ultrapassar 100%.'))
        ->warning()
        ->noDestroy();
    }

    /*
    $metodosPadrao = [
      'credit_card' => ['label' => 'CartÃ£o de crÃ©dito', 'enabled' => true, 'fee' => 0],
      'debit_card' => ['label' => 'CartÃ£o de dÃ©bito', 'enabled' => false, 'fee' => 0],
      'pix' => ['label' => 'Pix', 'enabled' => true, 'fee' => 0],
      'meal' => ['label' => 'RefeiÃ§Ã£o/AlimentaÃ§Ã£o', 'enabled' => false, 'fee' => 0]
    ];
    $metodosRecebidos = [];
    $metodos = [];

    foreach ($metodosPadrao as $chave => $padrao) {
      $metodo = is_array($metodosRecebidos[$chave] ?? null) ? $metodosRecebidos[$chave] : [];
      $taxa = (float) ($metodo['fee'] ?? $padrao['fee']);
      $metodos[$chave] = [
        'label' => $padrao['label'],
        'enabled' => isset($metodo['enabled']) ? (bool) $metodo['enabled'] : $padrao['enabled'],
        'fee' => $taxa >= 0 ? round($taxa, 2) : 0
      ];
    }

    */
    $configuracao['rules'] = $gateway === 'Pagar.me' ? $regras : [];
    if ($gateway !== 'Pagar.me') {
      $publicKey = '';
      $secretKey = '';
      $configuracao['account_id'] = '';
    }
    unset($configuracao['payment_methods']);

    // Chaves de gateway nÃ£o devem ser gravadas no detalhamento do log.
    $this->modoLog = 'simples';

    return $this->update(
      [
        'prj_gateway' => $gateway,
        'prj_gateway_pk' => $publicKey,
        'prj_gateway_sk' => $secretKey,
        'prj_gateway_configuracao' => json_encode($configuracao, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
      ],
      ['prj_id' => 1],
      'Gateway atualizado com sucesso||Erro ao atualizar gateway'
    );
  }

  public function salvarEmailConfiguracao(array $data): object
  {
    $data = $this->filtrarCampos($data);
    $prjId = (int) ($data['prj_id'] ?? 0);

    if ($prjId !== 1) {
      return (new SimpleResponse('Erro', 'Erro ao identificar o projeto'))
        ->erro()
        ->noDestroy();
    }

    $header = trim((string) ($data['prj_email_header'] ?? ''));
    $footer = trim((string) ($data['prj_email_footer'] ?? ''));

    return $this->update(
      [
        'prj_email_header' => $header !== '' ? $header : null,
        'prj_email_footer' => $footer !== '' ? $footer : null
      ],
      ['prj_id' => $prjId],
      'Identidade dos e-mails atualizada com sucesso||Erro ao atualizar identidade dos e-mails'
    );
  }

  public function salvarImagens(array $data): object
  {
    $data = $this->filtrarCampos($data);
    $prjId = (int) ($data['prj_id'] ?? 0);

    if ($prjId !== 1) {
      return (new SimpleResponse('Erro', 'Erro ao identificar o projeto'))
        ->erro()
        ->noDestroy();
    }

    $getProjeto = $this->getProjeto();

    if (!$getProjeto->status || $getProjeto->total !== 1) {
      return (new SimpleResponse('Erro', 'Projeto nÃ£o encontrado'))
        ->erro()
        ->noDestroy();
    }

    $projetoAtual = $getProjeto->data[0];
    $camposImagem = [
      'prj_favicon' => 'favicon',
      'prj_logo' => 'logo',
      'prj_logonegativa' => 'logonegativa'
    ];
    $novasImagens = [];

    foreach ($camposImagem as $campo => $prefixo) {
      if (empty($data[$campo]) || !preg_match('/^data:[a-zA-Z0-9\.\+\-]+\/[a-zA-Z0-9\.\+\-]+;base64,/', $data[$campo])) {
        continue;
      }

      $upload = new SimpleUpload('projeto', $prjId);
      $salvarArquivo = $upload->salvarBase64($data[$campo], $prefixo, $prefixo);

      if (!$salvarArquivo->status) {
        return $salvarArquivo;
      }

      if (!empty($projetoAtual->{$campo}) && strpos($projetoAtual->{$campo}, 'storage/global') !== 0) {
        $upload->moverParaLixeira($projetoAtual->{$campo});
      }

      $novasImagens[$campo] = $salvarArquivo->arq_caminho;
    }

    if (empty($novasImagens)) {
      return (new SimpleResponse('AtenÃ§Ã£o', 'Nenhuma imagem foi selecionada.'))
        ->warning()
        ->noDestroy();
    }

    return $this->update(
      $novasImagens,
      ['prj_id' => $prjId],
      'Imagens do projeto atualizadas com sucesso||Erro ao atualizar imagens do projeto'
    );
  }

  public function insertProjeto(array $data): object
  {
    $data = $this->filtrarCampos($data);

    $validacao = $this->validarProjeto($data);

    if (!$validacao->status) {
      return $validacao;
    }

    if (empty($data['prj_id'])) {
      $data['prj_id'] = 1;
    }

    return $this->insert($data, "Projeto inserido com sucesso||Erro ao inserir projeto");
  }

  public function updateProjeto(array $data): object
  {
    $data = $this->filtrarCampos($data);

    if ((int) ($data['prj_id'] ?? 0) !== 1) {
      return (new SimpleResponse("Erro", "Erro ao identificar o projeto"))
        ->erro()
        ->setArrayParams(['campo' => 'prj_id'])
        ->noDestroy();
    }

    $prjId = (int) $data['prj_id'];
    unset($data['prj_id']);

    $getProjeto = $this->getProjeto();

    if (!$getProjeto->status || $getProjeto->total == 0) {
      return (new SimpleResponse("Erro", "Projeto não encontrado"))
        ->erro()
        ->setArrayParams(['prj_id' => $prjId])
        ->noDestroy();
    }

    $validacao = $this->validarProjeto($data, false);

    if (!$validacao->status) {
      return $validacao;
    }

    return $this->update($data, ['prj_id' => $prjId], "Projeto atualizado com sucesso||Erro ao atualizar projeto");
  }

  public function isCompartilhado(): bool
  {
    $getProjeto = $this->getProjeto();

    return $getProjeto->status
      && $getProjeto->total > 0
      && ($getProjeto->data[0]->prj_tipo ?? '') === 'Compartilhado';
  }

  public function isDedicado(): bool
  {
    $getProjeto = $this->getProjeto();

    return $getProjeto->status
      && $getProjeto->total > 0
      && ($getProjeto->data[0]->prj_tipo ?? '') === 'Dedicado';
  }

  private function validarProjeto(array $data, bool $validarObrigatorios = true): object
  {
    if ($validarObrigatorios) {
      $required = [
        'prj_nome',
        'prj_titulo',
        'prj_tipo'
      ];

      foreach ($required as $field) {
        if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
          return (new SimpleResponse("Erro", "O campo {$field} é obrigatório"))
            ->erro()
            ->setArrayParams(['campo' => $field])
            ->noDestroy();
        }
      }
    }

    if (isset($data['prj_tipo']) && !in_array($data['prj_tipo'], ['Compartilhado', 'Dedicado'], true)) {
      return (new SimpleResponse("Erro", "Tipo de projeto inválido"))
        ->erro()
        ->setArrayParams(['campo' => 'prj_tipo'])
        ->noDestroy();
    }

    return (new SimpleResponse("Validação", "Dados válidos"))
      ->sucesso()
      ->noDestroy();
  }
}
