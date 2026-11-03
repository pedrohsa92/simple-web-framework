<?php

namespace Source\Controller\Site;

use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Model\ContratacaoModel;
use Source\Model\PagarmeModel;
use Source\Model\PlanoModel;
use Source\Model\ProjetoModel;
use Source\Service\EmailService;

class ContrateController extends SimpleController
{
  public function __construct()
  {
    parent::__construct();
    $this->setFramework();
    $this->setAssetsCss(['global.css', 'site.css', 'phsa.css']);
    $this->setAssetsJs(['global.js', 'simplemodal.js?v=2026072602']);
  }

  public function index(?array $params = null, ?string $plaId = null): void
  {
    $id = (int) ($plaId ?: ($params['pla_id'] ?? 0) ?: ($_GET['pla_id'] ?? 0));
    $ciclo = strtolower(trim((string) ($_GET['p'] ?? 'mensal')));
    $cicloInicial = $ciclo === 'anual' ? 'Anual' : 'Mensal';
    $planosResponse = (new PlanoModel())->getPlanosPublicos();
    $planos = $planosResponse->status ? (array) ($planosResponse->data ?? []) : [];
    $projetoResponse = (new ProjetoModel())->getProjeto();
    $projetoAtual = $projetoResponse->status && !empty($projetoResponse->data[0])
      ? $projetoResponse->data[0]
      : $this->projeto;
    $planoSelecionado = null;

    foreach ($planos as $plano) {
      if ((int) ($plano->pla_id ?? 0) === $id) {
        $planoSelecionado = $plano;
        break;
      }
    }

    if (!$planoSelecionado) {
      $planoSelecionado = $planos[0] ?? null;
    }

    $resumo = null;
    $concluido = ($_GET['concluido'] ?? '') === '1';
    $contratacaoId = (int) ($_GET['plc_id'] ?? 0);
    $tokenConfirmacao = trim((string) ($_GET['token'] ?? ''));

    if ($concluido && $contratacaoId > 0 && $tokenConfirmacao !== '' && hash_equals($this->tokenConfirmacao($contratacaoId), $tokenConfirmacao)) {
      $resumoResponse = (new ContratacaoModel())->getResumoPublico($contratacaoId);
      $resumo = $resumoResponse->status && !empty($resumoResponse->data[0]) ? $resumoResponse->data[0] : null;
    }

    if ($concluido && is_null($resumo)) {
      $concluido = false;
    }

    $this->setMetaTags([
      'title' => 'Comece no icliv',
      'description' => 'Cadastre sua empresa e comece a organizar o cuidado com os animais no icliv.'
    ]);

    $this->render('site/contrate/contrate', [
      'planos' => $planos,
      'planoSelecionado' => $planoSelecionado,
      'iniciarEtapaOrganizacao' => $id > 0 && $planoSelecionado && in_array($planoSelecionado->pla_tipo ?? '', ['Teste', 'Comercial'], true),
      'cicloInicial' => $cicloInicial,
      'planoNaoDisponivel' => $planoSelecionado && !in_array($planoSelecionado->pla_tipo ?? '', ['Teste', 'Comercial'], true),
      'termosUso' => (string) ($projetoAtual->prj_termosuso ?? ''),
      'termosUsoVersao' => (string) ($projetoAtual->prj_termosusoversao ?? ''),
      'avisoPrivacidade' => (string) ($projetoAtual->prj_avisoprivacidade ?? ''),
      'avisoPrivacidadeVersao' => (string) ($projetoAtual->prj_avisoprivacidadeversao ?? ''),
      'resumo' => $resumo,
      'concluido' => $concluido,
      'tokenConfirmacao' => $tokenConfirmacao
    ]);
  }

