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
 * A virtual dialog field, this will be part of the dialog but has no XML output. It can collect data
 * from a parameter depending on the filter.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Collector extends UI\Dialog\Field {
  /**
   * Initialize object, field name, default value and filter
   *
   * @param string $name
   * @param mixed $default
   * @param Filter|null $filter
   */
  public function __construct($name, $default, Filter $filter = NULL) {
    $this->setName($name);
    $this->setDefaultValue($default);
    if (NULL !== $filter) {
      $this->setFilter($filter);
    }
  }

  /**
   * Empty, this field does not append anything to the DOM
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
  }
}
