<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaPluginFilterContentRecordsTest extends PapayaTestCase {

  /**
   * @covers PapayaPluginFilterContentRecords
   */
  public function testRecordsGetAfterSet() {
    $filterGroup = new PapayaPluginFilterContentRecords($this->getPageFixture());
    $filterGroup->records($records = $this->getMock('PapayaContentViewConfigurations'));
    $this->assertSame($records, $filterGroup->records());
  }

  /**
   * @covers PapayaPluginFilterContentRecords
   */
  public function testRecordsImplicitCreate() {
    $filterGroup = new PapayaPluginFilterContentRecords($this->getPageFixture());
    $this->assertInstanceOf('PapayaContentViewConfigurations', $filterGroup->records());
  }

  /**
   * @covers PapayaPluginFilterContentRecords
   */
  public function testIteratorFetchesPlugins() {
    $plugins = $this->getMock('PapayaPluginLoader');
    $plugins
      ->expects($this->once())
      ->method('get')
      ->with('guid', $this->isInstanceOf('PapayaUiContentPage'), 'options')
      ->will($this->returnValue($this->getMock('PapayaPluginFilterContent')));

    $records = $this->getMock('PapayaContentViewConfigurations');
    $records
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              array(
                'module_guid' => 'guid',
                'options' => 'options'
              )
            )
          )
        )
      );

    $filterGroup = new PapayaPluginFilterContentRecords($this->getPageFixture());
    $filterGroup->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));
    $filterGroup->records($records);

    $this->assertCount(1, iterator_to_array($filterGroup));
  }

  public function getPageFixture($viewId = NULL) {
    $page = $this
      ->getMockBuilder('PapayaUiContentPage')
      ->disableOriginalConstructor()
      ->getMock();
    if (isset($viewId)) {
      $page
        ->expects($this->once())
        ->method('getPageViewId')
        ->will($this->returnValue($viewId));
    }
    return $page;
  }

}
