<?php

class KFilterString extends KFilterAbstract
{
  protected function _validate($value)
  {
    $value = trim($value);
    return (is_string($value) && ($value === filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES)));
  }

  protected function _sanitize($value)
  {
    return filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
  }
}