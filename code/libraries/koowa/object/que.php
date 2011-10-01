<?php 

class KObjectQueue extends KObject implements Iterator, Countable
{
  protected $_object_list   = null;
  protected $_priority_list = null; 
  
  public function __construct(KConfig $config = null)
  {
    if(!isset($config)) $config = new KConfig();

    parent::__construct($config);

    $this->_object_list   = new ArrayObject();
    $this->_priority_list = new ArrayObject();       
  }
  
  public function enqueue( KObjectHandlable $object, $priority)
  {
    $result = false;

    if($handle = $object->getHandle()) 
    {
      $this->_object_list->offsetSet($handle, $object);

      $this->_priority_list->offsetSet($handle, $priority);
      $this->_priority_list->asort(); 

      $result = true;
    }

    return $result;   
  }  
  
  public function dequeue( KObjectHandlable $object)
  {
    $result = false;

    if($handle = $object->getHandle())
    {
      if($this->_object_list->offsetExists($handle)) 
      {
        $this->_object_list->offsetUnset($handle);
        $this->_priority_list->offsetUnSet($handle); 

        $result = true;  
      }  
    }

    return $result;  
  }
  
  public function setPriority(KObjectHandlable $object, $priority)
  {
    if($handle = $object->getHandle())
    {
      if($this->_priority_list->offsetExists($handle)) {
        $this->_priority_list->offsetSet($handle, $priority);
        $this->_priority_list->asort(); 
      }
    }

    return $this;  
  } 
  
  public function getPriority(KObjectHandlable $object)
  {
    $result = false;

    if($handle = $object->getHandle())
    {
      if($this->_priority_list->offsetExists($handle)) {
        $result = $this->_priority_list->offsetGet($handle);
      }  
    }   

    return $result;   
  }

  public function hasPriority($priority)
  {
    $result = array_search($priority, $this->_priority_list);
    return $result;   
  }
  
  public function contains(KObjectHandlable $object)
  {
    $result = false;

    if($handle = $object->getHandle()) {
      $result = $this->_object_list->offsetExists($handle);
    }

    return $result;  
  }  
  
  public function count()
  {
    return count($this->_object_list);
  }       
  
  public function rewind() 
	{
    reset($this->_object_list);
    reset($this->_priority_list);

    return $this;   
	}    
	
	public function valid() 
	{
		return !is_null(key($this->_priority_list)); 
	} 
 
	public function key() 
	{
		return key($this->_priority_list); 
	} 
 
	public function current() 
	{
		return $this->_object_list[$this->key()]; 
	} 
 
	public function next() 
	{
		return next($this->_priority_list); 
	}  
	
	public function top() 
	{
    $handles = array_keys((array)$this->_priority_list);

    $object = null;
    if(isset($handles[0])) {
      $object  = $this->_object_list[$handles[0]];
    }

    return $object;   
	} 
	
  public function isEmpty()
  {
    return !count($this->_object_list);
  }

  public function __clone()
  { 
    $this->_object_list   = clone $this->_object_list;
    $this->_priority_list = clone $this->_priority_list;
  }  
}