<?php 

// Functions for Kind Taxonomies

function get_the_kinds( $id = false ) {
/**
         * Filter the array of kinds to return for a post.
         *
         *
         */

	        $kinds = get_the_terms( $id, 'kind' );
	        if ( ! $kinds || is_wp_error( $kinds ) )
	                $kinds = array();
	
	        $kinds = array_values( $kinds );
	
	        foreach ( array_keys( $kinds ) as $key ) {
	                _make_cat_compat( $kinds[$key] );
	        }
	
	        return apply_filters( 'get_the_kind', $kinds );
	}

function get_the_kinds_list( $before = '', $sep = '', $after = '', $id = 0 ) {
        /**
         * Filter the kinds list for a given post.
         *
         * @param string $kind_list List of kinds.
         * @param string $before   String to use before kinds.
         * @param string $sep      String to use between the kinds.
         * @param string $after    String to use after kinds.
         * @param int    $id       Post ID.
         */
        return apply_filters( 'the_kinds', get_the_term_list( $id, 'kind', $before, $sep, $after ), $before, $sep, $after, $id );
}

function the_kinds( $before = null, $sep = ', ', $after = '' ) {
        if ( null === $before )
                $before = __('Kinds: ');
        echo get_the_kinds_list($before, $sep, $after);
}

function has_kind( $kind = '', $post = null ) {
        return has_term( $kind, 'kind', $post );
}

function get_kind_class ( $class = '', $classtype='u' , $id = false  ) {
   $kinds = get_the_kinds ($id);
   $classes = array();
   if ( ! $kinds || is_wp_error( $kinds ) )
            $kinds = array();
   foreach ( $kinds as $kind ) {
	    switch ($kind->slug) {
		     case "like":
                            $classes[] = $classtype.'-like-of';
			    $classes[] = $kind->slug;

	     	     break;
		     case "favorite":
			    $classes[] = $classtype.'-favorite-of';
			    $classes[] = $kind->slug;
		     break;
                     case "repost":
                            $classes[] = $classtype.'-repost-of';
			    $classes[] = $kind->slug;
                     break;
                     case "reply":
                            $classes[] = $classtype.'-in-reply-to';
			    $classes[] = $kind->slug;
                     break;
                     case "rsvp":
                            $classes[] = $classtype.'-in-reply-to';
                            $classes[] = $kind->slug;
                     break; 
     		     default:
			    $classes[] = $kind->slug;
		}


                }
   if ( ! empty( $class ) ) {
	                if ( !is_array( $class ) )
	                        $class = preg_split( '#\s+#', $class );
	                $classes = array_merge( $classes, $class );
	        } else {
	                // Ensure that we always coerce class to being an array.
	                $class = array();
   	        }
   $classes = array_map( 'esc_attr', $classes );
 /**
         * Filter the list of CSS kind classes for the current response URL.
         *
         *
         * @param array  $classes An array of kind classes.
         * @param string $class   A comma-separated list of additional classes added to the link.
         */
        return apply_filters( 'kind_classes', $classes, $class );
}

function kind_class( $class = '' ) {
        // Separates classes with a single space, collates classes
        echo 'class="' . join( ' ', get_kind_class( $class ) ) . '"';
}

function get_kind_verbs ( $id = false  ) {
   $kinds = get_the_kinds ($id);
   $verbs = array();
   if ( ! $kinds || is_wp_error( $kinds ) )
            $kinds = array();
   foreach ( $kinds as $kind ) {
            switch ($kind->slug) {
                     case "like":
                            $verbs[] = '<span class="like">Liked </span>';
                     break;
                     case "repost":
                            $verbs[] = '<span class="repost">Reposted </span>';
                     break;
                     case "reply":
                            $verbs[] = '<span class="reply">In Reply To </span>';
		     break;
		     case "favorite":
			    $verbs[] = '<span class="favorite">Favorited </span>'; 
		     break;
		     case "share":
			    $verbs[] = '<span class="share">Shared </span>';
		     break;
		     case "bookmark":
                            $verbs[] = '<span class="bookmark">Bookmarked </span>';
                     break;

		     case "rsvp":
			    $verbs[] = '<span class="rsvp">RSVPed </span>';
		     break;
		     case "checkin":
			    $verbs[] = '<span class="checkin">Checked in at </span>';
                     break;
                     default:
                            $verbs[] = '<span class="mention">Mentioned </span>';
                }


                }
 /**
         * Filter the list of CSS kind verbs for the current response URL.
         *
         *
         * @param array  $verbs An array of kind verbs.
         */
        return apply_filters( 'kind_verb', $verbs);
}

function kind_verbs() {
        // Separates verbs with an and, collates verbs
        echo join( ' and ', get_kind_verbs() ) . '"';
}


?>
