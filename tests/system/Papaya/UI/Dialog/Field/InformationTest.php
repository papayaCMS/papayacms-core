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

namespace Papaya\UI\Dialog\Field;
require_once __DIR__.'/../../../../../bootstrap.php';

/**
 * @covers \Papaya\UI\Dialog\Field\Information
 */
class InformationTest extends \Papaya\TestFramework\TestCase {

  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $document->appendElement('sample');
    $message = new Information('Information', 'image');
    $message->papaya(
      $this->mockPapaya()->application(
        array(
          'images' => array('image' => 'image.png')
        )
      )
    );
    $message->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <field class="DialogFieldInformation" error="no">
          <message image="image.png">Information</message>
        </field>
      </sample>',
      $document->documentElement->saveXML()
    );
  }

  public function testAppendToWithoutImage() {
    $document = new \Papaya\XML\Document();
    $document->appendElement('sample');
    $message = new Information('Information');
    $message->papaya(
      $this->mockPapaya()->application(
        array(
          'images' => array('image' => 'image.png')
        )
      )
    );
    $message->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <field class="DialogFieldInformation" error="no">
          <message>Information</message>
        </field>
      </sample>',
      $document->documentElement->saveXML()
    );
  }

}
