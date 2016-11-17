<?php

namespace WPStarter;

class Helpers {
  public static function extractNestedDataFromArray($args = []) {
    if (count($args) === 0) return '';
    if (count($args) === 1) return $args[0];
    $key = array_pop($args);
    if (count($args) > 1) {
      $data = self::extractNestedDataFromArray($args);
    } else {
      $data = $args[0];
    }
    $output = '';
    if (is_array($key) || is_object($key)) {
      $output = $key;
    } elseif (is_array($data) && array_key_exists($key, $data)) {
      $output = $data[$key];
    } elseif (is_object($data) && property_exists($data, $key)) {
      $output = $data->$key;
    }
    return $output;
  }
}
