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
namespace Papaya\Administration\Settings\Icons {

  use Papaya\Iterator;
  use Papaya\UI;
  use Papaya\XML;

  class Viewer extends \Papaya\Administration\Page\Part {
    public function appendTo(XML\Element $parent) {
      parent::appendTo($parent);
      $groups = new Iterator\Tree\Groups\RegEx(
        $this->papaya()->images,
        '((?<group>^[a-z]+)-)',
        'group',
        Iterator\Tree\Groups\RegEx::GROUP_KEYS
      );
      $path = $this->getPage()->getUI()->getLocalPath();
      foreach ($groups as $groupName) {
        $listView = new UI\ListView();
        $listView->mode = UI\ListView::MODE_TILES;

        $group = $groups->getChildren();
        $imageCount = 0;
        foreach ($group as $index => $fileName) {
          $sizes = ['16x16', '22x22', '48x48'];
          $image = 'pics/tpoint.gif';
          $sizesAvailable = [];
          foreach ($sizes as $size) {
            if (0 === strpos($fileName, 'icon.')) {
              list(, $category, $name) = \explode('.', $fileName);
              $imageFile = 'pics/icons/'.$size.'/'.$category.'/'.$name.'.svg';
              if (\file_exists($path.'/'.$imageFile)) {
                $image = $fileName;
                $sizesAvailable[] = $size;
              }
            } else {
              $imageFile = 'pics/icons/'.$size.'/'.$fileName;
              if (\file_exists($path.'/'.$imageFile)) {
                $image = './'.$imageFile;
                $sizesAvailable[] = $size;
              }
            }
          }
          $imageCount++;
          $listView->items[] = $item = new UI\ListView\Item(
            $image,
            \substr($index, \strlen($groupName) + 1)
          );
          $item->text = \implode(', ', $sizesAvailable);
          $item->hint = $index.': '.$fileName;
        }
        $listView->caption = \sprintf(
          '%s (%s: %d)',
          $groupName,
          new UI\Text\Translated('Icons'),
          $imageCount
        );

        $parent->append($listView);
      }
    }
  }
}
