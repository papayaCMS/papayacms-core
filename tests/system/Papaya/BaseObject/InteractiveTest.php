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

namespace Papaya\BaseObject {

  require_once __DIR__.'/../../../bootstrap.php';

  class InteractiveTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\BaseObject\Interactive::parameterMethod
     */
    public function testParameterMethod() {
      $parts = new Interactive_TestProxy();
      $this->assertEquals(
        \Papaya\Request\Parameters\Access::METHOD_MIXED_POST,
        $parts->parameterMethod()
      );
    }

    /**
     * @covers \Papaya\BaseObject\Interactive::parameterMethod
     */
    public function testParameterMethodChange() {
      $parts = new Interactive_TestProxy();
      $this->assertEquals(
        \Papaya\Request\Parameters\Access::METHOD_MIXED_GET,
        $parts->parameterMethod(\Papaya\Request\Parameters\Access::METHOD_MIXED_GET)
      );
    }

    /**
     * @covers \Papaya\BaseObject\Interactive_TestProxy::parameterGroup
     */
    public function testParameterGroupWithChange() {
      $parts = new Interactive_TestProxy();
      $this->assertEquals(
        'sample', $parts->parameterGroup('sample')
      );
    }

    /**
     * @covers \Papaya\BaseObject\Interactive::parameterGroup
     */
    public function testParameterGroupWithoutChange() {
      $parts = new Interactive_TestProxy();
      $this->assertEquals(
        '', $parts->parameterGroup()
      );
    }

    /**
     * @covers \Papaya\BaseObject\Interactive::parameters
     */
    public function testParametersGetAfterSet() {
      $parts = new Interactive_TestProxy();
      $parts->parameters($parameters = $this->createMock(\Papaya\Request\Parameters::class));
      $this->assertEquals(
        $parameters, $parts->parameters()
      );
    }

    /**
     * @covers \Papaya\BaseObject\Interactive::parameters
     */
    public function testParametersGetAllFromApplicationRequest() {
      $request = $this->createMock(\Papaya\Request::class);
      $request
        ->expects($this->once())
        ->method('getParameters')
        ->with(\Papaya\Request::SOURCE_QUERY | \Papaya\Request::SOURCE_BODY)
        ->will($this->returnValue($this->createMock(\Papaya\Request\Parameters::class)));
      $parts = new Interactive_TestProxy();
      $parts->papaya(
        $this->mockPapaya()->application(
          array('Request' => $request)
        )
      );
      $this->assertInstanceOf(\Papaya\Request\Parameters::class, $parts->parameters());
    }

    /**
     * @covers \Papaya\BaseObject\Interactive::parameters
     */
    public function testParametersGetGroupFromApplicationRequest() {
      $request = $this->createMock(\Papaya\Request::class);
      $request
        ->expects($this->once())
        ->method('getParameterGroup')
        ->with('group', \Papaya\Request::SOURCE_QUERY | \Papaya\Request::SOURCE_BODY)
        ->will($this->returnValue($this->createMock(\Papaya\Request\Parameters::class)));
      $parts = new Interactive_TestProxy();
      $parts->papaya(
        $this->mockPapaya()->application(
          array('Request' => $request)
        )
      );
      $parts->parameterGroup('group');
      $this->assertInstanceOf(\Papaya\Request\Parameters::class, $parts->parameters());
    }
  }

  class Interactive_TestProxy extends Interactive {

  }
}
