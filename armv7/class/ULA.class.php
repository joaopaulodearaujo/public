<?php

/**
  * Esta classe e' uma abstracao da Unidade Logica Aritmetica.
  * Cada metodo privado desta classe representa uma operacao suportada pela ULA.
  * O metodo executar e' responsavel por identificar e executar a operacao requisitada.
  * O codigo de operacao deve ser passado atravez do metodo set_alu_op.
  *
  * @package class
  */
class ULA{

	private static $instancia;

	private $alu_op;
	private $saida;
	private $zero;
	private $flag_zero;
	private $flag_overflow;
	private $flag_carry;
	private $flag_neg;

	/**
	  * Construtor da classe.
	  */
	private function __construct () {
		$this -> zero = "00000000000000000000000000000000";
	}

	/**
	  * Metodo magico do php para fazer uso de reflection.
	  */
	public function __call ($__metodo, $__argumentos) {

		return (property_exists(__CLASS__, $__metodo)) ?
			$this -> $__metodo :
			0;
	}

	/**
	  * Este metodo pega a instancia da classe ULA e garante
	  * que a mesma tenha uma instuncia unica.
	  */
	public static function get_instancia () {

		$_classe = __CLASS__;

		return ( ! (self::$instancia instanceof $_classe) ) ?
			new $_classe () :
			self::$instancia;

	}

	/**
	  * Metodo responsavel pela execucao das funcoes da ULA.
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  * @return $this -> saida Resultado da operacao realizada
	  */	
	public function executar ($__entrada1, $__entrada2) {

		$this -> flag_zero = NULL;
		$this -> flag_overflow = NULL;
		$this -> flag_carry = NULL;
		$this -> flag_neg = NULL;
		
		if ($this -> alu_op == NULL) {
			return '';
		}


		if (! method_exists($this, $this -> alu_op) ) {
			throw new ULAException (ULAException::OPERACAO_INVALIDA);
		}

		$this -> {$this -> alu_op} ($__entrada1, $__entrada2);

		return $this -> saida;

	}

	/**
	  * Metodo responsavel por setar alu_op.
	  *
	  * @param $__alu_op.
	  */
	public function set_aluop ($__alu_op) {

		$this -> alu_op = $__alu_op;

	}

	/**
	  * Metodo responsavel por carregar a constante mais significativa.
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  */
	private function carregar_constante_mais_significativos ($__entrada1, $__entrada2) {

		$this -> saida = str_pad($__entrada1, 32, 0, STR_PAD_RIGHT);

	}
	
	/**
	  * Metodo responsavel por carregar a constante menos significativa.
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  */
	private function carregar_constante_menos_significativos ($__entrada1, $__entrada2) {

		$this -> saida = str_pad($__entrada1, 32, 0, STR_PAD_LEFT);

	}





	/**
	  * Este metodo realiza a operacao zero(zero)
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  */
	private function zero($__entrada1, $__entrada2){

		$this -> flag_overflow = 0;
		$this -> flag_carry = 0;
		$this -> flag_neg = 0;
		$this -> flag_zero = 1;

		$this -> saida = $this -> zero;
	}

	/**
	  * Metodo responsavel pela execucao da instrucao de deslocamento aritmetico a direita(asr).
	  *
	  * @param $__valor Primeiro operando fonte
	  * @param $__deslocamento Segundo operando fonte
	  */
	private function deslocamento_aritmetico_direita ($__valor, $__deslocamento) {

		$_replica = $__valor[0];
		
		$_saida = str_pad(decbin(Utils::signed_bindec($__valor) >> 
			Utils::signed_bindec($__deslocamento)), 32, $_replica , STR_PAD_LEFT);

		$this -> flag_overflow = 0;
		$this -> flag_carry = 0;
		$this -> flag_neg = ($_saida[0] == "0") ? 0 : 1;
		$this -> flag_zero = ($_saida == $this -> zero) ? 1 : 0;

		$this -> saida = $_saida;

	}

