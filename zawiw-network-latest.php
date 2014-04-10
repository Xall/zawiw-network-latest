<?php
/*
Plugin Name: ZAWiW - Network latest posts
Plugin URI:
Description: Shows headlines of latest network posts
Version: 1.0
Author: Simon Volpert
Author URI: http://svolpert.eu
License: MIT 
*/

// Defines the zawiw-network-latest shortcode
add_shortcode( 'zawiw-network-latest', 'zawiw_network_latest_shortcode' );

// Load Scripts
add_action( 'wp_enqueue_scripts', 'zawiw_network_latest_queue_script' );
add_action( 'wp_enqueue_scripts', 'zawiw_network_latest_queue_stylesheet' );

function zawiw_network_latest_shortcode( $atts )
{
    // Save starting blog
    $starting_blog = get_current_blog_id();

    // start buffered output
    ob_start();

    $network_posts = array();

    foreach (wp_get_sites() as $blog) {
        if (!(isset($atts) and strpos( $blog['domain'], $atts['domain'] )) ) {
            continue;
        }

        // Activate each blog ...
        switch_to_blog($blog['blog_id']);
        // echo "<p>".get_bloginfo('name')."</p>";
        foreach (get_posts() as $post){
            $post->permalink = get_permalink($post->ID );
            $post->user = get_userdata($post->post_author );
            $post->blog = get_bloginfo('name');
            $post->date_sort = date_format(date_create($post->post_date), "Ymdis");
            array_push($network_posts, $post);
        }
    }

    // Sort array with custom (anonymous) sorting function. Requires php 5.3
    usort($network_posts, function($a, $b) {
        return -($a->date_sort - $b->date_sort);
    });

    ?>
    <ul>
    <?php foreach ($network_posts as $post) : ?>
        <li>
            <span><?php echo date_format(date_create($post->post_date),"d.m.Y").": "; ?></span>
            <a href="<?php echo $post->permalink ?>"><?php echo strlen($post->post_title) ? $post->post_title : 'Kein Titel'?></a><br>
            <span><i class="fa fa-user"></i> <?php echo $post->user->display_name ?></span>
            <span><i class="fa fa-home"></i> <?php echo $post->blog ?></span>
        </li>
    <?php endforeach ?>
    </ul>
    <?php
    // end buffered output
    $output = ob_get_contents();
    ob_end_clean();

    // Return to starting blog
    switch_to_blog($starting_blog);


    return $output;
}

function zawiw_network_latest_queue_script()
{
    # code...
}
function zawiw_network_latest_queue_stylesheet()
{
    wp_enqueue_style( 'font_awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );
}

?>
