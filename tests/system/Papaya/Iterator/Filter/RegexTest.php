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

namespace Papaya\Iterator\Filter;
require_once __DIR__.'/../../../../bootstrap.php';

/**
 * @covers \Papaya\Iterator\Filter\RegEx
 */
class RegExTest extends \Papaya\TestFramework\TestCase {

  public function testAccept() {
    $data = array(
      'ok' => 'offset pattern',
      'fail string' => 'wrong',
      'fail offset' => 'pattern',
    );
    $filter = new RegEx(
      new \ArrayIterator($data), '(pattern)', 4
    );
    $this->assertEquals(
      array('ok' => 'offset pattern'),
      iterator_to_array($filter, TRUE)
    );
  }

  public function testAcceptUsingKeys() {
    $data = array(
      'ok' => 'offset pattern',
      'fail string' => 'wrong',
      'fail offset' => 'pattern',
    );
    $filter = new RegEx(
      new \ArrayIterator(array_flip($data)), '(pattern)', 4, RegEx::FILTER_KEYS
    );
    $this->assertEquals(
      array('offset pattern' => 'ok'),
      iterator_to_array($filter, TRUE)
    );
  }

}
