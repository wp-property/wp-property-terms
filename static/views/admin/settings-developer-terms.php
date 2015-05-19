<?php
/**
 * Settings 'Developer' Tab
 * Section 'Taxonomies'
 */

?>
<h3><?php printf( __( '%1s Taxonomies', 'wpp' ), WPP_F::property_label() ); ?></h3>
<p><?php printf( __( 'Manage your %s Taxonomies here. Note, you can not remove all taxonomies, in this case default WP-Property taxonomies will be returned back.', ud_get_wpp_terms()->domain ), WPP_F::property_label() ); ?></p>

<table id="" class="ud_ui_dynamic_table widefat">
  <thead>
  <tr>
    <th class='wpp_draggable_handle'>&nbsp;</th>
    <th class='wpp_attribute_name_col'><?php _e( 'Label', 'wpp' ) ?></th>
    <th class='wpp_attribute_group_col'><?php _e( 'Group', 'wpp' ) ?></th>
    <th class='wpp_settings_col'><?php _e( 'Settings', 'wpp' ) ?></th>
    <th class='wpp_delete_col'>&nbsp;</th>
  </tr>
  </thead>
  <tbody>
  <?php foreach( (array) ud_get_wpp_terms( 'config.taxonomies', array() ) as $slug => $data ): ?>
    <?php

    $data = ud_get_wpp_terms()->prepare_taxonomy( $data, $slug );
    $gslug = ud_get_wpp_terms( "config.groups.{$slug}" );
    $group = ud_get_wp_property( "property_groups.{$gslug}" );

    ?>
    <tr class="wpp_dynamic_table_row" slug="<?php echo $slug; ?>" <?php echo( !empty( $gslug ) ? "wpp_attribute_group=\"" . $gslug . "\"" : "" ); ?> style="<?php echo( !empty( $group[ 'color' ] ) ? "background-color:" . $group[ 'color' ] : "" ); ?>" slug="<?php echo $slug; ?>" new_row='false'>
      <th class='wpp_draggable_handle'>&nbsp;</th>
      <td>
        <ul>
          <li>
            <input class="slug_setter" type="text" name="wpp_terms[taxonomies][<?php echo $slug; ?>][label]" value="<?php echo $data['label']; ?>"/>
          </li>
          <li class="hide-on-new-row">
            <a href="<?php echo admin_url( "edit-tags.php?taxonomy={$slug}&post_type=property" ); ?>"><?php _e( 'Manage Terms', ud_get_wpp_terms()->domain ); ?></a>
          </li>

          </li>
            <input type="text" class="slug" readonly='readonly' value="<?php echo $slug; ?>"/>
          </li>

        </ul>

      <td class="wpp_attribute_group_col">
        <input type="text" class="wpp_attribute_group wpp_taxonomy_group" value="<?php echo( !empty( $group[ 'name' ] ) ? $group[ 'name' ] : "" ); ?>"/>
        <input type="hidden" class="wpp_group_slug" name="wpp_terms[groups][<?php echo $slug; ?>]" value="<?php echo( !empty( $gslug ) ? $gslug : "" ); ?>">
      </td>

      <td>
        <ul>
          <li class=""">
            <label><input type="checkbox" name="wpp_terms[taxonomies][<?php echo $slug; ?>][hierarchical]" <?php checked( $data['hierarchical'], true ); ?> value="true"/> <?php _e( 'Hierarchical', ud_get_wpp_terms()->domain ); ?></label>
          </li>

          <li class=""">
            <label><input type="checkbox" name="wpp_terms[taxonomies][<?php echo $slug; ?>][public]" <?php checked( $data['public'], true ); ?> value="true"/> <?php _e( 'Public & Searchable', ud_get_wpp_terms()->domain ); ?></label>
          </li>

          <li class=""">
            <label><input type="checkbox" name="wpp_terms[taxonomies][<?php echo $slug; ?>][show_in_nav_menus]" <?php checked( $data['show_in_nav_menus'], true ); ?> value="true"/> <?php _e( 'Show in Nav Menus', ud_get_wpp_terms()->domain ); ?></label>
          </li>

          <li>
            <label><input type="checkbox" name="wpp_terms[taxonomies][<?php echo $slug; ?>][show_tagcloud]" <?php checked( $data['show_tagcloud'], true ); ?> value="true"/> <?php _e( 'Show in Tag Cloud', ud_get_wpp_terms()->domain ); ?></label>
          </li>

          <li class=""">
            <label><input type="checkbox" name="wpp_terms[taxonomies][<?php echo $slug; ?>][show_ui]" <?php checked( $data['show_ui'], true ); ?> value="true"/> <?php _e( 'Show in Menu and native WP Meta Box', ud_get_wpp_terms()->domain ); ?></label>
          </li>

          <li>
            <label><input type="checkbox" name="wpp_terms[taxonomies][<?php echo $slug; ?>][rich_taxonomy]" <?php checked( $data['rich_taxonomy'], true ); ?> value="true"/> <?php _e( 'Rich Taxonomy', ud_get_wpp_terms()->domain ); ?></label>
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
