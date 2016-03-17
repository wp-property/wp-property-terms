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

        /**
         * Add Terms UI on Settings page.
         */
        if( current_user_can( 'manage_wpp_categories' ) ) {

          /** Add Settings on Developer Tab */
          add_filter( 'wpp::settings_developer::tabs', function( $tabs ){
            $tabs['terms'] = array(
              'label' => __( 'Terms', ud_get_wpp_terms()->domain ),
              'template' => ud_get_wpp_terms()->path( 'static/views/admin/settings-developer-terms.php', 'dir' ),
              'order' => 25
            );
            return $tabs;
          } );

          /** Add Hidden Taxonomies on Types Tab */
          add_action( 'wpp::types::hidden_attributes', function( $property_slug ){
            include ud_get_wpp_terms()->path( 'static/views/admin/settings-hidden-terms.php', 'dir' );
          } );

          /** Add Inherited Taxonomies on Types Tab */
          add_action( 'wpp::types::inherited_attributes', function( $property_slug ){
            include ud_get_wpp_terms()->path( 'static/views/admin/settings-inherited-terms.php', 'dir' );
          } );

          add_action( 'wpp::save_settings', array( $this, 'save_settings' ) );

        }

        /** Load admin scripts */
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        /** Define our custom taxonomies. */
        add_filter( 'wpp_taxonomies', array( $this, 'define_taxonomies' ) );

        /** Prepare taxonomy's arguments before registering taxonomy. */
        add_filter( 'wpp::register_taxonomy', array( $this, 'prepare_taxonomy' ), 99, 2 );

        /** Add Meta Box to manage taxonomies on Edit Property page. */
        add_filter( 'wpp::meta_boxes', array( $this, 'add_meta_box' ), 99 );
        /** Handle inherited taxonomies on property saving. */
        add_action( 'save_property', array( $this, 'save_property' ), 11 );

        /** Search hooks ( get_properties, property_overview shortcode, etc ) */
        add_filter( 'get_queryable_keys', array( $this, 'get_queryable_keys' ) );
        add_filter( 'wpp::get_properties::custom_case', array( $this, 'custom_search_case' ), 99, 2 );
        add_filter( 'wpp::get_properties::custom_key', array( $this, 'custom_search_query' ), 99, 3 );
        /** Add Search fields on 'All Properties' page ( admin panel ) */
        add_filter( 'wpp::overview::filter::fields', array( $this, 'get_filter_fields' ) );

        /** Property Search shortcode hooks */
        add_filter( 'wpp::search_attribute::label', array( $this, 'get_search_attribute_label' ), 10, 2 );

        /** on Clone Property action */
        add_action( 'wpp::clone_property::action', array( $this, 'clone_property_action' ), 99, 2 );

        add_action( 'admin_menu' , array( $this, 'maybe_remove_native_meta_boxes' ), 11 );
      }

      /**
       * Manage Admin Scripts and Styles.
       *
       */
      public function enqueue_scripts() {
        global $current_screen;

        switch( $current_screen->id ) {

          /** Edit Property page */
          case 'property':
            wp_enqueue_style( 'wpp-terms-admin-property', $this->path( '/static/styles/wpp.terms.property.css', 'url' ) );
            break;

        }

      }

      /**
       * Fix label for Taxonomy in Property Search form
       *
       * @see draw_property_search_form()
       * @action wpp::search_attribute::label
       * @param string $label
       * @param string $taxonomy
       * @return string $label
       */
      public function get_search_attribute_label( $label, $taxonomy ) {
        $taxonomies = get_object_taxonomies( 'property' );
        if( in_array( $taxonomy, $taxonomies ) ) {
          $taxonomy = get_taxonomy( $taxonomy );
          $label = $taxonomy->labels->name;
          if( is_admin() ) {
            $label .= ' (' . __( 'taxonomy' ) . ')';
          }
        }
        return $label;
      }

      /**
       * Remove Taxonomy Meta Boxes if they added
       * for hidden and inherited taxonomies to prevent issues.
       *
       */
      public function maybe_remove_native_meta_boxes() {

        if( isset( $_REQUEST['post'] ) && is_numeric( $_REQUEST['post'] ) ) {
          $type = get_post_meta( $_REQUEST['post'], 'property_type', true );
        }

        if( !isset( $type ) ) {
          return;
        }

        /** Remove meta boxes for all inherited taxonomies */
        $inherited = $this->get( 'config.inherited.' . $type, array() );
        if( !empty( $inherited ) && is_array( $inherited ) ) {
          foreach( $inherited as $taxonomy ) {
            remove_meta_box( 'tagsdiv-' . $taxonomy, 'property', 'side' );
          }
        }

        /** Remove meta boxes for all hidden taxonomies */
        $hidden = $this->get( 'config.hidden.' . $type, array() );
        if( !empty( $hidden ) && is_array( $hidden ) ) {
          foreach( $hidden as $taxonomy ) {
            remove_meta_box( 'tagsdiv-' . $taxonomy, 'property', 'side' );
          }
        }

      }

      /**
       * Maybe extend taxonomy functionality
       *
       */
      public function maybe_extend_taxonomies() {

        $taxonomies = $this->get( 'config.taxonomies', array() );

        $exclude = array();

        foreach( $taxonomies as $key => $data ) {
          if( !isset( $data[ 'rich_taxonomy' ] ) || !$data[ 'rich_taxonomy' ] ) {
            array_push( $exclude, $key );
          }
        }

        new \UsabilityDynamics\CFTPB\Loader( array(
          'post_types' => array( 'property' ),
          'exclude' => $exclude,
        ) );

      }

      /**
       * Handle inherited taxonomies on property saving.
       *
       * @see \UsabilityDynamics\WPP\WPP_Core::save_property
       * @action save_property
       * @param in $post_id
       */
      public function save_property( $post_id ) {

        //* Check if property has children */
        $children = get_children( "post_parent=$post_id&post_type=property" );
        //* Write any data to children properties that are supposed to inherit things */
        if( count( $children ) > 0 ) {
          //* Go through all children */
          foreach( $children as $id => $data ) {
            //* Determine child property_type */
            $type = get_post_meta( $id, 'property_type', true );
            //* Check if child's property type has inheritence rules, and if meta_key exists in inheritance array */
            $inherited = $this->get( 'config.inherited.' . $type, array() );
            if( !empty( $inherited ) && is_array( $inherited ) ) {
              foreach( $inherited as $taxonomy ) {
                $terms = wp_get_object_terms( $post_id, $taxonomy, array("fields" => "ids") );
                wp_set_object_terms( $id, $terms, $taxonomy );
              }
            }
          }
        }

      }

      /**
       * Apply filter fields for available taxonomies.
       *
       * @see \UsabilityDynamics\WPP\Admin_Overview::get_filter_fields()
       * @action wpp::overview::filter::fields
       * @param array $fields
       * @return array
       */
      public function get_filter_fields( $fields ) {
        if( !is_array( $fields ) ) {
          $fields = array();
        }

        /* Get all existing fields names */
        $defined = array();
        foreach( $fields as $field ) {
          array_push( $defined, $field['id'] );
        }

        $taxonomies = $this->get( 'config.taxonomies', array() );
        if( !empty($taxonomies) && is_array($taxonomies) ) {
          foreach( $taxonomies as $k => $v ) {
            /* Ignore taxonomy if field with the same name already exists */
            if( in_array( $k, $defined ) ) {
              continue;
            }
            array_push( $fields, array(
              'id' => $k,
              'name' => $v['label'],
              'type' => 'taxonomy',
              'multiple' => true,
              'js_options' => array(
                'allowClear' => false,
              ),
              'options' => array(
                'taxonomy' => $k,
                'type' => 'select_advanced',
                'args' => array(),
              ),
              'map' => array(
                'class' => 'taxonomy',
              ),
            ) );
          }
        }
        return $fields;
      }

      /**
       * On property cloning we also clone terms.
       *
       * @see UsabilityDynamics\WPP\Ajax::action_wpp_clone_property()
       * @action wpp::clone_property::action
       * @param array $old_property
       * @param int $new_post_id
       */
      public function clone_property_action( $old_property, $new_post_id ) {
        $taxonomies = $this->get( 'config.taxonomies', array() );

        if( !empty($taxonomies) && is_array($taxonomies) ) {
          foreach( $taxonomies as $k => $v ) {
            $terms = wp_get_object_terms( $old_property['ID'], $k, array("fields" => "ids") );
            if( !empty( $terms ) ) {
              wp_set_object_terms( $new_post_id, $terms, $k );
            }
          }
        }
      }
      
      /**
       * Determine if search key belongs taxonomy.
       * 
       * @action wpp::get_properties::custom_case
       * @see WPP_F::get_properties()
       * @param bool $bool
       * @param string $key
       * @return bool
       */
      public function custom_search_case( $bool, $key ) {
        $taxonomies = $this->get( 'config.taxonomies', array() );
        if( !empty( $taxonomies ) && is_array( $taxonomies ) && in_array( $key, array_keys($taxonomies) ) ) {
          return true;
        }
        return $bool;
      }
      
      /**
       * Do search for taxonomies.
       * 
       * @param array $matching_ids
       * @param string $key
       * @param string $criteria
       * @return array
       */
      public function custom_search_query( $matching_ids, $key, $criteria ) {
        // Be sure that queried key belongs to taxonomy
        $taxonomies = $this->get( 'config.taxonomies', array() );
        if( empty( $taxonomies ) || !is_array( $taxonomies ) || !in_array( $key, array_keys($taxonomies) ) ) {
          return $matching_ids;
        }

        if( !is_array( $criteria ) ) {
          $criteria = explode( ',', trim( $criteria ) );
        }

        $is_numeric = true;
        foreach($criteria as $i => $v) {
          $criteria[$i] = trim($v);
          if( !is_numeric($criteria[$i]) ) {
            $is_numeric = false;
          }
        }

        if($is_numeric) {
          $tax_query = array(
            array(
              'taxonomy' => $key,
              'field'    => 'term_id',
              'terms'    => $criteria,
            ),
          );
        } else {
          $tax_query = array(
            'relation' => 'OR',
            array(
              'taxonomy' => $key,
              'field'    => 'name',
              'terms'    => $criteria,
            ),
            array(
              'taxonomy' => $key,
              'field'    => 'slug',
              'terms'    => $criteria,
            ),
          );
        }

        $args = array(
          'post_type' => 'property',
          'post_status' => 'publish',
          'posts_per_page' => '-1',
          'tax_query' => $tax_query,
        );

        if( !empty( $matching_ids ) && is_array( $matching_ids ) ) {
          $args[ 'post__in' ] = $matching_ids;
        }

        $wp_query = new \WP_Query( $args );
        $matching_ids = array();
        if( $wp_query->have_posts() ) {
          while ( $wp_query->have_posts() ) {
            $wp_query->the_post();
            array_push( $matching_ids, get_the_ID() );
          }
          wp_reset_postdata();
        }
        
        return $matching_ids;
      }
      
      /**
       * Adds taxonomies keys to queryable keys list.
       * 
       * @see WPP_F::get_queryable_keys()
       * @param array $keys
       * @return array
       */
      public function get_queryable_keys( $keys ) {
        $taxonomies = $this->get( 'config.taxonomies', array() );
        if( !empty( $taxonomies ) && is_array( $taxonomies ) && is_array( $keys ) ) {
          $keys = array_unique( array_merge( $keys, array_keys($taxonomies) ) );
        }
        return $keys;
      }

      /**
       * Save custom Taxonomies
       *
       */
      public function save_settings( $data ) {
        if( !empty( $data[ 'wpp_terms' ] ) ) {

          /** Take care about available taxonomies */
          if( !empty($data[ 'wpp_terms' ][ 'taxonomies' ]) && is_array( $data[ 'wpp_terms' ][ 'taxonomies' ] ) ) {
            $taxonomies = array();
            foreach( $data[ 'wpp_terms' ][ 'taxonomies' ] as $taxonomy => $v ) {
              $taxonomy = substr($taxonomy, 0, 32);
              /* Ignore missed Taxonomy */
              if( empty( $v[ 'label' ] ) && count( $data[ 'wpp_terms' ] ) == 1 ) {
                break;
              }
              $taxonomies[ $taxonomy ] = $this->prepare_taxonomy( $v, $taxonomy );
            }
            $this->set( 'config.taxonomies', $taxonomies );
          }

          /** Take care about taxonomies groups */
          if( isset($data[ 'wpp_terms' ][ 'groups' ]) ) {
            $this->set( 'config.groups', $data[ 'wpp_terms' ][ 'groups' ] );
          }

          /** Take care about taxonomies types */
          if( isset($data[ 'wpp_terms' ][ 'types' ]) ) {
            $this->set( 'config.types', $data[ 'wpp_terms' ][ 'types' ] );
          }

          /** Take care about hidden taxonomies */
          if( isset($data[ 'wpp_terms' ][ 'hidden' ]) ) {
            $this->set( 'config.hidden', $data[ 'wpp_terms' ][ 'hidden' ] );
          }

          /** Take care about inherited taxonomies */
          if( isset($data[ 'wpp_terms' ][ 'inherited' ]) ) {
            $this->set( 'config.inherited', $data[ 'wpp_terms' ][ 'inherited' ] );
          }

          $this->settings->commit();
        }
      }

      /**
       * Register Meta Box for taxonomies on Edit Property Page
       *
       * @param $meta_boxes
       * @return array
       */
      public function add_meta_box( $meta_boxes ) {

        if( !is_array( $meta_boxes ) ) {
          $meta_boxes = array();
        }

        $type = false;
        if( isset( $_REQUEST['post'] ) && is_numeric( $_REQUEST['post'] ) ) {
          $post_id = $_REQUEST['post'];
          $type = get_post_meta( $post_id, 'property_type', true );
        }

        $taxonomies = $this->get( 'config.taxonomies', array() );
        $groups = $this->get( 'config.groups', array() );
        $types = $this->get( 'config.types', array() );

        $hidden = array();
        $inherited = array();
        if( $type ) {
          $hidden = $this->get( 'config.hidden.' . $type, array() );
          $inherited = $this->get( 'config.inherited.' . $type, array() );
        }

        $fields = array();

        foreach($taxonomies as $k => $d) {

          $field = array();

          switch( true ) {
            // Hidden
            case ( in_array( $k, $hidden ) ):
              // Ignore field, since it's hidden.
              break;
            case ( in_array( $k, $inherited ) ):
              $field = array(
                'name' => $d['label'],
                'id' => $k,
                'type' => 'wpp_taxonomy_inherited',
                'desc' => sprintf( __( 'The terms are inherited from Parent %s.', $this->get('domain') ), \WPP_F::property_label() ),
                'options' => array(
                  'taxonomy' => $k,
                  'type' => 'inherited',
                  'args' => array(),
                )
              );
              break;

            default:
              /** Do no add taxonomy field if native meta box is being used for it. */
              if( $d[ 'show_ui' ] ) {
                break;
              }
              $field = array(
                'name' => $d['label'],
                'id' => $k,
                'type' => 'taxonomy',
                'multiple' => ( isset( $types[ $k ] ) && $types[ $k ] == 'unique' ? false : true ),
                'options' => array(
                  'taxonomy' => $k,
                  'type' => ( isset( $d[ 'hierarchical' ] ) && $d[ 'hierarchical' ] == true ? 'select_tree' : 'select_advanced' ),
                  'args' => array(),
              ) );
              break;
          }

          if( !empty($field) ) {

            $group = !empty( $groups[ $k ] ) ? $groups[ $k ] : '_general';

            $pushed = false;
            foreach( $meta_boxes as $k => $meta_box ) {
              if( $group == $meta_box[ 'id' ] ) {
                if( !isset( $meta_boxes[$k][ 'fields' ] ) || !is_array( $meta_boxes[$k][ 'fields' ] ) ) {
                  $meta_boxes[$k][ 'fields' ] = array();
                }
                array_push( $meta_boxes[$k][ 'fields' ], $field );
                $pushed = true;
                break;
              }
            }

            if( !$pushed ) {
              array_push( $fields, $field );
            }

          }

        }

        /** It may happen only if we could not find related group. */
        if( !empty( $fields ) ) {

          $taxonomy_box = array(
            'id' => '_terms',
            'title' => __( 'Taxonomies', ud_get_wpp_terms()->domain ),
            'pages' => array( 'property' ),
            'context' => 'advanced',
            'priority' => 'low',
            'fields' => $fields,
          );

          $_meta_boxes = array();
          $added = false;
          foreach( $meta_boxes as $meta_box ) {
            /** We want to add Taxonomies under General Meta Box */
            array_push($_meta_boxes,  $meta_box );
            if( $meta_box['id'] == '_general' ) {
              array_push($_meta_boxes,  $taxonomy_box );
              $added = true;
            }
          }

          /* In case we did not add meta box, we do it at last. */
          if(!$added) {
            array_push($_meta_boxes,  $taxonomy_box );
          }
          $meta_boxes = $_meta_boxes;

        }

        return $meta_boxes;
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
            'types' => array(
              'multiple' => array(
                'label' => __( 'Multiple Terms', $this->domain ),
                'desc'  => __( 'Property can have multiple terms. It\'s a native WordPress functionality.', $this->domain ),
              ),
              'unique' => array(
                'label' => __( 'Unique Term', $this->domain ),
                'desc'  => __( 'Property can have only one term. Be sure to not enable native Meta Box for current taxonomy to prevent issues.', $this->domain ),
              ),
            )
          )
        ));

        /** Be sure that we have any taxonomy to register. If not, we set default taxonomies of WP-Property. */
        if( !$this->get( 'config.taxonomies' ) ) {
          $this->set( 'config.taxonomies', $taxonomies );
        }

        /**
         * Rich Taxonomies ( adds taxonomy post type )
         */
        $this->maybe_extend_taxonomies();

        /**
         * Extend Property Search with Taxonomies
         */
        $this->extend_wpp_settings();

        return $this->get( 'config.taxonomies', array() );
      }

      /**
       * Extend WP-Property settings:
       * - Extend Property Search with Taxonomies
       * - Adds Taxonomies to groups
       *
       */
      public function extend_wpp_settings() {
        global $wp_properties;

        /** STEP 1. Add taxonomies to searchable attributes */

        $taxonomies = $this->get( 'config.taxonomies', array() );

        if( !isset( $wp_properties[ 'searchable_attributes' ] ) || !is_array( $wp_properties[ 'searchable_attributes' ] ) ) {
          $wp_properties[ 'searchable_attributes' ] = array();
        }

        foreach( $taxonomies as $taxonomy => $data ) {
          if( isset( $data['public'] ) && $data['public'] ) {
            array_push( $wp_properties[ 'searchable_attributes' ], $taxonomy );
          }
        }

        ud_get_wp_property()->set( 'searchable_attributes', $wp_properties[ 'searchable_attributes' ] );

        /** STEP 2. Add taxonomies to property stats groups */

        $groups = $this->get( 'config.groups', array() );

        if( !isset( $wp_properties[ 'property_stats_groups' ] ) || !is_array( $wp_properties[ 'property_stats_groups' ] ) ) {
          $wp_properties[ 'property_stats_groups' ] = array();
        }

        $wp_properties[ 'property_stats_groups' ] = array_merge( $wp_properties[ 'property_stats_groups' ], $groups );

        ud_get_wp_property()->set( 'property_stats_groups', $wp_properties[ 'property_stats_groups' ] );

        /** STEP 3. Extend Property Search form */

        add_filter( 'wpp::show_search_field_with_no_values', function( $bool, $slug ) {
          $taxonomies = ud_get_wpp_terms( 'config.taxonomies', array() );
          if( array_key_exists( $slug, $taxonomies ) ) {
            return true;
          }
          return $bool;
        }, 10, 2 );

        /** Take care about Taxonomies fields */
        foreach( $taxonomies as $taxonomy => $data ){

          add_filter( 'wpp_search_form_field_' . $taxonomy, function( $html, $taxonomy, $label, $value, $input, $random_id ) {

            $search_input = ud_get_wp_property( "searchable_attr_fields.{$taxonomy}" );
            $terms = get_terms( $taxonomy, array( 'fields' => 'id=>name' ) );

            ob_start();

            switch( $search_input ) {

              case 'multi_checkbox':
                ?>
                <ul class="wpp_multi_checkbox taxonomy <?php echo $taxonomy; ?>">
                  <?php foreach ( $terms as $term_id => $label ) : ?>
                    <?php $unique_id = rand( 10000, 99999 ); ?>
                    <li>
                      <input name="wpp_search[<?php echo $taxonomy; ?>][]" <?php echo( is_array( $value ) && in_array( $term_id, $value ) ? 'checked="true"' : '' ); ?> id="wpp_attribute_checkbox_<?php echo $unique_id; ?>" type="checkbox" value="<?php echo $term_id; ?>"/>
                      <label for="wpp_attribute_checkbox_<?php echo $unique_id; ?>" class="wpp_search_label_second_level"><?php echo $label; ?></label>
                    </li>
                  <?php endforeach; ?>
                </ul>
                <?php
                break;

              case 'dropdown':
              default:
                ?>
                <select id="<?php echo $random_id; ?>" class="wpp_search_select_field taxonomy <?php echo $taxonomy; ?>" name="wpp_search[<?php echo $taxonomy; ?>]">
                  <option value="-1"><?php _e( 'Any', ud_get_wpp_terms('domain') ) ?></option>
                  <?php foreach ( $terms as $term_id => $label ) : ?>
                    <option value="<?php echo $term_id; ?>" <?php selected( $value, $term_id ); ?>><?php echo $label; ?></option>
                  <?php endforeach; ?>
                </select>
                <?php
                break;

            }

            return ob_get_clean();

          }, 10, 6 );

        }
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
          'rich_taxonomy' => false,
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

        if( $args[ 'hierarchical' ] ) {
          $args[ 'rewrite' ][ 'hierarchical' ] = true;
        }

        return $args;
      }

      /**
       * Plugin Activation
       *
       */
      public function activate() {
        //** flush Object Cache */
        wp_cache_flush();
        //** set transient to flush WP-Property cache */
        set_transient( 'wpp_cache_flush', time() );
      }

      /**
       * Plugin Deactivation
       *
       */
      public function deactivate() {
        //** flush Object Cache */
        wp_cache_flush();
      }

    }

  }

}
