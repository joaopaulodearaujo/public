<?php

/**
 * Classe responsável por gerenciar de forma genérica a interface do sistema.
 * Todas as requisições a interface/ são tratadas aqui.
 *
 * @author João Paulo de Araújo <joaopaulo@syllom.com.br>
 * @package phailom_controller
 *
 */

class InterfaceController extends PhailomAbstractController {

	/**
	 * postDispatch é responsável pelo que é processado com as informações
	 * do controller após a execução do método chamado. No caso, checa qual
	 * render será utilizado: o padrão do controller ou algum diferente.
	 *
	 * @access public
	 *
	 */
	public function postDispatch() {

		if (is_null($this -> render)) { 

			$this -> render('generic');

		} else {

			$this -> render($this -> render);

		}

	}

	/**
	 * Usamos __call para forçar com que toda requisição para uma action
	 * (método) não implementada no ServicesController venha parar aqui.
	 *
	 * @access public
	 *
	 * @param string $__method nome do action (método) invocado
	 * @param array $__args argumentos passados na chamada do action
	 *
	 * @todo suporte a múltiplos níveis de páginas
	 *
	 */
	public function __call($__metodo, $__argumento) {

		// Autenticar();
		// Autorizar();
		// Podem ser usados em todos os services ou de forma separada
		// Ambas estao implementadas na classe pai


		// Quebramos a URI em parâmetros desconsiderando o "root"
		// da aplicação.
		// No caso de /root/ ser o root do sistema, teríamos como exemplo
		// 		/root/interface/teste-interface/buscar:
		//		array (
		//			teste-interface,
		//			buscar,
		//		)
		// O argumento TRUE na chamada do método diz que as posições do array
		// devem ser convertidas para serem utilizadas como se fossem métodos
		// (como ocorre no caso do controlador de services)
		$_parametros = PhailomURI::getParameters(
			preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']), 
			$this -> property -> config('root'),
			TRUE
		);

		$_body_path = (! sizeof($_parametros)) 
				? $this -> property -> config('index')
				: implode('/', $_parametros);

		$_head_path = str_replace('/', '_', $_body_path);

		$_body = PhailomBody::$_body_path();
		$_head = PhailomHead::$_head_path();

		$_erro = (is_null($_body) || is_null($_head));

		if (! $_erro) {

			$this -> view -> body = $_body;
			$this -> view -> head = $_head;

			$this -> _helper -> layout -> setLayout($this -> property -> config('default_layout'));
	
		} elseif ($_erro && $__metodo == 'paginaNaoEncontradaAction') {

			die();

		} else {

			$this -> _redirect($this -> property -> config('notFound'));

		}
	}

}