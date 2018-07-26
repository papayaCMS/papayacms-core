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

class PapayaUiControlInteractiveTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiControlInteractive::parameterMethod
  */
  public function testParameterMethodSet() {
    $dialog = new \PapayaUiControlInteractive_TestProxy();
    $dialog->parameterMethod(\PapayaUiControlInteractive::METHOD_GET);
    $this->assertAttributeEquals(
      \PapayaUiControlInteractive::METHOD_GET, '_parameterMethod', $dialog
    );
  }

  /**
  * @covers \PapayaUiControlInteractive::parameterMethod
  */
  public function testParameterMethodGet() {
    $dialog = new \PapayaUiControlInteractive_TestProxy();
    $this->assertEquals(
      \PapayaUiControlInteractive::METHOD_GET,
      $dialog->parameterMethod(\PapayaUiControlInteractive::METHOD_GET)
    );
  }

  /**
  * @covers \PapayaUiControlInteractive::parameterGroup
  */
  public function testParameterGroupSet() {
    $dialog = new \PapayaUiControlInteractive_TestProxy();
    $dialog->parameterGroup('sample');
    $this->assertAttributeEquals(
      'sample', '_parameterGroup', $dialog
    );
  }

  /**
  * @covers \PapayaUiControlInteractive::parameterGroup
  */
  public function testParameterGroupGet() {
    $dialog = new \PapayaUiControlInteractive_TestProxy();
    $dialog->parameterGroup('sample');
    $this->assertEquals(
      'sample', $dialog->parameterGroup()
    );
  }

  /**
  * @covers \PapayaUiControlInteractive::parameters
  */
  public function testParametersGetAfterSet() {
    $parameters = $this->createMock(\PapayaRequestParameters::class);
    $dialog = new \PapayaUiControlInteractive_TestProxy();
    $this->assertSame(
      $parameters, $dialog->parameters($parameters)
    );
  }

  /**
  * @covers \PapayaUiControlInteractive::parameters
  */
  public function testParamtersGetImplicit() {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getParameters')
      ->with(\Papaya\Request::SOURCE_QUERY | \Papaya\Request::SOURCE_BODY)
      ->will($this->returnValue(new \PapayaRequestParameters(array('foo' => 'bar'))));
    $dialog = new \PapayaUiControlInteractive_TestProxy();
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $this->assertEquals(
      array('foo' => 'bar'), $dialog->parameters()->toArray()
    );
  }

  /**
  * @covers \PapayaUiControlInteractive::parameters
  */
  public function testParamtersGetImplicitWithGroup() {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getParameterGroup')
      ->with('group', \Papaya\Request::SOURCE_QUERY | \Papaya\Request::SOURCE_BODY)
      ->will($this->returnValue(new \PapayaRequestParameters(array('foo' => 'bar'))));
    $dialog = new \PapayaUiControlInteractive_TestProxy();
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->parameterGroup('group');
    $this->assertEquals(
      array('foo' => 'bar'), $dialog->parameters()->toArray()
    );
  }

  /**
  * @covers \PapayaUiControlInteractive::isPostRequest
  */
  public function testIsPostRequestExpectingTrue() {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $dialog = new \PapayaUiControlInteractive_TestProxy();
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $this->assertTrue($dialog->isPostRequest());
  }

  /**
  * @covers \PapayaUiControlInteractive::isPostRequest
  */
  public function testIsPostRequestExpectingFalse() {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('get'));
    $dialog = new \PapayaUiControlInteractive_TestProxy();
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $this->assertFalse($dialog->isPostRequest());
  }
}

class PapayaUiControlInteractive_TestProxy extends \PapayaUiControlInteractive {

  public function appendTo(\PapayaXmlElement $node) {

  }
}
