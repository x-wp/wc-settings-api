<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing
/**
 * Image_Select_Field class file.
 *
 * @package WooCommerce NestPay Payment Gateway
 * @subpackage WooCommerce\Admin
 */

namespace XWC\Admin;

/**
 * Outputs the image select field in WooCommerce settings API forms
 */
final class Image_Select_Field extends Custom_Field {
    protected function get_name(): string {
        return 'image_select';
    }

    protected function get_type(): string {
        return 'admin';
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

        $this->set_output();

        return \xwp_get_template_html(
            __DIR__ . '/Views/admin-html-image-select-template.php',
            array(
                'data'    => $data,
                'key'     => $key,
                'obj'     => $obj,
                'raw_key' => $raw_key,
            ),
        );
    }

    protected function get_css(): string {
        return <<<'CSS'
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
        CSS;
    }

    protected function get_js(): string {
        return <<<'JS'
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
    }
}
