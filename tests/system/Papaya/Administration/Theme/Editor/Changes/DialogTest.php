<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaAdministrationThemeEditorChangesDialogTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::createDialog
   */
  public function testCreateDialogWithEmptyPage() {
    $page = $this->createMock(PapayaContentStructurePage::class);
    $page
      ->expects($this->once())
      ->method('groups')
      ->will($this->returnValue(new EmptyIterator()));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
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

    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
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

    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiDialogFieldFactoryException $exception */
    $exception = $this->createMock(PapayaUiDialogFieldFactoryException::class);
    $factory = $this->createMock(PapayaUiDialogFieldFactory::class);
    $factory
      ->expects($this->once())
      ->method('getField')
      ->with('UNKNOWN_FIELD_TYPE', $this->isInstanceOf(PapayaUiDialogFieldFactoryOptions::class))
      ->will($this->throwException($exception));

    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
    $command->papaya($this->mockPapaya()->application());
    $command->themePage($page);
    $command->fieldFactory($factory);
    $this->assertInstanceOf(PapayaUiDialog::class, $dialog = $command->createDialog());
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

    $factory = $this->createMock(PapayaUiDialogFieldFactory::class);
    $factory
      ->expects($this->once())
      ->method('getField')
      ->with('UNKNOWN_FIELD_TYPE', $this->isInstanceOf(PapayaUiDialogFieldFactoryOptions::class))
      ->will($this->returnValue($this->createMock(PapayaUiDialogField::class)));

    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
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
    $definition = $this->createMock(PapayaContentStructure::class);
    $definition
      ->expects($this->once())
      ->method('getPage')
      ->with('SAMPLE_PAGE')
      ->will($this->returnValue(NULL));
    $themeHandler = $this->createMock(PapayaThemeHandler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('sample')
      ->will($this->returnValue($definition));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaContentThemeSet $themeSet */
    $themeSet = $this->createMock(PapayaContentThemeSet::class);
    $themeSet
      ->expects($this->once())
      ->method('load')
      ->with(23);

    $command = new PapayaAdministrationThemeEditorChangesDialog($themeSet);
    $command->papaya($papaya);
    $command->themeHandler($themeHandler);
    $this->assertInstanceOf(PapayaUiDialog::class, $command->createDialog());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::themePage
   */
  public function testThemePageGetAfterSet() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
    $command->themePage($themePage =  $this->createMock(PapayaContentStructurePage::class));
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
    $definition = $this->createMock(PapayaContentStructure::class);
    $definition
      ->expects($this->once())
      ->method('getPage')
      ->with('SAMPLE_PAGE')
      ->will($this->returnValue($this->createMock(PapayaContentStructurePage::class)));
    $themeHandler = $this->createMock(PapayaThemeHandler::class);
    $themeHandler
      ->expects($this->once())
      ->method('getDefinition')
      ->with('sample')
      ->will($this->returnValue($definition));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
    $command->papaya($papaya);
    $command->themeHandler($themeHandler);
    $this->assertInstanceOf(PapayaContentStructurePage::class, $command->themePage());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::themeHandler
   */
  public function testThemeHandlerGetAfterSet() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
    $command->themeHandler($themeHandler =  $this->createMock(PapayaThemeHandler::class));
    $this->assertSame($themeHandler, $command->themeHandler());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::themeHandler
   */
  public function testThemeHandlerGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
    $this->assertInstanceOf(PapayaThemeHandler::class, $command->themeHandler());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::fieldFactory
   */
  public function testFieldFactoryGetAfterSet() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
    $command->fieldFactory($fieldFactory =  $this->createMock(PapayaUiDialogFieldFactory::class));
    $this->assertSame($fieldFactory, $command->fieldFactory());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::fieldFactory
   */
  public function testFieldFactoryGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
    $this->assertInstanceOf(PapayaUiDialogFieldFactory::class, $command->fieldFactory());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::callbackSaveValues
   */
  public function testCallbackSaveValues() {
    $messages = $this->createMock(PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessageDisplay::class));
    $cache = $this->createMock(PapayaCacheService::class);
    $cache
      ->expects($this->once())
      ->method('delete')
      ->with('theme', '');

    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
    $command->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $command->cache($cache);
    $command->callbackSaveValues();
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::callbackShowError
   */
  public function testCallbackShowError() {
    $errors = $this->createMock(PapayaUiDialogErrors::class);
    $errors
      ->expects($this->once())
      ->method('getSourceCaptions')
      ->will($this->returnValue(array()));

    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiDialog $dialog */
    $dialog = $this->createMock(PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('errors')
      ->will($this->returnValue($errors));

    $messages = $this->createMock(PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageDisplay'));
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
    $command->cache($cache =  $this->createMock(PapayaCacheService::class));
    $this->assertSame($cache, $command->cache());
  }

  /**
   * @covers PapayaAdministrationThemeEditorChangesDialog::cache
   */
  public function testCacheGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceRecord $record */
    $record = $this->createMock(PapayaDatabaseInterfaceRecord::class);
    $command = new PapayaAdministrationThemeEditorChangesDialog($record);
    $command->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(PapayaCacheService::class, $command->cache());
  }

}
