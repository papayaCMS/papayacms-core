<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiContentTeasersTest extends PapayaTestCase {

  /**
   * @covers PapayaUiContentTeasers::__construct
   */
  public function testConstructor() {
    $pages = $this->getPagesFixture();
    $teasers = new PapayaUiContentTeasers($pages);
    $this->assertSame($pages, $teasers->pages());
  }

  /**
   * @covers PapayaUiContentTeasers::__construct
   */
  public function testConstructorWithAllParameters() {
    $pages = $this->getPagesFixture();
    $teasers = new PapayaUiContentTeasers($pages, 200, 100, 'mincrop');
    $this->assertSame($pages, $teasers->pages());
  }

  /**
   * @covers PapayaUiContentTeasers::reference
   */
  public function testReferenceGetAfterSet() {
    $teasers = new PapayaUiContentTeasers($this->getPagesFixture());
    $teasers->reference($reference = $this->getMock('PapayaUiReferencePage'));
    $this->assertSame($reference, $teasers->reference());
  }

  /**
   * @covers PapayaUiContentTeasers::reference
   */
  public function testReferenceGetImplicitCreate() {
    $teasers = new PapayaUiContentTeasers($this->getPagesFixture());
    $this->assertInstanceOf('PapayaUiReferencePage', $teasers->reference());
  }

  /**
   * @covers PapayaUiContentTeasers::pages
   */
  public function testPagesGetAfterSet() {
    $teasers = new PapayaUiContentTeasers($this->getPagesFixture());
    $teasers->pages($pages = $this->getPagesFixture());
    $this->assertSame($pages, $teasers->pages());
  }

  /**
   * @covers PapayaUiContentTeasers::appendTo
   */
  public function testAppendToWithEmptyList() {
    $teasers = new PapayaUiContentTeasers($this->getPagesFixture());
    $teasers->papaya($this->mockPapaya()->application());

    $this->assertXmlStringEqualsXmlString(
      '<teasers/>',
      $teasers->getXml()
    );
  }

  /**
   * @covers PapayaUiContentTeasers::appendTo
   * @covers PapayaUiContentTeasers::appendTeaser
   * @covers PapayaUiContentTeasers::appendThumbnails
   */
  public function testAppendToWithPluginImplementingInterfaces() {
    $pages = $this->getPagesFixture(
      array(
        array(
          'id' => 42,
          'language_id' => 1,
          'title' => 'implementing PapayaPluginQuoteable',
          'module_guid' => '12345678901234567890123456789042',
          'content' => 'data'
        )
      )
    );
    $plugin = $this->getMock('PapayaUiContentTeasers_PagePluginMockClass');
    $plugin
      ->expects($this->once())
      ->method('appendQuoteTo');

    $plugins = $this->getMock('PapayaPluginLoader');
    $plugins
      ->expects($this->once())
      ->method('get')
      ->with('12345678901234567890123456789042')
      ->will($this->returnValue($plugin));

    $teasers = new PapayaUiContentTeasers($pages);
    $teasers->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));

    $this->assertXmlStringEqualsXmlString(
      '<teasers/>',
      $teasers->getXml()
    );
  }

  /**
   * @covers PapayaUiContentTeasers::appendTo
   * @covers PapayaUiContentTeasers::appendTeaser
   * @covers PapayaUiContentTeasers::appendThumbnails
   */
  public function testAppendToWithPluginHavingGetParsedTeaser() {
    $pages = $this->getPagesFixture(
      array(
        array(
          'id' => 21,
          'language_id' => 1,
          'title' => 'calling getParsedTeaser',
          'module_guid' => '12345678901234567890123456789021',
          'content' => 'data'
        )
      )
    );

    $plugin = $this
      ->getMockBuilder('base_content')
      ->disableOriginalConstructor()
      ->getMock();

    $plugins = $this->getMock('PapayaPluginLoader');
    $plugins
      ->expects($this->once())
      ->method('get')
      ->with('12345678901234567890123456789021')
      ->will($this->returnValue($plugin));

    $teasers = new PapayaUiContentTeasers($pages);
    $teasers->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));

    $this->assertXmlStringEqualsXmlString(
      '<teasers/>',
      $teasers->getXml()
    );
  }

  /**
   * @covers PapayaUiContentTeasers::appendTo
   * @covers PapayaUiContentTeasers::appendTeaser
   * @covers PapayaUiContentTeasers::appendThumbnails
   */
  public function testAppendToWithNonExistingPlugin() {
    $pages = $this->getPagesFixture(
      array(
        array(
          'id' => 23,
          'language_id' => 1,
          'title' => 'invalid',
          'module_guid' => '12345678901234567890123456789023',
          'content' => 'data'
        )
      )
    );

    $plugins = $this->getMock('PapayaPluginLoader');
    $plugins
      ->expects($this->once())
      ->method('get')
      ->with(12345678901234567890123456789023)
      ->will($this->returnValue(NULL));

    $teasers = new PapayaUiContentTeasers($pages);
    $teasers->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));

    $this->assertXmlStringEqualsXmlString(
      '<teasers/>',
      $teasers->getXml()
    );
  }

  /**
   * @covers PapayaUiContentTeasers::appendTo
   * @covers PapayaUiContentTeasers::appendTeaser
   * @covers PapayaUiContentTeasers::appendThumbnails
   */
  public function testAppendToWithPluginAddingThumbnails() {
    $pages = $this->getPagesFixture(
      array(
        array(
          'id' => 42,
          'language_id' => 1,
          'title' => 'callback and thumbnails',
          'module_guid' => '12345678901234567890123456789042',
          'content' => 'data'
        )
      )
    );

    $plugins = $this->getMock('PapayaPluginLoader');
    $plugins
      ->expects($this->once())
      ->method('get')
      ->with('12345678901234567890123456789042')
      ->will($this->returnValue(new PapayaUiContentTeasers_PagePluginMockClass()));

    $teasers = new PapayaUiContentTeasers($pages, 200, 100);
    $teasers->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));

    $this->assertXmlStringEqualsXmlString(
      '<teasers>
        <teaser
          page-id="42"
          plugin-guid="12345678901234567890123456789042"
          plugin="PapayaUiContentTeasers_PagePluginMockClass"
          href="http://www.test.tld/index.42.html">
          <title>sample title</title>
          <image>
            <img src="sample.png"/>
          </image>
          <text>sample teaser</text>
        </teaser>
        <teaser-thumbnails>
          <thumbnail page-id="42">
            <papaya:media
              xmlns:papaya="http://www.papaya-cms.com/ns/papayacms"
              src="sample.png"
              resize="mincrop"
              width="200"
              height="100"/>
          </thumbnail>
        </teaser-thumbnails>
      </teasers>',
      $teasers->getXml()
    );
  }

  public function callbackAppendTeaser(PapayaXmlElement $parent) {
    $parent->appendElement('title', array(), 'sample title');
    $parent->appendElement('image')->appendElement('img', array('src' => 'sample.png'));
    $parent->appendElement('text', array(), 'sample teaser');
  }

  /************************
   * Fixtures
   ************************/

  private function getPagesFixture(array $pageRecords = array()) {
    $pages = $this->getMock('PapayaContentPages');
    $pages
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator($pageRecords)));
    return $pages;
  }
}

class PapayaUiContentTeasers_PagePluginMockClass
  implements PapayaPluginQuoteable {

  public function appendQuoteTo(PapayaXmlElement $parent) {
    $parent->appendElement('title', array(), 'sample title');
    $parent->appendElement('image')->appendElement('img', array('src' => 'sample.png'));
    $parent->appendElement('text', array(), 'sample teaser');
  }
}