<?php

class Interpretador {

	private $instrucoes;
	private $condicoes;
	private $rotulos;
	private $excecoes;
	private static $instancia;

	const TAMANHO_INSTRUCAO = 4;

	private function __construct ($__instrucoes, $__condicoes) {

		$this -> excecoes = array();
		$this -> rotulos = array();
		$this -> instrucoes = parse_ini_file($__instrucoes, TRUE);
		$this -> condicoes = parse_ini_file($__condicoes, TRUE);

	}
	
	public static function get_instancia ($__instrucoes = 'interpretador.ini', $__condicoes = 'condicoes.ini') {

		$_classe = __CLASS__;

		return ( ! (self::$instancia instanceof $_classe) ) ?
			new $_classe ($__instrucoes, $__condicoes) :
			self::$instancia;

	}

	private function get_registrador($__registrador, $__tamanho, $__i) {

		if (! preg_match('/^(R([0-9]|1[0-5])|PC)$/i', $__registrador) ) {

			$this -> adicionar_excecao(InterpretadorException::REGISTRADOR_INVALIDO, $__registrador, $__i);
			return str_pad (0, intval($__tamanho), 0);

		}

		$_registrador = ($__registrador == 'PC' || $__registrador == 'pc')
			? 15
			: intval( ltrim($__registrador, 'Rr') );

		if ( $_registrador < 0 || $_registrador > 15) {

			$this -> adicionar_excecao(InterpretadorException::REGISTRADOR_INVALIDO, $__registrador, $__i);
			return str_pad (0, intval($__tamanho), 0);

		}

		return str_pad ( decbin($_registrador), intval($__tamanho), 0, STR_PAD_LEFT );
	}

	private function validar_numero($__entrada) {
		return (preg_match('/^[-]?[[:digit:]]+$/', $__entrada)) ? TRUE : FALSE;
	}

	private function inserir_rotulo($__i, $__linha) {

		$_rotulo = rtrim(trim($__linha), ':');

		if (array_key_exists($_rotulo, $this -> rotulos)) {

			$this -> adicionar_excecao(InterpretadorException::ROTULO_EXISTENTE, $_rotulo, $__i);

			return FALSE;

		}

		$this -> rotulos[$_rotulo] = $__i * self::TAMANHO_INSTRUCAO;

	}

	private function adicionar_excecao($__excecao, $__linha, $__i) {

		array_push($this -> excecoes, '['.$__i.'] '.$__linha.' - '.$__excecao);

	}

