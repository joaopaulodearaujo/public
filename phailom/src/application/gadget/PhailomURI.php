<?php

abstract class PhailomURI {

	private function __construct() {}

	public static function getParameters($__uri, $__cut = null, $__convert = true) {

		$_i = strpos($__uri, $__cut);

		$_parametros = explode('/', substr($__uri, $_i + strlen($__cut)));

		$_words = array();

		if ($__convert) {

			foreach($_parametros as $_parametro) {

				$_explode = explode('-', $_parametro);

				$_word = $_explode[0];
				
				array_shift($_explode);

				$_explode = array_map('ucwords', $_explode);

				array_push($_words, $_word.implode($_explode));

			}

		} else {

			$_words = $_parametros;
		}

		array_shift($_words);

		if (end($_words) == '') {
			array_pop($_words);
		}

		return $_words;

	}

} 


?> 
