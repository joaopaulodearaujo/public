<?php

class Datapath {

	private static $instancia;

	private $banco_registradores;
	private $memoria_instrucoes;
	private $memoria_dados;
	private $instrucao;
	private $executar;
	private $controle;
	private $debugger;
	private $pipeline;
	private $dados;
	private $clock;
	private $cspr;
	private $pc;

	private function __construct () {

		$this -> controle = Controle::get_instancia();
		$this -> banco_registradores = Banco_Registradores::get_instancia();
		$this -> memoria_dados = Memoria_Dados::get_instancia();
		$this -> memoria_instrucoes = Memoria_Instrucoes::get_instancia();
		$this -> ula = ULA::get_instancia();
		$this -> debugger = Debugger::get_instancia();

		$this -> clock = 0;
		$this -> pc = 0;
		$this -> pipeline = array(
			'REGWRITE' => NULL,
			'BRANCH' => NULL,
			'DESTINO' => NULL,
			'EXECUTAR' => NULL
		);
		$this -> cspr = array(
			'N' => '.', 
			'Z' => '.', 
			'C' => '.', 
			'V' => '.'
		);

	}

	public static function get_instancia () {

		$_classe = __CLASS__;
	
		self::$instancia = NULL;

		return ( ! (self::$instancia instanceof $_classe) ) ?
			new $_classe () :
			self::$instancia;

	}

	public function carregar ($__entrada) {

		$_quantidade_instrucoes = 0;

		foreach ($__entrada as $_linha => $_linha_bin) {

			$_endereco_instrucao = $this -> memoria_instrucoes -> escrever($_linha_bin);

			$this -> debugger -> inserir_codigo($_linha, $_linha_bin, $_endereco_instrucao);

			$_quantidade_instrucoes++;

		}

		$this -> debugger -> inserir_memoria_instrucoes($this -> memoria_instrucoes -> get_memoria());

		$_SESSION['memoria_instrucoes'] = serialize($this -> memoria_instrucoes);
		$_SESSION['memoria_dados'] = serialize($this -> memoria_dados);
		$_SESSION['banco_registradores'] = serialize($this -> banco_registradores);
		$_SESSION['debugger'] = serialize($this -> debugger);
		$_SESSION['pipeline'] = serialize($this -> pipeline);
		$_SESSION['cspr'] = serialize($this -> cspr);
		$_SESSION['clock'] = 0;

		return $_quantidade_instrucoes;

	}

	public function interpretar() {

		$this -> memoria_instrucoes = unserialize($_SESSION['memoria_instrucoes']);
		$this -> memoria_dados = unserialize($_SESSION['memoria_dados']);
		$this -> banco_registradores = unserialize($_SESSION['banco_registradores']);
		$this -> debugger = unserialize($_SESSION['debugger']);
		$this -> pipeline = unserialize($_SESSION['pipeline']);
		$this -> cspr = unserialize($_SESSION['cspr']);
		$this -> clock = $_SESSION['clock'];
		$this -> pc = bindec($this -> banco_registradores -> get_pc());

		$this -> buscar();
		$this -> decodificar();
		$this -> executar();
		$this -> manipular_memoria();
		$this -> manipular_banco();

		if($this -> pipeline['EXECUTAR']) {

			if ($this -> pipeline['BRANCH']) {

				$this -> clock++;

				$_clock = array($this -> clock, $this -> clock + 1, $this -> clock + 2);

			}

		}

		$_clock = array($this -> clock, $this -> clock + 1, $this -> clock + 2);

		$this -> debugger -> inserir_debug (
			array(
				'EXECUTAR' => ($this -> executar) ? 1 : 0,
				'CSPR' => $this -> cspr,
				'SAIDA_ULA' => $this -> debugger -> converter($this -> dados['saida_execucao']),
				'MEMORIA_DADOS' => $this -> memoria_dados -> get_memoria(),
				'BANCO_REGISTRADORES' => $this -> banco_registradores -> get_registradores()
			),
			$this -> pc,
			$_clock
		);

		$this -> pipeline = array(
			'REGWRITE' => $this -> controle -> regwrite(),
			'BRANCH' => $this -> controle -> branch(),
			'DESTINO' => $this -> dados['rd']['id'], 
			'EXECUTAR' => $this -> executar
		);

		if ($this -> controle -> branch()) {

			$this -> banco_registradores -> set_pc(
				str_pad(decbin($this -> dados['endereco_desvio']), 32, 0, STR_PAD_LEFT)
			);

		}

		$this -> clock++;

		$this -> banco_registradores -> set_pc($this -> banco_registradores -> get_pc());

		$_SESSION['memoria_instrucoes'] = serialize($this -> memoria_instrucoes);
		$_SESSION['memoria_dados'] = serialize($this -> memoria_dados);
		$_SESSION['banco_registradores'] = serialize($this -> banco_registradores);
		$_SESSION['debugger'] = serialize($this -> debugger);
		$_SESSION['pipeline'] = serialize($this -> pipeline);
		$_SESSION['cspr'] = serialize($this -> cspr);
		$_SESSION['clock'] = $this -> clock;

		return $this -> debugger -> get_debug();

	}

