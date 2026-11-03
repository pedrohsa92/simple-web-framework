<?php
  $colunas = [
    ['label' => 'Nome', 'campo' => 'ctf_nome'],
    ['label' => 'Tipo', 'campo' => 'ctf_tipo', 'valores' => [
      'Caixa' => 'Caixa',
      'Conta corrente' => 'Conta corrente',
      'Conta poupanca' => 'Conta poupança',
      'Cartao' => 'Cartão',
      'Carteira digital' => 'Carteira digital',
      'Outra' => 'Outra'
    ]]
  ];

  $campos = [
    [
      'type' => 'select',
      'name' => 'ctf_status',
      'label' => 'Status',
      'required' => true,
      'options' => [
        (object) ['value' => 'Ativo', 'label' => 'Ativo'],
        (object) ['value' => 'Inativo', 'label' => 'Inativo']
      ]
    ],
    [
      'type' => 'select',
      'name' => 'ctf_tipo',
      'label' => 'Tipo',
      'required' => true,
      'options' => [
        (object) ['value' => 'Caixa', 'label' => 'Caixa'],
        (object) ['value' => 'Conta corrente', 'label' => 'Conta corrente'],
        (object) ['value' => 'Conta poupanca', 'label' => 'Conta poupança'],
        (object) ['value' => 'Cartao', 'label' => 'Cartão'],
        (object) ['value' => 'Carteira digital', 'label' => 'Carteira digital'],
        (object) ['value' => 'Outra', 'label' => 'Outra']
      ]
    ],
    ['type' => 'text', 'name' => 'ctf_nome', 'label' => 'Nome', 'required' => true, 'maxlength' => 80, 'class' => 'col-12 col-md-4'],
    ['type' => 'text', 'name' => 'ctf_saldo_inicial', 'label' => 'Saldo inicial', 'inputClass' => 'dinheiro text-end', 'disabledOnUpdate' => true],
    ['type' => 'date', 'name' => 'ctf_dt_saldo_inicial', 'label' => 'Data do saldo inicial', 'inputClass' => 'text-center', 'disabledOnUpdate' => true],
    ['type' => 'textarea', 'name' => 'ctf_observacao', 'label' => 'Observação', 'maxlength' => 255, 'class' => 'col-12'],
    ['type' => 'checkbox', 'name' => 'ctf_padrao', 'label' => 'Definir como padrão', 'class' => 'col-12']
  ];

  $this->insert('sistema/financeiro/basico/_cadastro', $this->data + compact('colunas', 'campos'));
?>
