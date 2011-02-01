<?php

class sgal_config
{
	private static $instance;
	
	protected $db, $_configValues;
	
	private function __construct()
	{
		$this->db = sgal::db();
		$this->_configValues = array();
		
		try {
			$statement = $this->db->query('SELECT * FROM config');
			while ($row = $statement->fetch()) {
				$this->_configValues[$row['key']] = $row['value'];
			}
		} catch (PDOException $e) {
			return;
		}
	}
	
	public static function getInstance()
	{
		if (!isset(self::$instance)) {
            $classname = __CLASS__;
            self::$instance = new $classname;
        }

        return self::$instance;
	}
	
	public function __get($key)
	{
		if (isset($this->_configValues[$key])) {
			return $this->_configValues[$key];
		} else {
			return null;
		}
	}
	
	public function __set($key, $value)
	{
		$this->_configValues[$key] = $value;
	}
	
	public function save()
	{
		$statement = $this->db->prepare('INSERT INTO config (key, value) VALUES (?, ?)');

		foreach ($this->_configValues as $key => $value) {
			$statement->execute(array($key, $value));
		}
	}
}

?>
