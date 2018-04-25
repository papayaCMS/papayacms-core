<?php
require_once __DIR__.'/../../bootstrap.php';

class PapayaConfigurationTest extends PapayaTestCase {

  /**
  * @covers PapayaConfiguration::__construct
  * @covers PapayaConfiguration::defineOptions
  */
  public function testConstructorDefinesOptions() {
    $config = new PapayaConfiguration(
      array('sample' => NULL)
    );
    $this->assertEquals(
      array('sample' => NULL), iterator_to_array($config)
    );
  }

  /**
  * @covers PapayaConfiguration::__construct
  * @covers PapayaConfiguration::defineOptions
  */
  public function testConstructorDefinesInvalidOptionExpectingException() {
    $this->expectException(UnexpectedValueException::class);
    $config = new PapayaConfiguration(
      array('sample' => new stdClass)
    );
  }

  /**
  * @covers PapayaConfiguration::getHash
  */
  public function testGetHash() {
    $config = new PapayaConfiguration(array());
    $this->assertEquals(
      md5(serialize(array())), $config->getHash()
    );
  }

  /**
  * @covers PapayaConfiguration::get
  * @covers PapayaConfiguration::filter
  */
  public function testGet() {
    $config = new PapayaConfiguration_TestProxy();
    $this->assertEquals(
      42, $config->get('SAMPLE_INT')
    );
  }

  /**
  * @covers PapayaConfiguration::get
  * @covers PapayaConfiguration::filter
  */
  public function testGetWithCasting() {
    $config = new PapayaConfiguration_TestProxy();
    $this->assertSame(
      42, $config->get('SAMPLE_INT', 23)
    );
  }

  /**
  * @covers PapayaConfiguration::get
  * @covers PapayaConfiguration::filter
  */
  public function testGetWithUnknownOptionReturningDefault() {
    $config = new PapayaConfiguration(array());
    $this->assertSame(
      23, $config->get('UNKNOWN_OPTION', 23)
    );
  }

  /**
  * @covers PapayaConfiguration::get
  * @covers PapayaConfiguration::filter
  */
  public function testGetWithFilter() {
    $config = new PapayaConfiguration_TestProxy();
    $this->assertSame(
      23, $config->get('SAMPLE_INT', 23, new PapayaFilterInteger(0, 1))
    );
  }
  /**
  * @covers PapayaConfiguration::getOption
  * @covers PapayaConfiguration::filter
  */
  public function testGetOptionWithCasting() {
    $config = new PapayaConfiguration_TestProxy();
    $this->assertSame(
      42, $config->getOption('SAMPLE_INT', 23)
    );
  }

  /**
  * @covers PapayaConfiguration::set
  */
  public function testSet() {
    $config = new PapayaConfiguration_TestProxy();
    $config->set('SAMPLE_INT', 21);
    $this->assertEquals(
      21, $config->get('SAMPLE_INT')
    );
  }

  /**
  * @covers PapayaConfiguration::set
  */
  public function testSetCanNotChangeTheType() {
    $config = new PapayaConfiguration_TestProxy();
    $config->set('SAMPLE_INT', '23');
    $this->assertSame(23, $config->get('SAMPLE_INT'));
  }
  /**
  * @covers PapayaConfiguration::has
  */
  public function testHasExpectingTrue() {
    $config = new PapayaConfiguration_TestProxy();
    $this->assertTrue($config->has('SAMPLE_INT'));
  }

  /**
  * @covers PapayaConfiguration::has
  */
  public function testHasExpectingFalse() {
    $config = new PapayaConfiguration_TestProxy();
    $this->assertFalse($config->has('INVALID_OPTION_NAME'));
  }

  /**
  * @covers PapayaConfiguration::assign
  */
  public function testAssign() {
    $config = new PapayaConfiguration_TestProxy();
    $config->assign(array('SAMPLE_INT' => 21));
    $this->assertEquals(
      21, $config->get('SAMPLE_INT')
    );
  }

  /**
  * @covers PapayaConfiguration::assign
  */
  public function testAssignWithInvalidArgumentExpectingException() {
    $config = new PapayaConfiguration_TestProxy();
    $this->expectException(UnexpectedValueException::class);
    $config->assign('STRING');
  }

  /**
  * @covers PapayaConfiguration::storage
  */
  public function testStorageGetAfterSet() {
    $storage = $this->createMock(PapayaConfigurationStorage::class);
    $config = new PapayaConfiguration_TestProxy();
    $this->assertSame(
      $storage, $config->storage($storage)
    );
  }

