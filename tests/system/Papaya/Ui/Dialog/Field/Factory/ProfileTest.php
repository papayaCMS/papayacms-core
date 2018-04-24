<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfile::options
   */
  public function testOptionsGetAfterSet() {
    $profile = new PapayaUiDialogFieldFactoryProfile_TestProxy();
    $profile->options($options = $this->createMock(PapayaUiDialogFieldFactoryOptions::class));
    $this->assertSame(
      $options,
      $profile->options()
    );
  }

  /**
   * @covers PapayaUiDialogFieldFactoryProfile::options
   */
  public function testOptionsGetImplicitCreate() {
    $profile = new PapayaUiDialogFieldFactoryProfile_TestProxy();
    $this->assertInstanceOf(
      'PapayaUiDialogFieldFactoryOptions',
      $profile->options()
    );
  }

}

class PapayaUiDialogFieldFactoryProfile_TestProxy extends PapayaUiDialogFieldFactoryProfile {

  function getField() {
  }
}
