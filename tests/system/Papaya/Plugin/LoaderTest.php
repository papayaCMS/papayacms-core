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

class PapayaPluginLoaderTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testPluginsGetAfterSet() {
    $plugins = $this->createMock(\Papaya\Plugin\Collection::class);
    $loader = new \Papaya\Plugin\Loader();
    $this->assertSame(
      $plugins, $loader->plugins($plugins)
    );
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testPluginsGetWithImplicitCreate() {
    $loader = new \Papaya\Plugin\Loader();
    $this->assertInstanceOf(
      \Papaya\Plugin\Collection::class, $loader->plugins()
    );
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testOptionsGetAfterSet() {
    $options = $this->createMock(\Papaya\Plugin\Option\Groups::class);
    $loader = new \Papaya\Plugin\Loader();
    $this->assertSame(
      $options, $loader->options($options)
    );
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testOptionsGetWithImplicitCreate() {
    $loader = new \Papaya\Plugin\Loader();
    $this->assertInstanceOf(
      \Papaya\Plugin\Option\Groups::class, $loader->options()
    );
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testMagicPropertyPlguinsGetAfterSet() {
    $plugins = $this->createMock(\Papaya\Plugin\Collection::class);
    $loader = new \Papaya\Plugin\Loader();
    $loader->plugins = $plugins;
    $this->assertSame(
      $plugins, $loader->plugins
    );
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testMagicPropertyOptionsGetAfterSet() {
    $options = $this->createMock(\Papaya\Plugin\Option\Groups::class);
    $loader = new \Papaya\Plugin\Loader();
    $loader->options = $options;
    $this->assertSame(
      $options, $loader->options
    );
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testMagicMethodGetWithInvalidPropertyExpectingException() {
    $loader = new \Papaya\Plugin\Loader();
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Can not read unknown property Papaya\Plugin\Loader::$unkownProperty');
    /** @noinspection PhpUndefinedFieldInspection */
    $loader->unkownProperty;
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testMagicMethodSetWithInvalidPropertyExpectingException() {
    $loader = new \Papaya\Plugin\Loader();
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Can not write unknown property Papaya\Plugin\Loader::$unkownProperty');
    /** @noinspection PhpUndefinedFieldInspection */
    $loader->unkownProperty = 'dummy';
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testHasExpectingTrue() {
    $loader = new \Papaya\Plugin\Loader();
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
  * @covers \Papaya\Plugin\Loader
  */
  public function testHasExpectingFalse() {
    $plugins = $this->createMock(\Papaya\Plugin\Collection::class);
    $loader = new \Papaya\Plugin\Loader();
    $loader->plugins($plugins);
    $this->assertFalse($loader->has('123'));
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testGet() {
    $loader = new \Papaya\Plugin\Loader();
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => __DIR__.'/TestData/',
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
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetPluginInstance() {
    $loader = new \Papaya\Plugin\Loader();
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
    /** @noinspection PhpDeprecationInspection */
    $this->assertInstanceOf(
      PluginLoader_SampleClass::class, $loader->getPluginInstance('123')
    );
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetWithPluginData() {
    $loader = new \Papaya\Plugin\Loader();
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
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */'<data version="2"><data-element name="foo">bar</data-element></data>',
      $samplePlugin->data
    );
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetWithPluginDataAsString() {
    $loader = new \Papaya\Plugin\Loader();
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
      /** @lang XML */'<data version="2"><data-element name="foo">bar</data-element></data>'
    );
    $this->assertEquals(
    /** @lang XML */'<data version="2"><data-element name="foo">bar</data-element></data>',
      $samplePlugin->data
    );
  }


  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetEditableWithPluginData() {
    $loader = new \Papaya\Plugin\Loader();
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
    /** @lang XML */'<data version="2"><data-element name="foo">bar</data-element></data>',
      $samplePlugin->content->getXml()
    );
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetEditableWithPluginDataAsString() {
    $loader = new \Papaya\Plugin\Loader();
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
      /** @lang XML */'<data version="2"><data-element name="foo">bar</data-element></data>'
    );
    $this->assertEquals(
    /** @lang XML */'<data version="2"><data-element name="foo">bar</data-element></data>',
      $samplePlugin->content->getXml()
    );
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetWithSingleInstance() {
    $loader = new \Papaya\Plugin\Loader();
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
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetWithNonExistingPlugin() {
    $loader = new \Papaya\Plugin\Loader();
    $loader->plugins(
      $this->getPluginListFixture(
        FALSE
      )
    );
    $this->assertNull($loader->get('123'));
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetWithInvalidPluginFileExpectingMessage() {
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Log::class));
    $loader = new \Papaya\Plugin\Loader();
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
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetWithAutloaderPrefix() {
    \PapayaAutoloader::clear();
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->any())
      ->method('dispatch')
      ->withAnyParameters();
    $loader = new \Papaya\Plugin\Loader();
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
          'path' => str_replace('\\', '/', __DIR__).'/TestData/',
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
        '/Plugin/Loader/Autoload/Prefix/' => str_replace('\\', '/', __DIR__).'/TestData/'
      ),
      '_paths',
      \PapayaAutoloader::class
    );
    \PapayaAutoloader::clear();
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetWithAutloaderClassmap() {
    \PapayaAutoloader::clear();
    $loader = new \Papaya\Plugin\Loader();
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => str_replace('\\', '/', __DIR__).'/TestData/',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass',
          'prefix' => '',
          'classes' => '_classmap.php'
        )
      )
    );
    $this->assertInstanceOf('PluginLoader_SampleClass', $loader->get('123'));
    \PapayaAutoloader::clear();
  }


  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetWithInvalidPluginClassExpectingMessage() {
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Log::class));
    $loader = new \Papaya\Plugin\Loader();
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
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetFileName() {
    \PapayaAutoloader::clear();
    $loader = new \Papaya\Plugin\Loader();
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
      \PapayaAutoloader::class
    );
    \PapayaAutoloader::clear();
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetFileNameFromClassmap() {
    \PapayaAutoloader::clear();
    $loader = new \Papaya\Plugin\Loader();
    $loader->papaya($this->mockPapaya()->application());
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => $path = str_replace('\\', '/', __DIR__).'/TestData/',
          'file' => '',
          'class' => 'PluginLoader_SampleClass',
          'classes' => '_classmap.php',
        )
      )
    );
    $this->assertEquals(
      $path.'SampleClass.php', $loader->getFileName('123')
    );
    \PapayaAutoloader::clear();
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetFileNameWithPathFromOptions() {
    \PapayaAutoloader::clear();
    $loader = new \Papaya\Plugin\Loader();
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
    \PapayaAutoloader::clear();
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetFileNameWithComposerPath() {
    \PapayaAutoloader::clear();
    $loader = new \Papaya\Plugin\Loader();
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
    \PapayaAutoloader::clear();
  }

  /**
  * @covers \Papaya\Plugin\Loader
  */
  public function testGetFileNameOfNonExistingPlugin() {
    $loader = new \Papaya\Plugin\Loader();
    $loader->plugins(
      $this->getPluginListFixture(
        FALSE
      )
    );
    $this->assertEquals('', $loader->getFileName('123'));
  }

  /*************************
   * Fixtures
   ************************/

  /**
   * @param mixed $record
   * @return \PHPUnit_Framework_MockObject_MockObject
   */
  private function getPluginListFixture($record) {
    $plugins = $this->createMock(\Papaya\Plugin\Collection::class);
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
