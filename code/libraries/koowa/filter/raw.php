<?php

class KFilterRaw extends KFilterAbstract
{
  protected function _validate($value)
  {
    return true;
  }

  protected function _sanitize($value)
  {
    return $value;
  }   
}