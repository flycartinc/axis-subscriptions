<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */

get_header(); ?>

    <div id="primary" class="content-area axisubs_content_area">
            <?php
            // Start the Loop.
            while ( have_posts() ) : the_post();

                /*
                 * Include the post format-specific template for the content. If you want to
                 * use this in a child theme, then include a file called called content-___.php
                 * (where ___ is the post format) and that will be used instead.
                 */
                   // the_content();
                do_action('axisubs_single_subscribe');
                // Previous/next post navigation.
//                twentyfourteen_post_nav();

                // If comments are open or we have at least one comment, load up the comment template.
                /*if ( comments_open() || get_comments_number() ) {
                    comments_template();
                }*/
            endwhile;
            ?>
    </div><!-- #primary -->

<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
