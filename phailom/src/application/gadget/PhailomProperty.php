<?php

class PhailomProperty {

	private static $instance;
	private $properties;

	private function __construct() {

		self::$instance = $this;

		$this -> properties = new Zend_Config_Ini('configuration.ini', 'configuration');

	}

	public static function getInstance () {

		$_class = __CLASS__;

		return ( ! (self::$instance instanceof $_class) ) 
			? new $_class()
			: self::$instance;
	}

	public function config($__property) {

		return $this -> properties -> $__property;
	}

	public function messages() {

		$_session = new Zend_Session_Namespace($this -> config('session'));

		$_language = $_session -> language;

		unset($_session);

		$_directory = dirname(__FILE__) . '/..' . $this -> config('i18n_conf');
		$_file = $_directory . $_language . '.ini';

		if (! is_file($_file) ) {
			$_file = $_directory . $$this -> config('language').'.ini';
		}

		return new Zend_Config_Ini($_file, 'messages');
	}

} 
