<?php
if (!defined('ABSPATH')) {
    exit;
}

class CPT_Shortcode {

    public function __construct() {
        add_shortcode('cpts', array($this, 'cpts_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_bootstrap_assets'));
    }

    function cpts_shortcode($atts) {
        $atts = shortcode_atts(
            [
                'post_type'      => '',
                'posts_per_page' => 10,
                'order'          => 'DESC',
                'orderby'        => 'date',
            ],
            $atts,
            'cpts'
        );

        if (empty($atts['post_type']) || !post_type_exists($atts['post_type'])) {
            return '<p>Invalid or missing post type.</p>';
        }

        $args = [
            'post_type'      => $atts['post_type'],
            'posts_per_page' => (int) $atts['posts_per_page'],
            'order'          => $atts['order'],
            'orderby'        => $atts['orderby'],
        ];
        $query = new WP_Query($args);

        $output = '<div class="container mt-4">';
        $output .= '<div class="row gy-4">';
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $output .= '<div class="col-md-4">';
                $output .= '<div class="card shadow-sm h-100">';
                if (has_post_thumbnail()) {
                    $output .= '<img src="' . get_the_post_thumbnail_url(get_the_ID(), 'medium') . '" class="card-img-top" alt="' . get_the_title() . '">';
                }
                $output .= '<div class="card-body d-flex flex-column">';
                $output .= '<h5 class="card-title">' . get_the_title() . '</h5>';
                $output .= '<p class="card-text">' . get_the_excerpt() . '</p>';
                $output .= '<a href="' . get_permalink() . '" class="btn btn-primary mt-auto">Read More</a>';
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</div>';
            }
            wp_reset_postdata();
        } else {
            $output .= '<div class="col-12"><p>No posts found.</p></div>';
        }
        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }

    function enqueue_bootstrap_assets() {
        // Bootstrap CSS
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');

        // Bootstrap JS (optional, for interactive components like modals or tooltips)
        wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', [], null, true);
    }
}
