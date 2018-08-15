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

class PapayaIteratorFilterCallbackTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Iterator\Filter\Callback::__construct
  * @covers \Papaya\Iterator\Filter\Callback::setCallback
  * @covers \Papaya\Iterator\Filter\Callback::getCallback
  */
  public function testConstructor() {
    $callback = function($element) { return is_int($element); };
    $filter = new \Papaya\Iterator\Filter\Callback(new \EmptyIterator(), $callback);
    $this->assertEquals($callback, $filter->getCallback());
  }

  /**
  * @covers \Papaya\Iterator\Filter\Callback::setCallback
  */
  public function testSetCallbackWithInvalidCallbackExpectingException() {
    $this->expectException(UnexpectedValueException::class);
    new \Papaya\Iterator\Filter\Callback(new \EmptyIterator(), NULL);
  }

  /**
  * @covers \Papaya\Iterator\Filter\Callback::accept
  */
  public function testAccept() {
    $data = array(
      'ok' => 42,
      'fail' => 'wrong'
    );
    $filter = new \Papaya\Iterator\Filter\Callback(
      new \ArrayIterator($data), function($element) { return is_int($element); }
    );
    $this->assertEquals(
      array('ok' => 42),
      iterator_to_array($filter, TRUE)
    );
  }
}
