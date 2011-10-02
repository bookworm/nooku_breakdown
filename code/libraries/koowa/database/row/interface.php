<?php

interface KDatabaseRowInterface extends KObjectIdentifiable
{
  public function getStatus();
  public function load();
  public function save();
  public function delete();
  public function count();
  public function reset();
  public function getData($modified = false);
  public function setData( $data, $modified = true );
  public function getModified();
  public function isNew();
  public function isConnected();   
}