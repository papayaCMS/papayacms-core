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

class RangeTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Range::__construct
   */
  public function testConstructorWithDefaultArgumentValues() {
    $field = new Range('Range', 'range');
    $this->assertEquals(
      'Range', $field->caption
    );
    $this->assertEquals(
      'range', $field->name
    );
    $this->assertEquals(
      50, $field->defaultValue
    );
    $this->assertEquals(
      0, $field->minimum
    );
    $this->assertEquals(
      100, $field->maximum
    );
    $this->assertEquals(
      1, $field->step
    );
    $this->assertFalse(
      $field->mandatory
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Range::__construct
   * @dataProvider provideArgumentsForConstructor
   * @param int $default
   * @param int $min
   * @param int $max
   * @param int $step
   * @param bool $mandatory
   */
  public function testConstructorWithCustomArguments($default, $min, $max, $step, $mandatory) {
    $field = new Range('Range', 'range', $default, $min, $max, $step, $mandatory);
    $this->assertEquals(
      'Range', $field->caption
    );
    $this->assertEquals(
      'range', $field->name
    );
    $this->assertEquals(
      $default, $field->defaultValue
    );
    $this->assertEquals(
      $min, $field->minimum
    );
    $this->assertEquals(
      $max, $field->maximum
    );
    $this->assertEquals(
      $step, $field->step
    );
    $this->assertEquals(
      $mandatory,
      $field->mandatory
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Range
   * @dataProvider provideValidRangeInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new Range('Range', 'range');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Range
   * @dataProvider provideInvalidRangeInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new Range('Range', 'range');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Range::appendTo
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $field = new Range('Range', 'range');
    $field->papaya($this->mockPapaya()->application());
    $field->appendTo($document->appendElement('test'));
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<test>
        <field caption="Range" class="DialogFieldInputRange" error="no">
          <input type="range" name="range" min="0" max="100" step="1">50</input>
        </field>
      </test>',
      $document->saveXML($document->documentElement)
    );
  }

  public static function provideArgumentsForConstructor() {
    return array(
      array(70, 3, 101, 2, TRUE),
      array(-5, -43, 131, 2.4, FALSE),
    );
  }

  public static function provideValidRangeInputs() {
    return array(
      array(0.467564, TRUE),
      array(1, FALSE),
      array(0, FALSE),
      array(NULL, FALSE)
    );
  }

  public static function provideInvalidRangeInputs() {
    return array(
      array('ASDA', TRUE),
      array('sysdf', TRUE),
      array('#gfg', FALSE),
      array(NULL, TRUE)
    );
  }
}
