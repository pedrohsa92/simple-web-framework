<?php
ob_start(); 
session_start();
date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

if ($_SERVER['HTTP_HOST'] != "localhost" && !$_SERVER['HTTPS']) {
  header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
  exit;
}

require __DIR__ . "/vendor/autoload.php";

use Dotenv\Dotenv;
use Source\Core\SimpleResponse;
use Source\Core\SimpleValidator;
use Source\Core\SimpleConnect;
use Source\Core\SimpleWeb;
use Source\Core\SimpleRouter;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
foreach ($_ENV as $key => $ENV) define($key, $ENV);

try {
  SimpleValidator::validate();
  SimpleConnect::getInstance();
  $simpleWeb = new SimpleWeb();
} catch (\PDOException $e) {
  $response = new SimpleResponse("Erro de Conexão com o Banco de Dados", $e->getMessage());
  $response->erro()->html();
  exit;
} catch (\Exception $e) {
  $response = new SimpleResponse("Erro de Configuração", $e->getMessage());
  $response->erro()->html();
  exit;
}

$router = new SimpleRouter();

$router->group('', 'site', 'HomeController', function ($router) {
  $router->get('/', 'index');
}, 'site.');

$router->group('contrate', 'site', 'ContrateController', function ($router) {
  $router->get('', 'index', 'contrate.index');
  $router->get('{pla_id}', 'index', 'contrate.plano');
  $router->post('salvar');
  $router->post('tentarPagamento');
  $router->post('validarCnpj');
  $router->post('validarCpf');
  $router->post('validarEmail');
  $router->post('validarCelular');
}, 'contrate.');

$router->group('webhook', 'site', 'WebhookController', function ($router) {
  $router->post('pagarme');
}, 'webhook.');

$router->group('', 'sistema', 'DashboardController', function ($router) {
  $router->get('dashboard');
  $router->get('dashboard/assistente', 'assistente');
  $router->post('dashboard/salvarAssistente', 'salvarAssistente');
  $router->post('dashboard/getIndicadores', 'getIndicadores');
}, 'dashboard.');

$router->group('ajuda', 'sistema', 'CentralAjudaController', function ($router) {
  $router->get('{id}', 'index', 'ajuda.conteudo');
  $router->get('', 'index', 'ajuda.central');
  $router->post('registrarLeitura');
}, 'ajuda.');

$router->group('', 'sistema', 'OrganizacaoController', function ($router) {
  $router->get('organizacao/basico', 'basico');
  $router->get('organizacao/basico/{cadastro}/novo', 'basicoNovo');
  $router->get('organizacao/basico/{cadastro}/{id}', 'basicoEditar');
  $router->get('organizacao/basico/{cadastro}', 'basicoList');
  $router->post('organizacao/basico/{cadastro}/salvar', 'basicoSalvar');
  $router->post('organizacao/basico/{cadastro}/status', 'basicoAlterarStatus');
  $router->get('organizacao/regularizacao', 'regularizacao', 'regularizacao');
  $router->get('organizacao/plano/renovar', 'renovar', 'plano.renovar');
  $router->get('organizacao/plano/{id}', 'plano', 'plano.form');
  $router->get('organizacao/plano', 'plano', 'plano.list');
  $router->get('organizacao/{tabLink}', 'organizacao');
  $router->post('organizacao/salvarOrganizacao', 'salvarOrganizacao');
  $router->post('organizacao/gerarPagamento', 'gerarPagamento');
  $router->post('organizacao/salvarRenovacao', 'salvarRenovacao');
}, 'organizacao.');

