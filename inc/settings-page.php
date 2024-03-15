<?php

// the plugin settings page

namespace FCP\Schema;
defined( 'ABSPATH' ) || exit;


function settings_structure() {

    $fields_structure = [
        '' => [
            ['Schema for all pages', 'textarea', ['comment' => 'Insert the Schema code starting with <script> ending with </script>', 'placeholder' => '<script>...', 'rows' => '30', 'style' => 'width:
                100%']],
        ],
    ];

    return $fields_structure;
}

// settings page
add_action( 'admin_menu', function() {
	add_options_page( 'Schema Global', 'Schema', 'switch_themes', 'schema-global', function() {

        if ( !current_user_can( 'administrator' ) ) { return; } // besides the switch_themes above, it is still needed

        $settings = settings_get();

        ?>
        <div class="wrap">
            <h2><?php echo get_admin_page_title() ?></h2>
    
            <form action="options.php" method="POST">
                <?php
                    do_settings_sections( $settings->page ); // print fields of the page / tab
                    submit_button();
                    settings_fields( $settings->group ); // nonce
                ?>
            </form>
        </div>
        <?php
    });
});

// print the settings page
add_action( 'admin_init', function() {

    if ( !current_user_can( 'administrator' ) ) { return; }

    $settings = settings_get();
    // register and save the settings group
    register_setting( $settings->group, $settings->varname, __NAMESPACE__.'\settings_sanitize' ); // register, save, nonce


    // print settings
    global $pagenow;
    if ( $pagenow !== 'options-general.php' || $_GET['page'] !== 'schema-global' ) { return; } // get_current_screen() doesn't work here

    $fields_structure = settings_structure();

    $add_field = function( $title, $type = '', $atts = [] ) use ( $settings ) {

        $types = [ 'text', 'color', 'number', 'textarea', 'radio', 'checkbox', 'checkboxes', 'select', 'comment', 'image' ];
        $type = ( empty( $type ) || !in_array( $type, $types ) ) ? $types[0] : $type;
        $function = __NAMESPACE__.'\\'.$type;
        if ( !function_exists( $function ) ) { return; }
        $slug = $atts['slug'] ?? sanitize_title( $title );

        $attributes = (object) [
            'name' => $settings->varname.'['.$slug.']',
            'id' => $settings->varname . '--' . $slug,
            'value' => $slug ? ( $settings->values[ $slug ] ?? '' ) : '',
            'placeholder' => $atts['placeholder'] ?? '',
            'className' => $atts['className'] ?? '',
            'options' => $atts['options'] ?? [],
            'option' => $atts['option'] ?? '',
            'label' => $atts['label'] ?? '',
            'comment' => $atts['comment'] ?? '',
            'rows' => $atts['rows'] ?? 10,
            'cols' => $atts['cols'] ?? 50,
            'style' => $atts['style'] ?? '',
        ];

        add_settings_field(
            $slug,
            $title,
            function() use ( $attributes, $function ) { call_user_func( $function, $attributes ); },
            $settings->page,
            $settings->section
        );
    };

    $add_section = function( $section, $title, $slug = '' ) use ( &$settings, $add_field ) {

        $settings->section = $slug ?: sanitize_title( $title );
        add_settings_section( $settings->section, $title, '', $settings->page );

        foreach ( $section as $v ) {
            $add_field( $v[0], $v[1], $v[2] ?? [] );
        }
    };

    // add full structure
    foreach ( $fields_structure as $k => $v ) {
        $add_section( $v, $k );
    }

});

function settings_sanitize( $values ){

    //print_r( $values ); exit;
    $fields_structure = settings_structure();
    $get_default_values = get_default_values();
    
    $filters = [
        'integer' => function($v) {
            return trim( $v ) === '' ? '' : ( intval( $v ) ?: '' ); // 0 not allowed
        },
        'css' => function($v) {
            $css = $v;

            // try to escape tags inside svg with url-encoding
            if ( strpos( $css, '<' ) !== false && preg_match( '/<\/?\w+/', $css ) ) {
                // the idea is taken from https://github.com/yoksel/url-encoder/
                $svg_sanitized = preg_replace_callback( '/url\(\s*(["\']*)\s*data:\s*image\/svg\+xml(.*)\\1\s*\)/', function($m) {
                    return 'url('.$m[1].'data:image/svg+xml'
                        .preg_replace_callback( '/[\r\n%#\(\)<>\?\[\]\\\\^\`\{\}\|]+/', function($m) {
                            return urlencode( $m[0] );
                        }, urldecode( $m[2] ) )
                        .$m[1].')';
                }, $css );

                if ( $svg_sanitized !== null ) {
                    $css = $svg_sanitized;
                }
            }

            return $css; // sanitize_text_field is applied to textareas by field type
        }
    ];

    $trials = [];
    foreach ( $fields_structure as $v ) {
        foreach ( $v as $w ) {
            $atts = $w[2] ?? [];
            $slug = $atts['slug'] ?? sanitize_title( $w[0] );
            $trials[ $slug ] = (object) array_filter( [
                'type' => $w[1] ?? 'text',
                'options' => $atts['options'] ?? null,
                'option' => $atts['option'] ?? null,
                'filter' => $atts['filter'] ?? null,
                'default' => $get_default_values[ $slug ] ?? null, // the fallback value ++for future 'must be filled' values
            ]);
        }
    }
    //print_r( [$values, $trials] ); exit;
	foreach( $values as $k => &$v ){
        $trial = $trials[ $k ];

        if ( !empty( $trial->filter ) ) {
            $v = $filters[ $trial->filter ] ? $filters[ $trial->filter ]( $v ) : $v;
        }
        if ( !empty( $trial->options ) ) {
            if ( is_array( $v ) ) {
                $v = array_intersect( $v, array_keys( $trial->options ) );
            } else {
                $v = in_array( $v, array_keys( $trial->options ) ) ? $v : '';
            }
        }
        if ( !empty( $trial->option ) ) {
            $v = $v === $trial->option ? $v : '';
        }
        if ( $trial->type === 'text' ) { $v = sanitize_text_field( $v ); }
        if ( $trial->type === 'textarea' ) { $v = sanitize_textarea_field( $v ); }

	}
    //print_r( [$values, $trials] ); exit;

	return $values;
}