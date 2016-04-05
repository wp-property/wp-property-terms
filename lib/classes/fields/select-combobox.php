<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "select" field is loaded
require_once RWMB_FIELDS_DIR . 'select.php';

if ( ! class_exists( 'RWMB_Wpp_Select_Combobox_Field' ) )
{
  class RWMB_Wpp_Select_Combobox_Field extends RWMB_Select_Field{
    /**
     * Enqueue scripts and styles
     *
     * @return void
     */
    static function admin_enqueue_scripts(){
      wp_enqueue_style( 'field-wpp-select-combobox', ud_get_wpp_terms()->path( 'static/styles/fields/wpp-select-combobox.css' ), array(),  ud_get_wpp_terms('version'));
      wp_enqueue_script( 'field-wpp-select-combobox', ud_get_wpp_terms()->path( 'static/scripts/fields/wpp-select-combobox.js' ), array( 'jquery', 'jquery-ui-autocomplete', 'underscore' ), ud_get_wpp_terms('version'), true );
    }

    /**
     * Get field HTML
     *
     * @param mixed $meta
     * @param array $field
     *
     * @return string
     */
    static function html( $meta, $field ){
      global $wpp_terms_taxonomy_field_counter;
      $wpp_terms_taxonomy_field_counter++;
      $terms = array();
      $options = $field['_options'];
      foreach ($field['options'] as $id => $label) {
        $terms[] = array('value' => $id, 'label' => $label);
      }
      $meta     = array_values($meta);
      $term_id  = '';
      $term_name  = '';
      if(isset($meta[0])){
        $term_id = $meta[0];
        $term = get_term( $term_id , $options['taxonomy'] );
        $term_name = $term->name; 
      }

      ob_start();

      ?>
      <div 
        class="rwmb-field wpp-taxonomy-select-combobox wpp_ui" 
        data-name="<?php echo $field['field_name'];?>" 
        data-taxonomy="<?php echo $options['taxonomy'];?>" 
        data-tax-counter="<?php echo $wpp_terms_taxonomy_field_counter;?>">
        <span>
          <input
              type = "text"
              class="ui-widget ui-widget-content ui-state-default ui-corner-left ui-autocomplete-input" 
              autocomplete="off"
              name="<?php echo $field['field_name'];?>" 
              value="<?php echo $term->name;?>"
            >
          <a tabindex="-1" title="Show All Items" class="ui-button ui-widget ui-state-default ui-button-icon-only select-combobox-toggle ui-corner-right" role="button">
            <span class="ui-button-icon-primary ui-icon ui-icon-triangle-1-s"></span>
          </a>
        </span>
      </div>
      <?php

      $html = ob_get_clean();



      return $html;
    }

  }
}
