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

namespace Papaya\UI\Dialog\Field\Textarea;
require_once __DIR__.'/../../../../../../bootstrap.php';

class RichtextTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Textarea\Richtext::__construct
   */
  public function testConstructorSettingRteMode() {
    $richtext = new Richtext(
      'Caption', 'name', 12, NULL, NULL, Richtext::RTE_SIMPLE
    );
    $this->assertEquals(
      Richtext::RTE_SIMPLE, $richtext->getRteMode()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Textarea\Richtext::appendTo
   */
  public function testAppendTo() {
    $richtext = new Richtext('Caption', 'name');
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="Caption" class="DialogFieldTextareaRichtext" error="no">
        <textarea type="text" name="name" lines="10" data-rte="standard"/>
      </field>',
      $richtext->getXML()
    );
  }


  /**
   * @covers \Papaya\UI\Dialog\Field\Textarea\Richtext::appendTo
   */
  public function testAppendToWithAllParameters() {
    $richtext = new Richtext(
      'Caption', 'name', 12, NULL, NULL, Richtext::RTE_SIMPLE
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="Caption" class="DialogFieldTextareaRichtext" error="no">
        <textarea type="text" name="name" lines="12" data-rte="simple"/>
      </field>',
      $richtext->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Textarea\Richtext::setRteMode
   * @covers \Papaya\UI\Dialog\Field\Textarea\Richtext::getRteMode
   */
  public function testGetRteModeAfterSetRteMode() {
    $richtext = new Richtext('Caption', 'name');
    $richtext->setRteMode(Richtext::RTE_SIMPLE);
    $this->assertEquals(
      Richtext::RTE_SIMPLE, $richtext->getRteMode()
    );
  }
}
