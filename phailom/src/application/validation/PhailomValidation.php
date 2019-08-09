<?php

/**
 * Classe que contem expressoes regulares que podem ser usadas na 
 * validacao dos objetos.
 *
 * @author João Paulo de Araújo <joaopaulo@syllom.com.br>
 * @package phailom_validation
 *
 * @abstract
 *
 */
abstract class PhailomValidation {

	const INTEGER = '/^[[:digit:]]+$/';
	const ALPHA_3_255 = '/^[a-zA-Z ]{3,255}$/';
	const ALNUM_3 = '/^[[:alnum:]]{3}$/';
	const BOOLEAN = '/^(true|TRUE|false|FALSE|0|1)$/';

}

?> 
