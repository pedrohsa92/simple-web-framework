<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Model\ArquivoModel;
use Source\Model\CadastroBasicoModel;
use Source\Model\ContatoModel;
use Source\Model\ContratacaoModel;
use Source\Model\EnderecoModel;
use Source\Model\OrganizacaoModel;
use Source\Model\PagarmeModel;
use Source\Model\PlanoModel;
use Source\Model\PlanoContratadoModel;
use Source\Core\SimpleResponse;

class OrganizacaoController extends SimpleController
{
  use CadastroBasicoModuloTrait;

  protected function moduloCadastroBasico(): string { return 'organizacao'; }

  public function __construct()
  {
    parent::__construct();

    $this->setFramework();

    $this->setAssetsCss('phsa.css');
    $this->setAssetsCss('sistema.css');
    $this->setAssetsCss('global.css');

    $this->setAssetsJs('sistema.js');
    $this->setAssetsJs('simplemodal.js');
    $this->setAssetsJs('global.js');
  }

  public function regularizacao(): void
  {
    $orgId = (int) ($this->contexto->organizacao->org_id ?? 0);
    $modelo = new PlanoContratadoModel();
    $situacao = $modelo->getSituacaoAcessoPorOrganizacao($orgId);

    if (($situacao->modo ?? 'liberado') !== 'regularizacao') {
      header('Location: ' . Helpers::url($this->podeRegularizarPlano() ? 'organizacao/plano' : 'dashboard'));
      exit;
    }

    $contrato = $situacao->contrato ?? null;

    $this->setBreadcrumb([
      [
        'title' => 'Regularização',
        'label' => 'Regularização',
        'url' => Helpers::url('organizacao/regularizacao')
      ]
    ]);

    $this->render('sistema/organizacao/regularizacao', [
      'situacao' => $situacao,
      'contrato' => $contrato,
      'administrador' => $this->podeRegularizarPlano(),
      'organizacao' => $this->contexto->organizacao ?? null,
      'getRenovacao' => $modelo->getRenovacaoPendente($orgId),
      'getRenovacaoProgramada' => $modelo->getRenovacaoProgramada($orgId)
    ]);
  }

  public function organizacao($url = null): void
  {
    $this->validarPermissoes(['organizacao.visualizar']);

    $id = (int) ($this->contexto->organizacao->org_id ?? 0);

    $organizacaoModel = new OrganizacaoModel();
    $getOrganizacao = $organizacaoModel->getOrganizacaoById($id, true);

    if (!$getOrganizacao->status || $getOrganizacao->total != 1) {
      $this->render('template/sistema/erro', [
        'erro' => (object) [
          'status' => false,
          'title' => 'Organizacao nao encontrada',
          'text' => 'A organizacao ativa do contexto nao foi localizada.'
        ]
      ]);
      return;
    }

    $organizacao = $getOrganizacao->data[0];

    $this->setBreadcrumb([
      [
        'title' => 'Organizacao',
        'label' => Helpers::gerarAssinatura($organizacao->org_razaosocial),
        'url' => Helpers::url('organizacao')
      ]
    ]);

    $getEmpresas = $organizacao->empresas ?? $organizacaoModel->getEmpresasByOrganizacao((int) $organizacao->org_id);

    $this->render('sistema/organizacao/form', [
      'organizacao' => $organizacao,
      'getSexo' => (new CadastroBasicoModel('sexo'))->getCadastroBasicoAtivo('organizacao'),
      'getEstadoCivil' => (new CadastroBasicoModel('estadocivil'))->getCadastroBasicoAtivo('organizacao'),
      'getNaturezaJuridica' => (new CadastroBasicoModel('naturezajuridica'))->getCadastroBasicoAtivo('organizacao'),
      'getNaturalidade' => (new CadastroBasicoModel('naturalidade'))->getCadastroBasicoAtivo('organizacao'),
      'arquivoConfig' => $this->montarArquivoConfig($organizacao),
      'contatoConfig' => $this->montarContatoConfig($organizacao),
      'enderecoConfig' => $this->montarEnderecoConfig($organizacao),
      'getEmpresas' => $getEmpresas,
      'getPlanoVigente' => (new PlanoContratadoModel())->getPlanoVigentePorOrganizacao((int) $organizacao->org_id)
    ]);
  }

