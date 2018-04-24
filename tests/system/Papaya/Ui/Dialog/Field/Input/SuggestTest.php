<?php
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
    $filter = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $input = new PapayaUiDialogFieldInputSuggest('Caption', 'name', 'www.example.com', '50670', $filter);
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
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $input = new PapayaUiDialogFieldInputSuggest('Caption', 'name', 'www.example.com');
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(PapayaUiDialogFields::class));
    $input->appendTo($node);
    $this->assertEquals(
      '<sample>'.
        '<field caption="Caption" class="DialogFieldInputSuggest" error="no">'.
          '<input type="suggest" name="name" maxlength="1024" data-suggest="{&quot;url&quot;:&quot;www.example.com&quot;,&quot;limit&quot;:10}"/>'.
        '</field>'.
      '</sample>',
      $dom->saveXml($node)
    );
  }

  /**
  * @covers PapayaUiDialogFieldInputSuggest::appendTo
  */
  public function testAppendToWithDefaultValue() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $input = new PapayaUiDialogFieldInputSuggest('Caption', 'name', 'www.example.com');
    $request = $this->getMockRequestObject();
    $application = $this->getMockApplicationObject(array('request' => $request));
    $input->papaya($application);
    $input->collection($this->createMock(PapayaUiDialogFields::class));
    $input->setDefaultValue(50670);
    $input->appendTo($node);
    $this->assertEquals(
      '<sample>'.
        '<field caption="Caption" class="DialogFieldInputSuggest" error="no">'.
          '<input type="suggest" name="name" maxlength="1024" data-suggest="{&quot;url&quot;:&quot;www.example.com&quot;,&quot;limit&quot;:10}">50670</input>'.
        '</field>'.
      '</sample>',
      $dom->saveXml($node)
    );
  }
}
