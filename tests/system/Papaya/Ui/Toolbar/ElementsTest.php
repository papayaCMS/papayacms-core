<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiToolbarElementsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarElements::__construct
  * @covers PapayaUiToolbarElements::owner
  */
  public function testConstructor() {
    $menu = $this->createMock(PapayaUiMenu::class);
    $elements = new PapayaUiToolbarElements($menu);
    $this->assertSame(
      $menu, $elements->owner()
    );
  }

  /**
  * @covers PapayaUiToolbarElements::validateItemClass
  */
  public function testAddElementWhileGroupsAllowed() {
    $elements = new PapayaUiToolbarElements($this->createMock(PapayaUiMenu::class));
    $elements->allowGroups = TRUE;
    $group = $this->getMock('PapayaUiToolbarGroup', array(), array('caption'));
    $elements->add($group);
    $this->assertEquals(
      $group, $elements[0]
    );
  }

  /**
  * @covers PapayaUiToolbarElements::validateItemClass
  */
  public function testAddElementWhileGroupsNotAllowedExpectingException() {
    $elements = new PapayaUiToolbarElements($this->createMock(PapayaUiMenu::class));
    $elements->allowGroups = FALSE;
    $group = new PapayaUiToolbarGroup('caption');
    $this->setExpectedException(
      'InvalidArgumentException',
      'InvalidArgumentException: Invalid item class "PapayaUiToolbarGroup".'
    );
    $elements->add($group);
  }
}
