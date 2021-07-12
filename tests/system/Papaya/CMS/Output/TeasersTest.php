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

namespace Papaya\CMS\Output {

  use Papaya\CMS\Content\Pages as ContentPages;
  use Papaya\CMS\Content\View\Configurations as ViewConfigurations;
  use Papaya\CMS\Plugin\Loader as PluginLoader;
  use Papaya\Plugin\Quoteable as QuoteablePlugin;
  use Papaya\TestCase;
  use Papaya\CMS\Reference\Page as PageReference;
  use Papaya\Utility\Date as DateUtilities;
  use Papaya\XML\Element as XMLElement;

  require_once __DIR__.'/../../../../bootstrap.php';

    /**
     * @covers \Papaya\CMS\Output\Teasers
     */
  class TeasersTest extends TestCase {

    public function testConstructor() {
      $pages = $this->getPagesFixture();
      $teasers = new Teasers($pages);
      $this->assertSame($pages, $teasers->pages());
    }

    public function testConstructorWithAllParameters() {
      $pages = $this->getPagesFixture();
      $teasers = new Teasers($pages, 200, 100, 'mincrop');
      $this->assertSame($pages, $teasers->pages());
    }

    public function testReferenceGetAfterSet() {
      $teasers = new Teasers($this->getPagesFixture());
      /** @var \PHPUnit_Framework_MockObject_MockObject|PageReference $reference */
      $reference = $this->createMock(PageReference::class);
      $teasers->reference($reference);
      $this->assertSame($reference, $teasers->reference());
    }

    public function testReferenceGetImplicitCreate() {
      $teasers = new Teasers($this->getPagesFixture());
      $this->assertInstanceOf(PageReference::class, $teasers->reference());
    }

    public function testPagesGetAfterSet() {
      $teasers = new Teasers($this->getPagesFixture());
      $teasers->pages($pages = $this->getPagesFixture());
      $this->assertSame($pages, $teasers->pages());
    }

    public function testAppendToWithEmptyList() {
      $teasers = new Teasers($this->getPagesFixture());
      $teasers->papaya($this->mockPapaya()->application());

      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<teasers/>',
        $teasers->getXML()
      );
    }

    public function testAppendToWithPluginImplementingInterfaces() {
      $pages = $this->getPagesFixture(
        array(
          array(
            'id' => 42,
            'language_id' => 1,
            'title' => 'implementing Papaya\CMS\Plugin\Quoteable',
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

      /** @var \PHPUnit_Framework_MockObject_MockObject|PluginLoader $plugins */
      $plugins = $this->createMock(PluginLoader::class);
      $plugins
        ->expects($this->once())
        ->method('get')
        ->with('12345678901234567890123456789042')
        ->willReturn($plugin);

      $teasers = new Teasers($pages);
      /** @var \PHPUnit_Framework_MockObject_MockObject|ViewConfigurations $viewConfigurations */
      $viewConfigurations = $this->createMock(ViewConfigurations::class);
      $teasers->viewConfigurations($viewConfigurations);
      $teasers->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));

      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<teasers/>',
        $teasers->getXML()
      );
    }

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

      /** @var \PHPUnit_Framework_MockObject_MockObject|PluginLoader $plugins */
      $plugins = $this->createMock(PluginLoader::class);
      $plugins
        ->expects($this->once())
        ->method('get')
        ->with('12345678901234567890123456789021')
        ->willReturn($plugin);

