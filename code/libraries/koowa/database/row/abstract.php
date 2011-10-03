<?php

abstract class KDatabaseRowAbstract extends KObjectArray implements KDatabaseRowInterface
{
  protected $_modified = array();
  protected $_status = null;
  protected $_status_message = '';
  protected $_new = true;
  protected $_identity_column;
  
  public function __construct(KConfig $config = null)
  {
    if(!isset($config)) $config = new KConfig();
      
    parent::__construct($config);
      
    if(isset($config->identity_column))
      $this->_identity_column = $config->identity_column;
 
    $this->reset();

    $this->_new = $config->new;

    if(isset($config->data))
      $this->setData($config->data->toArray(), $this->_new);

    if(isset($config->status)) 
      $this->setStatus($config->status);

    if(!empty($config->status_message)) 
      $this->setStatusMessage($config->status_message);
  }  
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'data'             => null,
      'new'              => true,
      'status'           => null,
      'status_message'   => '', 
      'identity_column'  => null     
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
  
  public function getData($modified = false)
  {
    if($modified)
      $result = array_intersect_key($this->_data, $this->_modified);  
    else
      $result = $this->_data;

    return $result; 
  }
  
   public function setData( $data, $modified = true )
   {
    if($data instanceof KDatabaseRowInterface)
      $data = $data->toArray();
    else
      $data = (array) $data;

    if($modified) 
    {
      foreach($data as $column => $value) {
        $this->$column = $value;
      }  
    }
    else
      $this->_data = array_merge($this->_data, $data);

    return $this;
  }    
  
  public function getStatus()
  {
    return $this->_status;
  }     
  
  public function setStatus($status)
  {
    $this->_status   = $status;
    $this->_new      = ($status === NULL) ? true : false;

    return $this;
  }  
  
  public function getStatusMessage()
  {
    return $this->_status_message;
  }  
  
  public function setStatusMessage($message)
  {
    $this->_status_message = $message;
    return $this;
  }      
  
  public function load()
  {
    $this->_modified = array();

    return $this;   
  }      
  
  public function save()
  {
    $this->_modified = array();
  
    return false; 
  }    
  
  public function delete()
  {
    return false;
  }      
  
  public function reset()
  {
    $this->_data     = array();
    $this->_modified = array();
  
    $this->setStatus(NULL);
    return true; 
  }      
  
  public function count()
  {
    return false;
  }    
  
  public function __set($column, $value)
  {
    if(!isset($this->_data[$column]) || ($this->_data[$column] != $value) || $this->isNew()) 
    {
      parent::__set($column, $value);

      $this->_modified[$column] = true;
      $this->_status            = null;   
    }    
  }        
  
  public function __unset($column)
  {
    parent::__unset($column);

    unset($this->_modified[$column]); 
  }          
  
  public function getIdentityColumn()
  {
    return $this->_identity_column;
  }
  
  public function getModified()
  {
    return array_keys($this->_modified);
  }
  
  public function isNew()
  {
    return (bool) $this->_new;
  }  
  
  public function __call($method, array $arguments)
  {
    $parts = KInflector::explode($method);

    if($parts[0] == 'is' && isset($parts[1]))
    {
      if(isset($this->_mixed_methods[$method]))
        return true;  

      return false;
    }

    return parent::__call($method, $arguments);       
  }
}