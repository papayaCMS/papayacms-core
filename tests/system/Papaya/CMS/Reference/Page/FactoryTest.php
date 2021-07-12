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
namespace Papaya\CMS\Reference\Page {

  use Papaya\CMS\Content\Language as ContentLanguage;
  use Papaya\CMS\Content\Languages as ContentLanguages;
  use Papaya\CMS\Content\Link\Types as ContentLinkTypes;
  use Papaya\CMS\Content\Page\Publications as ContentPagePublications;
  use Papaya\CMS\Content\Pages as ContentPages;
  use Papaya\CMs\Domains;
  use Papaya\TestFramework\TestCase;
  use Papaya\CMS\Reference\Page as PageReference;
  use Papaya\Utility\Server\Protocol as ServerProtocolUtility;
  use Papaya\XML\Document as XMLDocument;

  require_once __DIR__.'/../../../../../bootstrap.php';

  /**
   * @covers \Papaya\CMS\Reference\Page\Factory
   */
  class FactoryTest extends TestCase {

    public function testCreate() {
      $factory = new Factory();
      $this->assertInstanceOf(
        PageReference::class,
        $factory->create()
      );
    }

    public function testGet() {
      $factory = new Factory();
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

    public function testGetExpectingHttps() {
      $factory = new Factory();
      $factory->papaya(
        $this->mockPapaya()->application()
      );
      $factory->pages(
        $this->getPagesFixture(
          [
            'id' => 42,
            'parent' => 21,
            'path' => [0, 1, 23],
            'title' => 'Sample Title',
            'scheme' => ServerProtocolUtility::HTTPS,
            'linktype_id' => 1
          ]
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

    public function testGetWithPreviewPage() {
      $factory = new Factory();
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

    public function testGetWithInvalidPage() {
      $factory = new Factory();
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

    public function testConfigure() {
      $factory = new Factory();
      $factory->papaya(
        $this->mockPapaya()->application()
      );
      $factory->pages($this->getPagesFixture());
      $factory->domains($this->getDomainsFixture());
      $factory->languages($this->getLanguagesFixture());

      $reference = new PageReference();
      $reference->papaya(
        $this->mockPapaya()->application(
          [
            'pageReferences' => $factory
          ]
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

    public function testConfigureWithPreviewPage() {
      $factory = new Factory();
      $factory->papaya(
        $this->mockPapaya()->application()
      );
      $factory->setPreview(TRUE);
      $factory->pages($this->getPagesFixture());
      $factory->domains($this->getDomainsFixture());
      $factory->languages($this->getLanguagesFixture());

      $reference = new PageReference();
      $reference->papaya(
        $this->mockPapaya()->application(
          [
            'pageReferences' => $factory
          ]
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

    public function testConfigureWithTargetDomain() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Domains $domains */
      $domains = $this->createMock(Domains::class);
      $domains
        ->expects($this->once())
        ->method('getDomainsByPath')
        ->with([0, 1, 23, 21, 42])
        ->willReturn(
          [
            1 => [
              'id' => 1,
              'scheme' => ServerProtocolUtility::HTTP,
              'host' => 'www.success.tld',
              'language_id' => '0',
              'group_id' => '0'
            ]
          ]
        );
      $domains
        ->expects($this->once())
        ->method('getCurrent')
        ->willReturn(
          [
            'id' => 2,
            'scheme' => ServerProtocolUtility::HTTP,
            'host' => 'www.failed.tld',
            'language_id' => '0',
            'group_id' => '0'
          ]
        );

      $factory = new Factory();
      $factory->papaya(
        $this->mockPapaya()->application()
      );
      $factory->pages($this->getPagesFixture());
      $factory->domains($domains);
      $factory->languages($this->getLanguagesFixture());

      $reference = new PageReference();
      $reference->papaya(
        $this->mockPapaya()->application(
          [
            'pageReferences' => $factory
          ]
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

    public function testConfigureWithTargetDomainWithoutProtocol() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Domains $domains */
      $domains = $this->createMock(Domains::class);
      $domains
        ->expects($this->once())
        ->method('getDomainsByPath')
        ->with([0, 1, 23, 21, 42])
        ->willReturn(
          [
            1 => [
              'id' => 1,
              'scheme' => ServerProtocolUtility::BOTH,
              'host' => 'www.success.tld',
              'language_id' => '0',
              'group_id' => '0'
            ]
          ]
        );
      $domains
        ->expects($this->once())
        ->method('getCurrent')
        ->willReturn(
          [
            'id' => 2,
            'scheme' => ServerProtocolUtility::BOTH,
            'host' => 'www.failed.tld',
            'language_id' => '0',
            'group_id' => '0'
          ]
        );

      $factory = new Factory();
      $factory->papaya(
        $this->mockPapaya()->application()
      );
      $factory->pages($this->getPagesFixture());
      $factory->domains($domains);
      $factory->languages($this->getLanguagesFixture());

      $reference = new PageReference();
      $reference->papaya(
        $this->mockPapaya()->application(
          [
            'request' => $this->mockPapaya()->request([], ''),
            'pageReferences' => $factory
          ]
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

    public function testConfigureInPreviewWithTargetDomainIgnored() {
      $factory = new Factory();
      $factory->papaya(
        $this->mockPapaya()->application()
      );
      $factory->setPreview(TRUE);
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());

      $reference = new PageReference();
      $reference->papaya(
        $this->mockPapaya()->application(
          [
            'pageReferences' => $factory
          ]
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

    public function testConfigureWithInvalidPage() {
      $factory = new Factory();
      $factory->papaya(
        $this->mockPapaya()->application()
      );
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());

      $reference = new PageReference();
      $reference->papaya(
        $this->mockPapaya()->application(
          [
            'pageReferences' => $factory
          ]
        )
      );
      $reference->setPageLanguage('de');
      $reference->setPageId(21);
      $factory->configure($reference);
      $this->assertFalse(
        $reference->valid()
      );
    }

    public function testConfigureWithInvalidDomain() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Domains $domains */
      $domains = $this->createMock(Domains::class);
      $domains
        ->expects($this->once())
        ->method('getDomainsByPath')
        ->with([0, 1, 23, 21, 42])
        ->willReturn([]);
      $domains
        ->expects($this->once())
        ->method('getCurrent')
        ->willReturn(
          [
            'id' => 2,
            'scheme' => ServerProtocolUtility::HTTP,
            'host' => 'www.failed.tld',
            'language_id' => '0',
            'group_id' => '0'
          ]
        );

      $factory = new Factory();
      $factory->papaya(
        $this->mockPapaya()->application()
      );
      $factory->pages($this->getPagesFixture());
      $factory->domains($domains);
      $factory->languages($this->getLanguagesFixture());

      $reference = new PageReference();
      $reference->papaya(
        $this->mockPapaya()->application(
          [
            'pageReferences' => $factory
          ]
        )
      );
      $reference->setPageLanguage('de');
      $reference->setPageId(42);
      $factory->configure($reference);
      $this->assertFalse(
        $reference->valid()
      );
    }

    public function testGetPageData() {
      $factory = new Factory();
      $factory->setPreview(TRUE);
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());
      $this->assertEquals(
        [
          'id' => 42,
          'parent' => 21,
          'path' => [0, 1, 23],
          'title' => 'Sample Title',
          'linktype_id' => 1,
          'scheme' => 0
        ],
        $factory->getPageData('de', 42)
      );
    }

    public function testGetPageDataExpectingFalse() {
      $factory = new Factory();
      $factory->setPreview(TRUE);
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());
      $this->assertFalse(
        $factory->getPageData('de', 21)
      );
    }

    public function testIsPreviewExpectingFalse() {
      $factory = new Factory();
      $this->assertFalse($factory->isPreview());
    }

    public function testIsPreviewAfterSetPreviewExpectingTrue() {
      $factory = new Factory();
      $factory->setPreview(TRUE);
      $this->assertTrue($factory->isPreview());
    }

    public function testSetPreviewToTrueResetsPagesObject() {
      $factory = new Factory();
      $this->assertInstanceOf(ContentPagePublications::class, $pages = $factory->pages());
      $factory->setPreview(TRUE);
      $this->assertNotInstanceOf(ContentPagePublications::class, $pages = $factory->pages());
    }

    public function testSetPreviewToFalseResetsPagesObject() {
      $factory = new Factory();
      $factory->setPreview(TRUE);
      $this->assertNotInstanceOf(ContentPagePublications::class, $pages = $factory->pages());
      $factory->setPreview(FALSE);
      $this->assertInstanceOf(ContentPagePublications::class, $pages = $factory->pages());
    }

    public function testGetDomainDataExpectingSameDomainReturnTrue() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Domains $domains */
      $domains = $this->createMock(Domains::class);
      $domains
        ->expects($this->once())
        ->method('getDomainsByPath')
        ->with([0, 1, 23, 21, 42])
        ->willReturn(
          [
            1 => [
              'id' => 1,
              'scheme' => ServerProtocolUtility::HTTP,
              'host' => 'www.success.tld',
              'language_id' => '0',
              'group_id' => '0'
            ]
          ]
        );
      $domains
        ->expects($this->once())
        ->method('getCurrent')
        ->willReturn(
          [
            'id' => 1,
            'scheme' => ServerProtocolUtility::HTTP,
            'host' => 'www.success.tld',
            'language_id' => '0',
            'group_id' => '0'
          ]
        );

      $factory = new Factory();
      $factory->setPreview(TRUE);
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());
      $factory->domains($domains);
      $this->assertTrue(
        $factory->getDomainData('de', 42)
      );
    }

    public function testGetDomainDataExpectingNoDomainReturnFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Domains $domains */
      $domains = $this->createMock(Domains::class);
      $domains
        ->expects($this->once())
        ->method('getDomainsByPath')
        ->with([0, 1, 23, 21, 42])
        ->willReturn([]);
      $domains
        ->expects($this->once())
        ->method('getCurrent')
        ->willReturn(
          [
            'id' => 1,
            'scheme' => ServerProtocolUtility::HTTP,
            'host' => 'www.success.tld',
            'language_id' => '0',
            'group_id' => '0'
          ]
        );

      $factory = new Factory();
      $factory->setPreview(TRUE);
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());
      $factory->domains($domains);
      $this->assertFalse(
        $factory->getDomainData('de', 42)
      );
    }

    public function testGetDomainDataExpectingUnrestrictedCurrentDomainReturnTrue() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Domains $domains */
      $domains = $this->createMock(Domains::class);
      $domains
        ->expects($this->once())
        ->method('getDomainsByPath')
        ->with([0, 1, 23, 21, 42])
        ->willReturn([]);
      $domains
        ->expects($this->once())
        ->method('getCurrent')
        ->willReturn(FALSE);

      $factory = new Factory();
      $factory->setPreview(TRUE);
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());
      $factory->domains($domains);
      $this->assertTrue(
        $factory->getDomainData('de', 42)
      );
    }

    public function testGetDomainDataRepeatCallExpectingCached() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Domains $domains */
      $domains = $this->createMock(Domains::class);
      $domains
        ->expects($this->once())
        ->method('getDomainsByPath')
        ->with([0, 1, 23, 21, 42])
        ->willReturn([]);
      $domains
        ->expects($this->once())
        ->method('getCurrent')
        ->willReturn(FALSE);

      $factory = new Factory();
      $factory->setPreview(TRUE);
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());
      $factory->domains($domains);
      $factory->getDomainData('de', 42);
      $this->assertTrue(
        $factory->getDomainData('de', 42)
      );
    }

    public function testGetDomainDataExpectingTargetDomain() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Domains $domains */
      $domains = $this->createMock(Domains::class);
      $domains
        ->expects($this->once())
        ->method('getDomainsByPath')
        ->with([0, 1, 23, 21, 42])
        ->willReturn(
          [
            1 => [
              'id' => 1,
              'scheme' => ServerProtocolUtility::HTTP,
              'host' => 'www.success.tld',
              'language_id' => '0',
              'group_id' => '0'
            ]
          ]
        );
      $domains
        ->expects($this->once())
        ->method('getCurrent')
        ->willReturn(
          [
            'id' => 2,
            'scheme' => ServerProtocolUtility::HTTP,
            'host' => 'www.failed.tld',
            'language_id' => '0',
            'group_id' => '0'
          ]
        );

      $factory = new Factory();
      $factory->setPreview(TRUE);
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());
      $factory->domains($domains);
      $this->assertEquals(
        [
          'id' => 1,
          'scheme' => ServerProtocolUtility::HTTP,
          'host' => 'www.success.tld',
          'language_id' => '0',
          'group_id' => '0'
        ],
        $factory->getDomainData('de', 42)
      );
    }

    public function testIsDomainWithoutWildcardsExpectingTrue() {
      $factory = new Factory();
      $this->assertTrue(
        $factory->isDomainWithoutWildcards(['host' => 'www.test.tld'])
      );
    }

    public function testIsDomainWithoutWildcardsExpectingFalse() {
      $factory = new Factory();
      $this->assertFalse(
        $factory->isDomainWithoutWildcards(['host' => '*.test.tld'])
      );
    }

    public function testGetLinkAttributesWithInvalidPageExpectingNull() {
      $factory = new Factory();
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());
      $this->assertNull(
        $factory->getLinkAttributes('de', 21)
      );
    }

    public function testGetLinkAttributesWithInvalidLinkTypeExpectingSimpleLink() {
      $factory = new Factory();
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());
      $factory->linkTypes($this->getLinkTypesFixture([]));
      $this->assertNull(
        $factory->getLinkAttributes('de', 42)
      );
    }

    public function testGetLinkAttributesExpectingSimpleLink() {
      $factory = new Factory();
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());
      $factory->linkTypes(
        $this->getLinkTypesFixture(
          [
            'id' => 1,
            'class' => 'sampleClass',
            'target' => 'sampleTarget',
            'is_popup' => FALSE,
            'popup_options' => []
          ]
        )
      );

      $document = new XMLDocument();
      $node = $document->appendElement('sample');
      $node->append($factory->getLinkAttributes('de', 42));
      $this->assertEquals(
      /** @lang XML */ '<sample class="sampleClass" target="sampleTarget"/>',
                       $node->saveXML()
      );
    }

    public function testGetLinkAttributesExpectingPopupLink() {
      $factory = new Factory();
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());
      $factory->linkTypes(
        $this->getLinkTypesFixture(
          [
            'id' => 1,
            'class' => 'sampleClass',
            'target' => 'sampleTarget',
            'is_popup' => TRUE,
            'popup_options' => [
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
            ]
          ]
        )
      );

      $document = new XMLDocument();
      $node = $document->appendElement('sample');
      $node->append($factory->getLinkAttributes('de', 42));
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

    public function testGetLinkAttributesExpectingPopupLinkWithoutBars() {
      $factory = new Factory();
      $factory->pages($this->getPagesFixture());
      $factory->languages($this->getLanguagesFixture());
      $factory->linkTypes(
        $this->getLinkTypesFixture(
          [
            'id' => 1,
            'class' => 'sampleClass',
            'target' => 'sampleTarget',
            'is_popup' => TRUE,
            'popup_options' => [
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
            ]
          ]
        )
      );

      $document = new XMLDocument();
      $node = $document->appendElement('sample');
      $node->append($factory->getLinkAttributes('de', 42));
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

    public function testPagesGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|ContentPages $pages */
      $pages = $this->createMock(ContentPages::class);
      $factory = new Factory();
      $factory->pages($pages);
      $this->assertSame($pages, $factory->pages());
    }

    public function testPagesGetImplicitCreatePagesPublications() {
      $factory = new Factory();
      $this->assertInstanceOf(ContentPagePublications::class, $pages = $factory->pages());
    }

    public function testPagesGetImplicitCreatePagesInPreviewMode() {
      $factory = new Factory();
      $factory->setPreview(TRUE);
      $this->assertNotInstanceOf(ContentPagePublications::class, $pages = $factory->pages());
    }

    public function testLinkTypesGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|ContentLinkTypes $linkTypes */
      $linkTypes = $this->createMock(ContentLinkTypes::class);
      $factory = new Factory();
      $factory->linkTypes($linkTypes);
      $this->assertSame($linkTypes, $factory->linkTypes());
    }

