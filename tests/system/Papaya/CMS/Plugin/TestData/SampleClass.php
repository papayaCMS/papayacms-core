<?php

use Papaya\Plugin\Editable\Content as EditableContent;

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

class PluginLoader_SampleClass extends \Papaya\Application\BaseObject {

  public $data;

  public function setData($data = NULL) {
    $this->data = $data;
  }

}

class PluginLoader_SampleClassEditable
  extends \Papaya\Application\BaseObject
  implements \Papaya\Plugin\Editable {

  /**
   * @var EditableContent $content
   */
  public $content;

  public function content(EditableContent $content = NULL): EditableContent {
    if (NULL !== $content) {
      $this->content = $content;
    } elseif (NULL === $this->content) {
      $this->content = new EditableContent();
    }
    return $this->content;
  }

}
