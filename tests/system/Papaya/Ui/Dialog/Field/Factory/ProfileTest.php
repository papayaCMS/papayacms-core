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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileTest extends PapayaTestCase {

  /**
   * @covers \PapayaUiDialogFieldFactoryProfile::options
   */
  public function testOptionsGetAfterSet() {
    $profile = new \PapayaUiDialogFieldFactoryProfile_TestProxy();
    $profile->options($options = $this->createMock(PapayaUiDialogFieldFactoryOptions::class));
    $this->assertSame(
      $options,
      $profile->options()
    );
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryProfile::options
   */
  public function testOptionsGetImplicitCreate() {
    $profile = new \PapayaUiDialogFieldFactoryProfile_TestProxy();
    $this->assertInstanceOf(
      \PapayaUiDialogFieldFactoryOptions::class,
      $profile->options()
    );
  }

}

class PapayaUiDialogFieldFactoryProfile_TestProxy extends PapayaUiDialogFieldFactoryProfile {

  public function getField() {
  }
}
