<?php 

class KCommand extends KObject implements KCommandInterface 
{   
  const PRIORITY_HIGHEST = 1;
  const PRIORITY_HIGH    = 2;
  const PRIORITY_NORMAL  = 3;
  const PRIORITY_LOW     = 4;
  const PRIORITY_LOWEST  = 5;  
  
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
    
  public function execute( $name, KCommandContext $context) 
  {  
    $type = '';   

    if($context->caller)
    {
      $identifier = clone $context->caller->getIdentifier();

      if($identifier->path) $type = array_shift($identifier->path);
      else $type = $identifier->name;

      $parts  = explode('.', $name);  
      $method = !empty($type) ? '_'.$type.ucfirst(KInflector::implode($parts)) : '_'.lcfirst(KInflector::implode($parts));
    }   
    
    if(in_array($method, $this->getMethods()))
      return $this->$method($context);

    return true;      
  }   

  public function getPriority()
  {
    return $this->_priority;
  } 
}