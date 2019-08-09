<?php

/**
 * Classe que define a memoria de dados e suas operacoes
 *
 * @package class
 */
class Memoria_Dados {

	const TAMANHO_MEMORIA = 8192;

	private static $instancia;

	private $memoria;
	private $memread;
	private $memwrite;

	/**
	  * Construtor privado da classe
	  *
	  */
	private function __construct () {

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
	  * Metodo que escreve $__dados na posicao $__posicao da memoria.
	  * A escrita so eh realizada caso o sinal de escrita
	  * na memoria estiver ativado
	  *
	  * @param $__dados
	  * @param $__posicao
	  */
	public function escrever ($__dados, $__posicao) {

		if ($this -> memwrite) {

			if (bindec($__posicao) >= self::TAMANHO_MEMORIA) {

				throw new MemoriaException(MemoriaExpcetion::POSICAO_INVALIDA_ESCRITA.' - '.$__posicao);

			}

			$this -> memoria[$__posicao] = $__dados;

		}

	}

	/**
	  * Metodo que, caso a leitura em memoria esteja ativa
	  * le a posicao $__posicao da memoria e retorna seu conteudo
	  *
	  * @param $__posicao
	  */
	public function ler ($__posicao) {

		if ($this -> memread) {

			if (!array_key_exists($__posicao, $this -> memoria)) {

				throw new MemoriaException(MemoriaException::POSICAO_INVALIDA_LEITURA.' - '.$__posicao);

			}

			return $this -> memoria[$__posicao];

		}
	}

	/**
	  * Metodo que configura o sinal de leitura na memoria
	  *
	  * @param $__memread
	  */
	public function set_memread($__memread) {
		$this -> memread = $__memread;
	}

	/**
	  * Metodo que configura o sinal de escrita na memoria
	  *
	  * @param $__memwrite
	  */
	public function set_memwrite($__memwrite) {
		$this -> memwrite = $__memwrite;
	}

	/**
	  * Metodo que que retorna a memoria de instrucoes. Apenas para fins de depuracao
	  */
	public function get_memoria() {

		return $this -> memoria;

	}

}

?>
