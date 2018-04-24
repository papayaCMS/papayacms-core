<?php
require_once __DIR__.'/../../../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileRichtextSimpleTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileRichtextSimple::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'rtefield',
        'caption' => 'Richtext',
        'default' => 'some value'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileRichtextSimple();
    $profile->options($options);
    $this->assertInstanceOf(
      PapayaUiDialogFieldTextareaRichtext::class, $field = $profile->getField()
    );
    $this->assertEquals(
      PapayaUiDialogFieldTextareaRichtext::RTE_SIMPLE,
      $field->getRteMode()
    );
  }
}
