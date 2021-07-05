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
namespace Papaya\Content\Structure;

/**
 * Content structure group element
 *
 * Content structure values are organized in groups and pages. A page can contain multiple groups
 * and a group multiple values.
 *
 * @package Papaya-Library
 * @subpackage Content
 *
 * @property string $title
 * @property string $name
 */
class Group extends Node {
  public $title = '';

  public $name = '';

  private $_values;

  private $_page;

  /**
   * Create object and store page
   *
   * @param Page $page
   */
  public function __construct(Page $page) {
    parent::__construct(
      [
        'name' => 'page',
        'title' => ''
      ]
    );
    $this->_page = $page;
  }

  /**
   * Getter/Setter for the values list
   *
   * @param Values $values
   *
   * @return Values
   */
  public function values(Values $values = NULL) {
    if (NULL !== $values) {
      $this->_values = $values;
    } elseif (NULL === $this->_values) {
      $this->_values = new Values($this);
    }
    return $this->_values;
  }

  /**
   * Get the identifier compiled from page identifier and group name
   *
   * @return string
   */
  public function getIdentifier() {
    return $this->_page->getIdentifier().'/'.$this->name;
  }

  public function getPage(): Page {
    return $this->_page;
  }
}
