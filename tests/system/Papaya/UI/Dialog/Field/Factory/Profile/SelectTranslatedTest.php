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

namespace Papaya\UI\Dialog\Field\Factory\Profile;
require_once __DIR__.'/../../../../../../../bootstrap.php';

class SelectTranslatedTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Profile\SelectTranslated::createField
   */
  public function testGetField() {
    $options = new \Papaya\UI\Dialog\Field\Factory\Options(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 0,
        'parameters' => array('foo', 'bar')
      )
    );
    $profile = new SelectTranslated();
    $profile->options($options);
    $this->assertInstanceOf(\Papaya\UI\Dialog\Field\Select::class, $field = $profile->getField());
    $this->assertInstanceOf(\Papaya\UI\Text\Translated\Collection::class, $field->getValues());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Profile\SelectTranslated::createField
   */
  public function testGetFieldEmptyElementsList() {
    $options = new \Papaya\UI\Dialog\Field\Factory\Options(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 0,
        'parameters' => NULL
      )
    );
    $profile = new SelectTranslated();
    $profile->options($options);
    $this->assertInstanceOf(\Papaya\UI\Dialog\Field\Select::class, $field = $profile->getField());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Profile\SelectTranslated::createField
   */
  public function testGetFieldWithHint() {
    $options = new \Papaya\UI\Dialog\Field\Factory\Options(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 0,
        'hint' => 'Some hint text',
        'parameters' => array('foo', 'bar')
      )
    );
    $profile = new SelectTranslated();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }
}
