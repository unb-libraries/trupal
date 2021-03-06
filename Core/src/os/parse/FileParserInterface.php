<?php

namespace Trupal\Core\os\parse;

use Trupal\Core\os\FileInterface;

/**
 * Interface for file parser implementations.
 *
 * @package Trupal\Core\os\parse
 */
interface FileParserInterface {

  /**
   * Retrieve the parser's identifier.
   *
   * @return string
   *   A string.
   */
  public static function getId();

  /**
   * Retrieve the definition of the parser.
   *
   * @return array
   *   An array.
   */
  public static function getSpecification();

  /**
   * Retrieve the file's content in a structured form.
   *
   * @param string $filepath
   *   The file to parse.
   *
   * @return mixed
   *   The parsed content.
   */
  public function parse($filepath);

  /**
   * Retrieve the file types this parser can process.
   *
   * @return \Trupal\Core\os\FileTypeInterface[]
   *   An array of file types.
   */
  public function getSupportedTypes();

}