  public function salvarOrganizacao(): void
  {
    $this->validarPermissoes(['organizacao.atualizar']);

    echo json_encode((new OrganizacaoModel())->salvarOrganizacao($_POST));
    exit;
  }

  public function plano(?array $params = null, ?string $id = null): void
  {
    $this->validarPermissoes(['organizacao.plano.contratar']);

    $orgId = (int) ($this->contexto->organizacao->org_id ?? 0);
    $plcId = (int) ($id ?: ($params['id'] ?? 0));
    $modelo = new PlanoContratadoModel();
    $filtros = [
      'pesquisa' => $_GET['pesquisa'] ?? '',
      'status' => $_GET['status'] ?? '',
      'financeiro' => $_GET['financeiro'] ?? '',
      'data_inicio' => $_GET['data_inicio'] ?? '',
      'data_fim' => $_GET['data_fim'] ?? ''
    ];

    $contratacao = null;
    if ($plcId > 0) {
      $getContratacao = $modelo->getContratacaoPorOrganizacao($plcId, $orgId);

      if (!$getContratacao->status || empty($getContratacao->data[0])) {
        $this->render('template/sistema/erro', [
          'erro' => (object) [
            'status' => false,
            'title' => 'Contratação não encontrada',
            'text' => 'A contratação não pertence à organização ativa ou não foi localizada.'
          ]
        ]);
        return;
      }

      $contratacao = $getContratacao->data[0];
    }

    $getContratacoes = $modelo->getContratacoesPorOrganizacao($orgId, $filtros);
    $getPlanoVigente = $modelo->getPlanoVigentePorOrganizacao($orgId);
    $getRenovacao = $modelo->getRenovacaoPendente($orgId);
    $getRenovacaoProgramada = $modelo->getRenovacaoProgramada($orgId);
    $getPlanos = (new PlanoModel())->getPlanosPublicos();
    $planosRenovacao = array_values(array_filter(
      (array) ($getPlanos->data ?? []),
      static fn($plano) => in_array($plano->pla_tipo ?? '', ['Comercial', 'Dedicado'], true)
    ));

    $renovacao = $this->montarAvisoRenovacao($getPlanoVigente, $getRenovacao, $getRenovacaoProgramada, $getContratacoes);

    $this->setBreadcrumb([
      [
        'title' => 'Organização',
        'label' => Helpers::gerarAssinatura($this->contexto->organizacao->org_razaosocial ?? 'Organização'),
        'url' => Helpers::url('organizacao')
      ],
      [
        'title' => 'Plano',
        'label' => $contratacao ? 'Contratação #' . $plcId : 'Planos contratados',
        'url' => Helpers::url('organizacao/plano')
      ]
    ]);

    $this->render('sistema/organizacao/planos', [
      'contratacoes' => $getContratacoes,
      'contratacao' => $contratacao,
      'filtros' => $filtros,
      'getPlanoVigente' => $getPlanoVigente,
      'renovacao' => $renovacao,
      'planosRenovacao' => $planosRenovacao,
      'getRenovacao' => $getRenovacao,
      'getRenovacaoProgramada' => $getRenovacaoProgramada
    ]);
  }

  public function renovar(): void
  {
    $this->validarPermissoes(['organizacao.plano.contratar']);

    $orgId = (int) ($this->contexto->organizacao->org_id ?? 0);
    $modelo = new PlanoContratadoModel();
    $getPlanos = (new PlanoModel())->getPlanosPublicos();
    $planosRenovacao = array_values(array_filter(
      (array) ($getPlanos->data ?? []),
      static fn($plano) => in_array($plano->pla_tipo ?? '', ['Comercial', 'Dedicado'], true)
    ));
    $getContratacoes = $modelo->getContratacoesPorOrganizacao($orgId);
    $getPlanoVigente = $modelo->getPlanoVigentePorOrganizacao($orgId);
    $getRenovacao = $modelo->getRenovacaoPendente($orgId);
    $getRenovacaoProgramada = $modelo->getRenovacaoProgramada($orgId);

    $this->setBreadcrumb([
      [
        'title' => 'Organização',
        'label' => Helpers::gerarAssinatura($this->contexto->organizacao->org_razaosocial ?? 'Organização'),
        'url' => Helpers::url('organizacao')
      ],
      [
        'title' => 'Plano',
        'label' => 'Renovar plano',
        'url' => Helpers::url('organizacao/plano')
      ]
    ]);

    $this->render('sistema/organizacao/planos', [
      'modo' => 'renovar',
      'contratacoes' => $getContratacoes,
      'contratacao' => null,
      'filtros' => [],
      'getPlanoVigente' => $getPlanoVigente,
      'renovacao' => $this->montarAvisoRenovacao($getPlanoVigente, $getRenovacao, $getRenovacaoProgramada, $getContratacoes),
      'planosRenovacao' => $planosRenovacao,
      'getRenovacao' => $getRenovacao,
      'getRenovacaoProgramada' => $getRenovacaoProgramada
    ]);
  }

