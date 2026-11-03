<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;
use Source\Core\SimpleUpload;
use Source\Model\PagarmeModel;
use Source\Service\PaletaCoresService;

class ContratacaoModel extends SimpleModel
{
  protected string $tabela = 'plano_contratado';
  protected string $prefixo = 'plc';
  protected string $identificador = 'id';
  protected string $escopoContexto = 'nenhum';
  protected bool $usarLog = false;

  public function contratar(array $data, object $plano, object $projeto): object
  {
    $organizacao = [
      'documento' => Helpers::somenteNumeros((string) ($data['org_cnpjcpf'] ?? '')),
      'razao' => trim((string) ($data['org_razaosocial'] ?? '')),
      'cor' => trim((string) ($data['org_cor_principal'] ?? '')),
      'logo' => trim((string) ($data['org_logo'] ?? ''))
    ];
    $usuario = [
      'documento' => Helpers::somenteNumeros((string) ($data['usu_cnpjcpf'] ?? '')),
      'nome' => trim((string) ($data['usu_razaosocial'] ?? '')),
      'email' => mb_strtolower(trim((string) ($data['usu_email'] ?? ''))),
      'celular' => Helpers::somenteNumeros((string) ($data['usu_celular'] ?? '')),
      'senha' => (string) ($data['usu_senha'] ?? '')
    ];
    $ciclo = (string) ($data['plc_ciclo'] ?? 'Mensal');
    $aceiteLegal = (int) ($data['plc_aceite'] ?? 0) === 1;
    $newsletter = (int) ($data['usu_newsletter'] ?? 0) === 1;
    $documentosLegais = [
      'termosuso' => [
        'conteudo' => trim((string) ($projeto->prj_termosuso ?? '')),
        'versao' => trim((string) ($projeto->prj_termosusoversao ?? ''))
      ],
      'avisoprivacidade' => [
        'conteudo' => trim((string) ($projeto->prj_avisoprivacidade ?? '')),
        'versao' => trim((string) ($projeto->prj_avisoprivacidadeversao ?? ''))
      ]
    ];

    $validacao = $this->validarDados(
      $organizacao,
      $usuario,
      $plano,
      $ciclo,
      $data['usu_senhaconfirmacao'] ?? null,
      $aceiteLegal,
      $documentosLegais
    );

    if (!$validacao->status) {
      return $validacao;
    }

    $agora = new \DateTimeImmutable('now');
    $dias = $plano->pla_tipo === 'Teste'
      ? max(1, (int) ($plano->pla_dias_teste ?? 30))
      : ($ciclo === 'Anual' ? 365 : 30);
    $fim = $agora->modify("+{$dias} days");
    $valor = $plano->pla_tipo === 'Teste'
      ? 0
      : (float) ($ciclo === 'Anual' ? $plano->pla_valoranual : $plano->pla_valormensal);
    $grupo = strtoupper(bin2hex(random_bytes(16)));
    $status = $plano->pla_tipo === 'Teste' ? 'Teste' : 'Pendente';
    $statusFinanceiro = $plano->pla_tipo === 'Teste' ? 'Isento' : 'Em aberto';
    $ambiente = $plano->pla_ambiente === 'Dedicado' ? 'Dedicado' : 'Compartilhado';
    // A página pública começa usando o CNPJ como endereço, como no LevHub.
    $slugWebapp = substr($organizacao['documento'], 0, 50) ?: null;
    $nomeOrganizacao = $organizacao['razao'];
    $paletaJson = json_encode(PaletaCoresService::gerar($organizacao['cor']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    try {
      $this->beginTransaction();

      // O responsavel da organizacao e obrigatorio, mas o usuario depende dos
      // identificadores da organizacao e da igreja. Criamos primeiro o usuario
      // em estado transacional e completamos seus vinculos depois.
      $user = $this->insertRaw('usuario', [
        'usu_chave' => 'LEV_USU_' . $grupo,
        'usu_idorganizacao' => null,
        'usu_idempresa' => null,
        'usu_idorganizacaoprincipal' => null,
        'usu_idempresaprincipal' => null,
        'usu_status' => 'Ativo',
        'usu_statusclass' => 'success',
        'usu_cnpjcpf' => $usuario['documento'],
        'usu_razaosocial' => $usuario['nome'],
        'usu_nomefantasia' => $usuario['nome'],
        'usu_niveisacesso' => '[]',
        'usu_email' => $usuario['email'],
        'usu_celular' => $usuario['celular'],
        'usu_senha' => password_hash($usuario['senha'], PASSWORD_DEFAULT),
        'usu_newsletter' => $newsletter ? 1 : 0,
        'usu_dtnewsletter' => date('Y-m-d H:i:s'),
        'usu_log' => '[]'
      ]);
      $this->assertInserted($user, 'Nao foi possivel criar o usuario principal.');
      $usuarioId = (int) $user->lastInsertId;

      $org = $this->insertRaw('organizacao', [
        'org_chave' => 'LEV_ORG_' . $grupo,
        'org_idusuarioresponsavel' => $usuarioId,
        'org_status' => 'Ativo',
        'org_statusclass' => 'success',
        'org_ambiente' => $ambiente,
        'org_cnpjcpf' => $organizacao['documento'],
        'org_razaosocial' => $organizacao['razao'],
        'org_nomefantasia' => $nomeOrganizacao,
        'org_paletacores' => $paletaJson,
        'org_urlamigavel' => $slugWebapp,
        'org_webapp_status' => 'Publica',
        'org_webapp_conteudo' => $this->conteudoWebapp('Organização', $nomeOrganizacao),
        'org_log' => '[]'
      ]);
      $this->assertInserted($org, 'Não foi possível criar a organização.');
      $orgId = (int) $org->lastInsertId;

      $empresa = $this->insertRaw('empresa', [
        'emp_chave' => 'LEV_EMP_' . $grupo,
        'emp_idorganizacao' => $orgId,
        'emp_tipo' => 'Matriz',
        'emp_status' => 'Ativo',
        'emp_statusclass' => 'success',
        'emp_cnpjcpf' => $organizacao['documento'],
        'emp_razaosocial' => $organizacao['razao'],
        'emp_nomefantasia' => $nomeOrganizacao,
        'emp_paletacores' => $paletaJson,
        'emp_urlamigavel' => $slugWebapp,
        'emp_webapp_status' => 'Publica',
        'emp_webapp_conteudo' => $this->conteudoWebapp('Empresa', $nomeOrganizacao),
        'emp_log' => '[]'
      ]);
      $this->assertInserted($empresa, 'Não foi possível criar a primeira igreja.');
      $empresaId = (int) $empresa->lastInsertId;

      $nivel = $this->insertRaw('nivelacesso', [
        'nva_idorganizacao' => $orgId,
        'nva_idempresa' => 0,
        'nva_escopo' => 'Organizacao',
        'nva_status' => 'Ativo',
        'nva_statusclass' => 'success',
        'nva_imutavel' => 0,
        'nva_padrao_inicial' => 1,
        'nva_nome' => 'Administrador da organização',
        'nva_descricao' => 'Administrador criado no cadastro inicial da organização.',
        'nva_log' => '[]'
      ]);
      $this->assertInserted($nivel, 'Não foi possível preparar o nível inicial de acesso.');
      $nivelId = (int) $nivel->lastInsertId;
      $this->copiarPermissoesAdministrativas($orgId, $nivelId);

      $this->assertUpdated($this->updateRaw('usuario', [
        'usu_idorganizacao' => $orgId,
        'usu_idempresa' => $empresaId,
        'usu_idorganizacaoprincipal' => $orgId,
        'usu_idempresaprincipal' => $empresaId,
        'usu_niveisacesso' => json_encode([['nva_id' => $nivelId, 'nva_nome' => 'Administrador da organização']], JSON_UNESCAPED_UNICODE)
      ], [
        'usu_id' => $usuarioId
      ]), 'Não foi possível completar o usuário principal.');

      $this->assertInserted($this->insertRaw('usuario_organizacao', [
        'uor_idusuario' => $usuarioId,
        'uor_idorganizacao' => $orgId,
        'uor_status' => 'Ativo',
        'uor_statusclass' => 'success',
        'uor_principal' => 1,
        'uor_log' => '[]'
      ]), 'Não foi possível vincular o usuário à organização.');
      $this->assertInserted($this->insertRaw('usuario_empresa', [
        'uem_idorganizacao' => $orgId,
        'uem_idusuario' => $usuarioId,
        'uem_idempresa' => $empresaId,
        'uem_status' => 'Ativo',
        'uem_statusclass' => 'success',
        'uem_principal' => 1,
        'uem_log' => '[]'
      ]), 'Não foi possível vincular o usuário à igreja.');
      $this->assertInserted($this->insertRaw('usuario_nivelacesso', [
        'una_idusuario' => $usuarioId,
        'una_idorganizacao' => $orgId,
        'una_idempresa' => null,
        'una_idnivelacesso' => $nivelId,
        'una_status' => 'Ativo',
        'una_statusclass' => 'success',
        'una_principal' => 1,
        'una_log' => '[]'
      ]), 'Não foi possível liberar o acesso administrativo inicial.');

      $dataAceite = date('Y-m-d H:i:s');
      $assinatura = [
        'aceito' => true,
        'data' => $dataAceite,
        'ip' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 255),
        'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        'usuario_id' => $usuarioId,
        'newsletter' => $newsletter,
        'documentos' => [
          'termosuso' => [
            'versao' => $documentosLegais['termosuso']['versao'],
            'hash' => hash('sha256', $documentosLegais['termosuso']['conteudo'])
          ],
          'avisoprivacidade' => [
            'versao' => $documentosLegais['avisoprivacidade']['versao'],
            'hash' => hash('sha256', $documentosLegais['avisoprivacidade']['conteudo'])
          ]
        ]
      ];

      $contratacao = $this->insertRaw('plano_contratado', [
        'plc_idorganizacao' => $orgId,
        'plc_idplano' => (int) $plano->pla_id,
        'plc_idusuariocontratante' => $usuarioId,
        'plc_grupo_contratacao' => $grupo,
        'plc_status' => $status,
        'plc_statusclass' => $plano->pla_tipo === 'Teste' ? 'success' : 'warning',
        'plc_statusfinanceiro' => $statusFinanceiro,
        'plc_origem' => 'Autocadastro',
        'plc_ciclo' => $plano->pla_tipo === 'Teste' ? 'Teste' : $ciclo,
        'plc_ambiente' => $ambiente,
        'plc_dtinicio' => $plano->pla_tipo === 'Teste' ? $agora->format('Y-m-d H:i:s') : null,
        'plc_dtfim' => $plano->pla_tipo === 'Teste' ? $fim->format('Y-m-d H:i:s') : null,
        'plc_dtvencimento' => null,
        'plc_valor' => number_format($valor, 2, '.', ''),
        'plc_observacao' => $plano->pla_tipo === 'Teste' ? 'Cadastro inicial com período de teste.' : 'Contratação inicial aguardando pagamento.',
        'plc_parametro' => $plano->pla_parametro ?? null,
        'plc_aceite' => 1,
        'plc_termosuso' => $documentosLegais['termosuso']['conteudo'],
        'plc_termosusoversao' => $documentosLegais['termosuso']['versao'],
        'plc_avisoprivacidade' => $documentosLegais['avisoprivacidade']['conteudo'],
        'plc_avisoprivacidadeversao' => $documentosLegais['avisoprivacidade']['versao'],
        'plc_assinatura' => json_encode($assinatura, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'plc_log' => '[]'
      ]);
      $this->assertInserted($contratacao, 'Não foi possível registrar a contratação.');
      $contratacaoId = (int) $contratacao->lastInsertId;

      if ($organizacao['logo'] !== '') {
        $upload = (new SimpleUpload('organizacao', $orgId, null, $orgId))->salvarBase64($organizacao['logo'], 'logo', 'logo');

        if ($upload->status && !empty($upload->arq_caminho)) {
          $this->assertUpdated($this->updateRaw('organizacao', ['org_logo' => $upload->arq_caminho], ['org_id' => $orgId]), 'Não foi possível salvar o logo da organização.');
          $this->assertUpdated($this->updateRaw('empresa', ['emp_logo' => $upload->arq_caminho], ['emp_id' => $empresaId, 'emp_idorganizacao' => $orgId]), 'Não foi possível replicar o logo para a primeira igreja.');
        }
      }

      $this->commit();

      return (new SimpleResponse('Cadastro realizado', 'A organização foi criada com sucesso.'))
        ->sucesso()
        ->setArrayParams([
          'data' => (object) [
            'org_id' => $orgId,
            'emp_id' => $empresaId,
            'usu_id' => $usuarioId,
            'plc_id' => $contratacaoId,
            'plc_status' => $status,
            'plc_statusfinanceiro' => $statusFinanceiro,
            'plc_ciclo' => $plano->pla_tipo === 'Teste' ? 'Teste' : $ciclo,
            'plc_valor' => $valor,
            'plc_dtfim' => $plano->pla_tipo === 'Teste' ? $fim->format('Y-m-d H:i:s') : null,
            'usu_email' => $usuario['email'],
            'plano_nome' => $plano->pla_nome
          ]
        ])
        ->noDestroy();
    } catch (\Throwable $e) {
      if ($this->pdo->inTransaction()) {
        $this->rollback();
      }

      return (new SimpleResponse('Não foi possível concluir', 'Revise os dados informados e tente novamente.'))
        ->warning()
        ->noDestroy();
    }
  }

  public function validarCnpjDisponibilidade(string $documento): object
  {
    $documento = Helpers::somenteNumeros($documento);
    if (!Helpers::validarCnpj($documento, false)) {
      return $this->respostaDisponibilidade(false, 'invalido', 'CNPJ inválido', 'Informe um CNPJ válido.');
    }
    return $this->validarDisponibilidade('CNPJ', $documento, 'SELECT org_id FROM ' . DB_PREFIX . '_organizacao WHERE org_cnpjcpf = :value LIMIT 1');
  }

  public function validarCpfDisponibilidade(string $documento): object
  {
    $documento = Helpers::somenteNumeros($documento);
    if (!Helpers::validarCpf($documento, false)) {
      return $this->respostaDisponibilidade(false, 'invalido', 'CPF inválido', 'Informe um CPF válido.');
    }
    return $this->validarDisponibilidade('CPF', $documento, 'SELECT usu_id FROM ' . DB_PREFIX . '_usuario WHERE usu_cnpjcpf = :value LIMIT 1');
  }

  public function validarEmailDisponibilidade(string $email): object
  {
    $email = mb_strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return $this->respostaDisponibilidade(false, 'invalido', 'E-mail inválido', 'Informe um e-mail válido.');
    }
    return $this->validarDisponibilidade('E-mail', $email, 'SELECT usu_id FROM ' . DB_PREFIX . '_usuario WHERE LOWER(usu_email) = LOWER(:value) LIMIT 1');
  }

  public function validarCelularDisponibilidade(string $celular): object
  {
    $celular = Helpers::somenteNumeros($celular);
    if (!in_array(strlen($celular), [10, 11], true)) {
      return $this->respostaDisponibilidade(false, 'invalido', 'Celular inválido', 'Informe um celular válido.');
    }
    return $this->validarDisponibilidade('Celular', $celular, 'SELECT usu_id FROM ' . DB_PREFIX . '_usuario WHERE usu_celular = :value LIMIT 1');
  }

  private function validarDisponibilidade(string $campo, string $valor, string $sql): object
  {
    $check = $this->rawQuery($sql, ['value' => $valor], true);
    if (!$check->status) {
      return $this->respostaDisponibilidade(false, 'indisponivel', 'Não foi possível validar', 'Não foi possível verificar ' . $campo . ' agora. Tente novamente.');
    }
    if ((int) ($check->total ?? 0) > 0) {
      return $this->respostaDisponibilidade(false, 'duplicado', $campo . ' já cadastrado', 'Este ' . $campo . ' já está cadastrado.');
    }
    return $this->respostaDisponibilidade(true, 'disponivel', $campo . ' válido', $campo . ' disponível para cadastro.', $valor);
  }

  private function respostaDisponibilidade(bool $valido, string $motivo, string $titulo, string $texto, string $valor = ''): object
  {
    $resposta = (new SimpleResponse($titulo, $texto))
      ->setArrayParams(['data' => (object) ['valido' => $valido, 'motivo' => $motivo, 'valor' => $valor]]);
    return ($valido ? $resposta->sucesso() : $resposta->warning())->noDestroy();
  }

  public function getResumoPublico(int $contratacaoId): object
  {
    return $this->rawQuery(
      'SELECT
        plc.plc_id,
        plc.plc_idusuariocontratante,
        plc.plc_status,
        plc.plc_statusfinanceiro,
        plc.plc_ciclo,
        plc.plc_valor,
        plc.plc_dtinicio,
        plc.plc_dtfim,
        plc.plc_dtvencimento,
        plc.plc_dtpagamento,
        plc.plc_pagarme_id,
        plc.plc_pagarme_url,
        plc.plc_pagarme_status,
        plc.plc_pagarme_response,
        pla.pla_id,
        pla.pla_nome,
        pla.pla_tipo,
        pla.pla_descricaoresumida,
        org.org_id,
        org.org_cnpjcpf,
        org.org_razaosocial,
        org.org_nomefantasia,
        org.org_logo,
        org.org_descricao,
        org.org_status,
        org.org_ambiente,
        emp.emp_id,
        emp.emp_cnpjcpf,
        emp.emp_razaosocial,
        emp.emp_nomefantasia,
        emp.emp_descricao,
        emp.emp_status,
        emp.emp_tipo,
        emp.emp_logo,
        emp.emp_urlamigavel,
        emp.emp_webapp_status,
        usu.usu_id,
        usu.usu_cnpjcpf,
        usu.usu_razaosocial,
        usu.usu_nomefantasia,
        usu.usu_email,
        usu.usu_celular,
        usu.usu_username,
        usu.usu_status
      FROM ' . DB_PREFIX . '_plano_contratado plc
      INNER JOIN ' . DB_PREFIX . '_plano pla ON pla.pla_id = plc.plc_idplano
      INNER JOIN ' . DB_PREFIX . '_organizacao org ON org.org_id = plc.plc_idorganizacao
      LEFT JOIN ' . DB_PREFIX . '_empresa emp
        ON emp.emp_id = (
          SELECT emp_principal.emp_id
          FROM ' . DB_PREFIX . '_empresa emp_principal
          WHERE emp_principal.emp_idorganizacao = org.org_id
          ORDER BY emp_principal.emp_id ASC
          LIMIT 1
        )
      LEFT JOIN ' . DB_PREFIX . '_usuario usu
        ON usu.usu_id = plc.plc_idusuariocontratante
      WHERE plc.plc_id = :id
      LIMIT 1',
      ['id' => $contratacaoId],
      true,
      'Resumo encontrado com sucesso||Não foi possível buscar o resumo da contratação'
    );
  }

  public function renovar(int $orgId, int $usuarioId, object $plano, string $ciclo): object
  {
    if ($orgId <= 0 || $usuarioId <= 0) {
      return $this->warning('Não foi possível identificar a organização e o usuário responsável.');
    }

    if (($plano->pla_status ?? '') !== 'Ativo' || ($plano->pla_tipo ?? '') !== 'Comercial') {
      return $this->warning('Escolha um plano comercial ativo para renovar a contratação.');
    }

    if (!in_array($ciclo, ['Mensal', 'Anual'], true)) {
      return $this->warning('Escolha uma periodicidade válida para a renovação.');
    }

    $pendente = $this->rawQuery(
      "SELECT plc_id FROM {$this->getTable()}
       WHERE plc_idorganizacao = :org_id
         AND plc_origem = 'Renovacao'
         AND (
           (plc_status = 'Pendente' AND plc_statusfinanceiro IN ('Em aberto', 'Falhou'))
           OR (plc_status = 'Agendado' AND plc_statusfinanceiro IN ('Pago', 'Pago Manual') AND plc_dtinicio > NOW())
         )
       ORDER BY plc_id DESC LIMIT 1",
      ['org_id' => $orgId],
      true
    );

    if ($pendente->status && !empty($pendente->data[0]->plc_id)) {
      return (new SimpleResponse('Renovação pendente', 'Já existe uma renovação aguardando pagamento para esta organização.'))
        ->warning()
        ->setArrayParams(['data' => (object) ['plc_id' => (int) $pendente->data[0]->plc_id]])
        ->noDestroy();
    }

    $anterior = $this->rawQuery(
      "SELECT plc_id FROM {$this->getTable()}
       WHERE plc_idorganizacao = :org_id
       ORDER BY COALESCE(plc_dtfim, plc_dtcad) DESC, plc_id DESC LIMIT 1",
      ['org_id' => $orgId],
      true
    );
    $anteriorId = !empty($anterior->data[0]->plc_id) ? (int) $anterior->data[0]->plc_id : null;
    $valor = (float) ($ciclo === 'Anual' ? ($plano->pla_valoranual ?? 0) : ($plano->pla_valormensal ?? 0));

    if ($valor <= 0) {
      return $this->warning('O plano escolhido não possui valor configurado para esta periodicidade.');
    }

    $grupo = strtoupper(bin2hex(random_bytes(16)));
    $resultado = $this->insertRaw('plano_contratado', [
      'plc_idorganizacao' => $orgId,
      'plc_idplano' => (int) $plano->pla_id,
      'plc_idusuariocontratante' => $usuarioId,
      'plc_idcontratacaoanterior' => $anteriorId,
      'plc_grupo_contratacao' => $grupo,
      'plc_status' => 'Pendente',
      'plc_statusclass' => 'warning',
      'plc_statusfinanceiro' => 'Em aberto',
      'plc_origem' => 'Renovacao',
      'plc_ciclo' => $ciclo,
      'plc_ambiente' => ($plano->pla_ambiente ?? '') === 'Dedicado' ? 'Dedicado' : 'Compartilhado',
      'plc_valor' => number_format($valor, 2, '.', ''),
      'plc_observacao' => 'Renovação aguardando pagamento.',
      'plc_parametro' => $plano->pla_parametro ?? null,
      'plc_log' => json_encode([
        'tipo' => 'renovacao',
        'plc_idcontratacaoanterior' => $anteriorId,
        'criado_em' => date('Y-m-d H:i:s')
      ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    ]);

    if (!$resultado->status || (int) ($resultado->lastInsertId ?? 0) <= 0) {
      return $this->warning('Não foi possível registrar a renovação.');
    }

    return (new SimpleResponse('Renovação registrada', 'A renovação foi registrada e aguarda o pagamento.'))
      ->sucesso()
      ->setArrayParams([
        'data' => (object) [
          'plc_id' => (int) $resultado->lastInsertId,
          'plc_valor' => $valor,
          'plc_ciclo' => $ciclo,
          'plc_idcontratacaoanterior' => $anteriorId
        ]
      ])
      ->noDestroy();
  }

  public function atualizarPagamento(int $contratacaoId, object $pagamento): object
  {
    $gatewayStatus = strtolower(trim((string) ($pagamento->status ?? 'failed')));
    $httpCode = (int) ($pagamento->http_code ?? 0);
    $falhou = $gatewayStatus === 'failed' || $httpCode >= 400 || empty($pagamento->id) || empty($pagamento->url);

    $response = $this->updateRaw('plano_contratado', [
      'plc_statusfinanceiro' => $falhou ? 'Falhou' : 'Em aberto',
      'plc_pagarme_id' => $pagamento->id ?? null,
      'plc_pagarme_tipo' => 'Link',
      'plc_pagarme_url' => $pagamento->url ?? null,
      'plc_pagarme_status' => $pagamento->status ?? 'failed',
      'plc_pagarme_dtultimasync' => date('Y-m-d H:i:s'),
      'plc_pagarme_ultimoevento' => $pagamento->status ?? 'failed',
      'plc_pagarme_request' => json_encode($pagamento->request ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      'plc_pagarme_response' => json_encode($pagamento->response ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    ], ['plc_id' => $contratacaoId]);

    return $response;
  }

  public function processarWebhookPagarme(array $evento): object
  {
    $tipoEvento = strtolower(trim((string) ($evento['type'] ?? $evento['event'] ?? '')));
    $dados = is_array($evento['data'] ?? null) ? $evento['data'] : [];
    $codigo = trim((string) ($dados['code'] ?? ''));

    if (!preg_match('/^LEVHUB-(\d+)$/i', $codigo, $matches)) {
      return (object) [
        'status' => false,
        'http_code' => 422,
        'text' => 'O código do pedido não identifica uma contratação LevHub.'
      ];
    }

    $contratacaoId = (int) $matches[1];
    $consulta = $this->rawQuery(
      'SELECT plc_id, plc_status, plc_statusfinanceiro, plc_ciclo, plc_valor, plc_log,
              plc_idcontratacaoanterior
       FROM ' . DB_PREFIX . '_plano_contratado
       WHERE plc_id = :id
       LIMIT 1',
      ['id' => $contratacaoId],
      true
    );

    if (!$consulta->status || empty($consulta->data[0])) {
      return (object) [
        'status' => false,
        'http_code' => 404,
        'text' => 'Contratação não encontrada para o pedido informado.'
      ];
    }

    $contratacao = $consulta->data[0];
    $valorEsperado = (int) round(((float) ($contratacao->plc_valor ?? 0)) * 100);
    $valorRecebido = (int) ($dados['amount'] ?? 0);

    if ($valorEsperado <= 0 || $valorRecebido !== $valorEsperado) {
      return (object) [
        'status' => false,
        'http_code' => 422,
        'text' => 'O valor do pedido não corresponde ao valor da contratação.'
      ];
    }

    if (!in_array($tipoEvento, ['order.paid', 'order.payment_failed'], true)) {
      return (object) [
        'status' => true,
        'plc_id' => $contratacaoId,
        'repetido' => false
      ];
    }

    $agora = new \DateTimeImmutable('now');
    $dataEvento = $this->dataWebhook($dados['paid_at'] ?? $dados['updated_at'] ?? $evento['created_at'] ?? null, $agora);
    $foiPago = $tipoEvento === 'order.paid';
    $jaPago = ($contratacao->plc_statusfinanceiro ?? '') === 'Pago';
    $statusGateway = strtolower(trim((string) ($dados['status'] ?? ($foiPago ? 'paid' : 'failed'))));

    $log = json_decode((string) ($contratacao->plc_log ?? '[]'), true);
    $log = is_array($log) ? $log : [];
    $log[] = [
      'tipo' => 'pagarme_webhook',
      'id' => $evento['id'] ?? null,
      'evento' => $tipoEvento,
      'recebido_em' => $agora->format('Y-m-d H:i:s'),
      'payload' => $evento
    ];
    $log = array_slice($log, -50);

    $dadosAtualizacao = [
      'plc_pagarme_status' => $statusGateway,
      'plc_pagarme_dtultimasync' => $agora->format('Y-m-d H:i:s'),
      'plc_pagarme_ultimoevento' => $tipoEvento,
      'plc_log' => json_encode($log, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    ];

    if ($foiPago && !$jaPago) {
      $dias = ($contratacao->plc_ciclo ?? '') === 'Anual' ? 365 : 30;
      $inicio = $dataEvento;

      if (!empty($contratacao->plc_idcontratacaoanterior)) {
        $anterior = $this->rawQuery(
          "SELECT plc_dtfim FROM {$this->getTable()} WHERE plc_id = :id LIMIT 1",
          ['id' => (int) $contratacao->plc_idcontratacaoanterior],
          true
        );
        $fimAnterior = $anterior->data[0]->plc_dtfim ?? null;

        if (!empty($fimAnterior)) {
          try {
            $dataFimAnterior = new \DateTimeImmutable((string) $fimAnterior);
            if ($dataFimAnterior > $inicio) {
              $inicio = $dataFimAnterior;
            }
          } catch (\Throwable $e) {
            // Se a data anterior estiver inválida, usa a data da confirmação.
          }
        }
      }

      $fim = $inicio->modify("+{$dias} days");
      $statusOperacional = $inicio > $dataEvento ? 'Agendado' : 'Ativo';
      $dadosAtualizacao += [
        'plc_status' => $statusOperacional,
        'plc_statusclass' => $statusOperacional === 'Agendado' ? 'info' : 'success',
        'plc_statusfinanceiro' => 'Pago',
        'plc_dtinicio' => $inicio->format('Y-m-d H:i:s'),
        'plc_dtfim' => $fim->format('Y-m-d H:i:s'),
        'plc_dtvencimento' => $fim->format('Y-m-d H:i:s'),
        'plc_dtpagamento' => $dataEvento->format('Y-m-d H:i:s')
      ];
    } elseif (!$foiPago && !$jaPago) {
      $dadosAtualizacao += [
        'plc_status' => 'Pendente',
        'plc_statusclass' => 'warning',
        'plc_statusfinanceiro' => 'Falhou'
      ];
    }

    $atualizacao = $this->updateRaw('plano_contratado', $dadosAtualizacao, ['plc_id' => $contratacaoId]);

    if (!$atualizacao->status) {
      return (object) [
        'status' => false,
        'http_code' => 500,
        'text' => 'Não foi possível atualizar a contratação.'
      ];
    }

    return (object) [
      'status' => true,
      'plc_id' => $contratacaoId,
      'repetido' => $foiPago && $jaPago,
      'notificarPagamento' => $foiPago && !$jaPago
    ];
  }

  private function dataWebhook($data, \DateTimeImmutable $padrao): \DateTimeImmutable
  {
    if (empty($data)) {
      return $padrao;
    }

    try {
      return (new \DateTimeImmutable((string) $data))
        ->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
    } catch (\Throwable $e) {
      return $padrao;
    }
  }

  private function validarDados(array $organizacao, array $usuario, object $plano, string $ciclo, $confirmacao, bool $aceiteLegal, array $documentosLegais): object
  {
    if (!in_array($plano->pla_tipo ?? '', ['Teste', 'Comercial'], true)) {
      return $this->warning('Este plano não está disponível para contratação online.');
    }

    if (!Helpers::validarCnpj($organizacao['documento'], false)) {
      return $this->warning('Informe um CNPJ válido para a organização.');
    }

    if ($organizacao['razao'] === '') {
      return $this->warning('Informe a razão social da organização.');
    }

    if (!preg_match('/^#[a-f0-9]{6}$/i', $organizacao['cor'])) {
      return $this->warning('Escolha uma cor principal válida para a organização.');
    }

    if (!preg_match('/^data:image\/(?:png|jpeg|webp);base64,([a-z0-9+\/=\s]+)$/i', $organizacao['logo'], $logoPartes)) {
      return $this->warning('Anexe uma logo vÃ¡lida para a organizaÃ§Ã£o.');
    }
    $logoBinaria = base64_decode($logoPartes[1], true);
    if ($logoBinaria === false || strlen($logoBinaria) > 2 * 1024 * 1024) {
      return $this->warning('A logo deve ter no máximo 2 MB.');
    }

    if (!Helpers::validarCpf($usuario['documento'], false)) {
      return $this->warning('Informe um CPF válido para o usuário principal.');
    }

    if ($usuario['nome'] === '') {
      return $this->warning('Informe o nome completo do usuário principal.');
    }

    if (!filter_var($usuario['email'], FILTER_VALIDATE_EMAIL)) {
      return $this->warning('Informe um e-mail válido para o usuário principal.');
    }

    if (strlen($usuario['celular']) < 10) {
      return $this->warning('Informe um celular válido para o usuário principal.');
    }

    if (strlen($usuario['senha']) < 8 || !preg_match('/[a-z]/', $usuario['senha']) || !preg_match('/[A-Z]/', $usuario['senha']) || !preg_match('/\d/', $usuario['senha']) || !preg_match('/[^a-zA-Z\d]/', $usuario['senha'])) {
      return $this->warning('A senha deve ter pelo menos 8 caracteres, com minúscula, maiúscula, número e caractere especial.');
    }

    if ($usuario['senha'] !== (string) $confirmacao) {
      return $this->warning('A confirmação da senha não confere.');
    }

    if ($plano->pla_tipo === 'Comercial' && !in_array($ciclo, ['Mensal', 'Anual'], true)) {
      return $this->warning('Escolha uma forma de contratação válida.');
    }

    if (!$aceiteLegal) {
      return $this->warning('Leia e aceite os Termos de uso e o Aviso de privacidade para continuar.');
    }

    foreach ($documentosLegais as $documento) {
      if (($documento['conteudo'] ?? '') === '' || ($documento['versao'] ?? '') === '') {
        return $this->warning('Os documentos legais ainda não estão configurados.');
      }
    }

    $checks = [
      ['SELECT org_id FROM ' . DB_PREFIX . '_organizacao WHERE org_cnpjcpf = :value LIMIT 1', $organizacao['documento'], 'Já existe uma organização com este CNPJ.'],
      ['SELECT usu_id FROM ' . DB_PREFIX . '_usuario WHERE usu_cnpjcpf = :value LIMIT 1', $usuario['documento'], 'Já existe um usuário com este CPF.'],
      ['SELECT usu_id FROM ' . DB_PREFIX . '_usuario WHERE LOWER(usu_email) = LOWER(:value) LIMIT 1', $usuario['email'], 'Já existe um usuário com este e-mail.'],
      ['SELECT usu_id FROM ' . DB_PREFIX . '_usuario WHERE usu_celular = :value LIMIT 1', $usuario['celular'], 'Já existe um usuário com este celular.']
    ];

    foreach ($checks as [$sql, $value, $message]) {
      $check = $this->rawQuery($sql, ['value' => $value], true);

      if (!$check->status) {
        return $this->warning('Não foi possível validar os dados agora.');
      }

      if ((int) ($check->total ?? 0) > 0) {
        return $this->warning($message);
      }
    }

    return (new SimpleResponse('Validação', 'Dados válidos.'))->sucesso()->noDestroy();
  }

  private function copiarPermissoesAdministrativas(int $orgId, int $nivelId): void
  {
    $result = $this->rawQuery(
      'INSERT INTO ' . DB_PREFIX . '_permissao (per_idorganizacao, per_idempresa, per_idnivelacesso, per_status, per_statusclass, per_nome, per_descricao, per_nivel, per_log) SELECT :org, 0, :nivel, per_status, per_statusclass, per_nome, per_descricao, per_nivel, \'[]\' FROM ' . DB_PREFIX . '_permissao WHERE per_idnivelacesso = 1 AND per_nome NOT LIKE \'projeto.%\'',
      ['org' => $orgId, 'nivel' => $nivelId],
      false
    );
    $this->assertUpdated($result, 'Não foi possível preparar as permissões iniciais.');
  }

  private function conteudoWebapp(string $tipo, string $nome): string
  {
    $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $tipoSeguro = htmlspecialchars($tipo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    return '<section><h1>' . $nomeSeguro . '</h1><p>Conheça ' . $tipoSeguro . ' <strong>' . $nomeSeguro . '</strong>, seus serviços, conteúdos e novidades.</p><p>Estamos aqui para atender você e cuidar do que é importante para o seu negócio.</p></section>';
  }

  private function insertRaw(string $tabela, array $data): object
  {
    $table = DB_PREFIX . '_' . $tabela;
    $columns = array_keys($data);
    $params = [];

    foreach ($columns as $column) {
      $params[$column] = $data[$column];
    }

    return $this->rawQuery(
      'INSERT INTO ' . $table . ' (`' . implode('`, `', $columns) . '`) VALUES (:' . implode(', :', $columns) . ')',
      $params,
      false
    );
  }

  private function updateRaw(string $tabela, array $data, array $where): object
  {
    $table = DB_PREFIX . '_' . $tabela;
    $set = [];
    $params = [];

    foreach ($data as $column => $value) {
      $set[] = "`{$column}` = :set_{$column}";
      $params['set_' . $column] = $value;
    }

    foreach ($where as $column => $value) {
      $params['where_' . $column] = $value;
    }

    return $this->rawQuery(
      'UPDATE ' . $table . ' SET ' . implode(', ', $set) . ' WHERE ' . implode(' AND ', array_map(fn($column) => "`{$column}` = :where_{$column}", array_keys($where))),
      $params,
      false
    );
  }

  private function assertInserted(object $response, string $message): void
  {
    if (!$response->status || (int) ($response->lastInsertId ?? 0) <= 0) {
      throw new \RuntimeException($message);
    }
  }

  private function assertUpdated(object $response, string $message): void
  {
    if (!$response->status) {
      throw new \RuntimeException($message);
    }
  }

  private function warning(string $message): object
  {
    return (new SimpleResponse('Atenção', $message))->warning()->noDestroy();
  }
}
