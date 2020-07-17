<?php

namespace Tozart\Discovery;

use Tozart\Discovery\Filter\SubjectValidationFilter;

/**
 * Discovery of subject specifications.
 *
 * @package Tozart\Discovery
 */
class SubjectDiscovery extends DiscoveryBase {

  /**
   * Create a new SubjectDiscovery instance.
   *
   * @param array $directories
   *   An array of directories or paths.
   * @param \Tozart\os\FileType $file_type
   *   A file type indicating the format which
   *   subjects should be declared in.
   */
  public function __construct(array $directories, $file_type) {
    parent::__construct($directories, [
      new SubjectValidationFilter($file_type),
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function findBy($key) {
    foreach ($this->discover() as $dir => $files) {
      foreach ($files as $file_path => $file) {
        /** @var \Tozart\os\File $file */
        $subject_specification = $file->parse();
        if ($subject_specification['id'] === $key) {
          return $subject_specification;
        }
      }
    }
    return FALSE;
  }

}
