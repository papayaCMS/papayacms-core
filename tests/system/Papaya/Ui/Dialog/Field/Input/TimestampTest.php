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

class PapayaUiDialogFieldInputTimestampTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldInputTimestamp::getCurrentValue
  */
  public function testGetCurrentValueFromDialogParameters() {
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will(
        $this->returnValue(
          new PapayaRequestParameters(array('date' => '2011-01-01 18:00'))
        )
      );
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date', 'date', NULL, FALSE, PapayaFilterDate::DATE_OPTIONAL_TIME
    );
    $field->collection($this->getCollectionMock($dialog));
    $this->assertEquals(strtotime('2011-01-01 18:00'), $field->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogFieldInputTimestamp::getCurrentValue
  */
  public function testGetCurrentValueFromDefaultValue() {
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date', 'date', NULL, FALSE, PapayaFilterDate::DATE_OPTIONAL_TIME
    );
    $field->setDefaultValue(strtotime('2011-01-01 18:00'));
    $this->assertEquals(strtotime('2011-01-01 18:00'), $field->getCurrentValue());
  }

  /**
   * @covers PapayaUiDialogFieldInputTimestamp
   * @dataProvider filterExpectingTrueProvider
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date', 'date', NULL, FALSE, PapayaFilterDate::DATE_OPTIONAL_TIME
    );
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue($field->validate());
  }

  /**
   * @covers PapayaUiDialogFieldInputTimestamp
   * @dataProvider filterExpectingFalseProvider
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date', 'date', NULL, FALSE, PapayaFilterDate::DATE_OPTIONAL_TIME
    );
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse($field->validate());
  }

  /**
  * @covers PapayaUiDialogFieldInputTimestamp::appendTo
  * @covers PapayaUiDialogFieldInputTimestamp::formatDateTime
  */
  public function testAppendTo() {
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date',
      'date',
      strtotime('2011-01-01 18:00'),
      FALSE,
      PapayaFilterDate::DATE_OPTIONAL_TIME,
      300.0
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Date" class="DialogFieldInputTimestamp" error="no">
        <input type="datetime" name="date" maxlength="19">2011-01-01 18:00:00</input>
      </field>',
      $field->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputTimestamp::appendTo
  * @covers PapayaUiDialogFieldInputTimestamp::formatDateTime
  */
  public function testAppendToWithoutTime() {
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date',
      'date',
      strtotime('2011-01-01 18:00'),
      FALSE,
      PapayaFilterDate::DATE_NO_TIME,
      300.0
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Date" class="DialogFieldInputTimestamp" error="no">
        <input type="date" name="date" maxlength="19">2011-01-01</input>
      </field>',
      $field->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputTimestamp::appendTo
  * @covers PapayaUiDialogFieldInputTimestamp::formatDateTime
  */
  public function testAppendToWithEmptyTimestamp() {
    $field = new PapayaUiDialogFieldInputTimestamp(
      'Date',
      'date',
      0,
      FALSE,
      PapayaFilterDate::DATE_NO_TIME,
      300.0
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Date" class="DialogFieldInputTimestamp" error="no">
        <input type="date" name="date" maxlength="19"/>
      </field>',
      $field->getXml()
    );
  }

  /*************************
  * Data Provider
  *************************/

  public static function filterExpectingTrueProvider() {
    return array(
      array('2011-08-13 11:35', TRUE),
      array('2011-08-13', TRUE),
      array('2011-08-13', FALSE),
      array('2011-08-13 11:35', FALSE),
      array('11:35', FALSE),
      array('', FALSE)
    );
  }

  public static function filterExpectingFalseProvider() {
    return array(
      array('', TRUE)
    );
  }

  /*************************
  * Fixtures
  *************************/

  /**
   * @param object|null $owner
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaUiDialogFields
   */
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
