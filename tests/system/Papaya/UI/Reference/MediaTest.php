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

namespace Papaya\UI\Reference;

require_once __DIR__.'/../../../../bootstrap.php';

class MediaTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Reference\Media::create
   */
  public function testStaticFunctionCreate() {
    $this->assertInstanceOf(
      Media::class,
      Media::create()
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Media::load
   */
  public function testLoad() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Request $request */
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getUrl')
      ->will($this->returnValue(new \stdClass));
    $request
      ->expects($this->once())
      ->method('getParameterGroupSeparator')
      ->will($this->returnValue('/'));
    $request
      ->expects($this->any())
      ->method('getParameter')
      ->with(
        $this->isType('string'),
        $this->anything(),
        $this->isNull(),
        $this->equalTo(\Papaya\Request::SOURCE_PATH)
      )
      ->will(
        $this->returnValue(TRUE)
      );
    $reference = new Media();
    $reference->load($request);
    $this->assertEquals(
      array(
        'title' => 'index',
        'mode' => 'media',
        'media_id' => '',
        'version' => 0,
        'extension' => '',
        'preview' => TRUE
      ),
      $this->readAttribute($reference, '_pageData')
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Media::get
   */
  public function testGetDefaultExpectingNull() {
    $reference = new Media();
    $this->assertNull(
      $reference->get()
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Media::setMediaId
   * @covers \Papaya\UI\Reference\Media::get
   */
  public function testSetMediaId() {
    $reference = new Media($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setMediaId('012345678901234567890123456789ab')
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'mode' => 'media',
        'media_id' => '012345678901234567890123456789ab',
        'version' => 0,
        'extension' => '',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/index.media.012345678901234567890123456789ab',
      $reference->get()
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Media::setMediaVersion
   */
  public function testSetMediaVersion() {
    $reference = new Media($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setMediaVersion(23)
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'mode' => 'media',
        'media_id' => '',
        'version' => 23,
        'extension' => '',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Media::setTitle
   */
  public function testSetTitle() {
    $reference = new Media($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setTitle('sample')
    );
    $this->assertEquals(
      array(
        'title' => 'sample',
        'mode' => 'media',
        'media_id' => '',
        'version' => 0,
        'extension' => '',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Media::setMode
   * @dataProvider setModeDataProvider
   * @param string $mode
   * @param array $expected
   */
  public function testSetMode($mode, $expected) {
    $reference = new Media($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setMode($mode)
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'mode' => $expected,
        'media_id' => '',
        'version' => 0,
        'extension' => '',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Media::setExtension
   */
  public function testSetExtension() {
    $reference = new Media($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setExtension('mp3')
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'mode' => 'media',
        'media_id' => '',
        'version' => 0,
        'extension' => 'mp3',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Media::setMediaUri
   * @covers \Papaya\UI\Reference\Media::get
   */
  public function testSetMediaUri() {
    $reference = new Media($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setMediaUri('012345678901234567890123456789abv23.png')
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'mode' => 'media',
        'media_id' => '012345678901234567890123456789ab',
        'version' => 23,
        'extension' => 'png',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/index.media.012345678901234567890123456789abv23.png',
      $reference->get()
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Media::setPreview
   * @covers \Papaya\UI\Reference\Media::get
   */
  public function testSetPreview() {
    $reference = new Media($this->getUrlObjectMockFixture());
    $reference->setMediaUri('012345678901234567890123456789abv23.png');
    $this->assertSame(
      $reference,
      $reference->setPreview(TRUE)
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'mode' => 'media',
        'media_id' => '012345678901234567890123456789ab',
        'version' => 23,
        'extension' => 'png',
        'preview' => TRUE
      ),
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

  private function getUrlObjectMockFixture() {
    $url = $this->createMock(\Papaya\URL::class);
    $url
      ->expects($this->any())
      ->method('getHostUrl')
      ->will($this->returnValue('http://www.sample.tld'));
    return $url;
  }

  /**********************************
   * Data Provider
   **********************************/

  public static function setModeDataProvider() {
    return array(
      array('media', 'media'),
      array('download', 'download'),
      array('thumb', 'media'),
      array('thumbnail', 'media')
    );
  }
}
