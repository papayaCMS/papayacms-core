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
namespace Papaya\UI\Dialog\Field\Factory\Profile;

use Papaya\Application;
use Papaya\Configuration;
use Papaya\File\System as FileSystem;
use Papaya\Iterator;
use Papaya\UI;

/**
 * Field factory profiles for a select field for a file list.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class SelectFile
  extends UI\Dialog\Field\Factory\Profile {
  /**
   * @var FileSystem\Factory
   */
  private $_fileSystem;

  protected $_fileSystemItems = FileSystem\Directory::FETCH_FILES;

  /**
   * @see \Papaya\UI\Dialog\Field\Factory\Profile::getField()
   *
   * @return UI\Dialog\Field\Select
   *
   * @throws \Papaya\UI\Dialog\Field\Factory\Exception\InvalidOption
   */
  public function getField() {
    $parameters = $this->options()->parameters;
    $path = $this->getPath();
    $directory = $this->fileSystem()->getDirectory((string)$path);
    if ($directory->isReadable()) {
      $elements = $directory->getEntries(
        empty($parameters[1]) ? '' : (string)$parameters[1],
        $this->_fileSystemItems
      );
      if (!$this->options()->mandatory) {
        $elements = new Iterator\Union(
          Iterator\Union::MIT_KEYS_ASSOC,
          new \ArrayIterator(['' => 'none']),
          $elements
        );
      }
      $field = new UI\Dialog\Field\Select(
        $this->options()->caption,
        $this->options()->name,
        new Iterator\Tree\Groups\RegEx(
          $elements,
          '(^(?P<group>.+)_([^_]+\\.[^.]+)$)',
          'group',
          Iterator\Tree\Groups\RegEx::GROUP_KEYS
        ),
        $this->options()->mandatory
      );
      $field->callbacks()->getOptionCaption = function(
        /** @noinspection PhpUnusedParameterInspection */
        $context, $element
      ) {
        return ($element instanceof \splFileInfo) ? $element->getFilename() : (string)$element;
      };
      $field->setDefaultValue($this->options()->default);
      $field->setHint($this->options()->hint ?: '');
    } else {
      $field = new UI\Dialog\Field\Message(
        \Papaya\Message::SEVERITY_ERROR,
        new UI\Text\Translated(
          'Can not open directory "%s"', [$path]
        )
      );
    }
    return $field;
  }

  /**
   * Get the path for the file list, ig it is an callback, fetch it from the context otherwise use
   * a \Papaya\Configuration\Path object.
   *
   * @return string|Configuration\Path
   *
   * @throws \Papaya\UI\Dialog\Field\Factory\Exception\InvalidOption
   */
  private function getPath() {
    $parameters = $this->options()->parameters;
    $basePath = empty($parameters[0]) ? '' : (string)$parameters[0];
    if (0 === \strpos($basePath, 'callback:')) {
      $callback = [$this->options()->context, \substr($basePath, 9)];
      $path = $callback();
    } else {
      $path = new Configuration\Path(
        $basePath,
        empty($parameters[2]) ? '' : (string)$parameters[2]
      );
      if ($this->options()->context instanceof Application\Access) {
        $path->papaya($this->options()->context->papaya());
      }
    }
    return $path;
  }

  /**
   * Getter/Setter for the file system factory
   *
   * @param FileSystem\Factory $fileSystem
   *
   * @return FileSystem\Factory
   */
  public function fileSystem(FileSystem\Factory $fileSystem = NULL) {
    if (NULL !== $fileSystem) {
      $this->_fileSystem = $fileSystem;
    } elseif (NULL === $this->_fileSystem) {
      $this->_fileSystem = new FileSystem\Factory();
    }
    return $this->_fileSystem;
  }
}
