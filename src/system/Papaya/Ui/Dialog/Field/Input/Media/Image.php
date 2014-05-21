<?php
/**
* A single line input for an image (gif, png, jpeg) from the media database
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Ui
* @version $Id: Image.php 37522 2012-10-01 16:27:17Z weinert $
*/

/**
* A single line input for an image (gif, png, jpeg) from the media database
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldInputMediaImage extends PapayaUiDialogFieldInputMediaFile {

  /**
  * Field type, used in template
  *
  * @var boolean
  */
  protected $_type = 'media_image';

}
