<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaPluginOptionGroupsTest extends \PapayaTestCase {

  /**
  * @covers \PapayaPluginOptionGroups::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $options = $this
      ->getMockBuilder(\Papaya\Configuration::class)
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new \PapayaPluginOptionGroups();
    $groups['123456789012345678901234567890ab'] = $options;
    $this->assertTrue(isset($groups['123456789012345678901234567890ab']));
  }

  /**
  * @covers \PapayaPluginOptionGroups::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $options = $this
      ->getMockBuilder(\Papaya\Configuration::class)
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new \PapayaPluginOptionGroups();
    $groups['ef123456789012345678901234567890'] = $options;
    $this->assertTrue(isset($groups['ef123456789012345678901234567890']));
  }

  /**
  * @covers \PapayaPluginOptionGroups::offsetGet
  * @covers \PapayaPluginOptionGroups::offsetSet
  * @covers \PapayaPluginOptionGroups::createLazy
  */
  public function testOffsetGetAfterOffsetSet() {
    $options = $this
      ->getMockBuilder(\Papaya\Configuration::class)
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new \PapayaPluginOptionGroups();
    $groups['ef123456789012345678901234567890'] = $options;
    $this->assertSame($options, $groups['ef123456789012345678901234567890']);
  }

  /**
  * @covers \PapayaPluginOptionGroups::offsetGet
  * @covers \PapayaPluginOptionGroups::createLazy
  */
  public function testOffsetGetImplicitCreate() {
    $groups = new \PapayaPluginOptionGroups();
    $this->assertInstanceOf(
      \Papaya\Configuration::class,
      $groups['123456789012345678901234567890ab']
    );
  }

  /**
  * @covers \PapayaPluginOptionGroups::offsetUnset
  */
  public function testOffsetUnset() {
    $options = $this
      ->getMockBuilder(\Papaya\Configuration::class)
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new \PapayaPluginOptionGroups();
    $groups['ef123456789012345678901234567890'] = $options;
    unset($groups['ef123456789012345678901234567890']);
    $this->assertFalse(isset($groups['ef123456789012345678901234567890']));
  }

}
