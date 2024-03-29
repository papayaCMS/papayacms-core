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

namespace Papaya\Filter\Equals;

require_once __DIR__.'/../../../../bootstrap.php';

/**
 * @covers \Papaya\Filter\Equals\Parameter
 */
class ParameterTest extends \Papaya\TestFramework\TestCase {

  public function testValidateTrue() {
    $parameters = new \Papaya\Request\Parameters(array('foo' => 'bar'));
    $filter = new Parameter($parameters, 'foo');
    $this->assertTrue($filter->validate('bar'));
  }

  public function testValidateInvalidFilterException() {
    $parameters = new \Papaya\Request\Parameters(array('foo' => 'booo'));
    $filter = new Parameter($parameters, 'foo');
    $this->expectException(\Papaya\Filter\Exception\InvalidValue::class);
    $this->expectExceptionMessage('Invalid value "bar"');
    $filter->validate('bar');
  }

  public function testFilterIsNull() {
    $parameters = new \Papaya\Request\Parameters(array());
    $filter = new Parameter($parameters, 'foo');
    $this->assertNull($filter->filter('foo3'));
  }

  public function testFilterExpectingValue() {
    $parameters = new \Papaya\Request\Parameters(array('foo' => 'bar'));
    $filter = new Parameter($parameters, 'foo');
    $this->assertEquals('bar', $filter->filter('bar'));
  }
}