    public function testLinkTypesGetImplicitCreatePagesPublications() {
      $factory = new Factory();
      $this->assertInstanceOf(ContentLinkTypes::class, $linkTypes = $factory->linkTypes());
    }

    public function testDomainsGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Domains $domains */
      $domains = $this->createMock(Domains::class);
      $factory = new Factory();
      $factory->domains($domains);
      $this->assertSame($domains, $factory->domains());
    }

    public function testDomainsGetImplicitCreatePagesPublications() {
      $factory = new Factory();
      $this->assertInstanceOf(Domains::class, $domains = $factory->domains());
    }

    public function testLanguagesGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|ContentLanguages $languages */
      $languages = $this->createMock(ContentLanguages::class);
      $factory = new Factory();
      $factory->languages($languages);
      $this->assertSame($languages, $factory->languages());
    }

    public function testLanguagesGetImplicitFromApplicationRegistry() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|ContentLanguages $languages */
      $languages = $this->createMock(ContentLanguages::class);
      $factory = new Factory();
      $factory->papaya(
        $this->mockPapaya()->application(
          ['languages' => $languages]
        )
      );
      $this->assertInstanceOf(ContentLanguages::class, $languages = $factory->languages());
    }

    public function testValidateLanguageIdentifierWithExistingLanguage() {
      $factory = new Factory();
      $factory->languages($this->getLanguagesFixture());
      $this->assertEquals(
        'de', $factory->validateLanguageIdentifier('de')
      );
    }

    public function testValidateLanguageIdentifierFromRequestParameters() {
      $factory = new Factory();
      $factory->papaya(
        $this->mockPapaya()->application(
          [
            'request' => $this
              ->mockPapaya()
              ->request(
                ['language' => 'de']
              )
          ]
        )
      );
      $factory->languages($this->getLanguagesFixture());
      $this->assertEquals(
        'de', $factory->validateLanguageIdentifier('ru')
      );
    }

    public function testValidateLanguageIdentifierFromOptions() {
      $factory = new Factory();
      $factory->papaya(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options(
              ['PAPAYA_CONTENT_LANGUAGE' => 2]
            )
          ]
        )
      );
      $factory->languages($this->getLanguagesFixture());
      $this->assertEquals(
        'de', $factory->validateLanguageIdentifier('ru')
      );
    }

    public function testValidateLanguageIdentifierFromInternalStorage() {
      $factory = new Factory();
      $factory->papaya(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options(
              ['PAPAYA_CONTENT_LANGUAGE' => 2]
            )
          ]
        )
      );
      $factory->languages($this->getLanguagesFixture());
      $factory->validateLanguageIdentifier('tw');
      $this->assertEquals(
        'de', $factory->validateLanguageIdentifier('ru')
      );
    }

    public function testPreload() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|ContentPages $pages */
      $pages = $this->createMock(ContentPages::class);
      $pages
        ->expects($this->once())
        ->method('load')
        ->with(
          [
            'id' => [21, 42],
            'language_id' => 2
          ]
        )
        ->willReturn(TRUE);
      $pages
        ->expects($this->exactly(2))
        ->method('offsetExists')
        ->willReturnMap(
          [
            [21, FALSE],
            [42, TRUE]
          ]
        );
      $pages
        ->expects($this->once())
        ->method('offsetGet')
        ->with(42)
        ->willReturn(
          ['title' => 'Sample']
        );
      $factory = new Factory();
      $factory->setPreview(TRUE);
      $factory->pages($pages);
      $factory->languages($this->getLanguagesFixture());
      $factory->preload(2, [21, 42]);
    }

    public function testPreloadOptimizesLoading() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|ContentPages $pages */
      $pages = $this->createMock(ContentPages::class);
      $pages
        ->expects($this->exactly(2))
        ->method('load')
        ->with(
          $this->logicalOr(
            ['id' => [21, 42], 'language_id' => 2],
            ['id' => [84], 'language_id' => 2]
          )
        )
        ->willReturn(TRUE);
      $pages
        ->expects($this->exactly(3))
        ->method('offsetExists')
        ->willReturnMap(
          [
            [21, FALSE],
            [42, TRUE],
            [84, FALSE]
          ]
        );
      $pages
        ->expects($this->once())
        ->method('offsetGet')
        ->with(42)
        ->willReturn(
          ['title' => 'Sample']
        );
      $factory = new Factory();
      $factory->setPreview(TRUE);
      $factory->pages($pages);
      $factory->languages($this->getLanguagesFixture());
      $factory->preload(2, [21, 42]);
      $factory->preload(2, [21, 42, 84]);
    }

    public function testPreloadWithLanguageIdentifier() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|ContentPages $pages */
      $pages = $this->createMock(ContentPages::class);
      $pages
        ->expects($this->once())
        ->method('load')
        ->with(
          [
            'id' => [23],
            'language_id' => 2
          ]
        )
        ->willReturn(FALSE);
      $factory = new Factory();
      $factory->setPreview(TRUE);
      $factory->pages($pages);
      $factory->languages($this->getLanguagesFixture());
      $factory->preload('de', [23]);
    }

    public function testPreloadWithPublicDataExpectingTimeInFilter() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|ContentPages $pages */
      $pages = $this->createMock(ContentPages::class);
      $pages
        ->expects($this->once())
        ->method('load')
        ->with(
          $this->arrayHasKey('time')
        )
        ->willReturn(FALSE);
      $factory = new Factory();
      $factory->pages($pages);
      $factory->languages($this->getLanguagesFixture());
      $factory->preload('de', [23]);
    }

    /**************************
     * Fixtures
     **************************/

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ContentLanguages
     */
    public function getLanguagesFixture() {
      $languageGerman = $this->createMock(ContentLanguage::class);
      $languageGerman
        ->method('__get')
        ->willReturnMap(
          [
            ['id', 2],
            ['identifier', 'de'],
            ['isContent', TRUE]
          ]
        );

      $languages = $this->createMock(ContentLanguages::class);
      $languages
        ->method('getLanguageByIdentifier')
        ->willReturnMap(
          [
            ['de', $languageGerman],
            ['', NULL]
          ]
        );
      $languages
        ->method('getLanguage')
        ->willReturnMap(
          [
            [2, ContentLanguages::FILTER_IS_CONTENT, $languageGerman],
            ['de', ContentLanguages::FILTER_IS_CONTENT, $languageGerman],
            [1, ContentLanguages::FILTER_IS_CONTENT, NULL],
            [0, ContentLanguages::FILTER_IS_CONTENT, NULL]
          ]
        );
      return $languages;
    }

    /**
     * @param array|NULL $pageData
     * @return \PHPUnit_Framework_MockObject_MockObject|ContentPages
     */
    public function getPagesFixture(array $pageData = NULL) {
      if (empty($pageData)) {
        $pageData = [
          'id' => 42,
          'parent' => 21,
          'path' => [0, 1, 23],
          'title' => 'Sample Title',
          'scheme' => ServerProtocolUtility::BOTH,
          'linktype_id' => 1
        ];
      }
      $pages = $this->createMock(ContentPages::class);
      $pages
        ->method('load')
        ->with(
          $this->isType('array')
        )
        ->willReturn(TRUE);
      $pages
        ->method('offsetExists')
        ->willReturnMap(
          [
            [21, FALSE],
            [42, TRUE],
            [84, FALSE]
          ]
        );
      $pages
        ->method('offsetGet')
        ->with(42)
        ->willReturn(
          $pageData
        );
      return $pages;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Domains
     */
    public function getDomainsFixture() {
      $domains = $this->createMock(Domains::class);
      $domains
        ->method('getDomainsByPath')
        ->with([0, 1, 23, 21, 42])
        ->willReturn([]);
      $domains
        ->method('getCurrent')
        ->with()
        ->willReturn(FALSE);
      return $domains;
    }

    /**
     * @param $linkData
     * @return \PHPUnit_Framework_MockObject_MockObject|ContentLinkTypes
     */
    public function getLinkTypesFixture($linkData) {
      $linkTypes = $this->createMock(ContentLinkTypes::class);
      $linkTypes
        ->method('offsetExists')
        ->with($this->isType('integer'))
        ->willReturn(!empty($linkData));
      $linkTypes
        ->method('offsetGet')
        ->withAnyParameters()
        ->willReturn($linkData);
      return $linkTypes;
    }
  }
}
