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

namespace Papaya\Ui\Listview;
/**
 * A listview item represent one data line of the {@see \Papaya\Ui\PapayaUiListview}.
 *
 * @package Papaya-Library
 * @subpackage Ui
 *
 * @property string $image
 * @property string|\PapayaUiString $caption
 * @property string|\PapayaUiString $text
 * @property NULL|array $actionParameters
 * @property integer $indentation
 * @property integer $columnSpan
 * @property boolean $selected
 * @property boolean $emphased
 * @property \Papaya\Ui\Listview\Subitems $subitems
 * @property \PapayaUiReference $reference
 * @property \Papaya\Ui\Listview\Item\Node $node
 */
class Item extends \Papaya\Ui\Control\Collection\Item {

  /**
   * Subitems collection
   *
   * @var \Papaya\Ui\Listview\Subitems
   */
  protected $_subitems = NULL;

  /**
   * @var \Papaya\Ui\Listview\Item\Node
   */
  private $_node = NULL;

  /**
   * Image index or url
   *
   * @var string
   */
  protected $_image = '';

  /**
   * Listitem caption/title
   *
   * @var string
   */
  protected $_caption = '';

  /**
   * Listitem text/subtitle
   *
   * @var string
   */
  protected $_text = '';

  /**
   * Listitem caption/title
   *
   * @var string
   */
  protected $_selected = '';

  /**
   * Parameters for the standard link (on caption and image)
   *
   * @var NULL|array
   */
  protected $_actionParameters = NULL;

  /**
   * Reference object
   *
   * @var NULL|\PapayaUiReference
   */
  protected $_reference = NULL;

  /**
   * listview items can be indented, the property stpres the level of indentation
   *
   * @var integer indentation
   */
  protected $_indentation = 0;

  /**
   * listview items can be emphased, meaning the title will have a different formatting
   *
   * @var integer indentation
   */
  protected $_emphased = FALSE;

