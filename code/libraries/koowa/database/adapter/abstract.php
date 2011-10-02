<?php

abstract class KDatabaseAdapterAbstract extends KObject implements KDatabaseAdapterInterface, KObjectIdentifiable
{
  protected $_connected = null;
  protected $_connection = null;
  protected $_insert_id;
  protected $_affected_rows;
  protected $_table_schema = null;
  protected $_table_prefix = '';
  protected $_name_quote = '`';
  protected $_options = null;    
  
  public function __construct( KConfig $config = null )
  {
    if(!isset($config)) $config = new KConfig();
    
    parent::__construct($config);

    $this->setConnection($config->connection);  
    $this->_table_prefix = $config->table_prefix;
    $this->_options = $config->options;
    $this->mixin(new KMixinCommandchain($config->append(array('mixer' => $this))));
  }     
  
  public function __destruct()
  {
    $this->disconnect();
  } 
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'options'          => array(),
      'charset'          => 'UTF-8',
      'table_prefix'     => 'jos_',
      'command_chain'    =>  new KCommandChain(),
      'dispatch_events'  => true,
      'enable_callbacks' => false,
      'connection'       => null,    
    ));

    parent::_initialize($config);  
  } 
  
  public function getIdentifier()
  {
    return $this->_identifier;
  }

  public function getQuery(KConfig $config = null)
  {
    if(!isset($config))
      $config = new KConfig(array('adapter' => $this));
    
    return new KDatabaseQuery($config);
  }  
  
  public function reconnect()
  {
    $this->disconnect();
    $this->connect();
    
    return $this;
  }

  public function disconnect()
  {
    $this->_connection = null;
    $this->_connected  = false;
    
    return $this;
  }
  
  abstract function getDatabase();
  abstract function setDatabase($database); 
  
  public function getConnection()
  {
    return $this->_connection;
  }
  
  public function setConnection($resource)
  {
    $this->_connection = $resource;
    return $this;
  }  
  
  public function getInsertId()
  {
    return $this->_insert_id;
  } 
  
  public function select($query, $mode = KDatabase::FETCH_ARRAY_LIST, $key = '')
  {
    $context = $this->getCommandContext();
    $context->query   = $query;
    $context->operation = KDatabase::OPERATION_SELECT;
    $context->mode    = $mode;

    if($this->getCommandChain()->run('before.select', $context) !== false) 
    {
      if($result = $this->execute( $context->query, KDatabase::RESULT_USE))
      {
        switch($context->mode)
        {
          case KDatabase::FETCH_ARRAY: 
            $context->result = $this->_fetchArray($result); 
            break;
            
          case KDatabase::FETCH_ARRAY_LIST: 
            $context->result = $this->_fetchArrayList($result, $key); 
            break;
            
          case KDatabase::FETCH_FIELD: 
            $context->result = $this->_fetchField($result); 
            break;
            
          case KDatabase::FETCH_FIELD_LIST: 
            $context->result = $this->_fetchFieldList($result, $key); 
            break;
            
          case KDatabase::FETCH_OBJECT: 
            $context->result = $this->_fetchObject($result); 
            break;
            
          case KDatabase::FETCH_OBJECT_LIST: 
            $context->result = $this->_fetchObjectList($result, $key); 
            break;
            
          default : $result->free();
        }
      }
        
      $this->getCommandChain()->run('after.select', $context);
    }

    return KConfig::toData($context->result);
  }   
  
  public function show($query, $mode = KDatabase::FETCH_ARRAY_LIST)
  {
    $context = $this->getCommandContext();
    $context->query   = $query;
    $context->operation = KDatabase::OPERATION_SHOW;
    $context->mode    = $mode;

    if($this->getCommandChain()->run('before.show', $context) !== false) 
    {
      if($result = $this->execute( $context->query, KDatabase::RESULT_USE))
      {
        switch($context->mode)
        {
          case KDatabase::FETCH_ARRAY: 
            $context->result = $this->_fetchArray($result); 
            break;
            
          case KDatabase::FETCH_ARRAY_LIST: 
            $context->result = $this->_fetchArrayList($result); 
            break;
            
          case KDatabase::FETCH_FIELD: 
            $context->result = $this->_fetchField($result); 
            break;
            
          case KDatabase::FETCH_FIELD_LIST: 
            $context->result = $this->_fetchFieldList($result); 
            break;
            
          case KDatabase::FETCH_OBJECT: 
            $context->result = $this->_fetchObject($result); 
            break;
            
          case KDatabase::FETCH_OBJECT_LIST: 
            $context->result = $this->_fetchObjectList($result); 
            break;
            
          default : $result->free();
        }
      }
        
      $this->getCommandChain()->run('after.show', $context);
    }

    return KConfig::toData($context->result);
  }   
  
  public function insert($table, array $data)
  {
    $context = $this->getCommandContext();
    $context->table   = $table;
    $context->data    = $data;
    $context->operation = KDatabase::OPERATION_INSERT;

    if($this->getCommandChain()->run('before.insert', $context) !== false)
    {
      if(count($context->data)) 
      {
        foreach($context->data as $key => $val) {
          $vals[] = $this->quoteValue($val);
          $keys[] = '`'.$key.'`';
        }

        $context->query = 'INSERT INTO '.$this->quoteName('#__'.$context->table )
           . '('.implode(', ', $keys).') VALUES ('.implode(', ', $vals).')';
         
        $context->result = $this->execute($context->query);
        $context->affected = $this->_affected_rows; 
      
        $this->getCommandChain()->run('after.insert', $context);
      }
      else $context->affected = false;
    }

    return $context->affected;
  }     
  
  public function update($table, array $data, $where = null)
  {
    $context = $this->getCommandContext();
    $context->table   = $table;
    $context->data    = $data;
    $context->where     = $where;
    $context->operation = KDatabase::OPERATION_UPDATE;

    if($this->getCommandChain()->run('before.update', $context) !==  false)
    {
      if(count($context->data)) 
      {       
        foreach($context->data as $key => $val) {
          $vals[] = '`'.$key.'` = '.$this->quoteValue($val);
        }

        //Create query statement
        $context->query = 'UPDATE '.$this->quoteName('#__'.$context->table)
            .' SET '.implode(', ', $vals)
            .' '.$context->where
        ;
    
        $context->result = $this->execute($context->query);

        $context->affected = $this->_affected_rows;
        $this->getCommandChain()->run('after.update', $context);   
      }
      else $context->affected = false;  
    }

    return $context->affected;
  } 
  
  public function delete($table, $where)
  {
    $context = $this->getCommandContext();
    $context->table   = $table;
    $context->data    = null;
    $context->where     = $where;
    $context->operation = KDatabase::OPERATION_DELETE;

    if($this->getCommandChain()->run('before.delete', $context) !== false)
    {
      $context->query = 'DELETE FROM '.$this->quoteName('#__'.$context->table)
          .' '.$context->where
      ;

      $context->result = $this->execute($context->query);

      $context->affected = $this->_affected_rows;
      $this->getCommandChain()->run('after.delete', $context);
    }

    return $context->affected;
  }   
  
  public function execute($sql, $mode = KDatabase::RESULT_STORE )
  { 
    $sql = $this->replaceTablePrefix( $sql );
    
    $result = $this->_connection->query($sql, $mode);
    
    if($result === false) 
      throw new KDatabaseException($this->_connection->error.' of the following query : '.$sql, $this->_connection->errno);

    $this->_affected_rows = $this->_connection->affected_rows;
    $this->_insert_id     = $this->_connection->insert_id;

    return $result;
  }   

  public function setTablePrefix($prefix)
  {
    $this->_table_prefix = $prefix;
    return $this;
  }

  public function getTablePrefix()
  {
    return $this->_table_prefix;
  }         
  
  public function replaceTablePrefix( $sql, $replace = null, $needle = '#__' )
  {
    $replace = $replace ? $replace : $this->getTablePrefix();
    $sql = trim( $sql );
    
    $pattern = "($needle(?=[a-z0-9]))";
    $sql = preg_replace($pattern, $replace, $sql);
      
    return $sql;
  } 
  
  public function quoteValue($value)
  {
    if(is_array($value))
    {
      foreach ($value as $k => $v) {
        $value[$k] = $this->quoteValue($v);
      }

      $value = implode(', ', $value);    
    }
    else
    {
      if(is_string($value) && !is_null($value))
        $value = $this->_quoteValue($value);
    }
    return $value; 
  }
  
  public function quoteName($spec)
  {
    if(is_array($spec))
    {
      foreach ($spec as $key => $val) {
        $spec[$key] = $this->quoteName($val);
      }

      return $spec;  
    }

    $spec = trim($spec);
    $spec = preg_replace_callback('#(?:\b|\#)+(?<!`)([a-z0-9\.\#\-_]+)(?!`)\b#', array($this, '_quoteName') , $spec);

    return $spec; 
  } 

  abstract protected function _fetchField($result);
  abstract protected function _fetchFieldList($result);
  abstract protected function _fetchArray($sql);
  abstract protected function _fetchArrayList($result, $key = '');
  abstract protected function _fetchObject($result);
  abstract protected function _fetchObjectList($result, $key='' );
  abstract protected function _parseTableInfo($info);
  abstract protected function _parseColumnInfo($info);
  abstract protected function _parseColumnType($spec);
  abstract protected function _quoteValue($value);    
  
  protected function _quoteName($name)
  {
    $result =  '';

    if(is_array($name)) $name = $name[0];

    $name   = trim($name);

    if($name == '*' || is_numeric($name)) return $name;

    if($pos = strrpos($name, '.'))
    {
      $table  = $this->_quoteName(substr($name, 0, $pos));
      $column = $this->_quoteName(substr($name, $pos + 1)); 
      $result = "$table.$column";
    }
    else $result = $this->_name_quote. $name.$this->_name_quote;

    return $result;   
  }
}