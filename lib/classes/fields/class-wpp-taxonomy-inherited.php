<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Wpp_Taxonomy_Inherited_Field' ) )  {

  class RWMB_Wpp_Taxonomy_Inherited_Field extends RWMB_Taxonomy_Field {

    /**
     * Nothing to load
     *
     * @return void
     */
    static function admin_enqueue_scripts() {}

    /**
     * Get field HTML
     *
     * @param mixed $meta
     * @param array $field
     *
     * @return string
     */
    static function html( $meta, $field ) {


      return sprintf(
        '<input type="text" readonly="readonly" class="rwmb-text" name="%s" id="%s" value="%s" placeholder="%s" size="%s">',
        $field['field_name'],
        $field['id'],
        $meta,
        $field['placeholder'],
        $field['size']
      );
    }

    /**
     * Get meta value
     *
     * @param int   $post_id
     * @param bool  $saved
     * @param array $field
     *
     * @return mixed
     */
    static function meta( $post_id, $saved, $field ) {

      /**
       * For special fields like 'divider', 'heading' which don't have ID, just return empty string
       * to prevent notice error when displayin fields
       */
      if ( empty( $field['id'] ) )
        return '';

      $meta = '';

      return $meta;
    }

    /**
     * Normalize parameters for field
     *
     * @param array $field
     * @return array
     */
    static function normalize_field( $field ) {
      return $field;
    }

  }

}
