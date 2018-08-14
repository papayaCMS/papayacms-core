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

namespace Papaya\UI\Reference;
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
class Factory extends \Papaya\Application\BaseObject {

  private $_patterns = array(
    'page' => '(
      ^
      (?:(?P<category_id>\\d+)\\.)? # category id
      (?P<page_id>\\d+) # page id
      (?:\\.(?P<language>[a-zA-Z]{2,4}))? # language identifier
      (?:\\.(?P<mode>[a-z]+))? # output mode
      (?:\\?(?P<query>[^?#]*))? # query string
      (?:\\#(?P<fragment>[^?#]*))? # query string
      $
     )x',
    'absolute_url' => '(
      ^
      (?P<url>\w+://[^\\r\\n]*)
      $
     )x',
    'relative_url' => '(
      ^
      (?P<path>[^?#]+)
      (?P<query>[^#]+)?
      (?P<fragment>[^\\s]+)?
      $
     )x'
  );

  public function byString($string) {
    foreach ($this->_patterns as $type => $pattern) {
      if (preg_match($pattern, $string, $matches)) {
        switch ($type) {
          case 'page' :
            $reference = $this->createPageReference($matches);
          break;
          case 'absolute_url' :
            $reference = new \Papaya\UI\Reference(new \Papaya\URL($string));
          break;
          case 'relative_url' :
          default :
            $reference = new \Papaya\UI\Reference(
              clone $this->papaya()->request->getURL()
            );
            $reference->setRelative($string);
        }
        return $reference;
      }
    }
    return new \Papaya\UI\Reference(
      clone $this->papaya()->request->getURL()
    );
  }

  private function createPageReference($options) {
    $reference = new \Papaya\UI\Reference\Page();
    $reference->papaya($this->papaya());
    if (!empty($options['page_id'])) {
      $reference->setPageId($options['page_id']);
    }
    if (!empty($options['language'])) {
      $reference->setPageLanguage($options['language']);
    }
    if (!empty($options['category_id'])) {
      $reference->setCategoryId($options['category_id']);
    }
    if (!empty($options['mode'])) {
      $reference->setOutputMode($options['mode']);
    }
    return $reference;
  }
}