<?php

abstract class PhailomBody {
	
	public static function __callStatic ($__metodo, $__argumentos) {

		$_property = PhailomProperty::getInstance();

		$_sessao = new Zend_Session_Namespace($_property -> config('session'));

		$_contents = $_property -> config('contents');

		$__metodo = str_replace('_', '/', $__metodo);

		$_diretorio = dirname(__FILE__) . '/..' . $_contents . $__metodo . '/';

		$_arquivo = $_diretorio . $_sessao -> language . '.php';

		if (!is_file($_arquivo)) {
			$_arquivo = $_diretorio . $_property -> config . '.php';
		}

		$_body = null;

		if (is_file($_arquivo) && is_readable($_arquivo)) {

			ob_start();

				Zend_Loader::loadFile($_arquivo);
				$_body = ob_get_contents();

			ob_end_clean();
			
		}

		return $_body;
	}

}