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
namespace Papaya\UI\Dialog\Field\Select\Media;

use Papaya\Content;
use Papaya\Iterator;
use Papaya\UI;
use Papaya\Utility;
use Papaya\XML;

/**
 * A selection field displayed as radio boxes, only a single value can be selected.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Folder extends UI\Dialog\Field {
  /**
   * @var Content\Media\Folders
   */
  private $_folders;

  public function __construct($caption, $name) {
    $this->setCaption($caption);
    $this->setName($name);
  }

  /**
   * Append select field to DOM
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $select = $field->appendElement(
      'select',
      [
        'name' => $this->_getParameterName($this->getName()),
        'type' => 'dropdown',
      ]
    );
    $iterator = new Iterator\RecursiveTraversableIterator(
      $this->mediaFolders(), Iterator\RecursiveTraversableIterator::SELF_FIRST
    );
    foreach ($iterator as $folderId => $folder) {
      $caption = '';
      if ($iterator->getDepth() > 0) {
        $caption .= \str_repeat('  ', $iterator->getDepth() - 1).'->';
      }
      $caption .= Utility\Arrays::get($folder, 'title', '');
      $option = $select->appendElement(
        'option', ['value' => $folderId], $caption
      );
      if ($folderId === $this->getCurrentValue()) {
        $option->setAttribute('selected', 'selected');
      }
    }
  }

  /**
   * Getter/Setter for the media folders data object, it implements \IteratorAggregate and
   * returning a RecursiveIterator
   *
   * @param Content\Media\Folders $folders
   *
   * @return Content\Media\Folders
   */
  public function mediaFolders(Content\Media\Folders $folders = NULL) {
    if (NULL !== $folders) {
      $this->_folders = $folders;
      $this->setFilter(new \Papaya\Filter\ArrayKey($this->_folders));
    } elseif (NULL === $this->_folders) {
      $this->_folders = new Content\Media\Folders();
      $this->_folders->activateLazyLoad();
      $this->setFilter(new \Papaya\Filter\ArrayKey($this->_folders));
    }
    return $this->_folders;
  }
}