  public function salvarRenovacao(): void
  {
    $this->validarPermissoes(['organizacao.plano.contratar']);

    $orgId = (int) ($this->contexto->organizacao->org_id ?? 0);
    $usuarioId = (int) ($this->usuarioLogado->usu_id ?? 0);
    $planoId = (int) ($_POST['pla_id'] ?? 0);
    $ciclo = trim((string) ($_POST['plc_ciclo'] ?? 'Mensal'));
    $planoResponse = (new PlanoModel())->getPlanoById($planoId);

    if (!$planoResponse->status || empty($planoResponse->data[0])) {
      (new SimpleResponse('Plano não encontrado', 'Escolha um plano comercial disponível.'))->warning()->json();
      return;
    }

    $plano = $planoResponse->data[0];
    $modelo = new ContratacaoModel();
    $resultado = $modelo->renovar($orgId, $usuarioId, $plano, $ciclo);

    if (!$resultado->status && !empty($resultado->data->plc_id)) {
      $resultado->setArrayParams([
        'location' => Helpers::url('organizacao/plano/' . (int) $resultado->data->plc_id)
      ])->json();
      return;
    }

    if (!$resultado->status || empty($resultado->data->plc_id)) {
      $resultado->json();
      return;
    }

    $plcId = (int) $resultado->data->plc_id;
    $pagamento = (new PagarmeModel($this->projeto))->criarLinkPagamento([
      'plc_id' => $plcId,
      'plano_nome' => $plano->pla_nome,
      'valor' => $resultado->data->plc_valor
    ]);

    if (!empty($pagamento->data)) {
      $modelo->atualizarPagamento($plcId, $pagamento->data);
    }

    $location = Helpers::url('organizacao/plano/' . $plcId);
    if (!$pagamento->status || empty($pagamento->data->url)) {
      (new SimpleResponse('Renovação registrada', 'A contratação foi preservada, mas ainda não foi possível gerar o link de pagamento.'))
        ->warning()
        ->setArrayParams(['location' => $location])
        ->json();
      return;
    }

    (new SimpleResponse('Renovação pronta', 'A renovação foi registrada e o link de pagamento está disponível.'))
      ->sucesso()
      ->setArrayParams(['location' => $location])
      ->json();
  }

  public function gerarPagamento(): void
  {
    $this->validarPermissoes(['organizacao.plano.contratar']);

    $orgId = (int) ($this->contexto->organizacao->org_id ?? 0);
    $plcId = (int) ($_POST['plc_id'] ?? 0);
    $modelo = new PlanoContratadoModel();
    $getContratacao = $modelo->getContratacaoPorOrganizacao($plcId, $orgId);

    if (!$getContratacao->status || empty($getContratacao->data[0])) {
      (new SimpleResponse('Contratação não encontrada', 'Não foi possível localizar a contratação da organização.'))->warning()->json();
      return;
    }

    $contratacao = $getContratacao->data[0];
    if (($contratacao->pla_tipo ?? '') !== 'Comercial' || in_array($contratacao->plc_statusfinanceiro ?? '', ['Pago', 'Pago Manual', 'Isento'], true)) {
      (new SimpleResponse('Pagamento indisponível', 'Esta contratação não precisa de uma nova cobrança neste momento.'))->warning()->json();
      return;
    }

    $urlPagamentoAtual = trim((string) ($contratacao->plc_pagarme_url ?? ''));
    $statusPagamentoAtual = strtolower(trim((string) ($contratacao->plc_pagarme_status ?? '')));
    $statusPagamentoNumerico = is_numeric($statusPagamentoAtual) ? (int) $statusPagamentoAtual : 0;
    $linkComErro = in_array($statusPagamentoAtual, ['expired', 'canceled', 'cancelled', 'failed', 'error', 'inactive'], true)
      || $statusPagamentoNumerico >= 400;

    if ($urlPagamentoAtual !== '' && !$linkComErro) {
      (new SimpleResponse('Link já disponível', 'O link de pagamento atual ainda está disponível. Use-o para concluir o pagamento ou aguarde sua expiração para gerar outro.'))->warning()->json();
      return;
    }

    $pagamento = (new PagarmeModel($this->projeto))->criarLinkPagamento([
      'plc_id' => $plcId,
      'plano_nome' => $contratacao->pla_nome,
      'valor' => $contratacao->plc_valor
    ]);

    if (!empty($pagamento->data)) {
      (new ContratacaoModel())->atualizarPagamento($plcId, $pagamento->data);
    }

    $pagamento->setArrayParams(['location' => Helpers::url('organizacao/plano/' . $plcId)])->json();
  }

