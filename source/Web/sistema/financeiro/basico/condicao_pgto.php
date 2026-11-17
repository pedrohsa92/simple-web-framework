<?php
  $formasPagamento = array_map(function ($forma) {
    return (object) [
      'value' => $forma->id,
      'label' => "{$forma->nome} (" . (['Organizacao' => 'Organização', 'Empresa' => 'Empresa'][$forma->escopo_financeiro] ?? $forma->escopo_financeiro) . ")"
    ];
  }, $opcoes['forma_pagamento'] ?? []);

  $colunas = [
    ['label' => 'Nome', 'campo' => 'cpg_nome'],
    ['label' => 'Parcelas', 'campo' => 'cpg_qtdparcela'],
    ['label' => 'Intervalo', 'campo' => 'cpg_intervalodias']
  ];

  $campos = [
    [
      'type' => 'select',
      'name' => 'cpg_status',
      'label' => 'Status',
      'required' => true,
      'options' => [
        (object) ['value' => 'Ativo', 'label' => 'Ativo'],
        (object) ['value' => 'Inativo', 'label' => 'Inativo']
      ]
    ],
    [
      'type' => 'select',
      'name' => 'cpg_idformapagamento',
      'label' => 'Forma de pagamento',
      'required' => true,
      'options' => $formasPagamento
    ],
    ['type' => 'text', 'name' => 'cpg_nome', 'label' => 'Nome', 'required' => true, 'maxlength' => 80, 'class' => 'col-12 col-sm-6'],
    ['type' => 'number', 'name' => 'cpg_qtdparcela', 'label' => 'Qtd. parcela', 'default' => 1, 'min' => 1, 'class' => 'col-12 col-sm-3', 'inputClass' => 'text-end'],
    ['type' => 'number', 'name' => 'cpg_intervalodias', 'label' => 'Dias entre parcelas', 'default' => 30, 'min' => 0, 'class' => 'col-12 col-sm-3', 'inputClass' => 'text-end'],
    ['type' => 'textarea', 'name' => 'cpg_observacao', 'label' => 'Observação', 'maxlength' => 255, 'class' => 'col-12'],
    ['type' => 'checkbox', 'name' => 'cpg_padrao', 'label' => 'Definir como padrão', 'class' => 'col-12']
  ];

  $this->insert('sistema/financeiro/basico/_cadastro', $this->data + compact('colunas', 'campos'));
?>
