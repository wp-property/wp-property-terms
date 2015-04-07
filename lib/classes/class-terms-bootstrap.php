<?php
/**
 * Bootstrap
 *
 * @since 1.0.0
 */
namespace UsabilityDynamics\WPP {

  if( !class_exists( 'UsabilityDynamics\WPP\Terms_Bootstrap' ) ) {

    final class Terms_Bootstrap extends \UsabilityDynamics\WP\Bootstrap_Plugin {
      
      /**
       * Singleton Instance Reference.
       *
       * @protected
       * @static
       * @property $instance
       * @type \UsabilityDynamics\WPP\Terms_Bootstrap object
       */
      protected static $instance = null;
      
      /**
       * Instantaite class.
       */
      public function init() {

        if( !class_exists( '\UsabilityDynamics\Settings' ) ) {
          $this->errors->add( __( 'Class \UsabilityDynamics\Settings is undefined.', $this->domain ) );
          return;
        }

        /** Add Terms UI on Settings Developer Tab. */
        if( current_user_can( 'manage_wpp_categories' ) ) {

          add_filter( 'wpp::settings_developer::tabs', function( $tabs ){
            $tabs['terms'] = array(
              'label' => __( 'Taxonomies', $this->domain ),
              'template' => $this->path( 'static/views/admin/settings-developer-terms.php', 'dir' ),
              'order' => 25
            );
            return $tabs;
          } );

          add_action( 'wpp::save_settings', array( $this, 'save_terms' ) );

        }

        /** Define our custom taxonomies. */
        add_filter( 'wpp_taxonomies', array( $this, 'define_taxonomies' ) );

        /** Prepare taxonomy's arguments before registering taxonomy. */
        add_filter( 'wpp::register_taxonomy', array( $this, 'prepare_taxonomy' ), 99, 2 );

      }

      /**
       * Save custom Taxonomies
       *
       */
      public function save_terms( $data ) {
        if( !empty( $data[ 'wpp_terms' ] ) && is_array( $data[ 'wpp_terms' ] ) ) {
          $taxonomies = array();

          foreach( $data[ 'wpp_terms' ] as $taxonomy => $v ) {

            /* Ignore missed Taxonomy */
            if( empty( $v[ 'label' ] ) && count( $data[ 'wpp_terms' ] ) == 1 ) {
              break;
            }

            $taxonomies[ $taxonomy ] = $this->prepare_taxonomy( $v, $taxonomy );

          }

          $this->set( 'config.taxonomies', $taxonomies );

          $this->settings->commit();
        }
      }

      /**
       * Define our custom taxonomies on wpp_taxonomies hook
       *
       */
      public function define_taxonomies( $taxonomies ) {

        /** Init Settings */
        $this->settings = new \UsabilityDynamics\Settings( array(
          'key'  => 'wpp_terms',
          'store'  => 'options',
          'data' => array(
            'name' => $this->name,
            'version' => $this->args[ 'version' ],
            'domain' => $this->domain,
          )
        ));

        /** Be sure that we have any taxonomy to register. If not, we set default taxonomies of WP-Property. */
        if( !$this->get( 'config.taxonomies' ) ) {
          $this->set( 'config.taxonomies', $taxonomies );
        }

        return $this->get( 'config.taxonomies', array() );
      }

      /**
       * Prepare arguments
       */
      public function prepare_taxonomy( $args, $taxonomy ) {

        $args = wp_parse_args( $args, array(
          'label' => $taxonomy,
          'labels' => array(),
          'public' => false,
          'hierarchical' => false,
          'show_ui' => false,
          'show_in_nav_menus' => false,
          'show_tagcloud' => false,
          'capabilities' => array(
            'manage_terms' => 'manage_wpp_categories',
            'edit_terms'   => 'manage_wpp_categories',
            'delete_terms' => 'manage_wpp_categories',
            'assign_terms' => 'manage_wpp_categories'
          ),
        ) );

        /* May be fix data type */
        foreach( $args as &$arg ) {
          if( is_string( $arg ) && $arg === 'true' ) {
            $arg = true;
          }
        }

        return $args;
      }
      
      /**
       * Plugin Activation
       *
       */
      public function activate() {}
      
      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {}

    }

  }

}
