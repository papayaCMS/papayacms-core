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

namespace Papaya\Cache\Identifier\Definition\Session;

require_once __DIR__.'/../../../../../../bootstrap.php';

class ParametersTest extends \PapayaTestCase {

  /**
   * @covers Parameters
   */
  public function testGetStatus() {
    $values = $this
      ->getMockBuilder(\Papaya\Session\Values::class)
      ->disableOriginalConstructor()
      ->getMock();
    $values
      ->expects($this->once())
      ->method('getKey')
      ->with('foo')
      ->will($this->returnValue('bar'));
    $values
      ->expects($this->once())
      ->method('offsetGet')
      ->with('bar')
      ->will($this->returnValue('session_value'));
    $session = $this->createMock(\Papaya\Session::class);
    $session
      ->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $session
      ->expects($this->once())
      ->method('values')
      ->will($this->returnValue($values));

    $definition = new Parameters('foo');
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'session' => $session
        )
      )
    );
    $this->assertEquals(
      array(Parameters::class => array('bar' => 'session_value')),
      $definition->getStatus()
    );
  }

  /**
   * @covers Parameters
   */
  public function testGetStatusValueReturnsNull() {
    $values = $this
      ->getMockBuilder(\Papaya\Session\Values::class)
      ->disableOriginalConstructor()
      ->getMock();
    $values
      ->expects($this->any())
      ->method('getKey')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $values
      ->expects($this->once())
      ->method('offsetGet')
      ->with('foo')
      ->will($this->returnValue(NULL));
    $session = $this->createMock(\Papaya\Session::class);
    $session
      ->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $session
      ->expects($this->once())
      ->method('values')
      ->will($this->returnValue($values));

    $definition = new Parameters('foo');
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'session' => $session
        )
      )
    );
    $this->assertTrue(
      $definition->getStatus()
    );
  }

  /**
   * @covers Parameters
   */
  public function testGetStatusNoSessionActive() {
    $session = $this->createMock(\Papaya\Session::class);
    $session
      ->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(FALSE));

    $definition = new Parameters('foo');
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'session' => $session
        )
      )
    );
    $this->assertTrue(
      $definition->getStatus()
    );
  }

  /**
   * @covers Parameters
   */
  public function testGetStatusMultipleParameters() {
    $values = $this
      ->getMockBuilder(\Papaya\Session\Values::class)
      ->disableOriginalConstructor()
      ->getMock();
    $values
      ->expects($this->any())
      ->method('getKey')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $values
      ->expects($this->any())
      ->method('offsetGet')
      ->withAnyParameters()
      ->will(
        $this->returnValueMap(
          array(
            array('foo', 21),
            array('bar', 42),
            array('foobar', NULL)
          )
        )
      );
    $session = $this->createMock(\Papaya\Session::class);
    $session
      ->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $session
      ->expects($this->once())
      ->method('values')
      ->will($this->returnValue($values));

    $definition = new Parameters('foo', 'bar', 'foobar');
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'session' => $session
        )
      )
    );
    $this->assertEquals(
      array(
        Parameters::class => array(
          'foo' => 21,
          'bar' => 42
        )
      ),
      $definition->getStatus()
    );
  }

  /**
   * @covers Parameters
   */
  public function testGetSources() {
    $definition = new Parameters('foo');
    $this->assertEquals(
      \Papaya\Cache\Identifier\Definition::SOURCE_SESSION,
      $definition->getSources()
    );
  }
}
