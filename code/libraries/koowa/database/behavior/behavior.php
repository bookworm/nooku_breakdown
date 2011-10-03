<?php

class KDatabaseBehavior
{ 
  public static function factory($behavior, $config = array())
  {   
    if(!($behavior instanceof KDatabaseBehaviorInterface))
    {   
      if(is_string($behavior) && strpos($behavior, '.') === false ) 
        $behavior = 'com.default.database.behavior.'.trim($behavior);  

      $behavior = KFactory::tmp($behavior, $config);

      if(!($behavior instanceof KDatabaseBehaviorInterface)) {
        $identifier = $behavior->getIdentifier();
        throw new KDatabaseBehaviorException("Database behavior $identifier does not implement KDatabaseBehaviorInterface");
      }  
    }

    return $behavior;    
  }
}