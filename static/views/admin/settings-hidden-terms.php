<?php
/**
 * Settings 'Developer' Tab
 * Section 'Types'
 *
 * Adds taxonomies to 'Hidden Attributes' list
 */

if( !isset( $property_slug ) ) {
  return;
}

$taxonomies = $this->get( 'config.taxonomies', array() );
$hidden = ud_get_wp_property( 'hidden_terms', array() );

?>
<?php foreach( $taxonomies as $k => $data ) : ?>
<li class="wpp_development_advanced_option">
  <input id="<?php echo $property_slug . "_" . $k; ?>_taxonomy_hidden" <?php if( isset( $hidden[ $property_slug ] ) && in_array( $k, $hidden[ $property_slug ] ) ) echo " checked "; ?> type="checkbox" name="wpp_settings[hidden_terms][<?php echo $property_slug; ?>][]" value="<?php echo $k; ?>"/>
  <label for="<?php echo $property_slug . "_" . $k; ?>_taxonomy_hidden">
    <?php echo $data['label']; ?> (<?php _e( 'taxonomy', ud_get_wpp_terms('domain') ) ?>)
  </label>
</li>
<?php endforeach; ?>
