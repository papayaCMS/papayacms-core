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

namespace Papaya\BaseObject\Options;
require_once __DIR__.'/../../../../bootstrap.php';

class CollectionTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\BaseObject\Options\Collection::__construct
   */
  public function testConstructor() {
    $options = new Collection(
      array('Sample' => 'Hallo World')
    );
    $this->assertSame(
      array('SAMPLE' => 'Hallo World'),
      iterator_to_array($options)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::offsetSet
   * @covers \Papaya\BaseObject\Options\Collection::_prepareName
   * @dataProvider offsetSetDataProvider
   * @param mixed $expected
   * @param string $name
   * @param mixed $value
   */
  public function testOffsetSet($expected, $name, $value) {
    $options = new Collection();
    $options[$name] = $value;
    $this->assertSame(
      $expected,
      iterator_to_array($options)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::set
   */
  public function testSet() {
    $options = new Collection();
    $options->set('foo', 'bar');
    $this->assertEquals(
      'bar',
      $options['foo']
    );
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::offsetSet
   */
  public function testOffsetSetWithNullRemovesElement() {
    $options = new Collection();
    $options['sample'] = 'failed';
    $this->assertEquals('failed', $options['sample']);
    $options['sample'] = NULL;
    $this->assertSame(
      array(),
      iterator_to_array($options)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::offsetSet
   * @covers \Papaya\BaseObject\Options\Collection::_prepareName
   */
  public function testOffsetSetWithInvalidName() {
    $options = new Collection();
    $this->expectException(\InvalidArgumentException::class);
    $options['INVALID OPTION WITH SPACE'] = '';
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::offsetSet
   */
  public function testOffsetSetWithInvalidValue() {
    $options = new Collection();
    $this->expectException(\InvalidArgumentException::class);
    $options['SAMPLE'] = new \stdClass();
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::offsetGet
   */
  public function testOffsetGet() {
    $options = new Collection();
    $options['SAMPLE'] = 42;
    $this->assertEquals(
      42,
      $options['sample']
    );
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::offsetExists
   */
  public function testOffsetExists() {
    $options = new Collection();
    $options['SAMPLE'] = 42;
    $this->assertTrue(
      isset($options['sample'])
    );
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::offsetUnset
   */
  public function testOffsetUnset() {
    $options = new Collection();
    $options['SAMPLE'] = 42;
    unset($options['sample']);
    $this->assertSame(
      array(),
      iterator_to_array($options)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::count
   */
  public function testCount() {
    $options = new Collection();
    $this->assertCount(0, $options);
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::__get
   * @covers \Papaya\BaseObject\Options\Collection::_read
   */
  public function testMagicMethodGet() {
    $options = new Collection();
    $options['SAMPLE'] = 'Hello World';
    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertEquals(
      'Hello World', $options->sample
    );
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::__set
   * @covers \Papaya\BaseObject\Options\Collection::_write
   */
  public function testMagicMethodSet() {
    $options = new Collection();
    /** @noinspection PhpUndefinedFieldInspection */
    $options->sample = 'Hello World';
    $this->assertEquals(
      'Hello World', $options['SAMPLE']
    );
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::__isset
   * @covers \Papaya\BaseObject\Options\Collection::_exists
   */
  public function testMagicMethodIsset() {
    $options = new Collection();
    /** @noinspection PhpUndefinedFieldInspection */
    $options->sample = 'Hello World';
    $this->assertTrue(
      isset($options->sample)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::__unset
   */
  public function testMagicMethodUnset() {
    $options = new Collection();
    /** @noinspection PhpUndefinedFieldInspection */
    $options->sample = 'Hello World';
    unset($options->sample);
    $this->assertFalse(
      isset($options->sample)
    );
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::toArray
   */
  public function testToArray() {
    $options = new Collection();
    /** @noinspection PhpUndefinedFieldInspection */
    $options->sample = 'Hello World';
    $this->assertEquals(
      array('SAMPLE' => 'Hello World'),
      $options->toArray()
    );
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::getIterator
   */
  public function testGetIterator() {
    $options = new Collection();
    /** @noinspection PhpUndefinedFieldInspection */
    $options->sample = 'Hello World';
    $this->assertEquals(
      array('SAMPLE' => 'Hello World'),
      iterator_to_array($options->getIterator())
    );
  }

  /**
   * @covers \Papaya\BaseObject\Options\Collection::assign
   */
  public function testAssign() {
    $options = new Collection();
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
