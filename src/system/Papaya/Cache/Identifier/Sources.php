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

namespace Papaya\Cache\Identifier;
/**
 * An class to get the sources in a more readable way
 *
 * @package Papaya-Library
 * @subpackage Plugins
 */
class Sources implements \IteratorAggregate {

  private $_names = array(
    Definition::SOURCE_URL => 'Url',
    Definition::SOURCE_REQUEST => 'Request',
    Definition::SOURCE_SESSION => 'Session',
    Definition::SOURCE_DATABASE => 'Database',
    Definition::SOURCE_VARIABLES => 'Variables'
  );

  private $_sources = 0;

  public function __construct($sources) {
    \PapayaUtilConstraints::assertInteger($sources);
    $this->_sources = $sources;
  }

  public function __toString() {
    return implode(', ', $this->toArray());
  }

  /**
   * @return \ArrayIterator
   */
  public function getIterator() {
    return new \ArrayIterator($this->toArray());
  }

  private function toArray() {
    $result = array();
    foreach ($this->_names as $source => $name) {
      if (\PapayaUtilBitwise::inBitmask($source, $this->_sources)) {
        $result[] = $name;
      }
    }
    return $result;
  }

}
