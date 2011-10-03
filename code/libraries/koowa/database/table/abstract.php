<?php

abstract class KDatabaseTableAbstract extends KObject implements KObjectIdentifiable
{
  protected $_name;
  protected $_base;
  protected $_identity_column;
  protected $_column_map = array();
  protected $_database = false;
  protected $_row;
  protected $_rowset;
  protected $_defaults;    
  
  public function __construct(KConfig $config = null)
  {
    if(!isset($config)) $config = new KConfig();

    parent::__construct($config);

    $this->_name        = $config->name;
    $this->_base        = $config->base;
    $this->_database    = $config->database;
    $this->_row         = $config->row;
    $this->_rowset      = $config->rowset; 
      
    if(!$info = $this->getSchema())
      throw new KDatabaseTableException('Table '.$this->_name.' does not exist');
               
    if(!isset($config->identity_column)) 
    {
      foreach ($this->getColumns(true) as $column)
      {
        if($column->autoinc) {
          $this->_identity_column = $column->name;
          break;
        } 
      } 
    }
    else $this->_identity_column = $config->identity_column;
      
    $this->_column_map = $config->column_map ? $config->column_map->toArray() : array();
    if(!isset( $this->_column_map['id']) && isset($this->_identity_column))
      $this->_column_map['id'] = $this->_identity_column;
         
    if(!empty($config->filters)) 
    {
      foreach($config->filters as $column => $filter) {
        $this->getColumn($column, true)->filter = KConfig::toData($filter);
      }       
    }
  
    $this->mixin(new KMixinCommandchain($config->append(array('mixer' => $this))));
         
    if(!empty($config->behaviors))
      $this->addBehavior($config->behaviors);
  }                               
  
  protected function _initialize(KConfig $config)
  {
    $package = $this->_identifier->package;
    $name    = $this->_identifier->name;

    $config->append(array(
      'database'          => KFactory::get('lib.koowa.database.adapter.mysqli'),
      'row'               => null,
      'rowset'            => null,
      'name'              => empty($package) ? $name : $package.'_'.$name,
      'column_map'        => null,
      'filters'           => array(),
      'behaviors'         => array(),
      'identity_column'   => null,
      'command_chain'     => new KCommandChain(),
      'dispatch_events'   => false,
      'enable_callbacks'  => false,      
    ))->append(
      array('base' => $config->name)
    );

    parent::_initialize($config);      
  }      

  public function getIdentifier()
  {
    return $this->_identifier;
  }
  
  public function getDatabase()
  {
    return $this->_database;
  }

  public function setDatabase(KDatabaseAdapterAbstract $database)
  {
    $this->_database = $database;
    return $this;
  }   
  
  public function isConnected()
  {
    return (bool) $this->getDatabase();
  }

  public function getName()
  {
    return $this->_name;
  } 
  
  public function getBase()
  {
    return $this->_base;
  }  
  
  public function getPrimaryKey()
  {
    $keys = array();
    $columns = $this->getColumns(true);

    foreach ($columns as $name => $description) {
      if($description->primary)
        $keys[$name] = $description;   
    }

    return $keys;  
  }
  
  public function hasBehavior($behavior)
  { 
    return isset($this->getSchema()->behaviors[$behavior]); 
  }  
  
  public function addBehavior($behaviors)
  {
    $behaviors = (array) KConfig::toData($behaviors);

    foreach($behaviors as $behavior)
    {
      if(!($behavior instanceof KDatabaseBehaviorInterface))
        $behavior   = $this->getBehavior($behavior);    

      $this->getSchema()->behaviors[$behavior->getIdentifier()->name] = $behavior;
      $this->getCommandChain()->enqueue($behavior);
    }

    return $this;   
  }      
  
  public function getBehavior($behavior, $config = array())
  {
    if(!($behavior instanceof KIdentifier))
    {
      if(is_string($behavior) && strpos($behavior, '.') === false )
      {
        $identifier = clone $this->_identifier;
        $identifier->path = array('database', 'behavior');
        $identifier->name = $behavior;       
      }
      else $identifier = KFactory::identify($behavior);    
    }

    if(!isset($this->getSchema()->behaviors[$identifier->name]))
      $behavior = KDatabaseBehavior::factory($identifier, array_merge($config, array('mixer' => $this)));
    else
      $behavior = $this->getSchema()->behaviors[$identifier->name];

    return $behavior; 
  }        
  
  public function getBehaviors()
  {
    return $this->getSchema()->behaviors;
  }                   
  
  public function getSchema()
  {
    $result = null;

    if($this->isConnected())
    {
      try {
        $result = $this->_database->getTableSchema($this->getBase());
      }
      catch(KDatabaseException $e) {
        throw new KDatabaseTableException($e->getMessage());
      } 
    }

    return $result;   
  }      
  
