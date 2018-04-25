<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationCommunityUsersListDialogTest extends PapayaTestCase {

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::prepare
  */
  public function testPrepare() {
    $dialog = new PapayaAdministrationCommunityUsersListDialog();
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->prepare();
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="get">
        <title caption="Users"/>
        <options>
          <option name="USE_CONFIRMATION" value="no"/>
          <option name="USE_TOKEN" value="no"/>
          <option name="PROTECT_CHANGES" value="yes"/>
          <option name="CAPTION_STYLE" value="0"/>
          <option name="DIALOG_WIDTH" value="s"/>
          <option name="TOP_BUTTONS" value="no"/>
          <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>
        <field caption="Search" class="DialogFieldInput" error="no">
          <input type="text" name="filter" maxlength="1024"/></field>
        <field class="DialogFieldButtons" error="no">
          <buttons>
            <button type="submit" align="right">Filter</button>
            <button type="submit" align="left" name="filter-reset[1]">Reset</button>
          </buttons>
        </field>
        <field class="DialogFieldListview" error="no">
          <listview><toolbar position="bottom right"/></listview>
        </field>
      </dialog-box>',
      $dialog->getXml()
    );
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::execute
  */
  public function testExecute() {
    $users = $this->createMock(PapayaContentCommunityUsers::class);
    $users
      ->expects($this->once())
      ->method('load')
      ->with(
        array('filter' => NULL),
        20,
        0
      );
    $dialog = new PapayaAdministrationCommunityUsersListDialog_TestProxy();
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->parameters(new PapayaRequestParameters(array()));
    $dialog->users($users);
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::execute
  */
  public function testExecuteWithFilter() {
    $users = $this->createMock(PapayaContentCommunityUsers::class);
    $users
      ->expects($this->once())
      ->method('load')
      ->with(
        array('filter' => 'foo'),
        20,
        20
      );
    $dialog = new PapayaAdministrationCommunityUsersListDialog_TestProxy();
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->parameters(
      new PapayaRequestParameters(
        array(
          'page' => 2
        )
      )
    );
    $dialog->data(
      new PapayaRequestParameters(
        array(
          'filter' => 'foo'
        )
      )
    );
    $dialog->users($users);
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::execute
  */
  public function testExecuteWithFilterReset() {
    $users = $this->createMock(PapayaContentCommunityUsers::class);
    $users
      ->expects($this->once())
      ->method('load')
      ->with(
        array('filter' => NULL),
        20,
        0
      );
    $dialog = new PapayaAdministrationCommunityUsersListDialog_TestProxy();
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->data(
      new PapayaRequestParameters(
        array(
          'filter' => 'foo',
          'filter-reset' => '1'
        )
      )
    );
    $dialog->users($users);
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::users
  */
  public function testUsersGetAfterset() {
    $users = $this->createMock(PapayaContentCommunityUsers::class);
    $dialog = new PapayaAdministrationCommunityUsersListDialog();
    $dialog->users($users);
    $this->assertSame($users, $dialog->users());
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::users
  */
  public function testUsersImplicitCreate() {
    $dialog = new PapayaAdministrationCommunityUsersListDialog();
    $this->assertInstanceOf(PapayaContentCommunityUsers::class, $dialog->users());
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::listview
  */
  public function testListviewGetAfterSet() {
    $listview = $this->createMock(PapayaUiListview::class);
    $dialog = new PapayaAdministrationCommunityUsersListDialog();
    $dialog->listview($listview);
    $this->assertSame($listview, $dialog->listview());
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::listview
  */
  public function testListviewImplicitCreate() {
    $dialog = new PapayaAdministrationCommunityUsersListDialog();
    $dialog->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(PapayaUiListview::class, $dialog->listview());
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::createUserItem
  */
  public function testCreateUserItem() {
    $dialog = new PapayaAdministrationCommunityUsersListDialog();
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->createUserItem(
      new stdClass, $dialog->listview()->items, array('id' => 42, 'caption' => 'test')
    );
    $this->assertXmlStringEqualsXmlString(
      '<listitem title="test" href="http://www.test.tld/test.html?page=1&amp;user_id=42"/>',
      $dialog->listview()->items[0]->getXml()
    );
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::paging
  */
  public function testPagingGetAfterSet() {
    $paging = $this
      ->getMockBuilder(PapayaUiToolbarPaging::class)
      ->disableOriginalConstructor()
      ->getMock();
    $dialog = new PapayaAdministrationCommunityUsersListDialog();
    $dialog->paging($paging);
    $this->assertSame($paging, $dialog->paging());
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::paging
  */
  public function testPagingImplicitCreate() {
    $dialog = new PapayaAdministrationCommunityUsersListDialog();
    $this->assertInstanceOf(PapayaUiToolbarPaging::class, $dialog->paging());
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(PapayaUiReference::class);
    $dialog = new PapayaAdministrationCommunityUsersListDialog();
    $dialog->reference($reference);
    $this->assertSame($reference, $dialog->reference());
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::reference
  */
  public function testReferenceImplicitCreate() {
    $dialog = new PapayaAdministrationCommunityUsersListDialog();
    $this->assertInstanceOf(PapayaUiReference::class, $dialog->reference());
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::setParameterNameMapping
  */
  public function testSetParamterNameMapping() {
    $dialog = new PapayaAdministrationCommunityUsersListDialog();
    $dialog->setParameterNameMapping('user', 'surfer_id');
    $dialog->setParameterNameMapping('filter', 'search');
    $dialog->setParameterNameMapping('page', 'offset_page');
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->createUserItem(
      new stdClass, $dialog->listview()->items, array('id' => 42, 'caption' => 'test')
    );
    $this->assertXmlStringEqualsXmlString(
      '<listitem title="test" href="http://www.test.tld/test.html?offset_page=1&amp;surfer_id=42"/>',
      $dialog->listview()->items[0]->getXml()
    );
  }

  /**
  * @covers PapayaAdministrationCommunityUsersListDialog::setParameterNameMapping
  */
  public function testSetParamterNameMappingExpectingException() {
    $dialog = new PapayaAdministrationCommunityUsersListDialog();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown parameter identifier "unknown-parameter".');
    $dialog->setParameterNameMapping('unknown-parameter', 'some');
  }

}

class PapayaAdministrationCommunityUsersListDialog_TestProxy
  extends PapayaAdministrationCommunityUsersListDialog {

  public $_executionResult = TRUE;
}
