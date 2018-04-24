<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaPluginFilterContentGroupTest extends PapayaTestCase {

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testConstructor() {
    $filter = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $this->assertSame($page, $filter->getPage());
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testAddAndIterator() {
    $filter = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filter->add(
      $filterOne = $this->createMock(PapayaPluginFilterContent::class)
    );
    $filter->add(
      $filterTwo = $this->createMock(PapayaPluginFilterContent::class)
    );
    $this->assertSame(
      array($filterOne, $filterTwo),
      iterator_to_array($filter, FALSE)
    );
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testPrepare() {
    $filterOne = $this->createMock(PapayaPluginFilterContent::class);
    $filterOne
      ->expects($this->once())
      ->method('prepare')
      ->with('data');

    $filterGroup = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->prepare('data');
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testPrepareBC() {
    $filterOne = $this->getMock(
      'stdClass',
      array('initialize', 'prepareFilterData', 'loadFilterData')
    );
    $filterOne
      ->expects($this->once())
      ->method('initialize')
      ->with($this->isInstanceOf(stdClass::class));
    $filterOne
      ->expects($this->once())
      ->method('prepareFilterData')
      ->with(array('text' => 'data'), array('text'));
    $filterOne
      ->expects($this->once())
      ->method('loadFilterData')
      ->with(array('text' => 'data'));

    $filterGroup = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->prepare('data');
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testApplyTo() {
    $filterOne = $this->createMock(PapayaPluginFilterContent::class);
    $filterOne
      ->expects($this->once())
      ->method('applyTo')
      ->with('data')
      ->will($this->returnValue('success'));

    $filterGroup = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->applyTo('data');
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testApplyToBC() {
    $filterOne = $this->getMock(
      'stdClass',
      array('applyFilterData')
    );
    $filterOne
      ->expects($this->once())
      ->method('applyFilterData')
      ->with('data')
      ->will($this->returnValue('success'));

    $filterGroup = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->applyTo('data');
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testAppendTo() {
    $dom = new PapayaXmlDocument();
    $node = $dom->appendElement('test');
    $filterOne = $this->createMock(PapayaPluginFilterContent::class);
    $filterOne
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));

    $filterGroup = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->appendTo($node);
  }

  /**
   * @covers PapayaPluginFilterContentGroup
   */
  public function testAppendToBC() {
    $dom = new PapayaXmlDocument();
    $node = $dom->appendElement('test');
    $filterOne = $this->getMock(
      'stdClass',
      array('getFilterData')
    );
    $filterOne
      ->expects($this->once())
      ->method('getFilterData')
      ->with()
      ->will($this->returnValue('success'));

    $filterGroup = new PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->appendTo($node);
    $this->assertEquals('<test>success</test>', $node->saveXml());
  }

  public function getPageFixture() {
    $page = $this
      ->getMockBuilder(PapayaUiContentPage::class)
      ->disableOriginalConstructor()
      ->getMock();
    return $page;
  }

}
