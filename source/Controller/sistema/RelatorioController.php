<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Core\SimpleSession;
use Source\Model\PermissaoRelatorioModel;
use Source\Model\ClienteModel;
use Source\Model\EmpresaModel;
use Source\Model\UsuarioModel;

class RelatorioController extends SimpleController
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
    $this->setAssetsJs('simplemodal.js'); 
    $this->setAssetsJs('global.js');
  }

  public function list()
  {
    $this->validarPermissoes(['visualizar_relatorio']);

    $permissaoRelatorioModel = new PermissaoRelatorioModel();
    $getRelatorio = $permissaoRelatorioModel->mySelect([
      'pmr_idnvacesso' => $this->usuarioLogado->nva_id
    ]);

    $this->setBreadcrumb([
      [
        'title' => 'Visualizar relatório',
        'label' => 'Relatório',
        'url' => Helpers::url('relatorio/list')
      ]
    ]);

    $this->setMetaTags([
      'title' => 'Pesquisa de relatório',
      'description' => 'Página para pesquisar relatório'
    ]);

    $this->render('sistema/relatorio/list', [
      'getRelatorio' => $getRelatorio
    ]);
  }

  public function form($url = null)
  {
    $this->validarPermissoes(['visualizar_relatorio']);

    if ( empty($url['id']) ) {
      $this->setBreadcrumb([
        [
          'title' => 'Visualizar relatório',
          'label' => 'Relatório',
          'url' => Helpers::url('relatorio/list')
        ]
      ]);

      $this->setMetaTags([
        'title' => 'Exibir Relatório',
        'description' => 'Visualização do relatório selecionado'
      ]);

      $this->render('template/sistema/erro', [
        'erro' => (object) [
          'status' => false,
          'title' => 'Erro de carregamento',
          'text' => 'Não foi possivel identificar o relatório informado',
        ]
      ]);
      exit;
    }

    $relatorio = urldecode($url['id']);
    $basePath = dirname(__DIR__, 2) . '/Web/sistema/relatorio/arquivo/';

    $permissaoRelatorioModel = new PermissaoRelatorioModel();
    $getRelatorio = $permissaoRelatorioModel->mySelect([
      'pmr_idnvacesso' => $this->usuarioLogado->nva_id,
      'pmr_dir' => $relatorio
    ]);

    if ( !$getRelatorio->status || $getRelatorio->total==0 ) {
      $this->setBreadcrumb([
        [
          'title' => 'Visualizar relatório',
          'label' => 'Relatório',
          'url' => Helpers::url('relatorio/list')
        ]
      ]);

      $this->setMetaTags([
        'title' => 'Exibir Relatório',
        'description' => 'Visualização do relatório selecionado'
      ]);

      if ( !$getRelatorio->status ) {
        $this->render('template/sistema/erro', [
          'erro' => (object) [
            'status' => false,
            'title' => 'Erro de carregamento',
            'text' => 'Erro ao buscar relatório: '.$getRelatorio->text,
          ]
        ]);
      } else {
        $this->render('template/sistema/erro', [
          'erro' => (object) [
            'status' => false,
            'title' => 'ATENÇÃO',
            'text' => 'Nenhum relatório encontrado ou você não tem permissão para acessa-lo',
          ]
        ]);
        exit;
      }
    }

    $filePath = $basePath . $relatorio;
    if ( !file_exists($filePath) ) {
      $this->setBreadcrumb([
        [
          'title' => 'Visualizar relatório',
          'label' => 'Relatório',
          'url' => Helpers::url('relatorio/list')
        ]
      ]);

      $this->setMetaTags([
        'title' => 'Exibir Relatório',
        'description' => 'Visualização do relatório selecionado'
      ]);

      $this->render('template/sistema/erro', [
        'erro' => (object) [
          'status' => false,
          'title' => 'Erro de carregamento',
          'text' => 'Relatório não encontrado',
        ]
      ]);
      exit;
    }

    $relatorioNome = str_replace('_', ' ', pathinfo($relatorio, PATHINFO_FILENAME));

    $this->setBreadcrumb([
      [
        'title' => 'Visualizar relatório',
        'label' => 'Relatório',
        'url' => Helpers::url('relatorio/list')
      ],
      [
        'title' => str_replace('_', ' ', pathinfo($relatorio, PATHINFO_FILENAME)),
        'label' => htmlspecialchars($relatorioNome)
      ]
    ]);

    $this->setMetaTags([
      'title' => 'Exibir Relatório',
      'description' => 'Visualização do relatório selecionado'
    ]);

    $urlParams = $_GET;
    unset($urlParams['url']);

    $this->render('sistema/relatorio/form', [
      'relatorioFile' => $relatorio,
      'relatorioPath' => $filePath,
      'relatorioNome' => $relatorioNome,
      'urlParams'     => $urlParams
    ]);
  }

  public function execute($url = null)
  {
    if (!isset($url['id']) || empty($url['id'])) {
      (new SimpleResponse('Erro', 'Relatório não identificado'))->erro()->json();
    }

    $relatorio = str_replace('.php', '', urldecode($url['id']));

    if (!method_exists($this, $relatorio)) {
      (new SimpleResponse('Erro', 'Relatório inválido'))->erro()->json();
    }

    $this->{$relatorio}($_POST);
  }

  private function montarFiltroLike(array $camposLike, &$stringWhere, &$params, $valor)
  {
    if (!empty($valor)) {
      $stringWhere .= (!empty($stringWhere) ? " AND (" : "(");
      $stringWhere .= implode(" LIKE :conteudo OR ", $camposLike) . " LIKE :conteudo)";
      $params[':conteudo'] = "%{$valor}%";
    }
  }

  private function montarFiltroStatus($campo, &$stringWhere, &$params, $valor)
  {
    if (!empty($valor)) {
      if (is_array($valor)) {
        $placeholders = [];
        foreach ($valor as $key => $status) {
          $paramKey = ":status$key";
          $placeholders[] = $paramKey;
          $params[$paramKey] = $status;
        }
        $stringWhere .= (!empty($stringWhere) ? " AND " : "") . "$campo IN (" . implode(',', $placeholders) . ")";
      } else {
        $stringWhere .= (!empty($stringWhere) ? " AND " : "") . "$campo = :status";
        $params[':status'] = $valor;
      }
    }
  }

  private function montarFiltroData($filtros, &$stringWhere, &$params)
  {
    if (!empty($filtros['tipoFiltro']) && !empty($filtros['campoFiltro'])) {
      if ($filtros['tipoFiltro'] === 'Exatidao' && !empty($filtros['iniFiltro'])) {
        $stringWhere .= (!empty($stringWhere) ? " AND " : "") . "DATE({$filtros['campoFiltro']}) = :iniFiltro";
        $params[':iniFiltro'] = $filtros['iniFiltro'];
      } elseif ($filtros['tipoFiltro'] === 'Periodo' && !empty($filtros['iniFiltro']) && !empty($filtros['fimFiltro'])) {
        $stringWhere .= (!empty($stringWhere) ? " AND " : "") . "DATE({$filtros['campoFiltro']}) BETWEEN :iniFiltro AND :fimFiltro";
        $params[':iniFiltro'] = $filtros['iniFiltro'];
        $params[':fimFiltro'] = $filtros['fimFiltro'];
      }
    }
  }

  private function montarFiltroOrderLimit($filtros, &$stringWhere)
  {
    if (!empty($filtros['campoOrder'])) {
      $orderBy = !empty($filtros['orderBy']) ? $filtros['orderBy'] : "ASC";
      $stringWhere .= " ORDER BY {$filtros['campoOrder']} $orderBy";
    }

    if (!empty($filtros['limit']) && is_numeric($filtros['limit'])) {
      $stringWhere .= " LIMIT " . (int)$filtros['limit'];
    }
  }

  public function Lista_de_cliente($filtros)
  {
    $clienteModel = new ClienteModel();
    
    $stringWhere  = '';
    $params    = [];

    if (!empty($filtros['conteudo'])) {
      $camposLike = [
        $clienteModel->prefixedColumn('id'),
        $clienteModel->prefixedColumn('nomefantasia'),
        $clienteModel->prefixedColumn('cnpjcpf')
      ];

      $this->montarFiltroLike($camposLike, $stringWhere, $params, $filtros['conteudo']);
    }

    if (!empty($filtros['status'])) {
      $this->montarFiltroStatus($clienteModel->prefixedColumn('status'), $stringWhere, $params, $filtros['status']);
    }

    $this->montarFiltroData($filtros, $stringWhere, $params);
    $this->montarFiltroOrderLimit($filtros, $stringWhere);

    $getResultado = $clienteModel->mySelect(
      where: $stringWhere,
      params: $params
    );

    if ( $getResultado->status && $getResultado->total>0 ) {
      foreach ($getResultado->data as $key => $cliente) {
        $cliente->{$clienteModel->prefixedColumn('cnpjcpf')} = Helpers::adicionaMascaraCnpjCPf($cliente->{$clienteModel->prefixedColumn('cnpjcpf')});
      }
    }

    $getResultado->urlString = Helpers::url("relatorio/form/Lista_de_cliente.php") . '?' . http_build_query($filtros);

    echo json_encode($getResultado);exit;
  }

  public function Lista_de_empresa($filtros)
  {
    $empresaModel = new EmpresaModel();
    
    $stringWhere  = '';
    $params    = [];

    if (!empty($filtros['conteudo'])) {
      $camposLike = [
        $empresaModel->prefixedColumn('id'),
        $empresaModel->prefixedColumn('nomefantasia'),
        $empresaModel->prefixedColumn('cnpjcpf')
      ];

      $this->montarFiltroLike($camposLike, $stringWhere, $params, $filtros['conteudo']);
    }

    if (!empty($filtros['status'])) {
      $this->montarFiltroStatus($empresaModel->prefixedColumn('status'), $stringWhere, $params, $filtros['status']);
    }

    $this->montarFiltroData($filtros, $stringWhere, $params);
    $this->montarFiltroOrderLimit($filtros, $stringWhere);

    $getResultado = $empresaModel->mySelect(
      where: $stringWhere,
      params: $params
    );

    $getResultado->urlString = Helpers::url("relatorio/form/Lista_de_empresa.php") . '?' . http_build_query($filtros);

    echo json_encode($getResultado);
    exit;
  }

  public function Lista_de_usuário($filtros)
  {
    $usuarioModel = new UsuarioModel();
    
    $stringWhere  = '';
    $params    = [];

    if (!empty($filtros['conteudo'])) {
      $camposLike = [
        $usuarioModel->prefixedColumn('id'),
        $usuarioModel->prefixedColumn('username'),
        $usuarioModel->prefixedColumn('email')
      ];

      $this->montarFiltroLike($camposLike, $stringWhere, $params, $filtros['conteudo']);
    }

    if (!empty($filtros['status'])) {
      $this->montarFiltroStatus($usuarioModel->prefixedColumn('status'), $stringWhere, $params, $filtros['status']);
    }

    $this->montarFiltroData($filtros, $stringWhere, $params);
    $this->montarFiltroOrderLimit($filtros, $stringWhere);

    $getResultado = $usuarioModel->mySelect(
      where: $stringWhere,
      params: $params
    );

    $getResultado->urlString = Helpers::url("relatorio/form/Lista_de_usuário.php") . '?' . http_build_query($filtros);

    echo json_encode($getResultado);
    exit;
  }

  public function getRelatorio()
  {
    if ( $_POST['nva_id']=='' || !isset($_POST['nva_id']) ) {
      (new SimpleResponse('Erro', 'Nível de acesso não identificado!'))->erro()->json();
    }

    $basePath = dirname(__DIR__, 2) . '/Web/sistema/relatorio/arquivo/';
    $arquivos = [];
    $permissoesAtuais = [];

    $permissaoRelatorioModel = new PermissaoRelatorioModel();
    $getPermissoes = $permissaoRelatorioModel->mySelect([
      'pmr_idnvacesso' => $_POST['nva_id']
    ]);

    if ($getPermissoes->status) {
      foreach ($getPermissoes->data as $permissao) {
        $permissoesAtuais[] = $permissao->pmr_dir;
      }
    }

    if (is_dir($basePath)) {
      $files = scandir($basePath);
      foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
          $arquivos[] = [
            'nome' => str_replace('_', ' ', pathinfo($file, PATHINFO_FILENAME)),
            'dir' => $file,
            'permitido' => in_array($file, $permissoesAtuais)
          ];
        }
      }
    }

    echo json_encode([
      'status' => !empty($arquivos),
      'data' => $arquivos
    ]);
    exit;
  }

  public function salvarPermissaoRelatorio()
  {
    if ( $_POST['nva_id']=='' || !isset($_POST['nva_id']) ) {
      (new SimpleResponse('Erro', 'Nível de acesso não identificado!'))->erro()->json();
    }

    $permissaoRelatorioModel = new PermissaoRelatorioModel();
    $deletePermissao = $permissaoRelatorioModel->myDelete(['pmr_idnvacesso' => $_POST['nva_id']]);

    if ( $deletePermissao->status ) {
      $deletePermissao->insertPermissao = [];
      $deletePermissao->erroInsert = [];

      $permissoes = $_POST['permissoesSelecionadas'] ?? [];
      foreach ($permissoes as $arquivo) {
        $data = [
          'pmr_dir' => $arquivo,
          'pmr_nome' => str_replace('_', ' ', pathinfo($arquivo, PATHINFO_FILENAME)),
          'pmr_idnvacesso' => $_POST['nva_id']
        ];
        $insertPermissao = $permissaoRelatorioModel->insertPermissaoRelatorio($data);

        if ( $insertPermissao->status ) {
          $deletePermissao->insertPermissao[] = $insertPermissao;
        } else {
          $deletePermissao->erroInsert[] = $insertPermissao;
        }
      }

      if ( empty($deletePermissao->erroInsert) ) {
        $deletePermissao->text = 'Permissões atualizadas com sucesso!';
      }else{
        $deletePermissao->text = 'Ocorreu erro ao salvar as novas permissões!';
        // (new SimpleResponse('Erro', 'Ocorreu erro ao salvar as novas permissões!'))->erro()->json();
      }
    }

    echo json_encode($deletePermissao);
    exit;
  }
}
