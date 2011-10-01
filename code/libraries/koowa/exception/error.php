<?php

class KExceptionError extends ErrorException implements KExceptionInterface
{
  public function __construct($message, $code, $severity, $filename, $lineno)
  {
    if (!$message) 
      throw new $this('Unknown '. get_class($this));

    parent::__construct($message, $code, $severity, $filename, $lineno);    
  }

  public function __toString()
  {        
    return "exception '".get_class($this) ."' with message '".$this->getMessage()
          ."' in ".$this->getFile().":".$this->getLine()
          ."\nStack trace:\n"
          . "  " . str_replace("\n", "\n  ", $this->getTraceAsString());  
  } 
}