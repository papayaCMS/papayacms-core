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

namespace Papaya\CMS\Plugin\Option;
require_once __DIR__.'/../../../../../bootstrap.php';

class GroupsTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\Plugin\Option\Groups::offsetExists
   */
  public function testOffsetExistsExpectingTrue() {
    $options = $this
      ->getMockBuilder(\Papaya\Configuration::class)
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new Groups();
    $groups['123456789012345678901234567890ab'] = $options;
    $this->assertTrue(isset($groups['123456789012345678901234567890ab']));
  }

  /**
   * @covers \Papaya\CMS\Plugin\Option\Groups::offsetExists
   */
  public function testOffsetExistsExpectingFalse() {
    $options = $this
      ->getMockBuilder(\Papaya\Configuration::class)
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new Groups();
    $groups['ef123456789012345678901234567890'] = $options;
    $this->assertTrue(isset($groups['ef123456789012345678901234567890']));
  }

  /**
   * @covers \Papaya\CMS\Plugin\Option\Groups::offsetGet
   * @covers \Papaya\CMS\Plugin\Option\Groups::offsetSet
   * @covers \Papaya\CMS\Plugin\Option\Groups::createLazy
   */
  public function testOffsetGetAfterOffsetSet() {
    $options = $this
      ->getMockBuilder(\Papaya\Configuration::class)
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new Groups();
    $groups['ef123456789012345678901234567890'] = $options;
    $this->assertSame($options, $groups['ef123456789012345678901234567890']);
  }

  /**
   * @covers \Papaya\CMS\Plugin\Option\Groups::offsetGet
   * @covers \Papaya\CMS\Plugin\Option\Groups::createLazy
   */
  public function testOffsetGetImplicitCreate() {
    $groups = new Groups();
    $this->assertInstanceOf(
      \Papaya\Configuration::class,
      $groups['123456789012345678901234567890ab']
    );
  }

  /**
   * @covers \Papaya\CMS\Plugin\Option\Groups::offsetUnset
   */
  public function testOffsetUnset() {
    $options = $this
      ->getMockBuilder(\Papaya\Configuration::class)
      ->disableOriginalConstructor()
      ->getMock();
    $groups = new Groups();
    $groups['ef123456789012345678901234567890'] = $options;
    unset($groups['ef123456789012345678901234567890']);
    $this->assertFalse(isset($groups['ef123456789012345678901234567890']));
  }

}
