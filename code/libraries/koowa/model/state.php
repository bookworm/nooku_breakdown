<?php 

class KModelState extends KModelAbstract
{
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'state'      => array(),
    )); 
  }
  
  public function __get($name)
  {
    if(isset($this->_state[$name])) 
      return $this->_state[$name]->value;

    return null;
  }
  
  public function __set($name, $value)
  {
    if(isset($this->_state[$name]))
      $this->_state[$name]->value = $value;    
  } 
  
  public function __isset($name)
  {
    return isset($this->_state[$name]);
  }  
  
  public function __unset($name)
  {
    if(isset($this->_state[$name]))
      $this->_state[$name]->value = $this->_state[$name]->default;    
  }    
  
  public function insert($name, $filter, $default = null, $unique = false, $required = array())
  {
    $state = new stdClass();
    $state->name     = $name;
    $state->filter   = $filter;
    $state->value    = $default;
    $state->unique   = $unique;
    $state->required = $required;
    $state->default  = $default;
    $this->_state[$name] = $state;

    return $this;   
  }       
  
  public function remove( $name )
  {
    unset($this->_state[$name]);
    return $this; 
  }   
  
  public function reset($default = true)
  {
    foreach($this->_state as $state) {
      $state->value = $default ? $state->default : null;
    }
  
    return $this;   
  } 
 
  public function setData(array $data)
  {
    foreach($data as $key => $value)
    {
      if(isset($this->_state[$key]))
      {
        $filter = $this->_state[$key]->filter;

        if(!($filter instanceof KFilterInterface))
          $filter = KFilter::factory($filter);

        $this->_state[$key]->value = $filter->sanitize($value);   
      }  
    }
    return $this;  
  }   
  
  public function getData($unique = false)
  {
    $data = array();

    foreach ($this->_state as $name => $state) 
    {
      if(isset($state->value))
      {
        if($unique) 
        {
          if($state->unique && $this->_validate($state)) 
          {
            $result = true;     
            
            foreach($state->required as $required) 
            {
              if(!$this->_validate($this->_state[$required])) {
                $result = false;
                break;
              }   
            }

            if($result) {
              $data[$name] = $state->value;

              foreach($state->required as $required) {
                $data[$required] = $this->_state[$required]->value;
              } 
            }  
          }  
        } 
        else $data[$name] = $state->value;    
      }      
    }
  
    return $data;  
  }  
  
  public function isUnique()
  {
    $states = $this->getData(true);
    return !empty($states);  
  }    
  
  public function isEmpty(array $exclude = array())
  {
    $states = $this->getData();
   
    foreach($exclude as $state) {
      unset($states[$state]); 
    }
  
    return (bool) (count($states) == 0);       
  }  
  
  public function toArray()
  {
    return $this->getData();
  }       
  
  protected function _validate($state)
  {
    if(empty($state->value) && !is_numeric($state->value))
      return false;
          
    if(is_array($state->value)) 
    {
      if(count($state->value) > 1)
        return false;
              
      $first = array_slice($state->value, 0, 1);
      if(empty($first) && !is_numeric($first))
        return false;
    }

    return true;     
  }
}