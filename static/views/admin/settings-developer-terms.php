<?php
/**
 * Settings 'Developer' Tab
 * Section 'Advanced'
 */

?>
<h3><?php printf( __( '%1s Taxonomies', 'wpp' ), WPP_F::property_label() ); ?></h3>
<p><?php printf( __( 'Manage your %s Taxonomies here. Note, you can not remove all taxonomies, in this case default WP-Property taxonomies will be returned back.', ud_get_wpp_terms()->domain ), WPP_F::property_label() ); ?></p>

<table id="" class="ud_ui_dynamic_table widefat">
  <thead>
  <tr>
    <th class='wpp_draggable_handle'>&nbsp;</th>
    <th class='wpp_attribute_name_col'><?php _e( 'Label', 'wpp' ) ?></th>
    <th class='wpp_attribute_slug_col'><?php _e( 'Slug', 'wpp' ) ?></th>
    <th class='wpp_settings_col'><?php _e( 'Settings', 'wpp' ) ?></th>
    <th class='wpp_delete_col'>&nbsp;</th>
  </tr>
  </thead>
  <tbody>
  <?php foreach( ud_get_wpp_terms( 'config.taxonomies', array() ) as $slug => $data ): ?>
    <?php $data = ud_get_wpp_terms()->prepare_taxonomy( $data, $slug ); ?>
    <tr class="wpp_dynamic_table_row" slug="<?php echo $slug; ?>" new_row='false'>
      <th class='wpp_draggable_handle'>&nbsp;</th>
      <td>
        <ul>
          <li>
            <input class="slug_setter" type="text" name="wpp_terms[<?php echo $slug; ?>][label]" value="<?php echo $data['label']; ?>"/>
          </li>
          <li class="hide-on-new-row">
            <a href="<?php echo admin_url( "edit-tags.php?taxonomy={$slug}&post_type=property" ); ?>"><?php _e( 'Manage Terms', ud_get_wpp_terms()->domain ); ?></a>
          </li>
        </ul>
      <td>
        <ul>
          <li>
            <input type="text" class="slug" readonly='readonly' value="<?php echo $slug; ?>"/>
          </li>
        </ul>
      </td>
      <td>
        <ul>
          <li>
            <label><input type="checkbox" name="wpp_terms[<?php echo $slug; ?>][hierarchical]" <?php checked( $data['hierarchical'], true ); ?> value="true"/> <?php _e( 'Hierarchical', ud_get_wpp_terms()->domain ); ?></label>
          </li>
          <li>
            <label><input type="checkbox" name="wpp_terms[<?php echo $slug; ?>][public]" <?php checked( $data['public'], true ); ?> value="true"/> <?php _e( 'Public', ud_get_wpp_terms()->domain ); ?></label>
          </li>
          <li>
            <label><input type="checkbox" name="wpp_terms[<?php echo $slug; ?>][show_in_nav_menus]" <?php checked( $data['show_in_nav_menus'], true ); ?> value="true"/> <?php _e( 'Show in Nav Menus', ud_get_wpp_terms()->domain ); ?></label>
          </li>
          <li>
            <label><input type="checkbox" name="wpp_terms[<?php echo $slug; ?>][show_tagcloud]" <?php checked( $data['show_tagcloud'], true ); ?> value="true"/> <?php _e( 'Show in Tag Cloud', ud_get_wpp_terms()->domain ); ?></label>
          </li>
          <li>
            <label><input type="checkbox" name="wpp_terms[<?php echo $slug; ?>][show_ui]" <?php checked( $data['show_ui'], true ); ?> value="true"/> <?php _e( 'Show native WordPress metabox.', ud_get_wpp_terms()->domain ); ?></label>
          </li>
        </ul>
      </td>

      <td>
        <span class="wpp_delete_row wpp_link"><?php _e( 'Delete', ud_get_wpp_terms()->domain ); ?></span>
      </td>
    </tr>

  <?php endforeach; ?>
  </tbody>

  <tfoot>
  <tr>
    <td colspan='4'>
      <input type="button" class="wpp_add_row button-secondary" value="<?php _e( 'Add Row', 'wpp' ) ?>"/>
    </td>
  </tr>
  </tfoot>

</table>
