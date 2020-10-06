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

namespace Papaya\Administration\Media\Commands {

  use Papaya\UI\Control\Command\Condition\Record as RecordCondition;
  use Papaya\UI\Control\Command\Dialog\Database\Record as DatabaseDialogCommand;
  use Papaya\UI\Text\Translated;

  class ChangeFile
    extends DatabaseDialogCommand {

    protected function createDialog() {
      $dialog = parent::createDialog();
      $dialog->caption = new Translated('Edit File');
      return $dialog;
    }

    public function createCondition() {
      return new RecordCondition($this->record());
    }
  }
}


