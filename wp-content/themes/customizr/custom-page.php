<?php
/*
Template Name: Custom Page Example
*/
?>
<?php do_action( '__before_main_wrapper' ); ##hook of the header with get_header ?>
<div id="main-wrapper" class="<?php echo esc_attr( tc__f( 'tc_main_wrapper_classes' , 'container' ) ) ?>">

    <?php do_action( '__before_main_container' ); ##hook of the featured page (priority 10) and breadcrumb (priority 20)...and whatever you need! ?>
    
    <div class="container" role="main">
        <div class="<?php echo esc_attr( tc__f( 'tc_column_content_wrapper_classes' , 'row column-content-wrapper' ) ) ?>">

            <?php do_action( '__before_article_container' ); ##hook of left sidebar?>
                
                <div id="content" class="<?php echo esc_attr( tc__f( '__screen_layout' , tc__f( '__ID' ) , 'class' ) ) ?> article-container">
                    
                    <?php do_action( '__before_loop' );##hooks the header of the list of post : archive, search... ?>

                        <?php if ( have_posts() ) : ?>  

                            <?php while ( have_posts() ) : ##all other cases for single and lists: post, custom post type, page, archives, search, 404 ?>
                                
                                <?php the_post(); ?>

                                <?php do_action( '__before_article' ) ?>
                                    <article <?php tc__f( '__article_selectors' ) ?>>
                                        <?php do_action( '__loop' ); ?>
                                    </article>
                                <?php do_action( '__after_article' ) ?>

                            <?php endwhile; ?>

                        <?php endif; ##end if have posts ?>

                    <?php do_action( '__after_loop' );##hook of the comments and the posts navigation with priorities 10 and 20 ?>

                </div><!--.article-container -->

           <?php do_action( '__after_article_container' ); ##hook of left sidebar ?>

        </div><!--.row -->
    </div><!-- .container role: main -->

    <?php do_action( '__after_main_container' ); ?>

</div><!--#main-wrapper"-->

<?php do_action( '__after_main_wrapper' );##hook of the footer with get_get_footer ?>