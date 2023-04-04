<?php
/**
 * Plugin Name:         Price Badge for WooCommerce
 * Plugin URI:          https://ivans.my.id
 * Description:         Add Badge Name after price
 * Author:              IvanLux
 * Author URI:          https://ivans.my.id
 * Text Domain:         price-badge
 * Domain Path:         /languages
 * Version:             1.0.3
 * Requires at least:   5.5
 * Requires PHP:        7.1
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

        add_filter( 'woocommerce_get_price_html','pbdge_front_display' );

        if(!function_exists('pbdge_front_display')){
            function pbdge_front_display( $price )
            {
                global $post;
                
                $product_id = $post->ID;
                $product = wc_get_product( $product_id );     
                $options = get_option('badge_price_options'); 

                $start = $options["prefix_field_start"] ?? '';
                $end = $options["prefix_field_end"] ?? ''; 

                $time_start  = strtotime($start);
                $time_end =   strtotime($end);
                $now = strtotime(date("Y-m-d"));
    
                $enable = $options["prefix_field_enable"] ?? '';  
                
                $image_url = $options["prefix_field_image"] ?? '';

                if ( $enable == 1 ) {
                    if ( $now >= $time_start && $now <= $time_end )
                    {
                        if( array_intersect($options["prefix_field_tag"], $product->get_tag_ids()) ) {      
                            $text_to_add_after_price  = ' <sup class="text-red" style="color:'.$options["prefix_field_color"].'">'.$options["prefix_field_name"].'</sup>' ; 
                            if ($options["prefix_field_type"] == 'images' && $image_url == null) {
                                return $price . $text_to_add_after_price;
                            }
                            if ($options["prefix_field_type"] == 'images') {
                                return '
                                <div class="container">
                                   <table style="border: none">
                                        <thead style="border: none">
                                            <tr style="border: none">
                                                <td style="border: none;  width: 10px; padding: 0px">
                                                    '.$price.'
                                                </td>
                                                <td style="border: none">
                                                    <img src="'.$image_url.'" style="max-width: 30px;" />
                                                </td>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                ';
                            } elseif($options["prefix_field_type"] == 'textbox') {
                                return $price . $text_to_add_after_price;
                            } 
                            return $price;
                        }  
                    } else {
                        return $price;
                    }
                } else {
                    return $price;
                }
            }
        }


        if(!function_exists('pbdge_field_validation')){
            function pbdge_field_validation() {
                register_setting('prefix', 'badge_price_options', [
                    'type'              => 'array',
                    'sanitize_callback' => 'pbdge_message_validation',
                ]);
    
                add_settings_section(
                    'prefix_section_info',
                    __('', 'prefix'), 'pbdge_info_callback',
                    'prefix'
                );
    
                add_settings_field(
                    'prefix_field_enable',
                    __('Enable Badge', 'prefix'),
                    'pbdge_enable_cb',
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
                    'pbdge_start_cb',
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
                    'pbdge_end_cb',
                    'prefix',
                    'prefix_section_info',
                    array(
                        'label_for' => 'prefix_field_end',
                        'class'     => 'prefix_row',
                    )
                );

                add_settings_field(
                    'prefix_field_type',
                    __('Badge Type', 'prefix'),
                    'pbdge_type_cb',
                    'prefix',
                    'prefix_section_info',
                    array(
                        'label_for' => 'prefix_field_type',
                        'class'     => 'prefix_row',
                    )
                );

                add_settings_field(
                    'prefix_field_image',
                    __('Badge Image', 'prefix'),
                    'pbdge_image_cb',
                    'prefix',
                    'prefix_section_info',
                    array(
                        'label_for' => 'prefix_field_image',
                        'class'     => 'prefix_row badge_image_row',
                    )
                );
    
                add_settings_field(
                    'prefix_field_name',
                    __('Badge Text', 'prefix'),
                    'pbdge_name_cb',
                    'prefix',
                    'prefix_section_info',
                    array(
                        'label_for' => 'prefix_field_name',
                        'class'     => 'prefix_row badge_text_row',
                    )
                );
    
                add_settings_field(
                    'prefix_field_color',
                    __('Badge Color', 'prefix'),
                    'pbdge_color_cb',
                    'prefix',
                    'prefix_section_info',
                    array(
                        'label_for' => 'prefix_field_color',
                        'class'     => 'prefix_row badge_text_row',
                    )
                );
    
                add_settings_field(
                    'prefix_field_tag',
                    __('Tag Product', 'prefix'),
                    'pbdge_tag_cb',
                    'prefix',
                    'prefix_section_info',
                    array(
                        'label_for' => 'prefix_field_tag',
                        'class'     => 'prefix_row',
                    )
                );
    
    
            }
        }

        add_action('admin_init', 'pbdge_field_validation');

        if (!function_exists('pbdge_message_validation')) {  
            function pbdge_message_validation( $data )
            {
                $old_options = get_option( 'badge_price_options' );
                $has_errors = false;
    
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
    
                if (empty($data['prefix_field_tag'])) {
                    add_settings_error( 'prefix_messages', 'prefix_message', __('Product Tag is required', 'prefix'), 'error' );
                    $has_errors = true;
                }
    
                if ($has_errors) {
                    $data = $old_options;
                }
    
                return $data;
            }
        }

        if (!function_exists('pbdge_info_callback')) {
            function pbdge_info_callback( $args ) 
            {
                ?>
                    <p id="<?php echo esc_attr( $args['id']); ?>"><?php esc_html_e( 'Please fill in the form correctly', 'prefix' ); ?></p>
                <?php
            }
        }

        if (!function_exists('pdge_enable_cb')) {
            function pbdge_enable_cb( $args ) 
            {
                $options = get_option('badge_price_options');
                $enable = $options[$args['label_for']]  ?? '';
                ?>
                    <input class="regular-text" type="checkbox" id="<?php echo esc_attr($args['label_for']); ?>" name="badge_price_options[<?php echo esc_attr($args['label_for']); ?>]" value="1" <?php if ( $enable  == 1) { echo 'checked="checked"'; } ?>>
                <?php   
            }    
        }

        if (!function_exists('pbdge_start_cb')) {
            function pbdge_start_cb( $args ) 
            {
                $options = get_option('badge_price_options');
                ?>
                    <input class="regular-text" type="date" id="<?php echo esc_attr($args['label_for']); ?>" name="badge_price_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($options[$args['label_for']] ?? ''); ?>">
                <?php
            }
        }

        if (!function_exists('pbdge_end_cb')) {
            function pbdge_end_cb( $args ) 
            {
                $options = get_option('badge_price_options');
                ?>
                    <input class="regular-text" type="date" id="<?php echo esc_attr($args['label_for']); ?>" name="badge_price_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($options[$args['label_for']] ?? ''); ?>">
                <?php
            }    
        }

        if(!function_exists('pbdge_type_cb')){
            function pbdge_type_cb( $args )
            {
                $options = get_option('badge_price_options');
                $type = $options[$args['label_for']] ?? '';
                ?>
                    <select name="badge_price_options[<?php echo esc_attr($args['label_for'] ?? ''); ?>]" id="type">
                        <option>-- Choose Badge Type First --</option>
                        <option name="images" value="images" <?php if ( $type  == 'images') { echo 'selected="selected"'; } ?>>Image</option>
                        <option name="textbox" value="textbox" <?php if ( $type  == 'textbox') { echo 'selected="selected"'; } ?>>Text</option>
                    </select> 
                    <script>
                        jQuery(document).ready(function($) {                      
                            var x = document.getElementById('type').value

                            $('.badge_image_row').hide();
                            $('.badge_text_row').hide();

                            if (x == "images") {
                                $('.badge_image_row').show();
                            }
                            else if (x == "images") {
                                $('.badge_text_row').hide();
                            }

                            if (x == "textbox") {
                                $('.badge_text_row').show();
                            }
                            else if (x == "textbox") {
                                $('.badge_image_row').hide();
                            }

                            $('#type').change(function(){
                                if($('#type').val() == 'images') {
                                    $('.badge_image_row').show(); 
                                } else {
                                    $('.badge_image_row').hide(); 
                                } 
                                if($('#type').val() == 'textbox') {
                                    $('.badge_text_row').show();
                                } else {
                                    $('.badge_text_row').hide();
                                } 
                            });
                        });
                    </script>
                <?php 
            }
        }

        if (!function_exists('pbdge_image_cb')) {
            function pbdge_image_cb( $args ) 
            {
                $options = get_option('badge_price_options');
                $image_url       = esc_attr__( $options['prefix_field_image'] );
                $hasImage       = false;
                if (!is_null($image_url) && $image_url !== "" && $image_url > 0) {
                    $hasImage   = true;
                }
                wp_enqueue_media();
                ?>
                    <div class="badge_image_row">
                        <input type="hidden" name="badge_price_options[<?php echo esc_attr($args['label_for']); ?>]" id="option_image_id" class="regular-text" value="<?php echo $image_url; ?>">
                        <input id="upload_img-btn" type="button" name="upload-btn" class="button-secondary" value="Upload Image">
                        
                        <div id="logo_container">
                            <?php 
                                if ($hasImage) { 
                                    ?>
                                        <img class="logo" src="<?php echo $image_url; ?>" style="max-height: 50px; margin-top: 5px; margin-bottom: 5px" />
                                    <?php 
                                }

                            ?>                       
                    </div>
                        <input id="delete_img-btn" type="button" name="delete-btn" class="button-secondary" value="Remove Image" <?php if (!$hasImage) echo 'style="display: none"'; ?>>
                    </div>   
                <?php            
            }    
        }

        if (!function_exists('pbdge_name_cb')) {
            function pbdge_name_cb( $args ) 
            {
                $options = get_option('badge_price_options');
                ?>
                    <div id="badge_text_row">
                        <input class="regular-text" placeholder="e.g. Valentine Price" type="text" id="<?php echo esc_attr($args['label_for']); ?>" name="badge_price_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($options[$args['label_for']] ?? ''); ?>">
                    </div>
                <?php
            }    
        }


        if (!function_exists('pbdge_color_cb')) {
            function pbdge_color_cb( $args ) 
            {
                $options = get_option('badge_price_options');
                ?>
                    <div id="badge_text_row">
                        <input class="regular-text" placeholder="e.g. #ffa700 or Yellow" style="width: 5%;" type="color" id="<?php echo esc_attr($args['label_for']); ?>" name="badge_price_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo esc_attr($options[$args['label_for']] ?? ''); ?>">
                    </div> 
                <?php
            }
        }

        if (!function_exists('pbdge_tag_cb')) {  
            function pbdge_tag_cb( $args ) 
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
        }

        add_action('admin_footer', 'pbdge_media_selector_scripts');

        if (!function_exists('pbdge_media_selector_scripts')) {
            function pbdge_media_selector_scripts()
            {
	            ?>
                    <script type='text/javascript'>
                        jQuery(document).ready(function($) {

                            $("#delete_img-btn").on("click", function(e) {
                                e.preventDefault();
                                $('#logo_container').html("");
                                $('#option_image_id').val("");
                                $("#delete_img-btn").hide();
                            });

                            $('#upload_img-btn').on("click", function(e) {
                                e.preventDefault();
                                var $el = jQuery( this );
                                var optionImageFrame = wp.media({ 
                                    title: $el.data( 'choose' ),
                                    button: {
                                        text: $el.data( 'update' )
                                    },
                                    states: [
                                        new wp.media.controller.Library({
                                            title: $el.data( 'choose' ),
                                            filterable: 'all',
                                            // mutiple: true if you want to upload multiple files at once
                                            multiple: false
                                        })
                                    ]           
                                });

                                optionImageFrame.on('select', function(e){
                                    // This will return the selected image from the Media Uploader, the result is an object
                                    var uploaded_image = optionImageFrame.state().get('selection').first();
                                    // We convert uploaded_image to a JSON object to make accessing it easier
                                    // Output to the console uploaded_image
                                    var attachment = uploaded_image.toJSON();
                                    var image_url = attachment.url;
                                    var image_id  = attachment.id;

                                    $('#option_image_id').val(image_url);

                                    // localStorage.setItem("Gambar", image_url);

                                    if (image_url) {
                                        $('#logo_container').empty().append('<img class="logo" src="' + image_url + '" style="max-height: 50px; margin-top: 5px; margin-bottom: 5px"/>');         
                                    }
                                    
                                    $("#delete_img-btn").show();
                                });
                                optionImageFrame.open();
                            });
                        });
                    </script>
                <?php
            }
        }

        add_action( 'admin_enqueue_scripts', 'pbdge_scripts_callback' );

        if (!function_exists('pbdge_scripts_callback')) {
            function pbdge_scripts_callback()
            { 
                wp_enqueue_style('select2-custom-css', plugins_url('/assets/select2/css/select2.min.css', __FILE__), array());
                wp_enqueue_script('select2-custom-js', plugins_url('/assets/select2/js/select2.min.js', __FILE__), array('jquery'));
                wp_enqueue_style('field-css', plugins_url('/assets/css/field.css', __FILE__), array());
            }
        }

        if(!function_exists('pbdge_backend_page')){
            function pbdge_backend_page() 
            {
                add_menu_page(
                    'Price Badge for WooCommerce',
                    'Price Badge Options',
                    'manage_options',
                    'prefix',
                    'pbdge_save_page'
                );
            }
        }
        
        add_action('admin_menu', 'pbdge_backend_page');

        if (!function_exists('pbdge_save_page')) {
            function pbdge_save_page() 
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
        }
?>

