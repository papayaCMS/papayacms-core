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

class InputSuggestTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Profile\InputSuggest::getField
   */
  public function testGetField() {
    $options = new \Papaya\UI\Dialog\Field\Factory\Options(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value',
        'parameters' => 'suggest.url'
      )
    );
    $profile = new InputSuggest();
    $profile->options($options);
    $this->assertInstanceOf(\Papaya\UI\Dialog\Field\Input\Suggest::class, $field = $profile->getField());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Profile\InputSuggest::getField
   */
  public function testGetFieldWithHint() {
    $options = new \Papaya\UI\Dialog\Field\Factory\Options(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value',
        'parameters' => 'suggest.url',
        'hint' => 'Some hint text'
      )
    );
    $profile = new InputSuggest();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertSame('Some hint text', $field->getHint());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Profile\InputSuggest::getField
   */
  public function testGetFieldDisabled() {
    $options = new \Papaya\UI\Dialog\Field\Factory\Options(
      array(
        'name' => 'inputfield',
        'caption' => 'Input',
        'default' => 'some value',
        'parameters' => 'suggest.url',
        'hint' => 'Some hint text',
        'disabled' => TRUE
      )
    );
    $profile = new InputSuggest();
    $profile->options($options);
    $field = $profile->getField();
    $this->assertTrue($field->getDisabled());
  }
}
