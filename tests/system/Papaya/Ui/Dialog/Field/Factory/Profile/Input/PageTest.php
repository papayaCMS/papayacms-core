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

class PapayaUiDialogFieldFactoryProfileInputPageTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputPage::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputPage();
    $profile->options($options);
    $this->assertInstanceOf(PapayaUiDialogFieldInputPage::class, $field = $profile->getField());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputPage::getField
   */
  public function testGetFieldWithHint() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value',
        'hint' => 'Some hint text'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputPage();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfileInputPage
   * @dataProvider provideValidPageInputs
   * @param string $value
   * @throws PapayaUiDialogFieldFactoryExceptionInvalidOption
   */
  public function testValidateDifferentInputs($value) {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => $value
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileInputPage();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertTrue($field->validate());
  }

  public static function provideValidPageInputs() {
    return array(
      array('42'),
      array('42,21'),
      array('foo'),
      array('http://foobar.tld/')
    );
  }
}
