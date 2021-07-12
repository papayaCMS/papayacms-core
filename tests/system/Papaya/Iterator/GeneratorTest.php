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

namespace Papaya\Iterator {

  use Papaya\TestFramework\TestCase;
  use Papaya\XML\Document;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Iterator\Generator
   */
  class GeneratorTest extends TestCase {

    public function testGetIteratorWithoutData() {
      $iterator = new Generator(
        static function ($traversable = NULL) {
          return $traversable;
        }
      );
      $this->assertInstanceOf('EmptyIterator', $iterator->getIterator());
    }

    public function testGetIteratorWithArray() {
      $iterator = new Generator(
        static function ($traversable = NULL) {
          return $traversable;
        },
        [['foo', 'bar']]
      );
      $this->assertEquals(
        new \ArrayIterator(['foo', 'bar']), $iterator->getIterator()
      );
    }

    public function testGetIteratorWithIterator() {
      $iterator = new Generator(
        static function ($traversable = NULL) {
          return $traversable;
        },
        [$innerIterator = new \EmptyIterator()]
      );
      $this->assertSame(
        $innerIterator, $iterator->getIterator()
      );
    }

    public function testGetIteratorWithIteratorAggregate() {
      $wrapper = $this->createMock(\IteratorAggregate::class);
      $wrapper
        ->expects($this->once())
        ->method('getIterator')
        ->willReturn(new \ArrayIterator(['foo']));

      $iterator = new Generator(
        static function ($traversable = NULL) {
          return $traversable;
        },
        [$wrapper]
      );
      $this->assertEquals(
        ['foo'], iterator_to_array($iterator)
      );
    }

    public function testGetIteratorWithTraversable() {
      $document = new Document();
      $node = $document->appendElement('foo');

      $iterator = new Generator(
        static function ($traversable = NULL) {
          return $traversable;
        },
        [$document->childNodes]
      );
      $this->assertEquals(
        [$node], iterator_to_array($iterator, FALSE)
      );
    }

    public function testMultipleCallsCreateIteratorOnlyOnce() {
      $iterator = new Generator(
        static function ($traversable = NULL) {
          return $traversable;
        }
      );
      $this->assertInstanceOf('EmptyIterator', $innerIterator = $iterator->getIterator());
      $this->assertSame($innerIterator, $iterator->getIterator());
    }

  }
}
