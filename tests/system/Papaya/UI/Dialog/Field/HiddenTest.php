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
 * @covers \Papaya\UI\Dialog\Field\Hidden
 */
class HiddenTest extends \Papaya\TestCase {

  public function testAppendToWithDefaultValue() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $input = new Hidden('name', 'default');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $input->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <field class="DialogFieldHidden">
        <input type="hidden" name="name">default</input>
        </field>
        </sample>',
      $document->saveXML($node)
    );
  }

  public function testAppendToWithId() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');

    $input = new Hidden('name', 'default');
    $input->setId('id');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $input->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <field class="DialogFieldHidden" id="id">
        <input type="hidden" name="name">default</input>
        </field>
        </sample>',
      $document->saveXML($node)
    );
  }
}
