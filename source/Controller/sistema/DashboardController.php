<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;
use Source\Core\SimpleSession;
use Source\Model\EnderecoModel;
use Source\Model\UsuarioModel;
use Source\Model\NivelAcessoModel;
use Source\Model\PermissaoModel;
use Source\Model\EmpresaModel;
use Source\Model\ClienteModel;
use Source\Model\CadastroBasicoModel;
use Source\Model\FornecedorModel;
use Source\Model\OrganizacaoModel;
use Source\Model\FinanceiroBasicoModel;
use Source\Model\TituloModel;
use Source\Model\ConhecimentoModel;

class DashboardController extends SimpleController
{
  public function __construct()
  {
    parent::__construct();

    $this->validarUsuarioLogado();
    
    $this->setFramework();

    $this->setAssetsCss('phsa.css');
    $this->setAssetsCss('sistema.css');
    $this->setAssetsCss('global.css');

    $this->setAssetsJs('sistema.js'); 
    $this->setAssetsJs('global.js');
    $this->setAssetsJs('simplemodal.js'); 
  }

  public function dashboard()
	{
	  $empresaAtiva = $this->getEmpresaAtiva();
	  $paletaEmpresaAtiva = $empresaAtiva->emp_paletacores ?? null;
	  if (empty($paletaEmpresaAtiva)) {
	    $paletaEmpresaAtiva = $this->usuarioLogado->empresa->emp_paletacores ?? '';
	  }

	  $this->setMetaTags([
	    'title' => 'Dashboard',
	    'description' => 'Visão geral da operação do ICLIV'
	  ]);

	  $this->render('sistema/dashboard/dashboard', [
	    'paletaEmpresaAtiva' => $paletaEmpresaAtiva,
	    'indicadores' => $this->montarIndicadores()
	  ]);
	}

  public function getIndicadores(): void
  {
    $this->validarUsuarioLogado(true);
    (new SimpleResponse('Dashboard', 'Indicadores atualizados com sucesso.'))
      ->sucesso()
      ->setArrayParams(['data' => $this->montarIndicadores()])
      ->json();
  }

