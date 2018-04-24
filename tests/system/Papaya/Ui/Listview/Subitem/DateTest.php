<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiListviewSubitemDateTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewSubitemDate::__construct
  */
  public function testConstructor() {
    $now = time();
    $subitem = new PapayaUiListviewSubitemDate($now);
    $this->assertEquals(
      $now, $subitem->timestamp
    );
  }

  /**
  * @covers PapayaUiListviewSubitemDate::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $subitem = new PapayaUiListviewSubitemDate(strtotime('2011-05-18 12:13:45'));
    $subitem->align = PapayaUiOptionAlign::CENTER;
    $subitem->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><subitem align="center">2011-05-18 12:13</subitem></test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemDate::appendTo
  */
  public function testAppendToDateOnly() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $subitem = new PapayaUiListviewSubitemDate(
      strtotime('2011-05-18 12:13:45'),
      PapayaUiListviewSubitemDate::SHOW_DATE
    );
    $subitem->align = PapayaUiOptionAlign::CENTER;
    $subitem->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><subitem align="center">2011-05-18</subitem></test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemDate::appendTo
  */
  public function testAppendToWithSeconds() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $subitem = new PapayaUiListviewSubitemDate(
      strtotime('2011-05-18 12:13:45'),
      PapayaUiListviewSubitemDate::SHOW_TIME | PapayaUiListviewSubitemDate::SHOW_SECONDS
    );
    $subitem->align = PapayaUiOptionAlign::CENTER;
    $subitem->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><subitem align="center">2011-05-18 12:13:45</subitem></test>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemDate::appendTo
  */
  public function testAppendToHidesZero() {
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $subitem = new PapayaUiListviewSubitemDate(0);
    $subitem->appendTo($dom->documentElement);
    $this->assertEquals(
      '<test><subitem align="left"></subitem></test>',
      $dom->saveXml($dom->documentElement)
    );
  }

}
