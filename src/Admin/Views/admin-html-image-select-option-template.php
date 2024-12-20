<?php
/**
 * Image select option template.
 *
 * @package eXtended WooCommerce
 * @version 3.7.3
 *
 * @var string              $img_url      Image URL.
 * @var array<string>       $classes      CSS classes.
 * @var string              $option_value Option value.
 * @var array<string,mixed> $opt          Option data.
 * @var array<string,mixed> $data         Field data.
 */

defined( 'ABSPATH' ) || exit;
?>

<div
    style="width: <?php echo \esc_attr( $data['selector_width'] ); ?>; height: auto"
    class="<?php echo \esc_attr( \implode( ' ', $classes ) ); ?>"
    data-option="<?php echo \esc_attr( $option_value ); ?>"
    data-tip="<?php echo \esc_attr( $opt['title'] ); ?>"
>
    <img
        src="<?php echo \esc_url( $img_url ); ?>"
        alt="<?php echo \esc_attr( $opt['title'] ); ?>"
    />
</div>
