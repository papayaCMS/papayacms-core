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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiReferencePageFactoryTest extends PapayaTestCase {

  /**
  * @covers PapayaUiReferencePageFactory::create
  */
  public function testCreate() {
    $factory = new PapayaUiReferencePageFactory();
    $this->assertInstanceOf(
      PapayaUiReferencePage::class,
      $factory->create()
    );
  }

  /**
  * @covers PapayaUiReferencePageFactory::get
  * @covers PapayaUiReferencePageFactory::prepareTitle
  */
  public function testGet() {
    $factory = new PapayaUiReferencePageFactory();
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
  * @covers PapayaUiReferencePageFactory::get
  * @covers PapayaUiReferencePageFactory::configure
  * @covers PapayaUiReferencePageFactory::prepareTitle
  */
  public function testGetExpectingHttps() {
    $factory = new PapayaUiReferencePageFactory();
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
          'scheme' => PapayaUtilServerProtocol::HTTPS,
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
  * @covers PapayaUiReferencePageFactory::get
  * @covers PapayaUiReferencePageFactory::prepareTitle
  */
  public function testGetWithPreviewPage() {
    $factory = new PapayaUiReferencePageFactory();
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
  * @covers PapayaUiReferencePageFactory::get
  * @covers PapayaUiReferencePageFactory::prepareTitle
  */
  public function testGetWithInvalidPage() {
    $factory = new PapayaUiReferencePageFactory();
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
  * @covers PapayaUiReferencePageFactory::configure
  * @covers PapayaUiReferencePageFactory::prepareTitle
  */
  public function testConfigure() {
    $factory = new PapayaUiReferencePageFactory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->pages($this->getPagesFixture());
    $factory->domains($this->getDomainsFixture());
    $factory->languages($this->getLanguagesFixture());

    $reference = new PapayaUiReferencePage();
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
  * @covers PapayaUiReferencePageFactory::configure
  * @covers PapayaUiReferencePageFactory::prepareTitle
  */
  public function testConfigureWithPreviewPage() {
    $factory = new PapayaUiReferencePageFactory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->domains($this->getDomainsFixture());
    $factory->languages($this->getLanguagesFixture());

    $reference = new PapayaUiReferencePage();
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
  * @covers PapayaUiReferencePageFactory::configure
  * @covers PapayaUiReferencePageFactory::prepareTitle
  */
  public function testConfigureWithTargetDomain() {
    $domains = $this->createMock(PapayaDomains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will(
        $this->returnValue(
          array(
            1 => array(
              'id' => 1,
              'scheme' => PapayaUtilServerProtocol::HTTP,
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
            'scheme' => PapayaUtilServerProtocol::HTTP,
            'host' => 'www.failed.tld',
            'language_id' => '0',
            'group_id' => '0'
          )
        )
      );

    $factory = new PapayaUiReferencePageFactory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->pages($this->getPagesFixture());
    $factory->domains($domains);
    $factory->languages($this->getLanguagesFixture());

    $reference = new PapayaUiReferencePage();
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
  * @covers PapayaUiReferencePageFactory::configure
  * @covers PapayaUiReferencePageFactory::prepareTitle
  */
  public function testConfigureWithTargetDomainWithoutProtocol() {
    $domains = $this->createMock(PapayaDomains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will(
        $this->returnValue(
          array(
            1 => array(
              'id' => 1,
              'scheme' => PapayaUtilServerProtocol::BOTH,
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
            'scheme' => PapayaUtilServerProtocol::BOTH,
            'host' => 'www.failed.tld',
            'language_id' => '0',
            'group_id' => '0'
          )
        )
      );

    $factory = new PapayaUiReferencePageFactory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->pages($this->getPagesFixture());
    $factory->domains($domains);
    $factory->languages($this->getLanguagesFixture());

    $reference = new PapayaUiReferencePage();
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
  * @covers PapayaUiReferencePageFactory::configure
  * @covers PapayaUiReferencePageFactory::prepareTitle
  */
  public function testConfigureInPreviewWithTargetDomainIgnored() {
    $factory = new PapayaUiReferencePageFactory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());

    $reference = new PapayaUiReferencePage();
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
  * @covers PapayaUiReferencePageFactory::configure
  * @covers PapayaUiReferencePageFactory::prepareTitle
  */
  public function testConfigureWithInvalidPage() {
    $factory = new PapayaUiReferencePageFactory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());

    $reference = new PapayaUiReferencePage();
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
  * @covers PapayaUiReferencePageFactory::configure
  * @covers PapayaUiReferencePageFactory::prepareTitle
  */
  public function testConfigureWithInvalidDomain() {
    $domains = $this->createMock(PapayaDomains::class);
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
            'scheme' => PapayaUtilServerProtocol::HTTP,
            'host' => 'www.failed.tld',
            'language_id' => '0',
            'group_id' => '0'
          )
        )
      );

    $factory = new PapayaUiReferencePageFactory();
    $factory->papaya(
      $this->mockPapaya()->application()
    );
    $factory->pages($this->getPagesFixture());
    $factory->domains($domains);
    $factory->languages($this->getLanguagesFixture());

    $reference = new PapayaUiReferencePage();
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
  * @covers PapayaUiReferencePageFactory::getPageData
  * @covers PapayaUiReferencePageFactory::isPageLoaded
  * @covers PapayaUiReferencePageFactory::lazyLoadPage
  */
  public function testGetPageData() {
    $factory = new PapayaUiReferencePageFactory();
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
  * @covers PapayaUiReferencePageFactory::getPageData
  * @covers PapayaUiReferencePageFactory::isPageLoaded
  * @covers PapayaUiReferencePageFactory::lazyLoadPage
  */
  public function testGetPageDataExpectingFalse() {
    $factory = new PapayaUiReferencePageFactory();
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $this->assertFalse(
      $factory->getPageData('de', 21)
    );
  }

  /**
  * @covers PapayaUiReferencePageFactory::isPreview
  */
  public function testIsPreviewExpectingFalse() {
    $factory = new PapayaUiReferencePageFactory();
    $this->assertFalse($factory->isPreview());
  }

  /**
  * @covers PapayaUiReferencePageFactory::isPreview
  * @covers PapayaUiReferencePageFactory::setPreview
  */
  public function testIsPreviewAfterSetPreviewExpectingTrue() {
    $factory = new PapayaUiReferencePageFactory();
    $factory->setPreview(TRUE);
    $this->assertTrue($factory->isPreview());
  }

  /**
  * @covers PapayaUiReferencePageFactory::setPreview
  */
  public function testSetPreviewToTrueResetsPagesObject() {
    $factory = new PapayaUiReferencePageFactory();
    $this->assertInstanceOf(PapayaContentPagesPublications::class, $pages = $factory->pages());
    $factory->setPreview(TRUE);
    $this->assertNotInstanceOf(PapayaContentPagesPublications::class, $pages = $factory->pages());
  }

  /**
  * @covers PapayaUiReferencePageFactory::setPreview
  */
  public function testSetPreviewToFalseResetsPagesObject() {
    $factory = new PapayaUiReferencePageFactory();
    $factory->setPreview(TRUE);
    $this->assertNotInstanceOf(PapayaContentPagesPublications::class, $pages = $factory->pages());
    $factory->setPreview(FALSE);
    $this->assertInstanceOf(PapayaContentPagesPublications::class, $pages = $factory->pages());
  }

  /**
  * @covers PapayaUiReferencePageFactory::getDomainData
  */
  public function testGetDomainDataExpectingSameDomainReturnTrue() {
    $domains = $this->createMock(PapayaDomains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will(
        $this->returnValue(
          array(
            1 => array(
              'id' => 1,
              'scheme' => PapayaUtilServerProtocol::HTTP,
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
            'scheme' => PapayaUtilServerProtocol::HTTP,
            'host' => 'www.success.tld',
            'language_id' => '0',
            'group_id' => '0'
          )
        )
      );

    $factory = new PapayaUiReferencePageFactory();
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $factory->domains($domains);
    $this->assertTrue(
      $factory->getDomainData('de', 42)
    );
  }

  /**
  * @covers PapayaUiReferencePageFactory::getDomainData
  */
  public function testGetDomainDataExpectingNoDomainReturnFalse() {
    $domains = $this->createMock(PapayaDomains::class);
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
            'scheme' => PapayaUtilServerProtocol::HTTP,
            'host' => 'www.success.tld',
            'language_id' => '0',
            'group_id' => '0'
          )
        )
      );

    $factory = new PapayaUiReferencePageFactory();
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $factory->domains($domains);
    $this->assertFalse(
      $factory->getDomainData('de', 42)
    );
  }

  /**
  * @covers PapayaUiReferencePageFactory::getDomainData
  */
  public function testGetDomainDataExpectingUnrestrictedCurrentDomainReturnTrue() {
    $domains = $this->createMock(PapayaDomains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will($this->returnValue(array()));
    $domains
      ->expects($this->once())
      ->method('getCurrent')
      ->will($this->returnValue(FALSE));

    $factory = new PapayaUiReferencePageFactory();
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $factory->domains($domains);
    $this->assertTrue(
      $factory->getDomainData('de', 42)
    );
  }

  /**
  * @covers PapayaUiReferencePageFactory::getDomainData
  */
  public function testGetDomainDataRepeatCallExpectingCached() {
    $domains = $this->createMock(PapayaDomains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will($this->returnValue(array()));
    $domains
      ->expects($this->once())
      ->method('getCurrent')
      ->will($this->returnValue(FALSE));

    $factory = new PapayaUiReferencePageFactory();
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
  * @covers PapayaUiReferencePageFactory::getDomainData
  */
  public function testGetDomainDataExpectingTargetDomain() {
    $domains = $this->createMock(PapayaDomains::class);
    $domains
      ->expects($this->once())
      ->method('getDomainsByPath')
      ->with(array(0, 1, 23, 21, 42))
      ->will(
        $this->returnValue(
          array(
            1 => array(
              'id' => 1,
              'scheme' => PapayaUtilServerProtocol::HTTP,
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
            'scheme' => PapayaUtilServerProtocol::HTTP,
            'host' => 'www.failed.tld',
            'language_id' => '0',
            'group_id' => '0'
          )
        )
      );

    $factory = new PapayaUiReferencePageFactory();
    $factory->setPreview(TRUE);
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $factory->domains($domains);
    $this->assertEquals(
      array(
        'id' => 1,
        'scheme' => PapayaUtilServerProtocol::HTTP,
        'host' => 'www.success.tld',
        'language_id' => '0',
        'group_id' => '0'
      ),
      $factory->getDomainData('de', 42)
    );
  }

  /**
  * @covers PapayaUiReferencePageFactory::isDomainWithoutWildcards
  */
  public function testIsDomainWithoutWildcardsExpectingTrue() {
    $factory = new PapayaUiReferencePageFactory();
    $this->assertTrue(
      $factory->isDomainWithoutWildcards(array('host' => 'www.test.tld'))
    );
  }

  /**
  * @covers PapayaUiReferencePageFactory::isDomainWithoutWildcards
  */
  public function testIsDomainWithoutWildcardsExpectingFalse() {
    $factory = new PapayaUiReferencePageFactory();
    $this->assertFalse(
      $factory->isDomainWithoutWildcards(array('host' => '*.test.tld'))
    );
  }

  /**
  * @covers PapayaUiReferencePageFactory::getLinkAttributes
  */
  public function testGetLinkAttributesWithInvalidPageExpectingNull() {
    $factory = new PapayaUiReferencePageFactory();
    $factory->pages($this->getPagesFixture());
    $factory->languages($this->getLanguagesFixture());
    $this->assertNull(
      $factory->getLinkAttributes('de', 21)
    );
  }

  /**
  * @covers PapayaUiReferencePageFactory::getLinkAttributes
  */
  public function testGetLinkAttributesWithInvalidLinkTypeExpectingSimpleLink() {
    $factory = new PapayaUiReferencePageFactory();
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
  * @covers PapayaUiReferencePageFactory::getLinkAttributes
  */
  public function testGetLinkAttributesExpectingSimpleLink() {
    $factory = new PapayaUiReferencePageFactory();
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

    $document = new PapayaXmlDocument();
    $node = $document
      ->appendElement('sample')
      ->append($factory->getLinkAttributes('de', 42));
    $this->assertEquals(
    /** @lang XML */'<sample class="sampleClass" target="sampleTarget"/>',
      $node->saveXml()
    );
  }

  /**
  * @covers PapayaUiReferencePageFactory::getLinkAttributes
  * @covers PapayaUiReferencePageFactory::setLinkPopupOption
  */
  public function testGetLinkAttributesExpectingPopupLink() {
    $factory = new PapayaUiReferencePageFactory();
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

    $document = new PapayaXmlDocument();
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
      $node->saveXml()
    );
  }

  /**
  * @covers PapayaUiReferencePageFactory::getLinkAttributes
  * @covers PapayaUiReferencePageFactory::setLinkPopupOption
  */
  public function testGetLinkAttributesExpectingPopupLinkWithoutBars() {
    $factory = new PapayaUiReferencePageFactory();
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

    $document = new PapayaXmlDocument();
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
      $node->saveXml()
    );
  }

  /**
  * @covers PapayaUiReferencePageFactory::pages
  */
  public function testPagesGetAfterSet() {
    $pages = $this->createMock(PapayaContentPages::class);
    $factory = new PapayaUiReferencePageFactory();
    $factory->pages($pages);
    $this->assertSame($pages, $factory->pages());
  }

  /**
  * @covers PapayaUiReferencePageFactory::pages
  */
  public function testPagesGetImplicitCreatePagesPublications() {
    $factory = new PapayaUiReferencePageFactory();
    $this->assertInstanceOf(PapayaContentPagesPublications::class, $pages = $factory->pages());
  }

  /**
  * @covers PapayaUiReferencePageFactory::pages
  */
  public function testPagesGetImplicitCreatePagesInPreviewMode() {
    $factory = new PapayaUiReferencePageFactory();
    $factory->setPreview(TRUE);
    $this->assertNotInstanceOf(PapayaContentPagesPublications::class, $pages = $factory->pages());
  }

  /**
  * @covers PapayaUiReferencePageFactory::linkTypes
  */
  public function testLinkTypesGetAfterSet() {
    $linkTypes = $this->createMock(Types::class);
    $factory = new PapayaUiReferencePageFactory();
    $factory->linkTypes($linkTypes);
    $this->assertSame($linkTypes, $factory->linkTypes());
  }

  /**
  * @covers PapayaUiReferencePageFactory::linkTypes
  */
  public function testLinkTypesGetImplicitCreatePagesPublications() {
    $factory = new PapayaUiReferencePageFactory();
    $this->assertInstanceOf(Types::class, $linkTypes = $factory->linkTypes());
  }

  /**
  * @covers PapayaUiReferencePageFactory::domains
  */
  public function testDomainsGetAfterSet() {
    $domains = $this->createMock(PapayaDomains::class);
    $factory = new PapayaUiReferencePageFactory();
    $factory->domains($domains);
    $this->assertSame($domains, $factory->domains());
  }

  /**
  * @covers PapayaUiReferencePageFactory::domains
  */
  public function testDomainsGetImplicitCreatePagesPublications() {
    $factory = new PapayaUiReferencePageFactory();
    $this->assertInstanceOf(PapayaDomains::class, $domains = $factory->domains());
  }

  /**
  * @covers PapayaUiReferencePageFactory::languages
  */
  public function testLanguagesGetAfterSet() {
    $languages = $this->createMock(PapayaContentLanguages::class);
    $factory = new PapayaUiReferencePageFactory();
    $factory->languages($languages);
    $this->assertSame($languages, $factory->languages());
  }

  /**
  * @covers PapayaUiReferencePageFactory::languages
  */
  public function testLanguagesGetImplicitFromApplicationRegistry() {
    $languages = $this->createMock(PapayaContentLanguages::class);
    $factory = new PapayaUiReferencePageFactory();
    $factory->papaya(
      $this->mockPapaya()->application(
        array('languages' => $languages)
      )
    );
    $this->assertInstanceOf(PapayaContentLanguages::class, $languages = $factory->languages());
  }

  /**
  * @covers PapayaUiReferencePageFactory::validateLanguageIdentifier
  */
  public function testValidateLanguageIdentifierWithExistingLanguage() {
    $factory = new PapayaUiReferencePageFactory();
    $factory->languages($this->getLanguagesFixture());
    $this->assertEquals(
      'de', $factory->validateLanguageIdentifier('de')
    );
  }

  /**
  * @covers PapayaUiReferencePageFactory::validateLanguageIdentifier
  */
  public function testValidateLanguageIdentifierFromRequestParameters() {
    $factory = new PapayaUiReferencePageFactory();
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
  * @covers PapayaUiReferencePageFactory::validateLanguageIdentifier
  */
  public function testValidateLanguageIdentifierFromOptions() {
    $factory = new PapayaUiReferencePageFactory();
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
  * @covers PapayaUiReferencePageFactory::validateLanguageIdentifier
  */
  public function testValidateLanguageIdentifierFromInternalStorage() {
    $factory = new PapayaUiReferencePageFactory();
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
  * @covers PapayaUiReferencePageFactory::preload
  * @covers PapayaUiReferencePageFactory::getFilter
  */
  public function testPreload() {
    $pages = $this->createMock(PapayaContentPages::class);
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
    $factory = new PapayaUiReferencePageFactory();
    $factory->setPreview(TRUE);
    $factory->pages($pages);
    $factory->languages($this->getLanguagesFixture());
    $factory->preload(2, array(21, 42));
  }

  /**
  * @covers PapayaUiReferencePageFactory::preload
  * @covers PapayaUiReferencePageFactory::getFilter
  */
  public function testPreloadOptimizesLoading() {
    $pages = $this->createMock(PapayaContentPages::class);
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
    $factory = new PapayaUiReferencePageFactory();
    $factory->setPreview(TRUE);
    $factory->pages($pages);
    $factory->languages($this->getLanguagesFixture());
    $factory->preload(2, array(21, 42));
    $factory->preload(2, array(21, 42, 84));
  }

  /**
  * @covers PapayaUiReferencePageFactory::preload
  * @covers PapayaUiReferencePageFactory::getFilter
  */
  public function testPreloadWithLanguageIdentifier() {

    $pages = $this->createMock(PapayaContentPages::class);
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
    $factory = new PapayaUiReferencePageFactory();
    $factory->setPreview(TRUE);
    $factory->pages($pages);
    $factory->languages($this->getLanguagesFixture());
    $factory->preload('de', array(23));
  }

  /**
  * @covers PapayaUiReferencePageFactory::preload
  * @covers PapayaUiReferencePageFactory::getFilter
  */
  public function testPreloadWithPublicDataExpectingTimeInFilter() {
    $pages = $this->createMock(PapayaContentPages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(
        $this->arrayHasKey('time')
      )
      ->will($this->returnValue(FALSE));
    $factory = new PapayaUiReferencePageFactory();
    $factory->pages($pages);
    $factory->languages($this->getLanguagesFixture());
    $factory->preload('de', array(23));
  }

  /**************************
  * Fixtures
  **************************/

  public function getLanguagesFixture() {
    $languageGerman = $this->createMock(PapayaContentLanguage::class);
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

    $languages = $this->createMock(PapayaContentLanguages::class);
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
            array(2, PapayaContentLanguages::FILTER_IS_CONTENT, $languageGerman),
            array('de', PapayaContentLanguages::FILTER_IS_CONTENT, $languageGerman),
            array(1, PapayaContentLanguages::FILTER_IS_CONTENT, NULL),
            array(0, PapayaContentLanguages::FILTER_IS_CONTENT, NULL)
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
        'scheme' => PapayaUtilServerProtocol::BOTH,
        'linktype_id' => 1
      );
    }
    $pages = $this->createMock(PapayaContentPages::class);
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
    $domains = $this->createMock(PapayaDomains::class);
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
