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

class PapayaUiDialogFieldInputNumberTest extends \PapayaTestCase {
  /**
  * @covers \Papaya\UI\Dialog\Field\Input\Number::__construct
  */
  public function testConstructSuccess() {
    $input = new \Papaya\UI\Dialog\Field\Input\Number('Number', 'number', '123', TRUE, 2, 4);
    $this->assertEquals('Number', $input->caption);
    $this->assertEquals('number', $input->name);
    $this->assertEquals('123', $input->defaultValue);
    $this->assertTrue($input->mandatory);
    $this->assertAttributeEquals(2, '_minimumLength', $input);
    $this->assertAttributeEquals(4, '_maximumLength', $input);
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Number::__construct
   * @dataProvider constructFailureProvider
   * @param mixed $minimumLength
   * @param mixed $maximumLength
   */
  public function testConstructFailure($minimumLength, $maximumLength) {
    $this->expectException(\UnexpectedValueException::class);
    new \Papaya\UI\Dialog\Field\Input\Number(
      'Number', 'number', '123', TRUE, $minimumLength, $maximumLength
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Number
   * @dataProvider filterExpectingTrueProvider
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $input = new \Papaya\UI\Dialog\Field\Input\Number('Number', 'number', NULL, FALSE, 2, 4);
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertTrue($input->validate());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Number
   * @dataProvider filterExpectingFalseProvider
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $input = new \Papaya\UI\Dialog\Field\Input\Number('Number', 'number', NULL, FALSE, 2, 4);
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertFalse($input->validate());
  }

  /**
  * @covers \Papaya\UI\Dialog\Field\Input\Number::getXML
  */
  public function testGetXml() {
    $input = new \Papaya\UI\Dialog\Field\Input\Number('Number', 'number', '123', FALSE, 2, 4);
    $this->assertXmlStringEqualsXmlString(
        /** @lang XML */
        '<field caption="Number" class="DialogFieldInputNumber" error="no">
          <input type="number" name="number" maxlength="4">123</input>
        </field>',
      $input->getXML()
    );
  }

  public static function constructFailureProvider() {
    return array(
      array(-1, NULL),
      array('String', NULL),
      array(NULL, -1),
      array(NULL, 'String'),
      array(5, 4)
    );
  }

  public static function filterExpectingTrueProvider() {
    return array(
      array('12', TRUE),
      array('123', TRUE),
      array('1234', TRUE),
      array('12', FALSE),
      array('123', FALSE),
      array('1234', FALSE),
      array('', FALSE)
    );
  }

  public static function filterExpectingFalseProvider() {
    return array(
      array('1', TRUE),
      array('12345', TRUE),
      array('NaN', TRUE),
      array('1', FALSE),
      array('12345', FALSE),
      array('NaN', FALSE),
      array('', TRUE)
    );
  }
}
