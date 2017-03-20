<?php
add_filter('Flynt/DataFilters/SingleComponent/foo', function ($data) {
  // cannot test anything that's in here, because WP ain't real
    return $data;
});
