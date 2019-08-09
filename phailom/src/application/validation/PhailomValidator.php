<?php

/**
 * Class que implementa méotodo que serão usados na validação de 
 * campos a partir de expressões regulares
 *
 * @author João Paulo de Araújo <joaopaulo@syllom.com.br>
 * @package phailom_validation
 *
 */
class PhailomValidator {

	/**
	 * Método estático para validar um conjunto de dados $__data, a partir de um conjunto de 
	 * expressões regulares $__validation.
	 * Para cada $_key de $__validation é procurado um campo com índice igual a $_key
	 * em $__data e é realizada a computaração caso exista essa condição
	 * Campos de $__data[$_key] com valor nulo ou que não foram passados podem ser desconsiderados
	 * caso $__ignoreNull esteja habilitado.
	 * Erros são acumulados em um array cotendo um prefixo concantena com a chave de validação
	 * onde houve falha.
	 *
	 * @access public
	 * @static
	 *
	 * @param array $__data dados a serem validados
	 * @param array $__validation array associativo contendo na chave o nome da validação e 
	 * em seu valor a expressão a ser executada
	 * @param boolean $__ignoreNull se verdadeiro, campos de posições de $__data não especificados
	 * ou com valor nulo são desconsiderados na validação
	 * @param string $__prefix prefixo a ser adicionado no início dos nomes de campos com erros de 
	 * validação
	 *
	 * @return array array contendo os erros da validação. Array vazio caso não haja erros.
	 *
	 */
	public static function validate ($__data, $__validation, $__ignoreNull, $__prefix = null) {

		$_errors = array();

		foreach ($__validation as $_key => $_regex) {

			if ( (!isset($__data[$_key]) || empty($__data[$_key])) && $__ignoreNull) {
				continue;
			}

			if (! preg_match($_regex, $__data[$_key])) {
				array_push($_errors, $__prefix.$_key);
			}
		}

		return $_errors;

	}

}

?> 
