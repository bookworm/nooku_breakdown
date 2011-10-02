<?php

class KDatabaseAdapterMysqli extends KDatabaseAdapterAbstract
{
  protected $_name_quote = '`';  
  
  protected $_typemap = array(
    // numeric
    'smallint'  => 'int',
    'int'       => 'int',
    'integer'   => 'int',
    'bigint'    => 'int',
    'mediumint' => 'int',
    'smallint'  => 'int',
    'tinyint'   => 'int',
    'numeric'   => 'numeric',
    'dec'       => 'numeric',
    'decimal'   => 'numeric',
    'float'     => 'float',
    'double'    => 'float',
    'real'      => 'float',
                
    // boolean
    'bool'    => 'boolean',
    'boolean' => 'boolean',

    // date & time
    'date'      => 'date',
    'time'      => 'time',
    'datetime'  => 'timestamp',
    'timestamp' => 'int',
    'year'      => 'int',

    // string
    'national char'    => 'string',
    'nchar'            => 'string',
    'char'             => 'string',
    'binary'           => 'string',
    'national varchar' => 'string',
    'nvarchar'         => 'string',
    'varchar'          => 'string',
    'varbinary'        => 'string',
    'text'             => 'string',
    'mediumtext'       => 'string',
    'tinytext'         => 'string',
    'longtext'         => 'string',

    // blob
    'blob'       => 'raw',
    'tinyblob'   => 'raw',
    'mediumblob' => 'raw',
    'longtext'   => 'raw',
    'longblob'   => 'raw',

    //other
    'set'  => 'string',
    'enum' => 'string',   
  ); 
  
