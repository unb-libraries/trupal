<?php

namespace Tozart\os;

use Tozart\os\DependencyInjection\FileSystemTrait;

/**
 * Class to interact with a file in the filesystem.
 *
 * @package Tozart\os
 */
class File {

  use FileSystemTrait;

  const CONTENT_REPLACE = 0;
  const CONTENT_APPEND = 1;

  /**
   * Name of the file.
   *
   * @var string
   */
  protected $name;

  /**
   * The directory which contains the file.
   *
   * @var Directory
   */
  protected $directory;

  /**
   * The type of the file.
   *
   * @var \Tozart\os\FileType
   */
  protected $type;

  /**
   * Handle to the file.
   *
   * @var resource
   */
  protected $handle;

  /**
   * Retrieve the name of the file.
   *
   * @return string
   *   A string.
   */
  public function name() {
    return $this->name;
  }

  /**
   * Retrieve the path to the file.
   *
   * @return string
   *   A string which represents an absolute filesystem path.
   */
  public function path() {
    return $this->directory()->systemPath() . $this->name();
  }

  /**
   * Retrieve the file extension.
   *
   * @return string
   *   A file extension string, e.g. "php".
   */
  public function extension() {
    return \pathinfo($this->path())['extension'];
  }

  /**
   * Access permissions for the directory.
   *
   * @return int
   *   The directory's permissions as a numeric mode.
   *   @see \fileperms()
   *
   */
  public function permissions() {
    return \octdec(substr(sprintf('%o', \fileperms($this->path())), -3));
  }

  /**
   * Retrieve the directory.
   *
   * @return Directory
   *   A Directory instance.
   */
  public function directory() {
    return $this->directory;
  }

  /**
   * Parse the file by an appropriate parser according to its file type.
   *
   * @return mixed
   *   The parsed content.
   */
  public function parse() {
    $parser = $this->type()->parser();
    return $parser->parse($this);
  }

  /**
   * Retrieve the file's type.
   *
   * @return \Tozart\os\FileType
   *   A file type object.
   */
  public function type() {
    return $this->type;
  }

  /**
   * Retrieve the file handle.
   *
   * @return resource
   *   A handle.
   */
  protected function handle() {
    if (!isset($this->handle)) {
      if (\file_exists($this->path())) {
        $this->handle = \fopen($this->path(), 'a+');
      }
      else {
        $this->handle = \fopen($this->path(), 'x+');
        \chmod($this->path(), $this->directory()->permissions());
        \clearstatcache();
      }
    }
    return $this->handle;
  }

  /**
   * Create a new file.
   *
   * @param string $name
   *   Name of the file.
   * @param Directory $directory
   *   Directory which contains the file.
   */
  public function __construct($name, Directory $directory) {
    $this->name = $name;
    $this->directory = $directory;
    $this->handle = $this->handle();
    $this->type = $this->fileSystem()
      ->getFileType($this->extension());
  }

  /**
   * Retrieve the file contents.
   *
   * @return string
   *   String containing the complete content of the file.
   */
  public function content() {
    return \file_get_contents($this->path());
  }

  /**
   * Fill the file with the given content.
   *
   * @param string $content
   *   A string.
   * @param int $mode
   *   How to treat existing content.
   */
  public function setContent($content, $mode = self::CONTENT_REPLACE) {
    switch ($mode) {
      case self::CONTENT_APPEND:
        \fwrite($this->handle(), $content);
        break;

      default:
        \file_put_contents($this->path(), $content);
    }
  }

  /**
   * Copy the file to the given destination.
   *
   * @param Directory $destination
   *   A Directory instance.
   * @param string $name
   *   (optional) The name to use at the destination.
   *   If left blank, the same name as the source will be used.
   * @param bool $override
   *   (optional) Whether to override any existing files.
   *
   * @return File
   *   The duplicated file.
   */
  public function copy(Directory $destination, $name = '', $override = TRUE) {
    if (!$name) {
      $name = $this->name();
    }
    if ($override || !$destination->containsFile($name)) {
      if ($copy = $destination->put($name)) {
        $copy->setContent($this->content());
        return $copy;
      }
    }
    return NULL;
  }

  /**
   * String representation of the file.
   *
   * @return string
   *   A file path string.
   */
  public function __toString() {
    return $this->path();
  }

}