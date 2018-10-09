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
namespace Papaya\UI\Dialog\Field\Input;

use Papaya\Filter;
use Papaya\UI;
use Papaya\Utility;
use Papaya\XML;

/**
 * A single line input with auto suggest
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string|UI\Text $caption
 * @property string $name
 * @property string $hint
 * @property string|null $defaultValue
 * @property bool $mandatory
 */
class Suggest extends UI\Dialog\Field\Input {
  /**
   * Field type, used in template
   *
   * @var string
   */
  protected $_type = 'suggest';

  /**
   * data attribute with multiple properties
   *
   * @var array
   */
  protected $_suggestionData = [
    'url' => '',
    'limit' => 10
  ];

  /**
   * declare dynamic properties
   *
   * @var array
   */
  protected $_declaredProperties = [
    'caption' => ['getCaption', 'setCaption'],
    'name' => ['getName', 'setName'],
    'hint' => ['getHint', 'setHint'],
    'defaultValue' => ['getDefaultValue', 'setDefaultValue'],
    'suggestionURL' => ['getSuggestionURL', 'setSuggestionURL'],
    'mandatory' => ['getMandatory', 'setMandatory']
  ];

  /**
   * Creates dialog field for input with suggest function with caption, name, default value and
   * mandatory status
   *
   * @param string $caption
   * @param string $name
   * @param string $suggestionURL
   * @param mixed $default optional, default NULL
   * @param Filter|null $filter
   */
  public function __construct(
    $caption, $name, $suggestionURL, $default = NULL, Filter $filter = NULL
  ) {
    parent::__construct($caption, $name, 1024, $default, $filter);
    $this->setSuggestionURL($suggestionURL);
  }

  /**
   * Set the suggestion url of this input field
   *
   * @param string $url
   */
  public function setSuggestionURL($url) {
    Utility\Constraints::assertNotEmpty($url);
    $this->_suggestionData['url'] = $url;
  }

  /**
   * Read the suggestion url of this input field
   */
  public function getSuggestionURL() {
    return $this->_suggestionData['url'];
  }

  /**
   * Append field and input output to DOM
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->appendElement(
      'input',
      [
        'type' => $this->getType(),
        'name' => $this->_getParameterName($this->getName()),
        'maxlength' => $this->_maximumLength,
        'data-suggest' => \json_encode($this->_suggestionData)
      ],
      $this->getCurrentValue()
    );
    return $field;
  }
}
