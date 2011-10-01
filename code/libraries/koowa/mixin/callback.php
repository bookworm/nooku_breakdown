<?php

class KMixinCallback extends KMixinAbstract implements KCommandInterface 
{
  protected $_callbacks = array();
  protected $_params = array(); 
  protected $_priority;    
  
  public function __construct(KConfig $config)
  {
    parent::__construct($config);
      
    if(is_null($config->command_chain))
      throw new KMixinException('command_chain [KCommandChain] option is required');
  
    $this->_priority = $config->command_priority;

    $config->command_chain->enqueue($this);     
  }   
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'command_chain'   => null,
      'command_priority'  => KCommand::PRIORITY_HIGH
    ));
    
    parent::_initialize($config);
  } 
  
  public function execute( $name, KCommandContext $context) 
  {
    $result = true;   

    if(isset($this->_callbacks[$name])) 
    {
      $callbacks = $this->_callbacks[$name];
      $params    = $this->_params[$name]; 
    
      foreach($callbacks as $key => $callback) 
      {
        $param = $params[$key]; 

        if(is_array($param) && is_numeric(key($param))) $result = call_user_func_array($callback, $params);
        else$result = call_user_func($callback,  $context->append($param));

        if ($result === false) break;   
      }
    }

    return $result === false ? false : true;   
  }    
  
  public function getCallbacks($command)
  {
    $result = array();
    $command = strtolower($command);
    
    if (isset($this->_callbacks[$command])) $result = $this->_callbacks[$command];
    
    return $result;
  }    
  
  public function registerCallback($commands, $callback, $params = array())
  {
    $commands = (array) $commands;
    $params  = (array) KConfig::toData($params); 
    
    foreach($commands as $command)
    {
      $command = strtolower($command);
    
      if(!isset($this->_callbacks[$command]) ) 
      {
        $this->_callbacks[$command] = array();
        $this->_params[$command]   = array();   
      }        
      
      $index = array_search($callback, $this->_callbacks[$command], true);

      if($index === false) { 
        $this->_callbacks[$command][] = $callback;
        $this->_params[$command][]    = $params; 
      }     
      else {
         $this->_params[$command][$index] = array_merge($this->_params[$command][$index], $params); 
      }
    }
    
    return $this->_mixer;
  }

  public function unregisterCallback($commands, $callback)
  {
    $commands = (array) $commands;

    foreach($commands as $command)
    {
      $command = strtolower($command);

      if (isset($this->_callbacks[$command]) ) 
      {
        $key = array_search($callback, $this->_callbacks[$command], true);
        unset($this->_callbacks[$command][$key]);
        unset($this->_params[$command][$key]);         
      }
    }

    return $this->_mixer; 
  } 
    
  public function getMixableMethods(KObject $mixer = null) 
  {
    return array_diff(parent::getMixableMethods(), array('execute', 'getPriority'));  
  }

  public function getPriority()
  {
    return $this->_priority;
  }       
}