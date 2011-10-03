<?php

class KFilterInternalurl extends KFilterAbstract
{
  protected function _validate($value)
  {
    if(!is_string($value)) return false;
      
    if(stripos($value, (string)  dirname(KRequest::url()->get(KHttpUrl::BASE))) !== 0)
      return false;

    return true;     
  }

  protected function _sanitize($value)
  {
    return filter_var($value, FILTER_SANITIZE_URL);
  }   
}

