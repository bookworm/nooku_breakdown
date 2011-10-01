<?php

interface KLoaderAdapterInterface
{
  public function path($class);
  public function getPrefix();
  public function getBasepath();
}