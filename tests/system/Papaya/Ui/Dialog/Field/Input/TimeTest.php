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

class PapayaUiDialogFieldInputTimeTest extends PapayaTestCase {
  /**
  * @covers \PapayaUiDialogFieldInputTime::__construct
  */
  public function testConstructor() {
    $input = new \PapayaUiDialogFieldInputTime('Time', 'time', '00:00:00', TRUE, 300.0);
    $this->assertEquals('Time', $input->caption);
    $this->assertEquals('time', $input->name);
    $this->assertEquals('00:00:00', $input->defaultValue);
    $this->assertTrue($input->mandatory);
    $this->assertAttributeEquals(300.0, '_step', $input);
  }

  /**
  * @covers \PapayaUiDialogFieldInputTime::__construct
  */
  public function testConstructorWithInvalidStep() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Step must not be less than 0.');
    new \PapayaUiDialogFieldInputTime('Time', 'time', '00:00:00', TRUE, -300.0);
  }

  /**
   * @covers \PapayaUiDialogFieldInputTime
   * @dataProvider filterExpectingTrueProvider
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $input = new \PapayaUiDialogFieldInputTime('Time', 'time');
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertTrue($input->validate());
  }

  /**
   * @covers \PapayaUiDialogFieldInputTime
   * @dataProvider filterExpectingFalseProvider
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $input = new \PapayaUiDialogFieldInputTime('Time', 'time');
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertFalse($input->validate());
  }

  /**
  * @covers \PapayaUiDialogFieldInputTime::getXml
  */
  public function testGetXml() {
    $input = new \PapayaUiDialogFieldInputTime('Time', 'time');
    $input->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Time" class="DialogFieldInputTime" error="no">
        <input type="time" name="time" maxlength="9"/>
      </field>',
      $input->getXml()
    );
  }

  public static function filterExpectingTrueProvider() {
    return array(
      array('18:35', TRUE),
      array('18:35', FALSE),
      array('', FALSE)
    );
  }

  public static function filterExpectingFalseProvider() {
    return array(
      array('18X35', TRUE),
      array('18X35', FALSE),
      array('', TRUE)
    );
  }
}
