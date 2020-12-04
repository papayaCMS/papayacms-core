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

  use Papaya\Administration\UI\Navigation\Reference\LanguageIcon;
  use Papaya\Administration\UI\Navigation\Reference\MimeTypeIcon;
  use Papaya\Filter\Date as DateFilter;
  use Papaya\Graphics\ImageTypes;
  use Papaya\Media\Thumbnail\Calculation;
  use Papaya\UI\Control\Command\Condition\Record as RecordCondition;
  use Papaya\UI\Control\Command\Dialog\Database\Record as DatabaseDialogCommand;
  use Papaya\UI\Dialog\Field;
  use Papaya\UI\ListView;
  use Papaya\UI\Reference\Media;
  use Papaya\UI\Text\Translated;
  use Papaya\Content\Media\File;

  /**
   * @method File record(File $file = NULL)
   */
  class ChangeFile
    extends DatabaseDialogCommand {

    const PARAMETER_TITLE = 'title';
    const PARAMETER_DESCRIPTION = 'description';
    const PARAMETER_NAME = 'name';
    const PARAMETER_CREATED = 'created';
    const PARAMETER_SOURCE = 'source';
    const PARAMETER_SOURCE_URL = 'source-url';
    const PARAMETER_KEYWORDS = 'keywords';
    const PARAMETER_SORT = 'sort';

    protected function createDialog() {
      $dialog = parent::createDialog();
      $dialog->caption = new Translated('Edit File');
      $dialog->fields[] = $row = new Field\Row(Field\Row::DISTANCE_MEDIUM, Field\Row::DISTANCE_MEDIUM);
      $file = $this->record();

      $generator = $this->papaya()->media->createThumbnailGenerator(
        $file->id, $file->revision, $file->name
      );
      $calculation = $this->papaya()->media->createCalculation(
        [200, 200], [$file->imageWidth, $file->imageHeight], Calculation::MODE_CONTAIN
      );
      if ($file->mimetype === ImageTypes::MIMETYPE_SVG) {
        $reference = $this->papaya()->media->createReference($file->id, $file->revision, $file->name);
        $row->fields[] = new Field\Image($reference, ...$calculation->getTargetSize());
      } else {
        $thumbnail = $generator->createThumbnail($calculation);
        if ($thumbnail) {
          $row->fields[] = new Field\Image('../' . $thumbnail->getURL());
        } else {
          $row->fields[] = new Field\Image(new MimeTypeIcon($file->icon, 48), 48, 48);
        }
      }

      $row->fields[] = new Field\ListView(
        $listView = new ListView()
      );
      $listView->items[] = $item = new ListView\Item('', new Translated('Id'));
      $item->subitems[] = new ListView\SubItem\Text($file->id);
      $listView->items[] = $item = new ListView\Item('', new Translated('Name'));
      $item->subitems[] = new ListView\SubItem\Text($file->name);
      $listView->items[] = $item = new ListView\Item('', new Translated('Size'));
      $item->subitems[] = new ListView\SubItem\Bytes($file->size);
      $listView->items[] = $item = new ListView\Item('', new Translated('Type'));
      $item->subitems[] = new ListView\SubItem\Text($file->mimetype);
      if ($file->imageWidth > 0) {
        $listView->items[] = $item = new ListView\Item('', new Translated('Width'));
        $item->indentation = 1;
        $item->subitems[] = new ListView\SubItem\Text($file->imageWidth);
      }
      if ($file->imageHeight > 0) {
        $listView->items[] = $item = new ListView\Item('', new Translated('Height'));
        $item->indentation = 1;
        $item->subitems[] = new ListView\SubItem\Text($file->imageHeight);
      }

      $dialog->fields[] = $group = new Field\Group(
        new Translated('Properties')
      );
      $group->fields[] = $field = new Field\Input(new Translated('File Name'), self::PARAMETER_NAME);
      $group->fields[] = $field = new Field\Input\Date(
        new Translated('Created'),
        self::PARAMETER_CREATED,
        '',
        FALSE,
        DateFilter::DATE_MANDATORY_TIME
      );
      $group->fields[] = $field = new Field\Textarea(
        new Translated('Keywords'),
        self::PARAMETER_KEYWORDS,
        5,
        ''
      );
      $group->fields[] = $field = new Field\Input(new Translated('Sort'), self::PARAMETER_SORT);

      $dialog->fields[] = $group = new Field\Group(
        new Translated('Source')
      );
      $group->fields[] = $field = new Field\Input(new Translated('Text'), self::PARAMETER_SOURCE);
      $group->fields[] = $field = new Field\Input(new Translated('URL'), self::PARAMETER_SOURCE_URL);

      $dialog->fields[] = $group = new Field\Group(
        $this->papaya()->administrationLanguage->title,
        new LanguageIcon($this->papaya()->administrationLanguage->image)
      );
      $group->fields[] = $field = new Field\Input(new Translated('Title'), self::PARAMETER_TITLE);

      $group->fields[] = $field = new Field\Textarea\Richtext(
        new Translated('Description'),
        self::PARAMETER_DESCRIPTION,
        6,
        '',
        NULL,
        Field\Textarea\Richtext::RTE_SIMPLE
      );
      return $dialog;
    }

    public function createCondition() {
      return new RecordCondition($this->record());
    }
  }
}


