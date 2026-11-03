<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Model\CadastroBasicoModel;

trait CadastroBasicoModuloTrait
{
  abstract protected function moduloCadastroBasico(): string;

  private function definicoesCadastroBasico(): array
  {
    $definicoes = [
      'estadocivil' => ['Estado civil', 'Defina as opções de estado civil utilizadas nos dados pessoais.'],
      'naturalidade' => ['Naturalidade', 'Gerencie cidades e estados de naturalidade.'],
      'sexo' => ['Sexo', 'Gerencie as opções de sexo utilizadas nos dados pessoais.'],
      'naturezajuridica' => ['Natureza jurídica', 'Classifique a natureza jurídica das empresas e organizações.']
    ];
    $modulo = $this->moduloCadastroBasico();
    $categorias = [
      'usuario' => ['estadocivil', 'naturalidade', 'sexo'],
      'empresa' => ['estadocivil', 'naturalidade', 'naturezajuridica', 'sexo'],
      'organizacao' => ['estadocivil', 'naturalidade', 'naturezajuridica', 'sexo']
    ][$modulo] ?? [];
    $cadastros = CadastroBasicoModel::getCadastros();
    $itens = [];
    foreach ($categorias as $slug) {
      if (isset($cadastros[$slug], $definicoes[$slug])) {
        $cadastros[$slug]->titulo = $definicoes[$slug][0];
        $cadastros[$slug]->descricao = $definicoes[$slug][1];
        $itens[$slug] = $cadastros[$slug];
      }
    }
    uasort($itens, static fn($a, $b) => strnatcasecmp($a->titulo, $b->titulo));
    return $itens;
  }

  private function validarAcessoCadastroBasico(string $acao = 'visualizar'): string
  {
    $modulo = $this->moduloCadastroBasico();
    $this->validarPermissoes(["{$modulo}.{$acao}"]);
    return $modulo;
  }

  private function carregarCadastroBasico(string $cadastro): ?object
  {
    return $this->definicoesCadastroBasico()[$cadastro] ?? null;
  }

  private function modelCadastroBasico(string $cadastro): CadastroBasicoModel
  {
    return new CadastroBasicoModel($cadastro, $this->moduloCadastroBasico() . '/basico');
  }

  private function breadcrumbCadastroBasico(string $modulo, ?object $cadastro = null, ?string $acao = null): void
  {
    $moduloLabel = ['usuario' => 'Usuários', 'empresa' => 'Empresas', 'organizacao' => 'Organizações'][$modulo] ?? ucfirst($modulo);
    $items = [
      ['title' => $moduloLabel, 'label' => $moduloLabel, 'url' => Helpers::url($modulo)],
      ['title' => 'Básico', 'label' => 'Básico', 'url' => Helpers::url("{$modulo}/basico")]
    ];
    if ($cadastro) {
      $items[] = ['title' => $cadastro->titulo, 'label' => $cadastro->titulo, 'url' => Helpers::url("{$modulo}/basico/{$cadastro->modulo}")];
    }
    if ($acao) $items[] = ['title' => $acao, 'label' => $acao, 'url' => '#'];
    $this->setBreadcrumb($items);
  }

  public function basico(): void
  {
    $modulo = $this->validarAcessoCadastroBasico();
    $this->breadcrumbCadastroBasico($modulo);
    $cadastros = [];
    foreach ($this->definicoesCadastroBasico() as $slug => $config) {
      $totais = $this->modelCadastroBasico($slug)->contarRegistrosPorStatusContexto();
      $config->total = $totais['Ativo'] + $totais['Inativo'];
      $config->ativos = $totais['Ativo'];
      $config->inativos = $totais['Inativo'];
      $cadastros[] = $config;
    }
    $rotulos = [
      'usuario' => ['Usuários', 'usuários', 'Básico de usuários'],
      'empresa' => ['Empresas', 'empresas', 'Básico de empresas'],
      'organizacao' => ['Organizações', 'organizações', 'Básico de organizações']
    ][$modulo] ?? [ucfirst($modulo), strtolower($modulo), 'Básico'];
    $this->render('sistema/cadastrobasico/hub', [
      'modulo' => $modulo,
      'cadastros' => $cadastros,
      'tituloPagina' => $rotulos[2],
      'rotuloModulo' => $rotulos[1],
      'urlVoltar' => Helpers::url("{$modulo}/list")
    ]);
  }

