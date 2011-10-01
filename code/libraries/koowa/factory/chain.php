<?php

class KFactoryChain extends KCommandChain
{ 
  final public function run($identifier, KCommandContext $context)
  {      
    foreach($this as $command) {       
      $result = $command->execute( $identifier, $context );
      if ($result !== false) return $result;   
    }

    return false;  
  }
}