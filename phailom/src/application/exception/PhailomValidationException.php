<?php

/**
 * Exceção lançada quando há erro na validação dos dados de entrada
 * do service. Trata-se de uma caso específico de um PhailomException
 *
 * @author João Paulo de Araújo <joaopaulo@syllom.com.br>
 * @package phailom_exception
 *
 */
class PhailomValidationException extends PhailomException {

	/**
	 * Armazena o vetor que contém os campos com erros de validação
	 *
	 * @access private
	 *
	 */
	private $errors;

	/**
	 * Construtor da classe. Pega os (array) $__errors passados
	 * e os atribui a $this -> errors;
	 * Uma chave "PHAILOM_VALIDATION_EXCEPTION" é passada para
	 * o construtor da classe pai (PhailomException)
	 * avisando que se trata de uma exceção de validação de dados.
	 *
	 * @access public
	 *
	 * @return array um vetor contento os campos com erros de validação.
	 *
	 */
	public function __construct ($__errors) {

 		parent::__construct('PHAILOM_VALIDATION_EXCEPTION');

		$this -> errors = $__errors;

	}

	/**
	 * Método que retorna o vetor que contém os campos com erros.
	 * Pode ser usado, por exemplo, para identificar quais campos 
	 * tiveram erros e pegar as mensagens de cada um deles
	 * corretamente
	 *
	 * @access public
	 *
	 * @return array um vetor contento os campos com erros de validação.
	 *
	 */
	public function getErrors() {
		return $this -> errors;
	}

}

?>