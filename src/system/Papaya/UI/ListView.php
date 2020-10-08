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

use Papaya\UI\ListView\Items\Builder;
use Papaya\XML;

/**
 * A listview gui control.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property ListView\Columns $columns
 * @property ListView\Items $items
 * @property Toolbars $toolbars
 * @property string|\Papaya\UI\Text $caption
 * @property string $mode
 * @property Reference $reference
 */
class ListView extends Control\Interactive {
  const MODE_DETAILS = 'details';

  const MODE_TILES = 'tiles';

  const MODE_THUMBNAILS = 'thumbnails';

  /**
   * Object buffer for listview items.
   *
   * @var ListView\Items
   */
  private $_items;

  /**
   * Object buffer for listview items builder (is this is set before the actual items are
   * requested, it is used to create them).
   *
   * @var ListView\Items
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
   * @var ListView\Columns
   */
  private $_columns;

  /**
   * Helper object to manage the four toolbars for the different positions.
   *
   * @var Toolbars
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
   * @var Reference
   */
  private $_reference;

  /**
   * Declared public properties, see property annotation of the class for documentation.
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

  public function __construct(Builder $builder = NULL) {
    if (isset($builder)) {
      $this->builder($builder);
    }
  }

  /**
   * Append listview output to parent element.
   *
   * @param XML\Element $parent
   *
   * @return null|XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $listview = $parent->appendElement('listview');
    if (!empty($this->_caption)) {
      $listview->setAttribute('title', (string)$this->_caption);
    }
    if (self::MODE_DETAILS !== $this->mode) {
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
   * @param ListView\Items $items
   *
   * @return ListView\Items
   */
  public function items(ListView\Items $items = NULL) {
    if (NULL !== $items) {
      $this->_items = $items;
    } elseif (NULL === $this->_items) {
      $this->_items = new ListView\Items($this);
      $this->_items->papaya($this->papaya());
    }
    $builder = $this->builder();
    if ($this->_useBuilder && NULL !== $builder && 0 === \count($this->_items)) {
      $this->_useBuilder = FALSE;
      $builder->fill($this->_items);
    }
    return $this->_items;
  }

  /**
   * The builder subobject allows you to change the creation process of the items and
   * add some items from a data source for example.
   *
   * @param ListView\Items\Builder $builder
   *
   * @return null|ListView\Items\Builder
   */
  public function builder(ListView\Items\Builder $builder = NULL) {
    if (NULL !== $builder) {
      $this->_builder = $builder;
      $this->_useBuilder = TRUE;
    }
    return $this->_builder;
  }

  /**
   * The list of listview columns
   *
   * @param ListView\Columns $columns
   *
   * @return ListView\Columns
   */
  public function columns(ListView\Columns $columns = NULL) {
    if (NULL !== $columns) {
      $this->_columns = $columns;
    } elseif (NULL === $this->_columns) {
      $this->_columns = new ListView\Columns($this);
      $this->_columns->papaya($this->papaya());
    }
    return $this->_columns;
  }

  /**
   * The list of listview toolbars
   *
   * @param Toolbars $toolbars
   *
   * @return Toolbars
   */
  public function toolbars(Toolbars $toolbars = NULL) {
    if (NULL !== $toolbars) {
      $this->_toolbars = $toolbars;
    } elseif (NULL === $this->_toolbars) {
      $this->_toolbars = new Toolbars();
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
   * @param Reference $reference
   *
   * @return Reference
   */
  public function reference(Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new Reference();
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
