<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiReferenceMediaTest extends PapayaTestCase {

  /**
  * @covers PapayaUiReferenceMedia::create
  */
  public function testStaticFunctionCreate() {
    $this->assertInstanceOf(
      'PapayaUiReferenceMedia',
      PapayaUiReferenceMedia::create()
    );
  }

  /**
  * @covers PapayaUiReferenceMedia::load
  */
  public function testLoad() {
    $request = $this->getMock('PapayaRequest');
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
    $reference = new PapayaUiReferenceMedia();
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
  * @covers PapayaUiReferenceMedia::get
  */
  public function testGetDefaultExpectingNull() {
    $reference = new PapayaUiReferenceMedia();
    $this->assertNull(
      $reference->get()
    );
  }

  /**
  * @covers PapayaUiReferenceMedia::setMediaId
  * @covers PapayaUiReferenceMedia::get
  */
  public function testSetMediaId() {
    $reference = new PapayaUiReferenceMedia($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceMedia::setMediaVersion
  */
  public function testSetMediaVersion() {
    $reference = new PapayaUiReferenceMedia($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceMedia::setTitle
  */
  public function testSetTitle() {
    $reference = new PapayaUiReferenceMedia($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceMedia::setMode
  * @dataProvider setModeDataProvider
  */
  public function testSetMode($mode, $expected) {
    $reference = new PapayaUiReferenceMedia($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceMedia::setExtension
  */
  public function testSetExtension() {
    $reference = new PapayaUiReferenceMedia($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceMedia::setMediaUri
  * @covers PapayaUiReferenceMedia::get
  */
  public function testSetMediaUri() {
    $reference = new PapayaUiReferenceMedia($this->getUrlObjectMockFixture());
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
  * @covers PapayaUiReferenceMedia::setPreview
  * @covers PapayaUiReferenceMedia::get
  */
  public function testSetPreview() {
    $reference = new PapayaUiReferenceMedia($this->getUrlObjectMockFixture());
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
    $url = $this->getMock('PapayaUrl');
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
