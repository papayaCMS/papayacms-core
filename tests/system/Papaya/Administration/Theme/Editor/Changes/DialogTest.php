<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaAdministrationThemeEditorChangesDialogTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::createDialog
   */
  public function testCreateDialogWithEmptyPage() {
    $page = $this->getMock('PapayaContentStructurePage');
    $page
      ->expects($this->once())
      ->method('groups')
      ->will($this->returnValue(new EmptyIterator()));
    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->papaya($this->mockPapaya()->application());
    $command->themePage($page);
    $this->assertInstanceOf('PapayaUiDialog', $command->createDialog());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::createDialog
   */
  public function testCreateDialogWithOneEmptyGroup() {
    $page = new PapayaContentStructurePage();
    $page->name = 'SAMPLE_PAGE';
    $page->title = 'Page title';
    $page->groups()->add($group = new PapayaContentStructureGroup($page));
    $group->name = 'SAMPLE_GROUP';
    $group->title = 'group title';

    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->papaya($this->mockPapaya()->application());
    $command->themePage($page);
    $this->assertInstanceOf('PapayaUiDialog', $command->createDialog());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::createDialog
   */
  public function testCreateDialogWithOneValueOfUnknownType() {
    $page = new PapayaContentStructurePage();
    $page->name = 'SAMPLE_PAGE';
    $page->title = 'Page title';
    $page->groups()->add($group = new PapayaContentStructureGroup($page));
    $group->name = 'SAMPLE_GROUP';
    $group->title = 'group title';
    $group->values()->add($value = new PapayaContentStructureValue($group));
    $value->name = 'SAMPLE_VALUE';
    $value->title = 'value title';
    $value->fieldType = 'UNKNOWN_FIELD_TYPE';
    $value->default = 'foo';

    $factory = $this->getMock('PapayaUiDialogFieldFactory');
    $factory
      ->expects($this->once())
      ->method('getField')
      ->with('UNKNOWN_FIELD_TYPE', $this->isInstanceOf('PapayaUiDialogFieldFactoryOptions'))
      ->will($this->throwException($this->getMock('PapayaUiDialogFieldFactoryException')));

    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->papaya($this->mockPapaya()->application());
    $command->themePage($page);
    $command->fieldFactory($factory);
    $this->assertInstanceOf('PapayaUiDialog', $dialog = $command->createDialog());
    $this->assertCount(1, $dialog->fields[0]->fields);
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::createDialog
   */
  public function testCreateDialogWithOneValue() {
    $page = new PapayaContentStructurePage();
    $page->name = 'SAMPLE_PAGE';
    $page->title = 'Page title';
    $page->groups()->add($group = new PapayaContentStructureGroup($page));
    $group->name = 'SAMPLE_GROUP';
    $group->title = 'group title';
    $group->values()->add($value = new PapayaContentStructureValue($group));
    $value->name = 'SAMPLE_VALUE';
    $value->title = 'value title';
    $value->fieldType = 'UNKNOWN_FIELD_TYPE';
    $value->default = 'foo';

    $factory = $this->getMock('PapayaUiDialogFieldFactory');
    $factory
      ->expects($this->once())
      ->method('getField')
      ->with('UNKNOWN_FIELD_TYPE', $this->isInstanceOf('PapayaUiDialogFieldFactoryOptions'))
      ->will($this->returnValue($this->getMock('PapayaUiDialogField')));

    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->papaya($this->mockPapaya()->application());
    $command->themePage($page);
    $command->fieldFactory($factory);
    $this->assertInstanceOf('PapayaUiDialog', $dialog = $command->createDialog());
    $this->assertCount(1, $dialog->fields[0]->fields);
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::createDialog
   */
  public function testCreateDialogWithEmptyDefinition() {
    $papaya = $this->mockPapaya()->application(
      array(
        'request' => $this->mockPapaya()->request(
          array('theme' => 'sample', 'set_id' => 23, 'page_identifier' => 'SAMPLE_PAGE')
        )
      )
    );
    $definition = $this->getMock('PapayaContentStructure');
    $definition
      ->expects($this->once())
      ->method('getPage')
      ->with('SAMPLE_PAGE')
      ->will($this->returnValue(NULL));
    $themeHandler = $this->getMock('PapayaThemeHandler');
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('sample')
      ->will($this->returnValue($definition));
    $themeSet = $this->getMock('PapayaContentThemeSet');
    $themeSet
      ->expects($this->once())
      ->method('load')
      ->with(23);

    $command = new PapayaAdministrationThemeEditorChangesDialog($themeSet);
    $command->papaya($papaya);
    $command->themeHandler($themeHandler);
    $this->assertInstanceOf('PapayaUiDialog', $command->createDialog());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::themePage
   */
  public function testThemePageGetAfterSet() {
    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->themePage($themePage =  $this->getMock('PapayaContentStructurePage'));
    $this->assertSame($themePage, $command->themePage());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::themePage
   */
  public function testThemePageGetImplicitCreate() {
    $papaya = $this->mockPapaya()->application(
      array(
        'request' => $this->mockPapaya()->request(
          array('theme' => 'sample', 'set_id' => 23, 'page_identifier' => 'SAMPLE_PAGE')
        )
      )
    );
    $definition = $this->getMock('PapayaContentStructure');
    $definition
      ->expects($this->once())
      ->method('getPage')
      ->with('SAMPLE_PAGE')
      ->will($this->returnValue($this->getMock('PapayaContentStructurePage')));
    $themeHandler = $this->getMock('PapayaThemeHandler');
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('sample')
      ->will($this->returnValue($definition));
    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->papaya($papaya);
    $command->themeHandler($themeHandler);
    $this->assertInstanceOf('PapayaContentStructurePage', $command->themePage());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::themeHandler
   */
  public function testThemeHandlerGetAfterSet() {
    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->themeHandler($themeHandler =  $this->getMock('PapayaThemeHandler'));
    $this->assertSame($themeHandler, $command->themeHandler());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::themeHandler
   */
  public function testThemeHandlerGetImplicitCreate() {
    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $this->assertInstanceOf('PapayaThemeHandler', $command->themeHandler());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::fieldFactory
   */
  public function testFieldFactoryGetAfterSet() {
    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->fieldFactory($fieldFactory =  $this->getMock('PapayaUiDialogFieldFactory'));
    $this->assertSame($fieldFactory, $command->fieldFactory());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::fieldFactory
   */
  public function testFieldFactoryGetImplicitCreate() {
    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $this->assertInstanceOf('PapayaUiDialogFieldFactory', $command->fieldFactory());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::callbackSaveValues
   */
  public function testCallbackSaveValues() {
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplay'));
    $cache = $this->getMock('PapayaCacheService');
    $cache
      ->expects($this->once())
      ->method('delete')
      ->with('theme', '');

    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $command->cache($cache);
    $command->callbackSaveValues(new stdClass, $this->getMock('PapayaUiDialog'));
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::callbackShowError
   */
  public function testCallbackShowError() {
    $errors = $this->getMock('PapayaUiDialogErrors');
    $errors
      ->expects($this->once())
      ->method('getSourceCaptions')
      ->will($this->returnValue(array()));
    $dialog = $this->getMock('PapayaUiDialog');
    $dialog
      ->expects($this->once())
      ->method('errors')
      ->will($this->returnValue($errors));

    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplay'));
    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $command->callbackShowError(new stdClass, $dialog);
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::cache
   */
  public function testCacheGetAfterSet() {
    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->cache($cache =  $this->getMock('PapayaCacheService'));
    $this->assertSame($cache, $command->cache());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::cache
   */
  public function testCacheGetImplicitCreate() {
    $command = new PapayaAdministrationThemeEditorChangesDialog(
      $this->getMock('PapayaDatabaseInterfaceRecord')
    );
    $command->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf('PapayaCacheService', $command->cache());
  }

}