	/**
	  * Metodo responsavel pela execucao da instrucao de deslocamento aritmetico a esquerda(asl).
	  *
	  * @param $__entrada1
	  * @param $__entrada2.
	  */
	private function deslocamento_aritmetico_esquerda ($__valor, $__deslocamento) {

		$_replica = $__valor[0];
		
		$_saida = str_pad(decbin(Utils::signed_bindec($__valor) << 
			Utils::signed_bindec($__deslocamento)), 32, $_replica , STR_PAD_LEFT);

		$this -> flag_overflow = 0;
		$this -> flag_carry = 0;
		$this -> flag_neg = ($_saida[0] == "0") ? 0 : 1;
		$this -> flag_zero = ($_saida == $this -> zero) ? 1 : 0;

		$this -> saida = $_saida;

	}

	/**
	  * Metodo responsavel pela execucao da instrucao de deslocamento logico a direita(lsr).
	  *
	  * @param $__valor Primeiro operando fonte
	  * @param $__deslocamento Segundo operando fonte
	  */
	private function deslocamento_logico_direita ($__valor, $__deslocamento) {

		$_zero = Utils::signed_bindec($__valor) >> Utils::signed_bindec($__deslocamento);

		$_zero = substr(decbin($_zero), -16);	
		
		$_saida = str_pad($_zero, 32, 0 , STR_PAD_LEFT);

		$this -> flag_overflow = 0;
		$this -> flag_carry = 0;
		$this -> flag_neg = ($_saida[0] == "0") ? 0 : 1;
		$this -> flag_zero = ($_saida == $this -> zero) ? 1 : 0;

 		$this -> saida = $_saida;

	}

	/**
	  * Metodo responsavel pela execucao da instrucao de deslocamento logico a esquerda(lsl).
	  *
	  * @param $__valor Primeiro operando fonte
	  * @param $__deslocamento Segundo operando fonte
	  */
	private function deslocamento_logico_esquerda ($__valor, $__deslocamento) {

		$_zero = Utils::signed_bindec($__valor) << Utils::signed_bindec($__deslocamento);

		$_zero = substr(decbin($_zero), -16);

		$_saida = str_pad($_zero, 32, 0 , STR_PAD_LEFT);

		$this -> flag_overflow = 0;
		$this -> flag_carry = 0;
		$this -> flag_neg = ($_saida[0] == "0") ? 0 : 1;
		$this -> flag_zero = ($_saida == $this -> zero) ? 1 : 0;
		
 		$this -> saida = $_saida;

	}
	
	/**********************************************************************************
	 * Tipo 1
	 **********************************************************************************/
	
	/**
	  * Metodo responsavel pela execucao da instrucao e(AND).
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  */
	private function e ($__entrada1, $__entrada2) {

		$_saida = $this -> zero;

		for ($_i = 31; $_i >= 0 ; $_i--) {
                    $_saida[$_i] = ($__entrada1[$_i] && $__entrada2[$_i]) ? 1 : 0;
		}

		$this -> flag_overflow = 0;
		$this -> flag_carry = 0;
		$this -> flag_neg = ($_saida[0] == "0") ? 0 : 1;
		$this -> flag_zero = ($_saida == $this -> zero) ? 1 : 0;

		$this -> saida = $_saida;
		
		return $_saida;
	}
	
	/**
	  * Este metodo realiza a operaca ou exclusivo(EOR)
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  */
	private function ou_exclusivo ($__entrada1, $__entrada2) {

		$_saida = $this -> zero;

		for ($_i = strlen($__entrada1) - 1; $_i > -1 ; $_i--) {
                    $_saida[$_i] = ($__entrada1[$_i] && !$__entrada2[$_i]) ||
			(!$__entrada1[$_i] && $__entrada2[$_i])? 1 : 0;
		}

		$this -> flag_overflow = 0;
		$this -> flag_carry = 0;
		$this -> flag_neg = ($_saida[0] == "0") ? 0 : 1;
		$this -> flag_zero = ($_saida == $this -> zero) ? 1 : 0;

		$this -> saida = $_saida;
		
		return $_saida;
	}
	
