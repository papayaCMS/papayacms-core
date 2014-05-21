<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaUiListviewSubitemImageListTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewSubitemImageList::__construct
  */
  public function testConstructor() {
    $icons = $this->getMock('PapayaUiIconList');
    $subitem = new PapayaUiListviewSubitemImageList(
      $icons, 'foo', PapayaUiListviewSubitemImageList::VALIDATE_BITMASK
    );
    $this->assertEquals(
      PapayaUiListviewSubitemImageList::VALIDATE_BITMASK, $subitem->selectionMode
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImageList::appendTo
  * @covers PapayaUiListviewSubitemImageList::validateSelection
  */
  public function testAppendToUseValues() {
    $iconValid = $this
      ->getMockBuilder('PapayaUiIcon')
      ->disableOriginalConstructor()
      ->getMock();
    $iconValid
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $iconInvalid = $this
      ->getMockBuilder('PapayaUiIcon')
      ->disableOriginalConstructor()
      ->getMock();
    $iconInvalid
      ->expects($this->once())
      ->method('__set')
      ->with('visible', FALSE);
    $iconInvalid
      ->expects($this->once())
      ->method('appendTo')
      ->withAnyParameters();
    $icons = $this->getMock(
      'PapayaUiIconList', array('getIterator'));
    $icons
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              'foo' => $iconValid,
              'bar' => $iconInvalid
            )
          )
        )
      );

    $dom = new PapayaXmlDocument();
    $subitem = new PapayaUiListviewSubitemImageList($icons, array('foo'));
    $subitem->icons = $icons;
    $subitem->appendTo($dom->appendElement('sample'));
    $this->assertEquals(
      '<sample><subitem align="left"><glyphs/></subitem></sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImageList::appendTo
  * @covers PapayaUiListviewSubitemImageList::validateSelection
  */
  public function testAppendToUseKeys() {
    $iconValid = $this
      ->getMockBuilder('PapayaUiIcon')
      ->disableOriginalConstructor()
      ->getMock();
    $iconValid
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $iconInvalid = $this
      ->getMockBuilder('PapayaUiIcon')
      ->disableOriginalConstructor()
      ->getMock();
    $iconInvalid
      ->expects($this->once())
      ->method('__set')
      ->with('visible', FALSE);
    $iconInvalid
      ->expects($this->once())
      ->method('appendTo')
      ->withAnyParameters();
    $icons = $this->getMock(
      'PapayaUiIconList', array('getIterator'));
    $icons
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              'foo' => $iconValid,
              'bar' => $iconInvalid
            )
          )
        )
      );

    $dom = new PapayaXmlDocument();
    $subitem = new PapayaUiListviewSubitemImageList(
      $icons,
      array('foo' => TRUE),
      PapayaUiListviewSubitemImageList::VALIDATE_KEYS
    );
    $subitem->icons = $icons;
    $subitem->appendTo($dom->appendElement('sample'));
    $this->assertEquals(
      '<sample><subitem align="left"><glyphs/></subitem></sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImageList::appendTo
  * @covers PapayaUiListviewSubitemImageList::validateSelection
  */
  public function testAppendToUseBitmask() {
    $iconValid = $this
      ->getMockBuilder('PapayaUiIcon')
      ->disableOriginalConstructor()
      ->getMock();
    $iconValid
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $iconInvalid = $this
      ->getMockBuilder('PapayaUiIcon')
      ->disableOriginalConstructor()
      ->getMock();
    $iconInvalid
      ->expects($this->once())
      ->method('__set')
      ->with('visible', FALSE);
    $iconInvalid
      ->expects($this->once())
      ->method('appendTo')
      ->withAnyParameters();
    $icons = $this->getMock(
      'PapayaUiIconList', array('getIterator'));
    $icons
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              1 => $iconValid,
              2 => $iconInvalid
            )
          )
        )
      );

    $dom = new PapayaXmlDocument();
    $subitem = new PapayaUiListviewSubitemImageList(
      $icons,
      5,
      PapayaUiListviewSubitemImageList::VALIDATE_BITMASK
    );
    $subitem->icons = $icons;
    $subitem->appendTo($dom->appendElement('sample'));
    $this->assertEquals(
      '<sample><subitem align="left"><glyphs/></subitem></sample>',
      $dom->saveXml($dom->documentElement)
    );
  }
}