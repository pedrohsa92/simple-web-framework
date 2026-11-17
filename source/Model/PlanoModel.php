<?php

namespace Source\Model;

use Source\Core\SimpleModel;
use Source\Core\SimpleResponse;

class PlanoModel extends SimpleModel
{
  protected string $tabela = 'plano';
  protected string $prefixo = 'pla';
  protected string $identificador = 'id';

  protected string $escopoContexto = 'nenhum';
  protected string $campoOrganizacao = '';
  protected string $campoEmpresa = '';

  protected bool $usarLog = true;
  protected string $modoLog = 'detalhado';
  protected int $limiteLog = 50;

  public function getPlanos(): object
  {
    return $this
      ->orderBy('pla_ordem', 'ASC')
      ->orderBy('pla_id', 'ASC')
      ->get('Planos encontrados com sucesso||Erro ao buscar planos');
  }

  public function getPlanosPublicos(): object
  {
    return $this
      ->where('pla_status', 'Ativo')
      ->whereIn('pla_tipo', ['Teste', 'Comercial', 'Dedicado'])
      ->orderBy('pla_ordem', 'ASC')
      ->orderBy('pla_id', 'ASC')
      ->get('Planos públicos encontrados com sucesso||Erro ao buscar planos públicos');
  }

  public function getPlanoById(int $plaId): object
  {
    return $this
      ->where('pla_id', $plaId)
      ->first('Plano encontrado com sucesso||Erro ao buscar plano');
  }

