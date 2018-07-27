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

use Papaya\Configuration\Iterator;
use Papaya\Configuration\Storage;

require_once __DIR__.'/../../bootstrap.php';

class PapayaConfigurationTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Configuration::__construct
  * @covers \Papaya\Configuration::defineOptions
  */
  public function testConstructorDefinesOptions() {
    $config = new \Papaya\Configuration(
      array('sample' => NULL)
    );
    $this->assertEquals(
      array('sample' => NULL), iterator_to_array($config)
    );
  }

  /**
  * @covers \Papaya\Configuration::__construct
  * @covers \Papaya\Configuration::defineOptions
  */
  public function testConstructorDefinesInvalidOptionExpectingException() {
    $this->expectException(UnexpectedValueException::class);
    new \Papaya\Configuration(
      array('sample' => new stdClass)
    );
  }

  /**
  * @covers \Papaya\Configuration::getHash
  */
  public function testGetHash() {
    $config = new \Papaya\Configuration(array());
    $this->assertEquals(
      md5(serialize(array())), $config->getHash()
    );
  }

  /**
  * @covers \Papaya\Configuration::get
  * @covers \Papaya\Configuration::filter
  */
  public function testGet() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->assertEquals(
      42, $config->get('SAMPLE_INT')
    );
  }

  /**
  * @covers \Papaya\Configuration::get
  * @covers \Papaya\Configuration::filter
  */
  public function testGetWithCasting() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->assertSame(
      42, $config->get('SAMPLE_INT', 23)
    );
  }

  /**
  * @covers \Papaya\Configuration::get
  * @covers \Papaya\Configuration::filter
  */
  public function testGetWithUnknownOptionReturningDefault() {
    $config = new \Papaya\Configuration(array());
    $this->assertSame(
      23, $config->get('UNKNOWN_OPTION', 23)
    );
  }

  /**
  * @covers \Papaya\Configuration::get
  * @covers \Papaya\Configuration::filter
  */
  public function testGetWithFilter() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->assertSame(
      23, $config->get('SAMPLE_INT', 23, new \Papaya\Filter\IntegerValue(0, 1))
    );
  }
  /**
  * @covers \Papaya\Configuration::getOption
  * @covers \Papaya\Configuration::filter
  */
  public function testGetOptionWithCasting() {
    $config = new \PapayaConfiguration_TestProxy();
    /** @noinspection PhpDeprecationInspection */
    $this->assertSame(
      42, $config->getOption('SAMPLE_INT', 23)
    );
  }

  /**
  * @covers \Papaya\Configuration::set
  */
  public function testSet() {
    $config = new \PapayaConfiguration_TestProxy();
    $config->set('SAMPLE_INT', 21);
    $this->assertEquals(
      21, $config->get('SAMPLE_INT')
    );
  }

  /**
  * @covers \Papaya\Configuration::set
  */
  public function testSetCanNotChangeTheType() {
    $config = new \PapayaConfiguration_TestProxy();
    $config->set('SAMPLE_INT', '23');
    $this->assertSame(23, $config->get('SAMPLE_INT'));
  }
  /**
  * @covers \Papaya\Configuration::has
  */
  public function testHasExpectingTrue() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->assertTrue($config->has('SAMPLE_INT'));
  }

  /**
  * @covers \Papaya\Configuration::has
  */
  public function testHasExpectingFalse() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->assertFalse($config->has('INVALID_OPTION_NAME'));
  }

  /**
  * @covers \Papaya\Configuration::assign
  */
  public function testAssign() {
    $config = new \PapayaConfiguration_TestProxy();
    $config->assign(array('SAMPLE_INT' => 21));
    $this->assertEquals(
      21, $config->get('SAMPLE_INT')
    );
  }

  /**
  * @covers \Papaya\Configuration::assign
  */
  public function testAssignWithInvalidArgumentExpectingException() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->expectException(UnexpectedValueException::class);
    /** @noinspection PhpParamsInspection */
    $config->assign('STRING');
  }

  /**
  * @covers \Papaya\Configuration::storage
  */
  public function testStorageGetAfterSet() {
    $storage = $this->createMock(Storage::class);
    $config = new \PapayaConfiguration_TestProxy();
    $this->assertSame(
      $storage, $config->storage($storage)
    );
  }

  /**
  * @covers \Papaya\Configuration::storage
  */
  public function testStorageGetBeforeSetExpectingException() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('No storage assigned to configuration.');
    $config->storage();
  }

  /**
  * @covers \Papaya\Configuration::load
  */
  public function testLoad() {
    $storage = $this->createMock(Storage::class);
    $storage
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $storage
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator(array('SAMPLE_INT' => 23))));
    $config = new \PapayaConfiguration_TestProxy();
    $config->storage($storage);
    $config->load();
    $this->assertEquals(
      23, $config->get('SAMPLE_INT')
    );
  }

  /**
  * @covers \Papaya\Configuration::load
  */
  public function testLoadWithUnknownOptionsAreIgnored() {
    $storage = $this->createMock(Storage::class);
    $storage
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(TRUE));
    $storage
      ->expects($this->once())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator(array('SAMPLE_INT_UNKNOWN' => 23))));
    $config = new \PapayaConfiguration_TestProxy();
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
  * @covers \Papaya\Configuration::load
  */
  public function testLoadFailure() {
    $storage = $this->createMock(Storage::class);
    $storage
      ->expects($this->once())
      ->method('load')
      ->will($this->returnValue(FALSE));
    $config = new \PapayaConfiguration_TestProxy();
    $config->load($storage);
    $this->assertEquals(
      42, $config->get('SAMPLE_INT')
    );
  }

  /**
  * @covers \Papaya\Configuration::getIterator
  */
  public function testGetIterator() {
    $config = new \PapayaConfiguration_TestProxy();
    $iterator = $config->getIterator();
    $this->assertInstanceOf(Iterator::class, $iterator);
    $this->assertAttributeEquals(
      array('SAMPLE_INT', 'PAPAYA_INCLUDE_PATH'),
      '_names',
      $iterator
    );
  }

  /**
  * @covers \Papaya\Configuration::__isset
  */
  public function testDynamicPropertyIssetExpectingTrue() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->assertTrue(isset($config->sampleInt));
  }

  /**
  * @covers \Papaya\Configuration::__isset
  */
  public function testDynamicPropertyIssetExpectingFalse() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->assertFalse(isset($config->unknwownOptionName));
  }

  /**
  * @covers \Papaya\Configuration::__get
  */
  public function testDynamicPropertyGet() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->assertEquals(42, $config->sampleInt);
  }

  /**
  * @covers \Papaya\Configuration::__set
  */
  public function testDynamicPropertySet() {
    $config = new \PapayaConfiguration_TestProxy();
    $config->sampleInt = 23;
    $this->assertEquals(23, $config->sampleInt);
  }

  /**
  * @covers \Papaya\Configuration::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->assertTrue(isset($config['SAMPLE_INT']));
  }

  /**
  * @covers \Papaya\Configuration::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->assertFalse(isset($config['UNKNOWN_OPTION_NAME']));
  }

  /**
  * @covers \Papaya\Configuration::offsetGet
  */
  public function testOffsetGet() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->assertEquals(42, $config['SAMPLE_INT']);
  }

  /**
  * @covers \Papaya\Configuration::offsetSet
  */
  public function testOffsetSet() {
    $config = new \PapayaConfiguration_TestProxy();
    $config['SAMPLE_INT'] = 23;
    $this->assertEquals(23, $config['SAMPLE_INT']);
  }

  /**
  * @covers \Papaya\Configuration::offsetUnset
  */
  public function testOffsetUnsetExpectingException() {
    $config = new \PapayaConfiguration_TestProxy();
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('LogicException: You can only read or write options, not remove them.');
    unset($config['SAMPLE_INT']);
  }
}

/**
 * @property int sampleInt
 */
class PapayaConfiguration_TestProxy extends \Papaya\Configuration {

  public function __construct() {
    parent::__construct(
      array(
        'SAMPLE_INT' => 42,
        'PAPAYA_INCLUDE_PATH' => 'not_defined'
      )
    );
  }
}
