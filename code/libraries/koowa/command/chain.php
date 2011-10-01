<?php

class KCommandChain extends KObjectQueue
{
  protected $_enabled = true;
  protected $_break_condition = false;
  protected $_context = null;         
  
  public function __construct(KConfig $config = null)
  {
    if(!isset($config)) $config = new KConfig();

    parent::__construct($config);

    $this->_break_condition = (boolean) $config->break_condition;
    $this->_enabled         = (boolean) $config->enabled;
    $this->_context         = $config->context;      
  }
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'context'         =>  new KCommandContext(),
      'enabled'         =>  true,
      'break_condition' =>  false,
    ));

    parent::_initialize($config);        
  }   
  
  public function enqueue(KCommandInterface $cmd, $priority = null)
  {
    $priority =  is_int($priority) ? $priority : $cmd->getPriority();
    return parent::enqueue($cmd, $priority);   
  }
  
  public function run( $name, KCommandContext $context )
  {
    if($this->_enabled)
    { 
      foreach($this as $command) 
      {
        if ( $command->execute( $name, $context ) === $this->_break_condition) {
          return $this->_break_condition;
        } 
      } 
    }  
  }     
  
  public function enable()
  {
    $this->_enabled = true;
    return $this;     
  } 
  
  public function disable()
  {
    $this->_enabled = false;
    return $this;
  }

  public function setPriority(KCommandInterface $cmd, $priority)
  {
    return parent::setPriority($cmd, $priority);
  }
  
  public function getPriority(KCommandInterface $cmd)
  {
    return parent::getPriority($cmd);
  }
  
  public function getContext()
  {   
    return clone $this->_context;
  }
}