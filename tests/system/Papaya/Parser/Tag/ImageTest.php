<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaParserTagImageTest extends PapayaTestCase {
  /**
   * @covers PapayaParserTagImage::appendTo
   */
  public function testAppendTo() {
    $image = new PapayaParserTagImage('d74f6d0324f5d90b23bb3771200ddf7d,60,96,max');
    $document = new PapayaXmlDocument();
    $container = $document->appendElement('container');
    $image->appendTo($container);
    $this->assertEquals(
      '<container><papaya:media xmlns:papaya="http://www.papaya-cms.com/namespace/papaya"'
      .' src="d74f6d0324f5d90b23bb3771200ddf7d"'
      .' width="60" height="96" resize="max"></papaya:media></container>
',
      $document->saveHTML()
    );
  }
}
