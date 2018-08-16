<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya;

class Autoloader {

  /**
  * prefix => path mapping array for modules/plugins.
  *
  * @var array
  */
  private static $_paths = array();

  /**
   * path => array('lowercaseclass' => '/path/class.php', ...)
   *
   * @var array
   */
  private static $_classmaps = array();

  /**
   * Pattern that matches the parts (namespaces) of a class
   * @var string
   */
  private static $classPattern = '(
      (?:\\\\[^\\\\]+)|
      (?:[A-Z][a-z\d_]+)|
      (?:[A-Z]+(?![a-z\d_]))
    )Sx';

  private static $_mapClasses = array(
    'PapayaAdministrationCommunityUsersListDialog' => Administration\Community\Users\Roster\Dialog::class,
    'PapayaConfigurationGlobal' => Configuration\GlobalValues::class,

    'PapayaContentPagesPublications' => Content\Page\Publications::class,

    'PapayaDatabaseRecordOrderList' => Database\Record\Order\Collection::class,
    'PapayaDatabaseRecordList' => Database\Record\Collection::class,

    'PapayaFilterArray' => Filter\ArrayOf::class,
    'PapayaFilterArrayAssociative' => Filter\AssociativeArray::class,
    'PapayaFilterArraySize' => Filter\ArraySize::class,
    'PapayaFilterBooleanString' => Filter\BooleanString::class,
    'PapayaFilterExceptionArrayKeyInvalid' => Filter\Exception\InvalidKey::class,
    'PapayaFilterExceptionInvalid' => Filter\Exception\InvalidValue::class,
    'PapayaFilterExceptionCallbackInvalid' => Filter\Exception\InvalidCallback::class,
    'PapayaFilterExceptionCallbackFailed' => Filter\Exception\FailedCallback::class,
    'PapayaFilterExceptionCharacterInvalid' => Filter\Exception\InvalidCharacter::class,
    'PapayaFilterExceptionEmpty' => Filter\Exception\IsEmpty::class,
    'PapayaFilterExceptionCountMismatch' => Filter\Exception\InvalidCount::class,
    'PapayaFilterExceptionLength' => Filter\Exception\InvalidLength::class,
    'PapayaFilterExceptionLengthMaximum' => Filter\Exception\InvalidLength\ToLong::class,
    'PapayaFilterExceptionLengthMinimum' => Filter\Exception\InvalidLength\ToShort::class,
    'PapayaFilterExceptionNotEmpty' => Filter\Exception\NotEmpty::class,
    'PapayaFilterExceptionNotEqual' => Filter\Exception\NotEqual::class,
    'PapayaFilterExceptionNotEnclosed' => Filter\Exception\NotIncluded::class,
    'PapayaFilterExceptionNotFloat' => Filter\Exception\NotNumeric::class,
    'PapayaFilterExceptionPartInvalid' => Filter\Exception\InvalidPart::class,
    'PapayaFilterExceptionPcre' => Filter\Exception\RegEx\NoMatch::class,
    'PapayaFilterExceptionRange' => Filter\Exception\OutOfRange::class,
    'PapayaFilterExceptionRangeMaximum' => Filter\Exception\OutOfRange\ToLarge::class,
    'PapayaFilterExceptionRangeMinimum' => Filter\Exception\OutOfRange\ToSmall::class,
    'PapayaFilterExceptionType' => Filter\Exception\UnexpectedType::class,
    'PapayaFilterExceptionUndefined' => Filter\Exception\IsUndefined::class,
    'PapayaFilterExceptionXml' => Filter\Exception\InvalidXML::class,
    'PapayaFilterFactoryExceptionInvalidFilter' => Filter\Factory\Exception\InvalidFilter::class,
    'PapayaFilterFactoryExceptionInvalidOptions' => Filter\Factory\Exception\InvalidOptions::class,
    'PapayaFilterFactoryExceptionInvalidProfile' => Filter\Factory\Exception\InvalidProfile::class,
    'PapayaFilterListKeys' => Filter\ArrayKey::class,
    'PapayaFilterListMultiple' => Filter\ArrayValues::class,
    'PapayaFilterLogicalAnd' => Filter\LogicalAnd::class,
    'PapayaFilterLogicalOr' => Filter\LogicalOr::class,
    'PapayaFilterNoLinebreak' => Filter\NoLinebreak::class,
    'PapayaFilterNotEmpty' => Filter\NotEmpty::class,
    'PapayaFilterNumericWithUnit' => Filter\NumberWithUnit::class,
    'PapayaFilterList' => Filter\ArrayElement::class,
    'PapayaFilterEmpty' => Filter\EmptyValue::class,
    'PapayaFilterIsset' => Filter\NotNull::class,
    'PapayaFilterPcre' => Filter\RegEx::class,

    'PapayaIteratorArrayMapper' => Iterator\ArrayMapper::class,
    'PapayaIteratorMultiple' => Iterator\Union::class,
    'PapayaIteratorTraversable' => Iterator\TraversableIterator::class,

    'PapayaMessageContextInterfaceList' => Message\Context\Interfaces\Items::class,
    'PapayaMessageContextInterface' => Message\Context\Data::class,
    'PapayaMessageContextList' => Message\Context\Items::class,

    'PapayaObject' => Application\BaseObject::class,
    'PapayaObjectInterface' => Application\Access::class,
    'PapayaObjectInterfacePropertiesWriteable' => BaseObject\Interfaces\Properties\Writable::class,

    'PapayaParserTag' => Template\Tag::class,
    'PapayaParserTagImage' => Template\Tag\Image::class,

    'PapayaPluginAdaptable' => Plugin\Configurable\Options::class,

    'PapayaRequestParametersInterface' => Request\Parameters\Access::class,
    'PapayaRequestParametersQuery' => Request\Parameters\QueryString::class,

    'PapayaStringUtf8' => Text\UTF8String::class,

    'PapayaTemplateEngineXsl' => Template\Engine\XSLT::class,
    'PapayaTemplateSimpleExceptionUnexpectedEof' => Template\Simple\Exception\UnexpectedEOF::class,
    'PapayaTemplateSimpleExceptionUnexpectedToken' => Template\Simple\Exception\UnexpectedToken::class,

    'PapayaUtilArrayMapper' => Utility\ArrayMapper::class,
    'PapayaUtilArray' => Utility\Arrays::class,

    'PapayaUiDialogButtonSubmitNamed' => UI\Dialog\Button\NamedSubmit::class,
    'PapayaUiDialogFieldBuilderArray' => UI\Dialog\Field\Builder\FromArray::class,
    'PapayaUiDialogFieldFactoryExceptionInvalidOption' => UI\Dialog\Field\Factory\Exception\InvalidOption::class,
    'PapayaUiDialogFieldFactoryExceptionInvalidProfile' => UI\Dialog\Field\Factory\Exception\InvalidProfile::class,
    'PapayaUiDialogFieldInputGeoPosition' => UI\Dialog\Field\Input\GeoPosition::class,
    'PapayaUiDialogFieldInputMediaImageResized' => UI\Dialog\Field\Input\Media\ImageResized::class,
    'PapayaUiListviewSubitemImageList' => UI\Listview\Subitem\Images::class,
    'PapayaUiListviewSubitemImageSelect' => UI\Listview\Subitem\Image\Toggle::class,
    'PapayaUiToolbarSet' => UI\Toolbar\Collection::class
  );

  private static $_mapParts = array(
    // Reserved Words
    'Boolean' => 'BooleanValue',
    'Float' => 'FloatValue',
    'Integer' => 'IntegerValue',
    'Object' => 'BaseObject',
    'String' => 'Text',
    'Interface' => 'Interfaces',
    'Switch' => 'Selector',
    'List' => 'Collection',

    // Typos
    'Anchestors' => 'Ancestors',
    'Ast' => 'AST',
    'Css' => 'CSS',
    'Csv' => 'CSV',
    'Http' => 'HTTP',
    'Php' => 'PHP',
    'Regex' => 'RegEx',
    'Svn' => 'SVN',
    'Ui' => 'UI',
    'Url' => 'URL',
    'Util' => 'Utility',
    'Xml' => 'XML'
  );

  /**
   *
   * @param string $name
   * @param string|NULL $file
   * @param string|NULL $alias
   * @return bool
   */
  public static function load($name, $file = NULL, $alias = NULL) {
    if (!self::exists($name, FALSE)) {
      $alternativeClass = self::convertToNamespaceClass($name);
      if (NULL !== $alternativeClass && $alternativeClass !== $name) {
        if (self::exists($alternativeClass, FALSE)) {
          class_alias($alternativeClass, $name);
          return TRUE;
        }
        if (self::load($alternativeClass, $file, $name)) {
          return TRUE;
        }
        $alias = $alternativeClass;
      }
      $file = NULL === $file ? self::getClassFile($name) : $file;
      if (NULL !== $file && file_exists($file) && is_file($file) && is_readable($file)) {
        /** @noinspection PhpIncludeInspection */
        include $file;
        if (NULL !== $alias) {
          if (self::exists($alias)) {
            class_alias($alias, $name);
          } else {
            class_alias($name, $alias);
          }
        }
      }
    }
    return self::exists($name, FALSE);
  }

  private static function exists($name, $allowAutoload = TRUE) {
    return
      class_exists($name, $allowAutoload) ||
      interface_exists($name, $allowAutoload) ||
      trait_exists($name, $allowAutoload);
  }

  /**
  * Get file for a class
  *
  * @param string $className
  * @return string|NULL
  */
  public static function getClassFile($className) {
    static $systemDirectory = NULL;
    $systemDirectory = NULL !== $systemDirectory
      ? $systemDirectory : str_replace('\\', '/', dirname(__DIR__));
    self::lazyLoadClassmap($systemDirectory);
    $key = strtolower($className);
    foreach (self::$_classmaps as $path => $map) {
      if (isset($map[$key])) {
        return $path.$map[$key];
      }
    }
    $fileName = self::prepareFileName($className);
    if (0 !== strpos($fileName, '/Papaya/') ||
        0 === strpos($fileName, '/Papaya/Module/')) {
      foreach (self::$_paths as $prefix => $path) {
        if (0 === strpos($fileName, $prefix)) {
          return $path.substr($fileName, strlen($prefix)).'.php';
        }
      }
      return NULL;
    }
    return $systemDirectory.$fileName.'.php';
  }

  /**
   * @param $className
   * @return string|NULL
   */
  private static function convertToNamespaceClass($className) {
    if (isset(self::$_mapClasses[$className])) {
      return self::$_mapClasses[$className];
    }
    if (
      0 === strpos($className, 'Papaya') &&
      FALSE === strpos($className, '\\') &&
      preg_match_all(self::$classPattern, $className, $matches)
    ) {
      /** @var array $parts */
      $parts = $matches[0];
      $result = '';
      foreach ($parts as $part) {
        if (isset(self::$_mapParts[$part])) {
          $part = self::$_mapParts[$part];
        }
        $result .= '\\'.$part;
      }
      return substr($result, 1);
    }
    return $className;
  }

  /**
  * Get file from matches class parts
  *
  * The file will include only the part of the path defined by the class.
  *
  * @param string $className
  * @return string
  */
  private static function prepareFileName($className) {
    if (preg_match_all(self::$classPattern, $className, $matches)) {
      /** @var array $parts */
      $parts = $matches[0];
    } else {
      return '/'.$className;
    }
    $result = '';
    foreach ($parts as $part) {
      if ('\\' === $part[0]) {
        $result .= '/'.substr($part, 1);
      } else {
        $result .= '/'.ucfirst(strtolower($part));
      }
    }
    return $result;
  }

  /**
  * Register an path for classes starting with a defined prefix. The prefix "Papaya" is reserved,
  * except "PapayaModule".
  *
  * @param string $modulePrefix
  * @param string $modulePath
  */
  public static function registerPath($modulePrefix, $modulePath) {
    self::$_paths[self::prepareFileName($modulePrefix).'/'] =
      \Papaya\Utility\File\Path::cleanup($modulePath);
    uksort(self::$_paths, array('self', 'compareByCharacterLength'));
  }

  /**
   * Check if a classname prefix is already registered.
   *
   * @param $modulePrefix
   * @return bool
   */
  public static function hasPrefix($modulePrefix) {
    return isset(self::$_paths[self::prepareFileName($modulePrefix).'/']);
  }

  /**
   * Register an class map for a path. The map is an array of lowercase classnames (as keys)
   * and the class specific part of the path to the file containing the class.
   *
   * The $path argument is used as a prefix for the class file name.
   *
   * array(
   *   'classname' => '/path/to/class.php',
   *   ...
   * )
   *
   * @param string $path
   * @param array $classMap
   */
  public static function registerClassMap($path, array $classMap) {
    self::$_classmaps[$path] = $classMap;
  }

  /**
   * Check if a classmap for the given path is already registered
   *
   * @param $path
   * @return bool
   */
  public static function hasClassMap($path) {
    return isset(self::$_classmaps[$path]);
  }

  /**
   * Registered prefix are sortet by length (descending). A longer prefix has a higher priority.
   *
   * @param string $prefixOne
   * @param string $prefixTwo
   * @return int
   */
  public static function compareByCharacterLength($prefixOne, $prefixTwo) {
    if (strlen($prefixOne) > strlen($prefixTwo)) {
      return -1;
    }
    return strcmp($prefixOne, $prefixTwo);
  }

  /**
  * Clear all additional registered data about class and path mappings
  */
  public static function clear() {
    self::$_paths = array();
    self::$_classmaps = array();
  }

  /**
   * Lazy load the class map for the old classes in the papaya-lib/system directory
   *
   * @param string $directory
   */
  private static function lazyLoadClassmap($directory) {
    if (empty(self::$_classmaps) || !isset(self::$_classmaps[$directory])) {
      /** @noinspection PhpIncludeInspection */
      self::registerClassMap($directory, include $directory.'/_classmap.php');
    }
  }
}

class_alias(Autoloader::class, 'PapayaAutoloader');
