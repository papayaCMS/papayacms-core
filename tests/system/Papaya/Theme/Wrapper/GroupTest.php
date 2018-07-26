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

class PapayaThemeWrapperGroupTest extends \PapayaTestCase {

  /**
  * @covers \PapayaThemeWrapperGroup::__construct
  */
  public function testConstructor() {
    $group = new \PapayaThemeWrapperGroup('sample.xml');
    $this->assertAttributeEquals(
      'sample.xml', '_themeFile', $group
    );
  }

  /**
  * @covers \PapayaThemeWrapperGroup::getFiles
  */
  public function testGetFilesRequestingMainCss() {
    $group = new \PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertEquals(
      array('basic.css', 'main.css'), $group->getFiles('main')
    );
  }

  /**
  * @covers \PapayaThemeWrapperGroup::getFiles
  */
  public function testGetFilesRequestingColorsCss() {
    $group = new \PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertEquals(
      array('colors.css'), $group->getFiles('colors')
    );
  }

  /**
  * @covers \PapayaThemeWrapperGroup::getFiles
  */
  public function testGetFilesRequestingMainJavascript() {
    $group = new \PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertEquals(
      array('main.js'), $group->getFiles('main', 'js')
    );
  }

  /**
  * @covers \PapayaThemeWrapperGroup::getFiles
  */
  public function testGetFilesRequestingNonExistingGroup() {
    $group = new \PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertEquals(
      array(), $group->getFiles('INVALID')
    );
  }

  /**
  * @covers \PapayaThemeWrapperGroup::getFiles
  */
  public function testGetFilesWithEmptyDocument() {
    $group = new \PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument(new DOMDocument('1.0', 'UTF-8'));
    $this->assertEquals(
      array(), $group->getFiles('DUMMY')
    );
  }

  /**
  * @covers \PapayaThemeWrapperGroup::allowDirectories
  */
  public function testAllowDirectoriesExpectingTrue() {
    $group = new \PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertTrue($group->allowDirectories('main'));
  }

  /**
  * @covers \PapayaThemeWrapperGroup::allowDirectories
  */
  public function testAllowDirectoriesExpectingFalse() {
    $group = new \PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertFalse($group->allowDirectories('colors'));
  }

  /**
  * @covers \PapayaThemeWrapperGroup::setDocument
  */
  public function testSetDocument() {
    $group = new \PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($document = new DOMDocument);
    $this->assertAttributeSame($document, '_document', $group);
  }

  /**
  * @covers \PapayaThemeWrapperGroup::getDocument
  */
  public function testGetDocumentAfterSet() {
    $group = new \PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($document = new DOMDocument);
    $this->assertSame($document, $group->getDocument());
  }

  /**
  * @covers \PapayaThemeWrapperGroup::getDocument
  */
  public function testGetDocumentLoadingFile() {
    $group = new \PapayaThemeWrapperGroup(__DIR__.'/TestData/theme.xml');
    $this->assertInstanceOf('DOMDocument', $group->getDocument());
  }

  /***********************************
  * Fixtures
  ***********************************/

  public function getThemeDocumentFixture() {
    $document = new DOMDocument('1.0', 'UTF-8');
    $document->load(__DIR__.'/TestData/theme.xml');
    return $document;
  }
}
