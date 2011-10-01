<?php 

class KMixinEventdispatcher extends KMixinAbstract
{
  protected $_event_dispatcher;

  public function __construct(KConfig $config)
  {
    parent::__construct($config);
    $this->_event_dispatcher = $config->event_dispatcher;   
  }  
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'event_dispatcher' => new KEventDispatcher(),
    ));

    parent::_initialize($config);     
  }
  
  public function getEventDispatcher()
  {
    return $this->_event_dispatcher;
  }

  public function setEventDispatcher(KEventDispatcher $dispatcher)
  {
    $this->_event_dispatcher = $dispatcher;
    return $this->_mixer;
  }

  public function addEventListener($event, KObjectHandable $listener, $priority = KEvent::PRIORITY_NORMAL)
  {
    $this->_event_dispatcher->addEventListener($event, $listener, $priority);
    return $this->_mixer;
  }

  public function removeEventListener($event, KObjectHandable $listener)
  {
    $this->_event_dispatcher->removeEventListener($event, $listener, $priority);
    return $this->_mixer;
  } 
}