  public function salvar(): void
  {
    $plaId = (int) ($_POST['pla_id'] ?? 0);
    $planoResponse = (new PlanoModel())->getPlanoById($plaId);

    if (!$planoResponse->status || $planoResponse->total !== 1 || !in_array($planoResponse->data[0]->pla_tipo ?? '', ['Teste', 'Comercial'], true) || ($planoResponse->data[0]->pla_status ?? '') !== 'Ativo') {
      (new SimpleResponse('Plano indisponível', 'Escolha um plano ativo para continuar.'))->warning()->json();
      return;
    }

    $plano = $planoResponse->data[0];
    $projetoResponse = (new ProjetoModel())->getProjeto();
    $projetoAtual = $projetoResponse->status && !empty($projetoResponse->data[0])
      ? $projetoResponse->data[0]
      : $this->projeto;

    if (!$projetoAtual) {
      (new SimpleResponse('Projeto indisponível', 'Não foi possível carregar as configurações do icliv.'))->erro()->json();
      return;
    }

    $resultado = (new ContratacaoModel())->contratar($_POST, $plano, $projetoAtual);

    if (!$resultado->status) {
      $resultado->json();
      return;
    }

    $dados = $resultado->data;

    if ($plano->pla_tipo === 'Comercial') {
      $pagamento = (new PagarmeModel($projetoAtual))->criarLinkPagamento([
        'plc_id' => $dados->plc_id,
        'plano_nome' => $plano->pla_nome,
        'valor' => $dados->plc_valor
      ]);

      if (!empty($pagamento->data)) {
        (new ContratacaoModel())->atualizarPagamento((int) $dados->plc_id, $pagamento->data);
        $dados->plc_pagarme_url = $pagamento->data->url ?? null;
        $dados->plc_pagarme_status = $pagamento->data->status ?? null;
      }
    }

    // O contrato já foi confirmado e o pagamento, quando aplicável, já foi registrado.
    // O envio não participa da transação principal: se o SMTP falhar, a contratação continua
    // válida e a tentativa fica disponível em lev_email_enviado para acompanhamento/reenvio.
    $resumoEmailResponse = (new ContratacaoModel())->getResumoPublico((int) $dados->plc_id);
    $resumoEmail = $resumoEmailResponse->status && !empty($resumoEmailResponse->data[0])
      ? $resumoEmailResponse->data[0]
      : null;
    $projetoUrl = rtrim((string) ($projetoAtual->prj_url_central ?? ''), '/');
    if ($projetoUrl === '') {
      $projetoUrl = rtrim(\Helpers::url(''), '/');
    }
    $emailDestino = trim((string) ($resumoEmail->usu_email ?? $dados->usu_email ?? $_POST['usu_email'] ?? ''));
    $emailNome = trim((string) ($resumoEmail->usu_razaosocial ?? $_POST['usu_razaosocial'] ?? ''));
    $emailVariaveis = [
      'projeto.nome' => $projetoAtual->prj_nome ?? 'icliv',
      'projeto.titulo' => $projetoAtual->prj_titulo ?? 'icliv',
      'projeto.descricao' => $projetoAtual->prj_descricao ?? '',
      'projeto.dominio' => $projetoAtual->prj_dominio ?? '',
      'projeto.url' => $projetoUrl,
      'projeto.status' => $projetoAtual->prj_status ?? 'Ativo',
      'organizacao.documento' => $resumoEmail->org_cnpjcpf ?? $_POST['org_cnpjcpf'] ?? '',
      'organizacao.nome' => $resumoEmail->org_razaosocial ?? $_POST['org_razaosocial'] ?? '',
      'organizacao.nomefantasia' => $resumoEmail->org_nomefantasia ?? $_POST['org_nomefantasia'] ?? '',
      'organizacao.descricao' => $resumoEmail->org_descricao ?? '',
      'organizacao.status' => $resumoEmail->org_status ?? 'Ativo',
      'organizacao.ambiente' => $resumoEmail->org_ambiente ?? '',
      'organizacao.logo' => $resumoEmail->org_logo ?? '',
      'empresa.documento' => $resumoEmail->emp_cnpjcpf ?? $_POST['org_cnpjcpf'] ?? '',
      'empresa.nome' => $resumoEmail->emp_razaosocial ?? $_POST['org_razaosocial'] ?? '',
      'empresa.nomefantasia' => $resumoEmail->emp_nomefantasia ?? $_POST['org_nomefantasia'] ?? '',
      'empresa.descricao' => $resumoEmail->emp_descricao ?? '',
      'empresa.status' => $resumoEmail->emp_status ?? 'Ativo',
      'empresa.tipo' => $resumoEmail->emp_tipo ?? 'Matriz',
      'empresa.logo' => $resumoEmail->emp_logo ?? '',
      'usuario.documento' => $resumoEmail->usu_cnpjcpf ?? $_POST['usu_cnpjcpf'] ?? '',
      'usuario.nome' => $emailNome,
      'usuario.nomefantasia' => $resumoEmail->usu_nomefantasia ?? $_POST['usu_nomefantasia'] ?? '',
      'usuario.email' => $emailDestino,
      'usuario.celular' => $resumoEmail->usu_celular ?? $_POST['usu_celular'] ?? '',
      'usuario.username' => $resumoEmail->usu_username ?? '',
      'usuario.status' => $resumoEmail->usu_status ?? 'Ativo'
    ];
    $emailEnvio = (new EmailService())->enviarTemplateProjeto('nova_contratacao', $emailDestino, $emailNome, $emailVariaveis);
    $dados->email = (object) [
      'status' => (bool) ($emailEnvio->status ?? false),
      'eme_id' => (int) ($emailEnvio->data->eme_id ?? 0),
      'text' => (string) ($emailEnvio->text ?? '')
    ];

    // Os dois fluxos terminam na confirmação para exibir o cadastro completo
    // e entregar o próximo passo adequado ao plano.
    $location = \Helpers::url('contrate?concluido=1&plc_id=' . (int) $dados->plc_id . '&token=' . rawurlencode($this->tokenConfirmacao((int) $dados->plc_id)));

    $resultado->setArrayParams(['location' => $location]);
    $resultado->json();
  }

