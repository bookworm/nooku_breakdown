<?php  

class KException extends Exception implements KExceptionInterface
{
  private $_previous = null;
  
  public function __construct($message = null, $code = KHttpResponse::INTERNAL_SERVER_ERROR, Exception $previous = null)
  {
    if (!$message)
      throw new $this('Unknown '. get_class($this));

    if (version_compare(PHP_VERSION, '5.3.0', '<')) {
      parent::__construct($message, (int) $code);     
      $this->_previous = $previous;        
    } 
    else 
      parent::__construct($message, (int) $code, $previous);
  }    
  
  protected function _getPrevious()
  {
    return $this->_previous;
  }
  
  public function __toString()
  {
    return "Exception '".get_class($this) ."' with message '".$this->getMessage()."' in ".$this->getFile().":".$this->getLine();
  }
}