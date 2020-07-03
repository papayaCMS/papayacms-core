<?php

namespace Papaya\Administration\Files\Commands {

  use Papaya\Administration\Settings\SettingsPage;
  use Papaya\BaseObject\Interfaces\StringCastable;
  use Papaya\Content\Language;
  use Papaya\Content\Tables;
  use Papaya\Database\Accessible;
  use Papaya\Iterator\Filter\Callback as CallbackFilter;
  use Papaya\Response;
  use Papaya\Response\Content\CSV as CSVResponseContent;
  use Papaya\UI\Dialog;
  use Papaya\UI\Text\Translated as TranslatedText;
  use Papaya\UI\Text\Translated\Collection as TranslatedList;
  use Papaya\Iterator\ArrayMapper;

  class ExportData extends \Papaya\UI\Control\Command\Dialog implements Accessible {

    use Accessible\Aggregation;

    const COMMAND_EXPORT = 'export_meta';
    const FIELD_COMMAND = 'cmd';
    const FIELD_LANGUAGES = 'languages';
    const FIELD_INCLUDE_TAGS = 'include_tags';
    const FIELD_INCLUDE_VERSIONS = 'include_versions';

    public function createDialog() {
      $dialog = parent::createDialog();
      $dialog->options->useToken = FALSE;
      $dialog->options->protectChanges = FALSE;
      $dialog->parameterGroup($this->parameterGroup());
      $dialog->hiddenFields->merge(
        [
          self::FIELD_COMMAND => self::COMMAND_EXPORT
        ]
      );
      $dialog->caption = new TranslatedText('Configure Export');
      $dialog->fields[] = new Dialog\Field\Select\Checkboxes(
        new TranslatedText('Languages'),
        self::FIELD_LANGUAGES,
        new ArrayMapper(
          new CallbackFilter(
            $this->papaya()->languages,
            function ($language) {
              return $language['is_content'];
            }
          ),
          'title'
        ),
        FALSE
      );
      $dialog->fields[] = new Dialog\Field\Select\Radio(
        new TranslatedText('Include tags'),
        self::FIELD_INCLUDE_TAGS,
        new TranslatedList([1 => 'On', 0 => 'Off']),
        FALSE
      );
      $dialog->fields[] = new Dialog\Field\Select\Radio(
        new TranslatedText('Include versions'),
        self::FIELD_INCLUDE_VERSIONS,
        new TranslatedList([1 => 'On', 0 => 'Off']),
        FALSE
      );
      $dialog->buttons[] = new Dialog\Button\Submit(new TranslatedText('Export CSV'));
      //$this->hideAfterSuccess(TRUE);
      $this->callbacks()->onExecuteSuccessful = function ($context, $dialog) {
        $this->exportCSV(
          $dialog->data[self::FIELD_LANGUAGES],
          $dialog->data[self::FIELD_INCLUDE_TAGS],
          $dialog->data[self::FIELD_INCLUDE_VERSIONS]
        );
      };
      return $dialog;
    }

