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
namespace Papaya\Administration\Settings\Commands {

  use Papaya\Configuration\CMS as CMSSettings;
  use Papaya\Iterator\Callback as CallbackIterator;
  use Papaya\Iterator\Filter\Callback as CallbackFilterIterator;
  use Papaya\Response\Content\CSV;
  use Papaya\UI\Control\Command;
  use Papaya\XML\Element;

  class ExportSettings extends Command {

    private static $_blocked = [
      CMSSettings::DB_URI,
      CMSSettings::DB_URI_WRITE
    ];

    public function appendTo(Element $parent) {
      $response = $this->papaya()->response;
      $response->setContentType('text/csv');
      $response->headers()->set(
        'Content-Disposition', 'attachment; filename="cms-settings.csv"'
      );
      $response->content(
        $csv = new CSV(
          new CallbackIterator(
            new CallbackFilterIterator(
              $this->papaya()->options,
              function($value, $setting) {
                return !in_array($setting, self::$_blocked, TRUE);
              }
            ),
            function($value, $setting) {
              return [$setting, $value === FALSE ? 0 : $value];
            }
          ),
          ['setting', 'value']
        )
      );
      $response->send(TRUE);
    }
  }
}

