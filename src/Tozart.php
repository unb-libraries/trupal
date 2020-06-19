<?php

namespace Tozart;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Where it all begins.
 *
 * @package Tozart
 */
final class Tozart {

  const PROJECT_ROOT = __DIR__ . DIRECTORY_SEPARATOR . '..';
  const CONFIG_DIR = self::PROJECT_ROOT . DIRECTORY_SEPARATOR . 'config';

  /**
   * The application container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected static $_container;

  /**
   * The only Tozart that should ever exist.
   *
   * @var \Tozart\Tozart
   */
  protected static $_instance;

  /**
   * Create (or retrieve) the only Tozart that should ever exist.
   *
   * @return \Tozart\Tozart
   *   A Tozart instance.
   */
  public static function create() {
    if (!static::$_instance) {
      static::$_instance = new static();
    }
    return static::$_instance;
  }

  /**
   * Create a new TestGen instance.
   */
  private function __construct() {
    static::initContainer();
  }

  /**
   * Initialize the application. Load components.
   */
  protected static function initContainer() {
    $container = new ContainerBuilder();
    try {
      $loader = new YamlFileLoader($container, new FileLocator(self::CONFIG_DIR));
      $loader->load('services.yml');

      $container->setParameter('TOZART_ROOT', defined('TOZART_ROOT')
        ? TOZART_ROOT
        : self::PROJECT_ROOT);
      $container->setParameter('MODEL_ROOT', defined('MODEL_ROOT')
        ? MODEL_ROOT
        : rtrim($container->getParameter('TOZART_ROOT')) . DIRECTORY_SEPARATOR . 'models');
      $container->setParameter('SUBJECT_ROOT', defined('SUBJECT_ROOT')
        ? SUBJECT_ROOT
        : rtrim($container->getParameter('TOZART_ROOT')) . DIRECTORY_SEPARATOR . 'subjects');
      $container->setParameter('TEMPLATE_ROOT', defined('TEMPLATE_ROOT')
        ? TEMPLATE_ROOT
        : rtrim($container->getParameter('TOZART_ROOT')) . DIRECTORY_SEPARATOR . 'templates');
    }
    catch (\Exception $e) {
    }
    finally {
      static::$_container = $container;
    }
  }

  /**
   * Retrieve the application container.
   *
   * @return \Symfony\Component\DependencyInjection\ContainerInterface
   *   A service container.
   */
  public static function container() {
    if (!isset(static::$_container)) {
      static::initContainer();
    }
    return static::$_container;
  }

  /**
   * The file system service.
   *
   * @return \Tozart\os\FileSystem
   *   A file system service instance.
   */
  public static function fileSystem() {
    /** @var \Tozart\os\FileSystem $file_system */
    $file_system = static::container()->get('file_system');
    return $file_system;
  }

  /**
   * The model factory service.
   *
   * @return \Tozart\Model\ModelFactory
   *   A model factory service instance.
   */
  public static function modelFactory() {
    /** @var \Tozart\Model\ModelFactory $model_factory */
    $model_factory = static::container()->get('model.factory');
    return $model_factory;
  }

  /**
   * The model root directory.
   *
   * @return \Tozart\os\Directory
   *   A directory object.
   */
  public function modelRoot() {
    $model_root_path = $this->container()->getParameter('MODEL_ROOT');
    return $this->fileSystem()->dir($model_root_path);
  }

  /**
   * The subject root directory.
   *
   * @return \Tozart\os\Directory
   *   A directory object.
   */
  public function subjectRoot() {
    $subject_root_path = $this->container()->getParameter('SUBJECT_ROOT');
    return $this->fileSystem()->dir($subject_root_path);
  }

  /**
   * The template root directory.
   *
   * @return \Tozart\os\Directory
   *   A directory object.
   */
  public function templateRoot() {
    $template_root_path = $this->container()->getParameter('TEMPLATE_ROOT');
    return $this->fileSystem()->dir($template_root_path);
  }

  /**
   * The file parser manager service.
   *
   * @return \Tozart\os\parse\FileParserManager
   *   A file parser manager service instance.
   */
  public static function fileParserManager() {
    /** @var \Tozart\os\parse\FileParserManager $file_parser_manager */
    $file_parser_manager = static::container()->get('file_system.parser.manager');
    return $file_parser_manager;
  }

  /**
   * The subject discovery service.
   *
   * @return \Tozart\Discovery\SubjectDiscovery
   *   A subject discovery service instance.
   */
  public static function subjectDiscovery() {
    /** @var \Tozart\Discovery\SubjectDiscovery $subject_discovery */
    $subject_discovery = static::container()->get('subject.discovery');
    return $subject_discovery;
  }

  /**
   * The subject manager service.
   *
   * @return \Tozart\Subject\SubjectManager
   *   A subject manager service instance.
   */
  public static function subjectManager() {
    /** @var \Tozart\Subject\SubjectManager $subject_manager */
    $subject_manager = static::container()->get('subject_manager');
    return $subject_manager;
  }

  /**
   * The subject factory service.
   *
   * @return \Tozart\Subject\SubjectFactory
   *   A subject factory service instance.
   */
  public static function subjectFactory() {
    /** @var \Tozart\Subject\SubjectFactory $factory */
    $factory = static::container()->get('subject_factory');
    return $factory;
  }

  /**
   * Write tests for all available subjects.
   *
   * @param mixed $dir
   *   The output directory or path.
   */
  public function write($dir) {
    if (is_string($dir)) {
      $dir = $this->fileSystem()->dir($dir);
    }
    $this->templateLocator()->setFileExtension(
      $this->printer()->templateFileExtension());
    foreach ($this->subjectManager()->subjects() as $subject_id => $subject) {
      $template = $this->templateLocator()->getTemplate($subject);
      $test_case = $dir->put($template->name());
      $content = $this->printer()
        ->render($template, $subject->getProperties());
      $test_case->setContent($content);
    }
  }

  /**
   * The template locator service.
   *
   * @return \Tozart\render\TemplateDiscovery
   *   A template locator service instance.
   */
  public static function templateLocator() {
    /** @var \Tozart\render\TemplateDiscovery $template_locator */
    $template_locator = static::container()->get('template_locator');
    return $template_locator;
  }

  /**
   * The printer service.
   *
   * @return \Tozart\render\Printer
   *   A printer service instance.
   */
  public static function printer() {
    /** @var \Tozart\render\Printer $printer */
    $printer = static::container()->get('printer');
    return $printer;
  }

}
