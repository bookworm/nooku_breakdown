<?php

interface KMixinInterface extends KObjectHandlable
{   
  public function getMixableMethods();
  public function getMixer();
  public function setMixer($mixer);     
}