<?php
/**
 * Image_Select_Field class file.
 *
 * @package WooCommerce NestPay Payment Gateway
 * @subpackage WooCommerce\Admin
 */

namespace XWC\Admin;

use XWP\Helper\Traits\Singleton;

/**
 * Outputs the image select field in WooCommerce settings API forms
 */
final class Image_Select_Field {
    use Singleton;

    /**
     * Did output flag
     *
     * @var bool
     */
    private bool $did_output = false;

    /**
     * Constructor
     */
    private function __construct() {
        \add_filter( 'woocommerce_generate_image_select_html', array( $this, 'output_field' ), 99, 4 );
        \add_filter( 'admin_footer', array( $this, 'output_css' ), 99, 0 );
        \add_filter( 'admin_footer', array( $this, 'output_js' ), 99, 0 );
    }

    /**
     * Renders image select field
     *
     * @param  string           $html  Empty string.
     * @param  string           $raw_key   Field key.
     * @param  array            $data Field data.
     * @param  \WC_Settings_API $obj   Settings API object.
     * @return string
     */
    public function output_field( $html, string $raw_key, array $data, \WC_Settings_API $obj ): string {
        $key = $obj->get_field_key( $raw_key );

        $defaults = array(
            'class'             => '',
            'css'               => '',
            'custom_attributes' => array(),
            'description'       => '',
            'desc_tip'          => false,
            'disabled'          => false,
            'options'           => array(),
            'selector_width'    => '50px',
            'title'             => '',
        );
        $data     = \wp_parse_args( $data, $defaults );

        \ob_start();
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
                <?php foreach ( $data['options'] as $option_value => $opt ) : ?>
                    <?php
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
                <?php endforeach; ?>
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
        <?php

        $this->did_output = true;

        $html = \ob_get_clean();

        return $html;
    }

    /**
     * Adds custom styles
     */
    public function output_css() {
        if ( ! $this->did_output ) {
            return;
        }
        ?>
        <style>
            .image-select-field {
                display:flex;
                gap: 10px;
            }

            .image-select-field .image-select-option {
                height:auto;
                cursor: pointer;
                padding: 5px;
                background-color: #fff;
                border: 1px solid #ddd;
            }

            .image-select-field .image-select-option.selected {
                border: 1px solid #007cba;
            }

            .image-select-field .image-select-option.disabled {
                cursor: not-allowed;
                opacity: 0.9;
            }

            .image-select-option img {
                width: 100%;
                height: auto;
                display: block;
                margin: 0 auto;
            }

            .image-select-field .image-select-option.disabled img {
                filter: grayscale(0.75);
            }
        </style>
        <?php
    }

    /**
     * Adds custom scripts
     */
    public function output_js() {
        if ( ! $this->did_output ) {
            return;
        }

        echo '<' .'script>'; // phpcs:ignore
        echo <<<JS
        jQuery(function($){
            $('.image-select-option').tipTip({
                'attribute': 'data-tip',
                'fadeIn': 50,
                'fadeOut': 50,
                'delay': 200,
                'keepAlive': true
            });

            $('.image-select-option').click(function() {
                if ($(this).hasClass('disabled')) {
                    return;
                }

                $(this).siblings().removeClass('selected');
                $(this).addClass('selected');
                $(this).closest('.image-select-field').find('input').val($(this).attr('data-option'));
            });
        });
        JS;
        echo '</script>';
    }
}
