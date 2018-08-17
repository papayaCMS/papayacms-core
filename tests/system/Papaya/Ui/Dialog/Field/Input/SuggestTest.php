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

class SuggestTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Suggest::__construct
   */
  public function testConstructor() {
    $input = new Suggest('Caption', 'name', 'www.example.com');
    $suggestionData = array(
      'url' => 'www.example.com',
      'limit' => 10
    );
    $this->assertAttributeEquals(
      'Caption', '_caption', $input
    );
    $this->assertAttributeEquals(
      'name', '_name', $input
    );
    $this->assertAttributeEquals(
      $suggestionData, '_suggestionData', $input
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Suggest::__construct
   */
  public function testConstructorWithAllParameters() {
    $filter = $this->createMock(\Papaya\Filter::class);
    $input = new Suggest(
      'Caption', 'name', 'www.example.com', '50670', $filter
    );
    $this->assertAttributeEquals(
      '50670', '_defaultValue', $input
    );
    $this->assertAttributeSame(
      $filter, '_filter', $input
    );
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Suggest::setType
   * @covers \Papaya\UI\Dialog\Field\Input\Suggest::getType
   */
  public function testGetTypeAfterSetType() {
    $input = new Suggest('Caption', 'name', 'www.example.com');
    $input->setType('suggest');
    $this->assertEquals('suggest', $input->getType());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Suggest::setSuggestionURL
   * @covers \Papaya\UI\Dialog\Field\Input\Suggest::getSuggestionURL
   */
  public function testGetSuggestionUrlAfterSetSuggestionUrl() {
    $url = 'www.example.com';
    $input = new Suggest('Caption', 'name', $url);
    $input->setSuggestionURL($url);
    $this->assertEquals($url, $input->getSuggestionURL());
  }

  /**
   * @covers \Papaya\UI\Dialog\Field\Input\Suggest::appendTo
   */
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
