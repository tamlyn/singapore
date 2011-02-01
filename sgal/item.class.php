<?php

abstract class sgal_item
{
	const unsaved = 1, lazy = 2, loaded = 3, modified = 4;  
	
	protected $db, $config, 
		$data = array(), 
		$state = self::unsaved;
	
	function __construct($data)
	{
		$this->db = sgal::db();
		$this->config = sgal_config::getInstance();
		
		$this->fromArray($data);
	}
	
	public function __get($name)
	{
		$method = 'get'.ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method();
		} elseif (in_array($name, $this->_propertyNames)) {
			return $this->data[$name];
		} else {
			throw new OutOfBoundsException('Invalid property: '.$name);
		}
	}
	
	public function __set($name, $value)
	{
		$method = 'set'.ucfirst($name);
		if (method_exists($this, $method)) {
			return $this->$method($value);
		} elseif (in_array($name, $this->_propertyNames)) {
			return $this->data[$name] = $value;
		} else {
			throw new OutOfBoundsException('Invalid property: '.$name);
		}
	}
	
	public function fromArray($data)
	{
		foreach ($data as $key => $value) {
			try {
				$this->$key = $value;
			} catch (OutOfBoundsException $e) {} // do nothing
		}
	}
	
	public function save()
	{
		$data = $this->toArray();
		if ($this->id) {
			$query = 'UPDATE '.$this->_tableName.' '.
				'SET '.implode('=?, ', array_keys($data)).'=? '.
				'WHERE id=?';
			//add id to fill last placeholder
			$data[] = $this->id;
		} else {
			$query = 'INSERT INTO '.$this->_tableName.' '.
				'('.implode(',', array_keys($data)).') '.
				'VALUES ('.implode(',', array_fill(0, count($data), '?')).')';
		}
		
		$statement = $this->db->prepare($query);
		$statement->execute(array_values($data));

		if (!$this->id) {
			$this->id = $this->db->lastInsertId();
		}
	}
	
	public function delete($hardDelete = false)
	{
		if ($hardDelete) {
			$statement = $this->db->prepare('DELETE FROM '.$this->tableName.' WHERE id = ?');
			$statement->execute(array($this->id));
			$this->id = null;
		} else {
			$this->deleted = date('Y-m-d H:i:s');
			$this->save();
		}
	}
	
	public function toArray()
	{
		$data = array();
		
		foreach ($this->_propertyNames as $property) {
			$data[$property] = $this->$property;
		}
		return $data;
	}
}
?>