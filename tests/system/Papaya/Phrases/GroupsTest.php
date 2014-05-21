<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaPhrasesGroupsTest extends PapayaTestCase {

  /**
   * @covers PapayaPhrasesGroups
   */
  public function testOffsetExistsExpectingFalse() {
    $phrases = $this
      ->getMockBuilder('PapayaPhrases')
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new PapayaPhrasesGroups($phrases);
    $this->assertFalse(isset($groups['example']));
  }

  /**
   * @covers PapayaPhrasesGroups
   */
  public function testOffsetExistsAfterOffsetSetExpectingTrue() {
    $phrases = $this
      ->getMockBuilder('PapayaPhrases')
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new PapayaPhrasesGroups($phrases);
    $groups['example'] = $this
      ->getMockBuilder('PapayaPhrasesGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertTrue(isset($groups['example']));
  }

  /**
   * @covers PapayaPhrasesGroups
   */
  public function testOffsetExistsAfterOffsetGetExpectingTrue() {
    $phrases = $this
      ->getMockBuilder('PapayaPhrases')
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new PapayaPhrasesGroups($phrases);
    $group = $groups['example'];
    $this->assertTrue(isset($groups['example']));
  }

  /**
   * @covers PapayaPhrasesGroups
   */
  public function testOffsetGetAfterOffsetSet() {
    $phrases = $this
      ->getMockBuilder('PapayaPhrases')
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new PapayaPhrasesGroups($phrases);
    $groups['example'] = $group = $this
      ->getMockBuilder('PapayaPhrasesGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertSame($group, $groups['example']);
  }

  /**
   * @covers PapayaPhrasesGroups
   */
  public function testGetAfterOffsetSet() {
    $phrases = $this
      ->getMockBuilder('PapayaPhrases')
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new PapayaPhrasesGroups($phrases);
    $groups['example'] = $group = $this
      ->getMockBuilder('PapayaPhrasesGroup')
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertSame($group, $groups->get('example'));
  }

  /**
   * @covers PapayaPhrasesGroups
   */
  public function testOffsetGetLazyCreate() {
    $phrases = $this
      ->getMockBuilder('PapayaPhrases')
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new PapayaPhrasesGroups($phrases);
    $this->assertInstanceOf('PapayaPhrasesGroup', $group = $groups['example']);
    $this->assertSame($group, $groups['example']);
  }

  /**
   * @covers PapayaPhrasesGroups
   */
  public function testOffsetUnset() {
    $phrases = $this
      ->getMockBuilder('PapayaPhrases')
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new PapayaPhrasesGroups($phrases);
    $groups['example'] = $this
      ->getMockBuilder('PapayaPhrasesGroup')
      ->disableOriginalConstructor()
      ->getMock();
    unset($groups['example']);
    $this->assertFalse(isset($groups['example']));
  }

  /**
   * @covers PapayaPhrasesGroups
   */
  public function testOffsetUnsetOnNonExistingGroup() {
    $phrases = $this
      ->getMockBuilder('PapayaPhrases')
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new PapayaPhrasesGroups($phrases);
    unset($groups['example']);
    $this->assertFalse(isset($groups['example']));
  }


}