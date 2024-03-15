<?php

// print to footer

namespace FCP\Schema;
defined( 'ABSPATH' ) || exit;

add_action( 'wp_footer', function() {
    $values = settings_get()->values;
    echo $values['schema-for-all-pages'];
});
