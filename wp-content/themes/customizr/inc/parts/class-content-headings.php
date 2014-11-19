<?php
/**
* Headings actions
*
* 
* @package      Customizr
* @subpackage   classes
* @since        3.1.0
* @author       Nicolas GUILLAUME <nicolas@themesandco.com>
* @copyright    Copyright (c) 2013, Nicolas GUILLAUME
* @link         http://themesandco.com/customizr
* @license      http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
if ( ! class_exists( 'TC_headings' ) ) :
  class TC_headings {
      static $instance;
      function __construct () {
        self::$instance =& $this;
        //Headings for archives, authors, search, 404
        add_action  ( '__before_loop'                 , array( $this , 'tc_archives_headings' ));
        //Headings for post, page, attachment
        add_action  ( '__before_content'              , array( $this , 'tc_content_headings' ));
        //Set single post/page icon with customizer options (since 3.2.0)
        add_filter ( 'tc_content_title_icon'          , array( $this , 'tc_set_post_page_icon' ));
        //Set single post/page icon with customizer options (since 3.2.0)
        add_filter ( 'tc_archive_icon'                , array( $this , 'tc_set_archive_icon' ));
      }



      /**
      * The template part for displaying the not post/page headings : archives, author, search, 404 and the post page heading (if not font page)
      *
      * @package Customizr
      * @since Customizr 3.1.0
      */
      function tc_archives_headings() {
        //case page for posts but not on front
        global $wp_query;
        if ( $wp_query -> is_posts_page && ! is_front_page() ) {
          //get page for post ID
          $page_for_post_id = get_option('page_for_posts');
          $header_class   = 'entry-header';
          $content        = sprintf('<%1$s class="entry-title %2$s">%3$s</%1$s>',
                apply_filters( 'tc_content_title_tag' , 'h1' ),
                apply_filters( 'tc_content_title_icon', 'format-icon' ),
                get_the_title( $page_for_post_id )
           );
          $content        = apply_filters( 'tc_page_for_post_header_content', $content );
        }


        //404
        if ( is_404() ) {
          $header_class   = 'entry-header';
          $content        = sprintf('<h1 class="entry-title %1$s">%2$s</h1>',
                apply_filters( 'tc_archive_icon', '' ),
                apply_filters( 'tc_404_title' , __( 'Ooops, page not found' , 'customizr' ) )
           );
          $content        = apply_filters( 'tc_404_header_content', $content );
        }

        //search results
        if ( is_search() && !is_singular() ) {
          $header_class   = 'search-header';
          $content        = sprintf( '<div class="row-fluid"><div class="%1$s"><h1 class="%2$s">%3$s%4$s %5$s </h1></div><div class="%6$s">%7$s</div></div>',
                apply_filters( 'tc_search_result_header_title_class', 'span8' ),
                apply_filters( 'tc_archive_icon', 'format-icon' ),
                have_posts() ? '' :  __( 'No' , 'customizr' ).'&nbsp;' ,
                apply_filters( 'tc_search_results_title' , __( 'Search Results for :' , 'customizr' ) ),
                '<span>' . get_search_query() . '</span>',
                apply_filters( 'tc_search_result_header_form_class', 'span4' ),
                have_posts() ? get_search_form(false) : ''
          );
          $content       = apply_filters( 'tc_search_results_header_content', $content );
        }
        
        //author's posts page
        if ( !is_singular() && is_author() ) {
          //gets the user ID
          $user_id = get_query_var( 'author' );
          $header_class   = 'archive-header';
          $content    = sprintf( '<h1 class="%1$s">%2$s %3$s</h1>',
                  apply_filters( 'tc_archive_icon', 'format-icon' ),
                  apply_filters( 'tc_author_archive_title' , __( 'Author Archives :' , 'customizr' ) ),
                  '<span class="vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( $user_id ) ) . '" title="' . esc_attr( get_the_author_meta( 'display_name' , $user_id ) ) . '" rel="me">' . get_the_author_meta( 'display_name' , $user_id ) . '</a></span>' 
          );
          if ( apply_filters ( 'tc_show_author_meta' , get_the_author_meta( 'description', $user_id  ) ) ) {
            $content    .= sprintf('%1$s<div class="author-info"><div class="%2$s">%3$s</div></div>',

              apply_filters( 'tc_author_meta_separator', '<hr class="featurette-divider '.current_filter().'">' ),

              apply_filters( 'tc_author_meta_wrapper_class', 'row-fluid' ),

              sprintf('<div class="%1$s">%2$s</div><div class="%3$s"><h2>%4$s</h2><p>%5$s</p></div>',
                  apply_filters( 'tc_author_meta_avatar_class', 'comment-avatar author-avatar span2'),
                  get_avatar( get_the_author_meta( 'user_email', $user_id ), apply_filters( 'tc_author_bio_avatar_size' , 100 ) ),
                  apply_filters( 'tc_author_meta_content_class', 'author-description span10' ),
                  sprintf( __( 'About %s' , 'customizr' ), get_the_author() ),
                  get_the_author_meta( 'description' , $user_id  )
              )
            );
          }
          $content       = apply_filters( 'tc_author_header_content', $content );
        }

        //category archives
        if ( !is_singular() && is_category() ) {
          $header_class   = 'archive-header';
          $content    = sprintf( '<h1 class="%1$s">%2$s %3$s</h1>',
                apply_filters( 'tc_archive_icon', 'format-icon' ),
                apply_filters( 'tc_category_archive_title' , __( 'Category Archives :' , 'customizr' ) ),
                '<span>' . single_cat_title( '' , false ) . '</span>'
          );
          if ( apply_filters ( 'tc_show_cat_description' , category_description() ) ) {
            $content    .= sprintf('<div class="archive-meta">%1$s</div>',
              category_description()
            );
          }
          $content       = apply_filters( 'tc_category_archive_header_content', $content );
        }

        //tag archives
        if ( !is_singular() && is_tag() ) {
          $header_class   = 'archive-header';
          $content    = sprintf( '<h1 class="%1$s">%2$s %3$s</h1>',
                apply_filters( 'tc_archive_icon', 'format-icon' ),
                apply_filters( 'tag_archive_title' , __( 'Tag Archives :' , 'customizr' ) ),
                '<span>' . single_tag_title( '' , false ) . '</span>'
          );
          if ( apply_filters ( 'tc_show_tag_description' , tag_description() ) ) {
            $content    .= sprintf('<div class="archive-meta">%1$s</div>',
              tag_description()
            );
          }
          $content       = apply_filters( 'tc_tag_archive_header_content', $content );
        }

        //time archives
        if ( !is_singular() && ( is_day() || is_month() || is_year() ) ) {
          $archive_type   = is_day() ? sprintf( __( 'Daily Archives: %s' , 'customizr' ), '<span>' . get_the_date() . '</span>' ) : __( 'Archives' , 'customizr' );
          $archive_type   = is_month() ? sprintf( __( 'Monthly Archives: %s' , 'customizr' ), '<span>' . get_the_date( _x( 'F Y' , 'monthly archives date format' , 'customizr' ) ) . '</span>' ) : $archive_type;
          $archive_type   = is_year() ? sprintf( __( 'Yearly Archives: %s' , 'customizr' ), '<span>' . get_the_date( _x( 'Y' , 'yearly archives date format' , 'customizr' ) ) . '</span>' ) : $archive_type;
          $header_class   = 'archive-header';
          $content        = sprintf('<h1 class="%1$s">%2$s</h1>',
            apply_filters( 'tc_archive_icon', 'format-icon' ),
            $archive_type
          );
          $content        = apply_filters( 'tc_time_archive_header_content', $content );
        }

        //renders the heading
        if ( !isset($content) || !isset($header_class) )
          return;
        global $wp_query;
        ob_start();
        ?>

        <header class="<?php echo apply_filters( 'tc_archive_header_class', $header_class ); ?>">
          <?php 
          do_action('__before_archive_title');

          echo apply_filters( 'tc_archive_header_content', $content );

          do_action('__after_archive_title');
          
          echo ( !tc__f('__is_home') && !$wp_query -> is_posts_page  ) ? apply_filters( 'tc_archives_headings_separator', '<hr class="featurette-divider '.current_filter(). '">' ) : '';
          ?>
        </header>

        <?php
        $html = ob_get_contents();
        ob_end_clean();
        echo apply_filters( 'tc_archives_headings', $html );
      }//end of function




      /**
       * The template part for displaying the post/page header
       *
       * @package Customizr
       * @since Customizr 3.1.0
       */
      function tc_content_headings() {
        //we don't display titles for some post formats
        $post_formats_with_no_header = apply_filters( 'tc_post_formats_with_no_header', TC_init::$instance -> post_formats_with_no_header );
        if( in_array( get_post_format(), $post_formats_with_no_header ) )
          return;

        //by default we don't display the title of the front page
        if( apply_filters('tc_show_page_title', is_front_page() && 'page' == get_option( 'show_on_front' ) ) )
          return
        ?>

          <header class="<?php echo apply_filters( 'tc_content_header_class', 'entry-header' ); ?>">
            
            <?php 
            do_action('__before_content_title');

            //adds filters for comment bubble style and icon
            $bubble_style                      = ( 0 == get_comments_number() ) ? 'style="color:#ECECEC" ':'';
            $bubble_style                      = apply_filters( 'tc_comment_bubble_style', $bubble_style );
            $bubble_comment                    = sprintf('<span class="fs1 icon-bubble" %1$s></span><span class="inner">%2$s</span>',
                                                    $bubble_style,
                                                    get_comments_number()
                                                    );
            $bubble_comment                    = apply_filters( 'tc_bubble_comment', $bubble_comment );

            //when are we showing the comments number in title?
            $comments_enabled                  = ( 1 == esc_attr( tc__f( '__get_option' , 'tc_page_comments' )) && comments_open() && get_comments_number() != 0 && !post_password_required() && is_page() ) ? true : false;
            $comments_enabled                  = ( comments_open() && get_comments_number() != 0 && !post_password_required() && !is_page() ) ? true : $comments_enabled;
            $comments_enabled                  = apply_filters( 'tc_comments_in_title', $comments_enabled );

            //when are we displaying the edit link?
            $edit_enabled                      = ( (is_user_logged_in()) && current_user_can('edit_pages') && is_page() ) ? true : false;
            $edit_enabled                      = ( (is_user_logged_in()) && current_user_can('edit_post' , get_the_ID() ) && ! is_page() ) ? true : $edit_enabled;
            $edit_enabled                      = apply_filters( 'tc_edit_in_title', $edit_enabled );

            //declares vars
            $html = '';
            $filter_args = array();

            if ( (get_the_title() != null) ) {
              
              //gets the post/page title
              if ( is_singular() || ! apply_filters('tc_display_link_for_post_titles' , true ) ) {
                $tc_heading_title = ( get_the_title() == null ) ? apply_filters( 'tc_no_title_post', __( '{no title} Read the post &raquo;' , 'customizr' ) )  : get_the_title();
              }
              else {
                $tc_heading_title = sprintf('<a href="%1$s" title="%2$s" rel="bookmark">%3$s</a>',
                                get_permalink(),
                                sprintf( apply_filters( 'tc_post_link_title' , __( 'Permalink to %s' , 'customizr' ) ) , esc_attr( strip_tags( get_the_title() ) ) ),
                                ( get_the_title() == null ) ? apply_filters( 'tc_no_title_post', __( '{no title} Read the post &raquo;' , 'customizr' ) )  : get_the_title()
                              );//end sprintf
              }

              $filter_args        =  array( $bubble_comment, $comments_enabled, $edit_enabled , $tc_heading_title );

              //renders the full title
              $html = sprintf('<%1$s class="entry-title %2$s">%3$s %4$s %5$s</%1$s>',
                              apply_filters( 'tc_content_title_tag' , is_singular() ? 'h1' : 'h2' ),
                              apply_filters( 'tc_content_title_icon', 'format-icon' ),
                              $tc_heading_title,

                              //checks if comments are opened AND if there are any comments to display
                              $comments_enabled ? sprintf('<span class="comments-link"><a href="%1$s#tc-comment-title" title="%2$s %3$s">%4$s</a></span>',
                                                    is_singular() ? '' : get_permalink(),
                                                    __( 'Comment(s) on' , 'customizr' ),
                                                    get_the_title(),
                                                    $bubble_comment
                                                    ) : '',

                              $edit_enabled ? sprintf('<span class="edit-link btn btn-inverse btn-mini"><a class="post-edit-link" href="%1$s" title="%2$s">%2$s</a></span>',
                                                get_edit_post_link(),
                                                __( 'Edit' , 'customizr' )
                                                ) : ''
              );//end sprintf

            }//end if title exists
            echo apply_filters( 'tc_content_headings' , $html, $filter_args );

            do_action('__after_content_title');

            echo is_singular() ? apply_filters( 'tc_content_headings_separator', '<hr class="featurette-divider '.current_filter().'">' ) : '';
            ?>

          </header><!-- .entry-header -->

        <?php
      }//end of function


      /**
      * Filter tc_content_title_icon
      * @return  boolean
      *
      * @package Customizr
      * @since Customizr 3.2.0
      */
      function tc_set_post_page_icon( $_bool ) {
          if ( is_page() )
            $_bool = ( 0 == esc_attr( tc__f( '__get_option' , 'tc_show_page_title_icon' ) ) ) ? false : $_bool;
          if ( is_single() && ! is_page() )
            $_bool = ( 0 == esc_attr( tc__f( '__get_option' , 'tc_show_post_title_icon' ) ) ) ? false : $_bool;
          if ( ! is_single() )
            $_bool = ( 0 == esc_attr( tc__f( '__get_option' , 'tc_show_post_list_title_icon' ) ) ) ? false : $_bool;
          //last condition
          return ( 0 == esc_attr( tc__f( '__get_option' , 'tc_show_title_icon' ) ) ) ? false : $_bool;
      }


      /**
      * Filter tc_archive_icon
      * @return  boolean
      * 
      * @package Customizr
      * @since Customizr 3.2.0
      */
      function tc_set_archive_icon( $_bool ) {
          $_bool = ( 0 == esc_attr( tc__f( '__get_option' , 'tc_show_archive_title_icon' ) ) ) ? false : $_bool;
          //last condition
          return ( 0 == esc_attr( tc__f( '__get_option' , 'tc_show_title_icon' ) ) ) ? false : $_bool;
      }

  }//end of class
endif;