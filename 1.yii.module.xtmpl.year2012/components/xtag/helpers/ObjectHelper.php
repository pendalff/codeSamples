<?php
/**
 *
 * @author: sem
 * Date: 28.03.12
 * Time: 21:44
 */
class ObjectHelper
{
  /**
   * Analoge Arr::path for object.
   * @static
   * @param $object
   * @param $path
   * @param null $default
   * @param string $delimiter
   * @param bool $allowArray
   * @return array|mixed|null
   */
  public static function path($object, $path, $default = NULL, $delimiter = '.', $allowArray = true)
  {
    if ($allowArray && is_array($object)) {
      return Arr::path($object, $path, $default, $delimiter);
    }

    if (!is_object($object)) {
      return $default; // This is not an object!
    }

    if (is_array($path)) {
      $keys = $path; // The path has already been separated into keys
    } else {

      if (isset($object->$path)) {
        return $object->$path; // No need to do extra processing
      }

      if ($delimiter === NULL) {
        // Use the default delimiter
        $delimiter = '.';
      }

      // Remove starting delimiters and spaces
      $path = ltrim($path, "{$delimiter} ");

      // Remove ending delimiters, spaces, and wildcards
      $path = rtrim($path, "{$delimiter} *");

      // Split the keys by delimiter
      $keys = explode($delimiter, $path);
    }

    do {
      $key = array_shift($keys);
      //$key = preg_replace('/[^a-z\d]/i', '', $key);

      if (isset($object->$key)) {

        if ($keys) {
          if (isset($object->$key) && is_object($object->$key)) {
            // Dig down into the next part of the path
            $object = $object->$key;
          } else {
            // Unable to dig deeper
            break;
          }
        } else {

          return $object->$key; // Found the path requested
        }
      } elseif ($key === '*') {
        // Handle wildcards
        $values = array();
        foreach (get_object_vars($object) as $arr) {
          if ($value = Arr::path($arr, implode('.', $keys))) {
            $values[] = $value;
          }
        }

        if ($values) {
          // Found the values requested
          return $values;
        } else {
          // Unable to dig deeper
          break;
        }
      } else {
        // Unable to dig deeper
        break;
      }
    } while ($keys);

    return $default; // Unable to find the value requested
  }
}