	private function buscar () {

		$this -> instrucao = $this -> memoria_instrucoes -> ler ($this -> pc);
		$this -> pc += 4;
		$this -> banco_registradores -> set_pc(str_pad(decbin($this -> pc), 32, 0, STR_PAD_LEFT));

	}

	private function decodificar () {

		$this -> controle -> reset_sinais();

		$_tipo = substr($this -> instrucao, 4, 2);

		$this -> dados['rn']['id'] = substr($this -> instrucao, 12, 4);
		$this -> dados['rd']['id'] = substr($this -> instrucao, 16, 4);
		$this -> dados['rm']['id'] = substr($this -> instrucao, 28, 4);
		$this -> dados['rs']['id'] = substr($this -> instrucao, 20, 4);

		$this -> dados['rn']['valor'] = $this -> banco_registradores -> ler ($this -> dados['rn']['id']);
		$this -> dados['rd']['valor'] = $this -> banco_registradores -> ler ($this -> dados['rd']['id']);
		$this -> dados['rm']['valor'] = $this -> banco_registradores -> ler ($this -> dados['rm']['id']);
		$this -> dados['rs']['valor'] = $this -> banco_registradores -> ler ($this -> dados['rs']['id']);

		$this -> dados['operando1']['id'] = NULL;
		$this -> dados['operando2']['id'] = NULL;

		$this -> dados['operando1']['valor'] = str_pad('', 32, 0, STR_PAD_LEFT);
		$this -> dados['operando2']['valor'] = str_pad('', 32, 0, STR_PAD_LEFT);

		$this -> controle -> set_sinal('branch', 0);

		switch ($_tipo) {

			case '00':

				$_opcode = '00-'.substr($this -> instrucao, 7, 4);

				$this -> controle -> set_sinal('setcond', ($this -> instrucao[11] == '1') ? 1 : 0);

				$this -> dados['operando1']['id'] = $this -> dados['rn']['id'];
				$this -> dados['operando2']['id'] = ($this -> instrucao[6] == '1') ?
					NULL :
					$this -> dados['rm']['valor'];

				$this -> dados['operando1']['valor'] = $this -> dados['rn']['valor'];
				$this -> dados['operando2']['valor'] = ($this -> instrucao[6] == '1') ?
					str_pad(substr($this -> instrucao, 20, 12), 32, $this -> instrucao[20], STR_PAD_LEFT) :
					$this -> dados['rm']['valor'];

			break;

			case '01':

				$_opcode = '01-----'.$this -> instrucao[11];

				$this -> controle -> set_sinal('setcond', 0);

				$this -> dados['operando1']['id'] = $this -> dados['rn']['valor'];
				$this -> dados['operando2']['id'] = ($this -> instrucao[6] == '0') ?
					NULL :
					$this -> dados['rm']['id'];

				$this -> dados['operando1']['valor'] = $this -> dados['rn']['valor'];
				$this -> dados['operando2']['valor'] = ($this -> instrucao[6] == '0') ?
					str_pad(substr($this -> instrucao, 20, 12), 32, $this -> instrucao[20], STR_PAD_LEFT) :
					$this -> dados['rm']['valor'];

			break;

			case '10':

				$_opcode = substr($this -> instrucao, 4, 4);

				$this -> controle -> set_sinal('link', ($this -> instrucao[7] == '1') ? 1 : 0);
				$this -> controle -> set_sinal('branch', 1);
				$this -> controle -> set_sinal('setcond', 0);

				$this -> dados['endereco_desvio'] = Utils::signed_bindec(substr($this -> instrucao, 8, 24));

			break;

			case '11':

				$_opcode = substr($this -> instrucao, 4, 7);

				$this -> controle -> set_sinal('setcond', ($this -> instrucao[11] == '1') ? 1 : 0);

				$this -> dados['rd']['id'] = substr($this -> instrucao, 12, 4);
				$this -> dados['rn']['id'] = substr($this -> instrucao, 16, 4);

				$this -> dados['rd']['valor'] = $this -> banco_registradores -> ler ($this -> dados['rd']['id']);
				$this -> dados['rn']['valor'] = $this -> banco_registradores -> ler ($this -> dados['rn']['id']);

				$this -> dados['operando1']['id'] = $this -> dados['rm']['id'];
				$this -> dados['operando2']['id'] = $this -> dados['rs']['id'];

				$this -> dados['operando1']['valor'] = $this -> dados['rm']['valor'];
				$this -> dados['operando2']['valor'] = $this -> dados['rs']['valor'];

			break;

			default:

		}

		$_cspr_set = $this -> controle -> get_cspr(substr($this -> instrucao, 0, 4));

		foreach($_cspr_set as $_cspr) {

			if (preg_match('/^('.implode('', $_cspr).'|\.\.\.\.)$/', implode('', $this -> cspr))) {

				$this -> executar = TRUE;

				if ($this -> controle -> link()) {

					$this -> banco_registradores -> set_link(
						str_pad(decbin($this -> pc), 32, 0, STR_PAD_LEFT)
					);

				}

				break;

			} else {

				$this -> executar = FALSE;

			}

		}

		if ($this -> executar) {

			$this -> controle -> gerar_sinais($_opcode);

			$this -> memoria_dados -> set_memread($this -> controle -> memread());
			$this -> memoria_dados -> set_memwrite($this -> controle -> memwrite());
			$this -> banco_registradores -> set_regwrite($this -> controle -> regwrite());
			$this -> ula -> set_aluop($this -> controle -> aluop());

		} else {

			$this -> controle -> reset_sinais();

		}

	}

