<?php
add_filter('WPStarter/DataFilters/SingleModule/foo', function ($data) {
  // cannot test anything that's in here, because WP ain't real
  return $data;
});