  public function getColumn($columnname, $base = false)
  {
    $columns = $this->getColumns($base);
    return isset($columns[$columnname]) ? $columns[$columnname] : null;
  }   
  
  public function getColumns($base = false)
  {
    $name = $base ? $this->getBase() : $this->getName();
    $columns = $this->getSchema($name)->columns;

    return $this->mapColumns($columns, true); 
  } 
  
  public function mapColumns($data, $reverse = false)
  {
    $map = $reverse ? array_flip($this->_column_map) : $this->_column_map;

    $result = null;
    if(is_array($data))
    {
      $result = array();
      foreach($data as $column => $value)
      {
        if(isset($map[$column]))
          $column = $map[$column];

        $result[$column] = $value;   
      }    
    } 

    if(is_string($data))
    {
      $result = $data;
      if(isset($map[$data]))
        $result = $map[$data];    
    }

    return $result; 
  }
  
  public function getIdentityColumn()
  {
    $result = null;
    if(isset($this->_identity_column))
      $result = $this->_identity_column; 

    return $result;        
  } 
  
  public function getUniqueColumns()
  {
    $result  = array();
    $columns = $this->getColumns(true);

    foreach($columns as $name => $description) {
      if($description->unique)
        $result[$name] = $description;      
    }

    return $result;
  }    
  
  public function getDefaults()
  {
    if(!isset($this->_defaults))
    {
      $defaults = array();
      $columns  = $this->getColumns();

      foreach($columns as $name => $description) {
        $defaults[$name] = $description->default;
      }

      $this->_defaults = $defaults;  
    }

    return $this->_defaults;     
  }  
  
  public function getDefault($columnname)
  {
    $defaults = $this->getDefaults();
    return isset($defaults[$columnname]) ? $defaults[$columnname] : null;
  }  
  
  public function getRow()
  {
    if(!($this->_row instanceof KDatabaseRowInterface))
    {
      $identifier         = clone $this->_identifier;
      $identifier->path   = array('database', 'row');
      $identifier->name   = KInflector::singularize($this->_identifier->name);

      $options  = array(
        'table'             => $this, 
        'identity_column'   => $this->mapColumns($this->getIdentityColumn(), true)
      );

      $this->_row = KFactory::tmp($identifier, $options);          
    }

    return clone $this->_row;      
  } 
  
  public function getRowset()
  {
    if(!($this->_rowset instanceof KDatabaseRowsetInterface))
    {
      $identifier         = clone $this->_identifier;
      $identifier->path   = array('database', 'rowset');

      $options  = array(
        'table'             => $this, 
        'identity_column'   => $this->mapColumns($this->getIdentityColumn(), true)
      );

      $this->_rowset = KFactory::tmp($identifier, $options);  
    }

    return clone $this->_rowset;   
  } 
  
  public function select( $query = null, $mode = KDatabase::FETCH_ROWSET)
  {
    if(is_string($query) || (is_array($query) && is_numeric(key($query))))
    {
      $key    = $this->getIdentityColumn();
      $values = (array) $query;

      $query = $this->_database->getQuery()
        ->where($key, 'IN', $values); 
    }

    if(is_array($query) && !is_numeric(key($query)))
    {
      $columns = $this->mapColumns($query);
      $query   = $this->_database->getQuery();    

      foreach($columns as $column => $value) {
        $query->where($column, 'IN', $value);
      } 
    }

    if($query instanceof KDatabaseQuery)
    {
      if(!is_null($query->columns) && !count($query->columns))
        $query->select('*');

      if(!count($query->from))
        $query->from($this->getName().' AS tbl');
    }

    $context = $this->getCommandContext();
    $context->operation = KDatabase::OPERATION_SELECT;
    $context->query     = $query;
    $context->table     = $this->getBase();
    $context->mode      = $mode;

    if($this->getCommandChain()->run('before.select', $context) !== false) 
    {                   
      if($context->query)
      {
        $data = $this->_database->select($context->query, $context->mode, $this->getIdentityColumn());

        if(($context->mode != KDatabase::FETCH_FIELD) || ($context->mode != KDatabase::FETCH_FIELD_LIST))
        { 
          if($context->mode % 2)
          {
            foreach($data as $key => $value) {
              $data[$key] = $this->mapColumns($value, true);
            }  
          }
          else $data = $this->mapColumns(KConfig::toData($data), true);    
        }   
      }

      switch($context->mode)
      {
        case KDatabase::FETCH_ROW: 
        {
          $context->data = $this->getRow();
          if(isset($data) && !empty($data))
            $context->data->setData($data, false)->setStatus(KDatabase::STATUS_LOADED);
          break; 
        }

        case KDatabase::FETCH_ROWSET : 
        {
          $context->data = $this->getRowset();
          if(isset($data) && !empty($data)) 
            $context->data->addData($data, false);
          break;
        }

        default : $context->data = $data;  
      } 
          
      $this->getCommandChain()->run('after.select', $context);
    }

    return KConfig::toData($context->data);   
  }  
  
