<?php

namespace Source\Controller\Sistema;

use Helpers;
use Source\Core\SimpleController;
use Source\Core\SimpleResponse;
use Source\Model\ConhecimentoModel;

class CentralAjudaController extends SimpleController
{
  public function __construct()
  {
    parent::__construct();
    $this->validarUsuarioLogado();
    $this->setFramework(['jquery']);
    $this->setAssetsCss(['phsa.css', 'sistema.css', 'global.css']);
    $this->setAssetsJs(['sistema.js', 'global.js']);
  }

  public function index($url = null): void
  {
    $idParametro = is_array($url) ? ($url['id'] ?? 0) : $url;
    $id = max(0, (int) $idParametro);
    $pesquisa = trim((string) ($_GET['pesquisa'] ?? ''));
    $categoriaInformada = array_key_exists('categoria', $_GET);
    $categoriaId = max(0, (int) ($_GET['categoria'] ?? 0));
    if ($id === 0 && $pesquisa === '' && !$categoriaInformada) {
      $categoriaId = 1;
    }
    $model = new ConhecimentoModel();
    $conteudos = $model->getConteudosAjuda($pesquisa, $categoriaId);
    $categorias = $model->getCategoriasAjuda();
    $registro = null;
    $midias = [];
    $topicoAnterior = null;
    $proximoTopico = null;
    $conteudoNaoEncontrado = false;

    if ($id > 0) {
      $getRegistro = $model->getConteudosAjuda('', 0, $id);
      if (!$getRegistro->status || empty($getRegistro->data[0])) {
        http_response_code(404);
        $conteudoNaoEncontrado = true;
        $registro = null;
      } else {
        $registro = $getRegistro->data[0];
        $getMidias = $model->getMidiasByConhecimentoId($id, true);
        $midias = $getMidias->status ? ($getMidias->data ?? []) : [];

        $topicos = $model->getTopicosNavegacaoAjuda();
        if ($topicos->status && !empty($topicos->data)) {
          foreach ($topicos->data as $indice => $topico) {
            if ((int) $topico->con_id !== $id) continue;
            $topicoAnterior = $topicos->data[$indice - 1] ?? null;
            $proximoTopico = $topicos->data[$indice + 1] ?? null;
            break;
          }
        }
      }
    }

    $tituloBreadcrumb = 'Central de ajuda';
    if ($registro) {
      $tituloBreadcrumb = (string) $registro->con_titulo;
    } elseif ($pesquisa !== '') {
      $tituloBreadcrumb = 'Pesquisa';
    } elseif ($categoriaId > 0 && $categorias->status) {
      foreach (($categorias->data ?? []) as $categoria) {
        if ((int) ($categoria->cct_id ?? 0) === $categoriaId) {
          $tituloBreadcrumb = (string) ($categoria->cct_nome ?? $tituloBreadcrumb);
          break;
        }
      }
    }

    $this->setBreadcrumb([
      ['title' => 'Ajuda', 'label' => 'Ajuda', 'url' => Helpers::url('ajuda')],
      ['title' => $tituloBreadcrumb, 'label' => $tituloBreadcrumb]
    ]);

    $this->render('sistema/ajuda/central', [
      'conteudos' => $conteudos->status ? ($conteudos->data ?? []) : [],
      'categorias' => $categorias->status ? ($categorias->data ?? []) : [],
      'registro' => $registro,
      'midias' => $midias,
      'topicoAnterior' => $topicoAnterior,
      'proximoTopico' => $proximoTopico,
      'pesquisa' => $pesquisa,
      'categoriaSelecionada' => $categoriaId,
      'conteudoNaoEncontrado' => $conteudoNaoEncontrado,
      'erroConsulta' => !$conteudos->status
    ]);
  }

  public function registrarLeitura(): void
  {
    $this->validarUsuarioLogado(true);
    $conhecimentoId = (int) ($_POST['id'] ?? 0);
    if ($conhecimentoId <= 0) {
      (new SimpleResponse('Conhecimento inválido', 'Não foi possível identificar o conteúdo.'))
        ->warning()
        ->json();
      return;
    }

    $leituras = $this->session->get('AJUDA_LEITURAS');
    $leituras = is_array($leituras) ? $leituras : [];
    $agora = time();
    $ultimaLeitura = (int) ($leituras[$conhecimentoId] ?? 0);
    $deveIncrementar = $ultimaLeitura <= 0 || ($agora - $ultimaLeitura) >= 1800;

    $resultado = (new ConhecimentoModel())->registrarLeituraAjuda($conhecimentoId, $deveIncrementar);
    if (!$resultado->status || empty($resultado->data)) {
      $resultado->json();
      return;
    }

    if ($deveIncrementar) {
      $ultimaLeitura = $agora;
      $leituras[$conhecimentoId] = $ultimaLeitura;
      $this->session->set('AJUDA_LEITURAS', $leituras);
    }

    $resultado->data = (object) [
      'visualizacoes' => (int) ($resultado->data->visualizacoes ?? 0),
      'atualizado_em' => $resultado->data->atualizado_em ?? null,
      'contada' => $deveIncrementar,
      'ultimaLeitura' => $ultimaLeitura
    ];
    $resultado->json();
  }
}


