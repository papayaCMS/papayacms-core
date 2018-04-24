<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldDateRangeTest extends PapayaTestCase {

  public function testAppendTo() {
    $field = new PapayaUiDialogFieldDateRange('Caption', 'name');
    $field->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      '<field caption="Caption" class="DialogFieldDateRange" data-include-time="false" error="no">'.
        '<group data-selected-page="fromTo">'.
          '<labels/>'.
            '<input name = "name[start]" type = "date" />'.
            '<input name = "name[end]" type = "date" />'.
        '</group> '.
      '</field> ',
      $field->getXml()
    );
  }
}
