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
 * @covers \Papaya\UI\Dialog\Field\Textarea
 */
class TextareaTest extends \Papaya\TestFramework\TestCase {

  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $textarea = new Textarea('Caption', 'name');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $textarea->papaya($application);
    $textarea->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $textarea->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <field caption="Caption" class="DialogFieldTextarea" error="no">
        <textarea type="text" name="name" lines="10"/>
        </field>
        </sample>',
      $document->saveXML($node)
    );
  }

  public function testAppendToWithDefaultValue() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $textarea = new Textarea('Caption', 'name');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $textarea->papaya($application);
    $textarea->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $textarea->setDefaultValue(50670);
    $textarea->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <field caption="Caption" class="DialogFieldTextarea" error="no">
        <textarea type="text" name="name" lines="10">50670</textarea>
        </field>
        </sample>',
      $document->saveXML($node)
    );
  }
}