$router->group('projeto', 'sistema', 'ProjetoController', function ($router) {
  $router->get('conhecimento/form/{id}', 'conhecimentoForm', 'conhecimento.form');
  $router->get('conhecimento/form', 'conhecimentoForm', 'conhecimento.form.novo');
  $router->get('conhecimento/categoria/novo', 'conhecimentoCategoriaForm', 'conhecimento.categoria.novo');
  $router->get('conhecimento/categoria/{id}', 'conhecimentoCategoriaForm', 'conhecimento.categoria.form');
  $router->get('conhecimento/categoria', 'conhecimentoCategoria', 'conhecimento.categoria');
  $router->get('conhecimento/list', 'conhecimentoList', 'conhecimento.list');
  $router->get('plano/{id}', 'plano', 'plano.form');
  $router->get('plano', 'plano', 'plano.list');
  $router->get('contratacao/{id}', 'contratacao', 'contratacao.form');
  $router->get('contratacao', 'contratacao', 'contratacao.list');
  $router->get('email/template/{id}', 'emailTemplate', 'email.template.form');
  $router->get('email/template', 'emailTemplate', 'email.template.list');
  $router->get('email/campanha', 'emailCampanha', 'email.campanha');
  $router->get('email/enviado/{id}', 'emailEnviado', 'email.enviado.form');
  $router->get('email/enviado', 'emailEnviado', 'email.enviado.list');
  $router->get('email/configuracao', 'emailConfiguracao', 'email.configuracao');
  $router->get('email', 'email', 'email.hub');
  $router->get('email/{id}', 'emailTemplate', 'email.form');
  $router->get('', 'projeto', 'cadastro');
  $router->get('{tabLink}', 'projeto', 'cadastro.tab');

  $router->post('salvarProjeto');
  $router->post('salvarDocumento');
  $router->post('salvarImagens');
  $router->post('salvarGateway');
  $router->post('pesquisarOrganizacoes');
  $router->post('pesquisarPalavrasChaves');
  $router->post('salvarPalavraChave');
  $router->post('excluirPalavraChave');
  $router->post('salvarPlano');
  $router->post('pesquisarContratacoes');
  $router->post('pesquisarEmails');
  $router->post('salvarEmail');
  $router->post('testarConexaoEmail');
  $router->post('salvarEmailConfiguracao');
  $router->post('pesquisarEmailsEnviados');
  $router->post('salvarConhecimento');
  $router->post('conhecimento/pesquisar', 'pesquisarConhecimento');
  $router->post('salvarConhecimentoMidia');
  $router->post('alterarStatusConhecimentoMidia');
  $router->post('salvarConhecimentoCategoria');
  $router->post('pesquisarConhecimentoCategorias');
  $router->post('reordenarConhecimentoCategoria');
}, 'projeto.');

$router->group('projeto/gateway', 'sistema', 'GatewayController', function ($router) {
  $router->post('consultar');
}, 'projeto.gateway.');

$router->group('empresa', 'sistema', 'EmpresaController', function ($router) {
  $router->get('basico', 'basico');
  $router->get('basico/{cadastro}/novo', 'basicoNovo');
  $router->get('basico/{cadastro}/{id}', 'basicoEditar');
  $router->get('basico/{cadastro}', 'basicoList');
  $router->post('basico/{cadastro}/salvar', 'basicoSalvar');
  $router->post('basico/{cadastro}/status', 'basicoAlterarStatus');
  $router->get('form/{id}', 'form');
  $router->get('list');

  $router->post('pesquisarEmpresa');
  $router->post('salvarEmpresa');
  $router->post('salvarPwa', 'salvar', 'empresa.pwa.salvar', 'PwaController');
  $router->post('pesquisarRapida');
}, 'empresa.');

$router->group('', 'sistema', 'UsuarioController', function ($router) {
  $router->get('login');
}, 'sistema.');

$router->group('usuario', 'sistema', 'UsuarioController', function ($router) {
  $router->get('basico', 'basico');
  $router->get('basico/{cadastro}/novo', 'basicoNovo');
  $router->get('basico/{cadastro}/{id}', 'basicoEditar');
  $router->get('basico/{cadastro}', 'basicoList');
  $router->post('basico/{cadastro}/salvar', 'basicoSalvar');
  $router->post('basico/{cadastro}/status', 'basicoAlterarStatus');
  $router->get('form/{id}', 'form');
  $router->get('list');
  $router->get('bloqueado');

  $router->post('validarLogin');
  $router->post('desbloquearUsuario');
  $router->post('logout');
  $router->post('setSideBar');
  $router->post('setPaletaCores');
  $router->post('setTema');
  $router->post('pesquisarUsuario');
  $router->post('salvarUsuario');
  $router->post('pesquisarRapida');
}, 'usuario.');

