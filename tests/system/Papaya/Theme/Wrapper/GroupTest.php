<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaThemeWrapperGroupTest extends PapayaTestCase {

  /**
  * @covers PapayaThemeWrapperGroup::__construct
  */
  public function testConstructor() {
    $group = new PapayaThemeWrapperGroup('sample.xml');
    $this->assertAttributeEquals(
      'sample.xml', '_themeFile', $group
    );
  }

  /**
  * @covers PapayaThemeWrapperGroup::getFiles
  */
  public function testGetFilesRequestingMainCss() {
    $group = new PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertEquals(
      array('basic.css', 'main.css'), $group->getFiles('main')
    );
  }

  /**
  * @covers PapayaThemeWrapperGroup::getFiles
  */
  public function testGetFilesRequestingColorsCss() {
    $group = new PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertEquals(
      array('colors.css'), $group->getFiles('colors')
    );
  }

  /**
  * @covers PapayaThemeWrapperGroup::getFiles
  */
  public function testGetFilesRequestingMainJavascript() {
    $group = new PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertEquals(
      array('main.js'), $group->getFiles('main', 'js')
    );
  }

  /**
  * @covers PapayaThemeWrapperGroup::getFiles
  */
  public function testGetFilesRequestingNonExistingGroup() {
    $group = new PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertEquals(
      array(), $group->getFiles('INVALID')
    );
  }

  /**
  * @covers PapayaThemeWrapperGroup::getFiles
  */
  public function testGetFilesWithEmptyDocument() {
    $group = new PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument(new DOMDocument('1.0', 'UTF-8'));
    $this->assertEquals(
      array(), $group->getFiles('DUMMY')
    );
  }

  /**
  * @covers PapayaThemeWrapperGroup::allowDirectories
  */
  public function testAllowDirectoriesExpectingTrue() {
    $group = new PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertTrue($group->allowDirectories('main'));
  }

  /**
  * @covers PapayaThemeWrapperGroup::allowDirectories
  */
  public function testAllowDirectoriesExpectingFalse() {
    $group = new PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($this->getThemeDocumentFixture());
    $this->assertFalse($group->allowDirectories('colors'));
  }

  /**
  * @covers PapayaThemeWrapperGroup::setDocument
  */
  public function testSetDocument() {
    $group = new PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($document = new DOMDocument);
    $this->assertAttributeSame($document, '_document', $group);
  }

  /**
  * @covers PapayaThemeWrapperGroup::getDocument
  */
  public function testGetDocumentAfterSet() {
    $group = new PapayaThemeWrapperGroup('sample.xml');
    $group->setDocument($document = new DOMDocument);
    $this->assertSame($document, $group->getDocument());
  }

  /**
  * @covers PapayaThemeWrapperGroup::getDocument
  */
  public function testGetDocumentLoadingFile() {
    $group = new PapayaThemeWrapperGroup(dirname(__FILE__).'/TestData/theme.xml');
    $this->assertInstanceOf('DOMDocument', $group->getDocument());
  }

  /***********************************
  * Fixtures
  ***********************************/

  public function getThemeDocumentFixture() {
    $document = new DOMDocument('1.0', 'UTF-8');
    $document->load(dirname(__FILE__).'/TestData/theme.xml');
    return $document;
  }
}
