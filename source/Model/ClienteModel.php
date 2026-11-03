<?php

namespace Source\Model;

use Helpers;
use Source\Core\SimpleModel;
use Source\Core\SimpleSession;
use Source\Core\SimpleUpload;
use Source\Model\EnderecoModel;
use Source\Model\ContatoModel;

class ClienteModel extends SimpleModel
{
  protected $table = 'cliente';
  protected $prefix = 'cli';

  public function __construct()
  {
    parent::__construct($this->table, $this->prefix, 'id');
  }

	public function insertCliente(array $dataRecebido, $text="Cliente inserido com sucesso"): object
	{
	  $data = $this->filtrarCamposComPrefixo($dataRecebido);

	  $requiredFields = [$this->prefixedColumn('cnpjcpf'), $this->prefixedColumn('razaosocial')];
	  $validation = $this->validarCampoObrigatorio($data, $requiredFields);

	  if (!$validation->status) {
	    return $validation;
	  }

	  $response = $this->myInsert($data, $text);
	  
	  if ( $response->status ) $response->location = Helpers::url('cliente/form/'.$response->lastModifyId.$dataRecebido['location']);

	  return $response;
	}

	public function updateCliente(array $dataRecebido, string $text = "Cliente atualizado com sucesso||Erro ao atualizar cliente"): object
	{
	  $data = $this->filtrarCamposComPrefixo($dataRecebido);
	  $idColumn = $this->prefixedColumn('id');

	  if (empty($data[$idColumn])) {
	    return (object)[
	      'status' => false,
	      'errorInfo' => 'O ID do cliente é obrigatório para atualização.'
	    ];
	  }

		if ($data[$this->prefixedColumn('senha')]!='') {
			$data[$this->prefixedColumn('senha')] = password_hash($data[$this->prefixedColumn('senha')], PASSWORD_DEFAULT);
		} else {
			$data = $this->removerCampoDeArray($data, $this->prefixedColumn('senha'));
		}

		if ($data[$this->prefixedColumn('username')]=='') {
      $data = $this->removerCampoDeArray($data, $this->prefixedColumn('username'));
    }
		
	  $data = $this->removerCampoDeArray($data, $idColumn);

	  $requiredFields = [$this->prefixedColumn('cnpjcpf'), $this->prefixedColumn('razaosocial')];

	  $presentFields = array_intersect_key($data, array_flip($requiredFields));

	  if ( !empty($presentFields) ) {
	    $validation = $this->validarCampoObrigatorio($data, $requiredFields);
	    if (!$validation->status) {
	      return $validation;
	    }
	  }
		
		if ( isset($data[$this->prefixedColumn('logo')]) ) {
		  $upload = new SimpleUpload('cliente', (int) $dataRecebido[$idColumn]);
      $clienteAtual = $this->findById($dataRecebido[$this->prefixedColumn('id')])->data[0] ?? null;

      if ( $clienteAtual->{$this->prefixedColumn('logo')}!='' ) {
        $moverParaLixeira = $upload->moverParaLixeira( $clienteAtual->{$this->prefixedColumn('logo')} );
        if ( !$moverParaLixeira->status ) return $moverParaLixeira;
      }

      $salvar = $upload->salvarBase64( $data[$this->prefixedColumn('logo')], 'logo');
      if ( $salvar->status ) {
        $data[$this->prefixedColumn('logo')] = $salvar->arq_caminho ?? 'storage/global/logo.jpg';
      } else {
        return $salvar;
      }
		}

		if ( isset($data[$this->prefixedColumn('planodefundo')]) ) {
		  $upload = new SimpleUpload('cliente', (int) $dataRecebido[$idColumn]);
      $clienteAtual = $this->findById($dataRecebido[$this->prefixedColumn('id')])->data[0] ?? null;

      if ( $clienteAtual->{$this->prefixedColumn('planodefundo')}!='' ) {
        $moverParaLixeira = $upload->moverParaLixeira( $clienteAtual->{$this->prefixedColumn('planodefundo')} );
        if ( !$moverParaLixeira->status ) return $moverParaLixeira;
      }

      $salvar = $upload->salvarBase64( $data[$this->prefixedColumn('planodefundo')], 'planodefundo');
      if ( $salvar->status ) {
        $data[$this->prefixedColumn('planodefundo')] = $salvar->arq_caminho ?? 'storage/global/planodefundo.jpg';
      } else {
        return $salvar;
      }
		}

	  $where = [$idColumn => $dataRecebido[$idColumn]];

	  $response = $this->myUpdate($data, $where, $text);

	  if ( $response->status ) $response->location = Helpers::url('cliente/form/'.$response->lastModifyId.$dataRecebido['location']);
	  
	  return $response;
	}

	public function getClienteById($id): object {
		$getCliente = $this->findById($id);
		$enderecoModel = new EnderecoModel();
		$contatoModel = new ContatoModel();
		
		if ($getCliente->status && $getCliente->total > 0) {
			$getCliente->data[0]->endereco = $enderecoModel->getEnderecoById($id, "cliente", false);
			$getCliente->data[0]->endereco->end_idorigem = $id;
			$getCliente->data[0]->endereco->end_origem = 'cliente';

			$getCliente->data[0]->contato = $contatoModel->getContatoByIdOrigem($id, "cliente");
	    $getCliente->data[0]->contato->ctt_idorigem = $id;
	    $getCliente->data[0]->contato->ctt_origem = 'cliente';
		}
		
		return $getCliente;
	}
}
