<?php
/**
 * WP-Property Upgrade Handler
 *
 * @since 2.1.0
 * @author peshkov@UD
 */
namespace UsabilityDynamics\WPP {

  if( !class_exists( 'UsabilityDynamics\WPP\Terms_Upgrade' ) ) {

    class Terms_Upgrade {

      /**
       * Run Upgrade Process
       *
       * @param $old_version
       * @param $new_version
       */
      static public function run( $old_version, $new_version ){
        global $wp_properties;

        self::do_backup( $old_version, $new_version );

        /**
         * Specific upgrade conditions.
         */
        switch( true ) {

          case ( version_compare( $old_version, '1.0.4', '<' ) ):
            $taxonomies = ud_get_wpp_terms()->define_taxonomies( array() );
            //$settings = ud_get_wpp_terms()->get( 'config.taxonomies', array() );
            if( is_array( $taxonomies ) ) {
              foreach ($taxonomies as $taxonomy => &$args) {
                if(isset($args['show_ui'])){
                  $args['show_in_menu'] = '';
                  $args['add_native_mtbox'] = '';
                  if($args['show_ui'] == "true"){
                    $args['show_in_menu'] = 'true';
                    $args['add_native_mtbox'] = 'true';
                  }
                  unset($args['show_ui']);
                }
              }
              ud_get_wpp_terms()->set( 'config.taxonomies', $taxonomies );
              ud_get_wpp_terms()->settings->commit();
            }
          break;

        }
        /* Additional stuff can be handled here */
        do_action( ud_get_wpp_terms()->slug . '::upgrade', $old_version, $new_version );
      }

      /**
       * Saves backup of WPP settings to uploads and to DB.
       *
       * @param $old_version
       * @param $new_version
       */
      static public function do_backup( $old_version, $new_version ) {
        /* Do automatic Settings backup! */
        global $wp_properties;
        if (empty($wp_properties)) {
          WPP_F::settings_action(true);
        }

        if( !empty( $wp_properties ) ) {

          /**
           * Fixes allowed mime types for adding download files on Edit Product page.
           *
           * @see https://wordpress.org/support/topic/2310-download-file_type-missing-in-variations-filters-exe?replies=5
           * @author peshkov@UD
           */
          add_filter( 'upload_mimes', function( $t ){
            if( !isset( $t['json'] ) ) {
              $t['json'] = 'application/json';
            }
            return $t;
          }, 99 );

          $filename = md5( 'wpp_settings_backup' ) . '.json';
          $upload = wp_upload_bits( $filename, null, json_encode( $wp_properties ) );

          if( !empty( $upload ) && empty( $upload[ 'error' ] ) ) {
            if( isset( $upload[ 'error' ] ) ) unset( $upload[ 'error' ] );
            $upload[ 'version' ] = $old_version;
            $upload[ 'time' ] = time();
            update_option( 'wpp_settings_backup', $upload );
          }

        }
      }

    }

  }

}
