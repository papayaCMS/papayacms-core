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

  interface Teasers {

    const FIELD_TEASER_ORDER = 'teaser-order';
    const FIELD_TEASER_LIMIT = 'teaser-limit';
    const FIELD_TEASER_IMAGE_RESIZE = 'teaser-image-resize-mode';
    const FIELD_TEASER_IMAGE_WIDTH = 'teaser-image-width';
    const FIELD_TEASER_IMAGE_HEIGHT = 'teaser-image-height';

    const _TEASER_DEFAULTS = [
      self::FIELD_TEASER_ORDER => PageTeaserFactory::ORDER_POSITION_ASCENDING,
      self::FIELD_TEASER_LIMIT => 10,
      self::FIELD_TEASER_IMAGE_RESIZE => 'max',
      self::FIELD_TEASER_IMAGE_WIDTH => 0,
      self::FIELD_TEASER_IMAGE_HEIGHT => 0
    ];

    /**
     * @param Dialog $dialog
     * @param EditableContent $content
     * @return void
     */
    public function appendTeaserFieldsToDialog(Dialog $dialog, EditableContent $content);

    /**
     * @param PageTeaserFactory|NULL $teasers
     * @return PageTeaserFactory
     */
    public function teaserFactory(PageTeaserFactory $teasers = NULL);
  }
}