  private function montarAvisoRenovacao(object $vigente, object $renovacao, object $renovacaoProgramada, object $contratacoes): ?object
  {
    if ($renovacao->status && !empty($renovacao->data[0])) {
      $pendente = $renovacao->data[0];
      return (object) [
        'tipo' => 'pendente',
        'classe' => 'warning',
        'titulo' => 'Renovação pendente',
        'texto' => 'Existe uma renovação aguardando pagamento para esta organização.',
        'botao' => 'Continuar pagamento',
        'url' => Helpers::url('organizacao/plano/' . (int) $pendente->plc_id)
      ];
    }

    if ($renovacaoProgramada->status && !empty($renovacaoProgramada->data[0])) {
      $programada = $renovacaoProgramada->data[0];
      return (object) [
        'tipo' => 'programada',
        'classe' => 'success',
        'titulo' => 'Renovação confirmada',
        'texto' => 'A próxima contratação já foi paga e começará após o encerramento do período atual.',
        'botao' => 'Ver renovação',
        'url' => Helpers::url('organizacao/plano/' . (int) $programada->plc_id)
      ];
    }

    $plano = $vigente->data[0] ?? null;
    if ($plano && !empty($plano->plc_dtfim)) {
      $fim = strtotime((string) $plano->plc_dtfim);
      $dias = $fim ? (int) floor(($fim - time()) / 86400) : 999;

      if ($dias >= 0 && $dias <= 10) {
        return (object) [
          'tipo' => 'renovar',
          'classe' => 'info',
          'titulo' => 'Seu plano está próximo do vencimento',
          'texto' => 'Renove nos próximos dias e mantenha a continuidade do acesso.',
          'botao' => 'Renovar plano',
          'url' => Helpers::url('organizacao/plano/renovar')
        ];
      }

      return null;
    }

    $ultimo = $contratacoes->data[0] ?? null;
    if ($ultimo && !empty($ultimo->plc_dtfim) && strtotime((string) $ultimo->plc_dtfim) < time()) {
      return (object) [
        'tipo' => 'vencido',
        'classe' => 'danger',
        'titulo' => 'Seu plano está vencido',
        'texto' => 'Regularize a contratação para retomar o acesso completo ao sistema.',
        'botao' => 'Renovar plano',
        'url' => Helpers::url('organizacao/plano/renovar')
      ];
    }

    return null;
  }

  private function montarArquivoConfig(object $organizacao): object
  {
    $config = (object) [
      'status' => true,
      'title' => 'Arquivos',
      'text' => '',
      'nomeModulo' => 'organizacao',
      'origem' => 'organizacao',
      'idorigem' => $organizacao->org_id,
      'idorganizacao' => $organizacao->org_id,
      'idempresa' => 0,
      'limite' => 0,
      'titulo' => 'Arquivos',
      'subtitulo' => 'Gerencie os arquivos vinculados a esta organizacao.',
      'permitir_criar' => isset($this->usuarioLogado->permissoes->{'arquivo.criar'}),
      'permitir_editar' => isset($this->usuarioLogado->permissoes->{'arquivo.atualizar'}),
      'permitir_excluir' => isset($this->usuarioLogado->permissoes->{'arquivo.excluir'}),
      'permitir_principal' => true,
      'obrigatorio' => false,
      'recarregar_ao_salvar' => true,
      'accept' => '.pdf,.txt,.doc,.docx,.xls,.xlsx,.json,.xml,.jpg,.jpeg,.png,.webp',
      'multiple' => true,
      'data' => [],
      'total' => 0,
      'atingiu_limite' => false,
      'pode_criar' => false,
      'componente_id' => ''
    ];

    $getArquivo = (new ArquivoModel())->getArquivosByConfig($config);

    if (!$getArquivo->status) {
      $config->status = false;
      $config->title = 'Erro';
      $config->text = $getArquivo->text ?? 'Erro ao buscar arquivos.';
    } else {
      $config->data = $getArquivo->data;
      $config->total = $getArquivo->total;
    }

    $config->atingiu_limite = $config->limite > 0 && $config->total >= $config->limite;
    $config->pode_criar = $config->permitir_criar && !$config->atingiu_limite;
    $config->componente_id = 'arquivo_' . md5("organizacao_{$organizacao->org_id}_{$organizacao->org_id}_0");

    return $config;
  }

