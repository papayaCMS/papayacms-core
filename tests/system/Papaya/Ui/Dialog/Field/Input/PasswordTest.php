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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputPasswordTest extends PapayaTestCase {

  /**
   * @covers \PapayaUiDialogFieldInputPassword::__construct
   */
  public function testConstructorCreatesDefaultFilter() {
    $field = new \PapayaUiDialogFieldInputPassword('Caption', 'fieldname');
    $field->setMandatory(TRUE);
    $this->assertInstanceOf(PapayaFilterPassword::class, $field->getFilter());
  }

  /**
   * @covers \PapayaUiDialogFieldInputPassword::__construct
   */
  public function testConstructorAttachingFilter() {
    $filter = $this->createMock(PapayaFilter::class);
    $field = new \PapayaUiDialogFieldInputPassword('Caption', 'fieldname', 42, $filter);
    $field->setMandatory(TRUE);
    $this->assertSame($filter, $field->getFilter());
  }

  /**
   * @covers \PapayaUiDialogFieldInputPassword::getCurrentValue
   */
  public function testGetCurrentValueIgnoresDefaultValue() {
    $field = new \PapayaUiDialogFieldInputPassword('Caption', 'fieldname');
    $field->setDefaultValue('not ok');
    $this->assertEmpty($field->getCurrentValue());
  }

  /**
   * @covers \PapayaUiDialogFieldInputPassword::getCurrentValue
   */
  public function testGetCurrentValueIgnoreData() {
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(1))
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters(array())));
    $dialog
      ->expects($this->never())
      ->method('data');
    $field = new \PapayaUiDialogFieldInputPassword('Caption', 'foo');
    $field->collection($this->getCollectionMock($dialog));
    $this->assertEmpty($field->getCurrentValue());
  }

  /**
   * @covers \PapayaUiDialogFieldInputPassword::getCurrentValue
   */
  public function testGetCurrentValueReadParameter() {
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters(array('foo' => 'success'))));
    $field = new \PapayaUiDialogFieldInputPassword('Caption', 'foo');
    $field->collection($this->getCollectionMock($dialog));
    $this->assertEquals('success', $field->getCurrentValue());
  }

  public function getCollectionMock($owner = NULL) {
    $collection = $this->createMock(PapayaUiDialogFields::class);
    if ($owner) {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(TRUE));
      $collection
        ->expects($this->any())
        ->method('owner')
        ->will($this->returnValue($owner));
    } else {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(FALSE));
    }
    return $collection;
  }
}
