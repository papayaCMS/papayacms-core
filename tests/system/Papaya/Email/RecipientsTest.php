<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaEmailRecipientsTest extends PapayaTestCase {

  /**
  * @covers PapayaEmailRecipients::__construct
  */
  public function testConstructor() {
    $recipients = new PapayaEmailRecipients();
    $this->assertEquals(PapayaEmailAddress::class, $recipients->getItemClass());
  }

  /**
  * @covers PapayaEmailRecipients::prepareItem
  */
  public function testAddItemAsObject() {
    $recipients = new PapayaEmailRecipients();
    $address = new PapayaEmailAddress();
    $address->address = 'John Doe <john.doe@local.tld>';
    $recipients[] = $address;
    $this->assertEquals(
      'John Doe <john.doe@local.tld>', (string)$recipients[0]
    );
  }

  /**
  * @covers PapayaEmailRecipients::prepareItem
  */
  public function testAddItemAsString() {
    $recipients = new PapayaEmailRecipients();
    $recipients[] = 'John Doe <john.doe@local.tld>';
    $this->assertInstanceOf(
      PapayaEmailAddress::class, $recipients[0]
    );
    $this->assertEquals(
      'John Doe <john.doe@local.tld>', (string)$recipients[0]
    );
  }
}
