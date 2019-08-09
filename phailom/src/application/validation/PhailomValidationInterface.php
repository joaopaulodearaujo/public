<?

/**
 * Interface que diz o que deve ser implementado por todos
 * que forem usar o processo de validação
 *
 * @author João Paulo de Araújo <joaopaulo@syllom.com.br>
 * @package phailom_validation
 *
 *
 */
interface PhailomValidationInterface {

	/**
	 * Método estático para que deve retornar um array associativo
	 * onde as chaves representam os nomes dos campos a serem validados
	 * e os valores são constantes declaradas em PhailomValidation
	 *
	 * @access public
	 * @static
	 *
	 * @return array array associativo com a validação. (campo => PhailomValidation::expressão)
	 *
	 */
    public static function getValidation();

}
 
