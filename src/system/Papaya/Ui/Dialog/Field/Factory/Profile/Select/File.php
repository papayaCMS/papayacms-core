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

/**
* Field factory profiles for a select field for a file list.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldFactoryProfileSelectFile
  extends PapayaUiDialogFieldFactoryProfile {

  /**
   * @var PapayaFileSystemFactory
   */
  private $_fileSystem = NULL;

  protected $_fileSystemItems = \PapayaFileSystemDirectory::FETCH_FILES;

  /**
   * @see \PapayaUiDialogFieldFactoryProfile::getField()
   * @return \PapayaUiDialogFieldSelect
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
        $elements = new \PapayaIteratorMultiple(
          \PapayaIteratorMultiple::MIT_KEYS_ASSOC,
          new \ArrayIterator(array('' => 'none')),
          $elements
        );
      }
      $field = new \PapayaUiDialogFieldSelect(
        $this->options()->caption,
        $this->options()->name,
        new \PapayaIteratorTreeGroupsRegex(
          $elements,
          '(^(?P<group>.+)_([^_]+\\.[^.]+)$)',
          'group',
          \PapayaIteratorTreeGroupsRegex::GROUP_KEYS
        ),
        $this->options()->mandatory
      );
      $field->callbacks()->getOptionCaption = array($this, 'callbackGetFilename');
      $field->setDefaultValue($this->options()->default);
      $field->setHint($this->options()->hint ? $this->options()->hint : '');
    } else {
      $field = new \PapayaUiDialogFieldMessage(
        \PapayaMessage::SEVERITY_ERROR,
        new \PapayaUiStringTranslated(
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
   * @param string|splFileInfo $element
   * @return string
   */
  public function callbackGetFilename($context, $element) {
    return ($element instanceof \splFileInfo) ? $element->getFilename() : (string)$element;
  }

  /**
   * Get the path for the file list, ig it is an callback, fetch it from the context otherwise use
   * a PapayaConfigurationPath object.
   *
   * @return string|\PapayaConfigurationPath
   */
  private function getPath() {
    $parameters = $this->options()->parameters;
    $basePath = empty($parameters[0]) ? '' : (string)$parameters[0];
    if (0 === strpos($basePath, 'callback:')) {
      $callback = array($this->options()->context, substr($basePath, 9));
      $path = call_user_func($callback);
    } else {
      $path = new \PapayaConfigurationPath(
        $basePath,
        empty($parameters[2]) ? '' : (string)$parameters[2]
      );
      if ($this->options()->context instanceof \PapayaObjectInterface) {
        $path->papaya($this->options()->context->papaya());
      }
    }
    return $path;
  }

  /**
   * Getter/Setter for the file system factory
   *
   * @param \PapayaFileSystemFactory $fileSystem
   * @return \PapayaFileSystemFactory
   */
  public function fileSystem(\PapayaFileSystemFactory $fileSystem = NULL) {
    if (isset($fileSystem)) {
      $this->_fileSystem = $fileSystem;
    } elseif (NULL === $this->_fileSystem) {
      $this->_fileSystem = new \PapayaFileSystemFactory();
    }
    return $this->_fileSystem;
  }
}
