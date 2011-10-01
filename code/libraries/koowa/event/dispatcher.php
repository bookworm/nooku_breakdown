<?php 

class KEventDispatcher extends KObject
{
	protected $_listeners; 
  protected $_event = null;  
  
  public function __construct(KConfig $config = null) 
	{
    parent::__construct($config);
    $this->_listeners = array();  
	}               
	
	public function dispatchEvent($name, $event = array())
  {
    $result = array();
  
    if(!$event instanceof KEvent) $event = new KEvent($name, $event); 
    
    if(isset($this->_listeners[$name])) 
    {
      foreach($this->_listeners[$name] as $listener) 
      {
        $listener->$name($event);
        if(!$event->canPropagate()) break;
      }
    }
  
    return $this;    
  }    
   
  public function addEventListener($name, KObjectHandlable $listener, $priority = KEvent::PRIORITY_NORMAL)
  {
    if(is_object($listener))
    {
      if(!isset($this->_listeners[$name])) $this->_listeners[$name] = new KObjectQueue();
      $this->_listeners[$name]->enqueue($listener, $priority);
    }
  
    return $this;    
  }    
  
  public function removeEventListener($name, KObjectHandable $listener)
  {
    if(is_object($listener)) {
      if(isset($this->_listeners[$name])) 
        $this->_listeners[$name]->dequeue($listener);
    }

    return $this;     
  } 
  
  public function getListeners($name)
  {
    $result = array();
    if(isset($this->_listeners[$name])) $result = $this->_listeners[$name];

    return $result;
  }   
  
  public function hasListeners($name)
  {
    $result = false;
    if(isset($this->_listeners[$name])) $result = (boolean) count($this->_listereners[$name]);

    return $result;  
  } 
  
  public function setEventPriority($name, KObjectHandable $listener, $priority)
  {
    if(isset($this->_listeners[$name])) 
      $this->_listeners[$name]->setPriority($listener, $priority);

    return $this;        
  } 
  
  public function getEventPriority($name, KObjectHandable $listener)
  {
    $result = false;
  
    if(isset($this->_listeners[$name])) 
      $result = $this->_listeners[$name]->getPriority($listener);
  
    return $result;  
  }
}