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

use Papaya\Filter;
use Papaya\UI;
use Papaya\XML;

/**
 * A dialog field with a control that is generated by a callback
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Callback extends UI\Dialog\Field {
  /**
   * Field xml generation callback
   *
   * @var int
   */
  private $_callback;

  /**
   * @var bool
   */
  protected $_isXhtml = FALSE;

  /**
   * Initialize object, set caption, field name and maximum length
   *
   * @param string|UI\Text $caption
   * @param string $name
   * @param callable $callback
   * @param mixed $default
   * @param Filter|null $filter
   */
  public function __construct(
    $caption, $name, $callback, $default = NULL, Filter $filter = NULL
  ) {
    $this->setCaption($caption);
    $this->setName($name);
    $this->_callback = $callback;
    $this->setDefaultValue($default);
    if (NULL !== $filter) {
      $this->setFilter($filter);
    }
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
    if (\is_callable($this->_callback)) {
      $target = $this->_isXhtml ? $field->appendElement('xhtml') : $field;
      $content = \call_user_func(
        $this->_callback, $this->getName(), $this, $this->getCurrentValue()
      );
      if ($content instanceof XML\Appendable) {
        $target->append($content);
      } elseif ($content instanceof \DOMElement) {
        $target->appendChild($field->ownerDocument->importNode($content, TRUE));
      } elseif (\is_string($content) || $content instanceof UI\Text) {
        $target->appendXML((string)$content);
      }
    }
    return $field;
  }
}