	/**
	  * Este metodo realiza a operaca de subtracao(SUB)
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  */
	private function subtracao ($__entrada1, $__entrada2) {

		$__entrada2 = Utils::inverter_sinal($__entrada2);
		$__entrada2 = str_pad($__entrada2, 32, 0, STR_PAD_LEFT);

		$_saida = str_pad ("", strlen ($__entrada1), 0);
		$_vai_um = 0;

                for($_i = strlen($__entrada1) - 1; $_i > -1; $_i--){

                        $_xor_temp = ($__entrada1[$_i] && !$__entrada2[$_i]) ||
				(!$__entrada1[$_i] && $__entrada2[$_i]) ? 1 : 0;

                        $_saida_temp = ($_xor_temp && !$_vai_um) ||
				(!$_xor_temp && $_vai_um) ? 1 : 0;
			
			$_vai_um = ($__entrada1[$_i] && $__entrada2[$_i]) ||
				($__entrada1[$_i] && $_vai_um) ||
				($__entrada2[$_i] && $_vai_um) ? 1 : 0;


			$_saida[$_i] = $_saida_temp;
		}

		$this -> flag_zero = ($_saida == $this -> zero) ? 1 : 0;

		$this -> flag_overflow = ( ($__entrada1[0] == 0 && $__entrada2[0] == 0 && $_saida[0] == 1) ||
			($__entrada1[1] == 1 && $__entrada2[1] == 1 && $_saida[0] == 0) ) ?
			1 : 0;

		$this -> flag_carry = $_vai_um;
		$this -> flag_neg = ($_saida[0] == "0") ? 0 : 1;

		$this -> saida = $_saida;

		return $_saida;
		
	}
	
	/**
	  * Este metodo realiza a operaca de subtracao invertida(RSB)
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  */
	private function subtracao_invertida ($__entrada1, $__entrada2){
		
		$this -> saida = $this -> subtracao($__entrada2, $__entrada1);
		
		return $this -> saida;
	}
	
	/**
	  * Metodo responsavel pela execucao da instrucao soma(ADD).
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  */
	private function soma ($__entrada1, $__entrada2) {

		$_saida = str_pad ("", strlen ($__entrada1), 0);
		$_vai_um = 0;

                for($_i = strlen($__entrada1) - 1; $_i > -1; $_i--){

                        $_xor_temp = ($__entrada1[$_i] && !$__entrada2[$_i]) ||
				(!$__entrada1[$_i] && $__entrada2[$_i]) ? 1 : 0;

                        $_saida_temp = ($_xor_temp && !$_vai_um) ||
				(!$_xor_temp && $_vai_um) ? 1 : 0;
			
			$_vai_um = ($__entrada1[$_i] && $__entrada2[$_i]) ||
				($__entrada1[$_i] && $_vai_um) ||
				($__entrada2[$_i] && $_vai_um) ? 1 : 0;

			$_saida[$_i] = $_saida_temp;
		}

		$this -> flag_zero = ($_saida == $this -> zero) ? 1 : 0;

		$this -> flag_overflow = ( ($__entrada1[0] == 0 && $__entrada2[0] == 0 && $_saida[0] == 1) ||
			($__entrada1[1] == 1 && $__entrada2[1] == 1 && $_saida[0] == 0) ) ?
			1 : 0;

		$this -> flag_carry = $_vai_um;
		$this -> flag_neg = ($_saida[0] == "0") ? 0 : 1;

		$this -> saida = $_saida;
		
		return $_saida;
	
	}
	
	/**
	  * Metodo responsavel pela execucao da instrucao soma com carry(ADC).
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  * @param $__carry Carry
	  */
	private function soma_carry ($__entrada1, $__entrada2, $__carry) {
		
		$_saida = $this-> soma($__entrada1, $__entrada2);
		$_saida = $this-> soma($_saida, $__carry);
		
		$this -> saida = $_saida;
		
		return $_saida;
	}
	