  /**
  * @covers PapayaConfiguration::storage
  */
  public function testStorageGetBeforeSetExpectingException() {
    $config = new PapayaConfiguration_TestProxy();
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('No storage assigned to configuration.');
    $storage = $config->storage();
  }

  /**
  * @covers PapayaConfiguration::load
  */
  public function testLoad() {
    $storage = $this->createMock(PapayaConfigurationStorage::class);
    $storage
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $storage
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator(array('SAMPLE_INT' => 23))));
    $config = new PapayaConfiguration_TestProxy();
    $config->storage($storage);
    $config->load();
    $this->assertEquals(
      23, $config->get('SAMPLE_INT')
    );
  }

  /**
  * @covers PapayaConfiguration::load
  */
  public function testLoadWithUnknownOptionsAreIgnored() {
    $storage = $this->createMock(PapayaConfigurationStorage::class);
    $storage
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $storage
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator(array('SAMPLE_INT_UNKNOWN' => 23))));
    $config = new PapayaConfiguration_TestProxy();
    $config->storage($storage);
    $config->load();
    $this->assertEquals(
      array(
        'SAMPLE_INT' => 42,
        'PAPAYA_INCLUDE_PATH' => 'not_defined'
      ),
      iterator_to_array($config)
    );
  }

  /**
  * @covers PapayaConfiguration::load
  */
  public function testLoadFailure() {
    $storage = $this->createMock(PapayaConfigurationStorage::class);
    $storage
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(FALSE));
    $config = new PapayaConfiguration_TestProxy();
    $config->load($storage);
    $this->assertEquals(
      42, $config->get('SAMPLE_INT')
    );
  }

  /**
  * @covers PapayaConfiguration::getIterator
  */
  public function testGetIterator() {
    $config = new PapayaConfiguration_TestProxy();
    $iterator = $config->getIterator();
    $this->assertInstanceOf(PapayaConfigurationIterator::class, $iterator);
    $this->assertAttributeEquals(
      array('SAMPLE_INT', 'PAPAYA_INCLUDE_PATH'),
      '_names',
      $iterator
    );
  }

  /**
  * @covers PapayaConfiguration::__isset
  */
  public function testDynamicPropertyIssetExpectingTrue() {
    $config = new PapayaConfiguration_TestProxy();
    $this->assertTrue(isset($config->sampleInt));
  }

  /**
  * @covers PapayaConfiguration::__isset
  */
  public function testDynamicPropertyIssetExpectingFalse() {
    $config = new PapayaConfiguration_TestProxy();
    $this->assertFalse(isset($config->unknwownOptionName));
  }

  /**
  * @covers PapayaConfiguration::__get
  */
  public function testDynamicPropertyGet() {
    $config = new PapayaConfiguration_TestProxy();
    $this->assertEquals(42, $config->sampleInt);
  }

  /**
  * @covers PapayaConfiguration::__set
  */
  public function testDynamicPropertySet() {
    $config = new PapayaConfiguration_TestProxy();
    $config->sampleInt = 23;
    $this->assertEquals(23, $config->sampleInt);
  }

  /**
  * @covers PapayaConfiguration::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $config = new PapayaConfiguration_TestProxy();
    $this->assertTrue(isset($config['SAMPLE_INT']));
  }

  /**
  * @covers PapayaConfiguration::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $config = new PapayaConfiguration_TestProxy();
    $this->assertFalse(isset($config['UNKNOWN_OPTION_NAME']));
  }

  /**
  * @covers PapayaConfiguration::offsetGet
  */
  public function testOffsetGet() {
    $config = new PapayaConfiguration_TestProxy();
    $this->assertEquals(42, $config['SAMPLE_INT']);
  }

  /**
  * @covers PapayaConfiguration::offsetSet
  */
  public function testOffsetSet() {
    $config = new PapayaConfiguration_TestProxy();
    $config['SAMPLE_INT'] = 23;
    $this->assertEquals(23, $config['SAMPLE_INT']);
  }

  /**
  * @covers PapayaConfiguration::offsetUnset
  */
  public function testOffsetUnsetExpectingException() {
    $config = new PapayaConfiguration_TestProxy();
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('LogicException: You can only read or write options, not remove them.');
    unset($config['SAMPLE_INT']);
  }
}

class PapayaConfiguration_TestProxy extends PapayaConfiguration {

  public function __construct() {
    parent::__construct(
      array(
        'SAMPLE_INT' => 42,
        'PAPAYA_INCLUDE_PATH' => 'not_defined'
      )
    );
  }
}
