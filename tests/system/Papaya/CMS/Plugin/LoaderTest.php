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

namespace Papaya\CMS\Plugin {

  use Papaya\Autoloader;
  use Papaya\Message\Log as LogMessage;
  use Papaya\Message\Manager as MessageManager;
  use Papaya\TestCase;
  use PluginLoader_SampleClass;
  use PluginLoader_SampleClassEditable;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\CMS\Plugin\Loader
   */
  class LoaderTest extends TestCase {

    public function testPluginsGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Collection $plugins */
      $plugins = $this->createMock(Collection::class);
      $loader = new Loader();
      $this->assertSame(
        $plugins, $loader->plugins($plugins)
      );
    }

    public function testPluginsGetWithImplicitCreate() {
      $loader = new Loader();
      $this->assertInstanceOf(
        Collection::class, $loader->plugins()
      );
    }

    public function testOptionsGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Option\Groups $options */
      $options = $this->createMock(Option\Groups::class);
      $loader = new Loader();
      $this->assertSame(
        $options, $loader->options($options)
      );
    }

    public function testOptionsGetWithImplicitCreate() {
      $loader = new Loader();
      $this->assertInstanceOf(
        Option\Groups::class, $loader->options()
      );
    }

    public function testMagicPropertyPluginsGetAfterSet() {
      $plugins = $this->createMock(Collection::class);
      $loader = new Loader();
      $this->assertTrue(isset($loader->plugins));
      $loader->plugins = $plugins;
      $this->assertSame(
        $plugins, $loader->plugins
      );
    }

    public function testMagicPropertyOptionsGetAfterSet() {
      $options = $this->createMock(Option\Groups::class);
      $loader = new Loader();
      $loader->options = $options;
      $this->assertTrue(isset($loader->options));
      $this->assertSame(
        $options, $loader->options
      );
    }

    public function testMagicMethodGetWithInvalidPropertyExpectingException() {
      $loader = new Loader();
      $this->assertFalse(isset($loader->unknownProperty));
      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('Can not read unknown property Papaya\CMS\Plugin\Loader::$unknownProperty');
      /** @noinspection PhpUndefinedFieldInspection */
      $loader->unknownProperty;
    }

    public function testMagicMethodSetWithInvalidPropertyExpectingException() {
      $loader = new Loader();
      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('Can not write unknown property Papaya\CMS\Plugin\Loader::$unknownProperty');
      /** @noinspection PhpUndefinedFieldInspection */
      $loader->unknownProperty = 'dummy';
    }

    public function testHasExpectingTrue() {
      $loader = new Loader();
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => '',
              'file' => 'SampleClass.php',
              'class' => 'PluginLoader_SampleClass'
            ]
          ]
        )
      );
      $this->assertTrue($loader->has('123'));
    }

    public function testHasExpectingFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Collection $plugins */
      $plugins = $this->createMock(Collection::class);
      $loader = new Loader();
      $loader->plugins($plugins);
      $this->assertFalse($loader->has('123'));
    }

    public function testGet() {
      $loader = new Loader();
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => __DIR__.'/TestData/',
              'file' => 'SampleClass.php',
              'class' => 'PluginLoader_SampleClass'
            ]
          ]
        )
      );
      $this->assertInstanceOf(
        'PluginLoader_SampleClass', $loader->get('123')
      );
    }

    public function testGetPluginInstance() {
      $loader = new Loader();
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => '',
              'file' => 'SampleClass.php',
              'class' => 'PluginLoader_SampleClass'
            ]
          ]
        )
      );
      /** @noinspection PhpDeprecationInspection */
      $this->assertInstanceOf(
        'PluginLoader_SampleClass', $loader->getPluginInstance('123')
      );
    }

    public function testGetWithPluginData() {
      $loader = new Loader();
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => '',
              'file' => 'SampleClass.php',
              'class' => 'PluginLoader_SampleClass'
            ]
          ]
        )
      );
      $samplePlugin = $loader->get('123', NULL, ['foo' => 'bar']);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<data version="2"><data-element name="foo">bar</data-element></data>',
        $samplePlugin->data
      );
    }

    public function testGetWithPluginDataAsString() {
      $loader = new Loader();
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => '',
              'file' => 'SampleClass.php',
              'class' => 'PluginLoader_SampleClass'
            ]
          ]
        )
      );
      $samplePlugin = $loader->get(
        '123',
        NULL,
        /** @lang XML */
        '<data version="2"><data-element name="foo">bar</data-element></data>'
      );
      $this->assertEquals(
      /** @lang XML */
        '<data version="2"><data-element name="foo">bar</data-element></data>',
        $samplePlugin->data
      );
    }

    public function testGetEditableWithPluginData() {
      $loader = new Loader();
      $loader->papaya($this->mockPapaya()->application());
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => '',
              'file' => __DIR__.'/TestData/SampleClass.php',
              'class' => 'PluginLoader_SampleClassEditable'
            ]
          ]
        )
      );
      /** @var PluginLoader_SampleClassEditable $samplePlugin */
      $samplePlugin = $loader->get('123', NULL, ['foo' => 'bar']);
      $this->assertEquals(
      /** @lang XML */
        '<data version="2"><data-element name="foo">bar</data-element></data>',
        $samplePlugin->content->getXml()
      );
    }

    public function testGetEditableWithPluginDataAsString() {
      $loader = new Loader();
      $loader->papaya($this->mockPapaya()->application());
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => '',
              'file' => __DIR__.'/TestData/SampleClass.php',
              'class' => 'PluginLoader_SampleClassEditable'
            ]
          ]
        )
      );
      $samplePlugin = $loader->get(
        '123',
        NULL,
        /** @lang XML */
        '<data version="2"><data-element name="foo">bar</data-element></data>'
      );
      $this->assertEquals(
      /** @lang XML */
        '<data version="2"><data-element name="foo">bar</data-element></data>',
        $samplePlugin->content->getXml()
      );
    }

    public function testGetWithSingleInstance() {
      $loader = new Loader();
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => '',
              'file' => 'SampleClass.php',
              'class' => 'PluginLoader_SampleClass'
            ]
          ]
        )
      );
      $plugin = $loader->get('123', NULL, [], TRUE);
      $this->assertSame(
        $plugin, $loader->get('123', NULL, [], TRUE)
      );
    }

    public function testGetWithNonExistingPlugin() {
      $loader = new Loader();
      $loader->plugins(
        $this->getPluginListFixture([])
      );
      $this->assertNull($loader->get('123'));
    }

    public function testGetWithInvalidPluginFileExpectingMessage() {
      $messages = $this->createMock(MessageManager::class);
      $messages
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->isInstanceOf(LogMessage::class));
      $loader = new Loader();
      $loader->papaya(
        $this->mockPapaya()->application(
          [
            'messages' => $messages
          ]
        )
      );
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => '',
              'file' => 'SampleClass.php',
              'class' => 'PluginLoader_InvalidSampleClass'
            ]
          ]
        )
      );
      $this->assertNull($loader->get('123'));
    }

    public function testGetWithAutoloaderPrefix() {
      Autoloader::clear();
      $messages = $this->createMock(MessageManager::class);
      $messages
        ->method('dispatch')
        ->withAnyParameters();
      $loader = new Loader();
      $loader->papaya(
        $this->mockPapaya()->application(
          [
            'messages' => $messages
          ]
        )
      );
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => str_replace('\\', '/', __DIR__).'/TestData/',
              'file' => 'SampleClass.php',
              'class' => 'PluginLoader_InvalidSampleClass',
              'prefix' => 'PluginLoaderAutoloadPrefix',
              'classes' => ''
            ]
          ]
        )
      );
      $this->assertNull($loader->get('123'));
      $this->assertEquals(
        [
          '/Plugin/Loader/Autoload/Prefix/' => str_replace('\\', '/', __DIR__).'/TestData/'
        ],
        Autoloader::getRegisteredPaths()
      );
      Autoloader::clear();
    }

    public function testGetWithAutoloaderClassmap() {
      Autoloader::clear();
      $loader = new Loader();
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => str_replace('\\', '/', __DIR__).'/TestData/',
              'file' => 'SampleClass.php',
              'class' => 'PluginLoader_SampleClass',
              'prefix' => '',
              'classes' => '_classmap.php'
            ]
          ]
        )
      );
      $this->assertInstanceOf('PluginLoader_SampleClass', $loader->get('123'));
      Autoloader::clear();
    }

    public function testGetWithInvalidPluginClassExpectingMessage() {
      $messages = $this->createMock(MessageManager::class);
      $messages
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->isInstanceOf(LogMessage::class));
      $loader = new Loader();
      $loader->papaya(
        $this->mockPapaya()->application(
          [
            'messages' => $messages
          ]
        )
      );
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => '',
              'file' => 'SampleClass.php',
              'class' => 'PluginLoader_InvalidSampleClass'
            ]
          ]
        )
      );
      $this->assertNull($loader->get('123'));
    }

    public function testGetFileName() {
      Autoloader::clear();
      $loader = new Loader();
      $loader->papaya($this->mockPapaya()->application());
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => '/base/path/sample/path/',
              'file' => 'sample.php',
              'class' => 'SampleClass',
              'prefix' => 'PluginLoaderAutoloadPrefix',
            ]
          ]
        )
      );
      $this->assertEquals(
        '/base/path/sample/path/sample.php', $loader->getFileName('123')
      );
      $this->assertEquals(
        [
          '/Plugin/Loader/Autoload/Prefix/' => '/base/path/sample/path/'
        ],
        Autoloader::getRegisteredPaths()
      );
      Autoloader::clear();
    }

    public function testGetFileNameFromClassmap() {
      Autoloader::clear();
      $loader = new Loader();
      $loader->papaya($this->mockPapaya()->application());
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => $path = str_replace('\\', '/', __DIR__).'/TestData/',
              'file' => '',
              'class' => 'PluginLoader_SampleClass',
              'classes' => '_classmap.php',
            ]
          ]
        )
      );
      $this->assertEquals(
        $path.'SampleClass.php', $loader->getFileName('123')
      );
      Autoloader::clear();
    }

    public function testGetFileNameWithPathFromOptions() {
      Autoloader::clear();
      $loader = new Loader();
      $loader->papaya(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options(['PAPAYA_INCLUDE_PATH' => '/foo/bar'])
          ]
        )
      );
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => 'sample/path/',
              'file' => 'sample.php',
              'class' => 'SampleClass',
              'prefix' => 'PluginLoaderAutoloadPrefix',
            ]
          ]
        )
      );
      $this->assertEquals(
        '/foo/bar/modules/sample/path/sample.php', $loader->getFileName('123')
      );
      Autoloader::clear();
    }

    public function testGetFileNameWithComposerPath() {
      Autoloader::clear();
      $loader = new Loader();
      $loader->papaya($this->mockPapaya()->application());
      $loader->plugins(
        $this->getPluginListFixture(
          [
            123 => [
              'guid' => '123',
              'path' => 'vendor:/sample/path/',
              'file' => 'sample.php',
              'class' => 'SampleClass',
              'prefix' => 'PluginLoaderAutoloadPrefix',
            ]
          ]
        )
      );
      $this->assertStringEndsWith(
        '/vendor/sample/path/sample.php', $loader->getFileName('123')
      );
      Autoloader::clear();
    }

    public function testGetFileNameOfNonExistingPlugin() {
      $loader = new Loader();
      $loader->plugins(
        $this->getPluginListFixture([])
      );
      $this->assertEquals('', $loader->getFileName('123'));
    }

    public function testPreloadReturnsTrue() {
      $loader = new Loader();
      $this->assertTrue($loader->preload());
    }

    public function testGetPluginInstancesFilteredByType() {
      $data = [
        123 => [
          'guid' => '123',
          'path' => str_replace('\\', '/', __DIR__).'/TestData/',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass'
        ]
      ];
      $plugins = $this->getPluginListFixture($data);
      $plugins
        ->expects($this->once())
        ->method('withType')
        ->with(Types::LOGGER)
        ->willReturn($data);
      $loader = new Loader();

      $loader->plugins($plugins);
      $instances = iterator_to_array($loader->withType(Types::LOGGER));
      $this->assertInstanceOf(PluginLoader_SampleClass::class, $instances[123]);
    }

    /*************************
     * Fixtures
     ************************/

    /**
     * @param array $records
     * @return \PHPUnit_Framework_MockObject_MockObject|Collection
     */
    private function getPluginListFixture(array $records) {
      $plugins = $this->createMock(Collection::class);
      $plugins
        ->method('offsetExists')
        ->willReturnCallback(
          static function($offset) use ($records) {
            return isset($records[$offset]);
          }
        );
      $plugins
        ->method('offsetGet')
        ->willReturnCallback(
          static function($offset) use ($records) {
            return isset($records[$offset]) ? $records[$offset] : NULL;
          }
        );
      return $plugins;
    }
  }
}
