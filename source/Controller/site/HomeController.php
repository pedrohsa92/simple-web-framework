<?php

namespace Source\Controller\Site;

use Source\Core\SimpleController;
use Source\Model\EnderecoModel;
use Source\Model\EmpresaModel;
use Source\Model\PlanoModel;
use Source\Model\PwaModel;
use Source\Model\OrganizacaoModel;

class HomeController extends SimpleController
{
  public function __construct()
  {
    parent::__construct();
    
    $this->setFramework();
  }

  public function index()
  {
    $this->setMetaTags([
      'title' => 'icliv — gestão que cuida',
      'description' => 'O ecossistema simples para negócios que cuidam de animais, seus clientes e suas histórias.'
    ]);

    $this->setAssetsCss([
      'global.css',
      'site.css',
      'phsa.css'
    ]);
    $this->setAssetsJs('global.js');

    $getPlanos = (new PlanoModel())->getPlanosPublicos();

    $this->render('site/home/home', [
      'planos' => $getPlanos->status ? ($getPlanos->data ?? []) : []
    ]);
  }

  public function paginaPublica(?array $params = null, ?string $slug = null): void
  {
    $slug = trim((string) ($slug ?: ($params['slug'] ?? '')));
    $resultadoEmpresa = (new EmpresaModel())->getEmpresaPaginaPublicaPorSlug($slug);
    $empresa = $resultadoEmpresa->status ? ($resultadoEmpresa->data[0] ?? null) : null;

    if (!$empresa) {
      $resultadoOrganizacao = (new OrganizacaoModel())->getOrganizacaoPaginaPublicaPorSlug($slug);
      $organizacao = $resultadoOrganizacao->status ? ($resultadoOrganizacao->data[0] ?? null) : null;

      if ($organizacao) {
        $nomeOrganizacao = trim((string) ($organizacao->org_nomefantasia ?? '')) ?: trim((string) ($organizacao->org_razaosocial ?? 'Organização'));
        $descricaoMeta = trim(preg_replace('/\\s+/', ' ', strip_tags((string) ($organizacao->org_webapp_conteudo ?: $organizacao->org_descricao))));
        $this->setMetaTags([
          'title' => $nomeOrganizacao . ' — icliv',
          'description' => mb_substr($descricaoMeta !== '' ? $descricaoMeta : 'Página oficial de ' . $nomeOrganizacao . '.', 0, 250)
        ]);
        $this->setAssetsCss(['global.css', 'site.css', 'phsa.css']);
        $this->setAssetsJs('global.js');
        $this->render('site/home/organizacaoPublica', [
          'organizacaoPublica' => $organizacao,
          'conteudoOrganizacaoSeguro' => $this->sanitizarConteudoPublico((string) ($organizacao->org_webapp_conteudo ?? ''))
        ]);
        return;
      }

      http_response_code(404);
      $this->setMetaTags([
        'title' => 'Página não encontrada — icliv',
        'description' => 'A página solicitada não está disponível.'
      ]);
      $this->setAssetsCss(['global.css', 'site.css', 'phsa.css']);
      $this->setAssetsJs('global.js');
      $this->render('site/home/publica', ['empresaPublica' => null]);
      return;
    }

    $nomeEmpresa = trim((string) ($empresa->emp_nomefantasia ?? '')) ?: trim((string) ($empresa->emp_razaosocial ?? 'Empresa'));
    $descricaoMeta = trim(preg_replace('/\\s+/', ' ', strip_tags((string) ($empresa->emp_webapp_conteudo ?: $empresa->emp_descricao))));
    if ($descricaoMeta === '') {
      $descricaoMeta = 'Página oficial de ' . $nomeEmpresa . '.';
    }

    $this->setMetaTags([
      'title' => $nomeEmpresa . ' — icliv',
      'description' => mb_substr($descricaoMeta, 0, 250)
    ]);
    $this->setAssetsCss(['global.css', 'site.css', 'phsa.css']);
    $this->setAssetsJs('global.js');
    $this->setAssetsJs('simplemodal.js?v=2026072602');

    $organizacaoId = (int) ($empresa->emp_idorganizacao ?? 0);
    $empresaId = (int) ($empresa->emp_id ?? 0);

    $enderecos = (new EnderecoModel())->getEnderecosByConfig((object) [
      'idorganizacao' => $organizacaoId,
      'idempresa' => $empresaId,
      'idorigem' => $empresaId,
      'origem' => 'empresa'
    ]);
    $enderecoPublico = null;
    if ($enderecos->status) {
      foreach (($enderecos->data ?? []) as $endereco) {
        if (($endereco->end_status ?? '') === 'Ativo' && (int) ($endereco->end_principal ?? 0) === 1) {
          $enderecoPublico = $endereco;
          break;
        }
      }
    }

    $pwaResponse = (new PwaModel())->getPwaByEmpresa($empresaId, $organizacaoId);
    $pwaPublico = $pwaResponse->status ? ($pwaResponse->data[0] ?? null) : null;
    if (($pwaPublico->pwa_status ?? '') !== 'Ativo') {
      $pwaPublico = null;
    }

    $this->render('site/home/publica', [
      'empresaPublica' => $empresa,
      'enderecoPublico' => $enderecoPublico,
      'pwaPublico' => $pwaPublico
    ]);
  }

