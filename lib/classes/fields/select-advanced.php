<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

// Make sure "select" field is loaded
require_once RWMB_FIELDS_DIR . 'select.php';

if ( ! class_exists( 'RWMB_Wpp_Select_Advanced_Field' ) )
{
  class RWMB_Wpp_Select_Advanced_Field extends RWMB_Select_Field
  {
    /**
     * Enqueue scripts and styles
     *
     * @return void
     */
    static function admin_enqueue_scripts()
    {
      wp_enqueue_style( 'field-wpp-taxonomy-inherited', ud_get_wpp_terms()->path( 'static/styles/fields/wpp-select-advance.css' ), array('wp-admin'),  ud_get_wpp_terms('version'));
      wp_enqueue_script( 'rwmb-taxonomy', ud_get_wpp_terms()->path( 'static/scripts/fields/wpp-select-advance.js' ), array( 'jquery', 'jquery-ui-autocomplete', 'underscore' ), ud_get_wpp_terms('version'), true );
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
      ob_start();

      ?>
      <div class="rwmb-field rwmb-wpp-taxonomy-wrapper" data-name="<?php echo $field['field_name'];?>" data-tax-counter="<?php echo $wpp_terms_taxonomy_field_counter;?>">
        <div class="taxsdiv">
          <div class="jaxtag">
            <div class="ui-widget clearfix">
              <input type="text" class="newtag form-input-tip" size="<?php $field['size'];?>" autocomplete="off" value="">
              <input type="button" id="terms-input-auto-<?php echo $wpp_terms_taxonomy_field_counter;?>" class="button taxadd" value="Add">
            </div>
            <p class="howto" id="new-tag-property_feature-desc">Separate tags with commas</p>
          </div>
          <div class="tagchecklist">
            <?php
            $i = 0;
            if(is_array($meta))
              foreach ($meta as $term) {
                $i++;
                $term = self::get_term( $term , $terms );
                echo "<span class='tax-tag'>";
                  echo "<a id='property_feature-check-num-$i' class='ntdelbutton notice-dismiss' tabindex='0'>X</a>&nbsp;{$term['label']}";
                  echo "<input type='hidden' name='{$field['field_name']}' value='{$term['value']}' />";
                echo "</span>";
              }
            ?>
          </div>
        </div>
        <script>
          var wpp_terms_available_options_<?php echo $wpp_terms_taxonomy_field_counter;?> = <?php echo json_encode($terms);?>;
        </script>
      </div>
      <?php if($wpp_terms_taxonomy_field_counter == 1):?>
      <script type="text/html" id="wpp-terms-taxnomy-template">
        <span class="tax-tag">
          <a class='ntdelbutton notice-dismiss' tabindex='0'>X</a>&nbsp;<%= label %>
          <input type='hidden' name='<%= name %>' value='<%= val %>' />
        </span>
      </script>
      <?php endif;

      $html = ob_get_clean();



      return $html;
    }

    static function get_term($term, $terms){
      foreach ($terms as $key => $t) {
        if($term == $t['value'])
          return $t;
      }
    }

  }
}
