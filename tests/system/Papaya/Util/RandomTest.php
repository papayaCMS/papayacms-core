<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaUtilRandomTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilRandom::rand
  */
  public function testRand() {
    $random = PapayaUtilRandom::rand();
    $this->assertGreaterThanOrEqual(0, $random);
  }

  /**
  * @covers PapayaUtilRandom::rand
  */
  public function testRandWithLimits() {
    $random = PapayaUtilRandom::rand(1, 1);
    $this->assertGreaterThanOrEqual(1, $random);
  }

  /**
  * @covers PapayaUtilRandom::getId
  */
  public function testGetId() {
    $idOne = PapayaUtilRandom::getId();
    $idTwo = PapayaUtilRandom::getId();
    $this->assertNotEquals($idOne, $idTwo);
  }
}