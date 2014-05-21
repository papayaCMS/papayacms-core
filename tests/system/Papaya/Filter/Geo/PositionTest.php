<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterGeoPositionTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterGeoPosition
   */
  public function testFilter() {
    $filter = new PapayaFilterGeoPosition();
    $this->assertTrue($filter->validate('50.94794501585774, 6.944365873932838'));
  }
}