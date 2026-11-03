<?php
  $planosConta = array_map(function ($plano) {
    return (object) [
      'value' => $plano->id,
      'label' => "{$plano->nome} (" . (['Organizacao' => 'Organização', 'Empresa' => 'Empresa'][$plano->escopo_financeiro] ?? $plano->escopo_financeiro) . ")"
    ];
  }, $opcoes['plano_conta'] ?? []);

  $colunas = [
    ['label' => 'Nome', 'campo' => 'pct_nome'],
    ['label' => 'Natureza', 'campo' => 'pct_natureza', 'valores' => ['Entrada' => 'Entrada', 'Saida' => 'Saída']],
    ['label' => 'Nível', 'campo' => 'pct_nivel'],
    ['label' => 'Código', 'campo' => 'pct_codigo']
  ];

  $campos = [
    [
      'type' => 'select',
      'name' => 'pct_status',
      'label' => 'Status',
      'required' => true,
      'options' => [
        (object) ['value' => 'Ativo', 'label' => 'Ativo'],
        (object) ['value' => 'Inativo', 'label' => 'Inativo']
      ]
    ],
    [
      'type' => 'select',
      'name' => 'pct_natureza',
      'label' => 'Natureza',
      'required' => true,
      'disabledOnUpdate' => true,
      'options' => [
        (object) ['value' => 'Entrada', 'label' => 'Entrada'],
        (object) ['value' => 'Saida', 'label' => 'Saída']
      ]
    ],
    [
      'type' => 'select',
      'name' => 'pct_nivel',
      'label' => 'Nível',
      'required' => true,
      'disabledOnUpdate' => true,
      'options' => [
        (object) ['value' => 'Grupo', 'label' => 'Grupo'],
        (object) ['value' => 'Item', 'label' => 'Item']
      ]
    ],
    [
      'type' => 'select',
      'name' => 'pct_idpai',
      'label' => 'Registro pai',
      'disabledOnUpdate' => true,
      'options' => $planosConta
    ],
    ['type' => 'text', 'name' => 'pct_nome', 'label' => 'Nome', 'required' => true, 'maxlength' => 80, 'class' => 'col-12 col-sm-12'],
    ['type' => 'text', 'name' => 'pct_codigo', 'label' => 'Código', 'maxlength' => 30],
    ['type' => 'textarea', 'name' => 'pct_descricao', 'label' => 'Descrição', 'maxlength' => 255, 'class' => 'col-12'],
    ['type' => 'checkbox', 'name' => 'pct_padrao', 'label' => 'Definir como padrão', 'class' => 'col-12']
  ];

  $this->insert('sistema/financeiro/basico/_cadastro', $this->data + compact('colunas', 'campos'));
?>
