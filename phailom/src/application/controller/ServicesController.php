<?php

/**
 * Classe responsável por gerenciar de forma genérica todos os serviços do sistema.
 * Todas as requisições a services/ são tratadas aqui.
 *
 * @author João Paulo de Araújo <joaopaulo@syllom.com.br>
 * @package phailom_controller
 *
 */
class ServicesController extends PhailomAbstractController {


	/**
	 * Usamos __call para forçar com que toda requisição para uma action
	 * (método) não implementada no ServicesController venha parar aqui.
	 *
	 * @access public
	 *
	 * @param string $__method nome do action (método) invocado
	 *
	 * @param array $__args argumentos passados na chamada do action
	 */
	public function __call($__method, $__args) {

		// Instância do envelope padrão que será usado para retornar os dados
		$_output = new PhailomOutputDataEnvelope();

		// Autenticar();
		// Autorizar();
		// Podem ser usados em todos os services ou de forma separada
		// Ambas estao implementadas na classe pai

		try {

			// Quebramos a URI em parâmetros desconsiderando o "root"
			// da aplicação.
			// No caso de /root/ ser o root do sistema, teríamos como exemplo
			// 		/root/services/teste/buscar:
			//		array (
			//			teste,
			//			buscar,
			//		)
			$_parametros = PhailomURI::getParameters(
				$_SERVER['REQUEST_URI'], 
				$this -> property -> config('root')
			);

			// A classe, o service, que vai ser usado no momento é o parâmetro
			// 0 de $_parametros, com inicial maiúscula e concatenado com "Service"
			$_class = ucwords($_parametros[0]).'Service' ;

			// O método que será utilizado do serviço é o segundo parâmetro,
			// mas desconsiderando desconsiderando a querystring
			// exemplo /root/teste/buscar/?id=2
			// o $_method assume "buscar"
			$_method = preg_replace('/\?.*/', '', $_parametros[1]);

			// Bom, temamos carregar o serviço solicitado
			// Caso ele não exista, exceção dizendo que o serviço
			// Não existe
			try {

				Zend_Loader::loadClass($_class);

			} catch (Zend_Exception $_e) {

				throw new PhailomException('SERVICE_NOT_FOUND');
		
			}

			$_service = new $_class();

			// O metaData de de um service contém informações que serão
			// importates para a execução dos métodos do service, tais como:
			//    - tal método utiliza determinado dao, validação, aceita campos nulos,
			//      entre outros
			$_method_config = $_service -> getMetaData($_method);

			// @todo - corrigir as exceções abaixo

			// Procuramos o método que precisamos (no nosso exemplo, "buscar") no service
			// e também checamos se há "metaDatum" sobre ele.
			// Caso um dos dois não exista, é lançada uma exceção avisando que o 
			// método não pode ser encontrado
 			if (! method_exists($_service, $_method) || is_null($_method_config)) {

				throw new PhailomException('METHOD_NOT_FOUND');

			}

			// Memesmo o método existindo, ainda não temos a garantia de que podemos usá-lo.
			// Precisamos do "metaDatum" dele ok. Para simplificar o debug, no caso de erro
			// foi prudente lançar exceções diferentes nesses casos.
			// Se o "metaDatum" do método não está ok, avisamos que há um problema através de
			// exceção
			if (is_null($_method_config)) {

				throw new PhailomException('INVALID_METHOD_CONFIG');

			}

			// Temos carregar o dao do "metaDatum" do método que será utilizado
			// do service
			try {

				Zend_Loader::loadClass($_method_config['dao']);

			} catch (Zend_Exception $_e) {

				throw new PhailomException('DAO_NOT_FOUND');
		
			}

			// $_dao é uma instância desse dao do "metaDatum".
			// Esse dao herda PhailomAbstractDao que contém uma
			// série de implementações úteis e também força que 
			// a validação de todo dao seja especificada (através da implementaçào
			// de um método abstrato)
			$_dao = new $_method_config['dao']();

			// Dizemos ao service, qual dao ele usará (setDao é implementado em 
			// PhailomAbstractService). Com isso garantimos que também posssamos usar 
			// os métodos já implementados em PhailomAbstractService.
			$_service -> setDao($_dao);

			if (isset($_method_config['input_method'])) {

				// Se o método da requisição for diferente do especificado no 
				// "metaDatum" do método do serviço e tenha sido especificado um método a ser usado
				// temos uma exceção
				if (
					($_SERVER['REQUEST_METHOD'] != $_method_config['input_method'])
					&& (! is_null($_method_config['input_method']))
				) {

					throw new PhailomException('INVALID_REQUEST_METHOD');

				}

				// Pegamos os dados da requisição. Caso não haja nada,
				// especificamos como sendo um array vazio.
				$_data = ($_SERVER['REQUEST_METHOD'] == 'GET') 
					? $_GET
					: (($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : array());

				// Os erros de validação iniciam como um array vazio
				$_validation_errors = array();

				// O conceito de accept_raw diz se podemos ou não aceitar
				// entradas no formado ?a=b&c=d. Caso isso não seja verdade
				// só podemos recebemos objetos (json, xml)
				if ($_method_config['accept_raw'] == TRUE) {

					// Chamamos a validação para a dao do service, que será responsável
					// por validar os campos "raw".
					// A validação é implementada no dao e é obrigatória, sendo um
					// método abstrato da classe PhailomAbstractDao
					$_validation = call_user_func($_method_config['dao'].'::getValidation');

					// Realizamos a validação dos campos "raw", considerando 
					// se podemos ou não ignorar campos nulos.
					// Dizemos também qual deve ser o pre-fixo para os erros encontrados.
					// Isso ajuda quando da geração das mensagens de retorno.
					// Por exemplo, um erro no campo "nome" de Teste, será 
					// marcado como Teste.nome
					$_validation_errors = PhailomValidator::validate (
						$_data, 
						$_validation, 
						$_method_config['ignore_null'],
						$_method_config['dao'].'.'
					);

				}

				// Temos também que considerar os objetos que podem ser passados na requisição (json, xml)
				// Cada um deles utiliza a validação de um dao (que deve ser especificada separadamente para
				// cada objeto). 

				if (is_array($_method_config['input_objects'])) {

					foreach ($_method_config['input_objects'] as $_object_name => $_object_config) {

						// Como existe a possibilidade de especificarmos um dao para validação inexistente,
						// tratamos possiveis excecoes aqui.
						try {

							Zend_Loader::loadClass($_object_config['validation']);

						} catch (Zend_Exception $_e) {

							throw new PhailomException('VALIDATION_CLASS_NOT_FOUND');

						}

						// Pegamos a validacao do dao
						$_validation = call_user_func($_object_config['validation'].'::getValidation');

						// Por hora suportamos só objetos json. Futuramente xml, entre outros
						$_data[$_object_name] = json_decode($_data[$_object_name], true);

						// É realizada a validação, observando a possibilidade de ignorar campos nulos
						// para o objeto em questão (além do prefixo).
						$_object_validation_errors = PhailomValidator::validate (
							$_data[$_object_name], 
							$_validation, 
							$_object_config['ignore_null'],
							$_object_config['validation'].'.'
							
						);

						// Os erros de validação são sempre acumulados. No final da execução, 
						// casa haja erros todos são retornados para o usuário de uma única vez
						$_validation_errors = array_merge (
							$_validation_errors,
							$_object_validation_errors
						);

					}

					// Pra que isso? Caso haja apenas um objeto de entrada e a requisicao não 
					// suporte campos "raw", fazemos com que $_data seja esse único objeto, o que facilita
					// sua manipualação por parte de métodos mais simples
					if (sizeof($_method_config['input_objects']) == 1 && ! $_method_config['accept_raw']) {
						$_data = reset($_data);
					}
				}

				// Se hover erros, lançamos a exceção correta, já com as mensagens de erro corretas.
				// O tratamento das mensagens ainda falta ser implementado.
				if (sizeof($_validation_errors) > 0) {
					throw new PhailomValidationException($_validation_errors);
				}

			}

			// Bom, tudo testado, tudo ok. Agora chamamos o método do nosso serviço e passamos
			// nossos dados já tratados e prontos para ir para a lógica / banco.
			// $_service_output pode ser um id de inserção, um conjunto de dados, 
			// um booleano, qualquer coisa que a lógica julgar necessário retornar para o usário 
			$_service_output = $_service -> $_method($_data);

			// Checamos o valor de $_service_output.
			// Em "data" do nosso envelope padrão sempre é retornado um array.
			// Caso $_service_output seja um array, "data" assume seu valor. 
			// Caso contrário "empurramos" o meliante para o array (array_push)
			if (! is_null($_service_output)) {

				if (is_array($_service_output)) {

					$_output -> data = $_service_output;

				} else {

					array_push($_output -> data, $_service_output);

				}

			}

			// Todo método ($_method) de todo serviço ($_class) tem uma mensagem padrão. 
			// Isso ainda será implementado já em conjunto com a internacionalização.
			// Mas tomando o cuidado de mantermos mensagens padrões para casos emergenciais.
			// O mesmo se aplica para as mensagens de validação.
			// O "status" do envelope padrão pode ser útil para a interface
			// saber se a operação foi ou não realziada com sucesso
			$_output -> message =  $_class.' -> '.$_method;
			$_output -> status = PhailomOutputStatus::SUCCESS;


		// Tratamos a exceção de validação, preenchendo corretamente o envelope padrão
		} catch (PhailomValidationException $_e) {

			$_output -> message = $_e -> getMessage();
			$_output -> data = $_e -> getErrors();
			$_output -> status = PhailomOutputStatus::ERROR;

		// Tratamos as demais exceções do Phailom, seguindo os mesmo princípios supracitados.
		} catch (PhailomException $_e) {

			$_output -> message = $_e -> getMessage();
			$_output -> status = PhailomOutputStatus::ERROR;

		// Demais exceções. O plano é ter um tratamento especial para as exceções lançadas
		// pelas bases de dados (já internacionalizando, claro e seguinte os códigos de retorno de 
		// erro de cada base)
		} catch (Exception $_e) {

			$_output -> message = 'UNKOWN_EXCEPTION';
			$_output -> status = PhailomOutputStatus::ERROR;

			phailomLogException($_output -> message, $_e);

		}

		// Especifico o tamanho (quantidade de posições) do "data" retornado. 
		// Pode ser útil para fins de paginação na interface
		$_output -> length = sizeof($_output -> data);

		// Coloco o envelope padrão na resposta que será renderizada
		$this -> view -> response = $_output;

		// Desabilitamos o uso de layouts
		$this -> _helper -> layout -> disableLayout();

		// Especifico o render. Por hora apenas json, há planos de expandir para pelo menos mais
		// xml
		$this -> render('json');

		// Auditar()
		// No processo de auditoria podemos manter logs do que está sendo realizado no sistema
		// A autoria está implementada na classe pai

	}

}

?>
