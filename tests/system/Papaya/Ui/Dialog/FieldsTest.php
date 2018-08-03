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

class PapayaUiDialogFieldsTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Dialog\Fields::validate
  */
  public function testValidateExpectingTrue() {
    $fieldOne = $this->getMockField();
    $fieldOne
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $fieldTwo = $this->getMockField();
    $fieldTwo
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $fields = new \Papaya\Ui\Dialog\Fields();
    $fields->add($fieldOne);
    $fields->add($fieldTwo);
    $this->assertTrue($fields->validate());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Fields::validate
  */
  public function testValidateExpectingFalse() {
    $fieldOne = $this->getMockField();
    $fieldOne
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(FALSE));
    $fieldTwo = $this->getMockField();
    $fieldTwo
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $fields = new \Papaya\Ui\Dialog\Fields();
    $fields->add($fieldOne);
    $fields->add($fieldTwo);
    $this->assertFalse($fields->validate());
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Dialog\Field
   */
  private function getMockField() {
    return $this->createMock(\Papaya\Ui\Dialog\Field::class);
  }
}
