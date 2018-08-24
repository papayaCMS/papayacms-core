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

class IdentifierTest extends \Papaya\TestCase {
  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Identifier::__construct
   */
  public function testConstructor() {
    $field = new Identifier('Name', 'name', 'default', TRUE);
    $this->assertEquals('Name', $field->caption);
    $this->assertEquals('name', $field->name);
    $this->assertEquals('default', $field->defaultValue);
    $this->assertTrue($field->getMandatory());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Identifier
   * @dataProvider provideValidIdentifierInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $field = new Identifier('Identifier', 'test');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertTrue(
      $field->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Identifier
   * @dataProvider provideInvalidIdentifierInputs
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $field = new Identifier('Identifier', 'test');
    $field->mandatory = $mandatory;
    $field->defaultValue = $value;
    $this->assertFalse(
      $field->validate()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Identifier::appendTo
   */
  public function testAppendTo() {
    $field = new Identifier('Identifier', 'test');
    $field->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="Identifier" class="DialogFieldInputIdentifier" error="no">
        <input type="text" name="test" maxlength="100"/>
      </field>',
      $field->getXML()
    );
  }

  public static function provideValidIdentifierInputs() {
    return array(
      array('foobar', TRUE),
      array('foo', FALSE),
      array('', FALSE),
    );
  }

  public static function provideInvalidIdentifierInputs() {
    return array(
      array('$$$', TRUE),
      array('$$$', FALSE),
      array('', TRUE),
    );
  }
}
