<?php 

class KObject implements KObjectHandlable  
{
  private $__methods        = array();
  protected $_mixed_methods = array();     
  protected $_identifier;
  
  public function __construct(KConfig $config = null)
  {
    if($this instanceof KObjectIdentifiable) {
      $this->_identifier = $config->identifier;
    }
    
    if($config) {
      $this->_initialize($config);
    }
  }     
  
  protected function _initialize(KConfig $config)
  {
    # Do nothing
  }  
  
  public function set($property, $value = null)
  {
    if(is_object($property))
      $property = get_object_vars($property);  
      
    if(is_array($property)) 
    {
      foreach ($property as $k => $v) {
        $this->set($k, $v);
      }  
    }
    else 
    {
      if('_' == substr($property, 0, 1))
        throw new KObjectException("Protected or private properties can't be set outside of object scope in ".get_class($this));

      $this->$property = $value;           
    }

    return $this;
  }  
  
  public function get($property = null, $default = null)
  {
    $result = $default;

    if(is_null($property)) 
    {
      $result  = get_object_vars($this);
  
      foreach ($result as $key => $value)
      {
        if ('_' == substr($key, 0, 1)) {
          unset($result[$key]);
        }      
      }
    } 
    else
    {
      if(isset($this->$property)) {
        $result = $this->$property;
      }
    }

    return $result;  
  }   
  
  public function mixin(KMixinInterface $object)
  {
    $methods = $object->getMixableMethods($this);

    foreach($methods as $method) {
      $this->_mixed_methods[$method] = $object;
    }

    $object->setMixer($this);

    return $this;  
  }        
  
  public function inherits($class)
  {
    if ($this instanceof $class) return true;

    $objects = array_values($this->_mixed_methods);

    foreach($objects as $object) {   
      if($object instanceof $class) return true;
    }

    return false;
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

      $this->__methods = array_merge($methods, array_keys($this->_mixed_methods));                        
    }

    return $this->__methods;   
  }    
  
  public function __call($method, array $arguments)
  {
    if(isset($this->_mixed_methods[$method])) 
    {
      $object = $this->_mixed_methods[$method];
      $result = null;
    
      $object->setMixer($this);
    
      switch(count($arguments)) 
      { 
        case 0 :
          $result = $object->$method();
          break;
        case 1 : 
          $result = $object->$method($arguments[0]); 
          break; 
        case 2: 
          $result = $object->$method($arguments[0], $arguments[1]); 
          break; 
        case 3: 
          $result = $object->$method($arguments[0], $arguments[1], $arguments[2]); 
          break; 
        default: 
          $result = call_user_func_array(array($object, $method), $arguments);                               
      } 
     
      return $result;   
    }
  
    throw new BadMethodCallException('Call to undefined method :'.$method);     
  }   
}