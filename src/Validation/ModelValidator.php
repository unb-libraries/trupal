<?php

namespace Tozart\Validation;

use Tozart\Model\ModelInterface;

/**
 * Validator for model specifications.
 *
 * @package Tozart\Validation
 */
class ModelValidator extends SpecificationValidator {

  /**
   * {@inheritDoc}
   */
  protected function essentialProperties(array $specification) {
    return [
      'type',
      'class',
    ];
  }

  /**
   * {@inheritDoc}
   */
  protected function requiredProperties(array $specification) {
    return [];
  }

  /**
   * {@inheritDoc}
   */
  protected function optionalProperties(array $specification) {
    return [
      'requirements',
      'options',
    ];
  }

  /**
   * Validation callback for the "class" property.
   *
   * @param string $class
   *   The class value.
   * @param string $property
   *   The property identifier.
   * @param array $specification
   *   The entire specification.
   *
   * @return array
   *   An array of error message strings.
   *
   * @see \Tozart\Validation\SpecificationValidator::validateProperty().
   */
  protected function validateClass($class, $property, array $specification) {
    $errors = [];
    if (!class_exists($class)) {
      $errors[] = "'{$class}' does not exist.";
    }
    elseif (!in_array(ModelInterface::class, class_implements($class))) {
      $errors[] = "{$class} must implement " . ModelInterface::class;
    }
    return $errors;
  }

  /**
   * Validation callback for the "requirements" property.
   *
   * @param array $requirements
   *   An array of required properties (strings).
   * @param string $property
   *   The property identifier.
   * @param array $specification
   *   The entire specification.
   *
   * @return array
   *   An array of error message strings.
   *
   * @see \Tozart\Validation\SpecificationValidator::validateProperty().
   */
  protected function validateRequirements(array $requirements, $property, array $specification) {
    $errors = [];
    if (!is_array($requirements)) {
      $errors[] = "{$property} must be of type \"array\"";
    }
    elseif (empty($requirements)) {
      $errors[] = "{$property} must not be empty.";
    }
    return $errors;
  }

  /**
   * Validation callback for the "options" property.
   *
   * @param array $options
   *   An array of optional properties (strings) and
   *   their default values.
   * @param string $property
   *   The property identifier.
   * @param array $specification
   *   The entire specification.
   *
   * @return array
   *   An array of error message strings.
   *
   * @see \Tozart\Validation\SpecificationValidator::validateProperty().
   */
  protected function validateOptions(array $options, $property, array $specification) {
    $errors = [];
    if (!is_array($options)) {
      $errors[] = "{$property} must be of type \"array\"";
    }
    else {
      foreach ($options as $key => $default_value) {
        if (is_numeric($key) || empty($default_value)) {
          $errors[] = "{$property} must define a default value.";
        }
      }
    }
    return $errors;
  }

}
