<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaUiDialogFieldSelectRadioTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldSelectRadio
  */
  public function testAppendTo() {
    $select = new PapayaUiDialogFieldSelectRadio(
      'Caption', 'name', array(1 => 'One', 2 => 'Two')
    );
    $select->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      '<field caption="Caption" class="DialogFieldSelectRadio" error="yes" mandatory="yes">'.
        '<select name="name" type="radio">'.
          '<option value="1">One</option>'.
          '<option value="2">Two</option>'.
        '</select>'.
      '</field>',
      $select->getXml()
    );
  }
}
