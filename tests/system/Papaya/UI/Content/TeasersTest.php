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

namespace Papaya\UI\Content {

  require_once __DIR__.'/../../../../bootstrap.php';

  class TeasersTest extends \Papaya\TestCase {

    /**
     * @covers \Papaya\UI\Content\Teasers::__construct
     */
    public function testConstructor() {
      $pages = $this->getPagesFixture();
      $teasers = new Teasers($pages);
      $this->assertSame($pages, $teasers->pages());
    }

    /**
     * @covers \Papaya\UI\Content\Teasers::__construct
     */
    public function testConstructorWithAllParameters() {
      $pages = $this->getPagesFixture();
      $teasers = new Teasers($pages, 200, 100, 'mincrop');
      $this->assertSame($pages, $teasers->pages());
    }

    /**
     * @covers \Papaya\UI\Content\Teasers::reference
     */
    public function testReferenceGetAfterSet() {
      $teasers = new Teasers($this->getPagesFixture());
      $teasers->reference($reference = $this->createMock(\Papaya\UI\Reference\Page::class));
      $this->assertSame($reference, $teasers->reference());
    }

    /**
     * @covers \Papaya\UI\Content\Teasers::reference
     */
    public function testReferenceGetImplicitCreate() {
      $teasers = new Teasers($this->getPagesFixture());
      $this->assertInstanceOf(\Papaya\UI\Reference\Page::class, $teasers->reference());
    }

    /**
     * @covers \Papaya\UI\Content\Teasers::pages
     */
    public function testPagesGetAfterSet() {
      $teasers = new Teasers($this->getPagesFixture());
      $teasers->pages($pages = $this->getPagesFixture());
      $this->assertSame($pages, $teasers->pages());
    }

