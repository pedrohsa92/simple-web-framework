<?php
  $escopoClass = 'col-12 col-sm-6';

  $colunas = [
    ['label' => 'Nome', 'campo' => 'fpg_nome'],
    ['label' => 'Aceita parcela', 'campo' => 'fpg_aceita_parcela', 'tipo' => 'booleano']
  ];

  $campos = [
    [
      'type' => 'select',
      'name' => 'fpg_status',
      'label' => 'Status',
      'required' => true,
      'class' => 'col-12 col-sm-6',
      'options' => [
        (object) ['value' => 'Ativo', 'label' => 'Ativo'],
        (object) ['value' => 'Inativo', 'label' => 'Inativo']
      ]
    ],
    ['type' => 'text', 'name' => 'fpg_nome', 'label' => 'Nome', 'required' => true, 'maxlength' => 80, 'class' => 'col-12 col-sm-12'],
    ['type' => 'textarea', 'name' => 'fpg_observacao', 'label' => 'Observação', 'maxlength' => 255, 'class' => 'col-12'],
    ['type' => 'checkbox', 'name' => 'fpg_aceita_parcela', 'label' => 'Aceita parcela', 'class' => 'col-12 col-sm-6'],
    ['type' => 'checkbox', 'name' => 'fpg_padrao', 'label' => 'Definir como padrão', 'class' => 'col-12 col-sm-6']
  ];

  $this->insert('sistema/financeiro/basico/_cadastro', $this->data + compact('colunas', 'campos'));
?>
