<?php

abstract class KDatabaseRowsetAbstract extends KObjectSet implements KDatabaseRowsetInterface
{ 
  protected $_identity_column;
  protected $_row;     
  
  public function __construct(KConfig $config = null)
  {
    if(!isset($config)) $config = new KConfig();

    parent::__construct($config);

    $this->_row = $config->row;

    if(isset($config->identity_column))
      $this->_identity_column = $config->identity_column;
 
    $this->reset();

    if(!empty($config->data))
      $this->addData($config->data->toArray(), $config->new);	
  }         
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'row'               => null,
      'data'              => null,
      'new'               => true,
      'identity_column'   => null   
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
	
	public function insert(KDatabaseRowInterface $row)
  {
     if(isset($this->_identity_column))
       $handle = $row->{$this->_identity_column};
     else
       $handle = $row->getHandle();
 
     if($handle)
       $this->_object_set->offsetSet($handle, $row);
 
     return true;   
  }       
  
  public function extract(KDatabaseRowInterface $row)
  {
    if(isset($this->_identity_column))
      $handle = $row->{$this->_identity_column};
    else
      $handle = $row->getHandle();

    if($this->_object_set->offsetExists($handle))
      $this->_object_set->offsetUnset($handle);

    return $this;   
  }
  
  public function getData($modified = false)
  {
    $result = array();
    foreach ($this as $key => $row)  {
      $result[$key] = $row->getData($modified);
    }
    return $result;         
  }     
  
  public function setData($data, $modified = true)
  {
    if($data instanceof KDatabaseRowInterface)
      $data = $data->toArray();
    else
      $data = (array) $data;

    if(isset($this->_identity_column))
      unset($data[$this->_identity_column]);

    if($modified)
    {
      foreach($data as $column => $value) {
        $this->setColumn($column, $value);
      }    
    }
    else
    {
      foreach ($this as $row) {
        $row->setData($data, false);
      }     
    }

    return $this;  
  }    
  
  public function addData(array $data, $new = true)
  {   
    foreach($data as $k => $row)
    {
      $instance = $this->getRow()
        ->setData($row, $new)
        ->setStatus($new ? NULL : KDatabase::STATUS_LOADED);     

      $this->insert($instance);  
    }

    return $this;  
  }           
  
  public function getIdentityColumn()
  {
    return $this->_identity_column;
  }   
  
  public function find($needle)
  {
    $result = null;

    if(!is_scalar($needle))
    {
      $result = clone $this;

      foreach ($result as $i => $row) 
      { 
        foreach($needle as $key => $value) {
          if(!in_array($row->{$key}, (array) $value))
            $result->extract($row);
        } 
      } 
    }
    else 
    {
      if(isset($this->_object_set[$needle]))
        $result = $this->_object_set[$needle];
    }

    return $result; 
  }    
  
  public function save()
  {
    $result = false;

    if(count($this))
    {
      $result = true;

      foreach ($this as $i => $row) {
        if(!$row->save()) 
          $result = false; 
      }  
    } 

    return $result;  
  }  
  
  public function delete()
  {
    $result = false;

    if(count($this))
    {
      $result = true;

      foreach ($this as $i => $row) {
        if(!$row->delete()) 
          $result = false;  
      }        
    } 

    return $result;  
  }      
  
  public function reset()
  {
    $this->_object_set->exchangeArray(array());

    return true;  
  }
  
  public function getRow()
  { 
    if(!($this->_row instanceof KDatabaseRowInterface))
    {
      $identifier         = clone $this->_identifier;
      $identifier->path   = array('database', 'row');
      $identifier->name   = KInflector::singularize($this->_identifier->name);

      $options  = array(
        'identity_column' => $this->getIdentityColumn()
      );

      $this->_row = KFactory::tmp($identifier, $options); 
    }

    return clone $this->_row; 
  }  
  
  public function getColumn($column)
  {
    $result = array();
    foreach($this as $key => $row) {
      $result[$key] = $row->$column;        
    }

    return $result;   
  }       
  
  public function setColumn($column, $value)
  {
    foreach($this as $row) {
      $row->$column = $value;
    } 
  }    
  
  public function toArray()
  {
    $result = array();
    foreach ($this as $key => $row)  {
      $result[$key] = $row->toArray();
    }
    return $result;   
  }       
  
  public function __call($method, array $arguments)
  {
    $parts = KInflector::explode($method);

    if($parts[0] == 'is' && isset($parts[1])) {
      if(isset($this->_mixed_methods[$method])) return true;
      return false;    
    }
    else
    {
      if(isset($this->_mixed_methods[$method]))
      {
        foreach ($this as $i => $row) {
          $row->__call($method, $arguments);
        }

        return $this;         
      }     
    }

    throw new BadMethodCallException('Call to undefined method :'.$method);  
  }   
}