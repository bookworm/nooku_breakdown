<?php

class KCommandEvent extends KCommand
{
  protected $_dispatcher;

  public function __construct( KConfig $config = null) 
  { 
    if(!isset($config)) $config = new KConfig();
    parent::__construct($config);
    $this->_dispatcher = $config->dispatcher;    
  }

  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'dispatcher' => KFactory::get('lib.koowa.event.dispatcher')
    ));

    parent::_initialize($config);         
  } 

  public function execute( $name, KCommandContext $context) 
  {
    $type = '';     

    if($context->caller)
    {
      $identifier = clone $context->caller->getIdentifier();

      if($identifier->path) $type = array_shift($identifier->path);
      else $type = $identifier->name;  
    }

    $parts = explode('.', $name);   
    $event = 'on'.ucfirst($type.KInflector::implode($parts));
       
    $this->_dispatcher->dispatchEvent($event, clone($context));

    return true; 
  }
}