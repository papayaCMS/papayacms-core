<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseSequenceMd5Test extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseSequenceMd5::create
  */
  public function testCreate7Bytes() {
    $sequence = new PapayaDatabaseSequenceMd5('table', 'field');
    $this->assertRegExp(
      '(^[a-f\d]{32}$)D', $sequence->create()
    );
  }

  /**
  * @covers PapayaDatabaseSequenceMd5::create
  */
  public function testCreateIsRandom() {
    $sequence = new PapayaDatabaseSequenceMd5('table', 'field');
    $idOne = $sequence->create();
    $idTwo = $sequence->create();
    $this->assertNotEquals(
      $idOne, $idTwo
    );
  }
}
