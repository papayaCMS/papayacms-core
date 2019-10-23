<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\Database\Condition;

use Papaya\Database;
use Papaya\Database\Access as DatabaseAccess;

abstract class Condition {
  private $_parent;

  /**
   * Element constructor.
   *
   * @param Group $parent
   */
  public function __construct(
    Group $parent
  ) {
    $this->_parent = $parent;
  }

  /**
   * @return DatabaseAccess
   */
  public function getDatabaseAccess() {
    return $this->getParent()->getDatabaseAccess();
  }

  /**
   * @return null|Database\Interfaces\Mapping
   */
  public function getMapping() {
    return ($parent = $this->getParent()) ? $parent->getMapping() : NULL;
  }

  /**
   * @return Group
   */
  public function getParent() {
    return $this->_parent;
  }

  /**
   * @param bool $silent
   * @return string
   */
  abstract public function getSql($silent = FALSE);

  /**
   * @return string
   */
  public function __toString() {
    return $this->getSql(TRUE) ?: '';
  }

  /**
   * @param $name
   * @return false|string
   */
  protected function mapFieldName($name) {
    if (empty($name)) {
      throw new \LogicException(
        'Can not generate condition, provided name was empty.'
      );
    }
    if ($mapping = $this->getMapping()) {
      $field = $mapping->getField($name);
    } else {
      $field = $name;
    }
    if (empty($field)) {
      throw new \LogicException(
        \sprintf(
          'Can not generate condition, given name "%s" could not be mapped to a field.',
          $name
        )
      );
    }
    return $field;
  }
}
