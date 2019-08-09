<?php

abstract class PhailomHead {
	
	public static function __callStatic ($__metodo, $__argumentos) {

		$_head = NULL;

		try {

			$_property = PhailomProperty::getInstance();

			$_sessao = new Zend_Session_Namespace($_property -> config('session'));

			$_head = new Zend_Config_Ini($_property -> config('contents_file'), $__metodo);
			$_head = $_head -> toArray();


		} catch (Exception $_e) {

			$_head = NULL;

		}

		return $_head;
	}

}