<?php

namespace Source\Service;

use Source\Core\SimpleSession;
use Source\Model\EmpresaModel;
use Source\Model\FornecedorModel;
use Source\Model\UsuarioModel;

class LimitePlanoService
{
  private ?object $contexto;

  public function __construct(?object $contexto = null)
  {
    $this->contexto = $contexto ?? (new SimpleSession())->get('CONTEXTO');
  }

  public function verificar(string $recurso, ?int $referenciaId = null): object
  {
    $configuracao = $this->configuracaoRecurso($recurso);
    $titulo = $this->tituloRecurso($recurso);

    if (!$configuracao->status) {
      return (object) [
        'status' => false,
        'permitido' => true,
        'atingiu_limite' => false,
        'motivo' => 'configuracao',
        'recurso' => $recurso,
        'recurso_titulo' => $titulo,
        'limite' => 0,
        'total' => 0,
        'plano' => $this->nomePlano(),
        'text' => $configuracao->text
      ];
    }

    if (!$configuracao->disponivel) {
      return (object) [
        'status' => true,
        'permitido' => false,
        'atingiu_limite' => false,
        'motivo' => 'indisponivel',
        'recurso' => $recurso,
        'recurso_titulo' => $titulo,
        'limite' => 0,
        'total' => 0,
        'plano' => $this->nomePlano(),
        'text' => "O recurso {$titulo} não está disponível no plano atual."
      ];
    }

    $contagem = $this->contarRecurso($recurso, $referenciaId);

    if (!$contagem->status) {
      return (object) [
        'status' => false,
        'permitido' => true,
        'atingiu_limite' => false,
        'motivo' => 'contagem',
        'recurso' => $recurso,
        'recurso_titulo' => $titulo,
        'limite' => $configuracao->limite,
        'total' => 0,
        'plano' => $this->nomePlano(),
        'text' => $contagem->text ?? "Não foi possível consultar o limite de {$titulo}."
      ];
    }

    $total = (int) ($contagem->data[0]->total ?? 0);
    $limite = (int) $configuracao->limite;
    $atingiuLimite = $limite > 0 && $total >= $limite;

    return (object) [
      'status' => true,
      'permitido' => !$atingiuLimite,
      'atingiu_limite' => $atingiuLimite,
      'motivo' => $atingiuLimite ? 'limite' : 'liberado',
      'recurso' => $recurso,
      'recurso_titulo' => $titulo,
      'limite' => $limite,
      'total' => $total,
      'plano' => $this->nomePlano(),
      'text' => $atingiuLimite
        ? "O limite de {$titulo} permitido pelo plano {$this->nomePlano()} foi atingido."
        : ''
    ];
  }

  public function verificarTitulo(string $vencimento = '', int $tituloId = 0): object
  {
    // Os planos atuais do ICLIV ainda não configuram limites de títulos.
    return $this->verificar('titulo', $tituloId > 0 ? $tituloId : null);
  }

  private function configuracaoRecurso(string $recurso): object
  {
    if (!is_object($this->contexto) || empty($this->contexto->organizacao->org_id)) {
      return (object) [
        'status' => false,
        'disponivel' => true,
        'limite' => 0,
        'text' => 'Contexto de organização não identificado.'
      ];
    }

    $parametro = $this->contexto->plano->plc_parametro ?? null;
    $config = $this->obterConfiguracaoPorCaminho($parametro, $recurso);

    if (!is_object($config) && !is_array($config)) {
      return (object) [
        'status' => false,
        'disponivel' => true,
        'limite' => 0,
        'text' => "A configuração do recurso {$this->tituloRecurso($recurso)} não foi encontrada."
      ];
    }

    $getValor = static function ($origem, string $chave, $padrao = null) {
      if (is_object($origem)) {
        return $origem->{$chave} ?? $padrao;
      }

      return is_array($origem) ? ($origem[$chave] ?? $padrao) : $padrao;
    };

    return (object) [
      'status' => true,
      'disponivel' => $getValor($config, 'status', true) !== false,
      'limite' => max(0, (int) $getValor($config, 'limite', 0)),
      'configuracao' => $config,
      'text' => ''
    ];
  }

  private function contarRecurso(string $recurso, ?int $referenciaId = null): object
  {
    return match ($recurso) {
      'usuario' => (new UsuarioModel())->contarUsuariosContexto(true),
      'empresa' => (new EmpresaModel())->contarEmpresasContexto(true),
      'fornecedor' => (new FornecedorModel())->contarFornecedoresContexto(true),
      default => (object) [
        'status' => false,
        'text' => "O recurso {$recurso} ainda não possui uma contagem configurada."
      ]
    };
  }

  private function tituloRecurso(string $recurso): string
  {
    return match ($recurso) {
      'usuario' => 'usuários',
      'empresa' => 'empresas',
      'fornecedor' => 'fornecedores',
      default => $recurso
    };
  }

  private function obterConfiguracaoPorCaminho($parametro, string $recurso)
  {
    $valor = $parametro;

    foreach (explode('.', $recurso) as $chave) {
      if (is_object($valor)) {
        $valor = $valor->{$chave} ?? null;
      } elseif (is_array($valor)) {
        $valor = $valor[$chave] ?? null;
      } else {
        return null;
      }
    }

    return $valor;
  }

  private function nomePlano(): string
  {
    return (string) ($this->contexto->plano->pla_nome ?? 'plano atual');
  }
}
