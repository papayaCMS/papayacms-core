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

  use Papaya\Filter\RegEx as RegExFilter;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Plugin\Editable\Content as EditableContent;
  use Papaya\UI\Dialog;
  use Papaya\UI\Dialog\Field\Select\Radio as RadioGroupField;
  use Papaya\UI\Text\Placeholders as PlaceholdersText;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;
  use Papaya\UI\Dialog\Field as DialogField;

  trait QueryStringAggregation {

    public function appendQueryStringFieldsToDialog(Dialog $dialog, EditableContent $content) {
      $dialog->fields[] = $group = new DialogField\Group(new TranslatedText('QueryString'));
      $group->fields[] = $field = new RadioGroupField(
        new TranslatedText('Mode'),
        QueryString::FIELD_QUERYSTRING_MODE,
        new TranslatedList(
          [
            QueryString::QUERYSTRING_MODE_IGNORE => 'Ignore',
            QueryString::QUERYSTRING_MODE_APPEND => 'Append',
            QueryString::QUERYSTRING_MODE_TEMPLATE => 'Template'
          ]
        ),
        FALSE
      );
      $field->setDefaultValue(QueryString::_QUERYSTRING_DEFAULTS[QueryString::FIELD_QUERYSTRING_MODE]);
      $group->fields[] = $field = new DialogField\Input(
        new TranslatedText('Template'),
        QueryString::FIELD_QUERYSTRING_TEMPLATE,
        -1,
        QueryString::_QUERYSTRING_DEFAULTS[QueryString::FIELD_QUERYSTRING_TEMPLATE],
        new RegExFilter(
           '(^
             (?:
               (?:^|&)
               (?:[^=?#&]+(?:=([^=?#{}&]*|\\{[^=?#&{}]+\\}))?)
             )+
          $)Dix'
        )
      );
      $field->setHint(
        new TranslatedText(
          'Use {placeholder} for dynamic parameter values'
        )
      );
    }

    public function appendQueryStringToURL($targetURL) {
      $targetURL = (string)$targetURL;
      /** @var EditablePlugin $this */
      $content = $this->content()->withDefaults(QueryString::_QUERYSTRING_DEFAULTS);
      switch ($content) {
      case QueryString::QUERYSTRING_MODE_APPEND:
        $queryString = $this->papaya()->request->getURL()->getQuery();
        break;
      case QueryString::QUERYSTRING_MODE_TEMPLATE:
        $queryString = new PlaceholdersText(
          $content[QueryString::FIELD_QUERYSTRING_TEMPLATE],
          $this->papaya()->request->getParameters()->getList()
        );
        break;
      case QueryString::QUERYSTRING_MODE_IGNORE:
      default:
        return $targetURL;
      }
      $queryString = (string)$queryString;
      if ($queryString !== '') {
        $fragment = '';
        if (FALSE !== ($p = strpos($targetURL, '#'))) {
          $fragment = substr($targetURL, $p);
          $targetURL = substr($targetURL, 0, $p);
        }
        $separator = (FALSE !== strpos($targetURL, '?')) ? '&' : '?';
        return $targetURL.$separator.$queryString.$fragment;
      }
      return $targetURL;
    }
  }
}
