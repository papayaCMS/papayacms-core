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
namespace Papaya\UI\ListView;

/**
 * A list view item represent one data line of the {@see \Papaya\UI\ListView}.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string $image
 * @property string|\Papaya\UI\Text $caption
 * @property string|\Papaya\UI\Text $text
 * @property null|array $actionParameters
 * @property int $indentation
 * @property int $columnSpan
 * @property bool $selected
 * @property bool $emphased
 * @property \Papaya\UI\ListView\SubItems $subitems
 * @property \Papaya\UI\Reference $reference
 * @property \Papaya\UI\ListView\Item\Node $node
 */
class Item extends \Papaya\UI\Control\Collection\Item {
  /**
   * Subitems collection
   *
   * @var \Papaya\UI\ListView\SubItems
   */
  protected $_subitems;

  /**
   * @var \Papaya\UI\ListView\Item\Node
   */
  private $_node;

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
   * @var null|array
   */
  protected $_actionParameters;

  /**
   * Reference object
   *
   * @var null|\Papaya\UI\Reference
   */
  protected $_reference;

  /**
   * listview items can be indented, the property stpres the level of indentation
   *
   * @var int indentation
   */
  protected $_indentation = 0;

  /**
   * listview items can be emphased, meaning the title will have a different formatting
   *
   * @var int indentation
   */
  protected $_emphased = FALSE;

  /**
   * Listitems can span subitem columns, -1 means that is will span all columns
   *
   * @var int
   */
  protected $_columnSpan = 0;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = [
    'subitems' => ['subitems', 'subitems'],
    'node' => ['node', 'node'],
    'caption' => ['_caption', '_caption'],
    'text' => ['_text', '_text'],
    'image' => ['_image', '_image'],
    'actionParameters' => ['_actionParameters', 'setActionParameters'],
    'selected' => ['_selected', '_selected'],
    'indentation' => ['_indentation', 'setIndentation'],
    'emphased' => ['_emphased', '_emphased'],
    'columnSpan' => ['_columnSpan', '_columnSpan'],
    'reference' => ['reference', 'reference']
  ];

  /**
   * Create object and store intialization values.
   *
   *
   * @param string $image
   * @param string|\Papaya\UI\Text $caption
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
   * @param array|null $actionParameters
   */
  protected function setActionParameters(array $actionParameters = NULL) {
    $this->_actionParameters = $actionParameters;
  }

  /**
   * Set the indentation level of the listview item.
   *
   * @param int $indentation
   *
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
   * @return \Papaya\UI\ListView
   */
  public function getListView() {
    return $this->collection()->owner();
  }

  /**
   * Return the collection for the item, overload for code completion and type check
   *
   * @param \Papaya\UI\ListView\Items|\Papaya\UI\Control\Collection $items
   *
   * @return \Papaya\UI\ListView\Items|\Papaya\UI\Control\Collection
   */
  public function collection(\Papaya\UI\Control\Collection $items = NULL) {
    \Papaya\Utility\Constraints::assertInstanceOfOrNull(\Papaya\UI\ListView\Items::class, $items);
    return parent::collection($items);
  }

  /**
   * Getter/Setter for the node subobject
   *
   * @param \Papaya\UI\ListView\Item\Node $node
   *
   * @return \Papaya\UI\ListView\Item\Node
   */
  public function node(\Papaya\UI\ListView\Item\Node $node = NULL) {
    if (isset($node)) {
      $this->_node = $node;
    } elseif (NULL === $this->_node) {
      $this->_node = new \Papaya\UI\ListView\Item\Node($this);
    }
    return $this->_node;
  }

  /**
   * Getter/Setter for the item subitems. Subitems represent addiitonal data.
   *
   * @param \Papaya\UI\ListView\SubItems $subitems
   *
   * @return \Papaya\UI\ListView\SubItems
   */
  public function subitems(\Papaya\UI\ListView\SubItems $subitems = NULL) {
    if (isset($subitems)) {
      $this->_subitems = $subitems;
      $this->_subitems->owner($this);
    }
    if (\is_null($this->_subitems)) {
      $this->_subitems = new \Papaya\UI\ListView\SubItems($this);
    }
    return $this->_subitems;
  }

  /**
   * Getter/Setter for the reference subobject, if not explit set. The reference from the collection
   * is cloned or a new one is created (if no collection is available).
   *
   * @param \Papaya\UI\Reference $reference
   *
   * @return \Papaya\UI\Reference
   */
  public function reference(\Papaya\UI\Reference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (\is_null($this->_reference)) {
      if ($this->hasCollection()) {
        $this->_reference = clone $this->collection()->reference();
        $this->_reference->setParameters(
          $this->_actionParameters, $this->getListView()->parameterGroup()
        );
      } else {
        $this->_reference = new \Papaya\UI\Reference();
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
   * @param \Papaya\XML\Element $parent
   *
   * @return \Papaya\XML\Element
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $itemNode = $parent->appendElement(
      'listitem',
      [
        'title' => (string)$this->_caption,
      ]
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
    if (0 != $this->_columnSpan) {
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
   * @return int
   */
  protected function getColumnSpan() {
    if ($this->_columnSpan < 0) {
      return \count($this->getListView()->columns());
    } else {
      return $this->_columnSpan;
    }
  }
}
