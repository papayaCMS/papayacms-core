<?php
/**
* The actual content of an email part. This can be a list of other parts, text, html, binary data or
* or special content.
*
* @copyright 2002-2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Email
* @version $Id: Content.php 35539 2011-03-18 11:53:51Z weinert $
*/

/**
* The actual content of an email part. This can be a list of other parts, text, html, binary data or
* or special content.
*
* @package Papaya-Library
* @subpackage Email
*/
interface PapayaEmailContent {

  /**
  * Get the email content i a single encoded string
  *
  * @return string
  */
  function getString();

  /**
  * Set the email content from a single encoded string
  *
  * @param string $content
  */
  function setString($content);
}