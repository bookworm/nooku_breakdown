<?php

abstract class KMixinAbstract implements KMixinInterface
{
  protected $_mixer;
  private $__methods = array();
  private $__mixable_methods;   
  
  public function __construct(KConfig $config)
  {
    if(!empty($config))
      $this->_initialize($config);
  
    $this->_mixer = $config->mixer;   
  }
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'mixer' =>  $this,
    ));    
  }
  
  public function getMixer()
  {
    return $this->_mixer;
  }

  public function setMixer($mixer)
  {
    $this->_mixer = $mixer;
    return $this;    
  }

  public function getHandle()
  {
    return spl_object_hash( $this );
  }   
  
  public function getMethods()
  {
    if(!$this->__methods)
    {
      $methods = array();

      $reflection = new ReflectionClass($this);
      foreach($reflection->getMethods() as $method) {
        $methods[] = $method->name;
      }

      $this->__methods = $methods;     
    }

    return $this->__methods;  
  }

  public function getMixableMethods(KObject $mixer = null)
  {
    if(!$this->__mixable_methods)
    {
      $methods = array();

      $reflection = new ReflectionClass($this);
      foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        $methods[$method->name] = $method->name;
      }

      $reflection = new ReflectionClass(__CLASS__);
      foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) 
      {
        if(isset($methods[$method->name]))
          unset($methods[$method->name]);  
      }

      $this->__mixable_methods = $methods;
    }                                     

    return $this->__mixable_methods;  
  }       
  
  public function __set($key, $value) 
  {
    $this->_mixer->$key = $value;
  }

  public function __get($key)
  {
    return $this->_mixer->$key;
  }

  public function __isset($key)
  {
    return isset($this->_mixer->$key);
  }

  public function __unset($key)
  {
    if (isset($this->_mixer->$key)) unset($this->_mixer->$key);
  }
  
  public function __call($method, array $arguments)
  {
    if(isset($this->_mixer) && !($this->_mixer instanceof $this)) 
    {
      switch(count($arguments)) 
      { 
        case 0 :
          $result = $this->_mixer->$method();
          break;
        case 1 : 
          $result = $this->_mixer->$method($arguments[0]); 
          break; 
        case 2: 
          $result = $this->_mixer->$method($arguments[0], $arguments[1]); 
          break; 
        case 3: 
          $result = $this->_mixer->$method($arguments[0], $arguments[1], $arguments[2]); 
          break; 
        default: 
          $result = call_user_func_array(array($this->_mixer, $method), $arguments);                               
      } 
 
      return $result; 
    }

    throw new BadMethodCallException('Call to undefined method :'.$method);        
  }
}