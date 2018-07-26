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

class PapayaUiDialogErrorsTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiDialogErrors::getIterator
  */
  public function testIterator() {
    $errors = new \PapayaUiDialogErrors();
    $errors->add(new Exception(), new stdClass());
    $errors->add(new Exception(), new stdClass());
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
  * @covers \PapayaUiDialogErrors::count
  */
  public function testCountable() {
    $errors = new \PapayaUiDialogErrors();
    $errors->add(new Exception(), new stdClass());
    $this->assertCount(1, $errors);
  }

  /**
  * @covers \PapayaUiDialogErrors::add
  */
  public function testAddWithoutSource() {
    $errors = new \PapayaUiDialogErrors();
    $errors->add($e = new Exception());
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
  * @covers \PapayaUiDialogErrors::add
  */
  public function testAddWithSource() {
    $errors = new \PapayaUiDialogErrors();
    $errors->add($e = new Exception(), $source = new stdClass());
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
  * @covers \PapayaUiDialogErrors::clear
  */
  public function testClear() {
    $errors = new \PapayaUiDialogErrors();
    $errors->clear();
    $this->assertAttributeEquals(
      array(),
      '_errors',
      $errors
    );
  }

  /**
  * @covers \PapayaUiDialogErrors::getSourceCaptions
  */
  public function testGetSourceCaptions() {
    $errors = new \PapayaUiDialogErrors();
    $fieldOne = $this->createMock(\PapayaUiDialogField::class);
    $fieldOne
      ->expects($this->once())
      ->method('getCaption')
      ->will($this->returnValue('FieldOne'));
    $fieldTwo = $this->createMock(\PapayaUiDialogField::class);
    $fieldTwo
      ->expects($this->once())
      ->method('getCaption')
      ->will($this->returnValue('FieldTwo'));
    $errors->add(new Exception(), $fieldOne);
    $errors->add(new Exception()); // invalid (empty) source, without caption
    $errors->add(new Exception(), $fieldTwo);
    $this->assertEquals(
      array('FieldOne', 'FieldTwo'), $errors->getSourceCaptions()
    );
  }
}
