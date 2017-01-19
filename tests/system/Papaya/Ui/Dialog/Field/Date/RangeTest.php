<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaUiDialogFieldDateRangeTest extends PapayaTestCase {

  public function testAppendTo() {
    $field = new PapayaUiDialogFieldDateRange('Caption', 'name');
    $this->assertXmlStringEqualsXmlString(
      '<field caption="Caption" class="DialogFieldDateRange" data-include-time="true" error="no">'.
        '<input name="name[start]" type="date"/>'.
        '<input name="name[end]" type="date"/>'.
      '</field>',
      $field->getXml()
    );
  }
}
