<?php

/**
 * Classe que define o banco de registradores e suas operacoes
 *
 * @package class
 */
class Banco_Registradores {

	const NUMERO_REGISTRADORES = 16;

	private static $instancia;
	private $registradores;
	private $regwrite;

	/**
	  * Construtor privado da classe
	  *
	  */
	private function __construct () {

		$this -> registradores = array();

		for ($_i = 0; $_i < 16; $_i++) {

			$this -> registradores[str_pad(decbin($_i), 4, 0, STR_PAD_LEFT)] = str_pad("", 32, 0);

		}

	}
	

	/**
	  * Metodo estatico usado para pegar uma instancia
	  * da classe, caso a instancia nao exista, uma eh criada (singleton)
	  */
	public static function get_instancia () {

		$_classe = __CLASS__;

		return ( ! (self::$instancia instanceof $_classe) ) ?
			new $_classe () :
			self::$instancia;

	}


	/**
	  * Metodo que escreve $__dados em um registrador passado por 
	  * referencia. A escrita so eh realizada caso o sinal de escrita
	  * no banco de registradores estiver ativado
	  *
	  * @param $__dados
	  * @param &$__regdst
	  */
	public function escrever ($__dados, &$__regdst) {

		if ($this -> regwrite) {

			if (bindec($__regdst['id']) < self::NUMERO_REGISTRADORES) {

				$_dados = (strlen($__dados) > 32) ? substr($__dados, 32) : $__dados;

				$this -> registradores[$__regdst['id']] = $_dados;
				return $__regdst['valor'] = $_dados;
			}
		}

	}

	/**
	  * Metodo que le o registrador de id $__registrador do banco de registradores
	  * e retorna seu conteudo
	  *
	  * @param $__registrador
	  */
	public function ler ($__registrador) {

		return (array_key_exists($__registrador, $this -> registradores)) ?
			$this -> registradores[$__registrador] :
			str_pad("", 32, 0);

	}

	/**
	  * Metodo que configura o sinal de escrita no banco de registradores
	  *
	  * @param $__regwrite
	  */
	public function set_regwrite($__regwrite) {
		$this -> regwrite = $__regwrite;
	}

	/**
	  * Metodo que que retorna o banco de registradores. Apenas para fins de depuracao
	  */
	public function get_registradores() {

		return $this -> registradores;

	}

	/**
	  * Metodo que define o pc
	  */
	public function set_pc($__pc) {

		$this -> registradores[str_pad(decbin(15), 4, 0)] = $__pc;

	}

	/**
	  * Metodo que retorna o pc
	  */
	public function get_pc() {

		return $this -> registradores[str_pad(decbin(15), 4, 0)];

	}

	/**
	  * Metodo que define o link
	  */
	public function set_link($__pc) {

		$this -> registradores[str_pad(decbin(14), 4, 0)] = $__pc;

	}



}

?>