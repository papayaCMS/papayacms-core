<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaPluginLoaderTest extends PapayaTestCase {

  /**
  * @covers PapayaPluginLoader
  */
  public function testPluginsGetAfterSet() {
    $plugins = $this->createMock(PapayaPluginList::class);
    $loader = new PapayaPluginLoader();
    $this->assertSame(
      $plugins, $loader->plugins($plugins)
    );
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testPluginsGetWithImplicitCreate() {
    $loader = new PapayaPluginLoader();
    $this->assertInstanceOf(
      'PapayaPluginList', $loader->plugins()
    );
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testOptionsGetAfterSet() {
    $options = $this->createMock(PapayaPluginOptionGroups::class);
    $loader = new PapayaPluginLoader();
    $this->assertSame(
      $options, $loader->options($options)
    );
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testOptionsGetWithImplicitCreate() {
    $loader = new PapayaPluginLoader();
    $this->assertInstanceOf(
      'PapayaPluginOptionGroups', $loader->options()
    );
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testMagicPropertyPlguinsGetAfterSet() {
    $plugins = $this->createMock(PapayaPluginList::class);
    $loader = new PapayaPluginLoader();
    $loader->plugins = $plugins;
    $this->assertSame(
      $plugins, $loader->plugins
    );
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testMagicPropertyOptionsGetAfterSet() {
    $options = $this->createMock(PapayaPluginOptionGroups::class);
    $loader = new PapayaPluginLoader();
    $loader->options = $options;
    $this->assertSame(
      $options, $loader->options
    );
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testMagicMethodGetWithInvalidPropertyExpectingException() {
    $loader = new PapayaPluginLoader();
    $this->setExpectedException(
      'LogicException', 'Can not read unkown property PapayaPluginLoader::$unkownProperty'
    );
    $dummy = $loader->unkownProperty;
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testMagicMethodSetWithInvalidPropertyExpectingException() {
    $loader = new PapayaPluginLoader();
    $this->setExpectedException(
      'LogicException', 'Can not write unkown property PapayaPluginLoader::$unkownProperty'
    );
    $loader->unkownProperty = 'dummy';
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testHasExpectingTrue() {
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass'
        )
      )
    );
    $this->assertTrue($loader->has('123'));
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testHasExpectingFalse() {
    $plugins = $this->createMock(PapayaPluginList::class);
    $loader = new PapayaPluginLoader();
    $loader->plugins($plugins);
    $this->assertFalse($loader->has('123'));
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGet() {
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => dirname(__FILE__).'/TestData/',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass'
        )
      )
    );
    $this->assertInstanceOf(
      'PluginLoader_SampleClass', $loader->get('123')
    );
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetPluginInstance() {
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass'
        )
      )
    );
    $this->assertInstanceOf(
      'PluginLoader_SampleClass', $loader->getPluginInstance('123')
    );
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetWithPluginData() {
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass'
        )
      )
    );
    $samplePlugin = $loader->get('123', NULL, array('foo' => 'bar'));
    $this->assertEquals(
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $samplePlugin->data
    );
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetWithPluginDataAsString() {
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass'
        )
      )
    );
    $samplePlugin = $loader->get(
      '123',
      NULL,
      '<data version="2"><data-element name="foo">bar</data-element></data>'
    );
    $this->assertEquals(
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $samplePlugin->data
    );
  }


  /**
  * @covers PapayaPluginLoader
  */
  public function testGetEditableWithPluginData() {
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClassEditable'
        )
      )
    );
    $samplePlugin = $loader->get('123', NULL, array('foo' => 'bar'));
    $this->assertEquals(
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $samplePlugin->content->getXml()
    );
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetEditableWithPluginDataAsString() {
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClassEditable'
        )
      )
    );
    $samplePlugin = $loader->get(
      '123',
      NULL,
      '<data version="2"><data-element name="foo">bar</data-element></data>'
    );
    $this->assertEquals(
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $samplePlugin->content->getXml()
    );
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetWithSingleInstance() {
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass'
        )
      )
    );
    $plugin = $loader->get('123', NULL, array(), TRUE);
    $this->assertSame(
      $plugin, $loader->get('123', NULL, array(), TRUE)
    );
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetWithNonExistingPlugin() {
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        FALSE
      )
    );
    $this->assertNull($loader->get('123'));
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetWithInvalidPluginFileExpectingMessage() {
    $messages = $this->getMock('PapayaMessageManager', array('dispatch'));
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageLog'));
    $loader = new PapayaPluginLoader();
    $loader->papaya(
      $this->mockPapaya()->application(
        array(
          'messages' => $messages
        )
      )
    );
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'InvalidFile.php',
          'class' => 'PluginLoader_InvalidSampleClass'
        )
      )
    );
    $this->assertNull($loader->get('123'));
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetWithAutloaderPrefix() {
    PapayaAutoloader::clear();
    $messages = $this->getMock('PapayaMessageManager', array('dispatch'));
    $messages
      ->expects($this->any())
      ->method('dispatch')
      ->withAnyParameters();
    $loader = new PapayaPluginLoader();
    $loader->papaya(
      $this->mockPapaya()->application(
        array(
          'messages' => $messages
        )
      )
    );
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => str_replace('\\', '/', dirname(__FILE__)).'/TestData/',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_InvalidSampleClass',
          'prefix' => 'PluginLoaderAutoloadPrefix',
          'classes' => ''
        )
      )
    );
    $this->assertNull($loader->get('123'));
    $this->assertAttributeEquals(
      array(
        '/Plugin/Loader/Autoload/Prefix/' => str_replace('\\', '/', dirname(__FILE__)).'/TestData/'
      ),
      '_paths',
      'PapayaAutoloader'
    );
    PapayaAutoloader::clear();
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetWithAutloaderClassmap() {
    PapayaAutoloader::clear();
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => str_replace('\\', '/', dirname(__FILE__)).'/TestData/',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass',
          'prefix' => '',
          'classes' => '_classmap.php'
        )
      )
    );
    $this->assertInstanceOf('PluginLoader_SampleClass', $loader->get('123'));
    PapayaAutoloader::clear();
  }


  /**
  * @covers PapayaPluginLoader
  */
  public function testGetWithInvalidPluginClassExpectingMessage() {
    $messages = $this->getMock('PapayaMessageManager', array('dispatch'));
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageLog'));
    $loader = new PapayaPluginLoader();
    $loader->papaya(
      $this->mockPapaya()->application(
        array(
          'messages' => $messages
        )
      )
    );
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_InvalidSampleClass'
        )
      )
    );
    $this->assertNull($loader->get('123'));
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetFileName() {
    PapayaAutoloader::clear();
    $loader = new PapayaPluginLoader();
    $loader->papaya($this->mockPapaya()->application());
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '/base/path/sample/path/',
          'file' => 'sample.php',
          'class' => 'SampleClass',
          'prefix' => 'PluginLoaderAutoloadPrefix',
        )
      )
    );
    $this->assertEquals(
      '/base/path/sample/path/sample.php', $loader->getFileName('123')
    );
    $this->assertAttributeEquals(
      array(
        '/Plugin/Loader/Autoload/Prefix/' => '/base/path/sample/path/'
      ),
      '_paths',
      'PapayaAutoloader'
    );
    PapayaAutoloader::clear();
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetFileNameFromClassmap() {
    PapayaAutoloader::clear();
    $loader = new PapayaPluginLoader();
    $loader->papaya($this->mockPapaya()->application());
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => $path = str_replace('\\', '/', dirname(__FILE__)).'/TestData/',
          'file' => '',
          'class' => 'PluginLoader_SampleClass',
          'classes' => '_classmap.php',
        )
      )
    );
    $this->assertEquals(
      $path.'SampleClass.php', $loader->getFileName('123')
    );
    PapayaAutoloader::clear();
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetFileNameWithPathFromOptions() {
    PapayaAutoloader::clear();
    $loader = new PapayaPluginLoader();
    $loader->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options(array('PAPAYA_INCLUDE_PATH' => '/foo/bar'))
        )
      )
    );
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => 'sample/path/',
          'file' => 'sample.php',
          'class' => 'SampleClass',
          'prefix' => 'PluginLoaderAutoloadPrefix',
        )
      )
    );
    $this->assertEquals(
      '/foo/bar/modules/sample/path/sample.php', $loader->getFileName('123')
    );
    PapayaAutoloader::clear();
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetFileNameWithComposerPath() {
    PapayaAutoloader::clear();
    $loader = new PapayaPluginLoader();
    $loader->papaya($this->mockPapaya()->application());
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => 'vendor:/sample/path/',
          'file' => 'sample.php',
          'class' => 'SampleClass',
          'prefix' => 'PluginLoaderAutoloadPrefix',
        )
      )
    );
    $this->assertStringEndsWith(
      '/vendor/sample/path/sample.php', $loader->getFileName('123')
    );
    PapayaAutoloader::clear();
  }

  /**
  * @covers PapayaPluginLoader
  */
  public function testGetFileNameOfNonExistingPlugin() {
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        FALSE
      )
    );
    $this->assertEquals('', $loader->getFileName('123'));
  }

  /*************************
  * Fixtures
  *************************/

  private function getPluginListFixture($record) {
    $plugins = $this->createMock(PapayaPluginList::class);
    $plugins
      ->expects($this->any())
      ->method('offsetExists')
      ->will($this->returnValue(TRUE));
    $plugins
      ->expects($this->any())
      ->method('offsetGet')
      ->with($this->equalTo('123'))
      ->will($this->returnValue($record));
    return $plugins;
  }
}