  protected $_database;
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'options'  => array(
      'host'     => ini_get('mysqli.default_host'), 
      'username' => ini_get('mysqli.default_user'),
      'password' => ini_get('mysqli.default_pw'),
      'database' => '',
      'port'     => ini_get("mysqli.default_port"),
      'socket'   => ini_get("mysqli.default_socket")       
    )
    ));

    parent::_initialize($config); 
  }     
  
  public function connect()
  { 
    $oldErrorReporting = error_reporting(0);
      
    $mysqli = new mysqli(
      $this->_options->host, 
      $this->_options->username, 
      $this->_options->password,
      $this->_options->database, 
      $this->_options->port, 
      $this->_options->socket    
    );
      
    error_reporting($oldErrorReporting);
    
    if(mysqli_connect_errno())
      throw new KDatabaseAdapterException('Connect failed: (' . mysqli_connect_errno() . ') ' . mysqli_connect_error(), mysqli_connect_errno());
      
    if(defined('MYSQLI_OPT_INT_AND_FLOAT_NATIVE')) 
      $mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
 
    $this->_connection = $mysqli;
    $this->_connected  = true;  
    $this->_database   = $this->_options->database;
    
    return $this;
  }   
  
  public function disconnect()
  {
    if($this->isConnected()) 
    {
      $this->_connection->close();
      $this->_connection = null;
      $this->_connected  = false;     
    }   
    
    return $this;
  }  
  
  public function isConnected() 
  {   
    return ($this->_connection instanceof MySQLi) && @$this->_connection->ping();
  }     
  
  public function setConnection($resource)
  {
    if(!($resource instanceof MySQLi))
      throw new KDatabaseAdapterException('Not a MySQLi connection');
      
    $this->_connection = $resource;
    return $this;
  }    
  
  public function getDatabase()
  {
    if(!isset($this->_database)) 
      $database = $this->select("SELECT DATABASE()", KDatabase::FETCH_FIELD);

    return $this->_database;  
  }      
  
  public function setDatabase($database)
  {
    if(!$this->_connection->select_db($database))
      throw new KDatabaseException('Could not connect with database : '.$database);
  
    $this->_database = $database;
    return $this; 
  }  
  
  public function getTableSchema($table)
  {
    if(!isset($this->_table_schema[$table]))
    {
      $this->_table_schema[$table] = $this->_fetchTableInfo($table);
      $this->_table_schema[$table]->indexes = $this->_fetchTableIndexes($table);
      $this->_table_schema[$table]->columns = $this->_fetchTableColumns($table);  
    }

    return $this->_table_schema[$table];
  }    
  
  protected function _fetchField($result)
  {
    $return = null;
    if($row = $result->fetch_row())
      $return = $row[0];
    
    $result->free();
    
    return $return;
  }
  
  protected function _fetchFieldList($result)
  {
    $array = array();

    while ($row = $result->fetch_row( )) {
      $array[] = $row[0];
    }

    $result->free();

    return $array;
  }  
  
  protected function _fetchArray($result)
  {
    $array = $result->fetch_assoc( );
    $result->free();
    
    return $array;
  }
  
  protected function _fetchArrayList($result, $key = '')
  {
    $array = array();  
    
    while($row = $result->fetch_assoc()) {
      if($key) $array[$row[$key]] = $row;
      else $array[] = $row; 
    }
    
    $result->free();
    
    return $array;
  }
  
  protected function _fetchObject($result)
  {
    $object = $result->fetch_object();
    $result->free();
    
    return $object;
  } 
  
  protected function _fetchObjectList($result, $key='')
  {
    $array = array();
    while($row = $result->fetch_object()) {
      if($key) $array[$row->$key] = $row;
      else $array[] = $row;
    }
    
    $result->free();
    
    return $array;
  } 
  
  protected function _quoteValue($value)
  {
    $value =  '\''.mysqli_real_escape_string( $this->_connection, $value ).'\'';  
    return $value;    
  }  
  
  protected function _fetchTableInfo($table)
  {
    $result = null;
    $sql    = $this->quoteValue($this->getTablePrefix().$table);
      
    if($info = $this->show( 'SHOW TABLE STATUS LIKE '.$sql, KDatabase::FETCH_OBJECT ))
      $result = $this->_parseTableInfo($info);
    
    return $result;
  }   
  
  protected function _fetchTableColumns($table)
  {
    $result = array();
    $sql    = $this->quoteName($this->getTablePrefix().$table);

    if($columns = $this->show( 'SHOW FULL COLUMNS FROM '.$sql, KDatabase::FETCH_OBJECT_LIST))
    {
      foreach($columns as $column) 
      {
        $column->Table = $table;
        $column = $this->_parseColumnInfo($column, $table);
        $result[$column->name] = $column;         
      }     
    }   

    return $result;   
  }
  
  protected function _fetchTableIndexes($table)
  {
    $result = array();
    $sql    = $this->quoteName($this->getTablePrefix().$table);

    if($indexes = $this->show('SHOW INDEX FROM '.$sql , KDatabase::FETCH_OBJECT_LIST))
    {
      foreach ($indexes as $index) {
        $result[$index->Key_name][$index->Seq_in_index] = $index;
      }   
    }

    return $result; 
  }
  
  protected function _parseTableInfo($info)
  {   
    $table = new KDatabaseSchemaTable;
    $table->name        = $info->Name;
    $table->engine      = $info->Engine;
    $table->type        = $info->Comment == 'VIEW' ? 'VIEW' : 'BASE';
    $table->length      = $info->Data_length;
    $table->autoinc     = $info->Auto_increment;
    $table->collation   = $info->Collation;
    $table->behaviors   = array();
    $table->description = $info->Comment != 'VIEW' ? $info->Comment : ''; 
      
    return $table;
  }  
  
  protected function _parseColumnInfo($info)
  {   
    $filter = array();
    preg_match('#@Filter\("(.*)"\)#Ui', $info->Comment, $filter);

    list($type, $length, $scope) = $this->_parseColumnType($info->Type);

    $column = new KDatabaseSchemaColumn;
    $column->name     = $info->Field;
    $column->type     = $type;
    $column->length   = ($length  ? $length  : null);
    $column->scope    = ($scope ? (int) $scope : null);
    $column->default  = $info->Default;
    $column->required = (bool) ($info->Null != 'YES');
    $column->primary  = (bool) ($info->Key == 'PRI');
    $column->unique   = (bool) ($info->Key == 'UNI' || $info->Key == 'PRI');
    $column->autoinc  = (bool) (strpos($info->Extra, 'auto_increment') !== false);
    $column->filter   =  isset($filter[1]) ? explode(',', $filter[1]) : $this->_typemap[$type];
                                                                                                                         
    if(substr($type, -3) == 'int') $column->length = null;
  
    
    if($indexes = $this->_table_schema[$info->Table]->indexes) 
    {
      foreach($indexes as $index)
      {
        if(count($index) > 1 && !$index[1]->Non_unique)
        {
          $fields = array();
          foreach($index as $field) {
            $fields[$field->Column_name] = $field->Column_name;
          }

          if(array_key_exists($column->name, $fields))
          {
            unset($fields[$column->name]);
            $column->related = array_values($fields);  

            $column->unique = true;  
            break;  
          }     
        }   
      }  
    }   

    return $column;
  }  
  
  protected function _parseColumnType($spec)
  {
    $spec    = strtolower($spec);
    $type    = null;
    $length  = null;
    $scope   = null;

    $pos = strpos($spec, '(');
    if($pos === false) $type = $spec;
    else
    {
      $type = substr($spec, 0, $pos);

      $length = trim(substr($spec, $pos), '()');

      if($type != 'enum' && $type != 'set')
      {
        $pos = strpos($length, ',');
        if ($pos !== false) {
          $scope = substr($length, $pos + 1);
          $length  = substr($length, 0, $pos);     
        } 
      } 
      else $length = explode(',', str_replace("'", "", $length));
    }

    return array($type, $length, $scope);
  }                      
}