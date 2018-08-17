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

namespace Papaya\UI\Control {

  require_once __DIR__.'/../../../../bootstrap.php';

  class InteractiveTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\UI\Control\Interactive::parameterMethod
     */
    public function testParameterMethodSet() {
      $dialog = new Interactive_TestProxy();
      $dialog->parameterMethod(Interactive::METHOD_GET);
      $this->assertAttributeEquals(
        Interactive::METHOD_GET, '_parameterMethod', $dialog
      );
    }

    /**
     * @covers \Papaya\UI\Control\Interactive::parameterMethod
     */
    public function testParameterMethodGet() {
      $dialog = new Interactive_TestProxy();
      $this->assertEquals(
        Interactive::METHOD_GET,
        $dialog->parameterMethod(Interactive::METHOD_GET)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Interactive::parameterGroup
     */
    public function testParameterGroupSet() {
      $dialog = new Interactive_TestProxy();
      $dialog->parameterGroup('sample');
      $this->assertAttributeEquals(
        'sample', '_parameterGroup', $dialog
      );
    }

    /**
     * @covers \Papaya\UI\Control\Interactive::parameterGroup
     */
    public function testParameterGroupGet() {
      $dialog = new Interactive_TestProxy();
      $dialog->parameterGroup('sample');
      $this->assertEquals(
        'sample', $dialog->parameterGroup()
      );
    }

    /**
     * @covers \Papaya\UI\Control\Interactive::parameters
     */
    public function testParametersGetAfterSet() {
      $parameters = $this->createMock(\Papaya\Request\Parameters::class);
      $dialog = new Interactive_TestProxy();
      $this->assertSame(
        $parameters, $dialog->parameters($parameters)
      );
    }

    /**
     * @covers \Papaya\UI\Control\Interactive::parameters
     */
    public function testParamtersGetImplicit() {
      $request = $this->createMock(\Papaya\Request::class);
      $request
        ->expects($this->once())
        ->method('getParameters')
        ->with(\Papaya\Request::SOURCE_QUERY | \Papaya\Request::SOURCE_BODY)
        ->will($this->returnValue(new \Papaya\Request\Parameters(array('foo' => 'bar'))));
      $dialog = new Interactive_TestProxy();
      $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
      $this->assertEquals(
        array('foo' => 'bar'), $dialog->parameters()->toArray()
      );
    }

    /**
     * @covers \Papaya\UI\Control\Interactive::parameters
     */
    public function testParamtersGetImplicitWithGroup() {
      $request = $this->createMock(\Papaya\Request::class);
      $request
        ->expects($this->once())
        ->method('getParameterGroup')
        ->with('group', \Papaya\Request::SOURCE_QUERY | \Papaya\Request::SOURCE_BODY)
        ->will($this->returnValue(new \Papaya\Request\Parameters(array('foo' => 'bar'))));
      $dialog = new Interactive_TestProxy();
      $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
      $dialog->parameterGroup('group');
      $this->assertEquals(
        array('foo' => 'bar'), $dialog->parameters()->toArray()
      );
    }

    /**
     * @covers \Papaya\UI\Control\Interactive::isPostRequest
     */
    public function testIsPostRequestExpectingTrue() {
      $request = $this->createMock(\Papaya\Request::class);
      $request
        ->expects($this->once())
        ->method('getMethod')
        ->will($this->returnValue('post'));
      $dialog = new Interactive_TestProxy();
      $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
      $this->assertTrue($dialog->isPostRequest());
    }

    /**
     * @covers \Papaya\UI\Control\Interactive::isPostRequest
     */
    public function testIsPostRequestExpectingFalse() {
      $request = $this->createMock(\Papaya\Request::class);
      $request
        ->expects($this->once())
        ->method('getMethod')
        ->will($this->returnValue('get'));
      $dialog = new Interactive_TestProxy();
      $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
      $this->assertFalse($dialog->isPostRequest());
    }
  }

  class Interactive_TestProxy extends Interactive {

    public function appendTo(\Papaya\XML\Element $node) {

    }
  }
}
