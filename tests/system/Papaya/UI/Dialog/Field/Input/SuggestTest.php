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

namespace Papaya\UI\Dialog\Field\Input;
require_once __DIR__.'/../../../../../../bootstrap.php';

/**
 * @covers \Papaya\UI\Dialog\Field\Input\Suggest
 */
class SuggestTest extends \Papaya\TestFramework\TestCase {

  public function testGetTypeAfterSetType() {
    $input = new Suggest('Caption', 'name', 'www.example.com');
    $input->setType('suggest');
    $this->assertEquals('suggest', $input->getType());
  }

  public function testGetSuggestionUrlAfterSetSuggestionUrl() {
    $url = 'www.example.com';
    $input = new Suggest('Caption', 'name', $url);
    $input->setSuggestionURL($url);
    $this->assertEquals($url, $input->getSuggestionURL());
  }

  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $input = new Suggest('Caption', 'name', 'www.example.com');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $input->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <field caption="Caption" class="DialogFieldInputSuggest" error="no">
          <input type="suggest" name="name" maxlength="1024" data-suggest="{&quot;url&quot;:&quot;www.example.com&quot;,&quot;limit&quot;:10}"/>
        </field>
      </sample>',
      $document->saveXML($node)
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Suggest::appendTo
   */
  public function testAppendToWithDefaultValue() {
    $document = new \Papaya\XML\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $input = new Suggest('Caption', 'name', 'www.example.com');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(\Papaya\UI\Dialog\Fields::class));
    $input->setDefaultValue(50670);
    $input->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <field caption="Caption" class="DialogFieldInputSuggest" error="no">
          <input type="suggest" name="name" maxlength="1024" data-suggest="{&quot;url&quot;:&quot;www.example.com&quot;,&quot;limit&quot;:10}">50670</input>
        </field>
      </sample>',
      $document->saveXML($node)
    );
  }
}
