<?php

session_start();

error_reporting(E_ALL | E_STRICT);
ini_set('display_erros', 1);

include_once ('loader.php');

header('Content-type: application/json; charset=UTF-8');

try {

	if ($_POST['entrada'] != NULL) {

		$entrada = explode(PHP_EOL, $_POST['entrada']);

		$json = new StdClass;

		$interpretador = Interpretador::get_instancia();
		$binarios = $interpretador -> interpretar_entrada($entrada);
		
		$datapath = Datapath::get_instancia();

		$tamanho = $datapath -> carregar($binarios);

		if (! $tamanho) {
	

			$json -> sucesso = false;
			$json -> erro = 'N&atilde;o h&aacute; instru&ccedil;&otilde;es para serem executadas!';

			echo (json_encode($json));

			exit;

		}

		for (;;) {

			if ($datapath -> get_pc() / 4 > $tamanho - 1) {

				break;

			}

			$debug = $datapath -> interpretar();

		}

		$json -> sucesso = true;

		// -------------- INSTRUCOES ------------
	
		ob_start();

			foreach ($debug['MEMORIA_INSTRUCOES'] as $indice => $valor) {
	
				echo $indice.' => '.$valor.'<br />';
		
			}

			echo '<br />-------------------- <br />';
			echo 'N&uacute;mero de Instru&ccedil;&otilde;es Interpretadas: '
				.sizeof($debug['MEMORIA_INSTRUCOES']).'<br /><br />';
		
			$instrucoes = ob_get_contents();
		
		ob_end_clean();

		$final = end($debug['EXECUCAO']);

		// -------------- DADOS ------------

		ob_start();

			foreach ($final['MEMORIA_DADOS'] as $indice => $valor) {
	
				echo $indice.' => '.$valor.' ('.Utils::signed_bindec($valor).')<br />';
		
			}

			echo '<br />-------------------- <br />';
			echo 'Quantidade de posi&ccedil;&otilde;es ocupadas na mem&oacute;ria de dados: '
				.sizeof($final['MEMORIA_DADOS']).'<br /><br />';
		
			$dados = ob_get_contents();
		
		ob_end_clean();

		// -------------- REGISTRADORES ------------

		ob_start();

			foreach ($final['BANCO_REGISTRADORES'] as $indice => $valor) {
	
				echo $indice.' => '.$valor.' ('.Utils::signed_bindec($valor).')<br />';
		
			}

			echo '<br />';
		
			$registradores = ob_get_contents();
		
		ob_end_clean();

		// -------------- PIPELINE ------------

		ob_start();

			foreach ($debug['PIPELINE'] as $ciclo => $estagios) {

				echo '[CICLO] => '.$ciclo.'<br />';
	
				echo '[BUSCA] => '.((isset($estagios['BUSCA'])) ? $estagios['BUSCA'] : 'NOP').'<br />';
				echo '[DECODIFICACAO] => '.((isset($estagios['DECODIFICACAO'])) ? $estagios['DECODIFICACAO'] : 'NOP').'<br />';
				echo '[EXECUCAO] => '.((isset($estagios['EXECUCAO'])) ? $estagios['EXECUCAO'] : 'NOP').'<br /><br />';
				echo '--------------------<br /><br />';
		
			}

			echo 'N&uacute;mero de Ciclos do Pipeline Executados: '
				.sizeof($debug['PIPELINE']).'<br /><br />';
		
			$pipeline = ob_get_contents();
		
		ob_end_clean();

		// -------------- EXECUCAO ------------

		ob_start();
	
			foreach ($debug['EXECUCAO'] as $indice => $linha) {

				echo '[PC] => '.$linha['PC'].'<br />';
				echo '[EXECUTAR] => '.(($linha['EXECUTAR']) ? 'SIM' : 'NAO').'<br />';
				echo '[CODIGO] => '.$linha['CODIGO'].'<br />';
				echo '[BINARIO] => '.$linha['BINARIO'].'<br />';
				echo '[SAIDA_ULA] => '.$linha['SAIDA_ULA'].'<br />';
				echo '[CSPR] =><br/> '
					.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[N] => '.$linha['CSPR']['N'].'<br />'
					.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[Z] => '.$linha['CSPR']['Z'].'<br />'
					.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[C] => '.$linha['CSPR']['C'].'<br />'
					.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[V] => '.$linha['CSPR']['V'].'<br /><br />';
				echo '--------------------<br /><br />';
			}

			echo 'N&uacute;mero de Instru&ccedil;&otilde;es Executadas: '
				.sizeof($debug['EXECUCAO']).'<br /><br />';
		
			$execucao = ob_get_contents();
		
		ob_end_clean();


	}


	$json -> instrucoes = $instrucoes;
	$json -> dados = $dados;
	$json -> registradores = $registradores;
	$json -> pipeline = $pipeline;
	$json -> execucao = $execucao;

} catch (Exception $__e) {

	$json -> sucesso = false;

	$json -> erro = nl2br($__e -> getMessage());

}

echo (json_encode($json));

?>
