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
* A listview gui control.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property PapayaUiListviewColumns $columns
* @property PapayaUiListviewItems $items
* @property PapayaUiToolbars $toolbars
* @property string|PapayaUiString $caption
* @property string $mode
* @property PapayaUiReference $reference
*/
class PapayaUiListview extends \PapayaUiControlInteractive {

  const MODE_DETAILS = 'details';
  const MODE_TILES = 'tiles';
  const MODE_THUMBNAILS = 'thumbnails';

  /**
  * Object buffer for listview items.
  *
  * @var PapayaUiListviewItems
  */
  private $_items = NULL;

  /**
  * Object buffer for listview items builder (is this is set before the actual items are
  * requested, it is used to create them).
  *
  * @var PapayaUiListviewItems
  */
  private $_builder = NULL;

  /**
  * Defines if the builder should be used to fill the items on next access. This
  * will be set to true if an builder is assigned and back to false after the builder was called.
  *
  * @var boolean
  */
  private $_useBuilder = FALSE;

  /**
  * Object buffer for listview columns.
  *
  * @var PapayaUiListviewColumns
  */
  private $_columns = NULL;

  /**
  * Helper object to manage the four toolbars for the different positions.
  *
  * @var PapayaUiToolbars
  */
  private $_toolbars = NULL;

  /**
  * Listview caption/title
  *
  * @var string
  */
  protected $_caption = '';

  /**
   * Display mode
   * @var string
   */
  protected $_mode = self::MODE_DETAILS;

  /**
  * Listview reference object for links
  *
  * @var PapayaUiReference
  */
  private $_reference = NULL;

  /**
  * Declared public properties, see property annotaiton of the class for documentation.
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'columns' => array('columns', 'columns'),
    'items' => array('items', 'items'),
    'toolbars' => array('toolbars', 'toolbars'),
    'caption' => array('_caption', '_caption'),
    'mode' => array('_mode', 'setMode'),
    'reference' => array('reference', 'reference')
  );

  /**
  * Append listview output to parent element.
  *
  * @param \PapayaXmlElement $parent
  * @return NULL|\PapayaXmlElement
  */
  public function appendTo(\PapayaXmlElement $parent) {
    $listview = $parent->appendElement('listview');
    if (!empty($this->_caption)) {
      $listview->setAttribute('title', (string)$this->_caption);
    }
    if ($this->mode != self::MODE_DETAILS) {
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
  * @param \PapayaUiListviewItems $items
   * @return null|\PapayaUiListviewItems
   */
  public function items(\PapayaUiListviewItems $items = NULL) {
    if (isset($items)) {
      $this->_items = $items;
    } elseif (is_null($this->_items)) {
      $this->_items = new \PapayaUiListviewItems($this);
      $this->_items->papaya($this->papaya());
    }
    if ($this->_useBuilder && count($this->_items) == 0) {
      $this->_useBuilder = FALSE;
      $this->builder()->fill($this->_items);
    }
    return $this->_items;
  }

  /**
  * The builder subobject allows you to change the creation process of the items and
  * add some items from a data source for example.
  *
  * @param \PapayaUiListviewItemsBuilder $builder
  * @return NULL|\PapayaUiListviewItemsBuilder
  */
  public function builder(\PapayaUiListviewItemsBuilder $builder = NULL) {
    if (isset($builder)) {
      $this->_builder = $builder;
      $this->_useBuilder = TRUE;
    }
    return $this->_builder;
  }

  /**
  * The list of listview columns
  *
  * @param \PapayaUiListviewColumns $columns
   * @return \PapayaUiListviewColumns
   */
  public function columns(\PapayaUiListviewColumns $columns = NULL) {
    if (isset($columns)) {
      $this->_columns = $columns;
    }
    if (is_null($this->_columns)) {
      $this->_columns = new \PapayaUiListviewColumns($this);
      $this->_columns->papaya($this->papaya());
    }
    return $this->_columns;
  }

  /**
  * The list of listview toolbars
  *
  * @param \PapayaUiToolbars $toolbars
   * @return \PapayaUiToolbars
   */
  public function toolbars(\PapayaUiToolbars $toolbars = NULL) {
    if (isset($toolbars)) {
      $this->_toolbars = $toolbars;
    }
    if (is_null($this->_toolbars)) {
      $this->_toolbars = new \PapayaUiToolbars($this);
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
  * @param \PapayaUiReference $reference
  * @return \PapayaUiReference
  */
  public function reference(\PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    }
    if (is_null($this->_reference)) {
      $this->_reference = new \PapayaUiReference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }

  /**
   * Set display mode
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
