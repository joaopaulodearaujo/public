<?php

/**
  * Esta classe contem operacoes auxiliares utilizadas em
  * partes do simulador.
  * O metodo signed_bindec converte um numero binario para decimal
  *
  * @package class
  */
abstract class Utils {

	/**
	  * Este método converte um número binário para decimal,
  	  * sendo que o numero binario pode estar em complemento de 2.
	  * @param $__valor.
	  * @return $__valor(decimal).
	  */
	public static function signed_bindec ($__valor) {

		if ($__valor[0])  {

			for ($_i = 0; $_i < strlen($__valor); $_i++) {
				$__valor[$_i] = ($__valor[$_i]) ? 0 : 1;
			}

			return ( (bindec($__valor) + 1) * (-1) );

		}

		return bindec($__valor);
        }

	/**
	  * Este método inverte o sinal de um numero binario.
	  * @param $__valor.
	  * @return $_valor_decimal.
	  */
	public static function inverter_sinal ($__valor) {

		$_valor_decimal = self::signed_bindec($__valor);
		$_valor_decimal *= -1;
		$_retorno = decbin($_valor_decimal);
		return (strlen($_retorno) > 32) ? substr($_retorno, 32) : $_retorno;
	}

}

?>