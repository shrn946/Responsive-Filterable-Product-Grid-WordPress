<?php
/*
Plugin Name: Simple Filterable Product Grid
Description: A WordPress plugin for creating a filterable product grid.[custom_product_grid]
Version: 1.0
Author: Hassan Naqvi
*/

function filterable_product_grid_enqueue_scripts_styles() {
    // Styles

	
    wp_enqueue_style('flickity-styles', plugin_dir_url(__FILE__) . 'css/flickity.css');
    wp_enqueue_style('component-styles', plugin_dir_url(__FILE__) . 'css/style.css');

    // Scripts
    wp_enqueue_script('modernizr', plugin_dir_url(__FILE__) . 'js/modernizr.custom.js', array(), null, true);
    wp_enqueue_script('isotope', plugin_dir_url(__FILE__) . 'js/isotope.pkgd.min.js', array('jquery'), null, true);
    wp_enqueue_script('flickity', plugin_dir_url(__FILE__) . 'js/flickity.pkgd.min.js', array('jquery'), null, true);
    wp_enqueue_script('main', plugin_dir_url(__FILE__) . 'js/main.js', array('jquery'), null, true);
}

add_action('wp_enqueue_scripts', 'filterable_product_grid_enqueue_scripts_styles');



function custom_product_grid_shortcode($atts) {
    ob_start(); // Start output buffering

    // Shortcode attributes
    $atts = shortcode_atts(
        array(
            'category' => '', // WooCommerce product category
        ),
        $atts,
        'custom_product_grid'
    );

    // Get WooCommerce products based on the specified category
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1, // Adjust the number of products to display
        'order'          => 'DESC',
        'orderby'        => 'date',
        'tax_query'      => array(),
    );

    if (!empty($atts['category'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $atts['category'],
        );
    }

    $products = new WP_Query($args);

    // Output filter buttons
    ?>
    <div class="bar">
        <div class="filter">
            <button class="action filter__item filter__item--selected" data-filter="*">All</button>

            <?php
            // Output filter buttons based on product categories
            $product_categories = get_terms('product_cat');
            foreach ($product_categories as $category) {
                $filter_class = sanitize_title($category->name);
                ?>
                <button class="action filter__item" data-filter=".<?php echo esc_attr($filter_class); ?>">
                    <span class="action__text"><?php echo esc_html($category->name); ?></span>
                </button>
                <?php
            }
            ?>
        </div>
   
    </div>

    <div class="view">
        <section class="grid grid--loading">
            <img class="grid__loader" src="images/grid.svg" width="60" alt="Loader image" />
            <div class="grid__sizer"></div>

            <?php
            // Output product grid items
            if ($products->have_posts()) {
                while ($products->have_posts()) {
                    $products->the_post();
                    $product_id = get_the_ID();
                    $product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'slugs'));

                    // Add unique identifier to filter and loop class
                    $filter_class = implode(' ', $product_categories);
                    ?>
                    <div class="grid__item <?php echo esc_attr($filter_class); ?>">
    <div class="item_img">
        <div class="slider__item"><?php the_post_thumbnail('full'); ?></div>
    </div>
    <div class="meta">
        <h3 class="meta__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <span class="meta__brand category"><?php echo esc_html($product_categories[0]); ?></span>
        <span class="meta__price"><?php echo wc_price(get_post_meta($product_id, '_price', true)); ?></span>
    </div>

</div>
                    <?php
                }
            } else {
                echo '<p>No products found</p>';
            }

            wp_reset_postdata(); // Reset post data
            ?>

        </section>
    </div>
    <?php

    $output = ob_get_clean(); // Get the buffered output
    return $output;
}
add_shortcode('custom_product_grid', 'custom_product_grid_shortcode');


// Function to display the settings page content for the Free Product Carousel Shortcodes
function display_free_product_carousel_shortcodes() {
    ?>
    <div class="wrap">
        <h1>Filterable Product Grid Shortcodes</h1>
        
        <p>Welcome to the Filterable Product Grid plugin settings page.</p>
        <h2>How to Use Shortcode</h2>
               <p>Display all products with the default settings:</p>
        <pre>[custom_product_grid]</pre>

   
    <?php
}

// Function to add the settings page to the admin menu
function add_filterable_product_grid_menu() {
    add_options_page('Filterable Product Grid Settings', 'Filterable Product Grid', 'manage_options', 'filterable-product-grid-settings', 'display_free_product_carousel_shortcodes');
}

// Hook to add the settings page
add_action('admin_menu', 'add_filterable_product_grid_menu');
