<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_erros', 1);

include_once ('loader.php'); 

header('Content-type: application/json; charset=UTF-8');

$interpretador = Interpretador::get_instancia();

try {

	$saida = NULL;
	$json = new StdClass;
	$json -> sucesso = true;

	if (isset($_POST['entrada']) && $_POST['entrada'] != NULL) {
	
		$entrada = explode(PHP_EOL, $_POST['entrada']);

		$binarios = $interpretador -> interpretar_entrada($entrada);

		ob_start();

			foreach($binarios as $_indice => $_binario) {
				
				// Mexi aqui Joao, eu sei que vc vai querer mudar kkkk
				// Se for MULT...
				if(substr($_binario, 7, 2) == '11'){
					// Troca o 11 por 00 antes de exibir
					$_binario = substr($_binario, 0, 7).'00'.substr($_binario, 9, 33);
				}
				
				echo $_indice.' - '.$_binario.nl2br(PHP_EOL);

			}

			$saida = ob_get_contents();
		
		ob_end_clean();

	}

	$json -> saida = $saida;

} catch (InterpretadorException $__e) {

		$json -> sucesso = false;
		$json -> erro = nl2br($__e -> getMessage());

}

echo json_encode($json);

?>