  public function salvarPlano(array $data): object
  {
    $data = $this->filtrarCampos($data);
    $plaId = (int) ($data['pla_id'] ?? 0);

    if ($plaId <= 0) {
      return (new SimpleResponse('Atenção', 'Plano não informado'))
        ->warning()
        ->noDestroy();
    }

    $getPlano = $this->getPlanoById($plaId);

    if (!$getPlano->status || $getPlano->total !== 1) {
      return (new SimpleResponse('Plano não encontrado', 'O plano informado não foi localizado.'))
        ->warning()
        ->noDestroy();
    }

    $dados = [];
    $campos = [
      'pla_nome',
      'pla_descricaoresumida',
      'pla_descricao',
      'pla_rotulo',
      'pla_selo',
      'pla_beneficios',
      'pla_textobotao',
      'pla_status',
      'pla_ordem',
      'pla_tipo',
      'pla_dias_teste',
      'pla_valormensal',
      'pla_valoranual',
      'pla_valormensal_original',
      'pla_valoranual_original',
      'pla_contatocomercial'
    ];

    $campos = array_merge($campos, [
      'pla_config_usuario_status',
      'pla_config_usuario_limite',
      'pla_config_usuario_arquivo',
      'pla_config_usuario_contato',
      'pla_config_usuario_endereco',
      'pla_config_empresa_status',
      'pla_config_empresa_limite',
      'pla_config_empresa_arquivo',
      'pla_config_empresa_contato',
      'pla_config_empresa_endereco',
      'pla_config_nivelacesso_status',
      'pla_config_nivelacesso_limite',
      'pla_config_storage_limitemb'
    ]);

    foreach ($campos as $campo) {
      if (array_key_exists($campo, $data)) {
        $dados[$campo] = $data[$campo];
      }
    }

    if (array_key_exists('pla_nome', $dados)) {
      $dados['pla_nome'] = trim((string) $dados['pla_nome']);

      if ($dados['pla_nome'] === '') {
        return (new SimpleResponse('Atenção', 'O nome do plano é obrigatório.'))
          ->warning()
          ->noDestroy();
      }
    }

    if (array_key_exists('pla_tipo', $dados) && !in_array($dados['pla_tipo'], ['Teste', 'Comercial', 'Dedicado', 'Interno'], true)) {
      return (new SimpleResponse('Atenção', 'O tipo do plano é inválido.'))
        ->warning()
        ->noDestroy();
    }

    if (array_key_exists('pla_status', $dados)) {
      if (!in_array($dados['pla_status'], ['Ativo', 'Inativo'], true)) {
        return (new SimpleResponse('Atenção', 'O status do plano é inválido.'))
          ->warning()
          ->noDestroy();
      }

      $dados['pla_statusclass'] = $dados['pla_status'] === 'Ativo' ? 'success' : 'danger';
    }

    foreach (['pla_descricaoresumida', 'pla_descricao', 'pla_rotulo', 'pla_selo', 'pla_textobotao'] as $campo) {
      if (array_key_exists($campo, $dados)) {
        $dados[$campo] = trim((string) $dados[$campo]);
      }
    }

    if (array_key_exists('pla_selo', $dados) && $dados['pla_selo'] === '') {
      $dados['pla_selo'] = null;
    }

    foreach (['pla_valormensal', 'pla_valoranual', 'pla_valormensal_original', 'pla_valoranual_original'] as $campo) {
      if (array_key_exists($campo, $dados)) {
        $valor = trim((string) $dados[$campo]);
        $dados[$campo] = $valor === '' ? null : number_format(max(0, (float) $valor), 2, '.', '');
      }
    }

    if (array_key_exists('pla_dias_teste', $dados)) {
      $dados['pla_dias_teste'] = max(0, (int) $dados['pla_dias_teste']);
    }

    if (array_key_exists('pla_ordem', $dados)) {
      $dados['pla_ordem'] = max(0, (int) $dados['pla_ordem']);
    }

    if (array_key_exists('pla_beneficios', $dados)) {
      $beneficios = is_array($dados['pla_beneficios'])
        ? $dados['pla_beneficios']
        : json_decode((string) $dados['pla_beneficios'], true);

      $beneficios = is_array($beneficios) ? array_values(array_filter(array_map(
        static fn($beneficio) => trim((string) $beneficio),
        $beneficios
      ))) : [];

      if (count($beneficios) < 3 || count($beneficios) > 30 || count(array_filter($beneficios, static fn($beneficio) => mb_strlen($beneficio) > 80)) > 0) {
        return (new SimpleResponse('Atenção', 'Informe entre três e trinta benefícios curtos para o plano.'))
          ->warning()
          ->noDestroy();
      }

      $dados['pla_beneficios'] = json_encode($beneficios, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $configuracaoRecebida = false;
    foreach (array_keys($dados) as $campo) {
      if (strpos($campo, 'pla_config_') === 0) {
        $configuracaoRecebida = true;
        break;
      }
    }

    if ($configuracaoRecebida) {
      try {
        $dados['pla_parametro'] = $this->montarParametroEstruturado($dados);
      } catch (\InvalidArgumentException $e) {
        return (new SimpleResponse('Atenção', $e->getMessage()))
          ->warning()
          ->noDestroy();
      }

      foreach (array_keys($dados) as $campo) {
        if (strpos($campo, 'pla_config_') === 0) {
          unset($dados[$campo]);
        }
      }
    }

    if (array_key_exists('pla_contatocomercial', $dados)) {
      $contatos = is_array($dados['pla_contatocomercial'])
        ? $dados['pla_contatocomercial']
        : json_decode((string) $dados['pla_contatocomercial'], true);

      $contatos = is_array($contatos) ? array_values(array_map(static function ($contato) {
        $contato = is_array($contato) ? $contato : [];

        return [
          'nome' => trim((string) ($contato['nome'] ?? '')),
          'celular' => trim((string) ($contato['celular'] ?? '')),
          'email' => trim((string) ($contato['email'] ?? ''))
        ];
      }, $contatos)) : [];

      foreach ($contatos as $contato) {
        if ($contato['nome'] === '' || $contato['celular'] === '' || $contato['email'] === '' || !filter_var($contato['email'], FILTER_VALIDATE_EMAIL)) {
          return (new SimpleResponse('Atenção', 'Todos os contatos comerciais precisam informar nome, celular e um e-mail válido.'))
            ->warning()
            ->noDestroy();
        }
      }

      $dados['pla_contatocomercial'] = json_encode($contatos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $tipoPlano = $dados['pla_tipo'] ?? ($getPlano->data[0]->pla_tipo ?? 'Comercial');

    if ($tipoPlano !== 'Teste') {
      $dados['pla_dias_teste'] = null;
    }

    if ($tipoPlano !== 'Comercial') {
      foreach (['pla_selo', 'pla_valormensal', 'pla_valoranual', 'pla_valormensal_original', 'pla_valoranual_original'] as $campo) {
        $dados[$campo] = null;
      }
    }

    if ($tipoPlano !== 'Dedicado') {
      $dados['pla_contatocomercial'] = null;
    }

    unset($dados['pla_id'], $dados['pla_dtcad'], $dados['pla_dtalter'], $dados['pla_log']);

    if (empty($dados)) {
      return (new SimpleResponse('Atenção', 'Nenhuma alteração foi informada.'))
        ->warning()
        ->noDestroy();
    }

    $this->modoLog = 'detalhado';

    try {
      $this->beginTransaction();

      if (array_key_exists('pla_selo', $dados) && $dados['pla_selo'] !== null) {
        $limparSelos = $this->rawQuery(
          "UPDATE {$this->getTable()} SET pla_selo = NULL WHERE pla_id <> :pla_id",
          ['pla_id' => $plaId],
          false,
          'Selo dos demais planos atualizado||Erro ao atualizar selo dos demais planos'
        );

        if (!$limparSelos->status) {
          $this->rollback();
          return $limparSelos;
        }
      }

      $resultado = $this->update(
        $dados,
        ['pla_id' => $plaId],
        'Plano atualizado com sucesso||Erro ao atualizar plano'
      );

      if (!$resultado->status) {
        $this->rollback();
        return $resultado;
      }

      $this->commit();
      return $resultado;
    } catch (\Throwable $e) {
      if ($this->pdo->inTransaction()) {
        $this->rollback();
      }

      return (new SimpleResponse('Erro', 'Não foi possível atualizar o plano.'))
        ->erro()
        ->setArrayParams(['errorInfo' => $e->getMessage()])
        ->noDestroy();
    }
  }

  private function montarParametroEstruturado(array $dados): string
  {
    $limite = static function ($valor, string $campo): int {
      $valor = trim((string) ($valor ?? ''));

      if ($valor === '') {
        return 0;
      }

      if (!preg_match('/^\d+$/', $valor) || (float) $valor > 2147483647) {
        throw new \InvalidArgumentException("O limite de {$campo} deve ser um número inteiro igual ou maior que zero.");
      }

      return (int) $valor;
    };

    $status = static function ($valor): bool {
      return !empty($valor);
    };

    $parametro = [
      'usuario' => [
        'status' => $status($dados['pla_config_usuario_status'] ?? false),
        'limite' => $limite($dados['pla_config_usuario_limite'] ?? 0, 'usuários'),
        'arquivo' => ['limite' => $limite($dados['pla_config_usuario_arquivo'] ?? 0, 'arquivos de usuários')],
        'contato' => ['limite' => $limite($dados['pla_config_usuario_contato'] ?? 0, 'contatos de usuários')],
        'endereco' => ['limite' => $limite($dados['pla_config_usuario_endereco'] ?? 0, 'endereços de usuários')]
      ],
      'empresa' => [
        'status' => $status($dados['pla_config_empresa_status'] ?? false),
        'limite' => $limite($dados['pla_config_empresa_limite'] ?? 0, 'empresas'),
        'arquivo' => ['limite' => $limite($dados['pla_config_empresa_arquivo'] ?? 0, 'arquivos de empresas')],
        'contato' => ['limite' => $limite($dados['pla_config_empresa_contato'] ?? 0, 'contatos de empresas')],
        'endereco' => ['limite' => $limite($dados['pla_config_empresa_endereco'] ?? 0, 'endereços de empresas')]
      ],
      'nivelacesso' => [
        'status' => $status($dados['pla_config_nivelacesso_status'] ?? false),
        'limite' => $limite($dados['pla_config_nivelacesso_limite'] ?? 0, 'níveis de acesso')
      ],
      'storage' => [
        'limiteMb' => $limite($dados['pla_config_storage_limitemb'] ?? 0, 'armazenamento')
      ]
    ];

    $json = json_encode($parametro, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($json === false) {
      throw new \InvalidArgumentException('Não foi possível montar a configuração do plano.');
    }

    return $json;
  }
}
