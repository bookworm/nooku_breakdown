<?php     

class KObjectDecorator extends KObject
{
  private $__methods = array();
  protected $_object; 

  public function __construct($object)
  {
    $this->_object = $object;
  }

  public function getObject()
  {
    return $this->_object;
  }

  public function setObject($object)
  {
    $this->_object = $object;
    return $this;
  }

  public function getMethods()
  {
    if(!$this->__methods)
    {
      $methods = array();
      $object  = $this->getObject();

      if(!($object instanceof KObject)) 
      {
        $reflection = new ReflectionClass($object);
        foreach($reflection->getMethods() as $method) {
          $methods[] = $method->name;
        }  
      } 
      else $methods = $object->getMethods();
        
      $this->__methods = array_merge(parent::getMethods(), $methods);
    }
      
    return $this->__methods;
  }

  public function inherits($class)
  {    
    $result = false;
    $object = $this->getObject();

    if($object instanceof KObject)
    $result = $object->inherits($class);
    else 
    $result = $object instanceof $class;

    return $result;          
  }

  public function __set($key, $value)
  {
    $this->getObject()->$key = $value;
  }

  public function __get($key)
  {
    return $this->getObject()->$key;
  }

  public function __isset($key)
  {
    return isset($this->getObject()->$key);
  }

  public function __unset($key)
  {
    if (isset($this->getObject()->$key)) unset($this->getObject()->$key);
  }

  public function __call($method, array $arguments)
  {
    $object = $this->getObject();

    if($object instanceof KObject) {
      $methods = $object->getMethods();
      $exists  = in_array($method, $methods);
    }
    else $exists = method_exists($object, $method);

    if($exists)
    {
      $result = null;

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

      $class = get_class($object);
      if ($result instanceof $class) return $this;

      return $result;
    }

    return parent::__call($method, $arguments);
  }
}