  public function basicoList(array $url, string $cadastro): void
  {
    $modulo = $this->validarAcessoCadastroBasico();
    $config = $this->carregarCadastroBasico($cadastro);
    if (!$config) { $this->redirect("{$modulo}/basico"); return; }
    $model = $this->modelCadastroBasico($cadastro);
    $this->breadcrumbCadastroBasico($modulo, $config);
    $this->render('sistema/cadastrobasico/listaModulo', [
      'modulo' => $modulo,
      'config' => $config,
      'registros' => $model->getRegistrosContexto(true),
      'tiposCadastro' => $model->getTiposCadastroPermitidos(),
      'podeAtualizar' => Helpers::pode("{$modulo}.atualizar")
    ]);
  }

  public function basicoNovo(array $url, string $cadastro): void
  {
    $modulo = $this->validarAcessoCadastroBasico('criar');
    $this->renderFormularioCadastroBasico($modulo, $cadastro, 0);
  }

  public function basicoEditar(array $url, string $cadastro, string $id): void
  {
    $modulo = $this->validarAcessoCadastroBasico('atualizar');
    $this->renderFormularioCadastroBasico($modulo, $cadastro, (int) $id);
  }

  private function renderFormularioCadastroBasico(string $modulo, string $slug, int $id): void
  {
    $config = $this->carregarCadastroBasico($slug);
    if (!$config) { $this->redirect("{$modulo}/basico"); return; }
    $model = $this->modelCadastroBasico($slug);
    $registro = (object) [
      "{$config->prefixo}_id" => 0,
      "{$config->prefixo}_nome" => '',
      "{$config->prefixo}_tipocadastro" => 'Empresa',
      "{$config->prefixo}_status" => 'Ativo',
      "{$config->prefixo}_statusclass" => 'success',
      "{$config->prefixo}_ordem" => 0,
      "{$config->prefixo}_log" => '[]',
      "{$config->prefixo}_dtcad" => null,
      "{$config->prefixo}_dtalter" => null
    ];
    if ($id > 0) {
      $result = $model->getRegistroByIdContexto($id);
      if (!$result->status || (int) ($result->total ?? 0) !== 1) { $this->redirect("{$modulo}/basico/{$slug}"); return; }
      $registro = $result->data[0];
    }
    $this->breadcrumbCadastroBasico($modulo, $config, $id > 0 ? 'Editar' : 'Novo');
    $this->render('sistema/cadastrobasico/formModulo', [
      'modulo' => $modulo,
      'config' => $config,
      'registro' => $registro,
      'id' => $id,
      'tiposCadastro' => $model->getTiposCadastroPermitidos()
    ]);
  }

  public function basicoSalvar(array $url, string $cadastro): void
  {
    $modulo = $this->moduloCadastroBasico();
    $config = $this->carregarCadastroBasico($cadastro);
    if (!$config) { http_response_code(404); echo json_encode(['status' => false, 'text' => 'Cadastro básico inválido.']); return; }
    $id = (int) ($_POST[$config->prefixo . '_id'] ?? 0);
    $this->validarAcessoCadastroBasico($id > 0 ? 'atualizar' : 'criar');
    $model = $this->modelCadastroBasico($cadastro);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($model->salvarCadastroBasico($_POST), JSON_UNESCAPED_UNICODE);
  }

  public function basicoAlterarStatus(array $url, string $cadastro): void
  {
    $this->validarAcessoCadastroBasico('atualizar');
    header('Content-Type: application/json; charset=utf-8');
    if (!$this->carregarCadastroBasico($cadastro)) { echo json_encode(['status' => false, 'text' => 'Cadastro básico inválido.']); return; }
    $model = $this->modelCadastroBasico($cadastro);
    echo json_encode($model->alterarStatusCadastroBasico((int) ($_POST['id'] ?? 0), (string) ($_POST['status'] ?? ''), (string) ($_POST['motivo'] ?? '')), JSON_UNESCAPED_UNICODE);
  }
}
