<?php

namespace Tozart\Validation;

use Tozart\os\File;

class FileFormatValidator implements ValidatorInterface {

  protected $_fileType;

  /**
   * @return \Tozart\os\FileTypeInterface
   */
  public function fileType() {
    return $this->_fileType;
  }

  public static function getId() {
    return 'file_format';
  }

  public static function getSpecification() {
    return [
      'file_type' => 'yml',
    ];
  }

  public function __construct($file_type) {
    $this->_fileType = $file_type;
  }

  public static function create($configuration) {
    return new static($configuration['file_type']);
  }

  public function validate($object) {
    $errors = [];
    if (!($object instanceof File)) {
      // TODO: Use translation for string output.
      $errors[] = sprintf('FileFormatValidators expect input of type %s, %s given.',
        File::class, get_class($object));
    }
    elseif (!$object->type()) {
      $errors[] = sprintf('Unknown file type: %s', $object->path());
    }
    elseif (!$object->type()->equals($this->fileType())) {
      $errors[] = sprintf('File of type %s expected, %s given.',
        $this->fileType()->getName(), $object->type()->getName());
    }
    elseif (!$this->canParse($object)) {
      $errors[] = sprintf('File could not be parsed.');
    }

    return $errors;
  }

  protected function canParse(File $file) {
    try {
      $this->fileType()
        ->getParser()
        ->parse($file);
    }
    catch (\Exception $e) {
      return FALSE;
    }
    return TRUE;
  }

}
