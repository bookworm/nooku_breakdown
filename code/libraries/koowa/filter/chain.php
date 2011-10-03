<?php

class KFilterChain extends KCommandChain
{
  final public function run($name, KCommandContext $context)
  {
    $function = '_'.$name;
    $result =  $this->$function($context);
    return $result;    
  }            
  
  final protected function _validate(KCommandContext $context)
  {
    foreach($this as $filter) {
      if($filter->execute( 'validate', $context ) === false)
        return false;
    }

    return true;     
  } 
  
  final protected function _sanitize(KCommandContext $context)
  {
    foreach($this as $filter) {
      $context->data = $filter->execute('sanitize', $context); 
    }

    return $context->data; 
  }
}