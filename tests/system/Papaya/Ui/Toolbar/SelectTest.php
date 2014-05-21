<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiToolbarSelectTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarSelect::__construct
  * @covers PapayaUiToolbarSelect::options
  */
  public function testConstructorSettingOptions() {
    $select = new PapayaUiToolbarSelect('foo', array('foo' => 'bar'));
    $this->assertAttributeEquals(
      'foo', '_parameterName', $select
    );
    $this->assertAttributeEquals(
      array('foo' => 'bar'), '_options', $select
    );
  }

  /**
  * @covers PapayaUiToolbarSelect::__construct
  * @covers PapayaUiToolbarSelect::options
  */
  public function testOptionsExpectingException() {
    try {
      $select = new PapayaUiToolbarSelect('foo', 'failed');
    } catch (InvalidArgumentException $e) {
      $this->assertEquals(
        'Argument $options must be an array or implement Traversable.',
        $e->getMessage()
      );
    }
  }

  /**
  * @covers PapayaUiToolbarSelect::getCurrentValue
  * @covers PapayaUiToolbarSelect::validateCurrentValue
  */
  public function testGetCurrentValue() {
    $select = new PapayaUiToolbarSelect('foo', array(23 => 'bar'));
    $select->defaultValue = 21;
    $select->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $this->mockPapaya()->request(array('foo' => '23'), 'http://www.test.tld')
        )
      )
    );
    $this->assertSame(
      23, $select->getCurrentValue()
    );
  }

  /**
  * @covers PapayaUiToolbarSelect::getCurrentValue
  * @covers PapayaUiToolbarSelect::validateCurrentValue
  */
  public function testGetCurrentValueNotInListUseDefault() {
    $select = new PapayaUiToolbarSelect('foo', array(42 => 'bar'));
    $select->defaultValue = 21;
    $select->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $this->mockPapaya()->request(array('foo' => '23'), 'http://www.test.tld')
        )
      )
    );
    $this->assertSame(
      21, $select->getCurrentValue()
    );
  }

  /**
  * @covers PapayaUiToolbarSelect::setCurrentValue
  * @covers PapayaUiToolbarSelect::getCurrentValue
  * @covers PapayaUiToolbarSelect::validateCurrentValue
  */
  public function testGetCurrentValueAfterSet() {
    $select = new PapayaUiToolbarSelect('foo', array(42 => 'bar'));
    $select->currentValue = 42;
    $this->assertSame(
      42, $select->currentValue
    );
  }

  /**
  * @covers PapayaUiToolbarSelect::appendTo
  */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument;
    $dom->appendElement('sample');
    $select = new PapayaUiToolbarSelect('foo', array('foo' => 'bar'));
    $select->papaya($this->mockPapaya()->application());
    $select->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample>'.
        '<combo name="foo" action="http://www.test.tld/test.html">'.
        '<option value="foo">bar</option>'.
        '</combo>'.
        '</sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiToolbarSelect::appendTo
  */
  public function testAppendToWithAllProperties() {
    $dom = new PapayaXmlDocument;
    $dom->appendElement('sample');
    $select = new PapayaUiToolbarSelect('foo', array('foo' => 'bar'));
    $select->papaya($this->mockPapaya()->application());
    $select->defaultCaption = 'Please Select';
    $select->defaultValue = 42;
    $select->caption = 'Sample Caption';
    $select->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample>'.
        '<combo name="foo" action="http://www.test.tld/test.html" title="Sample Caption">'.
        '<option value="42">Please Select</option>'.
        '<option value="foo">bar</option>'.
        '</combo>'.
        '</sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiToolbarSelect::appendTo
  */
  public function testAppendToWithActionParameters() {
    $reference = $this->getMock('PapayaUiReference');
    $reference
      ->expects($this->any())
      ->method('getParameterGroupSeparator')
      ->will($this->returnValue('[]'));
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->with(NULL, FALSE)
      ->will($this->returnValue('sample.php'));
    $reference
      ->expects($this->once())
      ->method('getParametersList')
      ->will($this->returnValue(array('additional' => '42')));
    $dom = new PapayaXmlDocument;
    $dom->appendElement('sample');
    $select = new PapayaUiToolbarSelect('foo', array('foo' => 'bar'));
    $select->papaya($this->mockPapaya()->application());
    $select->reference = $reference;
    $select->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample>'.
        '<combo name="foo" action="sample.php">'.
        '<parameter name="additional" value="42"/>'.
        '<option value="foo">bar</option>'.
        '</combo>'.
        '</sample>',
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiToolbarSelect::appendTo
  */
  public function testAppendToWithCurrentValue() {
    $dom = new PapayaXmlDocument;
    $dom->appendElement('sample');
    $select = new PapayaUiToolbarSelect('foo', array('foo' => 'bar'));
    $select->papaya($this->mockPapaya()->application());
    $select->currentValue = 'foo';
    $select->appendTo($dom->documentElement);
    $this->assertEquals(
      '<sample>'.
        '<combo name="foo" action="http://www.test.tld/test.html">'.
        '<option value="foo" selected="selected">bar</option>'.
        '</combo>'.
        '</sample>',
      $dom->saveXml($dom->documentElement)
    );
  }
}
