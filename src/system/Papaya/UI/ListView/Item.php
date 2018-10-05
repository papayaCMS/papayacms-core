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

use Papaya\UI;
use Papaya\XML;

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
 * @property bool $emphasised
 * @property SubItems $subitems
 * @property UI\Reference $reference
 * @property Item\Node $node
 */
class Item extends UI\Control\Collection\Item {
  /**
   * Subitems collection
   *
   * @var SubItems
   */
  protected $_subitems;

  /**
   * @var Item\Node
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
   * @var null|UI\Reference
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
  protected $_emphasised = FALSE;

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
    'emphased' => ['_emphasised', '_emphasised'],
    'emphasised' => ['_emphasised', '_emphasised'],
    'columnSpan' => ['_columnSpan', '_columnSpan'],
    'reference' => ['reference', 'reference']
  ];

  /**
   * Create object and store initialization values.
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
   * @param Items|UI\Control\Collection $items
   *
   * @return Items|UI\Control\Collection
   */
  public function collection(UI\Control\Collection $items = NULL) {
    \Papaya\Utility\Constraints::assertInstanceOfOrNull(Items::class, $items);
    return parent::collection($items);
  }

  /**
   * Getter/Setter for the node subobject
   *
   * @param Item\Node $node
   *
   * @return Item\Node
   */
  public function node(Item\Node $node = NULL) {
    if (NULL !== $node) {
      $this->_node = $node;
    } elseif (NULL === $this->_node) {
      $this->_node = new Item\Node($this);
    }
    return $this->_node;
  }

  /**
   * Getter/Setter for the item subitems. Subitems represent addiitonal data.
   *
   * @param SubItems $subitems
   *
   * @return SubItems
   */
  public function subitems(SubItems $subitems = NULL) {
    if (NULL !== $subitems) {
      $this->_subitems = $subitems;
      $this->_subitems->owner($this);
    } elseif (NULL === $this->_subitems) {
      $this->_subitems = new SubItems($this);
    }
    return $this->_subitems;
  }

  /**
   * Getter/Setter for the reference subobject, if not explit set. The reference from the collection
   * is cloned or a new one is created (if no collection is available).
   *
   * @param UI\Reference $reference
   *
   * @return UI\Reference
   */
  public function reference(UI\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      if ($this->hasCollection()) {
        $this->_reference = clone $this->collection()->reference();
        $this->_reference->setParameters(
          $this->_actionParameters, $this->getListView()->parameterGroup()
        );
      } else {
        $this->_reference = new UI\Reference();
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
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
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
    if (!empty($this->_actionParameters) || NULL !== $this->_reference) {
      $itemNode->setAttribute('href', $this->reference()->getRelative());
    }
    if ($this->_indentation > 0) {
      $itemNode->setAttribute('indent', $this->_indentation);
    }
    if (0 !== $this->_columnSpan) {
      $itemNode->setAttribute('span', $this->getColumnSpan());
    }
    if ((bool)$this->_selected) {
      $itemNode->setAttribute('selected', 'selected');
    }
    if ((bool)$this->_emphasised) {
      $itemNode->setAttribute('emphasized', 'emphasized');
      /* @todo remove property and attribute after changing the use */
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
    return $this->_columnSpan < 0 ? \count($this->getListView()->columns()) : $this->_columnSpan;
  }
}
