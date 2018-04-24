<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaFilterFactoryExceptionInvalidFilterTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryExceptionInvalidFilter
   */
  public function testConstructor() {
    $exception = new PapayaFilterFactoryExceptionInvalidFilter('ExampleFilter');
    $this->assertEquals(
      'Can not use invalid filter class: "ExampleFilter".',
      $exception->getMessage()
    );
  }

}
