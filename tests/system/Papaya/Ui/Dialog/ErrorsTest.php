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

namespace Papaya\UI\Dialog;

require_once __DIR__.'/../../../../bootstrap.php';

class ErrorsTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Dialog\Errors::getIterator
   */
  public function testIterator() {
    $errors = new Errors();
    $errors->add(new \Exception(), new \stdClass());
    $errors->add(new \Exception(), new \stdClass());
    $result = array();
    foreach ($errors as $index => $error) {
      $result[$index] = get_class($error['exception']).','.get_class($error['source']);
    }
    $this->assertEquals(
      array('Exception,stdClass', 'Exception,stdClass'),
      $result
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Errors::count
   */
  public function testCountable() {
    $errors = new Errors();
    $errors->add(new \Exception(), new \stdClass());
    $this->assertCount(1, $errors);
  }

  /**
   * @covers \Papaya\UI\Dialog\Errors::add
   */
  public function testAddWithoutSource() {
    $errors = new Errors();
    $errors->add($e = new \Exception());
    $this->assertAttributeEquals(
      array(
        array(
          'exception' => $e,
          'source' => NULL
        )
      ),
      '_errors',
      $errors
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Errors::add
   */
  public function testAddWithSource() {
    $errors = new Errors();
    $errors->add($e = new \Exception(), $source = new \stdClass());
    $this->assertAttributeEquals(
      array(
        array(
          'exception' => $e,
          'source' => $source
        )
      ),
      '_errors',
      $errors
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Errors::clear
   */
  public function testClear() {
    $errors = new Errors();
    $errors->clear();
    $this->assertAttributeEquals(
      array(),
      '_errors',
      $errors
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Errors::getSourceCaptions
   */
  public function testGetSourceCaptions() {
    $errors = new Errors();
    $fieldOne = $this->createMock(Field::class);
    $fieldOne
      ->expects($this->once())
      ->method('getCaption')
      ->will($this->returnValue('FieldOne'));
    $fieldTwo = $this->createMock(Field::class);
    $fieldTwo
      ->expects($this->once())
      ->method('getCaption')
      ->will($this->returnValue('FieldTwo'));
    $errors->add(new \Exception(), $fieldOne);
    $errors->add(new \Exception()); // invalid (empty) source, without caption
    $errors->add(new \Exception(), $fieldTwo);
    $this->assertEquals(
      array('FieldOne', 'FieldTwo'), $errors->getSourceCaptions()
    );
  }
}
