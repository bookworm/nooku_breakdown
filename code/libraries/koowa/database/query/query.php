<?php

class KDatabaseQuery extends KObject
{
  public $count   = false;
  public $distinct  = false;
  public $columns = array();
  public $from = array();
  public $join = array();
  public $where = array();
  public $group = array();
  public $having = array();
  public $order = array();
  public $limit = null;
  public $offset = null;
  protected $_adapter;
  
  public function __construct( KConfig $config = null)
  {
    if(!isset($config)) $config = new KConfig();

    parent::__construct($config);

    $this->_adapter = $config->adapter; 
  }     
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'adapter' => KFactory::get('lib.koowa.database.adapter.mysqli')
    ));

    parent::_initialize($config);
  }   
  
  public function getAdapter()
  {
    return $this->_adapter;
  }   
  
  public function select( $columns = '*')
  {
    settype($columns, 'array'); 

    $this->columns = array_unique( array_merge( $this->columns, $columns ) );
    return $this;  
  }
  
  public function count()
  {
    $this->count   = true;
    $this->columns = array();
    return $this;   
  }    
  
  public function distinct()
  {
    $this->distinct = true;
    return $this;
  }              
  
  public function from( $tables )
  {
    settype($tables, 'array'); 

    array_walk($tables, array($this, '_prefix'));

    $this->from = array_unique( array_merge( $this->from, $tables ) );
    return $this;   
  }   
  
  public function join($type, $table, $condition)
  {
    settype($condition, 'array'); 

    $this->_prefix($table); 

    $this->join[] = array(
      'type'      => strtoupper($type),
      'table'     => $table,
      'condition' => $condition,
    );

    return $this;  
  }    
  
  public function where($property, $constraint = null, $value = null, $condition = 'AND')
  {
    if(!empty($property)) 
    {
      $where = array();
      $where['property'] = $property;

      if(isset($constraint))
      {
        $constraint = strtoupper($constraint);
        $condition  = strtoupper($condition);

        $where['constraint'] = $constraint;
        $where['value']      = $value;       
      }

      $where['condition']  = count($this->where) ? $condition : '';

      $signature = md5($property.$constraint.$value);
      if(!isset($this->where[$signature]))
        $this->where[$signature] = $where;  
    }

    return $this;        
  }  
  
  public function group($columns)
  {
    settype($columns, 'array'); 
  
    $this->group = array_unique( array_merge( $this->group, $columns));
    return $this;   
  }

  public function having($columns)
  {
    settype($columns, 'array');

    $this->having = array_unique( array_merge( $this->having, $columns ));
    return $this;     
  }    
  
  public function order($columns, $direction = 'ASC')
  {
    settype($columns, 'array'); 

    foreach($columns as $column)
    {
      $this->order[] = array(
        'column'    => $column,
        'direction' => $direction
      );   
    }

    return $this;
  }    
                  
  public function limit($limit, $offset = 0)
  {
    $this->limit  = (int) $limit;
    $this->offset = (int) $offset;

    return $this;    
  }

  protected function _prefix(&$data)
  {
    $prefix = $this->_adapter->getTablePrefix();
    $data = $prefix.$data;    
  }
  
  public function __toString()
  {
    $query = '';
    if(!empty($this->columns) || $this->count)
    {   
      $query = 'SELECT';

      if($this->distinct) $query .= ' DISTINCT';

      if($this->count) $query .= ' COUNT(*)';
    }

    if(!empty($this->columns) && ! $this->count) 
    {
      $columns = array();
      foreach($this->columns as $column) {
        $columns[] = $this->_adapter->quoteName($column);
      } 
  
      $query .= ' '.implode(' , ', $columns);  
    }

    if(!empty($this->from)) 
    {
      $tables = array();
      foreach($this->from as $table) {
        $tables[] = $this->_adapter->quoteName($table);
      } 
    
      $query .= ' FROM '.implode(' , ', $tables);      
    }

    if (!empty($this->join))
    {
      $joins = array();
      foreach ($this->join as $join)
      {
        $tmp = ' ';

        if(!empty($join['type'])) $tmp .= $join['type'] . ' ';

        $tmp .= ' JOIN ' . $this->_adapter->quoteName($join['table']);
        $tmp .= ' ON (' . implode(' AND ', $this->_adapter->quoteName($join['condition'])) . ')'; 

        $joins[] = $tmp;    
      }

      $query .= implode(' ', $joins);   
    }

    if (!empty($this->where)) 
    {
      $query .= ' WHERE';

      foreach($this->where as $where)
      {
        if(isset($where['condition']))
          $query .= ' '.$where['condition'];      

        $query .= ' '. $this->_adapter->quoteName($where['property']);

        if(isset($where['constraint'])) 
        {
          $value = $this->_adapter->quoteValue($where['value']);

          if(in_array($where['constraint'], array('IN', 'NOT IN')))
            $value = ' ( '.$value. ' ) ';

          $query .= ' '.$where['constraint'].' '.$value; 
        }
      }  
    }

    if(!empty($this->group)) 
    {
      $columns = array();
      foreach($this->group as $column) {
        $columns[] = $this->_adapter->quoteName($column);
      } 

      $query .= ' GROUP BY '.implode(' , ', $columns);  
    }

    if(!empty($this->having)) 
    {
      $columns = array();
      foreach($this->having as $column) {
        $columns[] = $this->_adapter->quoteName($column);
      } 

      $query .= ' HAVING '.implode(' , ', $columns);  
    }

    if(!empty($this->order))
    {
      $query .= ' ORDER BY ';

      $list = array();
      foreach ($this->order as $order) {
        $list[] = $this->_adapter->quoteName($order['column']).' '.$order['direction'];
      }

      $query .= implode(' , ', $list);   
    }

    if(!empty($this->limit)) 
      $query .= ' LIMIT '.$this->offset.' , '.$this->limit;

    return $query; 
  }                 
}