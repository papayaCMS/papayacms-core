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

class PapayaUiDialogFieldFactoryProfileRichtextSimpleTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Ui\Dialog\Field\Factory\Profile\RichtextSimple::getField
   */
  public function testGetField() {
    $options = new \Papaya\Ui\Dialog\Field\Factory\Options(
      array(
        'name' => 'rtefield',
        'caption' => 'Richtext',
        'default' => 'some value'
      )
    );
    $profile = new \Papaya\Ui\Dialog\Field\Factory\Profile\RichtextSimple();
    $profile->options($options);
    $this->assertInstanceOf(
      \Papaya\Ui\Dialog\Field\Textarea\Richtext::class, $field = $profile->getField()
    );
    $this->assertEquals(
      \Papaya\Ui\Dialog\Field\Textarea\Richtext::RTE_SIMPLE,
      $field->getRteMode()
    );
  }
}
