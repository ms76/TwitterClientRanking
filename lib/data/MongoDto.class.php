<?php
/**
 * Mongo dto.
 *
 * @package    sample
 * @subpackage dto
 * @author     Masashi Sekine <sekine@cloudrop.jp>
 */
class MongoDto {
	private $_vars = array();

	public function __construct() {
		$_vars = get_object_vars($this);
		unset($_vars['_vars']);
		$this->_vars = $_vars;
	}

	public function __get($name) {
		return $this->__isset($name) ? $this->$name : null;
	}

	public function __set($name, $value) {
		$this->$name = $value;
	}

	public function __isset($name) {
		return isset($this->$name);
	}

	public function __call($name, $arguments) {

		if (preg_match('/^([g|s]et)([A-Z][A-Za-z0-9]+)$/', $name, $match)) {
			$method = strtolower(preg_replace('/(?<=\\w)(?=[A-Z])/', '_$1', $match[2]));
			if (!array_key_exists($method, $this->_vars)) throw new Exception('Method '.$name.' is not defined');

			switch ($match[1]) {
				case 'get':
					return $this->$method;
				case 'set':
					$this->$method = $arguments[0];
					return;
			}
		}

		throw new Exception('Method '.$name.' is not defined');
	}

	public function toArray() {
		$_vars = get_object_vars($this);
		unset($_vars['_vars']);
		return $_vars;
	}

}
