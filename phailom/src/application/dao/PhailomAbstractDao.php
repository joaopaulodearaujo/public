<?php

abstract class PhailomAbstractDao extends Zend_Db_Table_Abstract implements PhailomValidationInterface {

	protected $_name;

	protected function _setupTableName($__name = null) {

		$this -> _name = $__name;

		parent::_setupTableName();

	}

	public function findById($__data) {

		if(! isset($__data[$this -> getId()])) {

			throw new PhailomDaoException('INVALID_INPUT_DATA');

		}

		$_row = parent::find($__data[$this -> getID()]) -> toArray();

		if (sizeof($_row) == 0) {

			throw new PhailomDaoException('ID_NOT_FOUND');

		}

		return $_row;

	}

	public function findAll() {

		return parent::fetchAll() -> toArray();

	}

	public function insert(array $__data) {  

		unset($__data[$this -> getID()]);

		$_data = $this -> filterFields($__data);

		$_id = parent::insert($_data);

		return $this -> findById (array($this -> getID() => $_id));

	}

	public function deleteById($__data) {

		if (! isset($__data[$this -> getID()])) {

			throw new PhailomDaoException('INVALID_INPUT_DATA');

		}

		$_where = $this -> getAdapter() -> quoteInto($this -> getID().' = ?', $__data[$this -> getID()]);
		$_count = parent::delete($_where);

		if (! $_count) {

			throw new PhailomDaoException('NO_TUPLE_FOUND');

		}
	} 

	protected function getID() {

		parent::info();

		return reset($this -> _primary);

	}

	protected function filterFields($__data) {

		return array_intersect_key($__data, array_flip($this -> _getCols()) );

	}

}

?> 