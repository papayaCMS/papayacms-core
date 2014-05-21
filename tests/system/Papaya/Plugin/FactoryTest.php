<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaPluginFactoryTest extends PapayaTestCase {

  /**
  * @covers PapayaPluginFactory::__construct
  */
  public function testConstrcutor() {
    $factory = new PapayaPluginFactory_TestProxy($owner = new stdClass);
    $this->assertAttributeSame(
      $owner, '_owner', $factory
    );
  }

  /**
  * @covers PapayaPluginFactory::loader
  */
  public function testLoaderGetAfterSet() {
    $factory = new PapayaPluginFactory_TestProxy();
    $loader = $this->getMock('PapayaPluginLoader');
    $this->assertSame($loader, $factory->loader($loader));
  }

  /**
  * @covers PapayaPluginFactory::loader
  */
  public function testLoaderGetFromApplication() {
    $factory = new PapayaPluginFactory_TestProxy();
    $factory->papaya(
      $this->mockPapaya()->application(
        array(
          'plugins' => $loader = $this->getMock('PapayaPluginLoader')
        )
      )
    );
    $this->assertSame($loader, $factory->loader());
  }

  /**
  * @covers PapayaPluginFactory::has
  */
  public function testHasExpectingTrue() {
    $factory = new PapayaPluginFactory_TestProxy();
    $this->assertTrue($factory->has('samplePlugin'));
  }

  /**
  * @covers PapayaPluginFactory::has
  */
  public function testHasExpectingFalse() {
    $factory = new PapayaPluginFactory_TestProxy();
    $factory = new PapayaPluginFactory_TestProxy();
    $this->assertFalse($factory->has('undefinedPlugin'));
  }

  /**
  * @covers PapayaPluginFactory::get
  */
  public function testGet() {
    $factory = new PapayaPluginFactory_TestProxy();
    $loader = $this->getMock('PapayaPluginLoader');
    $loader
      ->expects($this->once())#
      ->method('get')
      ->with('123456789012345678901234567890ab', NULL, NULL, FALSE)
      ->will($this->returnValue(new stdClass));
    $factory->loader($loader);
    $this->assertInstanceOf('stdClass', $factory->get('samplePlugin'));
  }

  /**
  * @covers PapayaPluginFactory::get
  */
  public function testGetWithAllParameters() {
    $factory = new PapayaPluginFactory_TestProxy($owner = new stdClass);
    $loader = $this->getMock('PapayaPluginLoader');
    $loader
      ->expects($this->once())#
      ->method('get')
      ->with('123456789012345678901234567890ab', $owner, NULL, TRUE)
      ->will($this->returnValue(new stdClass));
    $factory->loader($loader);
    $this->assertInstanceOf('stdClass', $factory->get('samplePlugin', TRUE));
  }

  /**
  * @covers PapayaPluginFactory::get
  */
  public function testGetWithInvalidNameExpectingException() {
    $factory = new PapayaPluginFactory_TestProxy();
    $this->setExpectedException(
      'InvalidArgumentException',
      'InvalidArgumentException: "PapayaPluginFactory_TestProxy"'.
        ' does not know plugin "invalid plugin".'
    );
    $factory->get('invalid plugin');
  }

  /**
  * @covers PapayaPluginFactory::__get
  */
  public function testMagicMethodGet() {
    $factory = new PapayaPluginFactory_TestProxy();
    $loader = $this->getMock('PapayaPluginLoader');
    $loader
      ->expects($this->once())#
      ->method('get')
      ->with('123456789012345678901234567890ab', NULL, NULL, FALSE)
      ->will($this->returnValue(new stdClass));
    $factory->loader($loader);
    $this->assertInstanceOf('stdClass', $factory->samplePlugin);
  }

  /**
  * @covers PapayaPluginFactory::options
  */
  public function testOptionsGetAfterSet() {
    $options = $this
      ->getMockBuilder('PapayaConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $factory = new PapayaPluginFactory_TestProxy();
    $factory->options('samplePlugin', $options);
    $this->assertSame($options, $factory->options('samplePlugin'));
  }

  /**
  * @covers PapayaPluginFactory::options
  */
  public function testOptionsGetFromLoader() {
    $options = $this
      ->getMockBuilder('PapayaConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $groups = $this->getMock('PapayaPluginOptionGroups');
    $groups
      ->expects($this->once())
      ->method('offsetGet')
      ->with('123456789012345678901234567890ab')
      ->will($this->returnValue($options));
    $loader = $this->getMock('PapayaPluginLoader');
    $loader
      ->expects($this->once())#
      ->method('__get')
      ->with('options')
      ->will($this->returnValue($groups));

    $factory = new PapayaPluginFactory_TestProxy();
    $factory->loader($loader);
    $this->assertEquals($options, $factory->options('samplePlugin'));
  }

  /**
  * @covers PapayaPluginFactory::options
  */
  public function testOptionsWithInvalidPluginNameExpectingNull() {
    $factory = new PapayaPluginFactory_TestProxy();
    $this->assertNull($factory->options('undefinedPlugin'));
  }

  /**
  * @covers PapayaPluginFactory::getOption
  */
  public function testGetOption() {
    $options = $this
      ->getMockBuilder('PapayaConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $options
      ->expects($this->once())
      ->method('get')
      ->with('SAMPLE_OPTION')
      ->will($this->returnValue(42));
    $factory = new PapayaPluginFactory_TestProxy();
    $factory->options('samplePlugin', $options);
    $this->assertEquals(42, $factory->getOption('samplePlugin', 'SAMPLE_OPTION'));
  }

  /**
  * @covers PapayaPluginFactory::getOption
  */
  public function testGetOptionWithInvalidPluginNameExpectingDefault() {
    $factory = new PapayaPluginFactory_TestProxy();
    $this->assertEquals(23, $factory->getOption('invalidPlugin', 'SAMPLE_OPTION', 23));
  }
}

class PapayaPluginFactory_TestProxy extends PapayaPluginFactory {

  protected $_plugins = array(
    'samplePlugin' => '123456789012345678901234567890ab'
  );
}