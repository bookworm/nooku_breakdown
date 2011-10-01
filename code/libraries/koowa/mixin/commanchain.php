<?php

class KMixinCommandchain extends KMixinAbstract
{
  protected $_command_chain;
  
  public function __construct(KConfig $config)
  {
    parent::__construct($config);
      
    $this->_command_chain = $config->command_chain;
  
    if($config->enable_callbacks) 
    {
      $this->_mixer->mixin(new KMixinCallback(new KConfig(array(
        'mixer'             => $this->_mixer, 
        'command_chain'     => $this->_command_chain,
        'command_priority'  => $config->callback_priority
      ))));    
    }
  
    if($config->dispatch_events)
      $this->_command_chain->enqueue(KFactory::get('lib.koowa.command.event'), $config->event_priority);
  }
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'command_chain'     => new KCommandChain(),
      'dispatch_events'   => true,
      'event_priority'    => KCommand::PRIORITY_LOWEST,
      'enable_callbacks'  => false,
      'callback_priority' => KCommand::PRIORITY_HIGH,  
    ));

    parent::_initialize($config);
  }
  
  public function getCommandContext()
  {
    $context = $this->_command_chain->getContext();
    $context->caller = $this->_mixer;

    return $context;
  }
  
  public function getCommandChain()
  {
    return $this->_command_chain;
  }
  
  public function setCommandChain(KCommandChain $chain)
  {
    $this->_command_chain = $chain;
    return $this->_mixer;
  }
}