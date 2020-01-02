<?php

namespace TestGen\os;

/**
 * Class to interact with a directory in the filesystem.
 *
 * @package TestGen\os
 */
class Directory extends \Directory {

  /**
   * The parent directory.
   *
   * @var Directory
   */
  protected $parent;

  /**
   * Create a new directory.
   *
   * @param $path
   *   The path to map to the directory instance. If pointing
   *   at a non-existing path, a new directory will be created.
   * @param int $permissions
   *   Permissions to assign to a new directory. Ignored if the
   *   given path points at an already existing directory.
   */
  public function __construct($path, $permissions = 0) {
    $this->path = $this->mapOrCreate($path, $permissions);
  }

  /**
   * Resolve the path and map or create a directory.
   *
   * @param $path
   *   The path to map or under which to create the directory.
   * @param int $permissions
   *   Permissions for new directories. Ignored if path points
   *   at an existing directory.
   *
   * @return string
   *   Resolved, absolute path to the directory. Ends with a path
   *   delimiter, i.e. '/' (Unix) or '\' (Windows).
   */
  protected function mapOrCreate($path, $permissions = 0) {
    if (empty($path)) {
      // TODO: This is not OS independent.
      $resolved_path = DIRECTORY_SEPARATOR;
    }
    else {
      if (!$resolved_path = \realpath($path)) {
        $segments = \explode(DIRECTORY_SEPARATOR, $path);

        $parent_segments = \array_slice(
          $segments, 0, count($segments) - 1
        );
        $unresolved_parent = \implode(
          DIRECTORY_SEPARATOR, $parent_segments
        );
        $parent = $this->mapOrCreate($unresolved_parent, $permissions);
        $dirname = $segments[count($segments) - 1];

        $resolved_path =  $parent . $dirname;
        if (!\file_exists($resolved_path)) {
          if (!$permissions) {
            $permissions = \fileperms($parent);
          }
          \mkdir($resolved_path, $permissions);
        }
      }
    }

    $resolved_path = \rtrim(
      $resolved_path, DIRECTORY_SEPARATOR
    ) . DIRECTORY_SEPARATOR;

    return $resolved_path;
  }

  /**
   * Absolute, system-wide path to the directory.
   *
   * @return string
   *   Absolute filesystem path string. Ends with a path
   *   delimiter, i.e. '/' (Unix) or '\' (Windows).
   *
   */
  public function systemPath() {
    return $this->path;
  }

  /**
   * Directory name as identified by its parent.
   *
   * @return string
   *   Relative filesystem path. Ends with a path
   *   delimiter, i.e. '/' (Unix) or '\' (Windows).
   */
  public function name() {
    $segments = \explode(DIRECTORY_SEPARATOR, $this->path);
    if (!empty($segments)) {
      $last_segment_index = \count($segments) - 1;
      $last_segment = $segments[$last_segment_index];
      if (empty($last_segment) && count($segments) > 1) {
        $last_segment = $segments[$last_segment_index -1];
      }
    } else {
      $last_segment = '';
    }
    return $last_segment . DIRECTORY_SEPARATOR;
  }

  /**
   * Retrieves or creates an instance for the parent directory.
   *
   * @return Directory
   *   A Directory instance mapping the path to the parent.
   */
  public function parent() {
    if (!isset($this->parent)) {
      $this->parent = new static($this->parentPath());
    }
    return $this->parent;
  }

  /**
   * Retrieve the path to the parent without mapping to a new Directory instance.
   *
   * @return string
   *   Absolute filesystem path string. Ends with a path
   *   delimiter, i.e. '/' (Unix) or '\' (Windows).
   */
  public function parentPath() {
    if (isset($this->parent)) {
      $path = $this->parent->path;
    }
    else {
      $segments = \explode(DIRECTORY_SEPARATOR, $this->path);
      $base = \array_slice($segments, 0, \count($segments) - 1);
      $path = \implode(DIRECTORY_SEPARATOR, $base) . DIRECTORY_SEPARATOR;
    }
    return $path;
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
    return \fileperms($this->path);
  }

  /**
   * Whether this directory can be written to.
   *
   * @return bool
   *   True if this directory allows write operations. False otherwise.
   */
  public function isWritable() {
    return \is_writable($this->systemPath());
  }

  /**
   * Put a file with the given name inside this directory.
   *
   * @param string $filename
   *   Filename without directory path.
   * @param bool $close
   *   Whether the created file should immediately be closed.
   */
  public function put($filename, $close = FALSE) {
    $filepath = $this->systemPath() . $filename;
    if ($this->isWritable()) {
      $file = \fopen($filepath, 'x+');
      \chmod($filepath, $this->permissions());
      if ($close) {
        \fclose($file);
      }
    }
  }

  /**
   * Join the directory's path with the given path.
   *
   * @param string $path
   *   Relative path from within this directory.
   *
   * @return string
   *   Absolute filesystem path.
   */
  public function join($path) {
    return $this->path . $path;
  }

}