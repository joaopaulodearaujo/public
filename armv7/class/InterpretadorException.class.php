<?php


class InterpretadorException extends Exception {

	const INSTRUCAO_INVALIDA = 'Instrução Inválida';
	const REGISTRADOR_INVALIDO = 'Registrador Inválido';
	const INSTRUCAO_INEXISTENTE = 'Instrução inexistente';
	const CONSTANTE_INVALIDA = 'Constante Inválida';
	const MEMORIA_INVALIDA = 'Endereço de Memória Inválido';
	const TIPO_INEXISTENTE = 'Tipo de Instrução Inexistente';
	const ENTRADA_INVALIDA = 'Entrada Inválida';
	const ROTULO_EXISTENTE = 'Rótulo já existente';
	const ROTULO_INEXISTENTE = 'Rótulo não existente';
	const ROTULO_LIMITE = 'Rótulo fora do limite';
	const OPERADORES_INVALIDOS = 'Número inváldo de operadores passados para a instrução';
	const OPERANDO2_INVALIDO = 'Operando 2 Inválido';
	const ADDRESS_INVALIDO = 'Endereço para cálculo de load/store inválido';
	const OFFSET_LIMITE = 'Offset fora do limite válido';

}

?> 
