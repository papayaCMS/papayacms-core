<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldTextareaRichtextTest extends PapayaTestCase {

  /**
   * @covers PapayaUiDialogFieldTextareaRichtext::__construct
   */
  public function testConstructorSettingRteMode() {
    $richtext = new PapayaUiDialogFieldTextareaRichtext(
      'Caption', 'name', 12, NULL, NULL, PapayaUiDialogFieldTextareaRichtext::RTE_SIMPLE
    );
    $this->assertEquals(
      PapayaUiDialogFieldTextareaRichtext::RTE_SIMPLE, $richtext->getRteMode()
    );
  }

  /**
   * @covers PapayaUiDialogFieldTextareaRichtext::appendTo
   */
  public function testAppendTo() {
    $richtext = new PapayaUiDialogFieldTextareaRichtext('Caption', 'name');
    $this->assertXmlStringEqualsXmlString(
      '<field caption="Caption" class="DialogFieldTextareaRichtext" error="no">'.
        '<textarea type="text" name="name" lines="10" data-rte="standard"/>'.
      '</field>',
      $richtext->getXml()
    );
  }


  /**
   * @covers PapayaUiDialogFieldTextareaRichtext::appendTo
   */
  public function testAppendToWithAllParameters() {
    $richtext = new PapayaUiDialogFieldTextareaRichtext(
      'Caption', 'name', 12, NULL, NULL, PapayaUiDialogFieldTextareaRichtext::RTE_SIMPLE
    );
    $this->assertXmlStringEqualsXmlString(
      '<field caption="Caption" class="DialogFieldTextareaRichtext" error="no">'.
        '<textarea type="text" name="name" lines="12" data-rte="simple"/>'.
      '</field>',
      $richtext->getXml()
    );
  }

  /**
   * @covers PapayaUiDialogFieldTextareaRichtext::setRteMode
   * @covers PapayaUiDialogFieldTextareaRichtext::getRteMode
   */
  public function testGetRteModeAfterSetRteMode() {
    $richtext = new PapayaUiDialogFieldTextareaRichtext('Caption', 'name');
    $richtext->setRteMode(PapayaUiDialogFieldTextareaRichtext::RTE_SIMPLE);
    $this->assertEquals(
      PapayaUiDialogFieldTextareaRichtext::RTE_SIMPLE, $richtext->getRteMode()
    );
  }
}
