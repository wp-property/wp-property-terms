<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;
require_once RWMB_FIELDS_DIR . 'checkbox-list.php';

if ( ! class_exists( 'RWMB_Wpp_Taxonomy_Field' ) )
{
	class RWMB_Wpp_Taxonomy_Field extends RWMB_Taxonomy_Field
	{
		/**
		 * Enqueue scripts and styles
		 *
		 * @return void
		 */
		static function admin_enqueue_scripts()
		{
			RWMB_Select_Advanced_Field::admin_enqueue_scripts();
			RWMB_Wpp_Select_Advanced_Field::admin_enqueue_scripts();
			wp_enqueue_style( 'rwmb-taxonomy', RWMB_CSS_URL . 'taxonomy.css', array(), RWMB_VER );
			wp_enqueue_script( 'rwmb-taxonomy', RWMB_JS_URL . 'taxonomy.js', array( 'rwmb-select-advanced' ), RWMB_VER, true );
		}

		/**
		 * Get field HTML
		 *
		 * @param $field
		 * @param $meta
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			$options = $field['options'];
			$terms   = get_terms( $options['taxonomy'], $options['args'] );
			$field['_options']      = $options;
			$field['_terms']      	= $terms;
			$field['options']      = self::get_options( $terms );
			$field['display_type'] = $options['type'];

			$html = '';

			switch ( $options['type'] )
			{
				case 'checkbox_list':
					$html = RWMB_Checkbox_List_Field::html( $meta, $field );
					break;
				case 'checkbox_tree':
					$elements = self::process_terms( $terms );
					$html .= self::walk_checkbox_tree( $meta, $field, $elements, $options['parent'], true );
					break;
				case 'select_tree':
					$elements = self::process_terms( $terms );
					$html .= self::walk_select_tree( $meta, $field, $elements, $options['parent'], true );
					break;
				case 'select_advanced':
					if($field['multiple'] == true)
						$html = RWMB_Wpp_Select_Advanced_Field::html( $meta, $field );
					else // if it's not  multiple using default select advance field
						$html = RWMB_Select_Advanced_Field::html( $meta, $field );
					break;
				case 'select':
				default:
					$html = RWMB_Select_Field::html( $meta, $field );
			}

			return $html;
		}

		/**
		 * Save meta value
		 *
		 * @param mixed $new
		 * @param mixed $old
		 * @param int   $post_id
		 * @param array $field
		 *
		 * @return string
		 */
		static function save( $new, $old, $post_id, $field )
		{
			$new = array_unique( (array) $new );
			if(empty( $new ) || count($new) == 0){
				$new = null;
			}
			else{
				foreach ($new as $key => $term) {
					if(!is_numeric($term) && $term != '' ){
						if(!$t = term_exists($term, $field['options']['taxonomy']))
							$t = wp_insert_term( $term, $field['options']['taxonomy']);
						if(!is_wp_error($t))
							$new[$key] = $t['term_id'];
					}
				}
			}
			$new = array_unique( array_map( 'intval', (array) $new ) );
			wp_set_object_terms( $post_id, $new, $field['options']['taxonomy'] );
		}
	}
}
