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
  use Papaya\UI\Content\Teasers\Factory as PageTeaserFactory;
  use Papaya\UI\Dialog;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;
  use Papaya\UI\Dialog\Field as DialogField;

  /**
   * @method EditableContent content()
   */
  trait TeasersAggregation {

    private $_teaserFactory;

    /**
     * @param Dialog $dialog
     * @param EditableContent $content
     * @return void
     */
    public function appendTeaserFieldsToDialog(Dialog $dialog, EditableContent $content) {
      $dialog->fields[] = $group = new DialogField\Group(
        new TranslatedText('Teasers')
      );
      $group->fields[] = $field = new DialogField\Select(
        new TranslatedText('Order'),
        Teasers::FIELD_TEASER_ORDER,
        new TranslatedList(PageTeaserFactory::ORDER_POSSIBILITIES),
        TRUE
      );
      $field->setDefaultValue(Teasers::_TEASER_DEFAULTS[Teasers::FIELD_TEASER_ORDER]);
      $group->fields[] = new DialogField\Input\Number(
        new TranslatedText('Limit'),
        Teasers::FIELD_TEASER_LIMIT,
        Teasers::_TEASER_DEFAULTS[Teasers::FIELD_TEASER_LIMIT]
      );
      $dialog->fields[] = $group = new DialogField\Group(
        new TranslatedText('Teaser Images')
      );
      $group->fields[] = $field = new DialogField\Select\Radio(
        new TranslatedText('Resize Mode'),
        Teasers::FIELD_TEASER_IMAGE_RESIZE,
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
      $field->setDefaultValue(Teasers::_TEASER_DEFAULTS[Teasers::FIELD_TEASER_IMAGE_RESIZE]);
      $group->fields[] = new DialogField\Input\Number(
        new TranslatedText('Width'),
        Teasers::FIELD_TEASER_IMAGE_WIDTH,
        Teasers::_TEASER_DEFAULTS[Teasers::FIELD_TEASER_IMAGE_WIDTH]
      );
      $group->fields[] = new DialogField\Input\Number(
        new TranslatedText('Height'),
        Teasers::FIELD_TEASER_IMAGE_HEIGHT,
        Teasers::_TEASER_DEFAULTS[Teasers::FIELD_TEASER_IMAGE_HEIGHT]
      );
    }

    /**
     * @param PageTeaserFactory|NULL $teasers
     * @return PageTeaserFactory
     */
    public function teaserFactory(PageTeaserFactory $teasers = NULL) {
      $content = $this->content()->withDefaults(Teasers::_TEASER_DEFAULTS);
      if (NULL !== $teasers) {
        $this->_teaserFactory = $teasers;
      } elseif (NULL === $this->_teaserFactory) {
        $this->_teaserFactory = new PageTeaserFactory(
          $content[Teasers::FIELD_TEASER_IMAGE_WIDTH],
          $content[Teasers::FIELD_TEASER_IMAGE_HEIGHT],
          $content[Teasers::FIELD_TEASER_IMAGE_RESIZE]
        );
      }
      return $this->_teaserFactory;
    }
  }

}
