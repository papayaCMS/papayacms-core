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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaIteratorCallbackTest extends PapayaTestCase {

  /**
  * @covers \PapayaIteratorCallback::__construct
  * @covers \PapayaIteratorCallback::getInnerIterator
  */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Iterator $innerIterator */
    $innerIterator = $this->createMock(Iterator::class);
    $iterator = new \PapayaIteratorCallback(
      $innerIterator,
      array($this, 'callbackChangeValue')
    );
    $this->assertSame(
      $innerIterator, $iterator->getInnerIterator()
    );
  }

  /**
  * @covers \PapayaIteratorCallback
  */
  public function testIteration() {
    $iterator = new \PapayaIteratorCallback(
      new ArrayIterator(array(21, 42)),
      array($this, 'callbackChangeValue')
    );
    $this->assertEquals(
      array(
        0 => 'Key: 0, Value: 21',
        1 => 'Key: 1, Value: 42'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers \PapayaIteratorCallback
  */
  public function testIterationWithKeys() {
    $iterator = new \PapayaIteratorCallback(
      new ArrayIterator(array(21 => '50%', 42 => '100%')),
      array($this, 'callbackChangeValue')
    );
    $this->assertEquals(
      array(
        21 => 'Key: 21, Value: 50%',
        42 => 'Key: 42, Value: 100%'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers \PapayaIteratorCallback
  */
  public function testIterationModifyKeys() {
    $iterator = new \PapayaIteratorCallback(
      new ArrayIterator(array(21 => '50%', 42 => '100%')),
      array($this, 'callbackFlip'),
      \PapayaIteratorCallback::MODIFY_KEYS
    );
    $this->assertEquals(
      array(
        '50%' => '50%',
        '100%' => '100%'
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers \PapayaIteratorCallback
  */
  public function testIterationModifyKeysAndValues() {
    $iterator = new \PapayaIteratorCallback(
      new ArrayIterator(array(21 => '50%', 42 => '100%')),
      array($this, 'callbackFlip'),
      \PapayaIteratorCallback::MODIFY_BOTH
    );
    $this->assertEquals(
      array(
        '50%' => 21,
        '100%' => 42
      ),
      iterator_to_array($iterator)
    );
  }

  public function callbackFlip($element, $key, $target) {
    return ($target === \PapayaIteratorCallback::MODIFY_KEYS) ? $element : $key;
  }

  public function callbackChangeValue($element, $key) {
    return 'Key: '.$key.', Value: '.$element;
  }
}
