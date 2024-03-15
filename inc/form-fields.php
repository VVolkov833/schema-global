<?php

// form printing functions

namespace FCP\Schema;
defined( 'ABSPATH' ) || exit;

function textarea($a) {
    ?>
    <textarea
        name="<?php echo esc_attr( $a->name ) ?>"
        id="<?php echo esc_attr( $a->id ?? $a->name ) ?>"
        placeholder="<?php echo esc_attr( $a->placeholder ?? '' ) ?>"
        class="<?php echo esc_attr( $a->className ?? '' ) ?>"
        rows="<?php echo esc_attr( $a->rows ) ?>"
        cols="<?php echo esc_attr( $a->cols ) ?>"
        style="<?php echo esc_attr( $a->style ) ?>"
    ><?php echo esc_textarea( $a->value ?? '' ) ?></textarea>
    <?php echo isset( $a->comment ) ? '<p><em>'.esc_html( $a->comment ).'</em></p>' : '' ?>
    <?php
}