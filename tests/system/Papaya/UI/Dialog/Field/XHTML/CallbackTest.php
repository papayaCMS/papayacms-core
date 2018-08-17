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

namespace Papaya\UI\Dialog\Field\XHTML;
require_once __DIR__.'/../../../../../../bootstrap.php';

class CallbackTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\XHTML\Callback
   * @covers \Papaya\UI\Dialog\Field\Callback::appendTo
   */
  public function testAppendTo() {
    $xhtml = new Callback(
      'Caption', 'name', array($this, 'callbackGetFieldString')
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<field caption="Caption" class="DialogFieldXHTMLCallback" error="no">
        <xhtml><select/></xhtml>
      </field>',
      $xhtml->getXML()
    );
  }

  public function callbackGetFieldString() {
    return /** @lang XML */
      '<select/>';
  }

}
