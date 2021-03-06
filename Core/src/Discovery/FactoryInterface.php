<?php

namespace Trupal\Core\Discovery;

/**
 * Interface for factory implementations.
 *
 * @package Trupal\Core\Discovery
 */
interface FactoryInterface {

  /**
   * Create an instance based on the given parameters.
   *
   * @param string $key
   *   A key identifying the resource that should be created.
   *
   * @return mixed
   *   An object.
   */
  public function create($key);

}
