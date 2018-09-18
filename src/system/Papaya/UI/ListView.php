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

namespace Papaya\UI;

/**
 * A listview gui control.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property \Papaya\UI\ListView\Columns $columns
 * @property \Papaya\UI\ListView\Items $items
 * @property \Papaya\UI\Toolbars $toolbars
 * @property string|\Papaya\UI\Text $caption
 * @property string $mode
 * @property \Papaya\UI\Reference $reference
 */
class ListView extends Control\Interactive {
  const MODE_DETAILS = 'details';

  const MODE_TILES = 'tiles';

  const MODE_THUMBNAILS = 'thumbnails';

  /**
   * Object buffer for listview items.
   *
   * @var \Papaya\UI\ListView\Items
   */
  private $_items;

  /**
   * Object buffer for listview items builder (is this is set before the actual items are
   * requested, it is used to create them).
   *
   * @var \Papaya\UI\ListView\Items
   */
  private $_builder;

  /**
   * Defines if the builder should be used to fill the items on next access. This
   * will be set to true if an builder is assigned and back to false after the builder was called.
   *
   * @var bool
   */
  private $_useBuilder = FALSE;

  /**
   * Object buffer for listview columns.
   *
   * @var \Papaya\UI\ListView\Columns
   */
  private $_columns;

  /**
   * Helper object to manage the four toolbars for the different positions.
   *
   * @var \Papaya\UI\Toolbars
   */
  private $_toolbars;

  /**
   * List view caption/title
   *
   * @var string
   */
  protected $_caption = '';

  /**
   * Display mode
   *
   * @var string
   */
  protected $_mode = self::MODE_DETAILS;

  /**
   * List view reference object for links
   *
   * @var \Papaya\UI\Reference
   */
  private $_reference;

  /**
   * Declared public properties, see property annotaiton of the class for documentation.
   *
   * @var array
   */
  protected $_declaredProperties = [
    'columns' => ['columns', 'columns'],
    'items' => ['items', 'items'],
    'toolbars' => ['toolbars', 'toolbars'],
    'caption' => ['_caption', '_caption'],
    'mode' => ['_mode', 'setMode'],
    'reference' => ['reference', 'reference']
  ];

  /**
   * Append listview output to parent element.
   *
   * @param \Papaya\XML\Element $parent
   * @return null|\Papaya\XML\Element
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $listview = $parent->appendElement('listview');
    if (!empty($this->_caption)) {
      $listview->setAttribute('title', (string)$this->_caption);
    }
    if (self::MODE_DETAILS != $this->mode) {
      $listview->setAttribute('mode', $this->mode);
    }
    $this->toolbars()->appendTo($listview);
    $this->columns()->appendTo($listview);
    $this->items()->appendTo($listview);
    return $listview;
  }

  /**
   * The list of listview items
   *
   * @param \Papaya\UI\ListView\Items $items
   * @return null|\Papaya\UI\ListView\Items
   */
  public function items(\Papaya\UI\ListView\Items $items = NULL) {
    if (isset($items)) {
      $this->_items = $items;
    } elseif (\is_null($this->_items)) {
      $this->_items = new \Papaya\UI\ListView\Items($this);
      $this->_items->papaya($this->papaya());
    }
    if ($this->_useBuilder && 0 == \count($this->_items)) {
      $this->_useBuilder = FALSE;
      $this->builder()->fill($this->_items);
    }
    return $this->_items;
  }

  /**
   * The builder subobject allows you to change the creation process of the items and
   * add some items from a data source for example.
   *
   * @param \Papaya\UI\ListView\Items\Builder $builder
   * @return null|\Papaya\UI\ListView\Items\Builder
   */
  public function builder(\Papaya\UI\ListView\Items\Builder $builder = NULL) {
    if (isset($builder)) {
      $this->_builder = $builder;
      $this->_useBuilder = TRUE;
    }
    return $this->_builder;
  }

  /**
   * The list of listview columns
   *
   * @param \Papaya\UI\ListView\Columns $columns
   * @return \Papaya\UI\ListView\Columns
   */
  public function columns(\Papaya\UI\ListView\Columns $columns = NULL) {
    if (isset($columns)) {
      $this->_columns = $columns;
    }
    if (\is_null($this->_columns)) {
      $this->_columns = new \Papaya\UI\ListView\Columns($this);
      $this->_columns->papaya($this->papaya());
    }
    return $this->_columns;
  }

  /**
   * The list of listview toolbars
   *
   * @param \Papaya\UI\Toolbars $toolbars
   * @return \Papaya\UI\Toolbars
   */
  public function toolbars(\Papaya\UI\Toolbars $toolbars = NULL) {
    if (isset($toolbars)) {
      $this->_toolbars = $toolbars;
    }
    if (\is_null($this->_toolbars)) {
      $this->_toolbars = new \Papaya\UI\Toolbars($this);
      $this->_toolbars->papaya($this->papaya());
    }
    return $this->_toolbars;
  }

  /**
   * The basic reference object used by the subobjects to create urls.
   *
   * It is possible to assign individual reference objects to the subobjects, but if you do not they
   * will use this one.
   *
   * @param \Papaya\UI\Reference $reference
   * @return \Papaya\UI\Reference
   */
  public function reference(\Papaya\UI\Reference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    }
    if (\is_null($this->_reference)) {
      $this->_reference = new \Papaya\UI\Reference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }

  /**
   * Set display mode
   *
   * @param string $mode
   */
  public function setMode($mode) {
    switch ($mode) {
      case self::MODE_DETAILS :
      case self::MODE_TILES :
      case self::MODE_THUMBNAILS :
        $this->_mode = $mode;
      break;
      default :
        $this->_mode = self::MODE_DETAILS;
    }
  }
}
