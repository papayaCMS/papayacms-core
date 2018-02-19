<?php
require_once(__DIR__.'/../../../../bootstrap.php');

class PapayaMediaFilePropertiesTest extends PapayaTestCase {

  public function testFetchPropertiesFromInfoImplementation() {
    $infoMock = $this->createMock(PapayaMediaFileInfo::class);
    $infoMock
      ->expects($this->once())
      ->method('isSupported')
      ->willReturn(TRUE);
    $infoMock
      ->expects($this->once())
      ->method('getIterator')
      ->willReturn(new ArrayIterator(['foo' => 'bar']));
    $info = new PapayaMediaFileProperties('example.file');
    $info->fetchers($infoMock);

    $this->assertEquals(
      ['foo' => 'bar'],
      iterator_to_array($info)
    );
  }

  public function testLazyIntializationOfFetchers() {
    $info = new PapayaMediaFileProperties('example.file');
    $this->assertCount(3, $info->fetchers());
  }
}