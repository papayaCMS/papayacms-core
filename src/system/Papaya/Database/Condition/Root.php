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
namespace Papaya\Database\Condition;

use Papaya\Database;

class Root extends Group {

  /**
   * @param Database\Access|Database\Accessible $parent
   * @param Database\Interfaces\Mapping|NULL $mapping
   */
  public function __construct($parent, Database\Interfaces\Mapping $mapping = NULL) {
    if (NULL === $mapping && $parent instanceof Database\Interfaces\HasMapping) {
      $mapping = $parent->mapping();
    }
    parent::__construct($parent, $mapping);
  }

  /**
   * @param string $method
   * @param array $arguments
   *
   * @return \Papaya\Database\Condition\Element
   *
   * @throws \LogicException
   */
  public function __call($method, $arguments) {
    if (\count($this) > 0) {
      throw new \LogicException(
        \sprintf(
          '"%s" can only contain a single condition use logicalAnd() or logicalOr().',
          \get_class($this)
        )
      );
    }
    return parent::__call($method, $arguments);
  }

  /**
   * @param bool $silent
   *
   * @return string
   */
  public function getSql($silent = FALSE) {
    /** @var Element $condition */
    /** @noinspection LoopWhichDoesNotLoopInspection */
    foreach ($this as $condition) {
      return $condition->getSql($silent);
    }
    return '';
  }
}
