<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaDatabaseSequenceHumanTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseSequenceHuman::__construct
  */
  public function testConstructor() {
    $sequence = new PapayaDatabaseSequenceHuman('table', 'field');
    $this->assertAttributeEquals(
      10, '_length', $sequence
    );
  }

  /**
  * @covers PapayaDatabaseSequenceHuman::__construct
  */
  public function testConstructorWithByteLength() {
    $sequence = new PapayaDatabaseSequenceHuman('table', 'field', 42);
    $this->assertAttributeEquals(
      42, '_length', $sequence
    );
  }

  /**
  * @covers PapayaDatabaseSequenceHuman::create
  * @covers PapayaDatabaseSequenceHuman::getRandomCharacters
  */
  public function testCreate5Bytes() {
    $sequence = new PapayaDatabaseSequenceHuman('table', 'field', 5);
    $this->assertRegExp(
      '(^[a-z2-7]{5}$)D', $sequence->create()
    );
  }

  /**
  * @covers PapayaDatabaseSequenceHuman::create
  */
  public function testCreate7Bytes() {
    $sequence = new PapayaDatabaseSequenceHuman('table', 'field');
    $this->assertRegExp(
      '(^[a-z2-7]{10}$)', $sequence->create()
    );
  }

  /**
  * @covers PapayaDatabaseSequenceHuman::create
  */
  public function testCreateIsRandom() {
    $sequence = new PapayaDatabaseSequenceHuman('table', 'field');
    $idOne = $sequence->create();
    $idTwo = $sequence->create();
    $this->assertNotEquals(
      $idOne, $idTwo
    );
  }
}