	/**
	  * Este metodo realiza a operacao de subtracao com carry(SBC)
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  * @param $__carry Carry
	  */
	private function subtracao_carry ($__entrada1, $__entrada2, $__carry) {
		
		$_saida = $this -> subtracao($__entrada1, $__entrada2);
		$_saida = $this -> add($_saida, $__carry);
		$_um = str_pad ("", strlen ($__entrada1)-1, 0).'1';
		$_saida = $this -> subtracao($__entrada1, $_um);
		
		$this -> saida = $_saida;
		
		return $_saida;
	}

	/**
	  * Este metodo realiza a operacao de subtracao(RSC)
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  * @param $__carry Carry
	  */
	private function subtracao_carry_invetida ($__entrada1, $__entrada2, $__carry) {
		
		$_saida = $this -> subtracao_carry($__entrada2, $__entrada1, $__carry);
		$this -> saida = $_saida;
		
		return $_saida;
	}

   	/**
	  * Metodo responsavel pela execucao da instrucao ou(or).
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  */
	private function ou ($__entrada1, $__entrada2) {

		$_saida = $this -> zero;

		for ($_i = 31; $_i >= 0 ; $_i--) {
             $_saida[$_i] = ($__entrada1[$_i] || $__entrada2[$_i]) ? 1 : 0;
		}

		$this -> flag_overflow = 0;
		$this -> flag_carry = 0;
		$this -> flag_neg = ($_saida[0] == "0") ? 0 : 1;
		$this -> flag_zero = ($_saida == $this -> zero) ? 1 : 0;

		$this -> saida = $_saida;
	}
			
	/**
	  * Metodo responsavel pela copia(MOV) do parametro.
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  */
	private function copia ($__entrada1, $__entrada2) {

		$this -> flag_overflow = 0;
		$this -> flag_carry = 0;
		$this -> flag_neg = $__entrada2[0];
		$this -> flag_zero = ($__entrada2 == $this -> zero) ? 1 : 0;

		$this -> saida = $__entrada2;
		
		return $this -> saida;
	}
	
	/**
	  * Metodo responsavel pela copia(MOV) do parametro.
	  *
	  * @param $__valor Primeiro operando fonte
	  * @param $__deslocamento Segundo operando fonte
	  */
	private function e_negacao($__entrada1, $__entrada2){
		$_saida = $this->e($__entrada1, $__entrada2);
		$_saida = $this->negacao($_saida);
		
		$this->saida = $_saida;
		
		return $_saida;
	}
	
	/**
	  * Metodo responsavel pela execucao da negacao(MVN) do valor passado como parametro.
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  */
	private function negacao ($__entrada2) {

		$_saida = $this -> zero;

		for ($_i = 31; $_i >= 0 ; $_i--) {
                    $_saida[$_i] = ($__entrada2[$_i]) ? 0 : 1;
		}

		$this -> flag_overflow = 0;
		$this -> flag_carry = 0;
		$this -> flag_neg = ($_saida[0] == "0") ? 0 : 1;
		$this -> flag_zero = ($_saida) ? 0 : 1;

		$this -> saida = $_saida;
		
		return $_saida;
	}
	
	/**
	  * Metodo responsavel pela execucao da instrucao de multiplicacao(MUL).
	  *
	  * @param $__entrada1 Primeiro operando fonte
	  * @param $__entrada2 Segundo operando fonte
	  */
	private function multiplicacao ($__entrada1, $__entrada2) {
		
		$_zero = str_pad ("", 32, 0);
		
		if ($__entrada1 == $_zero || $__entrada2 == $_zero){

			$this -> flag_overflow = 0;
			$this -> flag_carry = 0;
			$this -> flag_neg = 0;
			$this -> flag_zero = 1;

			$this -> saida = $_zero;

		} else {

			$_saida = $_zero;
			
			for($_i = 0; $_i < bindec($__entrada2); $_i++){
				$_saida = $this -> soma($_saida, $__entrada1);
			}
			
			$this -> saida = $_saida;
		}

	}
	

}
?>