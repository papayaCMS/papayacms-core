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

class PapayaUiDialogFieldInputColorTest extends PapayaTestCase {
  /**
  * @covers PapayaUiDialogFieldInputColor::__construct
  */
  public function testConstructor() {
    $field = new PapayaUiDialogFieldInputColor('Color', 'color', '#000000', TRUE);
    $this->assertEquals(
      'Color',
      $field->caption
    );
    $this->assertEquals(
      'color',
      $field->name
    );
    $this->assertEquals(
      '#000000',
      $field->defaultValue
    );
    $this->assertTrue(
      $field->getMandatory()
    );
  }

  /**
   * @covers PapayaUiDialogFieldInputColor
   * @dataProvider provideValidColorInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputColor('Color', 'color');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
   * @covers PapayaUiDialogFieldInputColor
   * @dataProvider provideInvalidColorInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new PapayaUiDialogFieldInputColor('Color', 'color');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputColor::appendTo
  */
  public function testAppendTo() {
    $field = new PapayaUiDialogFieldInputColor('Color', 'color');
    $field->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Color" class="DialogFieldInputColor" error="no">
        <input type="color" name="color" maxlength="7"/>
      </field>',
      $field->getXml()
    );
  }

  public static function provideValidColorInputs() {
    return array(
      array('#000000', TRUE),
      array('#000000', FALSE),
      array('', FALSE),
    );
  }

  public static function provideInvalidColorInputs() {
    return array(
      array(':#000000', TRUE),
      array(':#000000', FALSE),
      array('000000', FALSE),
      array('', TRUE),
    );
  }
}
