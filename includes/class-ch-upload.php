<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

final class CH_Upload {

    /**
     * CH_Upload Constructor.
     */
    public function __construct() {
        $this->init_hooks();
        do_action( 'ch_upload_loaded' );
    }


    private function init_hooks() {
        // Create Page in Admin
        add_action('admin_menu', array( $this,'add_admin_menu_item'));

        add_action( 'admin_print_styles-woocommerce_page_ch_product_upload_page', array( $this,'includes_styles') );
        add_action( 'admin_print_scripts-woocommerce_page_ch_product_upload_page', array( $this,'includes_scripts') );

        add_action( 'wp_ajax_nopriv_add_product', array( $this,'add_product') );
        add_action( 'wp_ajax_add_product', array( $this,'add_product') );

    }

    public function includes_scripts() {
        wp_register_script( 'ch_uploads',  plugin_dir_url( CH_UPLOAD_PLUGIN_FILE ) . 'assets/js/scripts.js', array('jquery' ), '1.0.0', true );
        wp_enqueue_script( 'ch_uploads' );

        wp_localize_script( 'ch_uploads', 'ch_uploads', array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ));

        wp_register_script( 'bootstrap_js',  'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js', array('jquery' ), '1.0.0', true );
        wp_enqueue_script( 'bootstrap_js' );

    }

    public function includes_styles() {
        wp_register_style('bootstrap_css', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css');
        wp_enqueue_style('bootstrap_css');
    }

    public function add_admin_menu_item() {
        //create new woocommerce menu item
        add_submenu_page( 'woocommerce', 'Crowdyhouse Upload', 'Crowdyhouse Upload', 'edit_pages', 'ch_product_upload_page',  array( $this, 'create_page' ) );
    }

    public function create_page() {

        if (isset( $_POST['action']) &&  $_POST['action'] == "ch_upload_csv") {

            if ( is_uploaded_file( $_FILES['csvfile']['tmp_name'] ) ) {

                    $delimiter = ",";
                    $csv = array_map(function($d) use ($delimiter) {
                        return str_getcsv($d, $delimiter);
                    }, file($_FILES['csvfile']['tmp_name']));

                    array_walk($csv, function(&$a) use ($csv) {
                      $a = array_combine($csv[0], $a);
                    });
                    array_shift($csv); # remove column header

                echo '<script> var chUploadCSV=' .  json_encode($csv) . ';</script>';

            } else {

            }
        }

        ?>
       <div class="wrap">
            <h1>Crowdyhouse Upload Products</h1>
            
            <h4>Upload Simple Products</h4>
            <p>Download Example File: <a href="<?php echo plugin_dir_url( CH_UPLOAD_PLUGIN_FILE ) . 'files/example_simple_upload.csv' ?> " download >Here</a></p>

            <form method="POST" action="<?php echo admin_url( 'admin.php?page=ch_product_upload_page' ); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="ch_upload_csv" />
                <?php //wp_nonce_field( 'ch_product_upload_'.get_the_ID() ); ?>
                <div class="form-group">
                    <label for="csvfile">Upload CSV</label>
                    <input id="csvfile" name="csvfile" type="file" accept="text/csv" class="form-control-file">
                </div>
                <input class="btn btn-primary btn-sm" type="submit">
            </form>
       </div>

        <?php
    }



    public function add_product( $product ) {

        if (isset($_POST['term'])) {
            $row = $_POST['term'];

            $shipping_delivery_rules = array (
                "3days" => 1,
                "5days" => 2,
                "1week" => 3,
                "2weeks" => 4,
                "3weeks" => 5,
                "4weeks" => 8,
                "5weeks" => 9,
                "6weeks" => 6,
                "7weeks" => 10,
                "8weeks" => 11,
                "9weeks" => 12,
                "10weeks" => 13,
                "3months" => 7,
            );

        } else {
            wp_send_json( json_encode("error") );
        }

        $product = new WC_Product;
        $date = new DateTime();

        $product->set_name($row['product_title']);
        // $product->set_slug($data[1]);
        $product->set_status($row['product_status']); // Validate draft / publish

        $product->set_description(str_replace("\\", "", $row['product_description']));
        $product->set_short_description(str_replace("\\", "",$row['product_method']));

        //$product->set_price($row['price']);  // Validate is number
        $product->set_regular_price($row['price']);

        if ( isset($row['sale_price']) && $row['sale_price'] != 0 ) {
            $product->set_sale_price($row['sale_price']);
        }

        $product->set_weight($row['weight']); // Validate is greater than 0.1
        $product->set_length($row['length']); // Validate is greater than 0.1
        $product->set_width($row['width']); // Validate is greater than 0.1
        $product->set_height($row['height']); // Validate is greater than 0.1

        $main_categories = explode('+', $row['main_categories']);
        $sub_categories = explode('+', $row['sub_categories']);
        $sub_sub_categories = explode('+', $row['sub_sub_categories']);

        $categories = array_merge($main_categories, $sub_categories, $sub_sub_categories);

        foreach( $categories as &$category ) {
            $category_object = get_term_by('slug', $category, 'product_cat');
            $category = $category_object->term_id;
        }

        $product->set_category_ids( $categories ); // Validate do categories exist.

        // color

        $colors_array = explode('+', $row['product_color']);

        array_walk($colors_array, function( &$color ) {
            $term = get_term_by('slug', $color,'pa_prod-colour');
            $color = $term->name;
        });

        $colors = new WC_Product_Attribute;
        $color_id = wc_attribute_taxonomy_id_by_name( 'pa_prod-colour' );
        $colors->set_id( $color_id );
        $colors->set_name('pa_prod-colour');
        $colors->set_options(explode('+', $row['product_color']) );
        $colors->set_visible(true);

        // Material

        $materials_array_1 = explode('+', $row['product_materials_1']);

        array_walk($materials_array_1, function( &$material ) {
            $term = get_term_by('slug', $material,'pa_material');
            $material = $term->name;
        });

        $materials_array_2 = explode('+', $row['product_materials_2']);

        array_walk($materials_array_2, function( &$material ) {
            $term = get_term_by('slug', $material,'pa_material');
            $material = $term->name;
        });

        $materials_array_3 = explode('+', $row['product_materials_3']);

        array_walk($materials_array_3, function( &$material ) {
            $term = get_term_by('slug', $material,'pa_material');
            $material = $term->name;
        });

        $materials_array = array_merge($materials_array_1, $materials_array_2, $materials_array_3);

        $materials = new WC_Product_Attribute;
        $material_id = wc_attribute_taxonomy_id_by_name( 'pa_material' );
        $materials->set_id( $material_id );
        $materials->set_name('pa_material');
        $materials->set_options( $materials_array );
        $materials->set_visible(true);

        // Shipping attribute

        $shipping_groups = explode('|', $row['shipping']);

        array_walk($shipping_groups, function( &$shipping_group ) {
           $shipping_group = explode('+', $shipping_group);
           array_pop($shipping_group);
           array_pop($shipping_group);

           array_walk($shipping_group, function( &$country ) {
                $country = 'shipto_'. strtolower($country);
                $term = get_term_by('slug', $country,'pa_ship-to');
                $country = $term->name;
           });
        });

        $shipping = new WC_Product_Attribute;
        $shipping_id = wc_attribute_taxonomy_id_by_name( 'pa_ship-to' );
        $shipping->set_id( $shipping_id  );
        $shipping->set_name('pa_ship-to');
        $shipping->set_options( array_merge(...$shipping_groups) );
        $shipping->set_visible(false);

        $product_type = array('New');

        if ($row['is_vintage'] == 'yes') {
            $product_type = array('Vintage');
        }

        $type = new WC_Product_Attribute;
        $type_id = wc_attribute_taxonomy_id_by_name( 'pa_product-type' );
        $type->set_id( $type_id );
        $type->set_name('pa_product-type');
        $type->set_options( $product_type );
        $type->set_visible(true);

        $attributes = array($colors, $materials, $shipping, $type);
        $product->set_attributes($attributes);

        // Tags
        $tags = explode('+', $row['product_tags']);

        array_walk($tags, function( &$tag ) {
            $term = get_term_by('slug', $tag,'product_tag');
            $tag = $term->term_id;
        });

        $product->set_tag_ids( $tags );

        // Stock
        $product->set_manage_stock( $row['manage_stock'] );
        $product->set_stock_quantity( $row['stock'] );
        $product->set_stock_status( $row['stock_status'] );

        // images
        $gallery_images = explode('+', $row['gallery_images']);
        $product->set_image_id( $row['featured_image'] );
        $product->set_gallery_image_ids( $gallery_images );

        // Visabilty
        $product->set_featured(true);
        $product->set_catalog_visibility('visible');

        // Create Product
        $product->validate_props();
        $product_id = $product->save();

        // Shipping post_meta

        $shipping_rates = explode('|', $row['shipping']);

        // Add Shipping as term 
        // I would prefer to add this as an attribute using the same method for materials , but it does not seem possible.

        // foreach($shipping_rates as $shipping_rate) {

        //     $shipping_countries = explode('+', $shipping_rate);
        //     $shipping_price = array_pop($shipping_rate);
        //     $shipping_time = array_pop($shipping_rate);

        //     foreach($shipping_countries as $shipping_country) {
        //          wp_set_object_terms($product_id, 'shipto_' . strtolower($shipping_country), 'pa_ship-to', true);
        //     }

        // }

        // Create Shipping 
        array_walk($shipping_rates, function( &$shipping_rate, $count , $shipping_delivery_rules ) {
           $shipping_countries = explode('+', $shipping_rate);
           $shipping_price = array_pop($shipping_countries);
           $shipping_time = array_pop($shipping_countries);

           $shipping_rate = array(
                'country' => implode(",", $shipping_countries),
                'state' => 0,
                'fee' => $shipping_price,
                'method' => 'quantity',
                'range1' => 0,
                'range2' => 0,
                'range3' => 0,
                'rule_delivery' => $shipping_delivery_rules[$shipping_time]
            );
        }, $shipping_delivery_rules );

        update_post_meta($product_id, 'ch_shipping_rates', $shipping_rates);

        // Update Product Author
        $arg = array(
            'ID' => $product_id,
            'post_author' => $row['vendor_id'],
        );
        wp_update_post( $arg );

        // Update Images Authors
        $featured_image_arg = array(
            'ID' => $row['featured_image'],
            'post_author' => $row['vendor_id'],
        );
        wp_update_post( $featured_image_arg );

        foreach ( $gallery_images as $image_id) {
            $image_arg = array(
                'ID' => $image_id,
                'post_author' => $row['vendor_id'],
                'post_parent' => $product_id,
            );

            wp_update_post( $image_arg );
        }

        wp_send_json( json_encode($product_id) );
    }

}