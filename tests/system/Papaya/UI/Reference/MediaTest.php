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

namespace Papaya\UI\Reference {

  use Papaya\Request;
  use Papaya\TestCase;
  use Papaya\URL;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\UI\Reference\Media
   */
  class MediaTest extends TestCase {

    public function testStaticFunctionCreate() {
      $this->assertInstanceOf(
        Media::class,
        Media::create()
      );
    }

    public function testLoad() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Request $request */
      $request = $this->createMock(Request::class);
      $request
        ->expects($this->once())
        ->method('getUrl')
        ->willReturn(new \stdClass());
      $request
        ->expects($this->once())
        ->method('getParameterGroupSeparator')
        ->willReturn('/');
      $request
        ->method('getParameter')
        ->with(
          $this->isType('string'),
          $this->anything(),
          $this->isNull(),
          $this->equalTo(Request::SOURCE_PATH)
        )
        ->willReturn(
          TRUE
        );
      $reference = new Media();
      $reference->load($request);
      $this->assertEquals(
        [
          'title' => 'index',
          'mode' => 'media',
          'media_id' => '',
          'version' => 0,
          'extension' => '',
          'preview' => TRUE
        ],
        $this->readAttribute($reference, '_pageData')
      );
    }

    public function testGetDefaultExpectingNull() {
      $reference = new Media();
      $this->assertNull(
        $reference->get()
      );
    }

    public function testSetMediaId() {
      $reference = new Media($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setMediaId('012345678901234567890123456789ab')
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'mode' => 'media',
          'media_id' => '012345678901234567890123456789ab',
          'version' => 0,
          'extension' => '',
          'preview' => FALSE
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/index.media.012345678901234567890123456789ab',
        $reference->get()
      );
    }

    public function testSetMediaVersion() {
      $reference = new Media($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setMediaVersion(23)
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'mode' => 'media',
          'media_id' => '',
          'version' => 23,
          'extension' => '',
          'preview' => FALSE
        ],
        $this->readAttribute($reference, '_pageData')
      );
    }

    public function testSetTitle() {
      $reference = new Media($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setTitle('sample')
      );
      $this->assertEquals(
        [
          'title' => 'sample',
          'mode' => 'media',
          'media_id' => '',
          'version' => 0,
          'extension' => '',
          'preview' => FALSE
        ],
        $this->readAttribute($reference, '_pageData')
      );
    }

    /**
     * @param string $mode
     * @param array $expected
     * @testWith
     *   ["media", "media"]
     *   ["download", "download"]
     *   ["thumb", "media"]
     *   ["thumbnail", "media"]
     */
    public function testSetMode($mode, $expected) {
      $reference = new Media($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setMode($mode)
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'mode' => $expected,
          'media_id' => '',
          'version' => 0,
          'extension' => '',
          'preview' => FALSE
        ],
        $this->readAttribute($reference, '_pageData')
      );
    }

    public function testSetExtension() {
      $reference = new Media($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setExtension('mp3')
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'mode' => 'media',
          'media_id' => '',
          'version' => 0,
          'extension' => 'mp3',
          'preview' => FALSE
        ],
        $this->readAttribute($reference, '_pageData')
      );
    }

    public function testSetMediaUri() {
      $reference = new Media($this->getUrlObjectMockFixture());
      $this->assertSame(
        $reference,
        $reference->setMediaUri('012345678901234567890123456789abv23.png')
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'mode' => 'media',
          'media_id' => '012345678901234567890123456789ab',
          'version' => 23,
          'extension' => 'png',
          'preview' => FALSE
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/index.media.012345678901234567890123456789abv23.png',
        $reference->get()
      );
    }

    public function testSetPreview() {
      $reference = new Media($this->getUrlObjectMockFixture());
      $reference->setMediaUri('012345678901234567890123456789abv23.png');
      $this->assertSame(
        $reference,
        $reference->setPreview(TRUE)
      );
      $this->assertEquals(
        [
          'title' => 'index',
          'mode' => 'media',
          'media_id' => '012345678901234567890123456789ab',
          'version' => 23,
          'extension' => 'png',
          'preview' => TRUE
        ],
        $this->readAttribute($reference, '_pageData')
      );
      $this->assertEquals(
        'http://www.sample.tld/index.media.preview.012345678901234567890123456789abv23.png',
        $reference->get()
      );
    }


    /**********************************
     * Fixtures
     **********************************/

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|URL
     */
    private function getUrlObjectMockFixture() {
      $url = $this->createMock(URL::class);
      $url
        ->method('getHostUrl')
        ->willReturn('http://www.sample.tld');
      return $url;
    }
  }
}
