<?php

/**
 * Exceção lançada em situações que envolvem problemas com
 * a camada de persistência de dados.
 * Por padrão lança PHAILOM_DAO_DEFAULT_EXCEPTION
 * mas outras exceções podem ser lançadas, dependendo do tipo
 * de erro que tenha ocorrido. Trata-se de uma caso específico 
 * de um PhailomException
 *
 * @author João Paulo de Araújo <joaopaulo@syllom.com.br>
 * @package phailom_exception
 *
 */

class PhailomDaoException extends PhailomException {

	/**
	 * Construtor da classe que apenas pega o valor de 
	 * $__key e envia ao contrutor da classe pai (PhailomException)
	 *
	 * @access public
	 *
	 * @param string $__key chave que identifica qual erro houve na 
	 * camada de persistência de dados
	 *
	 */
	public function __construct ($__key = 'PHAILOM_DAO_DEFAULT_EXCEPTION') {

		parent::__construct($__key);

	}

}

?>