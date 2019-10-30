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

namespace Papaya\Theme {

  use Papaya\Content\Structure\Pages as ContentPagesStructure;
  use Papaya\TestCase;
  use Papaya\XML\Element as XMLElement;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Theme\Definition
   */
  class DefinitionTest extends TestCase {

    public function testLoad() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|ContentPagesStructure $pages */
      $pages = $this->createMock(ContentPagesStructure::class);
      $pages
        ->expects($this->once())
        ->method('load')
        ->with($this->isInstanceOf(XMLElement::class));

      $definition = new Definition();
      $definition->pages($pages);
      $definition->load(__DIR__.'/TestData/theme.xml');
      $this->assertAttributeEquals(
        [
          'name' => 'TestData',
          'title' => 'Sample Papaya Theme',
          'version' => '1.0',
          'version_date' => '2012-07-23',
          'author' => 'papaya Software GmbH',
          'description' => 'Sample description',
          'template_path' => 'template-path'
        ],
        '_properties',
        $definition
      );
      $this->assertAttributeEquals(
        [
          'medium' => 'default48.jpg',
          'large' => 'default100.jpg'
        ],
        '_thumbnails',
        $definition
      );
    }

    public function testMagicMethodIssetExpectingTrue() {
      $definition = new Definition();
      $definition->load(__DIR__.'/TestData/theme.xml');
      $this->assertTrue(
        isset($definition->thumbnails)
      );
    }

    public function testMagicMethodIssetExpectingFalse() {
      $definition = new Definition();
      $definition->load(__DIR__.'/TestData/theme.xml');
      $this->assertFalse(
        isset($definition->invalidProperty)
      );
    }

    public function testMagicMethodGet() {
      $definition = new Definition();
      $definition->load(__DIR__.'/TestData/theme.xml');
      $this->assertEquals(
        'Sample Papaya Theme', $definition->title
      );
      $this->assertEquals(
        [
          'medium' => 'default48.jpg',
          'large' => 'default100.jpg'
        ],
        $definition->thumbnails
      );
    }

    public function testMagicMethodGetExpectingException() {
      $definition = new Definition();
      $this->expectException(\UnexpectedValueException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      $definition->invalidProperty;
    }

    public function testMagicMethodSetExpectingException() {
      $definition = new Definition();
      $definition->load(__DIR__.'/TestData/theme.xml');
      $this->expectException(\UnexpectedValueException::class);
      $definition->thumbnails = 'fail';
    }

    public function testMagicMethodUnsetExpectingException() {
      $definition = new Definition();
      $definition->load(__DIR__.'/TestData/theme.xml');
      $this->expectException(\UnexpectedValueException::class);
      unset($definition->thumbnails);
    }
  }
}
