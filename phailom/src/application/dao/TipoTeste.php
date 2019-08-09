<?php

class TipoTeste extends PhailomAbstractDao {

	public function init() {

		$this -> _setupTableName('teste.tipo_teste');

	}

	public static function getValidation() {

		return array (

			'nome_teste' => PhailomValidation::ALPHA_3_255

		);

	}

}

?> 
