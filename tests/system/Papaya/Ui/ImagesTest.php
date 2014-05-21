<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaUiImagesTest extends PapayaTestCase {

  /**
  * @covers PapayaUiImages::__construct
  */
  public function testConstructorAddsImages() {
    $images = new PapayaUiImages(array('test' => 'test.png'));
    $this->assertAttributeEquals(
      array('test' => 'test.png'),
      '_images',
      $images
    );

  }

  /**
  * @covers PapayaUiImages::add
  */
  public function testAdd() {
    $images = new PapayaUiImages();
    $images->add(array('test' => 'test.png'));
    $this->assertAttributeEquals(
      array('test' => 'test.png'),
      '_images',
      $images
    );
  }

  /**
  * @covers PapayaUiImages::add
  */
  public function testAddIgnoreExisting() {
    $images = new PapayaUiImages();
    $images->add(array('test' => 'success.png'));
    $images->add(array('test' => 'fail.png'), PapayaUiImages::DUPLICATES_IGNORE);
    $this->assertAttributeEquals(
      array('test' => 'success.png'),
      '_images',
      $images
    );
  }

  /**
  * @covers PapayaUiImages::add
  */
  public function testAddOverwriteExisting() {
    $images = new PapayaUiImages();
    $images->add(array('test' => 'fail.png'));
    $images->add(array('test' => 'success.png'), PapayaUiImages::DUPLICATES_OVERWRITE);
    $this->assertAttributeEquals(
      array('test' => 'success.png'),
      '_images',
      $images
    );
  }

  /**
  * @covers PapayaUiImages::remove
  */
  public function testRemove() {
    $images = new PapayaUiImages();
    $images->add(array('test' => 'test.png'));
    $images->remove(array('test'));
    $this->assertAttributeEquals(
      array(),
      '_images',
      $images
    );
  }

  /**
  * @covers PapayaUiImages::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $images = new PapayaUiImages();
    $images->add(array('test' => 'test.png'));
    $this->assertTrue(isset($images['test']));
  }

  /**
  * @covers PapayaUiImages::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $images = new PapayaUiImages();
    $this->assertFalse(isset($images['test']));
  }

  /**
  * @covers PapayaUiImages::offsetGet
  */
  public function testOffsetGet() {
    $images = new PapayaUiImages();
    $images->add(array('test' => 'test.png'));
    $this->assertEquals('test.png', $images['test']);
  }

  /**
  * @covers PapayaUiImages::offsetGet
  */
  public function testOffsetGetWithNonExistingOffsetExpectingGivenOffset() {
    $images = new PapayaUiImages();
    $this->assertSame('test', $images['test']);
  }

  /**
  * @covers PapayaUiImages::offsetSet
  */
  public function testOffetSet() {
    $images = new PapayaUiImages();
    $images->add(array('test' => 'fail.png'));
    $images['test'] = 'success.png';
    $this->assertAttributeEquals(
      array('test' => 'success.png'),
      '_images',
      $images
    );
  }

  /**
  * @covers PapayaUiImages::offsetUnset
  */
  public function testOffsetUnset() {
    $images = new PapayaUiImages();
    $images->add(array('test' => 'test.png'));
    unset($images['test']);
    $this->assertAttributeEquals(
      array(),
      '_images',
      $images
    );
  }
}