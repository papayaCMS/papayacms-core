<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaPluginOptionStorageTest extends PapayaTestCase {

  /**
  * @covers PapayaPluginOptionStorage::__construct
  */
  public function testConstructor() {
    $storage = new PapayaPluginOptionStorage('AB123456789012345678901234567890');
    $this->assertAttributeEquals(
      'ab123456789012345678901234567890', '_guid', $storage
    );
  }

  /**
  * @covers PapayaPluginOptionStorage::load
  */
  public function testLoad() {
    $options = $this->createMock(PapayaContentModuleOptions::class);
    $options
      ->expects($this->once())
      ->method('load')
      ->with(array('guid' => 'ab123456789012345678901234567890'))
      ->will($this->returnValue(TRUE));
    $storage = new PapayaPluginOptionStorage('ab123456789012345678901234567890');
    $storage->options($options);
    $this->assertTrue($storage->load());
  }

  /**
  * @covers PapayaPluginOptionStorage::getIterator
  */
  public function testGetIterator() {
    $options = $this->createMock(PapayaContentModuleOptions::class);
    $options
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              array(
                'name' => 'foo',
                'value' => 'bar'
              )
            )
          )
        )
      );
    $storage = new PapayaPluginOptionStorage('ab123456789012345678901234567890');
    $storage->options($options);
    $this->assertEquals(
      array('foo' => 'bar'),
      iterator_to_array($storage)
    );
  }

  /**
  * @covers PapayaPluginOptionStorage::options
  */
  public function testOptionsGetAfterSet() {
    $options = $this->createMock(PapayaContentModuleOptions::class);
    $storage = new PapayaPluginOptionStorage('ab123456789012345678901234567890');
    $storage->options($options);
    $this->assertSame($options, $storage->options());
  }

  /**
  * @covers PapayaPluginOptionStorage::options
  */
  public function testOptionsGetImplicitCreate() {
    $storage = new PapayaPluginOptionStorage('ab123456789012345678901234567890');
    $this->assertInstanceOf('PapayaContentModuleOptions', $options = $storage->options());
  }
}
