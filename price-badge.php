<?php
/**
 * Plugin Name:         Price Badge for WooCommerce
 * Plugin URI:          https://ivans.my.id
 * Description:         Add Badge Name after price
 * Author:              Ivan S Nawawi
 * Text Domain:         price-badge
 * Domain Path:         /languages
 * Version:             1.0.0
 * Requires at least:   5.5
 * Requires PHP:        7.3
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

        add_filter( 'woocommerce_get_price_html','badge_after_price' );
        function badge_after_price( $price )
        {
            global $post;
            
            $product_id = $post->ID;
            $product = wc_get_product( $product_id );     

            $options = get_option('badge_price_options');

            $start = $options["prefix_field_start"];
            $end = $options["prefix_field_end"]; 

            $time_start  = strtotime($start);
            $time_end =   strtotime($end);
            $now = strtotime(date("Y-m-d"));

            $enable = $options["prefix_field_enable"] ?? '';      

            if ( $enable == 1 ) {
                if ( $now >= $time_start && $now <= $time_end )
                {
                    if( array_intersect($options["prefix_field_tag"], $product->get_tag_ids()) ) {      
                        $text_to_add_after_price  = ' <sup class="text-red" style="color:'.$options["prefix_field_color"].'">'.$options["prefix_field_name"].'</sup>' ; 
                        return $price .   $text_to_add_after_price;
                    } else {
                        return $price;
                    }  
                } else {
                    return $price;
                }
            } else {
                return $price;
            }
        }

        function prefix_settings_init() {
            register_setting('prefix', 'badge_price_options', [
                'type'              => 'array',
                'sanitize_callback' => 'prefix_sanitize_options',
            ]);

            add_settings_section(
                'prefix_section_info',
                __('', 'prefix'), 'prefix_section_info_callback',
                'prefix'
        );

        add_settings_field(
            'prefix_field_enable',
            __('Enable Badge', 'prefix'),
            'enable_cb',
            'prefix',
            'prefix_section_info',
            array(
                'label_for' => 'prefix_field_enable',
                'class'     => 'prefix_row',
            )
        );
            
            add_settings_field(
                'prefix_field_start',
                __('Start', 'prefix'),
                'start_cb',
                'prefix',
                'prefix_section_info',
                array(
                    'label_for' => 'prefix_field_start',
                    'class'     => 'prefix_row',
                )
            );

            add_settings_field(
                'prefix_field_end',
                __('End', 'prefix'),
                'end_cb',
                'prefix',
                'prefix_section_info',
                array(
                    'label_for' => 'prefix_field_end',
                    'class'     => 'prefix_row',
                )
            );

            add_settings_field(
                'prefix_field_name',
                __('Badge Name', 'prefix'),
                'name_cb',
                'prefix',
                'prefix_section_info',
                array(
                    'label_for' => 'prefix_field_name',
                    'class'     => 'prefix_row',
                )
            );

            add_settings_field(
                'prefix_field_color',
                __('Badge Color', 'prefix'),
                'color_cb',
                'prefix',
                'prefix_section_info',
                array(
                    'label_for' => 'prefix_field_color',
                    'class'     => 'prefix_row',
                )
            );

            add_settings_field(
                'prefix_field_tag',
                __('Tag Product', 'prefix'),
                'tag_cb',
                'prefix',
                'prefix_section_info',
                array(
                    'label_for' => 'prefix_field_tag',
                    'class'     => 'prefix_row',
                )
            );


        }
        add_action('admin_init', 'prefix_settings_init');

        function prefix_sanitize_options( $data )
        {
            $old_options = get_option( 'badge_price_options' );
            $has_errors = false;

            if (empty($data['prefix_field_name'])) {
                add_settings_error( 'prefix_messages', 'prefix_message', __('Name is required', 'prefix'), 'error' );
                $has_errors = true;
            }


            if (empty($data['prefix_field_start'])) {
                add_settings_error( 'prefix_messages', 'prefix_message', __('Start Date is required', 'prefix'), 'error' );
                $has_errors = true;
            }

            if (empty($data['prefix_field_end'])) {
                add_settings_error( 'prefix_messages', 'prefix_message', __('End Date is required', 'prefix'), 'error' );
                $has_errors = true;
            }

            if ($data['prefix_field_end'] < $data['prefix_field_start']) {
                add_settings_error( 'prefix_messages', 'prefix_message', __('Tanggal end tidak boleh lebih kecil dari tanggal start', 'prefix'), 'error' );
                $has_errors = true;
            }

            if ($has_errors) {
                $data = $old_options;
            }

            return $data;
        }

        function prefix_section_info_callback( $args ) 
        {
            ?>
                <p id="<?php echo esc_attr( $args['id']); ?>"><?php esc_html_e( 'Please fill in the form correctly', 'prefix' ); ?></p>
            <?php
        }

        function enable_cb( $args ) 
        {
            $options = get_option('badge_price_options');
            $enable = $options[$args['label_for']]  ?? '';
            ?>
                <input class="regular-text" type="checkbox" id="<?php echo esc_attr($args['label_for']); ?>" name="badge_price_options[<?php echo esc_attr($args['label_for']); ?>]" value="1" <?php if ( $enable  == 1) { echo 'checked="checked"'; } ?>>
            <?php
            
        }

        function start_cb( $args ) 
        {
            $options = get_option('badge_price_options');
            ?>
                <input class="regular-text" type="date" id="<?php echo esc_attr($args['label_for']); ?>" name="badge_price_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($options[$args['label_for']] ?? ''); ?>">
            <?php
        }

        function end_cb( $args ) 
        {
            $options = get_option('badge_price_options');
            ?>
                <input class="regular-text" type="date" id="<?php echo esc_attr($args['label_for']); ?>" name="badge_price_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($options[$args['label_for']] ?? ''); ?>">
            <?php
        }

        function name_cb( $args ) 
        {
            $options = get_option('badge_price_options');
            ?>
                <input class="regular-text" placeholder="e.g. Valentine Price" type="text" id="<?php echo esc_attr($args['label_for']); ?>" name="badge_price_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($options[$args['label_for']] ?? ''); ?>">
            <?php
        }

        function color_cb( $args ) 
        {
            $options = get_option('badge_price_options');
            ?>
                <input class="regular-text" placeholder="e.g. #ffa700 or Yellow" style="width: 5%;" type="color" id="<?php echo esc_attr($args['label_for']); ?>" name="badge_price_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($options[$args['label_for']] ?? ''); ?>">
            <?php
        }

        function tag_cb( $args ) 
        {
            $options = get_option('badge_price_options');
            $terms = get_terms('product_tag');
            $tag_select = $options[$args['label_for']] ?? $terms;
            ?>
                <select multiple="multiple" id="tag_select" name="badge_price_options[<?php echo esc_attr($args['label_for']); ?>][]">
                    <option value="">-- Choose Tag --</option>
                    <?php
                        $terms = get_terms('product_tag'); 
                    ?>
                    <?php 
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                        foreach( $terms as $term ) { 
                    ?>
                        <?php 
                            $select = in_array( $term->term_id, $tag_select ) ? 'selected="selected"' : ''; 
                        ?>
                            <option value="<?php echo esc_html( $term->term_id ); ?>" <?php echo $select; ?> > <?php echo esc_html($term->name); ?></option>
                        <?php 
                        } 
                    }
            ?>
                </select>
                    <script>
                        jQuery(document).ready(function($) {
                            $('#tag_select').select2();
                        });
                    </script>
            <?php
        }

        add_action( 'admin_enqueue_scripts', 'scripts_callback' );

        function scripts_callback()
        {
            //Add the Select2 CSS file
            wp_enqueue_style( 'select2-custom-css', '/wp-content/plugins/wc-price-badge/select2/css/select2.min.css', array());
            //Add the Select2 JavaScript file
            wp_enqueue_script('select2-custom-js', '/wp-content/plugins/wc-price-badge/select2/js/select2.min.js', array('jquery'));
        }

        function options_page() 
        {
            add_menu_page(
                'Price Badge for WooCommerce',
                'Price Badge Options',
                'manage_options',
                'prefix',
                'page_html'
            );
        }
        
        add_action('admin_menu', 'options_page');

        function page_html() 
        {
            if ( !current_user_can('manage_options') ) {
                return;
            }

            if ( isset($_GET['settings-updated']) && empty(get_settings_errors('prefix_messages')) ) {
                add_settings_error('prefix_messages', 'prefix_message', __('Settings Saved', 'prefix'), 'updated');
            }

            settings_errors('prefix_messages');
            ?>
                <div class="wrap">
                    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                    <form action="options.php" method="post">
                        <?php
                        settings_fields('prefix');
                        do_settings_sections('prefix');
                        submit_button('Save Settings');
                        ?>
                    </form>
                </div>
            <?php
        }
?>