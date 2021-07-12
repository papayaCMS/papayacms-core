<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\CMS\Administration\UI\ListView {

  use Papaya\UI\ListView;
  use Papaya\UI\Text\Translated;
  use Papaya\UI\Toolbar\Select\Buttons;

  class ViewToggle extends Buttons {

    public function __construct($parameterName) {
      parent::__construct(
        $parameterName,
        [
            ListView::MODE_DETAILS => ['image' => 'actions.view-list', 'hint' => new Translated('List')],
            ListView::MODE_TILES => ['image' => 'actions.view-tiles', 'hint' => new Translated('Tiles')],
            ListView::MODE_THUMBNAILS => ['image' => 'actions.view-icons', 'hint' => new Translated('Thumbnails')]
          ]
      );
      $this->defaultValue = ListView::MODE_DETAILS;
    }
  }
}


