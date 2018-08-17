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

class RichtextIndividualTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Factory\Profile\RichtextIndividual::getField
   */
  public function testGetField() {
    $options = new \Papaya\UI\Dialog\Field\Factory\Options(
      array(
        'name' => 'rtefield',
        'caption' => 'Richtext',
        'default' => 'some value'
      )
    );
    $profile = new RichtextIndividual();
    $profile->options($options);
    $this->assertInstanceOf(
      \Papaya\UI\Dialog\Field\Textarea\Richtext::class, $field = $profile->getField()
    );
    $this->assertEquals(
      \Papaya\UI\Dialog\Field\Textarea\Richtext::RTE_INDIVIDUAL,
      $field->getRteMode()
    );
  }
}
