<?php

/**
 * Classe que define a memoria de instrucoes e suas operacoes
 *
 * @package class
 */
class Memoria_Instrucoes {

	const TAMANHO_MEMORIA = 8192;

	private static $instancia;
	private $apontador;
	private $memoria;

	/**
	  * Construtor privado da classe
	  *
	  */
	private function __construct () {

		$this -> apontador = 0;
		$this -> memoria = array();

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
	  * Metodo que escreve $__instrucao na posicao $__endereco da memoria.
	  * Caso $__endereco seja NULL eh utilizado o apontador interno
          * da memoria
	  *
	  * @param $__instrucao
	  * @param $__endereco
	  */
	public function escrever ($__instrucao, $__endereco = NULL) {

		if ($__endereco) {
			$this -> apontador = $__endereco;
		}

		if ($this -> apontador >= self::TAMANHO_MEMORIA) {

			throw new MemoriaException(MemoriaExpcetion::POSICAO_INVALIDA_ESCRITA.' - '.$__endereco);

		}

		$_endereco = str_pad (decbin($this -> apontador), 32, 0, STR_PAD_LEFT);

		$this -> memoria[$_endereco] = $__instrucao;

		$this -> apontador += 4;

		return $this -> apontador - 4;

	}

	/**
	  * Metodo que le a posicao $__endereco da memoria e retorna seu conteudo
	  *
	  * @param $__endereco
	  */
	public function ler ($__endereco) {

		$_endereco = str_pad (decbin($__endereco), 32, 0, STR_PAD_LEFT);

		if (!array_key_exists($_endereco, $this -> memoria)) {

			throw new MemoriaException(MemoriaException::POSICAO_INEXISTENTE.' - '.$__endereco);

		}

		return $this -> memoria[$_endereco];

	}

	/**
	  * Metodo que que retorna a memoria de instrucoes. Apenas para fins de depuracao
	  */
	public function get_memoria() {

		return $this -> memoria;

	}

}

?>
