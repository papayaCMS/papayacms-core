<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../bootstrap.php';

class PapayaEmailRecipientsTest extends PapayaTestCase {

  /**
  * @covers \PapayaEmailRecipients::__construct
  */
  public function testConstructor() {
    $recipients = new \PapayaEmailRecipients();
    $this->assertEquals(PapayaEmailAddress::class, $recipients->getItemClass());
  }

  /**
  * @covers \PapayaEmailRecipients::prepareItem
  */
  public function testAddItemAsObject() {
    $recipients = new \PapayaEmailRecipients();
    $address = new \PapayaEmailAddress();
    $address->address = 'John Doe <john.doe@local.tld>';
    $recipients[] = $address;
    $this->assertEquals(
      'John Doe <john.doe@local.tld>', (string)$recipients[0]
    );
  }

  /**
  * @covers \PapayaEmailRecipients::prepareItem
  */
  public function testAddItemAsString() {
    $recipients = new \PapayaEmailRecipients();
    $recipients[] = 'John Doe <john.doe@local.tld>';
    $this->assertInstanceOf(
      PapayaEmailAddress::class, $recipients[0]
    );
    $this->assertEquals(
      'John Doe <john.doe@local.tld>', (string)$recipients[0]
    );
  }
}
