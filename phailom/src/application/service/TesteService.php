<?php

class TesteService extends PhailomAbstractService {

	public function __construct () {

		// Por hora podemos omitir o 'type' nas
		// nos input_objects.
		// Também vale lembrar que campos "raw"
		// (passados diretamente na requisição)
		// só serão processados na presença de um
		// "accept_raw = TRUE", ok?

		$this -> metaData = array (

			'listar' => array (
				'dao' => 'Teste',
			),

			'buscar' => array (
				'dao' => 'Teste',
				'input_method' => 'GET',
				'accept_raw' => TRUE,
				'ignore_null' => TRUE
			),

			'inserir' => array (

				'dao' => 'Teste',
				'input_method' => 'GET',
				'input_objects' => array (

					'teste' => array (
						'type' => 'JSON',
						'validation' => 'Teste',
						'ignore_null' => FALSE
					)
				)
			),

			'remover' => array (
				'dao' => 'Teste',
				'input_method' => 'GET',
				'accept_raw' => TRUE,
				'ignore_null' => TRUE
			),

			'testar' => array (

				'dao' => 'Teste',
				'input_method' => 'GET',

				'input_objects' => array (

					'teste' => array (
						'type' => 'JSON',
						'validation' => 'Teste',
						'ignore_null' => TRUE
					),
// 
// 					'tipo-teste' => array (
// 						'type' => 'JSON',
// 						'validation' => 'TipoTeste',
// 						'ignore_null' => TRUE
// 					)
				),

				'accept_raw' => TRUE,
				'ignore_null' => TRUE,

// 				'output_type' => 'application/json'
			)
		);

	}

}

?>
