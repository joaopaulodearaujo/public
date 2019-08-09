<?php

/**
* Classe que define a unidade de controle e suas operacoes
*
* @package class
*/
class Controle {

	private static $instancia;
	private $sinais;
	private $cspr;
	private $instrucoes;
	private $condicoes;

	/**
	* Construtor privado da classe
	* Carrega o ini que define as instrucoes e inicializa
	* os sinais de controle
	*
	* @param $__instrucoes
	*
	*/
	private function __construct ($__instrucoes, $__condicoes) {

		$this -> instrucoes = parse_ini_file($__instrucoes, TRUE);
		$this -> condicoes = parse_ini_file($__condicoes, TRUE);

		$this -> reset_sinais();

	}

	/**
	* Metodo estatico usado para pegar uma instancia
	* da classe, caso a instancia nao exista, uma eh criada (singleton)
	*
	* @param $__instrucoes
	*/
	public static function get_instancia ($__instrucoes  = 'instrucoes.ini', $__condicoes = 'condicoes.ini') {

		$_classe = __CLASS__;

		return ( ! (self::$instancia instanceof $_classe) ) ?
			new $_classe ($__instrucoes, $__condicoes) :
			self::$instancia;

	}

	/**
	* Metodo magico do php para realizacao de reflection.
	* Eh usado para retonar algum sinal de control
	*
	*  @param $__metodo
	*  @param $__argumentos
	*
	*/
	public function __call ($__metodo, $__argumentos) {

		$_metodo = strtoupper($__metodo);

		return (! array_key_exists($_metodo, $this -> sinais) ) ?
			0 :
			$this -> sinais[$_metodo];

	}

	/**
	* Metodo que gera os sinais para uma instrucao a partir
	* de seu opcode
	*
	*  @param $__opcode
	*
	*/
	public function gerar_sinais($__opcode) {

		if (array_key_exists($__opcode, $this -> instrucoes)) {

			foreach ($this -> sinais as $_sinal => $_valor) {

				$this -> sinais[$_sinal] =
					(array_key_exists($_sinal, $this -> instrucoes[$__opcode])) ?
						$this -> instrucoes[$__opcode][$_sinal] :
						((is_null($this -> sinais[$_sinal])) 
							? 0
							: $this -> sinais[$_sinal]
						);

			}

			$this -> sinais['OPCODE'] = $__opcode;

		} else {

			throw new ControleException(ControleException::OPCODE_INEXISTENTE.' - '.$__opcode);

		}

	}

	/**
	* Metodo que insere um sinal na unidade de controle
	*
	*  @param $__sinal
	*  @param $__valor
	*
	*/
	public function set_sinal($__sinal, $__valor) {

		$_sinal = strtoupper($__sinal);

		if (array_key_exists($_sinal, $this -> sinais)) {

			$this -> sinais[$_sinal] = $__valor;

		} else {

			throw new ControleException(ControleException::SINAL_INEXISTENTE.' - '.$__sinal.' => '.$__valor);

		}

	}

	/**
	* Metodo que le o CSPR de acordo com uma dada condicao
	*
	*  @param $__condicao
	*
	*/
	public function get_cspr($__condicao) {

		$this -> cspr = array();

		$_indice_cspr = NULL;

		foreach ($this -> condicoes as $_indice => $_condicao) {

			if ($_condicao['valor'] == $__condicao) {

				$_indice_cspr = $_indice;

			}

		}

		if (! is_null($_indice_cspr)) {

			if (array_key_exists('cspr', $this -> condicoes[$_indice_cspr])) {

				foreach ($this -> condicoes[$_indice_cspr]['cspr'] as $_cspr) {

					array_push(
						$this -> cspr, 
						array(
							'N' => $_cspr[0], 
							'Z' => $_cspr[1], 
							'C' => $_cspr[2], 
							'V' => $_cspr[3]
						)
					);

				}

			} else {

				throw new ControleException(ControleException::CSPR_INEXISTENTE.' - '.$__condicao);

			}
		
		} else {

			throw new ControleException(ControleException::CONDICAO_INEXISTENTE.' - '.$__condicao);

		}

		return $this -> cspr;

	}

	/**
	* Metodo para retornar os sinais de uma instrucao. Apenas teste
	*
	*/	
	public function get_sinais() {

		return $this -> sinais;

	}

	/**
	* Metodo para limapr os sinais da unidade de controle. Utilizado em stalls
	*
	*/	
	public function reset_sinais() {

		$this -> sinais = array (
			'OPCODE' => NULL,
			'REGWRITE' => 0,
			'MEMREAD' => 0,
			'MEMWRITE' => 0,
			'MEM2REG' => 0,
			'ALUOP' => NULL,
			'BRANCH' => 0,
			'ALU2REG' => 0,
			'ALU2MEM' => 0,
			'LINK' => 0,
			'SETCOND' => 0
		);

	}

}

?>