	private function executar () {

		$this -> dados['saida_execucao'] = $this -> ula -> executar(
			$this -> dados['operando1']['valor'], 
			$this -> dados['operando2']['valor']
		);

		if ($this -> controle -> setcond()) {

			$this -> cspr['N'] = $this -> ula -> flag_neg();
			$this -> cspr['Z'] = $this -> ula -> flag_zero();
			$this -> cspr['C'] = $this -> ula -> flag_carry();
			$this -> cspr['V'] = $this -> ula -> flag_overflow();

		}

	}

	private function manipular_memoria () {

		$_endereco_escrita = $this -> dados['saida_execucao'];

		$this -> dados['entrada_memoria'] = ($this -> controle -> alu2mem()) ?
			$this -> dados['saida_execucao'] :
			$this -> dados['rd']['valor'];
	
		$this -> memoria_dados -> escrever ($this -> dados['entrada_memoria'] , $_endereco_escrita);

		$_endereco_leitura = $this -> dados['saida_execucao'];

		$this -> dados['saida_memoria'] = $this -> memoria_dados -> ler ($_endereco_leitura);

	}

	private function manipular_banco ()  {

		$_entrada_registrador = ($this -> controle -> mem2reg()) ?
			$this -> dados['saida_memoria'] :
			$this -> dados['saida_execucao'];

		$this -> banco_registradores -> escrever($_entrada_registrador, $this -> dados['rd']);

	}

	public function get_pc() {

		return bindec($this -> banco_registradores -> get_pc());

	}

}

?>
