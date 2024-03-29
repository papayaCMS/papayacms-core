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

namespace Papaya\UI\Dialog\Field\Input;
require_once __DIR__.'/../../../../../../bootstrap.php';

class ColorTest extends \Papaya\TestFramework\TestCase {
  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Color::__construct
   */
  public function testConstructor() {
    $field = new Color('Color', 'color', '#000000', TRUE);
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
   * @covers \Papaya\UI\Dialog\Field\Input\Color
   * @dataProvider provideValidColorInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new Color('Color', 'color');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Color
   * @dataProvider provideInvalidColorInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new Color('Color', 'color');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Color::appendTo
   */
  public function testAppendTo() {
    $field = new Color('Color', 'color');
    $field->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="Color" class="DialogFieldInputColor" error="no">
        <input type="color" name="color" maxlength="7"/>
      </field>',
      $field->getXML()
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
