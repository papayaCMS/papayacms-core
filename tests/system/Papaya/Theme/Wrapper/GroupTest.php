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

namespace Papaya\Theme\Wrapper;

require_once __DIR__.'/../../../../bootstrap.php';

class GroupTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Theme\Wrapper\Group::__construct
   * @covers \Papaya\Theme\Wrapper\Group::getFiles
   */
  public function testGetFilesRequestingMainCss() {
    $group = new Group('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertEquals(
      array('basic.css', 'main.css'), $group->getFiles('main')
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\Group::getFiles
   */
  public function testGetFilesRequestingColorsCss() {
    $group = new Group('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertEquals(
      array('colors.css'), $group->getFiles('colors')
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\Group::getFiles
   */
  public function testGetFilesRequestingMainJavascript() {
    $group = new Group('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertEquals(
      array('main.js'), $group->getFiles('main', 'js')
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\Group::getFiles
   */
  public function testGetFilesRequestingNonExistingGroup() {
    $group = new Group('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertEquals(
      array(), $group->getFiles('INVALID')
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\Group::getFiles
   */
  public function testGetFilesWithEmptyDocument() {
    $group = new Group('sample.xml');
    $group->setDocument(new \DOMDocument('1.0', 'UTF-8'));
    $this->assertEquals(
      array(), $group->getFiles('DUMMY')
    );
  }

  /**
   * @covers \Papaya\Theme\Wrapper\Group::allowDirectories
   */
  public function testAllowDirectoriesExpectingTrue() {
    $group = new Group('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertTrue($group->allowDirectories('main'));
  }

  /**
   * @covers \Papaya\Theme\Wrapper\Group::allowDirectories
   */
  public function testAllowDirectoriesExpectingFalse() {
    $group = new Group('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertFalse($group->allowDirectories('colors'));
  }

  /**
   * @covers \Papaya\Theme\Wrapper\Group::setDocument
   * @covers \Papaya\Theme\Wrapper\Group::getDocument
   */
  public function testGetDocumentAfterSet() {
    $group = new Group('sample.xml');
    $group->setDocument($document = new \DOMDocument);
    $this->assertSame($document, $group->getDocument());
  }

  /**
   * @covers \Papaya\Theme\Wrapper\Group::getDocument
   */
  public function testGetDocumentLoadingFile() {
    $group = new Group(__DIR__.'/TestData/theme.xml');
    $this->assertInstanceOf('DOMDocument', $group->getDocument());
  }

  /***********************************
   * Fixtures
   ***********************************/

  public function getThemeDocumentFixture() {
    $document = new \DOMDocument('1.0', 'UTF-8');
    $document->load(__DIR__.'/TestData/theme.xml');
    return $document;
  }
}
