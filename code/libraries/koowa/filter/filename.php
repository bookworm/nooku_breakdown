<?php

class KFilterFilename extends KFilterAbstract
{ 
  protected function _validate($value)
  {
    return ((string) $value === $this->sanitize($value));
  }                     
  
  protected function _sanitize($value)
  {
    return basename($value);
  }
}