  public function count($query = null)
  {
    if(is_array($query) && !is_numeric(key($query)))
    {
      $columns = $this->mapColumns($query);

      $query   = $this->_database->getQuery();    
      foreach($columns as $column => $value) {
        $query->where($column, '=', $value);
      }               
    }

    if($query instanceof KDatabaseQuery)
    {
      $query->count();

      if(!count($query->from))
        $query->from($this->getName().' AS tbl');
    }

    $result = (int) $this->select($query, KDatabase::FETCH_FIELD);   
    return $result;  
  }      
  
  public function insert(KDatabaseRowInterface $row)
  {
    $context = $this->getCommandContext();
    $context->operation = KDatabase::OPERATION_INSERT;
    $context->data      = $row;
    $context->table     = $this->getBase();
    $context->query     = null;
    $context->affected  = false;    
      
    if($this->getCommandChain()->run('before.insert', $context) !== false) 
    {
      $data = $this->filter($context->data->getData(), true);
      $data = $this->mapColumns($data);
      $context->affected = $this->_database->insert($context->table, $data);

      if($context->affected !== false) 
      {
        if(((integer) $context->affected) > 0)
        {
          if($this->getIdentityColumn())
            $data[$this->getIdentityColumn()] = $this->_database->getInsertId();

          $context->data->setData($this->mapColumns($data, true), false)
            ->setStatus(KDatabase::STATUS_CREATED);  
        }
        else $context->data->setStatus(KDatabase::STATUS_FAILED);  
      }

      $this->getCommandChain()->run('after.insert', $context);     
    }

    return $context->affected; 
  }  
  
  public function update(KDatabaseRowInterface $row)
  {
    $context = $this->getCommandContext();
    $context->operation = KDatabase::OPERATION_UPDATE;
    $context->data      = $row;
    $context->table     = $this->getBase();
    $context->query     = null;
    $context->affected  = false;

    if($this->getCommandChain()->run('before.update', $context) !== false) 
    {
      $query = $this->_database->getQuery();

      foreach($this->getPrimaryKey() as $key => $column) {
        $query->where($column->name, '=', $this->filter(array($key => $context->data->$key), true));
      }

      $data = $this->filter($context->data->getData(true), true);
      $data = $this->mapColumns($data);

      $context->affected = $this->_database->update($context->table, $data, $query);

      if($context->affected !== false) 
      {
        if(((integer) $context->affected) > 0) {
          $context->data->setData($this->mapColumns($data, true), false)
            ->setStatus(KDatabase::STATUS_UPDATED);    
        }
        else $context->data->setStatus(KDatabase::STATUS_FAILED);     
      }      

      $context->query = $query;

      $this->getCommandChain()->run('after.update', $context);   
    }

    return $context->affected; 
  }    
  
  public function delete(KDatabaseRowInterface $row)
  {
    $context = $this->getCommandContext();
    $context->operation = KDatabase::OPERATION_DELETE;
    $context->table     = $this->getBase();
    $context->data      = $row;
    $context->query     = null;
    $context->affected  = false;
                                 
    if($this->getCommandChain()->run('before.delete', $context) !== false) 
    {
      $query = $this->_database->getQuery();

      foreach($this->getPrimaryKey() as $key => $column) {
        $query->where($column->name, '=', $context->data->$key);
      }

      $context->affected = $this->_database->delete($context->table, $query);

      if($context->affected !== false) 
      {
        if(((integer) $context->affected) > 0) {   
          $context->query = $query;
          $context->data->setStatus(KDatabase::STATUS_DELETED);
        }
        else $context->data->setStatus(KDatabase::STATUS_FAILED);
      }

      $this->getCommandChain()->run('after.delete', $context);
    }

    return $context->affected; 
  }  
  
  public function filter($data, $base = true)
  {
    settype($data, 'array');

    $data = array_intersect_key($data, $this->getColumns($base));

    foreach($data as $key => $value) {
      $data[$key] = $this->getColumn($key, $base)->filter->sanitize($value);
    }

    return $data;     
  }     
  
  public function __call($method, array $arguments)
  {
    $parts = KInflector::explode($method);

    if($parts[0] == 'is' && isset($parts[1]))
    {
      if($this->hasBehavior(strtolower($parts[1])))
        return true;    

      return false;    
    }

    return parent::__call($method, $arguments);      
  }
}