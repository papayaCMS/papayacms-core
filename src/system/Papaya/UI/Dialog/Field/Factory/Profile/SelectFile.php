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

/**
 * Field factory profiles for a select field for a file list.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class SelectFile
  extends \Papaya\UI\Dialog\Field\Factory\Profile {

  /**
   * @var \Papaya\File\System\Factory
   */
  private $_fileSystem = NULL;

  protected $_fileSystemItems = \Papaya\File\System\Directory::FETCH_FILES;

  /**
   * @see \Papaya\UI\Dialog\Field\Factory\Profile::getField()
   * @return \Papaya\UI\Dialog\Field\Select
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
        $elements = new \Papaya\Iterator\Union(
          \Papaya\Iterator\Union::MIT_KEYS_ASSOC,
          new \ArrayIterator(array('' => 'none')),
          $elements
        );
      }
      $field = new \Papaya\UI\Dialog\Field\Select(
        $this->options()->caption,
        $this->options()->name,
        new \Papaya\Iterator\Tree\Groups\RegEx(
          $elements,
          '(^(?P<group>.+)_([^_]+\\.[^.]+)$)',
          'group',
          \Papaya\Iterator\Tree\Groups\RegEx::GROUP_KEYS
        ),
        $this->options()->mandatory
      );
      $field->callbacks()->getOptionCaption = array($this, 'callbackGetFilename');
      $field->setDefaultValue($this->options()->default);
      $field->setHint($this->options()->hint ? $this->options()->hint : '');
    } else {
      $field = new \Papaya\UI\Dialog\Field\Message(
        \Papaya\Message::SEVERITY_ERROR,
        new \Papaya\UI\Text\Translated(
          'Can not open directory "%s"', array($path)
        )
      );
    }
    return $field;
  }

  /**
   * If the element is a fileinfo get the filename from it, cast the variable to string otherwise
   *
   * @param object $context
   * @param string|\splFileInfo $element
   * @return string
   */
  public function callbackGetFilename($context, $element) {
    return ($element instanceof \splFileInfo) ? $element->getFilename() : (string)$element;
  }

  /**
   * Get the path for the file list, ig it is an callback, fetch it from the context otherwise use
   * a \Papaya\Configuration\Path object.
   *
   * @return string|\Papaya\Configuration\Path
   * @throws \Papaya\UI\Dialog\Field\Factory\Exception\InvalidOption
   */
  private function getPath() {
    $parameters = $this->options()->parameters;
    $basePath = empty($parameters[0]) ? '' : (string)$parameters[0];
    if (0 === strpos($basePath, 'callback:')) {
      $callback = array($this->options()->context, substr($basePath, 9));
      $path = call_user_func($callback);
    } else {
      $path = new \Papaya\Configuration\Path(
        $basePath,
        empty($parameters[2]) ? '' : (string)$parameters[2]
      );
      if ($this->options()->context instanceof \Papaya\Application\Access) {
        $path->papaya($this->options()->context->papaya());
      }
    }
    return $path;
  }

  /**
   * Getter/Setter for the file system factory
   *
   * @param \Papaya\File\System\Factory $fileSystem
   * @return \Papaya\File\System\Factory
   */
  public function fileSystem(\Papaya\File\System\Factory $fileSystem = NULL) {
    if (isset($fileSystem)) {
      $this->_fileSystem = $fileSystem;
    } elseif (NULL === $this->_fileSystem) {
      $this->_fileSystem = new \Papaya\File\System\Factory();
    }
    return $this->_fileSystem;
  }
}
