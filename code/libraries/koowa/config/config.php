<?php 

class KConfig implements IteratorAggregate, ArrayAccess, Countable
{
  protected $_data;     
  
  public function __construct( $config = array() )
  { 
    if ($config instanceof KConfig) $config = clone $config;
  
    $this->_data = array();
    foreach ($config as $key => $value) {
      $this->__set($key, $value);
    }    
  } 
  
  public function __clone()
  {
    $array = array();
    foreach ($this->_data as $key => $value) 
    {
      if ($value instanceof KConfig) $array[$key] = clone $value;
      else $array[$key] = $value;           
    }
  
    $this->_data = $array;  
  } 
  
  public function get($name, $default = null)
  {
    $result = $default;
    if(isset($this->_data[$name]))
      $result = $this->_data[$name];

    return $result;   
  }                  

  public function __get($name)
  {
    return $this->get($name);
  }
  
  public function __set($name, $value)
  {
    if (is_array($value))
      $this->_data[$name] = new self($value);
    else
      $this->_data[$name] = $value;   
  }
  
  public function __isset($name)
  {
    return isset($this->_data[$name]);
  }
 
  public function __unset($name)
  {
    unset($this->_data[$name]);
  }
  
  public function getIterator() 
  {
    return new ArrayIterator($this->_data);
  }

  public function count()
  {
    return count($this->_data);
  }
  
  public function offsetExists($offset)
  {
    return isset($this->_data[$offset]);
  }
  
  public function offsetGet($offset)
  {
    $result = null;
    if(isset($this->_data[$offset])) 
    { 
      $result = $this->_data[$offset];
      if($result instanceof KConfig) $result = $result->toArray();  
    } 

    return $result; 
  } 

  public function offsetSet($offset, $value)
  {
    $this->_data[$offset] = $value;
    return $this;
  }

  public function offsetUnset($offset)
  {
    unset($this->_data[$offset]);
    return $this;
  }  

  public function toArray()
  {
    $array = array(); 
    $data  = $this->_data;
    foreach ($data as $key => $value) 
    {
      if ($value instanceof KConfig) $array[$key] = $value->toArray();
      else $array[$key] = $value;       
    }

    return $array;  
  }

  public function toJson()
  {
    return json_encode($this->toArray());
  }

  public static function toData($data)
  {
    return ($data instanceof KConfig) ? $data->toArray() : $data;
  } 
    
  public function append($config)
  {
    $config = KConfig::toData($config); 

    if(is_array($config))
    {
      if(!is_numeric(key($config))) 
      {
        foreach($config as $key => $value) 
        {
            if(array_key_exists($key, $this->_data)) 
            {
              if(!empty($value) && ($this->_data[$key] instanceof KConfig)) {
                $this->_data[$key] = $this->_data[$key]->append($value);
              } 
            } 
            else $this->__set($key, $value);       
        }
      }
      else 
      {
        foreach($config as $value) 
        { 
          if (!in_array($value, $this->_data, true)) {
            $this->_data[] = $value; 
          }      
        }  
      }   
    }
 
    return $this;
  }    

  public function __toString()
  {
    return $this->toJson();
  }   
}