<?php

interface KCommandInterface extends KObjectHandlable
{
	public function execute($name, KCommandContext $context);
	public function getPriority();
}