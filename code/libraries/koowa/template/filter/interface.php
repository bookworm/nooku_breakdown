<?php 

interface KTemplateFilterInterface extends KCommandInterface, KObjectIdentifiable
{
  public function getTemplate();
}