      $teasers = new Teasers($pages);
      /** @var \PHPUnit_Framework_MockObject_MockObject|ViewConfigurations $viewConfigurations */
      $viewConfigurations = $this->createMock(ViewConfigurations::class);
      $teasers->viewConfigurations($viewConfigurations);
      $teasers->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));

      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<teasers/>',
        $teasers->getXML()
      );
    }

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

      /** @var \PHPUnit_Framework_MockObject_MockObject|PluginLoader $plugins */
      $plugins = $this->createMock(PluginLoader::class);
      $plugins
        ->expects($this->once())
        ->method('get')
        ->with(12345678901234567890123456789023)
        ->willReturn(NULL);

      $teasers = new Teasers($pages);
      /** @var \PHPUnit_Framework_MockObject_MockObject|ViewConfigurations $viewConfigurations */
      $viewConfigurations = $this->createMock(ViewConfigurations::class);
      $teasers->viewConfigurations($viewConfigurations);
      $teasers->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));

      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<teasers/>',
        $teasers->getXML()
      );
    }

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

      $plugins = $this->createMock(PluginLoader::class);
      $plugins
        ->expects($this->once())
        ->method('get')
        ->with('12345678901234567890123456789042')
        ->willReturn(new Teasers_PagePluginMockClass());

      $teasers = new Teasers($pages, 200, 100);
      /** @var \PHPUnit_Framework_MockObject_MockObject|ViewConfigurations $viewConfigurations */
      $viewConfigurations = $this->createMock(ViewConfigurations::class);
      $teasers->viewConfigurations($viewConfigurations);
      $teasers->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));

      $date = DateUtilities::timestampToString(strtotime('2017-01-16T12:21Z'));
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        "<teasers>
        <teaser
          created='$date'
          published='$date'
          page-id='42'
          plugin-guid='12345678901234567890123456789042'
          plugin='Papaya\CMS\Output\Teasers_PagePluginMockClass'
          title='callback and thumbnails'>
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


    public function testAppendToWithPluginReplacingThumbnails() {
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

      $plugins = $this->createMock(PluginLoader::class);
      $plugins
        ->expects($this->once())
        ->method('get')
        ->with('12345678901234567890123456789042')
        ->willReturn(new Teasers_PagePluginMockClass());

      $teasers = new Teasers($pages, 200, 100, 'mincrop', TRUE);
      /** @var \PHPUnit_Framework_MockObject_MockObject|ViewConfigurations $viewConfigurations */
      $viewConfigurations = $this->createMock(ViewConfigurations::class);
      $teasers->viewConfigurations($viewConfigurations);
      $teasers->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));

      $date = DateUtilities::timestampToString(strtotime('2017-01-16T12:21Z'));
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        "<teasers>
        <teaser
          created='$date'
          published='$date'
          page-id='42'
          plugin-guid='12345678901234567890123456789042'
          plugin='Papaya\CMS\Output\Teasers_PagePluginMockClass'
          title='callback and thumbnails'>
          <title>sample title</title>
          <image>
            <papaya:media 
              xmlns:papaya='http://www.papaya-cms.com/ns/papayacms' 
              height='100' width='200' resize='mincrop' src='sample.png'/>
          </image>
          <text>sample teaser</text>
        </teaser>
      </teasers>",
        $teasers->getXML()
      );
    }

    public function callbackAppendTeaser(XMLElement $parent) {
      $parent->appendElement('title', array(), 'sample title');
      $parent->appendElement('image')->appendElement('img', array('src' => 'sample.png'));
      $parent->appendElement('text', array(), 'sample teaser');
    }

    /************************
     * Fixtures
     ************************/

    /**
     * @param array $pageRecords
     * @return \PHPUnit_Framework_MockObject_MockObject|ContentPages
     */
    private function getPagesFixture(array $pageRecords = array()) {
      $pages = $this->createMock(ContentPages::class);
      $pages
        ->method('getIterator')
        ->willReturn(new \ArrayIterator($pageRecords));
      return $pages;
    }
  }

  class Teasers_PagePluginMockClass
    implements QuoteablePlugin {

    public function appendQuoteTo(XMLElement $parent) {
      $parent->appendElement('title', array(), 'sample title');
      $parent->appendElement('image')->appendElement('img', array('src' => 'sample.png'));
      $parent->appendElement('text', array(), 'sample teaser');
    }
  }
}
