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

class PapayaTemplateParameters extends \Papaya\BaseObject\Options\Collection {

  public function __construct(array $options = NULL) {
    $this['SYSTEM_TIME'] = date('Y-m-d H:i:s');
    $this['SYSTEM_TIME_OFFSET'] = date('O');
    $this['PAPAYA_VERSION'] = defined('PAPAYA_VERSION_STRING') ? PAPAYA_VERSION_STRING : '';
    parent::__construct($options);
  }
}
