<?php

class KDatabaseRowTable extends KDatabaseRowAbstract
{
  protected $_table = false;
  
  public function __construct(KConfig $config = null)
  {
    parent::__construct($config);

    $this->_table = $config->table;

    $this->reset();

    if(isset($config->data))
      $this->setData($config->data->toArray(), $this->_new);
  }   
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'table' => $this->_identifier->name
    ));

    parent::_initialize($config);
  }    
  
  public function getTable()
  {
    if($this->_table !== false)
    {
      if(!($this->_table instanceof KDatabaseTableAbstract))
      {               
        if(!($this->_table instanceof KIdentifier))
          $this->setTable($this->_table);  
        try {
          $this->_table = KFactory::get($this->_table);
        } 
        catch(KDatabaseTableException $e) {
          $this->_table = false;
        } 
      } 
    }

    return $this->_table;
  } 
  
      
  public function setTable($table)
  {
    if(!($table instanceof KDatabaseTableAbstract))
    {
      if(is_string($table) && strpos($table, '.') === false ) 
      {                
        $identifier       = clone $this->_identifier;
        $identifier->path = array('database', 'table');
        $identifier->name = KInflector::tableize($table);
      }
      else  $identifier = KFactory::identify($table);

      if($identifier->path[1] != 'table')
        throw new KDatabaseRowsetException('Identifier: '.$identifier.' is not a table identifier');

      $table = $identifier;            
    } 

    $this->_table = $table;

    return $this;
  }   
  
  public function isConnected()
  {
    return (bool) $this->getTable();
  } 
  
  public function load()
  {
    $result = null;

    if($this->_new)
    {
      if($this->isConnected())
      {
        $data = $this->getTable()->filter($this->getData(true), true);
        $row  = $this->getTable()->select($data, KDatabase::FETCH_ROW);

        if(!$row->isNew())
        {
          $this->setData($row->toArray(), false);
          $this->_modified = array();
          $this->_new      = false;

          $this->setStatus(KDatabase::STATUS_LOADED);
          $result = $this;   
        }  
      } 
    }

    return $result;   
  }  
  
  public function save()
  {
    $result = false;

    if($this->isConnected())
    {  
      if($this->_new)
        $result = $this->getTable()->insert($this);
      else
        $result = $this->getTable()->update($this); 
      
      if($result !== false) {
        if(((integer) $result) > 0)    
          $this->_modified = $this->getTable()->filter($this->_modified, true);  
      }
    }

    return (bool) $result;    
  }                   
  
  public function delete()
  {
    $result = false;
    
    if($this->isConnected())
    {
      if(!$this->_new) 
      {
        $result = $this->getTable()->delete($this);

        if($result !== false) {
          if(((integer) $result) > 0)    
            $this->_new = true;
        }   
      }  
    }     

    return (bool) $result;
  } 
  
  public function reset()
  {
    $result = parent::reset();

    if($this->isConnected())
    {
      if($this->_data = $this->getTable()->getDefaults()) {
        $this->setStatus(null);
        $result = true;    
      }  
    }

    return $result;
  }
  
  public function count()
  {
    $result = false;
      
    if($this->isConnected()) {
      $data   = $this->getTable()->filter($this->getData(true), true);
      $result = $this->getTable()->count($data);
    }

    return $result;   
  }  
  
  public function __unset($column)
  {
    if($this->isConnected())
    {
      $field = $this->getTable()->getColumn($column);

      if(isset($field) && $field->required)
        $this->_data[$column] = $field->default;
      else
        parent::__unset($column);
    }
  }   
  
  public function __call($method, array $arguments)
  { 
    if($this->isConnected())
    {
      $parts = KInflector::explode($method);

      if($parts[0] == 'is' && isset($parts[1]))
      {
        if(!isset($this->_mixed_methods[$method]))
        { 
          $behavior = strtolower($parts[1]);

          if($this->getTable()->hasBehavior($behavior)) {
            $this->mixin($this->getTable()->getBehavior($behavior));
            return true; 
          }   

          return false;
        }
        return true; 
      }   
    }

    return parent::__call($method, $arguments);   
  }
}