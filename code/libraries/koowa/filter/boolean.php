<?php

class KFilterBoolean extends KFilterAbstract
{
  protected function _validate($value)
	{
		return (null !== filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
	}
	
	protected function _sanitize($value)
	{
		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}
}