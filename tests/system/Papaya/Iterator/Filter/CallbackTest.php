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

class PapayaIteratorFilterCallbackTest extends PapayaTestCase {

  /**
  * @covers \PapayaIteratorFilterCallback::__construct
  * @covers \PapayaIteratorFilterCallback::setCallback
  * @covers \PapayaIteratorFilterCallback::getCallback
  */
  public function testConstructor() {
    $callback = function($element) { return is_int($element); };
    $filter = new \PapayaIteratorFilterCallback(new EmptyIterator(), $callback);
    $this->assertEquals($callback, $filter->getCallback());
  }

  /**
  * @covers \PapayaIteratorFilterCallback::setCallback
  */
  public function testSetCallbackWithInvalidCallbackExpectingException() {
    $this->expectException(UnexpectedValueException::class);
    new \PapayaIteratorFilterCallback(new EmptyIterator(), NULL);
  }

  /**
  * @covers \PapayaIteratorFilterCallback::accept
  */
  public function testAccept() {
    $data = array(
      'ok' => 42,
      'fail' => 'wrong'
    );
    $filter = new \PapayaIteratorFilterCallback(
      new ArrayIterator($data), function($element) { return is_int($element); }
    );
    $this->assertEquals(
      array('ok' => 42),
      iterator_to_array($filter, TRUE)
    );
  }
}
