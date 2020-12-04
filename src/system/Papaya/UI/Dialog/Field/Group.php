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
namespace Papaya\UI\Dialog\Field;

use Papaya\BaseObject\Interfaces\StringCastable;
use Papaya\UI;
use Papaya\Utility\Constraints;
use Papaya\XML;

/**
 * A simple single line input field with a caption.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string|UI\Text $caption
 * @property UI\Dialog\Fields $fields
 */
class Group extends UI\Dialog\Field {
  /**
   * Grouped input fields
   *
   * @var UI\Dialog\Fields
   */
  protected $_fields;

  /**
   * declare dynamic properties
   *
   * @var array
   */
  protected $_declaredProperties = [
    'caption' => ['getCaption', 'setCaption'],
    'caption' => ['setImage', 'getImage'],
    'fields' => ['fields', 'fields']
  ];

  /**
   * @var mixed|UI\Text|string
   */
  private $_image;

  /**
   * Initialize object, set caption, field name and maximum length
   *
   * @param string|StringCastable $caption
   * @param string|StringCastable $image
   */
  public function __construct($caption, $image = '') {
    $this->setCaption($caption);
    $this->setImage($image);
  }

  /**
   * @param string|StringCastable $image
   *
   * @throws \UnexpectedValueException
   */
  public function setImage($image) {
    Constraints::assertStringCastable($image);
    $this->_image = $image;
  }

  /**
   * Casts the caption value to a string and returns it.
   *
   * @return string
   */
  public function getImage() {
    return (string)$this->_image;
  }

  /**
   * Group fields getter/setter
   *
   * @param UI\Dialog\Fields $fields
   *
   * @return UI\Dialog\Fields
   */
  public function fields(UI\Dialog\Fields $fields = NULL) {
    if (NULL !== $fields) {
      $this->_fields = $fields;
      if ($this->hasCollection() && $this->collection()->hasOwner()) {
        $fields->owner($this->collection()->owner());
      }
    } elseif (NULL === $this->_fields) {
      $this->_fields = new UI\Dialog\Fields(
        $this->hasDialog() ? $this->getDialog() : NULL
      );
    }
    return $this->_fields;
  }

  /**
   * Validate field group
   *
   * @return bool
   */
  public function validate() {
    if (NULL !== $this->_validationResult) {
      return $this->_validationResult;
    }
    if (NULL !== $this->_fields) {
      $this->_validationResult = $this->_fields->validate();
    } else {
      $this->_validationResult = TRUE;
    }
    return $this->_validationResult;
  }

  /**
   * Collect field group data
   *
   * @return bool
   */
  public function collect() {
    if (
      NULL !== $this->_fields &&
      parent::collect()
    ) {
      $this->_fields->collect();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Append group and fields in this group to the DOM.
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    if (NULL !== $this->_fields && \count($this->_fields) > 0) {
      $group = $parent->appendElement(
        'field-group',
        [
          'caption' => $this->getCaption(),
          'image' => ($image = $this->getImage()) !== '' ? $image :NULL
        ]
      );
      $id = $this->getId();
      if (!empty($id)) {
        $group->setAttribute('id', $id);
      }
      $this->_fields->appendTo($group);
    }
  }

  /**
   * Return the owner collection of the item.
   *
   * @param UI\Control\Collection $collection
   *
   * @return UI\Control\Collection
   */
  public function collection(UI\Control\Collection $collection = NULL) {
    $result = parent::collection($collection);
    if (NULL !== $collection && $collection->hasOwner()) {
      $this->fields()->owner($collection->owner());
    }
    return $result;
  }
}
