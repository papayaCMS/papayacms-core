<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaPhrasesGroupTest extends PapayaTestCase {

  /**
   * @covers PapayaPhrasesGroup
   */
  public function testGet() {
    $phrases = $this
      ->getMockBuilder(PapayaPhrases::class)
      ->disableOriginalConstructor()
      ->getMock();
    $phrases
      ->expects($this->once())
      ->method('getText')
      ->with('Test', '#default')
      ->will($this->returnValue('Success'));
    $group = new PapayaPhrasesGroup($phrases, '#default');
    $phrase = $group->get('Test');
    $this->assertInstanceOf(PapayaUiStringTranslated::class, $phrase);
    $this->assertEquals('Success', (string)$phrase);
  }

  /**
   * @covers PapayaPhrasesGroup
   */
  public function testGetList() {
    $phrases = $this
      ->getMockBuilder(PapayaPhrases::class)
      ->disableOriginalConstructor()
      ->getMock();
    $phrases
      ->expects($this->once())
      ->method('getText')
      ->with('Test', '#default')
      ->will($this->returnValue('Success'));
    $group = new PapayaPhrasesGroup($phrases, '#default');
    $phraseList = $group->getList(array('One' => 'Test'));
    $this->assertInstanceOf(PapayaUiStringTranslatedList::class, $phraseList);
    $list = iterator_to_array($phraseList);
    $this->assertEquals('Success', (string)$list['One']);
  }
}