  private function sanitizarConteudoPublico(string $html): string
  {
    if (!class_exists(\DOMDocument::class)) {
      return nl2br(htmlspecialchars(trim(strip_tags($html)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    $documento = new \DOMDocument('1.0', 'UTF-8');
    $errosAnteriores = libxml_use_internal_errors(true);
    $documento->loadHTML('<?xml encoding="UTF-8"><div id="conteudo-publico">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($errosAnteriores);
    $raiz = $documento->getElementById('conteudo-publico');
    if (!$raiz) {
      return '';
    }

    $permitidas = ['section', 'div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'blockquote', 'br', 'hr', 'a', 'img'];
    $limpar = static function (\DOMNode $pai) use (&$limpar, $permitidas): void {
      foreach (iterator_to_array($pai->childNodes) as $no) {
        if (!$no instanceof \DOMElement) {
          continue;
        }
        $tag = strtolower($no->tagName);
        if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'svg', 'math'], true)) {
          $pai->removeChild($no);
          continue;
        }
        if (!in_array($tag, $permitidas, true)) {
          while ($no->firstChild) {
            $pai->insertBefore($no->firstChild, $no);
          }
          $pai->removeChild($no);
          continue;
        }
        $atributos = $tag === 'a' ? ['href', 'title', 'target', 'rel'] : ($tag === 'img' ? ['src', 'alt', 'title', 'width', 'height'] : []);
        foreach (iterator_to_array($no->attributes) as $atributo) {
          if (!in_array(strtolower($atributo->name), $atributos, true)) {
            $no->removeAttribute($atributo->name);
          }
        }
        $urlAtributo = $tag === 'a' ? 'href' : ($tag === 'img' ? 'src' : null);
        if ($urlAtributo && $no->hasAttribute($urlAtributo)) {
          $url = trim($no->getAttribute($urlAtributo));
          $esquema = strtolower((string) parse_url($url, PHP_URL_SCHEME));
          $esquemasPermitidos = $tag === 'a' ? ['', 'http', 'https', 'mailto', 'tel'] : ['', 'http', 'https'];
          if ($url === '' || str_starts_with($url, '//') || preg_match('/[\x00-\x20]/', $url) || !in_array($esquema, $esquemasPermitidos, true)) {
            $no->removeAttribute($urlAtributo);
          }
        }
        if ($tag === 'a' && strtolower($no->getAttribute('target')) === '_blank') {
          $no->setAttribute('rel', 'noopener noreferrer');
        } elseif ($tag === 'a' && $no->hasAttribute('target')) {
          $no->removeAttribute('target');
        }
        $limpar($no);
      }
    };
    $limpar($raiz);

    $conteudo = '';
    foreach ($raiz->childNodes as $no) {
      $conteudo .= $documento->saveHTML($no);
    }
    return $conteudo;
  }
}
