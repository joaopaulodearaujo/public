<?php

abstract class PhailomAbstractService {

	protected $dao;
	protected $metaData;

	protected function __construct() {
		$this -> metaData = array();
	}

	public function setDao($__dao) {
		$this -> dao = $__dao;
	}

	public function getMetaData($__method) {

		return $this -> metaData[$__method];

	}

	public function getValidation() {

		return $this -> dao -> getValidation();

	}

	// a ideia aqui eh a seguinte
	// o ultimo argumento de cada metodo especifica
	// qual dao utilizar na operação.
	// com isso eu ganho flexibilidade
	// (sem precisar de apelar para coisas que tive
	// que fazer em Java -> mais de um abstracService, para
	// poder manipular views). 
	// Por hora podemos deixar como está

	public function listar() {

		return $this -> dao -> findAll();

	}

	public function buscar(array $__dados) {

		return $this -> dao -> findById ($__dados);

	}

	public function inserir(array $__dados) {

		return $this -> dao -> insert($__dados);

	}

	public function remover(array $__dados) {

		return $this -> dao -> deleteById ($__dados);

	}

	public function testar (array $__dados) {

		return $__dados;

	}

}
