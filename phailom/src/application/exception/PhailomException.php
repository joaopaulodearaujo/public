<?php

/**
 * Exceção mais geral que pode ser lançada pelo Phailom.
 * É superclasse de todas as outras exceções do Phailom.
 * Tem como chave padrão "PHAILOM_DEFAULT_EXCEPTION"
 *
 * @author João Paulo de Araújo <joaopaulo@syllom.com.br>
 * @package phailom_exception
 *
 */
class PhailomException extends Exception {

	/**
	 * Chave/String que identifica a exceção.
	 * Ela é usada para, no tratamento da exceção, 
	 * retornar a mensagem correta a view.
	 *
	 * @access private
	 *
	 */
	private $key;

	public function __construct ($__key = 'PHAILOM_DEFAULT_EXCEPTION') {

		$this -> key = $__key;

 		parent::__construct($__key);

	}

	/**
	 * Overload de __toString() da classe Exception
	 * Apenas faz com que a string retornada seja a 
	 * chave da exceção
	 *	
	 * @access public
	 * 
	 * @return string uma string com a chave da exceção
	 *
	 */
	public function __toString() {
		return $this -> key;
	}

}

?>