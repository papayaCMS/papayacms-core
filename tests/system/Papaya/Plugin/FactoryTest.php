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

namespace Papaya\Plugin {

  require_once __DIR__.'/../../../bootstrap.php';

  class FactoryTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\Plugin\Factory::__construct
     */
    public function testConstructor() {
      $factory = new Factory_TestProxy($owner = new \stdClass);
      $this->assertSame(
        $owner, $factory->getOwner()
      );
    }

    /**
     * @covers \Papaya\Plugin\Factory::loader
     */
    public function testLoaderGetAfterSet() {
      $factory = new Factory_TestProxy();
      $loader = $this->createMock(Loader::class);
      $this->assertSame($loader, $factory->loader($loader));
    }

    /**
     * @covers \Papaya\Plugin\Factory::loader
     */
    public function testLoaderGetFromApplication() {
      $factory = new Factory_TestProxy();
      $factory->papaya(
        $this->mockPapaya()->application(
          array(
            'plugins' => $loader = $this->createMock(Loader::class)
          )
        )
      );
      $this->assertSame($loader, $factory->loader());
    }

    /**
     * @covers \Papaya\Plugin\Factory::has
     */
    public function testHasExpectingTrue() {
      $factory = new Factory_TestProxy();
      $this->assertTrue($factory->has('samplePlugin'));
    }

    /**
     * @covers \Papaya\Plugin\Factory::has
     */
    public function testHasExpectingFalse() {
      $factory = new Factory_TestProxy();
      $this->assertFalse($factory->has('undefinedPlugin'));
    }

    /**
     * @covers \Papaya\Plugin\Factory::get
     */
    public function testGet() {
      $factory = new Factory_TestProxy();
      $loader = $this->createMock(Loader::class);
      $loader
        ->expects($this->once())#
        ->method('get')
        ->with('123456789012345678901234567890ab', NULL, NULL, FALSE)
        ->will($this->returnValue(new \stdClass));
      $factory->loader($loader);
      $this->assertInstanceOf(\stdClass::class, $factory->get('samplePlugin'));
    }

    /**
     * @covers \Papaya\Plugin\Factory::get
     */
    public function testGetWithAllParameters() {
      $factory = new Factory_TestProxy($owner = new \stdClass);
      $loader = $this->createMock(Loader::class);
      $loader
        ->expects($this->once())#
        ->method('get')
        ->with('123456789012345678901234567890ab', $owner, NULL, TRUE)
        ->will($this->returnValue(new \stdClass));
      $factory->loader($loader);
      $this->assertInstanceOf(\stdClass::class, $factory->get('samplePlugin', TRUE));
    }

    /**
     * @covers \Papaya\Plugin\Factory::get
     */
    public function testGetWithInvalidNameExpectingException() {
      $factory = new Factory_TestProxy();
      $this->expectException(\InvalidArgumentException::class);
      $this->expectExceptionMessage(
        'InvalidArgumentException: "Papaya\Plugin\Factory_TestProxy" does not know plugin "invalid plugin".'
      );
      $factory->get('invalid plugin');
    }

    /**
     * @covers \Papaya\Plugin\Factory::__isset
     */
    public function testMagicMethodIssetExpectingTrue() {
      $factory = new Factory_TestProxy();
      $this->assertTrue(isset($factory->samplePlugin));
    }

    /**
     * @covers \Papaya\Plugin\Factory::__isset
     */
    public function testMagicMethodIssetExpectingFalse() {
      $factory = new Factory_TestProxy();
      $this->assertFalse(isset($factory->undefinedPlugin));
    }


    /**
     * @covers \Papaya\Plugin\Factory::__get
     */
    public function testMagicMethodGet() {
      $factory = new Factory_TestProxy();
      $loader = $this->createMock(Loader::class);
      $loader
        ->expects($this->once())
        ->method('get')
        ->with('123456789012345678901234567890ab', NULL, NULL, FALSE)
        ->will($this->returnValue(new \stdClass));
      $factory->loader($loader);
      $this->assertInstanceOf(\stdClass::class, $factory->samplePlugin);
    }

    /**
     * @covers \Papaya\Plugin\Factory::__set
     */
    public function testMagicMethodSetExpectingException() {
      $factory = new Factory_TestProxy();
      $this->expectException(\BadMethodCallException::class);
      $factory->samplePlugin = '123';
    }

    /**
     * @covers \Papaya\Plugin\Factory::__unset
     */
    public function testMagicMethodUnsetExpectingException() {
      $factory = new Factory_TestProxy();
      $this->expectException(\BadMethodCallException::class);
      unset($factory->samplePlugin);
    }


    /**
     * @covers \Papaya\Plugin\Factory::options
     */
    public function testOptionsGetAfterSet() {
      $options = $this
        ->getMockBuilder(\Papaya\Configuration::class)
        ->disableOriginalConstructor()
        ->getMock();
      $factory = new Factory_TestProxy();
      $factory->options('samplePlugin', $options);
      $this->assertSame($options, $factory->options('samplePlugin'));
    }

    /**
     * @covers \Papaya\Plugin\Factory::options
     */
    public function testOptionsGetFromLoader() {
      $options = $this
        ->getMockBuilder(\Papaya\Configuration::class)
        ->disableOriginalConstructor()
        ->getMock();
      $groups = $this->createMock(Option\Groups::class);
      $groups
        ->expects($this->once())
        ->method('offsetGet')
        ->with('123456789012345678901234567890ab')
        ->will($this->returnValue($options));
      $loader = $this->createMock(Loader::class);
      $loader
        ->expects($this->once())#
        ->method('__get')
        ->with('options')
        ->will($this->returnValue($groups));

      $factory = new Factory_TestProxy();
      $factory->loader($loader);
      $this->assertEquals($options, $factory->options('samplePlugin'));
    }

    /**
     * @covers \Papaya\Plugin\Factory::options
     */
    public function testOptionsWithInvalidPluginNameExpectingNull() {
      $factory = new Factory_TestProxy();
      $this->assertNull($factory->options('undefinedPlugin'));
    }

    /**
     * @covers \Papaya\Plugin\Factory::getOption
     */
    public function testGetOption() {
      $options = $this
        ->getMockBuilder(\Papaya\Configuration::class)
        ->disableOriginalConstructor()
        ->getMock();
      $options
        ->expects($this->once())
        ->method('get')
        ->with('SAMPLE_OPTION')
        ->will($this->returnValue(42));
      $factory = new Factory_TestProxy();
      $factory->options('samplePlugin', $options);
      $this->assertEquals(42, $factory->getOption('samplePlugin', 'SAMPLE_OPTION'));
    }

    /**
     * @covers \Papaya\Plugin\Factory::getOption
     */
    public function testGetOptionWithInvalidPluginNameExpectingDefault() {
      $factory = new Factory_TestProxy();
      $this->assertEquals(23, $factory->getOption('invalidPlugin', 'SAMPLE_OPTION', 23));
    }
  }

  /**
   * @property string samplePlugin
   */
  class Factory_TestProxy extends Factory {

    protected $_plugins = array(
      'samplePlugin' => '123456789012345678901234567890ab'
    );
  }
}