	public function interpretar_linha ($__i, $__linha) {

		$_endereco_presente = preg_match('/\[[^\]]+\]/', $__linha, $_matches);

		$_campos = preg_replace('/[[:space:]]+/', ' ', $__linha);

		if ($_endereco_presente) {

			$_linha = preg_replace('/\[[^\]]+\]/', '',$__linha);
			$_campos = explode(' ', trim(preg_replace('/[[:space:]]*,[[:space:]]*/', ' ', $_linha)));
			array_push($_campos, $_matches[0]);

		} else {

			$_campos = explode(' ', trim(preg_replace('/[[:space:]]*,[[:space:]]*/', ' ', $__linha)));

		}

		$_numero_campos = sizeof($_campos);

		$_instrucoes = array_keys($this -> instrucoes);

		$_operacao = NULL;

		$_modificadores = FALSE;

		for ($_i = strlen($_campos[0]); $_i > 0; $_i--) {

			$_substr = strtoupper(substr($_campos[0], 0, $_i));

			if (in_array($_substr, $_instrucoes)) {

				$_operacao = $_substr;

				break;

			}

		}

		if (! array_key_exists($_operacao, $this -> instrucoes) ) {

			$this -> adicionar_excecao(InterpretadorException::INSTRUCAO_INEXISTENTE, $__linha, $__i);

			return FALSE;

		}

		$_set_condicao = $this -> instrucoes[$_operacao]['set_condicao'];

		$_operacoes = array();

		array_push($_operacoes, $_operacao);

		if ($_set_condicao) {

			array_push($_operacoes, $_operacao.'S');

		}

		foreach (array_keys($this -> condicoes) as $_condicao) {

			array_push($_operacoes, $_operacao.$_condicao);

			if ($_set_condicao) {

				array_push($_operacoes, $_operacao.$_condicao.'S');

			}

		}

		if (! in_array(strtoupper($_campos[0]), $_operacoes) ) {

			$this -> adicionar_excecao(InterpretadorException::INSTRUCAO_INEXISTENTE, $__linha, $__i);

			return FALSE;

		}

		$_operandos = $this -> instrucoes[$_operacao]['operandos'];

		if ($_operandos + 1 != $_numero_campos) {

			if ($_operandos + 2 == $_numero_campos && (! $this -> instrucoes[$_operacao]['expandir_operando'])) {

				$this -> adicionar_excecao(InterpretadorException::OPERADORES_INVALIDOS, $__linha, $__i);
	
				return FALSE;

			} elseif ($_operandos + 2 != $_numero_campos) {

				$this -> adicionar_excecao(InterpretadorException::OPERADORES_INVALIDOS, $__linha, $__i);
	
				return FALSE;

			}

		}

		$_diferenca_tamanho = strlen($_campos[0]) - $_i;
		$_set_condicao_on = (
				$_diferenca_tamanho == 1
				|| $_diferenca_tamanho == 3
				|| (
					isset($this -> instrucoes[$_operacao]['cspr'])
					&& $this -> instrucoes[$_operacao]['cspr'] == TRUE
				)
			) ? 1 : 0;
		$_condicao_presente = ($_diferenca_tamanho == 2 || $_diferenca_tamanho == 3) ? TRUE : FALSE;

		$_condicao = ($_condicao_presente && $_set_condicao_on) 
			? substr($_campos[0], -3, 2)
			: (($_condicao_presente && ! $_set_condicao_on) 
				? substr($_campos[0], -2) 
				: NULL
			);

		$_valor_condicao = ($_condicao_presente)
			? $this -> condicoes[$_condicao]['valor']
			: $this -> condicoes['AL']['valor'];

		$_opcode = (isset($this -> instrucoes[$_operacao]['opcode'])) 
			? $this -> instrucoes[$_operacao]['opcode'] 
			: NULL;

		$_tipo = $this -> instrucoes[$_operacao]['tipo'];

		$_saida = NULL;

		switch ($_tipo) {

			case 0: 

				#----------------------------------------------------
				# Tipo 0 - [31 cond 28|27 101 25|24 L |23 offset 0]
				#----------------------------------------------------

				$_link = (isset($this -> instrucoes[$_operacao]['link'])) 
					? $this -> instrucoes[$_operacao]['link'] 
					: 0;

				if ($this -> validar_numero($_campos[1])) {

					if ($_campos[1] < 0 || $_campos[1] > 16777215) {

						$this -> adicionar_excecao(InterpretadorException::ROTULO_LIMITE, $__linha, $__i);

						return FALSE;

					}

					$_rotulo = str_pad(decbin($_campos[1]), 24, 0, STR_PAD_LEFT);

				} else {

					if (! array_key_exists($_campos[1], $this -> rotulos)) {

						$this -> adicionar_excecao(InterpretadorException::ROTULO_INEXISTENTE, $__linha, $__i);

						return FALSE;

					}

					$_rotulo = str_pad(decbin($this -> rotulos[$_campos[1]]), 24, 0, STR_PAD_LEFT);

				}

				$_saida = $_valor_condicao.'101'.$_link.$_rotulo;

				break;

			case 1:

				#--------------------------------------------------------------------------------------------
				# Tipo 1 - [31 cond 28|27 00 26|25 I |24 OPCODE 21|20 S |19 Rn 16|15 Rd 12|11 Operando 2 0]
				#--------------------------------------------------------------------------------------------

				$_ultimo_campo = end($_campos);

				$_imediato = (substr($_ultimo_campo, 0, 1) == '#') ? 1 : 0;

				$_rd = $this -> get_registrador($_campos[1], 4, $__i);

				$_rn = ($this -> instrucoes[$_operacao]['operandos'] == 2)
					? $this -> get_registrador($_campos[1], 4, $__i)
					: $this -> get_registrador($_campos[2], 4, $__i);

				if ($_imediato) {

					$_valor_imediato = substr($_ultimo_campo, 1);

					# --------------------------------------------
					# Resolver a questao da rotacao dos imediatos
					# --------------------------------------------

					if ($this -> validar_numero($_valor_imediato) 
						&& $_valor_imediato >= -2048
						&& $_valor_imediato <= 2047
					) {

						$_operando2 = decbin($_valor_imediato);
						$_operando2 = (strlen($_operando2) > 32) 
							? substr($_operando2, 32)
							: $_operando2;

						$_operando2 = (strlen($_operando2) == 32)
							? substr($_operando2, 20)
							: str_pad($_operando2, 12, 0, STR_PAD_LEFT);

					} else {

						$_operando2 = str_pad(0, 12, 0);

						$this -> adicionar_excecao(InterpretadorException::OPERANDO2_INVALIDO, $__linha, $__i);

					}

				}

				if ($_numero_campos == $_operandos + 1) {

					if (! $_imediato) {

						$_operando2 = $this -> get_registrador($_ultimo_campo, 12, $__i);

					}

				} else {


				}

				$_saida = $_valor_condicao.'00'.$_imediato.$_opcode.$_set_condicao_on.$_rn.$_rd.$_operando2;

				break;

			case 2:

				#--------------------------------------------------------------------------------------------
				# Tipo 2 - [31 cond 28|27 000000 22|21 A |20 S |19 Rd 16|15 Rn 12|11 Rs 8|7 1001 4|3 Rm 0]
				#--------------------------------------------------------------------------------------------
				# MUDANCA: o tipo 2 agora passa a ser o seguinte. Na exibicao do interpretador aparece da forma
				#		   anterior. 
				#--------------------------------------------------------------------------------------------
				# Tipo 2 - [31 cond 28|27 110000 22|21 A |20 S |19 Rd 16|15 Rn 12|11 Rs 8|7 1001 4|3 Rm 0]
				#--------------------------------------------------------------------------------------------

				$_acumular = $this -> instrucoes[$_operacao]['acumular'] ? 1 : 0;
				$_rd = $this -> get_registrador($_campos[1], 4, $__i);
				$_rm = $this -> get_registrador($_campos[2], 4, $__i);
				$_rs = $this -> get_registrador($_campos[3], 4, $__i);

				$_rn = ($this -> instrucoes[$_operacao]['operandos'] == 4)
					? $this -> get_registrador($_campos[4], 4, $__i)
					: str_pad(0, 4, 0);

				$_saida = $_valor_condicao.'110000'.$_acumular.$_set_condicao_on.$_rd.$_rn.$_rs.'1001'.$_rm;

				break;

			case 3:

				#---------------------------------------------------------------------------------------------------
				# Tipo 3 - [31 cond 28|27 01 26|25 I |24 P |23 U |22 B |21 W |20 L |19 Rn 16|15 Rd 12|11 Offset 0]
				#---------------------------------------------------------------------------------------------------

				$_rd = $this -> get_registrador($_campos[1], 4, $__i);

				$_address = $_campos[2];

				if (! preg_match('/^\[R([0-9]|1[0-4])(,[[:space:]]*(([+-]?R([0-9]|1[0-4]))|#[+-]?[[:digit:]]+))?\]$/', $_address)) {

					$this -> adicionar_excecao(InterpretadorException::ADDRESS_INVALIDO, $__linha, $__i);

					return FALSE;

				}

				$_address = trim($_address, '[]');

				$_campos_address = explode(' ', trim(preg_replace('/[[:space:]]*,[[:space:]]*/', ' ', $_address)));

				$_rn = $this -> get_registrador($_campos_address[0], 4, $__i);

 				if (sizeof($_campos_address) > 2) {

					$this -> adicionar_excecao(InterpretadorException::ADDRESS_INVALIDO, $__linha, $__i);

					return FALSE;

				}

				$_load_store = $this -> instrucoes[$_operacao]['load_store'];

				$_rn = $this -> get_registrador($_campos_address[0], 4, $__i);

				if (sizeof($_campos_address) == 2) {

					$_ultimo_campo = end($_campos_address);

					$_imediato = (substr($_ultimo_campo, 0, 1) == '#') ? 0 : 1;

					if ($_imediato == 0) {

						$_valor_imediato = substr($_ultimo_campo, 1);

						if ($this -> validar_numero($_valor_imediato)) {

							if ($_valor_imediato < -2048 || $_valor_imediato > 2047) {

								$this -> adicionar_excecao(InterpretadorException::OFFSET_LIMITE, $__linha, $__i);

								return FALSE;

							}

							$_operando2 = str_pad(decbin($_valor_imediato), 12, 0, STR_PAD_LEFT);

							$_operando2 = (strlen($_operando2) > 32) 
								? substr($_operando2, 32)
								: $_operando2;

							$_operando2 = (strlen($_operando2) == 32)
								? substr($_operando2, 20)
								: str_pad($_operando2, 12, 0, STR_PAD_LEFT);

							$_adicao_subtracao = 1;

						} else {

							$_operando2 = str_pad(0, 12, 0);

							$this -> adicionar_excecao(InterpretadorException::OPERANDO2_INVALIDO, $__linha, $__i);

						}

					} else {

						$_adicao_subtracao = (substr($_campos_address[1], 0, 1) == '-') ? 0 : 1;

						$_rm = (substr($_campos_address[1], 0, 1) == 'R')
							? $this -> get_registrador($_campos_address[1], 4, $__i)
							: $this -> get_registrador(substr($_campos_address[1], 1), 4, $__i);

						$_operando2 = str_pad($_rm, 12, 0, STR_PAD_LEFT);
					}

				} else {

					$_operando2 = str_pad(0, 12, 0);
					$_adicao_subtracao = 1;
					$_imediato = 0;

				}

				$_saida = $_valor_condicao.'01'.$_imediato.'0'.$_adicao_subtracao.'00'.$_load_store.$_rn.$_rd.$_operando2;

				break;

			default:

				$this -> adicionar_excecao(InterpretadorException::TIPO_INEXISTENTE, $__linha, $__i);

		}

		return $_saida;

	}

	public function interpretar_entrada($__entrada) {

		if (! is_array($__entrada)) {

			throw new InterpretadorException(InterpretadorException::ENTRADA_INVALIDA);

		}

		$_binarios = array();

		$_i = 0;

		foreach($__entrada as $_indice => $_linha) {

			if (preg_match('/^;[[:space:]]*/', trim($_linha))) {

				unset($__entrada[$_indice]);

				$_i--;

			}

			if (preg_match('/^[[:alnum:]]+:[[:space:]]*$/', trim($_linha))) {

				$this -> inserir_rotulo($_i, $_linha);
				unset($__entrada[$_indice]);

				$_i--;

			}

			if (preg_match('/[A-Za-z0-9]+/', $_linha) ){
 
				$_i++;

			}

		}

		$_i = 0;

		foreach($__entrada as $_linha) {

			if (preg_match('/[A-Za-z0-9]+/', $_linha)) {

				$_binario = $this -> interpretar_linha($_i, $_linha);

				if ($_binario) {

					$_binarios[$_linha] = $_binario;

				}

				$_i++;

			}

		}

		if (sizeof($this -> excecoes)) {

			throw new InterpretadorException(implode(PHP_EOL, $this -> excecoes));

		}

		return $_binarios;

	}

}

?>
