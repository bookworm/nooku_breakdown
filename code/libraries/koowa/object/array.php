<?php 

class KObjectArray extends KObject implements IteratorAggregate, ArrayAccess, Serializable
{
  protected $_data = array();
  
  public function __construct(KConfig $config = null)
  {
    if(!isset($config)) $config = new KConfig();

    parent::__construct($config);
    
    $this->_data = KConfig::toData($config->data);  
  }  
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'data'  => array(),
    ));

    parent::_initialize($config);    
  }

  public function offsetExists($offset)
  {
    return $this->__isset($offset);
  }

  public function offsetGet($offset)
  {   
    return $this->__get($offset);
  }             

  public function offsetSet($offset, $value)
  {
    if (is_null($offset)) $this->_data[] = $value; 
    else $this->__set($offset, $value); 

    return $this;    
  } 

  public function offsetUnset($offset)
  {
    $this->__unset($offset);
    return $this;
  }

  public function getIterator() 
  {
    return new ArrayIterator($this->_data);
  }

  public function serialize()
  {
    return serialize($this->_data);
  }

  public function unserialize($data)
  {
    $this->data = unserialize($data);
  }     
  
  public function __get($key)
  {
    $result = null;        
    
    if(isset($this->_data[$key])) $result = $this->_data[$key];
  
    return $result;
  }

  public function __set($key, $value)
  {
    $this->_data[$key] = $value;
  }

  public function __isset($key)
  {
    return array_key_exists($key, $this->_data);
  }

  public function __unset($key)
  {
    unset($this->_data[$key]);
  }

  public function toArray()
  {
    return $this->_data;
  }
}