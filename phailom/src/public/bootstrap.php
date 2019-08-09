<?php

/**
 * Bootstrap da aplicação
 *
 * @author João Paulo de Araújo <joaopaulo@syllom.com.br>
 * 
 * @package phailom_bootstrap
 *
 */


// Especificamos o timezone
date_default_timezone_set('America/Sao_Paulo');

// Configuração da exibição de erro
// Em se tratando de testes, é prudente deixar display erros ativo
error_reporting(E_ALL & ~E_NOTICE | E_STRICT);
ini_set('display_erros', 1);

// Especificamos o path padrão de todo o sistema
set_include_path(
	'.' . PATH_SEPARATOR .
	'../lib'. PATH_SEPARATOR .
	'../application/config'. PATH_SEPARATOR .
	'../application/contents'. PATH_SEPARATOR .
	'../application/controller'. PATH_SEPARATOR .
	'../application/dao'. PATH_SEPARATOR .
	'../application/exception'. PATH_SEPARATOR .
	'../application/gadget'. PATH_SEPARATOR .
	'../application/output'. PATH_SEPARATOR .
	'../application/service'. PATH_SEPARATOR .
	'../application/validation'. PATH_SEPARATOR
);

// Inclusão do Zend Loader. Responsável por carregar
// as páginas de forma mais transparente
include_once 'Zend/Loader/Autoloader.php';

// Inclusão do PhailomErrorHandler. Possui funções responsáveis
// por tratar e logar os erros do sistema.
include_once 'PhailomErrorHandler.php';

// Aqui faço com que todoas as páginas que são iniciadas
// por Phailom, possam ser adicionadas automaticamente,
// sem a necessidade de includes
$autoLoader = Zend_Loader_Autoloader::getInstance();
$autoLoader -> registerNamespace('Phailom');

// Carrega o arquivo de configuração do sistema
$config = new Zend_Config_Ini('configuration.ini', 'configuration');

// Inicializa a sessão
Zend_Session::start();
$session = new Zend_Session_Namespace($config -> session);

// Especifica o idioma da sessão caso ainda nõa tenha sido especificado
if (!isset($session -> language)) {
	$session -> language = $config -> language;
}

// Configurações do Controller. 
// Definimos o diretório onde se encontram os controller, entre outras características
// (controller padrão, action padrão, et coetera)
$frontController = Zend_Controller_Front::getInstance();
$frontController -> setControllerDirectory(array('default' => '../application/controller'));
$frontController -> throwExceptions(true);
$frontController -> returnResponse(true);
$frontController -> setDefaultControllerName($config -> controller);
$frontController -> setDefaultAction($config -> action);

// Pega um instância do que nos possibilita pegar propriedade da configuração do sistema
$property = PhailomProperty::getInstance();

// Configura o banco de dados e especifica o adaptador padrão
// Ainda acho que isso é pertinência de cada controller. 
// Bom, depois resolvemos isso
$db = Zend_Db::factory(

	new Zend_Config_Ini(
		$property -> config('db_file'), 
		$property -> config('db')
	)
);

Zend_Db_Table_Abstract::setDefaultAdapter($db);

// Especifica diretório padrão para os layouts
Zend_Layout::startMvc(array(
	'layoutPath' => '../application/layout',
));

// Temos fazer o dispatch das requisições e exibir o resultado.
// Caso haja erros no Dispatcher do Zend, redirecionar para uma 
// página padrão de "not-found". 
// Em caso de outras exceções do Zend exibimos a mensagem.
// A ideia aqui é logar essas informações e tentar deixar tudo o 
// transparente possível para o usuário
try {

	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	$response = $frontController -> dispatch();
	$response -> sendResponse();
	
} catch (Zend_Controller_Dispatcher_Exception $e) {
		
	header('Location: ' . $config -> root.$config -> notFound);

} catch (Zend_Exception $e) {

	$_arquivo_log = fopen($property -> config('exception_file'), 'a');
	fwrite(
		$_arquivo_log, 
		'['.date('H:i:s Y-m-d').'] '.$e -> getFile().';'.$e -> getFile().';'.$e -> getLine().PHP_EOL.PHP_EOL
	);
	fclose($_arquivo_log);

}
	
?>