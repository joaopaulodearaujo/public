<?php

/**
 * Error Handler padrão do Phailom.
 * É realizada a verificação nos arquivos de configuração e
 * caso solicitado grava os erros em um arquivo de log e/ou os retornamos
 * para o usuário
 *
 * @author João Paulo de Araújo <joaopaulo@syllom.com.br>
 * @package phailom_error
 *
 */
function phailomErrorHandler ($__erro, $__string, $__arquivo, $__linha) {

	// Primeiro verificamos se $__erro está em error_reporting fazendo um 
	// "e" lógico. Se o erro estiver sendo desconsiderado por error_reporting, 
	// o desconsideramos aqui também.
	if (! (error_reporting() & $__erro) ) {
		return TRUE;
	}

	$_hora = date('H:i:s Y-m-d');

	switch ($__erro) {

		case E_WARNING:
			$_tipo = 'E_WARNING';
		break;

		default:
			$_tipo = 'UNKOWN';
		break;
 
	}

	phailomLogError("\"$_tipo\";\"$_hora\";\"$__arquivo\";\"$__linha\";\"$__string\"");

	// retornamos TRUE para que o tratador de error interno do PHP não seja
	// executado
    return TRUE;
}

set_error_handler('phailomErrorHandler');

function phailomLogError($__mensagem) {

	$_property = PhailomProperty::getInstance();

	$_arquivo_log = fopen($_property -> config('error_file'), 'a');

	fwrite($_arquivo_log, trim(nl2br($__mensagem)).PHP_EOL);
	fclose($_arquivo_log);

	return TRUE;

}

function phailomLogException(&$__mensagem, $__e = NULL) {

	$_property = PhailomProperty::getInstance();

	$_arquivo_log = fopen($_property -> config('exception_file'), 'a');

	fwrite(
		$_arquivo_log, 
		'['.date('H:i:s Y-m-d').'] '.$__mensagem .': '.$__e -> getMessage().
		PHP_EOL.$__e -> getTraceAsString().PHP_EOL.PHP_EOL
	);

	fclose($_arquivo_log);

	return TRUE;

}

?>