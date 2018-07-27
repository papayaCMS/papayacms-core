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

class PapayaFilterLogicalTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Filter\Logical::__construct
  * @covers \Papaya\Filter\Logical::_setFilters
  */
  public function testConstructorWithTwoFilters() {
    $subFilterOne = $this->createMock(\Papaya\Filter::class);
    $subFilterTwo = $this->createMock(\Papaya\Filter::class);
    $filter = new \PapayaFilterLogical_TestProxy($subFilterOne, $subFilterTwo);
    $this->assertAttributeEquals(
      array($subFilterOne, $subFilterTwo),
      '_filters',
      $filter
    );
  }
  /**
  * @covers \Papaya\Filter\Logical::__construct
  * @covers \Papaya\Filter\Logical::_setFilters
  */
  public function testConstructorWithTwoScalars() {
    $subFilterOne = new \Papaya\Filter\Equals('one');
    $subFilterTwo = new \Papaya\Filter\Equals('two');
    $filter = new \PapayaFilterLogical_TestProxy('one', 'two');
    $this->assertAttributeEquals(
      array($subFilterOne, $subFilterTwo),
      '_filters',
      $filter
    );
  }

  /**
  * @covers \Papaya\Filter\Logical::__construct
  * @covers \Papaya\Filter\Logical::_setFilters
  */
  public function testConstructorWithThreeFilters() {
    $subFilterOne = $this->createMock(\Papaya\Filter::class);
    $subFilterTwo = $this->createMock(\Papaya\Filter::class);
    $subFilterThree = $this->createMock(\Papaya\Filter::class);
    $filter = new \PapayaFilterLogical_TestProxy($subFilterOne, $subFilterTwo, $subFilterThree);
    $this->assertAttributeEquals(
      array($subFilterOne, $subFilterTwo, $subFilterThree),
      '_filters',
      $filter
    );
  }

  /**
  * @covers \Papaya\Filter\Logical::__construct
  * @covers \Papaya\Filter\Logical::_setFilters
  */
  public function testConstructorWithOneFilterExpectingException() {
    $this->expectException(InvalidArgumentException::class);
    new \PapayaFilterLogical_TestProxy(
      $this->createMock(\Papaya\Filter::class)
    );
  }

  /**
  * @covers \Papaya\Filter\Logical::__construct
  * @covers \Papaya\Filter\Logical::_setFilters
  */
  public function testContructorWithInvalidObjectsExpectingException() {
    $this->expectException(InvalidArgumentException::class);
    new \PapayaFilterLogical_TestProxy(
      new stdClass(), new stdClass()
    );
  }
}

class PapayaFilterLogical_TestProxy extends \Papaya\Filter\Logical {

  public function validate($value) {
  }

  public function filter($value) {
  }
}
