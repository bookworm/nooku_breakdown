<?php

class KEventListener extends KObject implements KObjectIdentifiable
{
  private $__event_handlers;
  protected $_priority; 
  
  public function __construct(KConfig $config)
	{
    parent::__construct($config);

    $this->_priority = $config->priority;

    if($config->auto_connect) $this->connect($config->dispatcher); 
	}    
	
	protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'dispatcher'   => KFactory::get('lib.koowa.event.dispatcher'),
      'auto_connect' => true,
      'priority'     => KCommand::PRIORITY_NORMAL   
    ));

    parent::_initialize($config);  
  }  
  
  public function getIdentifier()
  {
    return $this->_identifier;
  }   
  
  public function getEventHandlers()
  {
    if(!$this->__event_handlers)
    {
      $handlers  = array();

      $reflection = new ReflectionClass($this);
      foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if(substr($method->name, 0, 2) == 'on')
          $handlers[] = $method->name;       
      }
  
      $this->__event_handlers = $handlers; 
    }
    
    return $this->__event_handlers; 
  } 
  
  public function connect(KEventDispatcher $dispatcher)
  {
    $handlers = $this->getEventHandlers();

    foreach($handlers as $handler) {
      $dispatcher->addEventListener($handler, $this, $this->_priority);    
    }

    return $this; 
  }

  public function disconnect(KEventDispatcher $dispatcher)
  {
    $handlers = $this->getEventHandlers();

    foreach($handlers as $handler) {
      $dispatcher->removeEventListener($handler, $this);    
    }

    return $this;  
  }
}