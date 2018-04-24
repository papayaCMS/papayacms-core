<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationPagesDependencyListviewTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationPagesDependencyListview::__construct
  */
  public function testConstructor() {
    $dependencies = $this->getDependenciesFixture();
    $references = $this->getReferencesFixture();
    $synchronizations = $this->getSynchronizationsFixture();
    $listview = new PapayaAdministrationPagesDependencyListview(
      21, 42, $dependencies, $references, $synchronizations
    );
    $this->assertAttributeSame(
      $dependencies, '_dependencies', $listview
    );
    $this->assertAttributeEquals(
      42, '_currentPageId', $listview
    );
    $this->assertAttributeEquals(
      21, '_originPageId', $listview
    );
    $this->assertAttributeSame(
      $synchronizations, '_synchronizations', $listview
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyListview::appendTo
  */
  public function testAppendToWithEmptyRecordList() {
    $dependencies = $this->getDependenciesFixture();
    $references = $this->getReferencesFixture();
    $synchronizations = $this->getSynchronizationsFixture();
    $listview = new PapayaAdministrationPagesDependencyListview(
      21, 42, $dependencies, $references, $synchronizations
    );
    $this->assertEquals('', $listview->getXml());
  }

  /**
  * @covers PapayaAdministrationPagesDependencyListview::appendTo
  * @covers PapayaAdministrationPagesDependencyListview::prepare
  */
  public function testAppendTo() {
    $dependencies = $this->getDependenciesFixture(
      array(
        21 => array(
          'id' => 21,
          'origin_id' => 42,
          'synchronization' => 35,
          'note' => 'sample text',
          'title' => 'selected page',
          'modified' => strtotime('2011-1-1 12:00')
        ),
        23 => array(
          'id' => 23,
          'origin_id' => 42,
          'synchronization' => 0,
          'title' => 'page',
          'modified' => strtotime('2011-1-1 12:00')
        )
      )
    );
    $references = $this->getReferencesFixture(
      array(
        21 => array(
          'source_id' => 42,
          'target_id' => 21,
          'title' => 'page 21',
          'modified' => strtotime('2011-1-1 12:00'),
          'note' => 'note 21 -> 42'
        ),
        84 => array(
          'source_id' => 42,
          'target_id' => 84,
          'title' => 'page 84',
          'modified' => strtotime('2011-1-1 12:00'),
          'note' => 'note 42 -> 84'
        )
      )
    );
    $synchronizations = $this->getSynchronizationsFixture();
    $listview = new PapayaAdministrationPagesDependencyListview(
      42, 21, $dependencies, $references, $synchronizations
    );
    $listview->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => array(
            'items-folder' => 'folder.png',
            'items-page' => 'page.png',
            'items-link' => 'link.png',
            'actions-go-superior' => 'up.png'
          )
        )
      )
    );
    $this->assertXmlStringEqualsXmlString(
      '<listview title="Dependent pages of page &quot;[...] #42&quot;">'.
        '<cols>'.
          '<col align="left">Page</col>'.
          '<col align="center">GoTo</col>'.
          '<col align="center">Synchronization</col>'.
          '<col align="center">Modified</col>'.
        '</cols>'.
        '<items>'.
          '<listitem title="Dependencies" image="folder.png">'.
            '<subitem align="center">'.
              '<glyph src="up.png" hint="Go to origin page"'.
              ' href="http://www.test.tld/test.html?page_id=42"/>'.
            '</subitem>'.
            '<subitem align="center"/>'.
            '<subitem align="center"/>'.
          '</listitem>'.
          '<listitem title="selected page #21" image="page.png" subtitle="sample text"'.
          ' href="http://www.test.tld/test.html?page_id=21" indent="1" selected="selected">'.
            '<subitem align="center"/>'.
            '<subitem align="center"><glyphs/></subitem>'.
            '<subitem align="center">2011-01-01 12:00</subitem>'.
          '</listitem>'.
          '<listitem title="page #23" image="page.png"'.
          ' href="http://www.test.tld/test.html?page_id=23" indent="1">'.
            '<subitem align="center"/>'.
            '<subitem align="center"><glyphs/></subitem>'.
            '<subitem align="center">2011-01-01 12:00</subitem>'.
          '</listitem>'.
          '<listitem title="References" image="folder.png" span="4"/>'.
          '<listitem title="page 21 #21" image="link.png" subtitle="note 21 -&gt; 42"'.
          ' href="http://www.test.tld/test.html'.
              '?cmd=reference_change&amp;page_id=42&amp;target_id=21" '.
            'indent="1">'.
            '<subitem align="center">'.
              '<glyph src="page.png" hint="Go to page page 21 #21"'.
              ' href="http://www.test.tld/test.html'.
                '?cmd=reference_change&amp;page_id=21&amp;target_id=42"/>'.
            '</subitem>'.
            '<subitem align="center"></subitem>'.
            '<subitem align="center">2011-01-01 12:00</subitem>'.
          '</listitem><listitem title="page 84 #84" image="link.png" subtitle="note 42 -&gt; 84"'.
          ' href="http://www.test.tld/test.html'.
              '?cmd=reference_change&amp;page_id=42&amp;target_id=84" '.
            'indent="1">'.
            '<subitem align="center">'.
              '<glyph src="page.png" hint="Go to page page 84 #84"'.
              ' href="http://www.test.tld/test.html'.
                '?cmd=reference_change&amp;page_id=84&amp;target_id=42"/>'.
            '</subitem>'.
            '<subitem align="center"></subitem>'.
            '<subitem align="center">2011-01-01 12:00</subitem>'.
          '</listitem>'.
        '</items>'.
      '</listview>',
      $listview->getXml()
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyListview::pages
  */
  public function testPagesGetAfterSet() {
    $pages = $this->getMock('PapayaContentPages');
    $dependencies = $this->getDependenciesFixture();
    $references = $this->getReferencesFixture();
    $synchronizations = $this->getSynchronizationsFixture();
    $listview = new PapayaAdministrationPagesDependencyListview(
      21, 42, $dependencies, $references, $synchronizations
    );
    $this->assertSame(
      $pages, $listview->pages($pages)
    );
  }

  /**
  * @covers PapayaAdministrationPagesDependencyListview::pages
  */
  public function testPagesGetImplicitCreate() {
    $dependencies = $this->getDependenciesFixture();
    $references = $this->getReferencesFixture();
    $synchronizations = $this->getSynchronizationsFixture();
    $listview = new PapayaAdministrationPagesDependencyListview(
      21, 42, $dependencies, $references, $synchronizations
    );
    $this->assertInstanceOf(
      'PapayaContentPages', $listview->pages()
    );
  }

  /**************************
  * Fixtures
  **************************/

  public function getDependenciesFixture($data = array()) {
    $dependencies = $this->getMock('PapayaContentPageDependencies');
    $dependencies
      ->expects($this->any())
      ->method('count')
      ->will($this->returnValue(count($data)));
    $dependencies
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator($data)));
    return $dependencies;
  }

  public function getReferencesFixture($data = array()) {
    $references = $this->getMock('PapayaContentPageReferences');
    $references
      ->expects($this->any())
      ->method('count')
      ->will($this->returnValue(count($data)));
    $references
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator($data)));
    return $references;
  }

  public function getSynchronizationsFixture() {
    $icons = $this->getMock('PapayaUiIconList');
    $icons
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator()));
    $sychronizations = $this->getMock('PapayaAdministrationPagesDependencySynchronizations');
    $sychronizations
      ->expects($this->any())
      ->method('getIcons')
      ->will(
        $this->returnValue($icons)
      );
    return $sychronizations;
  }
}
