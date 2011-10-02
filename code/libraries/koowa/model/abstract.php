<?php

abstract class KModelAbstract extends KObject implements KObjectIdentifiable
{
	protected $_state;
	protected $_total;
	protected $_list;
	protected $_item;
	protected $_column; 
	
	public function __construct(KConfig $config = null)
	{
    if(!isset($config)) $config = new KConfig();

    parent::__construct($config);

    $this->_state = $config->state;
	}
	
	protected function _initialize(KConfig $config)
	{
    $config->append(array(
      'state'      => KFactory::tmp('lib.koowa.model.state'),
    ));

    parent::_initialize($config);  
  }
  
  public function getIdentifier()
	{
		return $this->_identifier;
	}
	
  public function isConnected()
	{
    return true;
	} 
	
	public function set($property, $value = null)
  {
  	if(is_object($property)) 
    	$property = (array) KConfig::toData($property);

  	if(is_array($property)) $this->_state->setData($property); 
  	else $this->_state->$property = $value;

    return $this;
  }
  
  public function get($property = null, $default = null)
  {
    $result = $default;

    if(is_null($property))
      $result = $this->_state->getData();
    else {
      if(isset($this->_state->$property)) 
        $result = $this->_state->$property;          
    }

    return $result;           
  }   
  
  public function reset($default = true)
  {
    unset($this->_list);
    unset($this->_item);
    unset($this->_total);

    $this->_state->reset($default);

    return $this; 
  }

  public function getState()
  {
    return $this->_state;
  }

  public function getItem()
  {
    return $this->_item;
  }

  public function getList()
  {
    return $this->_list;
  }

  public function getTotal()
  {
    return $this->_total;
  }      
  
  public function getData()
  {
    if($this->_state->isUnique())
      $data = $this->getItem();
    else 
      $data = $this->getList();

    return $data;    
  }
  
  public function getColumn($column)
  {   
    return $this->_column[$column];
  }      
  
  public function __call($method, $args)
  {
    if(isset($this->_state->$method))
      return $this->set($method, $args[0]);
    return parent::__call($method, $args);  
  }
}