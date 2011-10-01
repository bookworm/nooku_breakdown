<?php

class KControllerBehaviorDiscoverable extends KControllerBehaviorAbstract
{
  protected function _actionOptions(KCommandContext $context)
	{
    $methods = array();
        
    $actions = array_diff($this->getActions(), array('browse', 'read', 'display'));

    foreach($actions as $key => $action)
    {
      if (isset( $this->_action_map[$action] )) 
        $action = $this->_action_map[$action];

      if($this->getBehavior('executable')->execute('before.'.$action, $context) === false) 
        unset($actions[$key]);
    }

    sort($actions);
      
    foreach(array('get', 'put', 'delete', 'post', 'options') as $method) {
      if(in_array($method, $actions)) 
        $methods[strtoupper($method)] = $method;
    }
  
    if(in_array('post', $methods)) {
      $actions = array_diff($actions, array('get', 'put', 'delete', 'post', 'options'));
      $methods['POST'] = array_diff($actions, $methods);
    }

    $result = implode(', ', array_keys($methods));

    foreach($methods as $method => $actions) {
     if(is_array($actions) && !empty($actions)) 
       $result = str_replace($method, $method.' ['.implode(', ', $actions).']', $result); 
    }

    $context->headers = array('Allow' => $result); 
	}
}