  /**
   * Listitems can span subitem columns, -1 means that is will span all columns
   *
   * @var integer
   */
  protected $_columnSpan = 0;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'subitems' => array('subitems', 'subitems'),
    'node' => array('node', 'node'),
    'caption' => array('_caption', '_caption'),
    'text' => array('_text', '_text'),
    'image' => array('_image', '_image'),
    'actionParameters' => array('_actionParameters', 'setActionParameters'),
    'selected' => array('_selected', '_selected'),
    'indentation' => array('_indentation', 'setIndentation'),
    'emphased' => array('_emphased', '_emphased'),
    'columnSpan' => array('_columnSpan', '_columnSpan'),
    'reference' => array('reference', 'reference')
  );

  /**
   * Create object and store intialization values.
   *
   *
   * @param string $image
   * @param string|\PapayaUiString $caption
   * @param array $actionParameters
   * @param bool $selected
   */
  public function __construct($image, $caption, array $actionParameters = NULL, $selected = FALSE) {
    $this->image = $image;
    $this->caption = $caption;
    $this->actionParameters = $actionParameters;
    $this->selected = (bool)$selected;
  }

  /**
   * Set the action parameters for the item link. The values will be merge with the listview default
   * link and used the validate if the item ist selected.
   *
   * @param array|NULL $actionParameters
   */
  protected function setActionParameters(array $actionParameters = NULL) {
    $this->_actionParameters = $actionParameters;
  }

  /**
   * Set the indentation level of the listview item.
   *
   * @param integer $indentation
   * @throws \InvalidArgumentException
   */
  protected function setIndentation($indentation) {
    \Papaya\Utility\Constraints::assertInteger($indentation);
    if ($indentation >= 0) {
      $this->_indentation = $indentation;
    } else {
      throw new \InvalidArgumentException(
        'InvalidArgumentException: $indentation must be greater or equal zero.'
      );
    }
  }

  /**
   * Getter/Setter for the owner listview
   *
   * @return \Papaya\Ui\Listview
   */
  public function getListview() {
    return $this->collection()->owner();
  }

  /**
   * Return the collection for the item, overload for code completion and type check
   *
   * @param \Papaya\Ui\Listview\Items|\Papaya\Ui\Control\Collection $items
   * @return \Papaya\Ui\Listview\Items|\Papaya\Ui\Control\Collection
   */
  public function collection(\Papaya\Ui\Control\Collection $items = NULL) {
    \Papaya\Utility\Constraints::assertInstanceOfOrNull(\Papaya\Ui\Listview\Items::class, $items);
    return parent::collection($items);
  }

  /**
   * Getter/Setter for the node subobject
   *
   * @param \Papaya\Ui\Listview\Item\Node $node
   * @return \Papaya\Ui\Listview\Item\Node
   */
  public function node(\Papaya\Ui\Listview\Item\Node $node = NULL) {
    if (isset($node)) {
      $this->_node = $node;
    } elseif (NULL === $this->_node) {
      $this->_node = new \Papaya\Ui\Listview\Item\Node($this);
    }
    return $this->_node;
  }

  /**
   * Getter/Setter for the item subitems. Subitems represent addiitonal data.
   *
   * @param \Papaya\Ui\Listview\Subitems $subitems
   * @return \Papaya\Ui\Listview\Subitems
   */
  public function subitems(\Papaya\Ui\Listview\Subitems $subitems = NULL) {
    if (isset($subitems)) {
      $this->_subitems = $subitems;
      $this->_subitems->owner($this);
    }
    if (is_null($this->_subitems)) {
      $this->_subitems = new \Papaya\Ui\Listview\Subitems($this);
    }
    return $this->_subitems;
  }

  /**
   * Getter/Setter for the reference subobject, if not explit set. The reference from the collection
   * is cloned or a new one is created (if no collection is available).
   *
   * @param \PapayaUiReference $reference
   * @return \PapayaUiReference
   */
  public function reference(\PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (is_null($this->_reference)) {
      if ($this->hasCollection()) {
        $this->_reference = clone $this->collection()->reference();
        $this->_reference->setParameters(
          $this->_actionParameters, $this->getListview()->parameterGroup()
        );
      } else {
        $this->_reference = new \PapayaUiReference();
        $this->_reference->papaya($this->papaya());
        $this->_reference->setParameters(
          $this->_actionParameters
        );
      }
    }
    return $this->_reference;
  }

  /**
   * Append list item xml to parent xml element.
   *
   * @param \Papaya\Xml\Element $parent
   * @return \Papaya\Xml\Element
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $itemNode = $parent->appendElement(
      'listitem',
      array(
        'title' => (string)$this->_caption,
      )
    );
    $image = $this->papaya()->images[(string)$this->_image];
    if (!empty($image)) {
      $itemNode->setAttribute('image', $image);
    }
    if (!empty($this->_text)) {
      $itemNode->setAttribute('subtitle', (string)$this->_text);
    }
    if (!empty($this->_actionParameters) || isset($this->_reference)) {
      $itemNode->setAttribute('href', $this->reference()->getRelative());
    }
    if ($this->_indentation > 0) {
      $itemNode->setAttribute('indent', $this->_indentation);
    }
    if ($this->_columnSpan != 0) {
      $itemNode->setAttribute('span', $this->getColumnSpan());
    }
    if ((bool)$this->_selected) {
      $itemNode->setAttribute('selected', 'selected');
    }
    if ((bool)$this->_emphased) {
      $itemNode->setAttribute('emphased', 'emphased');
    }
    $itemNode->append($this->node());
    $itemNode->append($this->subitems());
    return $itemNode;
  }

  /**
   * Read column count from listview or object member
   *
   * @return integer
   */
  protected function getColumnSpan() {
    if ($this->_columnSpan < 0) {
      return count($this->getListview()->columns());
    } else {
      return $this->_columnSpan;
    }
  }
}
