<?php

namespace app\helpers;

use yii\helpers\BaseStringHelper;

class StringHelper extends BaseStringHelper
{
  /**
   * The cache of snake-cased words.
   *
   * @var array
   */
  protected static $snakeCache = [];

  /**
   * The cache of camel-cased words.
   *
   * @var array
   */
  protected static $camelCache = [];

  /**
   * The cache of studly-cased words.
   *
   * @var array
   */
  protected static $studlyCache = [];

  /**
   * Convert a string to kebab case.
   *
   * @param  string  $value
   * @return string
   */
  public static function kebab($value)
  {
    return static::snake($value, '-');
  }

  /**
   * Convert a string to snake case.
   *
   * @param  string  $value
   * @param  string  $delimiter
   * @return string
   */
  public static function snake($value, $delimiter = '_')
  {
    $key = $value;

    if (isset(static::$snakeCache[$key][$delimiter])) {
      return static::$snakeCache[$key][$delimiter];
    }

    if (!ctype_lower($value)) {
      $value = preg_replace('/\s+/u', '', ucwords($value));

      $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
    }

    return static::$snakeCache[$key][$delimiter] = $value;
  }

  /**
   * Convert the given string to lower-case.
   *
   * @param  string  $value
   * @return string
   */
  public static function lower($value)
  {
    return mb_strtolower($value, 'UTF-8');
  }
}
