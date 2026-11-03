<?php
  $escopoClass = 'col-12 col-sm-4';

  $colunas = [
    ['label' => 'Nome', 'campo' => 'cdc_nome'],
    ['label' => 'Código', 'campo' => 'cdc_codigo']
  ];

  $campos = [
    [
      'type' => 'select',
      'name' => 'cdc_status',
      'label' => 'Status',
      'required' => true,
      'class' => 'col-12 col-sm-4',
      'options' => [
        (object) ['value' => 'Ativo', 'label' => 'Ativo'],
        (object) ['value' => 'Inativo', 'label' => 'Inativo']
      ]
    ],
    ['type' => 'text', 'name' => 'cdc_codigo', 'label' => 'Código', 'maxlength' => 30, 'class' => 'col-12 col-sm-4'],
    ['type' => 'text', 'name' => 'cdc_nome', 'label' => 'Nome', 'required' => true, 'maxlength' => 80, 'class' => 'col-12 col-sm-12'],
    ['type' => 'textarea', 'name' => 'cdc_descricao', 'label' => 'Descrição', 'maxlength' => 255, 'class' => 'col-12'],
    ['type' => 'checkbox', 'name' => 'cdc_padrao', 'label' => 'Definir como padrão', 'class' => 'col-12']
  ];

  $this->insert('sistema/financeiro/basico/_cadastro', $this->data + compact('colunas', 'campos'));
?>