$router->group('nivelacesso', 'sistema', 'NivelAcessoController', function ($router) {
  $router->get('form/{id}', 'form');
  $router->get('list');
  $router->post('salvarNivelAcesso');
  $router->post('pesquisarNivelAcesso');
}, 'nivelacesso.');

$router->group('financeiro', 'sistema', 'FinanceiroController', function ($router) {
  $router->get('dashboard');
  $router->get('relatorio', 'relatorios', 'relatorio');
  $router->get('relatorio/{tipo}', 'relatorioDetalhe', 'relatorioDetalhe');
  $router->get('relatorios');
  $router->get('transferencia', 'transferenciaList', 'transferencia');
  $router->get('transferencia/list', 'transferenciaList', 'transferenciaList');
  $router->get('transferencia/form', 'transferenciaForm', 'transferenciaFormNovo');
  $router->get('transferencia/form/{id}', 'transferenciaForm', 'transferenciaForm');
  $router->get('basico');
  $router->get('basico/{modulo}', 'basicoForm');
  $router->get('basico/{modulo}/novo', 'basicoForm', 'basicoFormNovo');
  $router->get('basico/{modulo}/{id}', 'basicoForm', 'basicoFormEdicao');
  $router->post('pesquisarTransferencia');
  $router->post('saldoDisponivelTransferencia');
  $router->post('salvarTransferencia');
  $router->post('estornarTransferencia');
  $router->post('salvarBasico');
  $router->post('basico/alterarStatus', 'alterarStatusBasico');
}, 'financeiro.');

$router->group('titulo', 'sistema', 'TituloController', function ($router) {
  $router->get('form', 'form', 'formNovo');
  $router->get('form/{id}', 'form', 'formEditar');
  $router->get('list');
  $router->post('salvarTitulo');
  $router->post('confirmarTitulo');
  $router->post('cancelarTitulo');
  $router->post('gerarLancamentoTitulo');
  $router->post('pagarReceberLancamento');
  $router->post('modalDetalhesLancamento');
  $router->post('modalCorrecaoLancamento');
  $router->post('corrigirLancamento');
  $router->post('estornarLancamento');
  $router->post('pesquisarTitulo');
  $router->post('pesquisarRapida');
  $router->post('pesquisarVinculo');
  $router->post('dadosLancamentoRapido');
  $router->post('registrarLancamentoRapido');
}, 'titulo.');

$router->group('fornecedor', 'sistema', 'FornecedorController', function ($router) {
  $router->get('form', 'form', 'formNovo');
  $router->get('form/{id}', 'form');
  $router->get('list');
  $router->post('pesquisarFornecedor');
  $router->post('pesquisarRapida');
  $router->post('salvarFornecedor');
  $router->post('salvarAcessoFornecedor');
  $router->post('alterarStatusFornecedor');
}, 'fornecedor.');

$router->group('contexto', 'sistema', 'ContextoController', function ($router) {
  $router->post('listar');
  $router->post('trocar');
}, 'contexto.');

$router->group('endereco', 'sistema', 'EnderecoController', function ($router) {
  $router->post('getEndereco/{id}', 'getEndereco');
  $router->post('salvarEndereco');
  $router->post('deleteEndereco/{id}', 'deleteEndereco');
}, 'endereco.');

$router->group('contato', 'sistema', 'ContatoController', function ($router) {
  $router->post('getContato/{id}', 'getContato');
  $router->post('salvarContato');
  $router->post('deleteContato/{id}', 'deleteContato');
}, 'contato.');

$router->group('arquivo', 'sistema', 'ArquivoController', function ($router) {
  $router->post('salvarArquivo');
  $router->post('deleteArquivo/{id}', 'deleteArquivo');
}, 'arquivo.');

// Rota pública da empresa: registrar após as rotas internas para não capturá-las.
$router->group('', 'site', 'HomeController', function ($router) {
  $router->get('{slug}', 'paginaPublica', 'site.paginaPublica');
}, 'site.publica.');

$router->dispatch();

ob_end_flush();
