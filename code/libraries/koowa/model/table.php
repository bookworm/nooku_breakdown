<?php

class KModelTable extends KModelAbstract
{
  protected $_table = false;
  
  public function __construct(KConfig $config)
  {
    parent::__construct($config);

    $this->_table = $config->table;

    // Set the static states
    $this->_state
      ->insert('limit'    , 'int')
      ->insert('offset'   , 'int')
      ->insert('sort'     , 'cmd')
      ->insert('direction', 'word', 'asc')
      ->insert('search'   , 'string')
      ->insert('callback' , 'cmd');  

    if($this->isConnected()) {
      foreach($this->getTable()->getUniqueColumns() as $key => $column) {
        $this->_state->insert($key, $column->filter, null, true, $this->getTable()->mapColumns($column->related, true));
      }  
    } 
  }
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'table' => $this->_identifier->name,
    ));

    parent::_initialize($config);   
  } 
  
  public function set($property, $value = null)
  {
    parent::set($property, $value);

    if($limit = $this->_state->limit) 
      $this->_state->offset = $limit != 0 ? (floor($this->_state->offset / $limit) * $limit) : 0;

    return $this;  
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
	
	public function getItem()
  {
    if(!isset($this->_item))
    {
      if($this->isConnected())
      {
        $query  = null;

        if($this->_state->isUnique())
        {
          $query = $this->getTable()->getDatabase()->getQuery();

          $this->_buildQueryColumns($query);
          $this->_buildQueryFrom($query);
          $this->_buildQueryJoins($query);
          $this->_buildQueryWhere($query);
          $this->_buildQueryGroup($query);
          $this->_buildQueryHaving($query);   
        }

        $this->_item = $this->getTable()->select($query, KDatabase::FETCH_ROW); 
      } 
    }

    return $this->_item;     
  } 
  
  public function getList()
  {
    if(!isset($this->_list))
    {
      if($this->isConnected())
      {
        $query  = null;

        if(!$this->_state->isEmpty())
        {
          $query = $this->getTable()->getDatabase()->getQuery();

          $this->_buildQueryColumns($query);
          $this->_buildQueryFrom($query);
          $this->_buildQueryJoins($query);
          $this->_buildQueryWhere($query);
          $this->_buildQueryGroup($query);
          $this->_buildQueryHaving($query);
          $this->_buildQueryOrder($query);
          $this->_buildQueryLimit($query);
        }

        $this->_list = $this->getTable()->select($query, KDatabase::FETCH_ROWSET);
      }
    }

    return $this->_list; 
  }
  
  public function getTotal()
  {
    if(!isset($this->_total))
    {
      if($this->isConnected())
      {
        $query = $this->getTable()->getDatabase()->getQuery()->count(); 
  
        $this->_buildQueryFrom($query);
        $this->_buildQueryJoins($query);
        $this->_buildQueryWhere($query);

        $total = $this->getTable()->count($query);
        $this->_total = $total;
      }    
    }

    return $this->_total;
  }  
  
  public function getColumn($column)
  {   
    if(!isset($this->_column[$column])) 
    {   
      if($this->isConnected()) 
      {
        $query = $this->getTable()->getDatabase()->getQuery()
            ->distinct()
            ->group('tbl.'.$this->getTable()->mapColumns($column));

        $this->_buildQueryOrder($query);
        
        $this->_column[$column] = $this->getTable()->select($query);  
      }    
    }

    return $this->_column[$column];  
  }    

  protected function _buildQueryColumns(KDatabaseQuery $query)
  {
    $query->select(array('tbl.*'));
  }

  protected function _buildQueryFrom(KDatabaseQuery $query)
  {
    $name = $this->getTable()->getName();
    $query->from($name.' AS tbl');
  }
  
  protected function _buildQueryJoins(KDatabaseQuery $query) { } 
  
  protected function _buildQueryWhere(KDatabaseQuery $query)
  {
    $states = $this->_state->getData(true);

    if(!empty($states))
    {
      $states = $this->getTable()->mapColumns($states);
      foreach($states as $key => $value) {
        if(isset($value))  
          $query->where('tbl.'.$key, 'IN', $value);
      }  
    }   
  }  
  
  protected function _buildQueryGroup(KDatabaseQuery $query) { }  
  protected function _buildQueryHaving(KDatabaseQuery $query) { }
  
  protected function _buildQueryOrder(KDatabaseQuery $query)
  {
    $sort       = $this->_state->sort;
    $direction  = strtoupper($this->_state->direction);

    if($sort) 
      $query->order($this->getTable()->mapColumns($sort), $direction);  

    if(array_key_exists('ordering', $this->getTable()->getColumns()))
      $query->order('tbl.ordering', 'ASC');   
  }  
  
  protected function _buildQueryLimit(KDatabaseQuery $query)
  {
    $limit = $this->_state->limit;

    if($limit) 
    {
      $offset = $this->_state->offset;
      $total  = $this->getTotal();

      if($offset !== 0 && $total !== 0)        
      {
        if($offset >= $total) {
          $offset = floor(($total-1) / $limit) * $limit;    
          $this->_state->offset = $offset;  
        }   
       }

       $query->limit($limit, $offset);       
    }    
  }
}   