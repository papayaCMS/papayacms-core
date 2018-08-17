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

class ThumbnailTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Reference\Thumbnail::create
   */
  public function testStaticFunctionCreate() {
    $this->assertInstanceOf(
      Thumbnail::class,
      Thumbnail::create()
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Thumbnail::load
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
    $reference = new Thumbnail();
    $reference->load($request);
    $this->assertEquals(
      array(
        'title' => 'index',
        'media_id' => '',
        'version' => 0,
        'thumbnail_mode' => 'max',
        'thumbnail_size' => '',
        'thumbnail_params' => '',
        'extension' => '',
        'preview' => TRUE
      ),
      $this->readAttribute($reference, '_pageData')
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Thumbnail::get
   */
  public function testGetDefaultExpectingNull() {
    $reference = new Thumbnail();
    $this->assertNull(
      $reference->get()
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Thumbnail::setMediaId
   * @covers \Papaya\UI\Reference\Thumbnail::get
   */
  public function testSetMediaId() {
    $reference = new Thumbnail($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setMediaId('012345678901234567890123456789ab')
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'media_id' => '012345678901234567890123456789ab',
        'version' => 0,
        'thumbnail_mode' => 'max',
        'thumbnail_size' => '',
        'thumbnail_params' => '',
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
   * @covers \Papaya\UI\Reference\Thumbnail::setMediaVersion
   */
  public function testSetMediaVersion() {
    $reference = new Thumbnail($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setMediaVersion(23)
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'media_id' => '',
        'version' => 23,
        'thumbnail_mode' => 'max',
        'thumbnail_size' => '',
        'thumbnail_params' => '',
        'extension' => '',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Thumbnail::setTitle
   */
  public function testSetTitle() {
    $reference = new Thumbnail($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setTitle('sample')
    );
    $this->assertEquals(
      array(
        'title' => 'sample',
        'media_id' => '',
        'version' => 0,
        'thumbnail_mode' => 'max',
        'thumbnail_size' => '',
        'thumbnail_params' => '',
        'extension' => '',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Thumbnail::setThumbnailMode
   */
  public function testSetThumbnailMode() {
    $reference = new Thumbnail($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setThumbnailMode('min')
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'media_id' => '',
        'version' => 0,
        'thumbnail_mode' => 'min',
        'thumbnail_size' => '',
        'thumbnail_params' => '',
        'extension' => '',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Thumbnail::setThumbnailSize
   */
  public function testSetThumbnailSize() {
    $reference = new Thumbnail($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setThumbnailSize('1x1')
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'media_id' => '',
        'version' => 0,
        'thumbnail_mode' => 'max',
        'thumbnail_size' => '1x1',
        'thumbnail_params' => '',
        'extension' => '',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
  }

  /**
   * @covers       \Papaya\UI\Reference\Thumbnail::setThumbnailParameters
   * @dataProvider setThumbnailParametersDataProvider
   * @param array|string $parameters
   * @param string $expected
   */
  public function testSetThumbnailParameters($parameters, $expected) {
    $reference = new Thumbnail($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setThumbnailParameters($parameters)
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'media_id' => '',
        'version' => 0,
        'thumbnail_mode' => 'max',
        'thumbnail_size' => '',
        'thumbnail_params' => $expected,
        'extension' => '',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Thumbnail::setExtension
   */
  public function testSetExtension() {
    $reference = new Thumbnail($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setExtension('JPG')
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'media_id' => '',
        'version' => 0,
        'thumbnail_mode' => 'max',
        'thumbnail_size' => '',
        'thumbnail_params' => '',
        'extension' => 'jpg',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Thumbnail::setMediaUri
   * @covers \Papaya\UI\Reference\Thumbnail::get
   */
  public function testSetMediaUri() {
    $reference = new Thumbnail($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setMediaUri(
        '012345678901234567890123456789abv23_min_20x20_012345678901234567890123456789de.png'
      )
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'media_id' => '012345678901234567890123456789ab',
        'version' => 23,
        'thumbnail_mode' => 'min',
        'thumbnail_size' => '20x20',
        'thumbnail_params' => '012345678901234567890123456789de',
        'extension' => 'png',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/'.
      'index.thumb.012345678901234567890123456789abv23'.
      '_min_20x20_012345678901234567890123456789de.png',
      $reference->get()
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Thumbnail::setMediaUri
   * @covers \Papaya\UI\Reference\Thumbnail::get
   */
  public function testSetMediaUriSimple() {
    $reference = new Thumbnail($this->getUrlObjectMockFixture());
    $this->assertSame(
      $reference,
      $reference->setMediaUri(
        '59b56cc48b253e36c87c2a2e15772dc1.jpg'
      )
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'media_id' => '59b56cc48b253e36c87c2a2e15772dc1',
        'version' => 0,
        'thumbnail_mode' => 'max',
        'thumbnail_size' => '',
        'thumbnail_params' => '',
        'extension' => 'jpg',
        'preview' => FALSE
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/index.media.59b56cc48b253e36c87c2a2e15772dc1.jpg',
      $reference->get()
    );
  }

  /**
   * @covers \Papaya\UI\Reference\Thumbnail::setPreview
   * @covers \Papaya\UI\Reference\Thumbnail::get
   */
  public function testSetPreview() {
    $reference = new Thumbnail($this->getUrlObjectMockFixture());
    $reference->setMediaUri('012345678901234567890123456789abv23_max_20x20.png');
    $this->assertSame(
      $reference,
      $reference->setPreview(TRUE)
    );
    $this->assertEquals(
      array(
        'title' => 'index',
        'media_id' => '012345678901234567890123456789ab',
        'version' => 23,
        'thumbnail_mode' => 'max',
        'thumbnail_size' => '20x20',
        'thumbnail_params' => '',
        'extension' => 'png',
        'preview' => TRUE
      ),
      $this->readAttribute($reference, '_pageData')
    );
    $this->assertEquals(
      'http://www.sample.tld/'.
      'index.thumb.preview.012345678901234567890123456789abv23_max_20x20.png',
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

  public static function setThumbnailParametersDataProvider() {
    return array(
      array('012345678901234567890123456789cd', '012345678901234567890123456789cd'),
      array('test', '098f6bcd4621d373cade4e832627b4f6'),
      array(array('test'), 'acc75d777f16492f95ba7c572335b7f7')
    );
  }
}
