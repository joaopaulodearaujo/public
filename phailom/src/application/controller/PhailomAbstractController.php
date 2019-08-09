<?php

/**
 * Classe abstrata responsável por implementar métodos ditos básicos
 * para outros controllers do sistema. 
 *
 * @author João Paulo de Araújo <joaopaulo@syllom.com.br>
 * @package phailom_controller
 *
 * @abstract
 *
 */
abstract class PhailomAbstractController extends Zend_Controller_Action {

	/**
	 * Armazena uma instância de Property (gadget), que é responsável
	 * por retornar propriedades da configuração do Phailom
	 * 
	 * @access protected
	 * 
	 */
	protected $property;

	/** 
	 *  Armazena uma instância da sessão padrão do Phailom
	 * 
	 * @access protected
	 *
	 */ 
	protected $session;

	/**
	 * Armazena qual render será utilizado pela view
	 * 
	 * @access protected
	 * 
	 */
	protected $render;

	/**
	 * Método do prórpio ZendFramewok para inicialização.
	 * de acordo com a própria documentação do Zend não é "saudável" sobreescrever
	 * o construtor do Zend_Controller_Action.
	 * init(), na verdade, nada mais é do que um metódo que é invocado como tarefa
	 * final do Zend_Controller_Action::__construct()
	 * 
	 * @access public
	 *
	 */
	public function init() {

		// Para poder pegar propriedades (vindas do arquivo de configuração do framework)
		$this -> property = PhailomProperty::getInstance();

		// Crio a sessão padrão da aplicação, responsável principalmente pelo idioma da sessão.
		// Outras sessões podem ser criadas de acordo com a necessidade dos services.
		$this -> session = new Zend_Session_Namespace($this -> property -> config('session'));

		// Apenas deixamos o render como NULL pois, caso ele mantenha este estado no final do
		// processamento, será utilizado o render padrão pela view.
		$this -> render = NULL;

	}

	/**
	 * Método para alterar o idioma da sessão para inglês.
	 * Feito isso o usuário é redirecionado para exatamente onde estava antes
	 * 
	 * @access public
	 * 
	 */
	public function englishAction() {

		$this -> session -> language = 'en_US';
		$this -> _redirect($_SERVER['QUERY_STRING']);

	}

	/**
	 * Método para alterar o idioma da sessão para português.
	 * Feito isso o usuário é redirecionado para exatamente onde estava antes
	 * 
	 * @access public
	 * 
	 */
	public function portuguesAction() {

		$this -> session -> language = 'pt_BR';
 		$this -> _redirect($_SERVER['QUERY_STRING']);

	}

	/**
	 * @todo implementar formas de autenticação genéricas 
	 */
	public function autenticar() {}

	/**
	 * @todo implementar formas de autorização genéricas. Se um usuário
	 * pode ou não acessar um dado conteúdo
	 */
	public function autorizar() {}

	/**
	 * @todo implementar a auditoria do sistema. Logs.
	 */
	public function auditar() {}

}

?> 
