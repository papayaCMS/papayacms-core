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

require_once __DIR__.'/../../../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileInputPasswordTest extends PapayaTestCase {

  /**
   * @covers \PapayaUiDialogFieldFactoryProfileInputPassword::getField
   */
  public function testGetField() {
    $options = new \PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value'
      )
    );
    $profile = new \PapayaUiDialogFieldFactoryProfileInputPassword();
    $profile->options($options);
    $this->assertInstanceOf(PapayaUiDialogFieldInputPassword::class, $field = $profile->getField());
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryProfileInputPassword::getField
   */
  public function testGetFieldWithHint() {
    $options = new \PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value',
        'hint' => 'Some hint text'
      )
    );
    $profile = new \PapayaUiDialogFieldFactoryProfileInputPassword();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }
}
