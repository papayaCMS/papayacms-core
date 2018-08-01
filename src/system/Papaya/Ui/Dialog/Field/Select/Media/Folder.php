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
* A selection field displayed as radio boxes, only a single value can be selected.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldSelectMediaFolder extends \PapayaUiDialogField {

  private $_folders = NULL;

  public function __construct($caption, $name) {
    $this->setCaption($caption);
    $this->setName($name);
  }

  /**
  * Append select field to DOM
  *
  * @param \PapayaXmlElement $parent
  */
  public function appendTo(\PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);
    $select = $field->appendElement(
      'select',
      array(
        'name' => $this->_getParameterName($this->getName()),
        'type' => 'dropdown',
      )
    );
    $iterator = new \RecursiveIteratorIterator(
      $this->mediaFolders(), \RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $folderId => $folder) {
      $caption = '';
      if ($iterator->getDepth() > 0) {
        $caption .= str_repeat('  ', $iterator->getDepth() - 1).'->';
      }
      $caption .= \Papaya\Utility\Arrays::get($folder, 'title', '');
      $option = $select->appendElement(
        'option', array('value' => $folderId), $caption
      );
      if ($folderId == $this->getCurrentValue()) {
        $option->setAttribute('selected', 'selected');
      }
    }
  }


  /**
   * Getter/Setter for the media folders data object, it implements \IteratorAggregate and
   * returning a RecursiveIterator
   *
   * @param \Papaya\Content\Media\Folders $folders
   * @return \Papaya\Content\Media\Folders
   */
  public function mediaFolders(\Papaya\Content\Media\Folders $folders = NULL) {
    if (isset($folders)) {
      $this->_folders = $folders;
      $this->setFilter(new \Papaya\Filter\ArrayKey($this->_folders));
    } elseif (NULL == $this->_folders) {
      $this->_folders = new \Papaya\Content\Media\Folders();
      $this->_folders->activateLazyLoad();
      $this->setFilter(new \Papaya\Filter\ArrayKey($this->_folders));
    }
    return $this->_folders;
  }
}
