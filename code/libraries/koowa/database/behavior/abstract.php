<?php 

abstract class KDatabaseBehaviorAbstract extends KMixinAbstract implements KDatabaseBehaviorInterface
{
  protected $_identifier;
  protected $_priority;  
  
  public function __construct(KConfig $config = null) 
  { 
    $this->_identifier = $config->identifier;
    parent::__construct($config);
  
    $this->_priority = $config->priority;
  
    if($config->auto_mixin) 
      $this->mixin($this);       
  }      
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'priority'   => KCommand::PRIORITY_NORMAL,
      'auto_mixin' => false    
    ));   

    parent::_initialize($config);
  }  
  
  public function getIdentifier()
  {
    return $this->_identifier;
  }

  public function getPriority()
  {
    return $this->_priority;
  } 
  
  final public function execute($name, KCommandContext $context) 
  {
    $identifier = clone $context->caller->getIdentifier();
    $type       = array_pop($identifier->path);

    $parts  = explode('.', $name);
    $method = '_'.$parts[0].ucfirst($type).ucfirst($parts[1]);

    if(method_exists($this, $method)) 
    {
      if($context->data instanceof KDatabaseRowInterface)
        $this->setMixer($context->data);

      return $this->$method($context);   
    }

    return true;    
  }        
  
  public function save()
  {
    $this->getTable()->getCommandChain()->disable();
    $this->_mixer->save();    
    $this->getTable()->getCommandChain()->enable();

    return $this->_mixer;   
  } 
  
  public function delete()
  {
    $this->getTable()->getCommandChain()->disable();
    $this->_mixer->delete();    
    $this->getTable()->getCommandChain()->enable();

    return $this->_mixer;      
  } 
  
  public function getHandle()
  {
    $methods = $this->getMethods();

    foreach($methods as $method) {
      if(substr($method, 0, 7) == '_before' || substr($method, 0, 6) == '_after')
        return parent::getHandle(); 
    }

    return null;        
  }
  
  public function getMixableMethods(KObject $mixer = null)
  {
    $methods   = parent::getMixableMethods($mixer);
    $methods[] = 'is'.ucfirst($this->_identifier->name);
      
    return array_diff($methods, array('execute', 'save', 'delete')); 
  }
}