    /**
     * @covers \Papaya\UI\Content\Teasers::appendTo
     */
    public function testAppendToWithEmptyList() {
      $teasers = new Teasers($this->getPagesFixture());
      $teasers->papaya($this->mockPapaya()->application());

      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<teasers/>',
        $teasers->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\Content\Teasers::appendTo
     * @covers \Papaya\UI\Content\Teasers::appendTeaserTo
     * @covers \Papaya\UI\Content\Teasers::appendThumbnails
     */
    public function testAppendToWithPluginImplementingInterfaces() {
      $pages = $this->getPagesFixture(
        array(
          array(
            'id' => 42,
            'language_id' => 1,
            'title' => 'implementing Papaya\Plugin\Quoteable',
            'module_guid' => '12345678901234567890123456789042',
            'content' => 'data',
            'created' => strtotime('2017-01-16T12:21Z'),
            'modified' => strtotime('2017-01-16T12:21Z'),
            'view_id' => 1,
            'viewmode_id' => 1
          )
        )
      );
      $plugin = $this->createMock(Teasers_PagePluginMockClass::class);
      $plugin
        ->expects($this->once())
        ->method('appendQuoteTo');

      $plugins = $this->createMock(\Papaya\Plugin\Loader::class);
      $plugins
        ->expects($this->once())
        ->method('get')
        ->with('12345678901234567890123456789042')
        ->will($this->returnValue($plugin));

      $teasers = new Teasers($pages);
      $teasers->viewConfigurations($this->createMock(\Papaya\Content\View\Configurations::class));
      $teasers->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));

      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<teasers/>',
        $teasers->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\Content\Teasers::appendTo
     * @covers \Papaya\UI\Content\Teasers::appendTeaserTo
     * @covers \Papaya\UI\Content\Teasers::appendThumbnails
     */
    public function testAppendToWithPluginHavingGetParsedTeaser() {
      $pages = $this->getPagesFixture(
        array(
          array(
            'id' => 21,
            'language_id' => 1,
            'title' => 'calling getParsedTeaser',
            'module_guid' => '12345678901234567890123456789021',
            'content' => 'data',
            'created' => strtotime('2017-01-16T12:21Z'),
            'modified' => strtotime('2017-01-16T12:21Z'),
            'view_id' => 1,
            'viewmode_id' => 1
          )
        )
      );

      $plugin = $this
        ->getMockBuilder(\base_content::class)
        ->disableOriginalConstructor()
        ->getMock();

      $plugins = $this->createMock(\Papaya\Plugin\Loader::class);
      $plugins
        ->expects($this->once())
        ->method('get')
        ->with('12345678901234567890123456789021')
        ->will($this->returnValue($plugin));

      $teasers = new Teasers($pages);
      $teasers->viewConfigurations($this->createMock(\Papaya\Content\View\Configurations::class));
      $teasers->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));

      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<teasers/>',
        $teasers->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\Content\Teasers::appendTo
     * @covers \Papaya\UI\Content\Teasers::appendTeaserTo
     * @covers \Papaya\UI\Content\Teasers::appendThumbnails
     */
    public function testAppendToWithNonExistingPlugin() {
      $pages = $this->getPagesFixture(
        array(
          array(
            'id' => 23,
            'language_id' => 1,
            'title' => 'invalid',
            'module_guid' => '12345678901234567890123456789023',
            'content' => 'data',
            'created' => strtotime('2017-01-16T12:21Z'),
            'modified' => strtotime('2017-01-16T12:21Z'),
            'view_id' => 1,
            'viewmode_id' => 1
          )
        )
      );

      $plugins = $this->createMock(\Papaya\Plugin\Loader::class);
      $plugins
        ->expects($this->once())
        ->method('get')
        ->with(12345678901234567890123456789023)
        ->will($this->returnValue(NULL));

      $teasers = new Teasers($pages);
      $teasers->viewConfigurations($this->createMock(\Papaya\Content\View\Configurations::class));
      $teasers->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));

      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<teasers/>',
        $teasers->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\Content\Teasers::appendTo
     * @covers \Papaya\UI\Content\Teasers::appendTeaserTo
     * @covers \Papaya\UI\Content\Teasers::appendThumbnails
     */
    public function testAppendToWithPluginAddingThumbnails() {
      $pages = $this->getPagesFixture(
        array(
          array(
            'id' => 42,
            'language_id' => 1,
            'title' => 'callback and thumbnails',
            'module_guid' => '12345678901234567890123456789042',
            'content' => 'data',
            'created' => strtotime('2017-01-16T12:21Z'),
            'modified' => strtotime('2017-01-16T12:21Z'),
            'view_id' => 1,
            'viewmode_id' => 1
          )
        )
      );

      $plugins = $this->createMock(\Papaya\Plugin\Loader::class);
      $plugins
        ->expects($this->once())
        ->method('get')
        ->with('12345678901234567890123456789042')
        ->will($this->returnValue(new Teasers_PagePluginMockClass()));

      $teasers = new Teasers($pages, 200, 100);
      $teasers->viewConfigurations($this->createMock(\Papaya\Content\View\Configurations::class));
      $teasers->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));

      $date = \Papaya\Utility\Date::timestampToString(strtotime('2017-01-16T12:21Z'));
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        "<teasers>
        <teaser
          created='$date'
          published='$date'
          page-id='42'
          plugin-guid='12345678901234567890123456789042'
          plugin='Papaya\UI\Content\Teasers_PagePluginMockClass'>
          <title>sample title</title>
          <image>
            <img src='sample.png'/>
          </image>
          <text>sample teaser</text>
        </teaser>
        <teaser-thumbnails>
          <thumbnail page-id='42'>
            <papaya:media
              xmlns:papaya='http://www.papaya-cms.com/ns/papayacms'
              src='sample.png'
              resize='mincrop'
              width='200'
              height='100'/>
          </thumbnail>
        </teaser-thumbnails>
      </teasers>",
        $teasers->getXML()
      );
    }

    public function callbackAppendTeaser(\Papaya\XML\Element $parent) {
      $parent->appendElement('title', array(), 'sample title');
      $parent->appendElement('image')->appendElement('img', array('src' => 'sample.png'));
      $parent->appendElement('text', array(), 'sample teaser');
    }

    /************************
     * Fixtures
     ************************/

    /**
     * @param array $pageRecords
     * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Content\Pages
     */
    private function getPagesFixture(array $pageRecords = array()) {
      $pages = $this->createMock(\Papaya\Content\Pages::class);
      $pages
        ->expects($this->any())
        ->method('getIterator')
        ->will($this->returnValue(new \ArrayIterator($pageRecords)));
      return $pages;
    }
  }

  class Teasers_PagePluginMockClass
    implements \Papaya\Plugin\Quoteable {

    public function appendQuoteTo(\Papaya\XML\Element $parent) {
      $parent->appendElement('title', array(), 'sample title');
      $parent->appendElement('image')->appendElement('img', array('src' => 'sample.png'));
      $parent->appendElement('text', array(), 'sample teaser');
    }
  }
}
