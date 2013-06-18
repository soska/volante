<?php
/*
Plugin Name: Volante
Plugin URI: http://github.com/soska/volante
Description: Let your users navigate trough your site using their keyboard.
Version: 1.0
Author: Armando Sosa <armsosa@gmail.com>
Author URI: http://armandososa.com/
*/

/**
* Volante Navigation
*/
class VolantePlugin{

  public $style = 'default';

  /**
   * Constructor
   */
  function __construct( $style = null ){

  	if ( $style !== null ) {
  		$this->style = $style;
  	}

    add_action( 'wp_footer', array( $this, 'render' ) );
  	add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
  }

  public function assets(){

	  wp_enqueue_script("jquery");

	  if ( $this->style ) {
      wp_register_style( $this->style, plugins_url( 'assets/' . $this->style . '.css', __FILE__ ) );
      wp_enqueue_style( $this->style );
	  }

  }

  private function archive( $args = array() ){

    global $wp_query;

    $return = '';

    if ( ! is_singular() ) {

      $defaults = array(
        'prelabel' => __('&laquo; Previous Page'),
        'nxtlabel' => __('Next Page &raquo;'),
      );

      $args = wp_parse_args( $args, $defaults );

      $max_num_pages = $wp_query->max_num_pages;
      $paged = get_query_var('paged');

      //only have sep if there's both prev and next results
      if ($paged < 2 || $paged >= $max_num_pages) {
        $args['sep'] = '';
      }

      if ( $max_num_pages > 1 ) {
        $prev = str_replace( 'href', 'rel="prev" href', get_previous_posts_link('') );
        $next = str_replace( 'href', 'rel="next" href', get_next_posts_link('') );
      }

      $return = "<ul class='volante-pager volante-pager-archive'>";

      if ( $prev ){
        $return .= "<li class='prev'>$prev</li>";
      }

      if ( $next ){
        $return .= "<li class='next'>$next</li>";
      }

      $return .="</ul>";

    }

    return $return;

  }

  private function posts( $args = array() ){

    error_reporting(E_ALL);

    $defaults = array(
      'in_same_cat' => false,
      'excluded_categories' => '',
    );

    $args = extract( wp_parse_args( $args, $defaults ) );

    $return = '';

    if ( is_attachment() )
      $prev_post = & get_post($GLOBALS['post']->post_parent);
    else
      $prev_post = get_adjacent_post($in_same_cat, $excluded_categories, true);
      $next_post = get_adjacent_post($in_same_cat, $excluded_categories, false);

    if ( ! $prev_post && ! $next_post )
      return;

    $prev = $next = false;

    if ( ! empty( $prev_post ) ) {
      $prev = '<a rel="prev" href="' . get_permalink( $prev_post ) . '"></a>';
    }

    if ( ! empty( $next_post ) ) {
      $next = '<a rel="next" href="' . get_permalink( $next_post ) . '"></a>';
    }


    // we invert this because defaults are retarded
    $return = "<ul class='volante-pager volante-pager-posts'>";

    if ( $next )
      $return .= "<li class='prev'>$next</li>";
    if ( $prev )
      $return .= "<li class='next'>$prev</li>";

    $return .="</ul>";

    return $return;
  }


  public function nav(){

    if (is_single()) {
      echo $this->posts();
    }else{
      echo $this->archive();
    }

    $this->script();

  }

  public function render(){
    echo $this->nav();
  }

  private function script(){
  ?>
    <script type="text/javascript">
    (function(){
    /**
     * Volante-Plugin
     * (c) 2013 Armando Sosa
     */
      // let's cache this values.
      var $prev = $('.volante-pager .prev a[rel=prev]');
      var prev = $prev.attr('href');
      var $next = $('.volante-pager .next a[rel=next]');
      var next = $next.attr('href');

      $(document).bind('keyup',function(e){
        if (e.keyCode === 39 && next) { //right
          $next.addClass('active');
          location.href = next;
        }else if(e.keyCode === 37 && prev){ // left
          $prev.addClass('active');
          location.href = prev;
        }
      });

      // remove click delay on touch devices.
      $prev.bind('touchstart',tap);
      $next.bind('touchstart',tap);
      function tap(e){
        console.log(this);
        location.href = this.href;
      }


    })();
    </script>
  <?php
  }

}

new VolantePlugin;