<?php 

interface KFactoryAdapterInterface extends KCommandInterface
{
	public function instantiate($identifier, KConfig $config);
}