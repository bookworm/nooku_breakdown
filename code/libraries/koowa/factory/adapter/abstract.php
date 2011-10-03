<?php

abstract class KFactoryAdapterAbstract extends KObject implements KFactoryAdapterInterface
{
  protected $_priority;

  public function __construct( KConfig $config = null) 
  { 
    if(!isset($config)) $config = new KConfig();
    parent::__construct($config);
    $this->_priority = $config->priority;  
  }

  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'priority'   => KCommand::PRIORITY_NORMAL,
    ));

    parent::_initialize($config);    
  }

  final public function execute($identifier, KCommandContext $context)
  {
    $result = $this->instantiate($identifier, $context->config);
    return $result;
  }

  public function getPriority()
  {
    return $this->_priority;
  }
}