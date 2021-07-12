<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\UI\Dialog\Field\Textarea {

  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\UI\Dialog\Field\Textarea\Lines
   */
  class LinesTest extends TestCase {

    public function testAppendTo() {
      $lines = new Lines('a caption', 'a-name');
      $this->assertXmlStringEqualsXmlString(
        '<?xml version="1.0"?>
        <field caption="a caption" class="DialogFieldTextareaLines" error="no">
          <textarea lines="10" name="a-name" type="lines"/>
        </field>',
        $lines->getXML()
      );
    }
  }

}
