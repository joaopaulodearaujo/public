<?php

class Debugger {

	public static $instancia;
	private $codigo;
	private $debug;
	private $contador;

	private function __construct() {
		$this -> codigo = array();
		$this -> debug = array();
		$this -> contador = 0;
	}

	public static function get_instancia () {

		$_classe = __CLASS__;
	
		self::$instancia = NULL;

		return ( ! (self::$instancia instanceof $_classe) ) ?
			new $_classe () :
			self::$instancia;

	}

	public function inserir_codigo($__linha, $__linha_bin, $__endereco) {

		$_endereco = str_pad (decbin($__endereco), 16, 0, STR_PAD_LEFT);

		$this -> codigo[$_endereco]['codigo'] = trim($__linha);
		$this -> codigo[$_endereco]['binario'] = $__linha_bin;
	}

	public function inserir_debug($__debug, $__endereco, $__clock) {

		$_endereco = str_pad (decbin($__endereco - 4), 16, 0, STR_PAD_LEFT);

		$this -> debug['PIPELINE'][$__clock[0]]['BUSCA'] = $this -> codigo[$_endereco]['codigo'];
		$this -> debug['PIPELINE'][$__clock[1]]['DECODIFICACAO'] = $this -> codigo[$_endereco]['codigo'];
		$this -> debug['PIPELINE'][$__clock[2]]['EXECUCAO'] = $this -> codigo[$_endereco]['codigo'];

		foreach ($__debug as $_campo => $_conteudo) {

			$this -> debug['EXECUCAO'][$this -> contador]['PC'] = $this -> converter($_endereco);
			$this -> debug['EXECUCAO'][$this -> contador]['CODIGO'] = $this -> codigo[$_endereco]['codigo'];
			$this -> debug['EXECUCAO'][$this -> contador]['BINARIO'] = $this -> codigo[$_endereco]['binario'];
			$this -> debug['EXECUCAO'][$this -> contador][$_campo] = $_conteudo;

		}

		$this -> contador++;

	}

	public function inserir_memoria_instrucoes($__memoria_instrucoes) {

		$this -> debug['MEMORIA_INSTRUCOES'] = $__memoria_instrucoes;

	}

	public function get_debug() {
		return $this -> debug;
	}

	public function get_codigo() {
		return $this -> codigo;
	}

	public function converter($__entrada) {
		return $__entrada.' ('.Utils::signed_bindec($__entrada).')';
	}
}