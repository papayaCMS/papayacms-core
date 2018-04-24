<?php
require_once __DIR__.'/../../../../../../../../bootstrap.php';

class PapayaUiDialogFieldFactoryProfileRichtextIndividualTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldFactoryProfileRichtextIndividual::getField
   */
  public function testGetField() {
    $options = new PapayaUiDialogFieldFactoryOptions(
      array(
        'name' => 'rtefield',
        'caption' => 'Richtext',
        'default' => 'some value'
      )
    );
    $profile = new PapayaUiDialogFieldFactoryProfileRichtextIndividual();
    $profile->options($options);
    $this->assertInstanceOf(
      PapayaUiDialogFieldTextareaRichtext::class, $field = $profile->getField()
    );
    $this->assertEquals(
      PapayaUiDialogFieldTextareaRichtext::RTE_INDIVIDUAL,
      $field->getRteMode()
    );
  }
}
