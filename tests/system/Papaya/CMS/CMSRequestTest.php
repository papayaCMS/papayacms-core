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

namespace Papaya\CMS {

  use Papaya\Request;
  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Request
   */
  class CMSRequestTest extends TestCase {


    public function testGetPropertyLanguage() {
      $request = new CMSRequest();
      $request->papaya($this->mockPapaya()->application());
      $this->assertInstanceOf(
        Content\Language::class,
        $request->language
      );
    }

    public function testGetPropertyLanguageInitializeFromParameter() {
      $request = new CMSRequest();
      $request->papaya($this->mockPapaya()->application());
      $request->setParameters(
        Request::SOURCE_PATH,
        new Request\Parameters(['language' => 'de'])
      );
      $this->assertEquals(
        [['identifier' => 'de']],
        $request->language->getLazyLoadParameters()
      );
    }

    public function testGetPropertyLanguageInitializeFromOptions() {
      $request = new CMSRequest();
      $request->papaya($this->mockPapaya()->application());
      $request->papaya(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options(['PAPAYA_CONTENT_LANGUAGE' => 3])
          ]
        )
      );
      $this->assertEquals(
        [['id' => 3]],
        $request->language->getLazyLoadParameters()
      );
    }

    public function testSetPropertyLanguage() {
      $request = new CMSRequest();
      $request->papaya($this->mockPapaya()->application());
      $request->language = $language = $this->createMock(Content\Language::class);
      $this->assertSame(
        $language,
        $request->language
      );
    }

    public function testGetPropertyLanguageId() {
      $language = $this->createMock(Content\Language::class);
      $language
        ->expects($this->once())
        ->method('__get')
        ->with('id')
        ->willReturn('3');

      $request = new CMSRequest();
      $request->language = $language;
      $this->assertEquals(3, $request->languageId);
    }

    public function testGetPropertyLanguageCode() {
      $language = $this->createMock(Content\Language::class);
      $language
        ->expects($this->once())
        ->method('__get')
        ->with('identifier')
        ->willReturn('en');

      $request = new CMSRequest();
      $request->language = $language;
      $this->assertEquals('en', $request->languageIdentifier);
    }

    public function testGetPropertyModeGetAfterSet() {
      $mode = $this->createMock(Content\View\Mode::class);
      $request = new CMSRequest();
      $request->mode = $mode;
      $this->assertSame($mode, $request->mode);
    }

    public function testGetPropertyModeId() {
      $mode = $this->createMock(Content\View\Mode::class);
      $mode
        ->expects($this->once())
        ->method('__get')
        ->with('id')
        ->willReturn(42);

      $request = new CMSRequest();
      $request->mode = $mode;
      $this->assertEquals(42, $request->modeId);
    }

    public function testGetPropertyIsAdministrationGetAfterSet() {
      $request = new CMSRequest();
      $this->assertTrue(isset($request->isAdministration));
      $this->assertFalse($request->isAdministration);
      $request->isAdministration = TRUE;
      $this->assertTrue($request->isAdministration);
    }

    public function testUnsetPropertyExpectingException() {
      $request = new CMSRequest();
      $this->expectException(\LogicException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      unset($request->isAdministration);
    }

    public function testGetPropertyPageIdFromParameters() {
      $request = new CMSRequest();
      $request->papaya($this->mockPapaya()->application());
      $request->setParameters(
        Request::SOURCE_PATH, new Request\Parameters(['page_id' => 42])
      );
      $this->assertTrue(isset($request->pageId));
      $this->assertEquals(42, $request->pageId);
    }

    public function testGetPropertyCategoryIdFromParameters() {
      $request = new CMSRequest();
      $request->papaya($this->mockPapaya()->application());
      $request->setParameters(
        Request::SOURCE_PATH, new Request\Parameters(['category_id' => 42])
      );
      $this->assertTrue(isset($request->categoryId));
      $this->assertEquals(42, $request->categoryId);
    }

    public function testGetPropertyPageIdFromOptions() {
      $request = new CMSRequest();
      $request->papaya(
        $this->mockPapaya()->application(
          [
            'options' => $this->mockPapaya()->options(['PAPAYA_PAGEID_DEFAULT' => 42])
          ]
        )
      );
      $this->assertEquals(42, $request->pageId);
    }

    public function testGetPropertyIsPreviewFromParameters() {
      $request = new CMSRequest();
      $request->papaya($this->mockPapaya()->application());
      $request->setParameters(
        Request::SOURCE_PATH, new Request\Parameters(['preview' => TRUE])
      );
      $this->assertTrue(isset($request->isPreview));
      $this->assertTrue($request->isPreview);
    }
  }
}
