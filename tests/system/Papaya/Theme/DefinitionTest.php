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

class PapayaThemeDefinitionTest extends PapayaTestCase {

  /**
   * @covers PapayaThemeDefinition::load
   */
  public function testLoad() {
    $pages = $this->createMock(PapayaContentStructurePages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with($this->isInstanceOf(PapayaXmlElement::class));

    $definition = new PapayaThemeDefinition();
    $definition->pages($pages);
    $definition->load(__DIR__.'/TestData/theme.xml');
    $this->assertAttributeEquals(
      array(
        'name' => 'TestData',
        'title' => 'Sample Papaya Theme',
        'version' => '1.0',
        'version_date' => '2012-07-23',
        'author' => 'papaya Software GmbH',
        'description' => 'Sample description',
        'template_path' => 'template-path'
      ),
      '_properties',
      $definition
    );
    $this->assertAttributeEquals(
      array(
        'medium' => 'default48.jpg',
        'large' => 'default100.jpg'
      ),
      '_thumbnails',
      $definition
    );
  }

  /**
   * @covers PapayaThemeDefinition::__get
   */
  public function testMagicMethodGet() {
    $definition = new PapayaThemeDefinition();
    $definition->load(__DIR__.'/TestData/theme.xml');
    $this->assertEquals(
      'Sample Papaya Theme', $definition->title
    );
    $this->assertEquals(
      array(
        'medium' => 'default48.jpg',
        'large' => 'default100.jpg'
      ),
      $definition->thumbnails
    );
  }

  /**
   * @covers PapayaThemeDefinition::__get
   */
  public function testMagicMethodGetExpectingException() {
    $definition = new PapayaThemeDefinition();
    $this->expectException(UnexpectedValueException::class);
    /** @noinspection PhpUndefinedFieldInspection */
    $definition->invalidProperty;
  }
}
