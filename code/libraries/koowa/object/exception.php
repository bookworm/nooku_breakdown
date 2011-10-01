<?php 

class KObjectException extends KException 
{
  public function __construct($message = null, $code = 0, Exception $previous = null)
  {
    parent::__construct($message, $code, $previous);

    $traces = $this->getTrace();

    if($traces[0]['function'] == '__call') 
    {
      foreach($traces as $trace)
      {
        if($trace['function'] != '__call')
        {
          $this->message = "Call to undefined method : ".$trace['class'].$trace['type'].$trace['function'];
          $this->file    = $trace['file'];
          $this->line    = $trace['line'];
          break; 
        }    
      }  
    }  
  }   
}