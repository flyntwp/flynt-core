<?php

namespace WPStarter\Helpers;

function extractNestedDataFromArray($args) {
  if (count($args) === 1) return '';
  $key = array_pop($args);
  if (count($args) > 1) {
    $data = extractNestedDataFromArray($args);
  } else {
    $data = $args[0];
  }
  $output = '';
  if($key === '*') {
    $output = $data;
  } elseif(isset($data) && is_array($data) && array_key_exists($key, $data)) {
    $output = $data[$key];
  }

  return $output;
}
