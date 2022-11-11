<?php
declare(strict_types=1);

namespace Papaya\Template\Engine\XSLT\Strings {

  abstract class Values {

    public static function upperCase(string $input): string {
      return self::getTransliterator('Any-Upper')->transliterate($input);
    }

    public static function lowerCase(string $input): string {
      return self::getTransliterator('Any-Lower')->transliterate($input);
    }

    private static function getTransliterator(string $rules): \Transliterator {
      return \Transliterator::create($rules);
    }
  }
}