  private function montarIndicadores(): array
  {
    $configuracao = [
      'organizacao' => [
        'permissao' => 'organizacao.visualizar', 'nome' => 'Organização', 'icone' => 'fa-sitemap',
        'href' => 'organizacao', 'tom' => 'blue', 'titulo' => 'Organizações', 'modelo' => OrganizacaoModel::class,
        'metodo' => 'contarOrganizacoesContexto'
      ],
      'empresa' => [
        'permissao' => 'empresa.visualizar', 'nome' => 'Empresas', 'icone' => 'fa-building',
        'href' => 'empresa/list', 'tom' => 'violet', 'titulo' => 'Empresas ativas', 'modelo' => EmpresaModel::class,
        'metodo' => 'contarEmpresasContexto'
      ],
      'usuario' => [
        'permissao' => 'usuario.visualizar', 'nome' => 'Usuários', 'icone' => 'fa-users',
        'href' => 'usuario/list', 'tom' => 'orange', 'titulo' => 'Usuários ativos', 'modelo' => UsuarioModel::class,
        'metodo' => 'contarUsuariosContexto'
      ],
      'titulo' => [
        'permissao' => 'titulo.visualizar', 'nome' => 'Títulos', 'icone' => 'fa-file-invoice-dollar',
        'href' => 'titulo/list', 'tom' => 'slate', 'titulo' => 'Títulos financeiros', 'modelo' => TituloModel::class,
        'metodo' => 'contarTitulosContexto'
      ],
      'fornecedor' => [
        'permissao' => 'fornecedor.visualizar', 'nome' => 'Fornecedores', 'icone' => 'fa-truck',
        'href' => 'fornecedor/list', 'tom' => 'rose', 'titulo' => 'Fornecedores', 'modelo' => FornecedorModel::class,
        'metodo' => 'contarFornecedoresContexto'
      ]
    ];

    $cards = [];
    $modulos = [];
    $permissoes = [];
    foreach ($configuracao as $chave => $item) {
      $permitido = Helpers::pode($item['permissao']);
      $permissoes[$chave] = $permitido;
      if (!$permitido) {
        continue;
      }

      $modelo = new $item['modelo']();
      $resposta = $modelo->{$item['metodo']}();
      $total = $resposta->status && !empty($resposta->data[0])
        ? (int) ($resposta->data[0]->total ?? 0)
        : 0;
      $cards[] = [
        'chave' => $chave,
        'titulo' => $item['titulo'],
        'valor' => $total,
        'icone' => $item['icone'],
        'tom' => $item['tom'],
        'href' => Helpers::url($item['href'])
      ];
      $modulos[] = [
        'nome' => $item['nome'],
        'icone' => $item['icone'],
        'href' => Helpers::url($item['href'])
      ];
    }

    $acoes = [];
    $acoesCadastro = [
      ['permissao' => 'empresa.criar', 'nome' => 'Cadastrar empresa', 'icone' => 'fa-building', 'href' => 'empresa/form'],
      ['permissao' => 'usuario.criar', 'nome' => 'Cadastrar usuário', 'icone' => 'fa-user-plus', 'href' => 'usuario/form'],
      ['permissao' => 'titulo.criar', 'nome' => 'Novo título', 'icone' => 'fa-file-invoice-dollar', 'href' => 'titulo/form'],
      ['permissao' => 'fornecedor.criar', 'nome' => 'Cadastrar fornecedor', 'icone' => 'fa-truck', 'href' => 'fornecedor/form']
    ];
    foreach ($acoesCadastro as $acao) {
      if (Helpers::pode($acao['permissao'])) {
        $acao['href'] = Helpers::url($acao['href']);
        $acoes[] = $acao;
      }
    }

    $nomeUsuario = (string) ($this->usuarioLogado->usu_nomesocial
      ?? $this->usuarioLogado->usu_nomefantasia
      ?? $this->usuarioLogado->usu_razaosocial
      ?? 'Usuário');
    $nomeUsuario = trim(explode(' ', $nomeUsuario)[0]) ?: 'Usuário';

    return [
      'usuario' => $nomeUsuario,
      'organizacao' => (string) ($this->contexto->organizacao->org_nomefantasia
        ?? $this->contexto->organizacao->org_razaosocial ?? 'Organização atual'),
      'empresa' => (string) ($this->contexto->empresa->emp_nomefantasia
        ?? $this->contexto->empresa->emp_razaosocial ?? 'Empresa atual'),
      'atualizado_em' => date('d/m/Y H:i'),
      'permissoes' => $permissoes,
      'cards' => $cards,
      'modulos' => $modulos,
      'acoes' => $acoes
    ];
  }

  public function assistente(): void
  {
    if ($this->configuracaoInicialConcluida()) {
      header('Location: ' . Helpers::url('dashboard'));
      exit;
    }

    $this->setBreadcrumb([
      [
        'title' => 'Configuração inicial',
        'label' => 'Configuração inicial',
        'url' => Helpers::url('dashboard/assistente')
      ]
    ]);
    $this->setMetaTags([
      'title' => 'Configuração inicial',
      'description' => 'Prepare os cadastros básicos para começar a usar o ICLIV.'
    ]);

    $conhecimentoPrimeiroAcesso = null;
    $midiasPrimeiroAcesso = [];
    $resultadoConhecimento = (new ConhecimentoModel())->getConteudosAjuda('', 0, 1);
    if ($resultadoConhecimento->status && !empty($resultadoConhecimento->data[0])) {
      $conhecimentoPrimeiroAcesso = $resultadoConhecimento->data[0];
      $resultadoMidias = (new ConhecimentoModel())->getMidiasByConhecimentoId(1, true);
      $midiasPrimeiroAcesso = $resultadoMidias->status ? ($resultadoMidias->data ?? []) : [];
    }

    $this->render('sistema/dashboard/assistente', [
      'empresaNome' => $this->contexto->empresa->emp_nomefantasia
        ?? $this->contexto->empresa->emp_razaosocial
        ?? 'sua empresa',
      'cadastrosAssistente' => $this->getCadastrosAssistente(),
      'cadastrosFinanceiro' => $this->getCadastrosFinanceiroAssistente(),
      'conhecimentoPrimeiroAcesso' => $conhecimentoPrimeiroAcesso,
      'midiasPrimeiroAcesso' => $midiasPrimeiroAcesso
    ]);
  }