  private function montarContatoConfig(object $organizacao): object
  {
    $config = (object) [
      'status' => true,
      'title' => 'Contatos',
      'text' => '',
      'nomeModulo' => 'organizacao',
      'origem' => 'organizacao',
      'idorigem' => $organizacao->org_id,
      'idorganizacao' => $organizacao->org_id,
      'idempresa' => 0,
      'limite' => 0,
      'titulo' => 'Contatos',
      'subtitulo' => 'Gerencie os contatos complementares vinculados a esta organizacao.',
      'permitir_criar' => isset($this->usuarioLogado->permissoes->{'contato.criar'}),
      'permitir_editar' => isset($this->usuarioLogado->permissoes->{'contato.atualizar'}),
      'permitir_excluir' => isset($this->usuarioLogado->permissoes->{'contato.excluir'}),
      'obrigatorio' => false,
      'recarregar_ao_salvar' => true,
      'data' => [],
      'total' => 0,
      'atingiu_limite' => false,
      'pode_criar' => false,
      'componente_id' => ''
    ];

    $getContato = (new ContatoModel())->getContatosByConfig($config);

    if (!$getContato->status) {
      $config->status = false;
      $config->title = 'Erro';
      $config->text = $getContato->text ?? 'Erro ao buscar contatos.';
    } else {
      $config->data = $getContato->data;
      $config->total = $getContato->total;
    }

    $config->atingiu_limite = $config->limite > 0 && $config->total >= $config->limite;
    $config->pode_criar = $config->permitir_criar && !$config->atingiu_limite;
    $config->componente_id = 'contato_' . md5("organizacao_{$organizacao->org_id}_{$organizacao->org_id}_0");

    return $config;
  }

  private function montarEnderecoConfig(object $organizacao): object
  {
    $config = (object) [
      'status' => true,
      'title' => 'Enderecos',
      'text' => '',
      'nomeModulo' => 'organizacao',
      'origem' => 'organizacao',
      'idorigem' => $organizacao->org_id,
      'idorganizacao' => $organizacao->org_id,
      'idempresa' => 0,
      'limite' => 0,
      'titulo' => 'Enderecos',
      'subtitulo' => 'Gerencie os enderecos vinculados a esta organizacao.',
      'permitir_criar' => isset($this->usuarioLogado->permissoes->{'endereco.criar'}),
      'permitir_editar' => isset($this->usuarioLogado->permissoes->{'endereco.atualizar'}),
      'permitir_excluir' => isset($this->usuarioLogado->permissoes->{'endereco.excluir'}),
      'permitir_principal' => true,
      'obrigatorio' => false,
      'recarregar_ao_salvar' => true,
      'data' => [],
      'total' => 0,
      'atingiu_limite' => false,
      'pode_criar' => false,
      'componente_id' => ''
    ];

    $getEndereco = (new EnderecoModel())->getEnderecosByConfig($config);

    if (!$getEndereco->status) {
      $config->status = false;
      $config->title = 'Erro';
      $config->text = $getEndereco->text ?? 'Erro ao buscar enderecos.';
    } else {
      $config->data = $getEndereco->data;
      $config->total = $getEndereco->total;
    }

    $config->atingiu_limite = $config->limite > 0 && $config->total >= $config->limite;
    $config->pode_criar = $config->permitir_criar && !$config->atingiu_limite;
    $config->componente_id = 'endereco_' . md5("organizacao_{$organizacao->org_id}_{$organizacao->org_id}_0");

    return $config;
  }
}
