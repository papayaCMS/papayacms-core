<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFileSystemActionScriptTest extends PapayaTestCase {

  /**
   * @covers PapayaFileSystemActionScript::__construct
   */
  public function testConstructor() {
    $action = new PapayaFileSystemActionScript('/local/script');
    $this->assertAttributeEquals(
      '/local/script', '_script', $action
    );
  }

  /**
   * @covers PapayaFileSystemActionScript::execute
   */
  public function testExecute() {
    $action = new PapayaFileSystemActionScript_TestProxy('/local/script');
    $action->execute(array('foo' => 'bar'));
    $this->assertEquals(
      array(
        '/local/script',
        array('--foo' => 'bar')
      ),
      $action->commandCall
    );
  }
}

class PapayaFileSystemActionScript_TestProxy extends PapayaFileSystemActionScript {

  public $commandCall = array();

  protected function executeCommand($command, $arguments) {
    $this->commandCall = func_get_args();
  }
}
