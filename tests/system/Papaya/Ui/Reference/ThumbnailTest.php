<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiReferenceThumbnailTest extends PapayaTestCase {

  /**
  * @covers PapayaUiReferenceThumbnail::create
  */
  public function testStaticFunctionCreate() {
    $this->assertInstanceOf(
      'PapayaUiReferenceThumbnail',
      PapayaUiReferenceThumbnail::create()
    );
  }

  /**
  * @covers PapayaUiReferenceThumbnail::load
  */
  public function testLoad() {
    $request = $this->createMock(PapayaRequest::class);
    $request
      ->expects($this->once())
      ->method('getUrl')
      ->will($this->returnValue(new stdClass));
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
        $this->equalTo(PapayaRequest::SOURCE_PATH)
      )
      ->will(
        $this->returnValue(TRUE)
      );
    $reference = new PapayaUiReferenceThumbnail();
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
  * @covers PapayaUiReferenceThumbnail::get
  */
  public function testGetDefaultExpectingNull() {
    $reference = new PapayaUiReferenceThumbnail();
    $this->assertNull(
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferenceThumbnail::setMediaId
  * @covers PapayaUiReferenceThumbnail::get
  */
  public function testSetMediaId() {
    $reference = new PapayaUiReferenceThumbnail($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceThumbnail::setMediaVersion
  */
  public function testSetMediaVersion() {
    $reference = new PapayaUiReferenceThumbnail($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceThumbnail::setTitle
  */
  public function testSetTitle() {
    $reference = new PapayaUiReferenceThumbnail($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceThumbnail::setThumbnailMode
  */
  public function testSetThumbnailMode() {
    $reference = new PapayaUiReferenceThumbnail($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceThumbnail::setThumbnailSize
  */
  public function testSetThumbnailSize() {
    $reference = new PapayaUiReferenceThumbnail($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceThumbnail::setThumbnailParameters
  * @dataProvider setThumbnailParametersDataProvider
  */
  public function testSetThumbnailParameters($parameters, $expected) {
    $reference = new PapayaUiReferenceThumbnail($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceThumbnail::setExtension
  */
  public function testSetExtension() {
    $reference = new PapayaUiReferenceThumbnail($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceThumbnail::setMediaUri
  * @covers PapayaUiReferenceThumbnail::get
  */
  public function testSetMediaUri() {
    $reference = new PapayaUiReferenceThumbnail($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceThumbnail::setMediaUri
  * @covers PapayaUiReferenceThumbnail::get
  */
  public function testSetMediaUriSimple() {
    $reference = new PapayaUiReferenceThumbnail($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceThumbnail::setPreview
  * @covers PapayaUiReferenceThumbnail::get
  */
  public function testSetPreview() {
    $reference = new PapayaUiReferenceThumbnail($this->getUrlObjectMockFixture());
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
    $url = $this->createMock(PapayaUrl::class);
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
