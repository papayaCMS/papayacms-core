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

require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileTextareaTest extends PapayaTestCase {

  /**
   * @covers \PapayaUiDialogFieldFactoryProfileTextarea::getField
   */
  public function testGetField() {
    $options = new \PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'textareafield',
        'caption' => 'Input',
        'default' => 'some value'
      )
    );
    $profile = new \PapayaUiDialogFieldFactoryProfileTextarea();
    $profile->options($options);
    $this->assertInstanceOf(PapayaUiDialogFieldTextarea::class, $field = $profile->getField());
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryProfileTextarea::getField
   */
  public function testGetFieldDisabled() {
    $options = new \PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'textareafield',
        'caption' => 'Input',
        'default' => 'some value',
        'disabled' => TRUE
      )
    );
    $profile = new \PapayaUiDialogFieldFactoryProfileTextarea();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertTrue($field->getDisabled());
  }

  /**
   * @covers \PapayaUiDialogFieldFactoryProfileTextarea::getField
   */
  public function testGetFieldWithHint() {
    $options = new \PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'textareafield',
        'caption' => 'Input',
        'default' => 'some value',
        'hint' => 'Some hint text'
      )
    );
    $profile = new \PapayaUiDialogFieldFactoryProfileTextarea();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }
}
