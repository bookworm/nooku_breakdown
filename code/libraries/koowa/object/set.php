<?php 

class KObjectSet extends KObject implements Iterator, ArrayAccess, Countable, Serializable
{
  protected $_object_set = null;   
  
  public function __construct(KConfig $config = null)
  {
    if(!isset($config)) $config = new KConfig();

    parent::__construct($config);

    $this->_object_set = new ArrayObject();    
  }  
  
  public function insert( KObjectHandlable $object)
  { 
    $result = false;

    if($handle = $object->getHandle()) {
      $this->_object_set->offsetSet($handle, $object);
      $result = true;
    }

    return $result;     
  }
  
  public function extract( KObjectHandlable $object)
  {
    $handle = $object->getHandle();
  
    if($this->_object_set->offsetExists($handle)) {
      $this->_object_set->offsetUnset($handle);
    }
  
    return $this;  
  }  
  
  public function contains( KObjectHandlable $object)
  {
    return $this->_object_set->offsetExists($object->getHandle());
  }     

  public function merge( KObjectSet $set)
  {
    foreach($set as $object) {
      $this->insert($object);
    }

    return $this;     
  }

  public function offsetExists($object)
  {
    if($object instanceof KObjectHandlable) return $this->contains($object);
  }

  public function offsetGet($object)
  {       
    if($object instanceof KObjectHandlable) 
      return $this->_object_set->offsetGet($object->getHandle());
  }

  public function offsetSet($object, $data)
  {
    if($object instanceof KObjectHandlable) $this->insert($object);
    return $this;      
  }          
  
  public function offsetUnset($object)
  {
    if($object instanceof KObjectHandlable) $this->extract($object);

    return $this;      
  }

  public function serialize()
  {
    return serialize($this->_object_set);
  }

  public function unserialize($serialized)
  {
    $this->_object_set = unserialize($serialized);
  }

  public function count()
  {
    return $this->_object_set->count();
  } 
  
  public function top() 
  {
    $objects = array_values($this->_object_set->getArrayCopy());

    $object = null;
    if(isset($objects[0])) $object = $objects[0];
      
    return $object;     
  }

  public function getIterator()
  {
    return $this->_object_set->getIterator();
  }

  public function rewind() 
  {
    reset($this->_object_set);
    return $this;     
  } 

  public function valid() 
  {
    return !is_null(key($this->_object_set)); 
  } 

  public function key() 
  {
    return key($this->_object_set); 
  } 
  
  public function current() 
  {
    return current($this->_object_set); 
  } 

  public function next() 
  {
    return next($this->_object_set); 
  }

  public function toArray()
  {
    return $this->_object_set->getArrayCopy();
  }

  public function __clone()
  { 
    $this->_object_set = clone $this->_object_set;
  }  
}