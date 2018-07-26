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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaIteratorFilterRegexTest extends PapayaTestCase {

  /**
  * @covers \PapayaIteratorFilterRegex::__construct
  */
  public function testConstructor() {
    $filter = new \PapayaIteratorFilterRegex(new ArrayIterator(array()), '(pattern)');
    $this->assertAttributeEquals(
      '(pattern)', '_pattern', $filter
    );
  }

  /**
  * @covers \PapayaIteratorFilterRegex::__construct
  */
  public function testConstructorWithAllArguments() {
    $filter = new \PapayaIteratorFilterRegex(
      new ArrayIterator(array()), '(pattern)', 42, \PapayaIteratorFilterRegex::FILTER_BOTH
    );
    $this->assertAttributeEquals(
      42, '_offset', $filter
    );
    $this->assertAttributeEquals(
      \PapayaIteratorFilterRegex::FILTER_BOTH, '_target', $filter
    );
  }

  /**
  * @covers \PapayaIteratorFilterRegex::accept
  * @covers \PapayaIteratorFilterRegex::isMatch
  */
  public function testAccept() {
    $data = array(
      'ok' => 'offset pattern',
      'fail string' => 'wrong',
      'fail offset' => 'pattern',
    );
    $filter = new \PapayaIteratorFilterRegex(
      new ArrayIterator($data), '(pattern)', 4
    );
    $this->assertEquals(
      array('ok' => 'offset pattern'),
      iterator_to_array($filter, TRUE)
    );
  }

  /**
  * @covers \PapayaIteratorFilterRegex::accept
  * @covers \PapayaIteratorFilterRegex::isMatch
  */
  public function testAcceptUsingKeys() {
    $data = array(
      'ok' => 'offset pattern',
      'fail string' => 'wrong',
      'fail offset' => 'pattern',
    );
    $filter = new \PapayaIteratorFilterRegex(
      new ArrayIterator(array_flip($data)), '(pattern)', 4, \PapayaIteratorFilterRegex::FILTER_KEYS
    );
    $this->assertEquals(
      array('offset pattern' => 'ok'),
      iterator_to_array($filter, TRUE)
    );
  }

}