    private function exportCSV(array $languages, $includeTags, $includeVersions) {
      $databaseAccess = $this->getDatabaseAccess();
      $databaseSyntax = $databaseAccess->syntax();
      foreach ($languages as $languageId) {
        $languageIdentifier = $this->papaya()->languages[$languageId]['identifier'];
        $fields[$languageIdentifier] = sprintf(
          ', %1$s.file_title title_%1$s, %1$s.file_description description_%1$s ',
          $languageIdentifier
        );
        $joins[$languageIdentifier] = sprintf(
          ' LEFT JOIN :table_%1$s AS %1$s ON (%1$s.file_id = files.file_id AND %1$s.lng_id = %2$d)',
          $languageIdentifier,
          $languageId
        );
      }
      if ($includeVersions) {
        $sql =
          "(SELECT files.file_id, files.current_version_id version_id, '✓' as is_current_version, files.file_name, 
             files.file_date, files.file_source, 
             files.file_source_url, files.file_keywords,
             ".$databaseSyntax->groupConcat($databaseSyntax->identifier('tag_translations.tag_title'), ', ')." as tags
             ".implode(' ', $fields)."
          FROM (:table_files AS files)
          LEFT JOIN :table_versions AS versions ON (versions.file_id = files.file_id)
          LEFT JOIN :table_tag_links AS links ON (links.link_type = 'media' AND links.link_id = files.file_id)
          LEFT JOIN :table_tags AS tags ON (tags.tag_id = links.tag_id)
          LEFT JOIN :table_tag_i18n AS tag_translations ON (tag_translations.tag_id = tags.tag_id AND tag_translations.lng_id = tags.default_lng_id)
             ".implode(' ', $joins).
          " GROUP BY files.file_id)
          UNION
          (SELECT files.file_id, versions.version_id, '' as is_current_version, versions.file_name, 
             versions.file_date, versions.file_source, 
             versions.file_source_url, versions.file_keywords,
             ".$databaseSyntax->groupConcat($databaseSyntax->identifier('tag_translations.tag_title'), ', ')." as tags
             ".implode(' ', $fields)."
          FROM (:table_files AS files)
          INNER JOIN :table_versions AS versions ON (versions.file_id = files.file_id AND versions.version_id != files.current_version_id)
          LEFT JOIN :table_tag_links AS links ON (links.link_type = 'media' AND links.link_id = files.file_id)
          LEFT JOIN :table_tags AS tags ON (tags.tag_id = links.tag_id)
          LEFT JOIN :table_tag_i18n AS tag_translations ON (tag_translations.tag_id = tags.tag_id AND tag_translations.lng_id = tags.default_lng_id)
             ".implode(' ', $joins).
          " GROUP BY files.file_id, versions.version_id)";
        $columns = [
          'file_id' => 'Id',
          'version_id' => 'Version',
          'is_current_version' => 'Current Version',
          'file_name' => 'Name',
          'file_date' => 'Date',
          'file_source' => 'Source',
          'file_source_url' => 'Source URL',
          'file_keywords' => 'Keywords'
        ];
      } else {
        $sql =
          "SELECT files.file_id, files.file_name, 
             files.file_date, files.file_source, 
             files.file_source_url, files.file_keywords,
             ".$databaseSyntax->groupConcat($databaseSyntax->identifier('tag_translations.tag_title'), ', ')." as tags
             ".implode(' ', $fields)."
          FROM (:table_files AS files)
          LEFT JOIN :table_tag_links AS links ON (links.link_type = 'media' AND links.link_id = files.file_id)
          LEFT JOIN :table_tags AS tags ON (tags.tag_id = links.tag_id)
          LEFT JOIN :table_tag_i18n AS tag_translations ON (tag_translations.tag_id = tags.tag_id AND tag_translations.lng_id = tags.default_lng_id)
             ".implode(' ', $joins).
          " GROUP BY files.file_id";
        $columns = [
          'file_id' => 'Id',
          'file_name' => 'Name',
          'file_date' => 'Date',
          'file_source' => 'Source',
          'file_source_url' => 'Source URL',
          'file_keywords' => 'Keywords'
        ];
      }
      if ($includeTags) {
        $columns['tags'] = 'Tags';
      }
      foreach ($languages as $languageId) {
        $languageIdentifier = $this->papaya()->languages[$languageId]['identifier'];
        $languageCode = $this->papaya()->languages[$languageId]['code'];
        $columns['title_'.$languageIdentifier] = 'Title ('.$languageCode.')';
        $columns['description_'.$languageIdentifier] = 'Description ('.$languageCode.')';
      }
      $statement = $databaseAccess->prepare($sql);
      $statement->addTableName('table_files', Tables::MEDIA_FILES);
      if ($includeVersions) {
        $statement->addTableName('table_versions', Tables::MEDIA_FILE_VERSIONS);
      }
      $statement->addTableName('table_tag_links', Tables::TAG_LINKS);
      $statement->addTableName('table_tags', Tables::TAGS);
      $statement->addTableName('table_tag_i18n', Tables::TAG_TRANSLATIONS);
      foreach ($joins as $languageCode => $join) {
        $statement->addTableName('table_'.$languageCode, Tables::MEDIA_FILE_TRANSLATIONS);
      }
      if ($result = $statement->execute()) {
        $response = $this->papaya()->response;
        $response->content(
          $content = new CSVResponseContent(
            $result,
            $columns
          )
        );
        $content->callbacks()->onMapField = function ($value, $field) {
          if ($field === 'file_date') {
            return gmdate(DATE_ATOM, $value);
          }
          return $value;
        };
        $response->setContentType('text/csv');
        $response->headers()->set('Content-Disposition', 'attachment; filename="media.csv"');
        $response->send(TRUE);
      }
    }
  }
}
