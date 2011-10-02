<?php

class KDatabaseSchemaColumn extends KObject
{
	public $name;
	public $type;
	public $length;
	public $scope;
	public $default;		
	public $required = false;
	public $primary = false;
	public $autoinc = false;
	public $unique = false;
	public $related = array();
	protected $_filter;       
	
	public function __set($key, $value)
  {
    if($key == 'filter') 
      $this->_filter = $value;
  } 
  
  public function __get($key)
  {
    if($key == 'filter') 
    {
      if(!isset($this->_filter)) 
        $this->_filter = $this->type;

      if(!($this->_filter instanceof KFilterInterface)) 
        $this->_filter = KFilter::factory($this->_filter);

      return $this->_filter;   
    }  
  }
}