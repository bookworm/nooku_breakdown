<?php 

class KDatabaseRowsetTable extends KDatabaseRowsetAbstract
{
  protected $_table = false;  
  
  public function __construct(KConfig $config = null)
  {
    parent::__construct($config);

    $this->_table = $config->table;

    $this->reset();

    if(!empty($config->data))
      $this->addData($config->data->toArray(), $config->new);                                                
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
        catch (KDatabaseTableException $e) {
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

  public function getRow() 
  {
    $result = null;

    if($this->isConnected()) 
      $result = $this->getTable()->getRow();

    return $result;      
  }      
  
  public function __call($method, array $arguments)
  {
    if($this->isConnected() && !isset($this->_mixed_methods[$method]))
    {
      foreach($this->getTable()->getBehaviors() as $behavior) {
        $this->mixin($behavior);
      }     
    }

    return parent::__call($method, $arguments);    
  }
}