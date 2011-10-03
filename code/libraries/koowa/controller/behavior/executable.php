<?php 

class KControllerBehaviorExecutable extends KControllerBehaviorAbstract
{
  protected $_readonly;
  
  public function __construct( KConfig $config) 
  {
    parent::__construct($config);

    $this->_readonly = (bool) $config->readonly;   
  }      
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'priority'   => KCommand::PRIORITY_HIGH,
      'readonly'   => false,
      'auto_mixin' => true
    ));

    parent::_initialize($config);      
  }        
  
  public function execute($name, KCommandContext $context) 
  { 
    $parts = explode('.', $name); 

    if($parts[0] == 'before') 
    {
      $action = $parts[1];

      if(!in_array($action, $context->caller->getActions()))
      {
        $context->setError(new KControllerException(
          'Action '.ucfirst($action).' Not Implemented', KHttpResponse::NOT_IMPLEMENTED
        ));

        $context->header = array('Allow' =>  $context->caller->execute('options', $context));
        return false;        
      }
 
      $method = 'can'.ucfirst($action);

      if(method_exists($this, $method)) 
      {
        if($this->$method() === false) 
        {
          if($context->action != 'options') 
          {
            $context->setError(new KControllerException(
              'Action '.ucfirst($action).' Not Allowed', KHttpResponse::METHOD_NOT_ALLOWED
            ));       

            $context->header = array('Allow' =>  $context->caller->execute('options', $context));  
          }
          return false;  
        }  
      }  
    } 

    return true;   
  } 
  
  public function getHandle()
  {
    return KMixinAbstract::getHandle();
  }
  
  public function setReadOnly($readonly)
  {
    $this->_readonly = (bool) $readonly; 
    return $this;  
  }

  public function isReadOnly()
  {
    return $this->readonly;
  } 
  
  public function canBrowse()
  {
    return true;
  }

  public function canRead()
  {
    return true;
  }

  public function canEdit()
  {
    return !$this->_readonly;
  }

  public function canAdd()
  {
    return !$this->_readonly;
  }

  public function canDelete()
  {
     return !$this->_readonly;
  }    
}