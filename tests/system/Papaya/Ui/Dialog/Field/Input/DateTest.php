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

class PapayaUiDialogFieldInputDateTest extends \PapayaTestCase {
  /**
  * @covers \Papaya\Ui\Dialog\Field\Input\Date::__construct
  */
  public function testConstructor() {
    $input = new \Papaya\Ui\Dialog\Field\Input\Date(
      'Date', 'date', '2011-01-01 18:00', TRUE, \Papaya\Filter\Date::DATE_OPTIONAL_TIME, 300.0
    );
    $this->assertEquals('Date', $input->caption);
    $this->assertEquals('date', $input->name);
    $this->assertEquals('2011-01-01 18:00', $input->defaultValue);
    $this->assertTrue($input->mandatory);
    $this->assertAttributeEquals(\Papaya\Filter\Date::DATE_OPTIONAL_TIME, '_includeTime', $input);
    $this->assertAttributeEquals(300.0, '_step', $input);
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Input\Date::__construct
  */
  public function testConstructorWithInvalidIncludeTimeOption() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage(
      'Argument must be Papaya\Filter\Date::DATE_NO_TIME, Papaya\Filter\Date::DATE_OPTIONAL_TIME,'.
      ' or Papaya\Filter\Date::DATE_MANDATORY_TIME.');
    new \Papaya\Ui\Dialog\Field\Input\Date(
      'Date', 'date', '2011-01-01 18:00', TRUE, 23, 300.0
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Input\Date::__construct
  */
  public function testConstructorWithInvalidStep() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Step must be greater than 0.');
    new \Papaya\Ui\Dialog\Field\Input\Date(
      'Date', 'date', '2011-01-01 18:00', TRUE, \Papaya\Filter\Date::DATE_OPTIONAL_TIME, -300.0
    );
  }

  /**
   * @covers \Papaya\Ui\Dialog\Field\Input\Date
   * @dataProvider filterExpectingTrueProvider
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $input = new \Papaya\Ui\Dialog\Field\Input\Date(
      'Date', 'date', NULL, FALSE, \Papaya\Filter\Date::DATE_OPTIONAL_TIME
    );
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertTrue($input->validate());
  }

  /**
   * @covers \Papaya\Ui\Dialog\Field\Input\Date
   * @dataProvider filterExpectingFalseProvider
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $input = new \Papaya\Ui\Dialog\Field\Input\Date(
      'Date', 'date', NULL, FALSE, \Papaya\Filter\Date::DATE_OPTIONAL_TIME
    );
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertFalse($input->validate());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Input\Date::getXml
  */
  public function testGetXml() {
    $input = new \Papaya\Ui\Dialog\Field\Input\Date('Date', 'date');
    $input->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<field caption="Date" class="DialogFieldInputDate" error="no">
          <input type="date" name="date" maxlength="19"/>
        </field>',
      $input->getXml()
    );
  }

  public static function filterExpectingTrueProvider() {
    return array(
      array('2011-08-13 11:35', TRUE),
      array('2011-08-13', TRUE),
      array('2011-08-13', FALSE),
      array('2011-08-13 11:35', FALSE),
      array('', FALSE)
    );
  }

  public static function filterExpectingFalseProvider() {
    return array(
      array('2011-99-99', TRUE),
      array('2011*08*13', TRUE),
      array('11:35', FALSE),
      array('', TRUE)
    );
  }
}
