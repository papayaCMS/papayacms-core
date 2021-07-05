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

/**
 * @covers \Papaya\UI\Dialog\Field\Input\Time
 */
class TimeTest extends \Papaya\TestCase {
  public function testConstructor() {
    $input = new Time('Time', 'time', '00:00:00', TRUE, 300.0);
    $this->assertEquals('Time', $input->caption);
    $this->assertEquals('time', $input->name);
    $this->assertEquals('00:00:00', $input->defaultValue);
    $this->assertTrue($input->mandatory);
  }

  public function testConstructorWithInvalidStep() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Step must not be less than 0.');
    new Time('Time', 'time', '00:00:00', TRUE, -300.0);
  }

  /**
   * @dataProvider filterExpectingTrueProvider
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingTrue($value, $mandatory) {
    $input = new Time('Time', 'time');
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertTrue($input->validate());
  }

  /**
   * @dataProvider filterExpectingFalseProvider
   * @param mixed $value
   * @param bool $mandatory
   */
  public function testImplicitFilterExpectingFalse($value, $mandatory) {
    $input = new Time('Time', 'time');
    $input->mandatory = $mandatory;
    $input->defaultValue = $value;
    $this->assertFalse($input->validate());
  }

  public function testGetXml() {
    $input = new Time('Time', 'time');
    $input->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="Time" class="DialogFieldInputTime" error="no">
        <input type="time" name="time" maxlength="9"/>
      </field>',
      $input->getXML()
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
