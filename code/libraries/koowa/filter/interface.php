<?php

interface KFilterInterface extends KCommandInterface
{
  public function validate($value);
  public function sanitize($value);
}