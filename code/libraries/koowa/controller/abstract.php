<?php 

abstract class KControllerAbstract extends KObject implements KObjectIdentifiable
{
  protected $_action_map = array();
  protected $_actions;
  protected $_dispatched;
  protected $_request = null;   
  protected $_behaviors = array();   
  
  public function __construct( KConfig $config = null)
  {
    if(!isset($config)) $config = new KConfig();

    parent::__construct($config);
    $this->_dispatched = $config->dispatched;

    $this->mixin(new KMixinCommandchain($config->append(array('mixer' => $this))));

    if(!empty($config->behaviors)) $this->addBehavior($config->behaviors);

    $this->setRequest((array) KConfig::toData($config->request)); 
  }     
  
  protected function _initialize(KConfig $config)
  {
    $config->append(array(
      'command_chain'     =>  new KCommandChain(),
      'dispatch_events'   => true,
      'enable_callbacks'  => true,
      'dispatched'    => false,
      'request'       => null,
      'behaviors'         => array(),
    ));

    parent::_initialize($config); 
  }  
  
  public function getIdentifier()
  {
    return $this->_identifier;
  }
  
  public function isDispatched()
  {
    return $this->_dispatched;
  }   
  
  public function execute($action, KCommandContext $context)
  {
    $action = strtolower($action);
  
    $context->action = $action;
    $context->caller = $this;
  
    if (isset( $this->_action_map[$action] )) 
      $command = $this->_action_map[$action];
    else $command = $action;
 
    if($this->getCommandChain()->run('before.'.$command, $context) !== false) 
    {
      $method = '_action'.ucfirst($command);

      if(!method_exists($this, $method)) 
      {
        if(!isset($this->_mixed_methods[$method]))
        {
          foreach($this->getBehaviors() as $behavior) {
            $this->mixin($behavior);
          }  
        } 

        if(isset($this->_mixed_methods[$command]))       
          $context->result = $this->_mixed_methods[$command]->execute('action.'.$command, $context);
        else
          throw new KControllerException("Can't execute '$command', method: '$method' does not exist");
        
      }
      else  $context->result = $this->$method($context);

      $this->getCommandChain()->run('after.'.$command, $context); 
    }
  
    if($context->getError() instanceof KException) 
    {
      if($context->headers) 
      {
        foreach($context->headers as $name => $value) {
          header($name.' : '.$value);
        }    
      }

      throw $context->getError();  
    }
 
    return $context->result;
  }  
  
  public function getActions($reload = false)
  {
    if(!$this->_actions || $reload)
    {
      $this->_actions = array();

      foreach($this->getMethods() as $method) {
        if(substr($method, 0, 7) == '_action') 
          $this->_actions[] = strtolower(substr($method, 7));
      }

      foreach($this->_behaviors as $behavior) 
      {
        foreach($behavior->getMethods() as $method) {
          if(substr($method, 0, 7) == '_action')
            $this->_actions[] = strtolower(substr($method, 7));  
        } 
      }

      $this->_actions = array_unique(array_merge($this->_actions, array_keys($this->_action_map))); 
    }

    return $this->_actions;
  }   
  
  public function getRequest()
  {
    return $this->_request;
  }

  public function setRequest(array $request)
  {
    $this->_request = new KConfig();
    foreach($request as $key => $value) {
      $this->$key = $value;
    }
    
    return $this;
  }       
  
  public function hasBehavior($behavior)
  { 
    return isset($this->_behaviors[$behavior]); 
  } 
  
  public function addBehavior($behaviors)
  { 
    $behaviors = (array) KConfig::toData($behaviors);

    foreach($behaviors as $behavior)
    {
      if(!($behavior instanceof KControllerBehaviorInterface))
        $behavior = $this->getBehavior($behavior);

      $this->_behaviors[$behavior->getIdentifier()->name] = $behavior;

      if($this->getCommandChain()->enqueue($behavior))
        $this->_actions = null;
    }

    return $this;    
  }   
  
  public function getBehavior($behavior, $config = array())
  {
     if(!($behavior instanceof KIdentifier))
     {
       if(is_string($behavior) && strpos($behavior, '.') === false )
       {
         $identifier = clone $this->_identifier;
         $identifier->path = array('controller', 'behavior');
         $identifier->name = $behavior;          
       }
       else $identifier = KFactory::identify($behavior);   
     }
         
     if(!isset($this->_behaviors[$identifier->name]))
       $behavior = KControllerBehavior::factory($identifier, array_merge($config, array('mixer' => $this)));
     else 
       $behavior = $this->_behaviors[$identifier->name];
     
     return $behavior;
  }    
  
  public function getBehaviors()
  {
    return $this->_behaviors;
  }   
  
  public function registerActionAlias( $alias, $action )
  {
    $alias = strtolower( $alias ); 
  
    if(!in_array($alias, $this->getActions())) $this->_action_map[$alias] = $action; 
  
    $this->getActions(true);

    return $this;  
  }
  
  public function __set($property, $value)
  {
    $this->_request->$property = $value;
  }     
  
  public function __get($property)
  {
    $result = null;   
    
    if(isset($this->_request->$property)) 
      $result = $this->_request->$property;
    
    return $result;
  }    
  
  public function __call($method, $args)
  {
    if(in_array($method, $this->getActions())) 
    {
      $data = !empty($args) ? $args[0] : array();
  
      if(!($data instanceof KCommandContext))
      {
        $context = $this->getCommandContext();
        $context->data   = $data;
        $context->result = false; 
      } 
      else $context = $data;
  
      return $this->execute($method, $context);
    }

    $parts = KInflector::explode($method);

    if($parts[0] == 'is' && isset($parts[1]))
    {
      $behavior = strtolower($parts[1]);

      if(!isset($this->_mixed_methods[$method]))
      { 
        if($this->hasBehavior($behavior)) {
          $this->mixin($this->getBehavior($behavior));
          return true;
        }

        return false;   
      }

      return true;       
    }

    return parent::__call($method, $args);   
  }
}