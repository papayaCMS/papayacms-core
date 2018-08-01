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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterEqualsParameterTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Filter\Equals\Parameter::__construct
  */
  public function testConstructor() {
    $parameters = new \PapayaRequestParameters(array('foo' => 'bar'));
    $filter = new \Papaya\Filter\Equals\Parameter($parameters, 'foo');
    $this->assertAttributeSame($parameters, '_parameters', $filter);
    $this->assertAttributeEquals(new \Papaya\Request\Parameters\Name('foo'), '_parameterName', $filter);
  }

  /**
   * @covers \Papaya\Filter\Equals\Parameter::validate
   */
  public function testValidateTrue() {
    $parameters = new \PapayaRequestParameters(array('foo' => 'bar'));
    $filter = new \Papaya\Filter\Equals\Parameter($parameters, 'foo');
    $this->assertTrue($filter->validate('bar'));
  }

  /**
   * @covers \Papaya\Filter\Equals\Parameter::validate
   */
  public function testValidateInvalidFilterException() {
    $parameters = new \PapayaRequestParameters(array('foo' => 'booo'));
    $filter = new \Papaya\Filter\Equals\Parameter($parameters, 'foo');
    $this->expectException(\Papaya\Filter\Exception\InvalidValue::class);
    $this->expectExceptionMessage('Invalid value "bar"');
    $filter->validate('bar');
  }

  /**
   * @covers \Papaya\Filter\Equals\Parameter::filter
   */
  public function testFilterIsNull() {
    $parameters = new \PapayaRequestParameters(array());
    $filter = new \Papaya\Filter\Equals\Parameter($parameters, 'foo');
    $this->assertNull($filter->filter('foo3'));
  }

  /**
   * @covers \Papaya\Filter\Equals\Parameter::filter
   */
  public function testFilterExpectingValue() {
    $parameters = new \PapayaRequestParameters(array('foo' => 'bar'));
    $filter = new \Papaya\Filter\Equals\Parameter($parameters, 'foo');
    $this->assertEquals('bar', $filter->filter('bar'));
  }
}
