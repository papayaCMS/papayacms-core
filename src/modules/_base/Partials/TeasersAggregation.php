<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Modules\Core\Partials {

  use Papaya\Plugin\Editable\Content as EditableContent;
  use Papaya\CMS\Output\Teasers\Factory as PageTeaserFactory;
  use Papaya\UI\Dialog;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;
  use Papaya\UI\Dialog\Field as DialogField;

  /**
   * @method EditableContent content(\Papaya\Plugin\Editable\Content $content = NULL)
   */
  trait TeasersAggregation {

    private $_teaserFactory;

    /**
     * @param Dialog $dialog
     * @param EditableContent $content
     * @return void
     */
    public function appendTeasersFieldsToDialog(Dialog $dialog, EditableContent $content) {
      $defaults = $this->getTeasersDefaultContent();
      $dialog->fields[] = $group = new DialogField\Group(
        new TranslatedText('Teasers')
      );
      $group->fields[] = $field = new DialogField\Select(
        new TranslatedText('Order'),
        Teasers::FIELD_TEASERS_ORDER,
        new TranslatedList(PageTeaserFactory::ORDER_POSSIBILITIES),
        TRUE
      );
      $field->setDefaultValue($defaults[Teasers::FIELD_TEASERS_ORDER]);
      $group->fields[] = new DialogField\Input\Number(
        new TranslatedText('Limit'),
        Teasers::FIELD_TEASERS_LIMIT,
        $defaults[Teasers::FIELD_TEASERS_LIMIT]
      );
      $dialog->fields[] = $group = new DialogField\Group(
        new TranslatedText('Teaser Images')
      );
      $group->fields[] = $field = new DialogField\Select\Radio(
        new TranslatedText('Resize Mode'),
        Teasers::FIELD_TEASERS_IMAGE_RESIZE,
        new TranslatedList(
          [
            'max' => 'Maximum',
            'min' => 'Minimum',
            'mincrop' => 'Minimum crop',
            'abs' => 'Absolute',
          ]
        ),
        TRUE
      );
      $field->setDefaultValue($defaults[Teasers::FIELD_TEASERS_IMAGE_RESIZE]);
      $group->fields[] = new DialogField\Input\Number(
        new TranslatedText('Width'),
        Teasers::FIELD_TEASERS_IMAGE_WIDTH,
        $defaults[Teasers::FIELD_TEASERS_IMAGE_WIDTH]
      );
      $group->fields[] = new DialogField\Input\Number(
        new TranslatedText('Height'),
        Teasers::FIELD_TEASERS_IMAGE_HEIGHT,
        $defaults[Teasers::FIELD_TEASERS_IMAGE_HEIGHT]
      );
    }

    /**
     * @return array
     */
    public function getTeasersDefaultContent() {
      return Teasers::_TEASERS_DEFAULTS;
    }

    /**
     * @param PageTeaserFactory|NULL $teasers
     * @return PageTeaserFactory
     */
    public function teaserFactory(PageTeaserFactory $teasers = NULL) {
      $content = $this->content()->withDefaults($this->getTeasersDefaultContent());
      if (NULL !== $teasers) {
        $this->_teaserFactory = $teasers;
      } elseif (NULL === $this->_teaserFactory) {
        $this->_teaserFactory = new PageTeaserFactory(
          $content[Teasers::FIELD_TEASERS_IMAGE_WIDTH],
          $content[Teasers::FIELD_TEASERS_IMAGE_HEIGHT],
          $content[Teasers::FIELD_TEASERS_IMAGE_RESIZE],
          TRUE
        );
      }
      return $this->_teaserFactory;
    }
  }

}