  public function validarCnpj(): void
  {
    (new ContratacaoModel())->validarCnpjDisponibilidade((string) ($_POST['org_cnpjcpf'] ?? ''))->json();
  }

  public function validarCpf(): void
  {
    (new ContratacaoModel())->validarCpfDisponibilidade((string) ($_POST['usu_cnpjcpf'] ?? ''))->json();
  }

  public function validarEmail(): void
  {
    (new ContratacaoModel())->validarEmailDisponibilidade((string) ($_POST['usu_email'] ?? ''))->json();
  }

  public function validarCelular(): void
  {
    (new ContratacaoModel())->validarCelularDisponibilidade((string) ($_POST['usu_celular'] ?? ''))->json();
  }

  public function tentarPagamento(): void
  {
    $contratacaoId = (int) ($_POST['plc_id'] ?? 0);
    $token = trim((string) ($_POST['token'] ?? ''));

    if ($contratacaoId <= 0 || $token === '' || !hash_equals($this->tokenConfirmacao($contratacaoId), $token)) {
      (new SimpleResponse('Acesso inválido', 'Não foi possível identificar esta contratação.'))
        ->erro()
        ->json();
      return;
    }

    $resumoResponse = (new ContratacaoModel())->getResumoPublico($contratacaoId);
    $resumo = $resumoResponse->status && !empty($resumoResponse->data[0]) ? $resumoResponse->data[0] : null;

    if (!$resumo || ($resumo->pla_tipo ?? '') !== 'Comercial') {
      (new SimpleResponse('Pagamento indisponível', 'Esta contratação não está disponível para uma nova tentativa de pagamento.'))
        ->warning()
        ->json();
      return;
    }

    $urlPagamentoAtual = trim((string) ($resumo->plc_pagarme_url ?? ''));
    $statusPagamentoAtual = strtolower(trim((string) ($resumo->plc_pagarme_status ?? '')));
    $statusPagamentoNumerico = is_numeric($statusPagamentoAtual) ? (int) $statusPagamentoAtual : 0;
    $linkComErro = in_array($statusPagamentoAtual, ['expired', 'canceled', 'cancelled', 'failed', 'error', 'inactive'], true)
      || $statusPagamentoNumerico >= 400;

    if ($urlPagamentoAtual !== '' && !$linkComErro) {
      (new SimpleResponse('Link já disponível', 'O link de pagamento atual ainda está disponível. Use-o para concluir o pagamento.'))
        ->warning()
        ->json();
      return;
    }

    $pagamento = (new PagarmeModel($this->projeto))->criarLinkPagamento([
      'plc_id' => $contratacaoId,
      'plano_nome' => $resumo->pla_nome,
      'valor' => $resumo->plc_valor
    ]);

    if (!empty($pagamento->data)) {
      (new ContratacaoModel())->atualizarPagamento($contratacaoId, $pagamento->data);
    }

    if (!$pagamento->status || empty($pagamento->data->url)) {
      (new SimpleResponse('Pagamento pendente', 'Ainda não foi possível gerar o link. Tente novamente em instantes.'))
        ->warning()
        ->json();
      return;
    }

    (new SimpleResponse('Link atualizado', 'O link de pagamento foi gerado.'))
      ->sucesso()
      ->setArrayParams([
        'location' => \Helpers::url('contrate?concluido=1&plc_id=' . $contratacaoId . '&token=' . rawurlencode($token))
      ])
      ->json();
  }

  private function tokenConfirmacao(int $contratacaoId): string
  {
    $secret = defined('CONF_DB_KEY') && CONF_DB_KEY !== '' ? CONF_DB_KEY : (defined('SS_PREFIX') ? SS_PREFIX : 'icliv');

    return hash_hmac('sha256', 'contrate:' . $contratacaoId, $secret);
  }
}
