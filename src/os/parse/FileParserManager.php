<?php

namespace Tozart\os\parse;

/**
 * Manager of file parser instances.
 *
 * @package Tozart\os\parse
 */
class FileParserManager {

  /**
   * Available file parsers.
   *
   * @var \Tozart\os\parse\FileParserInterface[]
   */
  protected $_parsers;

  /**
   * Retrieve all available file parsers.
   *
   * @return \Tozart\os\parse\FileParserInterface[]
   *   An array of file parser instances.
   */
  public function parsers() {
    return $this->_parsers;
  }

  /**
   * Create a FileParserManager instance.
   *
   * @param array $parsers
   *   An array of file parsers.
   */
  public function __construct(array $parsers) {
    foreach ($parsers as $parser) {
      $this->addParser($parser);
    }
  }

  /**
   * Add the given file parser.
   *
   * @param \Tozart\os\parse\FileParserInterface $parser
   *   A file parser object.
   */
  public function addParser(FileParserInterface $parser) {
    foreach ($parser->getSupportedTypes() as $file_type) {
      $this->_parsers[$file_type->getName()] = $parser;
    }
  }

  /**
   * Retrieve a parser which can process the given file type.
   *
   * @param string $type
   *   A file type name.
   *
   * @return \Tozart\os\parse\FileParserInterface|null
   *   A file parser object, if one exists to parse
   *   the given type.
   */
  public function getParser(string $type) {
    if (array_key_exists($type, $this->parsers())) {
      return $this->parsers()[$type];
    }
    return NULL;
  }

}
