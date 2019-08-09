<?php

class TipoTesteService extends PhailomAbstractService {

	public function __construct () {

		$this -> metaData = array (

			'listar' => array (
				'dao' => 'TipoTeste',
				'input_method' => NULL,
				'input_type' => NULL,
				'output_type' => 'application/json',
				'ignore_null' => TRUE
			),

			'buscar' => array (
				'dao' => 'TipoTeste',
				'input_method' => 'GET',
				'input_type' => 'RAW',
				'output_type' => 'application/json',
				'ignore_null' => FALSE
			),

			'inserir' => array (
				'dao' => 'TipoTeste',
				'input_method' => 'GET',
				'input_type' => 'JSON',

				'input_objects' => array (

					'tipo-teste' => array (
						'type' => 'JSON',
						'validation' => 'TipoTeste',
						'ignore_null' => FALSE
					)
				),

				'output_type' => 'application/json',
				'ignore_null' => FALSE
			),

			'remover' => array (
				'dao' => 'TipoTeste',
				'input_method' => 'GET',
				'input_type' => 'JSON',
				'output_type' => 'application/json',
				'ignore_null' => TRUE
			)
		);

	}

}

?>