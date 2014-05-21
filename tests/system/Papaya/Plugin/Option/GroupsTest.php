<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaPluginOptionGroupsTest extends PapayaTestCase {

  /**
  * @covers PapayaPluginOptionGroups::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $options = $this
      ->getMockBuilder('PapayaConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new PapayaPluginOptionGroups();
    $groups['123456789012345678901234567890ab'] = $options;
    $this->assertTrue(isset($groups['123456789012345678901234567890ab']));
  }

  /**
  * @covers PapayaPluginOptionGroups::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $options = $this
      ->getMockBuilder('PapayaConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new PapayaPluginOptionGroups();
    $groups['ef123456789012345678901234567890'] = $options;
    $this->assertTrue(isset($groups['ef123456789012345678901234567890']));
  }

  /**
  * @covers PapayaPluginOptionGroups::offsetGet
  * @covers PapayaPluginOptionGroups::offsetSet
  * @covers PapayaPluginOptionGroups::createLazy
  */
  public function testOffsetGetAfterOffsetSet() {
    $options = $this
      ->getMockBuilder('PapayaConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new PapayaPluginOptionGroups();
    $groups['ef123456789012345678901234567890'] = $options;
    $this->assertSame($options, $groups['ef123456789012345678901234567890']);
  }

  /**
  * @covers PapayaPluginOptionGroups::offsetGet
  * @covers PapayaPluginOptionGroups::createLazy
  */
  public function testOffsetGetImplicitCreate() {
    $groups = new PapayaPluginOptionGroups();
    $this->assertInstanceOf(
      'PapayaConfiguration',
      $groups['123456789012345678901234567890ab']
    );
  }

  /**
  * @covers PapayaPluginOptionGroups::offsetUnset
  */
  public function testOffsetUnset() {
    $options = $this
      ->getMockBuilder('PapayaConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new PapayaPluginOptionGroups();
    $groups['ef123456789012345678901234567890'] = $options;
    unset($groups['ef123456789012345678901234567890']);
    $this->assertFalse(isset($groups['ef123456789012345678901234567890']));
  }

}