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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldInputSuggestTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldInputSuggest::__construct
  */
  public function testConstructor() {
    $input = new PapayaUiDialogFieldInputSuggest('Caption', 'name', 'www.example.com');
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
  * @covers PapayaUiDialogFieldInputSuggest::__construct
  */
  public function testConstructorWithAllParameters() {
    $filter = $this->createMock(PapayaFilter::class);
    $input = new PapayaUiDialogFieldInputSuggest(
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
  * @covers PapayaUiDialogFieldInputSuggest::setType
  * @covers PapayaUiDialogFieldInputSuggest::getType
  */
  public function testGetTypeAfterSetType() {
    $input = new PapayaUiDialogFieldInputSuggest('Caption', 'name', 'www.example.com');
    $input->setType('suggest');
    $this->assertEquals('suggest', $input->getType());
  }

  /**
   * @covers PapayaUiDialogFieldInputSuggest::setSuggestionUrl
   * @covers PapayaUiDialogFieldInputSuggest::getSuggestionUrl
   */
  public function testGetSuggestionUrlAfterSetSuggestionUrl() {
    $url = 'www.example.com';
    $input = new PapayaUiDialogFieldInputSuggest('Caption', 'name', $url);
    $input->setSuggestionUrl($url);
    $this->assertEquals($url, $input->getSuggestionUrl());
  }

  /**
  * @covers PapayaUiDialogFieldInputSuggest::appendTo
  */
  public function testAppendTo() {
    $document = new PapayaXmlDocument();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $input = new PapayaUiDialogFieldInputSuggest('Caption', 'name', 'www.example.com');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(PapayaUiDialogFields::class));
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
  * @covers PapayaUiDialogFieldInputSuggest::appendTo
  */
  public function testAppendToWithDefaultValue() {
    $document = new PapayaXmlDocument();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $input = new PapayaUiDialogFieldInputSuggest('Caption', 'name', 'www.example.com');
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(PapayaUiDialogFields::class));
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
