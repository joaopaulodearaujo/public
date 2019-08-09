<?php

class Teste extends PhailomAbstractDao {

	public function init() {

		$this -> _setupTableName('teste.teste');

	}

	public static function getValidation () {

		return array (

			'id_tipo_teste' => PhailomValidation::INTEGER,
			'campo_um' => PhailomValidation::ALPHA_3_255,
			'campo_dois' => PhailomValidation::ALPHA_3_255,
			'ativo' => PhailomValidation::BOOLEAN

		);

	}
} 

?>