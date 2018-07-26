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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaObjectInteractiveTest extends \PapayaTestCase {

  /**
   * @covers \PapayaObjectInteractive::parameterMethod
   */
  public function testParameterMethod() {
    $parts = new \PapayaObjectInteractive_TestProxy();
    $this->assertEquals(
      \PapayaRequestParametersInterface::METHOD_MIXED_POST,
      $parts->parameterMethod()
    );
  }

  /**
   * @covers \PapayaObjectInteractive::parameterMethod
   */
  public function testParameterMethodChange() {
    $parts = new \PapayaObjectInteractive_TestProxy();
    $this->assertEquals(
      \PapayaRequestParametersInterface::METHOD_MIXED_GET,
      $parts->parameterMethod(\PapayaRequestParametersInterface::METHOD_MIXED_GET)
    );
  }

  /**
   * @covers \PapayaObjectInteractive_TestProxy::parameterGroup
   */
  public function testParameterGroupWithChange() {
    $parts = new \PapayaObjectInteractive_TestProxy();
    $this->assertEquals(
      'sample', $parts->parameterGroup('sample')
    );
  }

  /**
   * @covers \PapayaObjectInteractive::parameterGroup
   */
  public function testParameterGroupWithoutChange() {
    $parts = new \PapayaObjectInteractive_TestProxy();
    $this->assertEquals(
      '', $parts->parameterGroup()
    );
  }

  /**
   * @covers \PapayaObjectInteractive::parameters
   */
  public function testParametersGetAfterSet() {
    $parts = new \PapayaObjectInteractive_TestProxy();
    $parts->parameters($parameters = $this->createMock(\PapayaRequestParameters::class));
    $this->assertEquals(
      $parameters, $parts->parameters()
    );
  }

  /**
   * @covers \PapayaObjectInteractive::parameters
   */
  public function testParametersGetAllFromApplicationRequest() {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getParameters')
      ->with(\Papaya\Request::SOURCE_QUERY | \Papaya\Request::SOURCE_BODY)
      ->will($this->returnValue($this->createMock(\PapayaRequestParameters::class)));
    $parts = new \PapayaObjectInteractive_TestProxy();
    $parts->papaya(
      $this->mockPapaya()->application(
        array('Request' => $request)
      )
    );
    $this->assertInstanceOf(\PapayaRequestParameters::class, $parts->parameters());
  }

  /**
   * @covers \PapayaObjectInteractive::parameters
   */
  public function testParametersGetGroupFromApplicationRequest() {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getParameterGroup')
      ->with('group', \Papaya\Request::SOURCE_QUERY | \Papaya\Request::SOURCE_BODY)
      ->will($this->returnValue($this->createMock(\PapayaRequestParameters::class)));
    $parts = new \PapayaObjectInteractive_TestProxy();
    $parts->papaya(
      $this->mockPapaya()->application(
        array('Request' => $request)
      )
    );
    $parts->parameterGroup('group');
    $this->assertInstanceOf(\PapayaRequestParameters::class, $parts->parameters());
  }
}

class PapayaObjectInteractive_TestProxy extends \PapayaObjectInteractive {

}
