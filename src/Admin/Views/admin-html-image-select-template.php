<?php
/**
 * Image select field template.
 *
 * @package eXtended WooCommerce
 * @version 3.7.3
 *
 * @var string              $key     Field key.
 * @var string              $raw_key Field key.
 * @var array<string,mixed> $data    Field data.
 * @var WC_Settings_API     $obj     Settings API object.
 */

defined( 'ABSPATH' ) || exit;
?>

<tr valign="top">
    <th scope="row" class="titledesc">
        <label for="<?php echo \esc_attr( $key ); ?>">
            <?php
            echo \wp_kses_post( $data['title'] );
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $obj->get_tooltip_html( $data );
            ?>
        </label>
    </th>
    <td class="forminp">
        <legend class="screen-reader-text">
            <span><?php echo \wp_kses_post( $data['title'] ); ?></span>
        </legend>
        <div data-input-name="<?php echo \esc_attr( $key ); ?>" class="image-select-field">
            <?php
            foreach ( $data['options'] as $option_value => $opt ) {
                /**
                 * Filters the image option URL
                 *
                 * @param  string $image_url Image URL.
                 * @param  string $key       Option key.
                 * @return string            Modified image URL
                 *
                 * @since 2.2.2
                 */
                $img_url = \apply_filters( 'xwc_image_select_option_image_url', $opt['image'], $key );
                $classes = array( 'image-select-option' );

                if ( $obj->get_option( $raw_key, '' ) === $option_value ) {
                    $classes[] = 'selected';
                }

                if ( $opt['disabled'] ?? false ) {
                    $classes[] = 'disabled';
                }

                include __DIR__ . '/admin-html-image-select-option-template.php';
            }
            ?>
            <input
                id="<?php echo \esc_attr( $key ); ?>"
                name="<?php echo \esc_attr( $key ); ?>"
                value="<?php echo \esc_attr( $obj->get_option( $raw_key, '' ) ); ?>"
                type="hidden"
            />
        </div>
        <?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $obj->get_description_html( $data );
        ?>
    </td>
</tr>
