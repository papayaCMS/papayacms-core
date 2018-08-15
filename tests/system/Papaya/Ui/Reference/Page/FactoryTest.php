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

use Papaya\Content\Link\Types;
use Papaya\Content\Page\Publications;
use Papaya\Content\Language;
use Papaya\Content\Languages;
use Papaya\Content\Pages;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiReferencePageFactoryTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::create
  */
  public function testCreate() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $this->assertInstanceOf(
      \Papaya\UI\Reference\Page::class,
      $factory->create()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::get
  * @covers \Papaya\UI\Reference\Page\Factory::prepareTitle
  */
  public function testGet() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->pages($this->getPagesFixture());
    $factory->domains($this->getDomainsFixture());
    $factory->languages($this->getLanguagesFixture());
    $reference = $factory->get('de', 42);
    $this->assertEquals(
      'http://www.test.tld/sample-title.42.de.html',
      $reference->get()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::get
  * @covers \Papaya\UI\Reference\Page\Factory::configure
  * @covers \Papaya\UI\Reference\Page\Factory::prepareTitle
  */
  public function testGetExpectingHttps() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->pages(
      $this->getPagesFixture(
        array(
          'id' => 42,
          'parent' => 21,
          'path' => array(0, 1, 23),
          'title' => 'Sample Title',
          'scheme' => \Papaya\Utility\Server\Protocol::HTTPS,
          'linktype_id' => 1
        )
      )
    );
    $factory->domains($this->getDomainsFixture());
    $factory->languages($this->getLanguagesFixture());
    $reference = $factory->get('de', 42);
    $this->assertEquals(
      'https://www.test.tld/sample-title.42.de.html',
      $reference->get()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::get
  * @covers \Papaya\UI\Reference\Page\Factory::prepareTitle
  */
  public function testGetWithPreviewPage() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->domains($this->getDomainsFixture());
    $factory->languages($this->getLanguagesFixture());
    $reference = $factory->get('de', 42);
    $this->assertEquals(
      'http://www.test.tld/sample-title.42.de.html.preview',
      $reference->get()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::get
  * @covers \Papaya\UI\Reference\Page\Factory::prepareTitle
  */
  public function testGetWithInvalidPage() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $reference = $factory->get('de', 21);
    $this->assertFalse(
      $reference->valid()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::configure
  * @covers \Papaya\UI\Reference\Page\Factory::prepareTitle
  */
  public function testConfigure() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->pages($this->getPagesFixture());
    $factory->domains($this->getDomainsFixture());
    $factory->languages($this->getLanguagesFixture());

    $reference = new \Papaya\UI\Reference\Page();
    $reference->papaya(
      $this->mockPapaya()->application(
        array(
          'pageReferences' => $factory
        )
      )
    );
    $reference->setPageLanguage('de');
    $reference->setPageId(42);
    $factory->configure($reference);
    $this->assertEquals(
      'http://www.test.tld/sample-title.42.de.html',
      $reference->get()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::configure
  * @covers \Papaya\UI\Reference\Page\Factory::prepareTitle
  */
  public function testConfigureWithPreviewPage() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->domains($this->getDomainsFixture());
    $factory->languages($this->getLanguagesFixture());

    $reference = new \Papaya\UI\Reference\Page();
    $reference->papaya(
      $this->mockPapaya()->application(
        array(
          'pageReferences' => $factory
        )
      )
    );
    $reference->setPageLanguage('de');
    $reference->setPageId(42);
    $factory->configure($reference);
    $this->assertEquals(
      'http://www.test.tld/sample-title.42.de.html.preview',
      $reference->get()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::configure
  * @covers \Papaya\UI\Reference\Page\Factory::prepareTitle
  */
  public function testConfigureWithTargetDomain() {
    $domains = $this->createMock(\Papaya\Domains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will(
        $this->returnValue(
          array(
            1 => array(
              'id' => 1,
              'scheme' => \Papaya\Utility\Server\Protocol::HTTP,
              'host' => 'www.success.tld',
              'language_id' => '0',
              'group_id' => '0'
            )
          )
        )
      );
    $domains
      ->expects($this->once())
      ->method('getCurrent')
      ->will(
        $this->returnValue(
          array(
            'id' => 2,
            'scheme' => \Papaya\Utility\Server\Protocol::HTTP,
            'host' => 'www.failed.tld',
            'language_id' => '0',
            'group_id' => '0'
          )
        )
      );

    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->pages($this->getPagesFixture());
    $factory->domains($domains);
    $factory->languages($this->getLanguagesFixture());

    $reference = new \Papaya\UI\Reference\Page();
    $reference->papaya(
      $this->mockPapaya()->application(
        array(
          'pageReferences' => $factory
        )
      )
    );
    $reference->setPageLanguage('de');
    $reference->setPageId(42);
    $factory->configure($reference);
    $this->assertEquals(
      'http://www.success.tld/sample-title.42.de.html',
      $reference->get()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::configure
  * @covers \Papaya\UI\Reference\Page\Factory::prepareTitle
  */
  public function testConfigureWithTargetDomainWithoutProtocol() {
    $domains = $this->createMock(\Papaya\Domains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will(
        $this->returnValue(
          array(
            1 => array(
              'id' => 1,
              'scheme' => \Papaya\Utility\Server\Protocol::BOTH,
              'host' => 'www.success.tld',
              'language_id' => '0',
              'group_id' => '0'
            )
          )
        )
      );
    $domains
      ->expects($this->once())
      ->method('getCurrent')
      ->will(
        $this->returnValue(
          array(
            'id' => 2,
            'scheme' => \Papaya\Utility\Server\Protocol::BOTH,
            'host' => 'www.failed.tld',
            'language_id' => '0',
            'group_id' => '0'
          )
        )
      );

    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->pages($this->getPagesFixture());
    $factory->domains($domains);
    $factory->languages($this->getLanguagesFixture());

    $reference = new \Papaya\UI\Reference\Page();
    $reference->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this->mockPapaya()->request(array(), ''),
          'pageReferences' => $factory
        )
      )
    );
    $reference->setPageLanguage('de');
    $reference->setPageId(42);
    $factory->configure($reference);
    $this->assertEquals(
      'http://www.success.tld/sample-title.42.de.html',
      $reference->get()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::configure
  * @covers \Papaya\UI\Reference\Page\Factory::prepareTitle
  */
  public function testConfigureInPreviewWithTargetDomainIgnored() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());

    $reference = new \Papaya\UI\Reference\Page();
    $reference->papaya(
      $this->mockPapaya()->application(
        array(
          'pageReferences' => $factory
        )
      )
    );
    $reference->setPageLanguage('de');
    $reference->setPageId(42);
    $factory->configure($reference);
    $this->assertEquals(
      'http://www.test.tld/sample-title.42.de.html.preview',
      $reference->get()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::configure
  * @covers \Papaya\UI\Reference\Page\Factory::prepareTitle
  */
  public function testConfigureWithInvalidPage() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());

    $reference = new \Papaya\UI\Reference\Page();
    $reference->papaya(
      $this->mockPapaya()->application(
        array(
          'pageReferences' => $factory
        )
      )
    );
    $reference->setPageLanguage('de');
    $reference->setPageId(21);
    $factory->configure($reference);
    $this->assertFalse(
      $reference->valid()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::configure
  * @covers \Papaya\UI\Reference\Page\Factory::prepareTitle
  */
  public function testConfigureWithInvalidDomain() {
    $domains = $this->createMock(\Papaya\Domains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will($this->returnValue(array()));
    $domains
      ->expects($this->once())
      ->method('getCurrent')
      ->will(
        $this->returnValue(
          array(
            'id' => 2,
            'scheme' => \Papaya\Utility\Server\Protocol::HTTP,
            'host' => 'www.failed.tld',
            'language_id' => '0',
            'group_id' => '0'
          )
        )
      );

    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->pages($this->getPagesFixture());
    $factory->domains($domains);
    $factory->languages($this->getLanguagesFixture());

    $reference = new \Papaya\UI\Reference\Page();
    $reference->papaya(
      $this->mockPapaya()->application(
        array(
          'pageReferences' => $factory
        )
      )
    );
    $reference->setPageLanguage('de');
    $reference->setPageId(42);
    $factory->configure($reference);
    $this->assertFalse(
      $reference->valid()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::getPageData
  * @covers \Papaya\UI\Reference\Page\Factory::isPageLoaded
  * @covers \Papaya\UI\Reference\Page\Factory::lazyLoadPage
  */
  public function testGetPageData() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $this->assertEquals(
      array(
        'id' => 42,
        'parent' => 21,
        'path' => array(0, 1, 23),
        'title' => 'Sample Title',
        'linktype_id' => 1,
        'scheme' => 0
      ),
      $factory->getPageData('de', 42)
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::getPageData
  * @covers \Papaya\UI\Reference\Page\Factory::isPageLoaded
  * @covers \Papaya\UI\Reference\Page\Factory::lazyLoadPage
  */
  public function testGetPageDataExpectingFalse() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $this->assertFalse(
      $factory->getPageData('de', 21)
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::isPreview
  */
  public function testIsPreviewExpectingFalse() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $this->assertFalse($factory->isPreview());
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::isPreview
  * @covers \Papaya\UI\Reference\Page\Factory::setPreview
  */
  public function testIsPreviewAfterSetPreviewExpectingTrue() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->setPreview(TRUE);
    $this->assertTrue($factory->isPreview());
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::setPreview
  */
  public function testSetPreviewToTrueResetsPagesObject() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $this->assertInstanceOf(Publications::class, $pages = $factory->pages());
    $factory->setPreview(TRUE);
    $this->assertNotInstanceOf(Publications::class, $pages = $factory->pages());
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::setPreview
  */
  public function testSetPreviewToFalseResetsPagesObject() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->setPreview(TRUE);
    $this->assertNotInstanceOf(Publications::class, $pages = $factory->pages());
    $factory->setPreview(FALSE);
    $this->assertInstanceOf(Publications::class, $pages = $factory->pages());
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::getDomainData
  */
  public function testGetDomainDataExpectingSameDomainReturnTrue() {
    $domains = $this->createMock(\Papaya\Domains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will(
        $this->returnValue(
          array(
            1 => array(
              'id' => 1,
              'scheme' => \Papaya\Utility\Server\Protocol::HTTP,
              'host' => 'www.success.tld',
              'language_id' => '0',
              'group_id' => '0'
            )
          )
        )
      );
    $domains
      ->expects($this->once())
      ->method('getCurrent')
      ->will(
        $this->returnValue(
          array(
            'id' => 1,
            'scheme' => \Papaya\Utility\Server\Protocol::HTTP,
            'host' => 'www.success.tld',
            'language_id' => '0',
            'group_id' => '0'
          )
        )
      );

    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $factory->domains($domains);
    $this->assertTrue(
      $factory->getDomainData('de', 42)
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::getDomainData
  */
  public function testGetDomainDataExpectingNoDomainReturnFalse() {
    $domains = $this->createMock(\Papaya\Domains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will($this->returnValue(array()));
    $domains
      ->expects($this->once())
      ->method('getCurrent')
      ->will(
        $this->returnValue(
          array(
            'id' => 1,
            'scheme' => \Papaya\Utility\Server\Protocol::HTTP,
            'host' => 'www.success.tld',
            'language_id' => '0',
            'group_id' => '0'
          )
        )
      );

    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $factory->domains($domains);
    $this->assertFalse(
      $factory->getDomainData('de', 42)
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::getDomainData
  */
  public function testGetDomainDataExpectingUnrestrictedCurrentDomainReturnTrue() {
    $domains = $this->createMock(\Papaya\Domains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will($this->returnValue(array()));
    $domains
      ->expects($this->once())
      ->method('getCurrent')
      ->will($this->returnValue(FALSE));

    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $factory->domains($domains);
    $this->assertTrue(
      $factory->getDomainData('de', 42)
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::getDomainData
  */
  public function testGetDomainDataRepeatCallExpectingCached() {
    $domains = $this->createMock(\Papaya\Domains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will($this->returnValue(array()));
    $domains
      ->expects($this->once())
      ->method('getCurrent')
      ->will($this->returnValue(FALSE));

    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $factory->domains($domains);
    $factory->getDomainData('de', 42);
    $this->assertTrue(
      $factory->getDomainData('de', 42)
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::getDomainData
  */
  public function testGetDomainDataExpectingTargetDomain() {
    $domains = $this->createMock(\Papaya\Domains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will(
        $this->returnValue(
          array(
            1 => array(
              'id' => 1,
              'scheme' => \Papaya\Utility\Server\Protocol::HTTP,
              'host' => 'www.success.tld',
              'language_id' => '0',
              'group_id' => '0'
            )
          )
        )
      );
    $domains
      ->expects($this->once())
      ->method('getCurrent')
      ->will(
        $this->returnValue(
          array(
            'id' => 2,
            'scheme' => \Papaya\Utility\Server\Protocol::HTTP,
            'host' => 'www.failed.tld',
            'language_id' => '0',
            'group_id' => '0'
          )
        )
      );

    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $factory->domains($domains);
    $this->assertEquals(
      array(
        'id' => 1,
        'scheme' => \Papaya\Utility\Server\Protocol::HTTP,
        'host' => 'www.success.tld',
        'language_id' => '0',
        'group_id' => '0'
      ),
      $factory->getDomainData('de', 42)
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::isDomainWithoutWildcards
  */
  public function testIsDomainWithoutWildcardsExpectingTrue() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $this->assertTrue(
      $factory->isDomainWithoutWildcards(array('host' => 'www.test.tld'))
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::isDomainWithoutWildcards
  */
  public function testIsDomainWithoutWildcardsExpectingFalse() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $this->assertFalse(
      $factory->isDomainWithoutWildcards(array('host' => '*.test.tld'))
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::getLinkAttributes
  */
  public function testGetLinkAttributesWithInvalidPageExpectingNull() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $this->assertNull(
      $factory->getLinkAttributes('de', 21)
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::getLinkAttributes
  */
  public function testGetLinkAttributesWithInvalidLinkTypeExpectingSimpleLink() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $factory->linkTypes(
      $this->getLinkTypesFixture(
        array()
      )
    );
    $this->assertNull(
      $factory->getLinkAttributes('de', 42)
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::getLinkAttributes
  */
  public function testGetLinkAttributesExpectingSimpleLink() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $factory->linkTypes(
      $this->getLinkTypesFixture(
        array(
          'id' => 1,
          'class' => 'sampleClass',
          'target' => 'sampleTarget',
          'is_popup' => FALSE,
          'popup_options' => array()
        )
      )
    );

    $document = new \Papaya\XML\Document();
    $node = $document
      ->appendElement('sample')
      ->append($factory->getLinkAttributes('de', 42));
    $this->assertEquals(
    /** @lang XML */'<sample class="sampleClass" target="sampleTarget"/>',
      $node->saveXML()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::getLinkAttributes
  * @covers \Papaya\UI\Reference\Page\Factory::setLinkPopupOption
  */
  public function testGetLinkAttributesExpectingPopupLink() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $factory->linkTypes(
      $this->getLinkTypesFixture(
        array(
          'id' => 1,
          'class' => 'sampleClass',
          'target' => 'sampleTarget',
          'is_popup' => TRUE,
          'popup_options' => array(
            'popup_width' => '84%',
            'popup_height' => '42%',
            'popup_left' => '48',
            'popup_top' => '21',
            'popup_resizable' => 1,
            'popup_location' => 1,
            'popup_menubar' => 1,
            'popup_scrollbars' => 2,
            'popup_status' => 1,
            'popup_toolbar' => 1,
          )
        )
      )
    );

    $document = new \Papaya\XML\Document();
    $node = $document
      ->appendElement('sample')
      ->append($factory->getLinkAttributes('de', 42));
    $this->assertEquals(
      '<sample class="sampleClass"'.
        ' target="sampleTarget"'.
        ' data-popup="{&quot;width&quot;:&quot;84%&quot;,&quot;height&quot;:&quot;42%&quot;,'.
          '&quot;top&quot;:&quot;21&quot;,&quot;left&quot;:&quot;48&quot;,'.
          '&quot;resizeable&quot;:true,&quot;toolBar&quot;:true,'.
          '&quot;menuBar&quot;:true,&quot;locationBar&quot;:true,'.
          '&quot;statusBar&quot;:true,'.
          '&quot;scrollBars&quot;:&quot;auto&quot;}"/>',
      $node->saveXML()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::getLinkAttributes
  * @covers \Papaya\UI\Reference\Page\Factory::setLinkPopupOption
  */
  public function testGetLinkAttributesExpectingPopupLinkWithoutBars() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $factory->linkTypes(
      $this->getLinkTypesFixture(
        array(
          'id' => 1,
          'class' => 'sampleClass',
          'target' => 'sampleTarget',
          'is_popup' => TRUE,
          'popup_options' => array(
            'popup_width' => '84%',
            'popup_height' => '42%',
            'popup_left' => '48',
            'popup_top' => '21',
            'popup_resizable' => 0,
            'popup_location' => 0,
            'popup_menubar' => 0,
            'popup_scrollbars' => 0,
            'popup_status' => 0,
            'popup_toolbar' => 0,
          )
        )
      )
    );

    $document = new \Papaya\XML\Document();
    $node = $document
      ->appendElement('sample')
      ->append($factory->getLinkAttributes('de', 42));
    $this->assertEquals(
      '<sample class="sampleClass"'.
        ' target="sampleTarget"'.
        ' data-popup="{&quot;width&quot;:&quot;84%&quot;,&quot;height&quot;:&quot;42%&quot;,'.
          '&quot;top&quot;:&quot;21&quot;,&quot;left&quot;:&quot;48&quot;,'.
          '&quot;resizeable&quot;:false,&quot;toolBar&quot;:false,'.
          '&quot;menuBar&quot;:false,&quot;locationBar&quot;:false,'.
          '&quot;statusBar&quot;:false,'.
          '&quot;scrollBars&quot;:&quot;no&quot;}"/>',
      $node->saveXML()
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::pages
  */
  public function testPagesGetAfterSet() {
    $pages = $this->createMock(Pages::class);
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->pages($pages);
    $this->assertSame($pages, $factory->pages());
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::pages
  */
  public function testPagesGetImplicitCreatePagesPublications() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $this->assertInstanceOf(Publications::class, $pages = $factory->pages());
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::pages
  */
  public function testPagesGetImplicitCreatePagesInPreviewMode() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->setPreview(TRUE);
    $this->assertNotInstanceOf(Publications::class, $pages = $factory->pages());
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::linkTypes
  */
  public function testLinkTypesGetAfterSet() {
    $linkTypes = $this->createMock(Types::class);
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->linkTypes($linkTypes);
    $this->assertSame($linkTypes, $factory->linkTypes());
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::linkTypes
  */
  public function testLinkTypesGetImplicitCreatePagesPublications() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $this->assertInstanceOf(Types::class, $linkTypes = $factory->linkTypes());
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::domains
  */
  public function testDomainsGetAfterSet() {
    $domains = $this->createMock(\Papaya\Domains::class);
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->domains($domains);
    $this->assertSame($domains, $factory->domains());
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::domains
  */
  public function testDomainsGetImplicitCreatePagesPublications() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $this->assertInstanceOf(\Papaya\Domains::class, $domains = $factory->domains());
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::languages
  */
  public function testLanguagesGetAfterSet() {
    $languages = $this->createMock(Languages::class);
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->languages($languages);
    $this->assertSame($languages, $factory->languages());
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::languages
  */
  public function testLanguagesGetImplicitFromApplicationRegistry() {
    $languages = $this->createMock(Languages::class);
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application(
        array('languages' => $languages)
      )
    );
    $this->assertInstanceOf(Languages::class, $languages = $factory->languages());
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::validateLanguageIdentifier
  */
  public function testValidateLanguageIdentifierWithExistingLanguage() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->languages($this->getLanguagesFixture());
    $this->assertEquals(
      'de', $factory->validateLanguageIdentifier('de')
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::validateLanguageIdentifier
  */
  public function testValidateLanguageIdentifierFromRequestParameters() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this
            ->mockPapaya()
            ->request(
              array('language' => 'de')
            )
        )
      )
    );
    $factory->languages($this->getLanguagesFixture());
    $this->assertEquals(
      'de', $factory->validateLanguageIdentifier('ru')
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::validateLanguageIdentifier
  */
  public function testValidateLanguageIdentifierFromOptions() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options(
            array('PAPAYA_CONTENT_LANGUAGE' => 2)
          )
        )
      )
    );
    $factory->languages($this->getLanguagesFixture());
    $this->assertEquals(
      'de', $factory->validateLanguageIdentifier('ru')
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::validateLanguageIdentifier
  */
  public function testValidateLanguageIdentifierFromInternalStorage() {
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options(
            array('PAPAYA_CONTENT_LANGUAGE' => 2)
          )
        )
      )
    );
    $factory->languages($this->getLanguagesFixture());
    $factory->validateLanguageIdentifier('tw');
    $this->assertEquals(
      'de', $factory->validateLanguageIdentifier('ru')
    );
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::preload
  * @covers \Papaya\UI\Reference\Page\Factory::getFilter
  */
  public function testPreload() {
    $pages = $this->createMock(Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(
        array(
          'id' => array(21, 42),
          'language_id' => 2
        )
      )
      ->will($this->returnValue(TRUE));
    $pages
      ->expects($this->exactly(2))
      ->method('offsetExists')
      ->will(
        $this->returnValueMap(
          array(
            array(21, FALSE),
            array(42, TRUE)
          )
        )
      );
    $pages
      ->expects($this->once())
      ->method('offsetGet')
      ->with(42)
      ->will(
        $this->returnValue(
          array('title' => 'Sample')
        )
      );
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->setPreview(TRUE);
    $factory->pages($pages);
    $factory->languages($this->getLanguagesFixture());
    $factory->preload(2, array(21, 42));
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::preload
  * @covers \Papaya\UI\Reference\Page\Factory::getFilter
  */
  public function testPreloadOptimizesLoading() {
    $pages = $this->createMock(Pages::class);
    $pages
      ->expects($this->exactly(2))
      ->method('load')
      ->with(
        $this->logicalOr(
          array('id' => array(21, 42), 'language_id' => 2),
          array('id' => array(84), 'language_id' => 2)
        )
      )
      ->will($this->returnValue(TRUE));
    $pages
      ->expects($this->exactly(3))
      ->method('offsetExists')
      ->will(
        $this->returnValueMap(
          array(
            array(21, FALSE),
            array(42, TRUE),
            array(84, FALSE)
          )
        )
      );
    $pages
      ->expects($this->once())
      ->method('offsetGet')
      ->with(42)
      ->will(
        $this->returnValue(
          array('title' => 'Sample')
        )
      );
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->setPreview(TRUE);
    $factory->pages($pages);
    $factory->languages($this->getLanguagesFixture());
    $factory->preload(2, array(21, 42));
    $factory->preload(2, array(21, 42, 84));
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::preload
  * @covers \Papaya\UI\Reference\Page\Factory::getFilter
  */
  public function testPreloadWithLanguageIdentifier() {

    $pages = $this->createMock(Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(
        array(
          'id' => array(23),
          'language_id' => 2
        )
      )
      ->will($this->returnValue(FALSE));
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->setPreview(TRUE);
    $factory->pages($pages);
    $factory->languages($this->getLanguagesFixture());
    $factory->preload('de', array(23));
  }

  /**
  * @covers \Papaya\UI\Reference\Page\Factory::preload
  * @covers \Papaya\UI\Reference\Page\Factory::getFilter
  */
  public function testPreloadWithPublicDataExpectingTimeInFilter() {
    $pages = $this->createMock(Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(
        $this->arrayHasKey('time')
      )
      ->will($this->returnValue(FALSE));
    $factory = new \Papaya\UI\Reference\Page\Factory();
    $factory->pages($pages);
    $factory->languages($this->getLanguagesFixture());
    $factory->preload('de', array(23));
  }

  /**************************
  * Fixtures
  **************************/

  public function getLanguagesFixture() {
    $languageGerman = $this->createMock(Language::class);
    $languageGerman
      ->expects($this->any())
      ->method('__get')
      ->will(
        $this->returnValueMap(
          array(
            array('id', 2),
            array('identifier', 'de'),
            array('isContent', TRUE)
          )
        )
      );

    $languages = $this->createMock(Languages::class);
    $languages
      ->expects($this->any())
      ->method('getLanguageByIdentifier')
      ->will(
        $this->returnValueMap(
          array(
            array('de', $languageGerman),
            array('', NULL)
          )
        )
      );
    $languages
      ->expects($this->any())
      ->method('getLanguage')
      ->will(
        $this->returnValueMap(
          array(
            array(2, Languages::FILTER_IS_CONTENT, $languageGerman),
            array('de', Languages::FILTER_IS_CONTENT, $languageGerman),
            array(1, Languages::FILTER_IS_CONTENT, NULL),
            array(0, Languages::FILTER_IS_CONTENT, NULL)
          )
        )
      );
    return $languages;
  }

  public function getPagesFixture(array $pageData = NULL) {
    if (empty($pageData)) {
      $pageData = array(
        'id' => 42,
        'parent' => 21,
        'path' => array(0, 1, 23),
        'title' => 'Sample Title',
        'scheme' => \Papaya\Utility\Server\Protocol::BOTH,
        'linktype_id' => 1
      );
    }
    $pages = $this->createMock(Pages::class);
    $pages
      ->expects($this->any())
      ->method('load')
      ->with(
        $this->isType('array')
      )
      ->will($this->returnValue(TRUE));
    $pages
      ->expects($this->any())
      ->method('offsetExists')
      ->will(
        $this->returnValueMap(
          array(
            array(21, FALSE),
            array(42, TRUE),
            array(84, FALSE)
          )
        )
      );
    $pages
      ->expects($this->any())
      ->method('offsetGet')
      ->with(42)
      ->will(
        $this->returnValue($pageData)
      );
    return $pages;
  }

  public function getDomainsFixture() {
    $domains = $this->createMock(\Papaya\Domains::class);
    $domains
      ->expects($this->any())
      ->method('getDomainsByPath')
      ->with(array(0,1,23,21,42))
      ->will($this->returnValue(array()));
    $domains
      ->expects($this->any())
      ->method('getCurrent')
      ->with()
      ->will($this->returnValue(FALSE));
    return $domains;
  }

  public function getLinkTypesFixture($linkData) {
    $linkTypes = $this->createMock(Types::class);
    $linkTypes
      ->expects($this->any())
      ->method('offsetExists')
      ->with($this->isType('integer'))
      ->will($this->returnValue(!empty($linkData)));
    $linkTypes
      ->expects($this->any())
      ->method('offsetGet')
      ->withAnyParameters()
      ->will($this->returnValue($linkData));
    return $linkTypes;
  }
}