  public function salvarAssistente(): void
  {
    $this->validarUsuarioLogado(true);

    if ($this->configuracaoInicialConcluida()) {
      $this->responderErroAssistente('A configuração inicial desta empresa já foi concluída.');
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
      $this->responderErroAssistente('A confirmação deve ser enviada pelo formulário.');
    }
    if ((string) ($_POST['configuracao_inicial_aceite'] ?? '') !== '1') {
      $this->responderErroAssistente('Confirme a revisão dos cadastros antes de continuar.');
    }

    $dados = json_decode((string) ($_POST['configuracao_inicial_dados'] ?? ''), true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($dados)) {
      $this->responderErroAssistente('Não foi possível interpretar os dados. Revise as etapas e tente novamente.');
    }

    $configuracao = $this->getCadastrosAssistente();
    $configuracaoFinanceiro = array_column($this->getCadastrosFinanceiroAssistente(), null, 'id');
    $itensRecebidos = [];
    foreach (($dados['cadastros'] ?? []) as $grupo) {
      if (!is_array($grupo)) {
        continue;
      }
      $chave = trim((string) ($grupo['painel'] ?? ''));
      if ((isset($configuracao[$chave]) || isset($configuracaoFinanceiro[$chave])) && is_array($grupo['itens'] ?? null)) {
        $itensRecebidos[$chave] = $grupo['itens'];
      }
    }

    foreach ($configuracao as $chave => $cadastro) {
      if (empty($itensRecebidos[$chave])) {
        $this->responderErroAssistente("Selecione ao menos uma opção em {$cadastro['titulo']}.");
      }
      foreach ($itensRecebidos[$chave] as $item) {
        if (!is_array($item)) {
          $this->responderErroAssistente("Há uma opção inválida em {$cadastro['titulo']}.");
        }
        $nome = trim((string) ($item['nome'] ?? ''));
        if ($nome === '' || mb_strlen($nome, 'UTF-8') > 255) {
          $this->responderErroAssistente("Informe um nome válido para todas as opções de {$cadastro['titulo']}.");
        }
      }
    }

    foreach ($configuracaoFinanceiro as $chave => $cadastro) {
      if (empty($itensRecebidos[$chave])) {
        $this->responderErroAssistente("Selecione ao menos uma opção em {$cadastro['titulo']}.");
      }
      foreach ($itensRecebidos[$chave] as $item) {
        if (!is_array($item) || trim((string) ($item['nome'] ?? '')) === '') {
          $this->responderErroAssistente("Informe um nome válido para todas as opções de {$cadastro['titulo']}.");
        }
      }
    }
    $erroHierarquiaFinanceira = $this->validarHierarquiaPlanoAssistente($itensRecebidos['plano-conta'] ?? []);
    if ($erroHierarquiaFinanceira !== '') {
      $this->responderErroAssistente($erroHierarquiaFinanceira);
    }

    $empresaId = (int) ($this->contexto->empresa->emp_id ?? 0);
    $organizacaoId = (int) ($this->contexto->organizacao->org_id ?? 0);
    if ($empresaId <= 0 || $organizacaoId <= 0) {
      $this->responderErroAssistente('Não foi possível identificar a empresa e a organização ativas.');
    }

    $transacao = new SimpleModel('empresa', 'emp');
    $mensagemFalha = 'Não foi possível concluir a configuração inicial.';
    try {
      $transacao->beginTransaction();

      foreach ($configuracao as $chave => $cadastro) {
        $tipo = $cadastro['tipo'] ?? 'basico';
        $modelo = null;
        $prefixo = $cadastro['prefixo'] ?? '';
        $existentesEmpresa = [];
        if ($tipo === 'basico') {
          $modelo = new CadastroBasicoModel($chave);
          $existentes = $modelo->getRegistrosContexto();
          if (!$existentes->status) {
            $mensagemFalha = "Não foi possível consultar {$cadastro['titulo']} no contexto atual.";
            throw new \RuntimeException($mensagemFalha . ' ' . (string) ($existentes->text ?? ''));
          }
          foreach ($existentes->data ?? [] as $registro) {
            if ((int) ($registro->{$prefixo . '_idempresa'} ?? 0) === $empresaId) {
              $chaveExistente = mb_strtolower(trim((string) ($registro->{$prefixo . '_nome'} ?? '')), 'UTF-8');
              if ($chave === 'naturalidade') {
                $chaveExistente .= '|' . strtoupper(trim((string) ($registro->nat_uf ?? ''))) . '|' . mb_strtolower(trim((string) ($registro->nat_pais ?? 'Brasil')), 'UTF-8');
              }
              $existentesEmpresa[$chaveExistente] = $registro;
            }
          }
        }

        foreach ($itensRecebidos[$chave] as $indice => $item) {
          $nome = trim((string) ($item['nome'] ?? ''));

          $dadosSalvar = [
            $prefixo . '_nome' => $nome,
            $prefixo . '_ordem' => max(0, (int) ($item['ordem'] ?? $indice)),
            $prefixo . '_status' => 'Ativo',
            $prefixo . '_tipocadastro' => 'Empresa',
            $prefixo . '_padrao' => !empty($item['padrao']) ? 1 : 0
          ];
          if ($chave === 'naturalidade') {
            $dadosSalvar['nat_uf'] = strtoupper(substr(trim((string) ($item['uf'] ?? '')), 0, 2));
            $dadosSalvar['nat_pais'] = trim((string) ($item['pais'] ?? 'Brasil')) ?: 'Brasil';
          }

          $chaveExistente = mb_strtolower($nome, 'UTF-8');
          if ($chave === 'naturalidade') {
            $chaveExistente .= '|' . strtoupper(substr(trim((string) ($item['uf'] ?? '')), 0, 2)) . '|' . mb_strtolower(trim((string) ($item['pais'] ?? 'Brasil')) ?: 'brasil', 'UTF-8');
          }
          $existente = $existentesEmpresa[$chaveExistente] ?? null;
          if ($existente) {
            $dadosSalvar[$prefixo . '_id'] = (int) ($existente->id ?? $existente->{$prefixo . '_id'} ?? 0);
          }
          $resultado = $modelo->salvarCadastroBasico($dadosSalvar);
          if (!$resultado->status) {
            $mensagemFalha = trim("Não foi possível salvar '{$nome}' em {$cadastro['titulo']}. " . (string) ($resultado->text ?? ''));
            throw new \RuntimeException($mensagemFalha);
          }
        }
      }

      $resultadoFinanceiro = $this->salvarCadastrosFinanceirosAssistente($itensRecebidos);
      if (!$resultadoFinanceiro->status) {
        $mensagemFalha = (string) ($resultadoFinanceiro->text ?? 'Não foi possível salvar os cadastros financeiros.');
        throw new \RuntimeException($mensagemFalha);
      }

      $atualizacao = $transacao->update(
        ['emp_configuracao_inicial' => 1],
        [
          'emp_id' => $empresaId,
          'emp_idorganizacao' => $organizacaoId,
          'emp_status' => 'Ativo'
        ],
        'Configuração inicial concluída||Erro ao concluir configuração inicial'
      );
      if (!$atualizacao->status) {
        $mensagemFalha = trim('Não foi possível registrar a conclusão da configuração inicial. ' . (string) ($atualizacao->text ?? ''));
        throw new \RuntimeException($mensagemFalha);
      }

      $transacao->commit();
      (new SimpleResponse(
        'Configuração concluída',
        'Os cadastros básicos foram configurados. O ICLIV está pronto para uso.'
      ))
        ->sucesso()
        ->setArrayParams(['location' => Helpers::url('dashboard')])
        ->json();
    } catch (\Throwable $e) {
      error_log('[ICLIV assistente] ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
      if (method_exists($transacao, 'rollback')) {
        try {
          $transacao->rollback();
        } catch (\Throwable $rollbackError) {
          // A transação pode já ter sido encerrada pelo driver.
        }
      }
      $this->responderErroAssistente($mensagemFalha . ' Nenhum dado foi confirmado.');
    }
  }

  private function configuracaoInicialConcluida(): bool
  {
    $empresaId = (int) ($this->contexto->empresa->emp_id ?? 0);
    $organizacaoId = (int) ($this->contexto->organizacao->org_id ?? 0);
    if ($empresaId <= 0 || $organizacaoId <= 0) {
      return false;
    }

    $modelo = new SimpleModel('empresa', 'emp');
    $resposta = $modelo->rawQuery(
      "SELECT emp_configuracao_inicial FROM {$modelo->getTable()}
       WHERE emp_id = :emp_id AND emp_idorganizacao = :org_id AND emp_status = 'Ativo' LIMIT 1",
      ['emp_id' => $empresaId, 'org_id' => $organizacaoId],
      true,
      'Configuração consultada||Erro ao consultar configuração'
    );
    return $resposta->status && (int) ($resposta->data[0]->emp_configuracao_inicial ?? 0) === 1;
  }

  private function responderErroAssistente(string $mensagem): void
  {
    (new SimpleResponse('Erro na configuração inicial', $mensagem))
      ->erro()
      ->json();
    exit;
  }

  private function getCadastrosAssistente(): array
  {
    return [
      'sexo' => [
        'titulo' => 'Sexo', 'prefixo' => 'sex',
        'descricao' => 'Opções de sexo utilizadas nos cadastros de pessoas.',
        'sugestoes' => ['Não definido', 'Feminino', 'Masculino']
      ],
      'estadocivil' => [
        'titulo' => 'Estado civil', 'prefixo' => 'ecv',
        'descricao' => 'Estados civis disponíveis nos cadastros de pessoas.',
        'sugestoes' => ['Não definido', 'Casado(a)', 'Divorciado(a)', 'Solteiro(a)', 'Viúvo(a)']
      ],
      'naturalidade' => [
        'titulo' => 'Naturalidade', 'prefixo' => 'nat',
        'descricao' => 'Cidades e estados disponíveis para identificar a naturalidade.',
        'sugestoes' => ['Não definida']
      ],
      'naturezajuridica' => [
        'titulo' => 'Natureza jurídica', 'prefixo' => 'ntj',
        'descricao' => 'Naturezas jurídicas para classificar a organização e as empresas.',
        'sugestoes' => ['Organização religiosa', 'Associação privada']
      ],
    ];
  }

  private function getCadastrosFinanceiroAssistente(): array
  {
    return [
      ['id' => 'conta-financeira', 'titulo' => 'Conta financeira', 'descricao' => 'Defina a conta que representa o caixa inicial da empresa.', 'itens' => [['nome' => 'Caixa Geral', 'tipo' => 'Caixa', 'saldoInicial' => '0,00']], 'padrao' => 0],
      ['id' => 'plano-conta', 'titulo' => 'Plano de contas', 'descricao' => 'Organize as classificações em grupos e itens. Cada grupo selecionado precisa ter ao menos um item selecionado.', 'grupos' => [
        ['nome' => 'ENTRADAS', 'natureza' => 'Entrada', 'itens' => ['Vendas', 'Outras receitas']],
        ['nome' => 'SAÍDAS', 'natureza' => 'Saida', 'itens' => ['Compras', 'Outras despesas']]
      ], 'padrao' => 1],
      ['id' => 'centro-custo', 'titulo' => 'Centro de custo', 'descricao' => 'Defina o centro geral para organizar os lançamentos.', 'itens' => [['nome' => 'GERAL']], 'padrao' => 0],
      ['id' => 'forma-pagamento', 'titulo' => 'Forma de pagamento', 'descricao' => 'Escolha como a empresa recebe e realiza pagamentos.', 'itens' => array_map(static fn ($nome) => ['nome' => $nome], ['Dinheiro', 'Pix', 'Cartão de Crédito', 'Transferência', 'Depósito', 'Cartão de débito']), 'padrao' => 1],
      ['id' => 'condicao-pgto', 'titulo' => 'Condição de pagamento', 'descricao' => 'As condições são vinculadas às formas de pagamento selecionadas.', 'itens' => [
        ['nome' => 'à vista', 'dependencia' => 'forma-pagamento-0', 'quantidade' => 1, 'intervalo' => 0],
        ['nome' => 'à vista', 'dependencia' => 'forma-pagamento-1', 'quantidade' => 1, 'intervalo' => 0],
        ['nome' => '1x', 'dependencia' => 'forma-pagamento-2', 'quantidade' => 1, 'intervalo' => 0],
        ['nome' => 'à vista', 'dependencia' => 'forma-pagamento-3', 'quantidade' => 1, 'intervalo' => 0],
        ['nome' => 'à vista', 'dependencia' => 'forma-pagamento-4', 'quantidade' => 1, 'intervalo' => 0],
        ['nome' => 'à vista', 'dependencia' => 'forma-pagamento-5', 'quantidade' => 1, 'intervalo' => 0]
      ], 'padrao' => 1]
    ];
  }

  private function salvarCadastrosFinanceirosAssistente(array $cadastros): object
  {
    $idsFormas = [];
    foreach ($cadastros['forma-pagamento'] as $indice => $item) {
      $modelo = new FinanceiroBasicoModel('forma_pagamento');
      $resposta = $modelo->salvarFinanceiroBasico([
        'fpg_nome' => trim((string) $item['nome']), 'fpg_ordem' => $indice,
        'fpg_padrao' => !empty($item['padrao']) ? 1 : 0, 'fpg_aceita_parcela' => 1,
        'escopo_financeiro' => 'Empresa'
      ], false);
      if (!$resposta->status) return $resposta;
      $idsFormas[(string) ($item['chave'] ?? ('forma-pagamento-' . $indice))] = (int) ($resposta->data->id ?? $resposta->lastInsertId ?? 0);
    }

    foreach ($cadastros['conta-financeira'] as $indice => $item) {
      $resposta = (new FinanceiroBasicoModel('conta_financeira'))->salvarFinanceiroBasico([
        'ctf_nome' => trim((string) $item['nome']), 'ctf_tipo' => (string) ($item['tipo'] ?? 'Caixa'),
        'ctf_saldo_inicial' => (string) ($item['saldoInicial'] ?? '0,00'), 'ctf_padrao' => !empty($item['padrao']) ? 1 : 0,
        'escopo_financeiro' => 'Empresa'
      ], false);
      if (!$resposta->status) return $resposta;
    }

    $idsGrupos = [];
    $naturezasGrupos = [];
    foreach ($cadastros['plano-conta'] as $indice => $item) {
      if (($item['nivel'] ?? '') !== 'Grupo') continue;
      $resposta = (new FinanceiroBasicoModel('plano_conta'))->salvarFinanceiroBasico([
        'pct_nome' => trim((string) $item['nome']), 'pct_natureza' => (string) ($item['natureza'] ?? 'Entrada'),
        'pct_nivel' => 'Grupo', 'pct_idpai' => null, 'pct_padrao' => !empty($item['padrao']) ? 1 : 0,
        'escopo_financeiro' => 'Empresa'
      ], false);
      if (!$resposta->status) return $resposta;
      $chaveGrupo = mb_strtolower(trim((string) $item['nome']), 'UTF-8');
      $idsGrupos[$chaveGrupo] = (int) ($resposta->data->id ?? $resposta->lastInsertId ?? 0);
      $naturezasGrupos[$chaveGrupo] = (string) ($item['natureza'] ?? 'Entrada');
    }
    foreach ($cadastros['plano-conta'] as $item) {
      if (($item['nivel'] ?? '') !== 'Item') continue;
      $paiId = $idsGrupos[mb_strtolower(trim((string) ($item['pai'] ?? '')), 'UTF-8')] ?? 0;
      if ($paiId <= 0) return (object) ['status' => false, 'text' => 'Cada item do plano de contas precisa estar ligado a um grupo válido.'];
      $resposta = (new FinanceiroBasicoModel('plano_conta'))->salvarFinanceiroBasico([
        'pct_nome' => trim((string) $item['nome']), 'pct_natureza' => $naturezasGrupos[mb_strtolower(trim((string) ($item['pai'] ?? '')), 'UTF-8')] ?? 'Entrada',
        'pct_nivel' => 'Item', 'pct_idpai' => $paiId, 'pct_padrao' => !empty($item['padrao']) ? 1 : 0,
        'escopo_financeiro' => 'Empresa'
      ], false);
      if (!$resposta->status) return $resposta;
    }

    foreach ($cadastros['centro-custo'] as $indice => $item) {
      $resposta = (new FinanceiroBasicoModel('centro_custo'))->salvarFinanceiroBasico([
        'cdc_nome' => trim((string) $item['nome']), 'cdc_padrao' => !empty($item['padrao']) ? 1 : 0,
        'escopo_financeiro' => 'Empresa'
      ], false);
      if (!$resposta->status) return $resposta;
    }
    foreach ($cadastros['condicao-pgto'] as $item) {
      $formaId = $idsFormas[(string) ($item['dependencia'] ?? '')] ?? 0;
      if ($formaId <= 0) return (object) ['status' => false, 'text' => 'Cada condição precisa estar ligada a uma forma de pagamento selecionada.'];
      $resposta = (new FinanceiroBasicoModel('condicao_pgto'))->salvarFinanceiroBasico([
        'cpg_idformapagamento' => $formaId, 'cpg_nome' => trim((string) $item['nome']),
        'cpg_qtdparcela' => max(1, (int) ($item['quantidade'] ?? 1)), 'cpg_intervalodias' => max(0, (int) ($item['intervalo'] ?? 0)),
        'cpg_padrao' => !empty($item['padrao']) ? 1 : 0, 'escopo_financeiro' => 'Empresa'
      ], false);
      if (!$resposta->status) return $resposta;
    }
    return (object) ['status' => true, 'text' => 'Cadastros financeiros salvos.'];
  }

  private function validarHierarquiaPlanoAssistente(array $itens): string
  {
    $grupos = [];
    $itensPlano = [];
    foreach ($itens as $item) {
      if (!is_array($item)) {
        return 'O plano de contas contém uma opção inválida. Revise grupos e itens.';
      }
      $nome = trim((string) ($item['nome'] ?? ''));
      $nivel = trim((string) ($item['nivel'] ?? ''));
      if ($nome === '' || mb_strlen($nome, 'UTF-8') > 80 || !in_array($nivel, ['Grupo', 'Item'], true)) {
        return 'Cada opção do plano de contas precisa ter descrição e nível válidos (Grupo ou Item).';
      }
      $chaveNome = mb_strtolower($nome, 'UTF-8');
      if ($nivel === 'Grupo') {
        if (isset($grupos[$chaveNome])) {
          return "O grupo '{$nome}' está duplicado no plano de contas.";
        }
        $natureza = (string) ($item['natureza'] ?? '');
        if (!in_array($natureza, ['Entrada', 'Saida'], true)) {
          return "A natureza do grupo '{$nome}' é inválida.";
        }
        $grupos[$chaveNome] = ['nome' => $nome, 'itens' => 0];
      } else {
        $itensPlano[] = ['nome' => $nome, 'pai' => trim((string) ($item['pai'] ?? ''))];
      }
    }
    if (!$grupos || !$itensPlano) {
      return 'Selecione ao menos um grupo e um item no plano de contas.';
    }
    foreach ($itensPlano as $item) {
      $chavePai = mb_strtolower($item['pai'], 'UTF-8');
      if ($item['pai'] === '' || !isset($grupos[$chavePai])) {
        return "O item '{$item['nome']}' precisa estar ligado a um grupo selecionado.";
      }
      $grupos[$chavePai]['itens']++;
    }
    foreach ($grupos as $grupo) {
      if ($grupo['itens'] === 0) {
        return "O grupo '{$grupo['nome']}' não possui nenhum item selecionado. Selecione ao menos um item ou desmarque o grupo.";
      }
    }
    return '';
  }
}
