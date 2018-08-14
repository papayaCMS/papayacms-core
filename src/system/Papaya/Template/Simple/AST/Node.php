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

namespace Papaya\Template\Simple\AST;
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
abstract class Node implements \Papaya\Template\Simple\AST {

  /**
   * Read private properties stored in constructor
   *
   * @param string $name
   * @throws \LogicException
   * @return mixed
   */
  public function __get($name) {
    $property = '_'.$name;
    if (property_exists($this, $property)) {
      return $this->$property;
    }
    throw new \LogicException(
      sprintf('Unknown property: %s::$%s', get_class($this), $name)
    );
  }

  /**
   * Block all undefined properties
   *
   * @param string $name
   * @param mixed $value
   * @throws \LogicException
   */
  public function __set($name, $value) {
    throw new \LogicException('All properties are defined in the constrcutor, they are read only.');
  }

  /**
   * Tell the visitor to visit this node.
   *
   * @param \Papaya\Template\Simple\Visitor $visitor
   */
  public function accept(\Papaya\Template\Simple\Visitor $visitor) {
    $visitor->visit($this);
  }
}
