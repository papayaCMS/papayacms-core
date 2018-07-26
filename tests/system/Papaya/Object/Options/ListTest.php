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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaObjectOptionsListTest extends \PapayaTestCase {

  /**
  * @covers \PapayaObjectOptionsList::__construct
  */
  public function testConstructor() {
    $options = new \PapayaObjectOptionsList(
      array('Sample' => 'Hallo World')
    );
    $this->assertAttributeSame(
      array('SAMPLE' => 'Hallo World'),
      '_options',
      $options
    );
  }

  /**
   * @covers \PapayaObjectOptionsList::offsetSet
   * @covers \PapayaObjectOptionsList::_prepareName
   * @dataProvider offsetSetDataProvider
   * @param mixed $expected
   * @param string $name
   * @param mixed $value
   */
  public function testOffsetSet($expected, $name, $value) {
    $options = new \PapayaObjectOptionsList();
    $options[$name] = $value;
    $this->assertAttributeSame(
      $expected,
      '_options',
      $options
    );
  }

  /**
  * @covers \PapayaObjectOptionsList::set
  */
  public function testSet() {
    $options = new \PapayaObjectOptionsList();
    $options->set('foo', 'bar');
    $this->assertEquals(
      'bar',
      $options['foo']
    );
  }

  /**
  * @covers \PapayaObjectOptionsList::offsetSet
  */
  public function testOffsetSetWithNullRemovesElement() {
    $options = new \PapayaObjectOptionsList();
    $options['sample'] = 'failed';
    $this->assertEquals('failed', $options['sample']);
    $options['sample'] = NULL;
    $this->assertAttributeSame(
      array(),
      '_options',
      $options
    );
  }

  /**
  * @covers \PapayaObjectOptionsList::offsetSet
  * @covers \PapayaObjectOptionsList::_prepareName
  */
  public function testOffsetSetWithInvalidName() {
    $options = new \PapayaObjectOptionsList();
    $this->expectException(InvalidArgumentException::class);
    $options['INVALID OPTION WITH SPACE'] = '';
  }

  /**
  * @covers \PapayaObjectOptionsList::offsetSet
  */
  public function testOffsetSetWithInvalidValue() {
    $options = new \PapayaObjectOptionsList();
    $this->expectException(InvalidArgumentException::class);
    $options['SAMPLE'] = new stdClass();
  }

  /**
  * @covers \PapayaObjectOptionsList::offsetGet
  */
  public function testOffsetGet() {
    $options = new \PapayaObjectOptionsList();
    $options['SAMPLE'] = 42;
    $this->assertEquals(
      42,
      $options['sample']
    );
  }

  /**
  * @covers \PapayaObjectOptionsList::offsetExists
  */
  public function testOffsetExists() {
    $options = new \PapayaObjectOptionsList();
    $options['SAMPLE'] = 42;
    $this->assertTrue(
      isset($options['sample'])
    );
  }

  /**
  * @covers \PapayaObjectOptionsList::offsetUnset
  */
  public function testOffsetUnset() {
    $options = new \PapayaObjectOptionsList();
    $options['SAMPLE'] = 42;
    unset($options['sample']);
    $this->assertAttributeSame(
      array(),
      '_options',
      $options
    );
  }

  /**
  * @covers \PapayaObjectOptionsList::count
  */
  public function testCount() {
    $options = new \PapayaObjectOptionsList();
    $this->assertCount(0, $options);
  }

  /**
  * @covers \PapayaObjectOptionsList::__get
  * @covers \PapayaObjectOptionsList::_read
  */
  public function testMagicMethodGet() {
    $options = new \PapayaObjectOptionsList();
    $options['SAMPLE'] = 'Hello World';
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertEquals(
      'Hello World', $options->sample
    );
  }

  /**
  * @covers \PapayaObjectOptionsList::__set
  * @covers \PapayaObjectOptionsList::_write
  */
  public function testMagicMethodSet() {
    $options = new \PapayaObjectOptionsList();
    /** @noinspection PhpUndefinedFieldInspection */
    $options->sample = 'Hello World';
    $this->assertEquals(
      'Hello World', $options['SAMPLE']
    );
  }

  /**
  * @covers \PapayaObjectOptionsList::__isset
  * @covers \PapayaObjectOptionsList::_exists
  */
  public function testMagicMethodIsset() {
    $options = new \PapayaObjectOptionsList();
    /** @noinspection PhpUndefinedFieldInspection */
    $options->sample = 'Hello World';
    $this->assertTrue(
      isset($options->sample)
    );
  }

  /**
  * @covers \PapayaObjectOptionsList::__unset
  */
  public function testMagicMethodUnset() {
    $options = new \PapayaObjectOptionsList();
    /** @noinspection PhpUndefinedFieldInspection */
    $options->sample = 'Hello World';
    unset($options->sample);
    $this->assertFalse(
      isset($options->sample)
    );
  }

  /**
  * @covers \PapayaObjectOptionsList::toArray
  */
  public function testToArray() {
    $options = new \PapayaObjectOptionsList();
    /** @noinspection PhpUndefinedFieldInspection */
    $options->sample = 'Hello World';
    $this->assertEquals(
      array('SAMPLE' => 'Hello World'),
      $options->toArray()
    );
  }

  /**
  * @covers \PapayaObjectOptionsList::getIterator
  */
  public function testGetIterator() {
    $options = new \PapayaObjectOptionsList();
    /** @noinspection PhpUndefinedFieldInspection */
    $options->sample = 'Hello World';
    $this->assertEquals(
      array('SAMPLE' => 'Hello World'),
      iterator_to_array($options->getIterator())
    );
  }

  /**
  * @covers \PapayaObjectOptionsList::assign
  */
  public function testAssign() {
    $options = new \PapayaObjectOptionsList();
    $options->assign(
      array(
        'sample' => 'Hello World'
      )
    );
    $this->assertEquals(
      array('SAMPLE' => 'Hello World'),
      iterator_to_array($options->getIterator())
    );
  }

  /******************************
  * DataProvider
  *******************************/

  public static function offsetSetDataProvider() {
    return array(
      array(
        array('SAMPLE' => 42), 'SAMPLE', 42
      ),
      array(
        array('SAMPLE' => 42), 'sample', 42
      ),
      array(
        array('SAMPLE_OPTION' => 42), 'sample_option', 42
      ),
      array(
        array('SAMPLE_OPTION' => 42), 'sampleOption', 42
      ),
      array(
        array('SAMPLE_OPTION_ABBR_TEST' => 42), 'sampleOptionABBRTest', 42
      ),
      array(
        array('STRING' => 'Hello'), 'string', 'Hello'
      ),
      array(
        array('BOOLEAN' => TRUE), 'boolean', TRUE
      ),
      array(
        array('FLOAT' => 42.21), 'float', 42.21
      )
    );
  }

}
