<?php 

class KControllerBehavior
{
  public static function factory($behavior, $config = array())
  {   
    if(!($behavior instanceof KControllerBehaviorInterface))
    {   
      if(is_string($behavior) && strpos($behavior, '.') === false )
        $behavior = 'com.default.controller.behavior.'.trim($behavior);  

      $behavior = KFactory::tmp($behavior, $config);

      if(!($behavior instanceof KControllerBehaviorInterface)) {
        $identifier = $behavior->getIdentifier();
        throw new KControllerBehaviorException("Controller behavior $identifier does not implement KControllerBehaviorInterface");
      }  
    }

    return $behavior;  
  }
}