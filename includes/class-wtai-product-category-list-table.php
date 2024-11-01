<?php
/**
 * Custom WP List class for category lists
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Extends WP List Table
 */
class WTAI_Product_Category_List_Table extends WP_List_Table {
	/**
	 * Level.
	 *
	 * @var int
	 */
	private $level;

	/**
	 * Callback arguments.
	 *
	 * @var array
	 */
	public $callback_args;

	/**
	 * API results.
	 *
	 * @var array
	 */
	public $api_results;

	/**
	 * Taxonomy name.
	 *
	 * @var string
	 */
	public $taxonomy = 'product_cat';

	/**
	 * WTA max number of pages.
	 *
	 * @var int
	 */
	public $wtai_max_num_pages = 0;

	/**
	 * Has Filter.
	 *
	 * @var bool
	 */
	private $has_filter;

	/**
	 * Prepare the items to display
	 */
	public function prepare_items() {
		$taxonomy = $this->taxonomy;

		$tags_per_page = $this->get_items_per_page( "edit_{$taxonomy}_per_page" );

		$orderby      = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$order        = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$search_query = ( ! empty( $_REQUEST['s'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		$search = ! empty( $search_query ) ? trim( wp_unslash( $search_query ) ) : '';

		$args = array(
			'taxonomy'   => $taxonomy,
			'search'     => $search,
			'page'       => $this->get_pagenum(),
			'number'     => $tags_per_page,
			'hide_empty' => 0,
		);

		if ( ! empty( $orderby ) ) {
			if ( 'wtai_generate_date' === $orderby || 'wtai_transfer_date' === $orderby ) {
				$parsed_orderby = str_replace( 'wta', 'wtai', trim( wp_unslash( $orderby ) ) );

				$sorted_term_ids = $this->get_sorted_term_ids( $parsed_orderby, $order );

				$args['orderby']               = 'name';
				$args['wtai_custom_term_sort'] = $sorted_term_ids;
			} elseif ( 'wtai_title' === $orderby ) {
					$args['orderby'] = 'name';
			} else {
				$args['orderby'] = trim( wp_unslash( $orderby ) );
			}
		}

		if ( ! empty( $order ) ) {
			$args['order'] = trim( wp_unslash( $order ) );
		}

		$args['offset'] = ( $args['page'] - 1 ) * $args['number'];

		// Save the values because 'number' and 'offset' can be subsequently overridden.
		$this->callback_args = $args;

		$check_hierarchy = false;
		if ( is_taxonomy_hierarchical( $taxonomy ) && ! isset( $args['orderby'] ) ) {
			// We'll need the full set of terms then.
			$args['number'] = 0;
			$args['offset'] = $args['number'];
		}

		// Filter.
		$filter_results = $this->get_filtered_category_ids();

		$this->has_filter = false;
		if ( $filter_results ) {
			$filtered_category_ids = $filter_results['category_ids'];
			$wtai_writetext_status = $filter_results['wtai_writetext_status'];
			if ( ( 'not_generated' === $wtai_writetext_status || 'no_activity' === $wtai_writetext_status ) && $filtered_category_ids && -1 !== intval( $filtered_category_ids[0] ) ) {
				$args['exclude'] = $filtered_category_ids;

				$this->has_filter = true;
			} elseif ( $filtered_category_ids ) {
				$args['include'] = $filtered_category_ids;

				$this->has_filter = true;
			}
		}

		$this->items = $this->wtai_list_table_data( $args );

		// Get all defined columns.
		$wtai_column = $this->get_columns();

		// Get all defined hidden columns.
		$wtai_hd_column = array();

		// Get all defined sortable columns.
		$wtai_sortable = $this->get_sortable_columns();

		// Pass the data to headers to apply.
		$this->_column_headers = array( $wtai_column, $wtai_hd_column, $wtai_sortable );

		$total_args = $args;
		unset( $total_args['page'] );
		unset( $total_args['number'] );
		unset( $total_args['offset'] );

		$total_items = wp_count_terms(
			$total_args
		);

		$this->wtai_max_num_pages = ceil( $total_items / $tags_per_page );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $tags_per_page,
			)
		);
	}

	/**
	 * Display the no items label.
	 */
	public function no_items() {
		echo wp_kses_post( get_taxonomy( $this->taxonomy )->labels->not_found );
	}

	/**
	 * Display the rows or placeholder.
	 */
	public function display_rows_or_placeholder() {
		$taxonomy = $this->taxonomy;

		$number = $this->callback_args['number'];
		$offset = $this->callback_args['offset'];

		// Convert it to table rows.
		$count = 0;

		if ( empty( $this->items ) || ! is_array( $this->items ) ) {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . esc_attr( $this->get_column_count() ) . '">';
			$this->no_items();
			echo '</td></tr>';
			return;
		}

		if ( is_taxonomy_hierarchical( $taxonomy ) && ! isset( $this->callback_args['orderby'] ) ) {
			if ( ! empty( $this->callback_args['search'] ) ) {// Ignore children on searches.
				$children = array();
			} else {
				$children = _get_term_hierarchy( $taxonomy );
			}

			/*
			 * Some funky recursion to get the job done (paging & parents mainly) is contained within.
			 * Skip it for non-hierarchical taxonomies for performance sake.
			 */
			$this->display_sub_rows( $taxonomy, $this->items, $children, $offset, $number, $count );
		} else {
			foreach ( $this->items as $term ) {
				$this->single_row( $term );
			}
		}
	}

	/**
	 * Get the columns item data
	 *
	 * @param array $args Arguments.
	 */
	public function wtai_list_table_data( $args = array() ) {
		$datas = array();

		// Custom sorting filter for the date columns.
		if ( isset( $args['wtai_custom_term_sort'] ) && is_array( $args['wtai_custom_term_sort'] ) ) {
			add_filter( 'terms_clauses', array( $this, 'add_custom_wtai_sorting' ), 10, 3 );
		}

		$terms = get_terms( $args );

		$term_ids = array();
		foreach ( $terms as $term ) {
			$term_ids[] = $term->term_id;
		}

		// Get API results.
		$meta_keys = apply_filters( 'wtai_category_fields', array() );
		$meta_keys = array_keys( $meta_keys );
		$fields    = array(
			'fields'       => $meta_keys,
			'single_value' => 1,
		);

		$api_results         = array();
		$api_record_per_page = 5;
		$api_total_records   = count( $term_ids );
		$api_max_page        = ceil( $api_total_records / $api_record_per_page );

		$api_results = array();
		for ( $api_page_no = 1; $api_page_no <= $api_max_page; $api_page_no++ ) {
			$batch_ids = array_slice( $term_ids, ( ( $api_page_no - 1 ) * $api_record_per_page ), $api_record_per_page );

			$batch_api_results = apply_filters( 'wtai_generate_category_text', array(), implode( ',', $batch_ids ), $fields );

			if ( $batch_api_results ) {
				foreach ( $batch_api_results as $api_record_id => $api_record ) {
					$api_results[ $api_record_id ] = $api_record;
				}
			}
		}

		$this->api_results = $api_results;

		foreach ( $terms as $term ) {
			$row = $this->parse_wtai_term_data( $term );

			$datas[] = $row;

			$term_ids[] = $term->term_id;
		}

		return $datas;
	}

	/**
	 * Parse term data
	 *
	 * @param object $term Term object.
	 */
	private function parse_wtai_term_data( $term = null ) {
		if ( ! $term ) {
			return;
		}

		global $wtai_product_category;

		$api_results = $this->api_results;

		$column_objs = $wtai_product_category->get_category_fields_list();

		$category_id          = $term->term_id;
		$category_name        = $term->name;
		$category_description = $term->description;
		$category_link        = get_term_link( $category_id );

		$thumbnail_id = get_term_meta( $category_id, 'thumbnail_id', true );

		if ( $thumbnail_id ) {
			$cat_image = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail' );
			$image_url = $cat_image[0];
		} else {
			$image_url = wc_placeholder_img_src();
		}

		$row = array(
			'wtai_id'            => $category_id,
			'wtai_thumb'         => '<img src="' . $image_url . '" class="wtai-thumb" />',
			'wtai_category_link' => $category_link,
			'wtai_title'         => '<a href="' . $category_link . '" target="_blank" class="wtai-cwe-action-title" >' . $category_name . '</a>',
			'wtai_language'      => apply_filters( 'wtai_column_language', get_locale(), $post ),
			'wtai_generate_date' => get_term_meta( $category_id, 'wtai_generate_date', true ),
			'wtai_transfer_date' => get_term_meta( $category_id, 'wtai_transfer_date', true ),
		);

		$seo_values = wtai_get_category_values( $category_id );

		foreach ( $column_objs as $column_obj_key => $column_obj_value ) {
			$column_name = $column_obj_key;

			$api_content = ''; // To be fetched from the API.
			if ( isset( $api_results[ $category_id ] ) ) {
				$api_content = $api_results[ $category_id ][ $column_obj_key ][0]['value'];
			}

			$platform_value = $seo_values[ $column_name ]; // To be fetched from the database.

			$platform_value = wpautop( nl2br( $platform_value ) );
			$api_content    = wpautop( nl2br( $api_content ) );

			$row[ $column_obj_key . '_full' ]           = $platform_value;
			$row[ $column_obj_key ]                     = ( $platform_value ) ? wp_trim_words( $platform_value, 15, '...' ) : '';
			$row[ 'wtai_' . $column_obj_key . '_full' ] = $api_content;
			$row[ 'wtai_' . $column_obj_key ]           = ( $api_content ) ? wp_trim_words( $api_content, 15, '...' ) : '';
		}

		$row['term'] = $term;

		return $row;
	}

	/**
	 * Define displayed columns
	 */
	public function get_columns() {
		global $wtai_product_category;
		$column_objs = $wtai_product_category->get_category_fields_list();

		$columns = array(
			'wtai_thumb' => '<img src="' . WTAI_DIR_URL . 'assets/images/ic_thumb.png" class="wtai-thumb" />',
			'wtai_title' => __( 'Category name', 'writetext-ai' ),
		);

		foreach ( $column_objs as $column_obj_key => $column_obj_value ) {
			$wp_header_label   = '';
			$wtai_header_label = '';
			if ( 'page_title' === $column_obj_key ) {
				$wp_header_label   = __( 'WordPress meta title', 'writetext-ai' );
				$wtai_header_label = __( 'WriteText.ai meta title', 'writetext-ai' );
			} elseif ( 'page_description' === $column_obj_key ) {
				$wp_header_label   = __( 'WordPress meta description', 'writetext-ai' );
				$wtai_header_label = __( 'WriteText.ai meta description', 'writetext-ai' );
			} elseif ( 'category_description' === $column_obj_key ) {
				$wp_header_label   = __( 'WordPress category description', 'writetext-ai' );
				$wtai_header_label = __( 'WriteText.ai category description', 'writetext-ai' );
			} elseif ( 'open_graph' === $column_obj_key ) {
				$wp_header_label   = __( 'WordPress Open Graph text', 'writetext-ai' );
				$wtai_header_label = __( 'WriteText.ai Open Graph text', 'writetext-ai' );
			}

			if ( $wtai_header_label ) {
				$columns[ 'wtai_' . $column_obj_key ] = $wtai_header_label;
			}
			if ( $wp_header_label ) {
				$columns[ $column_obj_key ] = $wp_header_label;
			}
		}
		$columns['wtai_language']      = __( 'Language', 'writetext-ai' );
		$columns['wtai_generate_date'] = __( 'Last edited in WriteText.ai', 'writetext-ai' );
		$columns['wtai_transfer_date'] = __( 'Last transferred to WordPress', 'writetext-ai' );

		return $columns;
	}

	/**
	 * Define sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'wtai_title'         => array( 'wtai_title', true ),
			'wtai_generate_date' => array( 'wtai_generate_date', true ),
			'wtai_transfer_date' => array( 'wtai_transfer_date', true ),
		);
	}

	/**
	 * Add default columns
	 *
	 * @param array  $item item.
	 * @param string $column_name column name.
	 */
	public function column_default( $item, $column_name ) {
		$post_id = $item['wtai_id'];

		$return_value = '';
		switch ( $column_name ) {
			case 'transfer_page_title':
			case 'transfer_page_description':
			case 'transfer_product_description':
			case 'transfer_product_excerpt':
			case 'transfer_open_graph':
				$field_ref = str_replace( 'transfer_', '', $column_name );
				$field_wta = str_replace( 'transfer_', 'wtai_', $column_name );
				$field_wta = isset( $item[ $field_wta ] ) ? $item[ $field_wta ] : '';
				if ( wtai_current_user_can( 'writeai_transfer_generated_text' ) ) {
					$field_wta = ! $field_wta ? 'wtai-disabled-button' : 'enabled_button';
				} else {
					$field_wta = 'enabled_button';
				}

				if ( wtai_current_user_can( 'writeai_transfer_generated_text' ) ) {
					$tooltip_transfer = 'tooltip-help-transfer';
				} else {
					$tooltip_transfer = '';
				}

				$transfer_text = __( 'Transfer', 'writetext-ai' );
				$title_attr    = $field_wta && wtai_current_user_can( 'writeai_transfer_generated_text' ) ? 'title="' . $transfer_text . '"' : '';

				$field_hidden = 'hidden';
				if ( 'enabled_button' === $field_wta ) {
					$field_hidden = '';
				}
				if ( wtai_current_user_can( 'writeai_transfer_generated_text' ) === false ) {
					return '';
				}

				break;
			case 'category_page_description':
			case 'category_open_graph':
				return isset( $item[ $column_name ] ) ? '<span class="wtai-text-description" >' . $item[ $column_name ] . '</span>' : '';
				break; // phpcs:ignore Squiz.PHP.NonExecutableCode.Unreachable
			case 'wtai_generate_date':
			case 'wtai_transfer_date':
				$item_value = '';
				if ( 'wtai_generate_date' === $column_name ) {
					$item_value = isset( $item['wtai_generate_date'] ) ? $item['wtai_generate_date'] : '';
				}
				if ( 'wtai_transfer_date' === $column_name ) {
					$item_value = isset( $item['wtai_transfer_date'] ) ? $item['wtai_transfer_date'] : '';
				}
				$item_value = $item[ $column_name ];

				if ( ! empty( $item_value ) ) {
					$t_time = sprintf(
						/* translators: %1$s: date  %2$s time */
						__( '%1$s at %2$s' ),
						date_i18n( get_option( 'date_format' ), $item_value ),
						date_i18n( get_option( 'time_format' ), $item_value )
					);
					return $t_time;
				}
				return '';
				break; // phpcs:ignore Squiz.PHP.NonExecutableCode.Unreachable
			default:
				return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
				break; // phpcs:ignore Squiz.PHP.NonExecutableCode.Unreachable
		}
	}

	/**
	 * Add bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array();

		return $actions;
	}

	/**
	 * Add row actions
	 *
	 * @param array  $item The current item.
	 * @param string $column_name The current column name.
	 * @param string $primary The primary column name.
	 */
	public function handle_row_actions( $item, $column_name, $primary ) {
		if ( $primary !== $column_name ) {
			return '';
		}

		$item_id       = $item['wtai_id'];
		$category_link = $item['wtai_category_link'];

		$disable_bulk_class = '';

		$action = array();

		$action['pid'] = '<span>ID: ' . $item_id . '</span>';

		$action['edit'] = '<a href="#" class="wtai-cwe-action-button wtai-cwe-action-button-edit" data-action="edit" >' . __( 'Edit', 'writetext-ai' ) . '</a>';

		$action['view'] = '<a href="' . $category_link . '" class="view" data-action="view" target="_blank">' . __( 'View', 'writetext-ai' ) . '</a>';

		return $this->row_actions( $action );
	}

	/**
	 * Display count per post status
	 */
	protected function get_views() {
		$status_links = array(); // No status links for category.
		return $status_links;
	}


	/**
	 * Add fields for filtering functions
	 *
	 * @param string $which Top or bottom.
	 */
	public function extra_tablenav( $which ) {

		if ( 'top' === $which ) {
			$meta_keys = apply_filters( 'wtai_category_fields', array() );

			$wtai_status = isset( $_GET['wtai_writetext_status'] ) ? sanitize_text_field( wp_unslash( $_GET['wtai_writetext_status'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification
			$wtai_fields = isset( $_GET['wtai_writetext_fields'] ) && is_array( $_GET['wtai_writetext_fields'] ) ? map_deep( wp_unslash( $_GET['wtai_writetext_fields'] ), 'sanitize_text_field' ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
			?>
				<form method="get" class="wtai-wp-table-list-filter">
					<input name="page" type="hidden" value="write-text-ai-category" />

				<?php if ( isset( $_GET['orderby'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
							<input name="orderby" type="hidden" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?>" />
					<?php endif; ?>
				<?php if ( isset( $_GET['order'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
							<input name="order" type="hidden" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?>" />
					<?php endif; ?>

				<?php
				$option_box = '';
				?>
					
					<div class="wtai-checkbox-dropdown" id="wtai-sel-writetext-status">
						<div class="wtai-filter-select wtai-filter-option-label"><span><?php echo wp_kses_post( __( 'Filter by WriteText.ai status', 'writetext-ai' ) ); ?></span></div>
						<div class="wtai-status-checkbox-options <?php echo esc_attr( $option_box ); ?>">
							
							<div class="wtai-d-flex">
								<div class="wtai-col wtai-col-1">
								<?php
								foreach ( $meta_keys  as $meta_key => $meta_label ) :
									$checked = '';
									// phpcs:ignore WordPress.Security.NonceVerification
									if ( ! isset( $_GET['wtai_writetext_fields'] ) ) {
										$checked = 'checked';
									} elseif ( ! empty( $wtai_fields ) && in_array( $meta_key, $wtai_fields, true ) ) {
										$checked = 'checked';
									}
									?>
										<label>
											<input type="checkbox" name="wtai_writetext_fields[]" value="<?php echo esc_attr( wp_unslash( $meta_key ) ); ?>" <?php echo esc_attr( $checked ); ?> />
										<?php echo wp_kses_post( $meta_label ); ?>
										</label>
									<?php endforeach; ?>
								</div>
								<div class="wtai-col wtai-col-2">
									<label>
										<input type="radio" class="wtai-status-rd wtai-all-status-rd" name="wtai_writetext_status" value="all" <?php echo $wtai_status ? checked( $wtai_status, 'all', false ) : 'checked'; ?> />
										<?php echo wp_kses_post( __( 'All', 'writetext-ai' ) ); ?>
										</label>
									<label>
										<input type="radio" class="wtai-status-rd" name="wtai_writetext_status" value="not_generated" <?php echo $wtai_status ? checked( $wtai_status, 'not_generated', false ) : 'checked'; ?> />
										<?php echo wp_kses_post( __( 'Not generated', 'writetext-ai' ) ); ?>
									</label>
									<label>
										<input type="radio" class="wtai-status-rd wtai-custom-status-rd" name="wtai_writetext_status" value="wtai_custom_status" <?php $wtai_status ? checked( $wtai_status, 'wtai_custom_status' ) : ''; ?> />
										<?php echo wp_kses_post( __( 'WriteText.ai status', 'writetext-ai' ) ); ?>
									</label>
									<?php
									$wtai_status_disabled                  = '';
									$wtai_label_status_disabled            = '';
									$wtai_writetext_custom_status_selected = isset( $_GET['wtai_writetext_custom_status'] ) ? map_deep( wp_unslash( $_GET['wtai_writetext_custom_status'] ), 'wp_kses_post' ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
									?>
									<div class="wtai-custom-grid-status-wrap" >
										<label class="wtai-custom-status-label <?php echo esc_attr( $wtai_label_status_disabled ); ?>">
											<input type="checkbox" class="wtai-custom-status-cb" <?php echo esc_attr( $wtai_status_disabled ); ?> name="wtai_writetext_custom_status[]" value="generated" <?php echo in_array( 'generated', $wtai_writetext_custom_status_selected, true ) ? 'checked' : ''; ?> />
											<span><?php echo wp_kses_post( __( 'Generated', 'writetext-ai' ) ); ?></span>
										</label>
										<label class="wtai-custom-status-label <?php echo esc_attr( $wtai_label_status_disabled ); ?>">
											<input type="checkbox" class="wtai-custom-status-cb" <?php echo esc_attr( $wtai_status_disabled ); ?> name="wtai_writetext_custom_status[]" value="edited" <?php echo in_array( 'edited', $wtai_writetext_custom_status_selected, true ) ? 'checked' : ''; ?> />
											<span><?php echo wp_kses_post( __( 'Edited', 'writetext-ai' ) ); ?></span>
										</label>
										<label class="wtai-custom-status-label wtai-custom-status-reviewed-label <?php echo esc_attr( $wtai_label_status_disabled ); ?>" >
											<input type="checkbox" class="wtai-custom-status-cb" <?php echo esc_attr( $wtai_status_disabled ); ?> name="wtai_writetext_custom_status[]" value="reviewed_not_transferred" <?php echo in_array( 'reviewed_not_transferred', $wtai_writetext_custom_status_selected, true ) ? 'checked' : ''; ?> />
											<span><?php echo wp_kses_post( __( 'Reviewed', 'writetext-ai' ) ); ?></span>
										</label>
										<label class="wtai-custom-status-label <?php echo esc_attr( $wtai_label_status_disabled ); ?>">
											<input type="checkbox" class="wtai-custom-status-cb" <?php echo esc_attr( $wtai_status_disabled ); ?> name="wtai_writetext_custom_status[]" value="transfered" <?php echo in_array( 'transfered', $wtai_writetext_custom_status_selected, true ) ? 'checked' : ''; ?> />
											<span><?php echo wp_kses_post( __( 'Transferred', 'writetext-ai' ) ); ?></span>
										</label>
									</div>
								</div>
							</div>
							<div class="wtai-activity-wrapper wtai-border-top wtai-button-text-length">
								<?php
								$last_no_activity = isset( $_GET['no_activity_days'] ) ? sanitize_text_field( wp_unslash( $_GET['no_activity_days'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
								if ( isset( $last_no_activity ) ) {
									$filter_date_from                = gmdate( 'Y-m-d', strtotime( "-{$last_no_activity} days" ) );
									$data_search['filter_date_from'] = $filter_date_from;

									$current_date                  = gmdate( 'Y-m-d' );
									$data_search['filter_date_to'] = $current_date;
									$filter_date_to                = $current_date;
								}

								if ( $last_no_activity ) {
									$last_no_activity = $last_no_activity;
								} else {
									$last_no_activity = 7;
								}
								?>
								<label class="wtai-lbl">
									<?php
									ob_start();
									?>
									<span class="wtai-input-group">
										<input type="number" class="wtai-no-activity-days" id="wtai-no-activity-days" 
											name="no_activity_days" 
											data-date_from="<?php echo esc_attr( $filter_date_from ); ?>" 
											data-date_to="<?php echo esc_attr( $filter_date_to ); ?>" 
											class="wtai-specs-input noactivity" value="<?php echo esc_attr( wp_unslash( $last_no_activity ) ); ?>" 
											data-mintext="1" 
											data-maxtext="365" 
											data-original-value="7" 
											title="<?php echo wp_kses_post( __( 'Please enter a value less than or equal to 365 days.', 'writetext-ai' ) ); ?>" >

										<span class="wtai-plus-minus-wrapper">
											<span class="dashicons dashicons-plus wtai-txt-plus noactivity noactivity-btn"></span>
											<span class="dashicons dashicons-minus wtai-txt-minus noactivity noactivity-btn"></span>
										</span>
									</span>
									<?php
									$day_filter_html = ob_get_clean();
									?>
									<input type="radio" class="wtai-status-rd" name="wtai_writetext_status" value="no_activity" <?php $wtai_status ? checked( $wtai_status, 'no_activity' ) : ''; ?> />
									<?php
									/* translators: %s: day input filter */
									$no_days_text = sprintf( __( 'Show categories without activity for the last %s day/s', 'writetext-ai' ), $day_filter_html );

									echo wp_kses( $no_days_text, wtai_kses_allowed_html() );
									?>
								</label>
							</div>
							<div class="wtai-reset wtai-btn-reset-status-wrapper">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=write-text-ai-category' ) ); ?>" class="wtai-btn-reset-status"><?php echo wp_kses_post( __( 'Reset', 'writetext-ai' ) ); ?></a>
							</div>
						</div>
					</div>

					<?php if ( isset( $_GET['s'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
						<input type="hidden" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?>" name="s" />
					<?php endif; ?>

					<div class="wtai-button-wrapper">
						<input type="submit" value="<?php echo wp_kses_post( __( 'Filter', 'writetext-ai' ) ); ?>" class="button" id="wtai-filter-submit">
					</div>

					<div class="wtai-tooltip wtai-two-cols"><span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
						<div class="wtai-tooltiptext">
							<div class="wtai-tooltip-arrow wtai-noshadow"></div>
							<div class="wtai-col wtai-col-1">
							<?php
							echo '<p class="wtai-heading">' . wp_kses_post( __( 'Not generated', 'writetext-ai' ) ) . '</p>
                            <p>' . wp_kses_post( __( 'You have not yet generated any text for these categories. Basically, these are the categories that are "untouched" by WriteText.ai.', 'writetext-ai' ) ) . '</p>

                            <p class="wtai-heading">' . wp_kses_post( __( 'Generated', 'writetext-ai' ) ) . '</p>
                            <p>' . wp_kses_post( __( 'You have generated at least one text type (meta title, meta description, category description, or Open Graph text) for these categories, but you have not yet done any editing to the text.', 'writetext-ai' ) ) . '</p>

                            <p class="wtai-heading">' . wp_kses_post( __( 'Edited', 'writetext-ai' ) ) . '</p>
                            <p>' . wp_kses_post( __( 'You have generated and edited at least one text type for these categories, but you have not yet marked any of them as "Reviewed".', 'writetext-ai' ) ) . '</p>
                            <p class="wtai-heading">' . wp_kses_post( __( 'Reviewed', 'writetext-ai' ) ) . '</p>
                            <p>' . wp_kses_post( __( 'You have marked these categories as "Reviewed" using the checkbox in the individual category editing page.', 'writetext-ai' ) ) . '</p>';
							?>
							</div>
							<div class="wtai-col wtai-col-2">
							<?php
							echo '<p class="wtai-heading">' . wp_kses_post( __( 'Transferred', 'writetext-ai' ) ) . '</p>
                            <p>' . wp_kses_post( __( 'You have transferred the text from WriteText.ai (whether edited by you or not) to WordPress.', 'writetext-ai' ) ) . '</p>
                            <p class="wtai-heading">' . wp_kses_post( __( 'Show categories without activity', 'writetext-ai' ) ) . '</p>
                            <p>' . wp_kses_post( __( 'Select this box to see categories without any activity (i.e., generating text, editing, reviewing, and transferring to WordPress) for the past number of days as defined in the field.', 'writetext-ai' ) ) . '</p>
                            <p><br><i>' . wp_kses_post( __( 'Note that WriteText.ai status is based on the last action done in the category.', 'writetext-ai' ) ) . '</i></p>';
							?>
							</div>
						</div>
					</div>
					
					<?php
					$paged = isset( $_REQUEST['paged'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
					?>
					<input type="hidden" id="wtai-filter-paged" name="paged" value="<?php echo esc_attr( $paged ); ?>" />
					<input type="hidden" id="wtai-filter-search" name="s" value="<?php echo esc_attr( wp_unslash( _admin_search_query() ) ); ?>" />
				</form>
			   
				<?php
		}
	}

	/**
	 * Displays the table.
	 *
	 * @since 3.1.0
	 */
	public function display() {
		global $category_ids;

		$singular    = $this->_args['singular'];
		$tbody_class = array();
		if ( ! wtai_current_user_can( 'writeai_transfer_generated_text' ) ) {
			$tbody_class[] = 'no_transfer';
		}
		$this->display_tablenav( 'top' );

		$this->screen->render_screen_reader_content( 'heading_list' );

		$product_edit_nonce = wp_create_nonce( 'wtai-product-nonce' );

		ob_start();
		$this->display_rows_or_placeholder();
		$data_rows_html = ob_get_clean();
		?>
		<div class="outer" id="wtai-start-sticky">
			<table class="wtai-list-table wp-list-table <?php echo esc_attr( implode( ' ', $this->get_table_classes() ) ); ?>" 
				data-product-nonce="<?php echo esc_attr( $product_edit_nonce ); ?>" 
				data-ids="<?php echo is_array( $category_ids ) ? implode( ',', map_deep( wp_unslash( $category_ids ), 'wp_kses_post' ) ) : ''; ?>">
				<thead>
					<tr>
						<?php $this->print_column_headers(); ?>
					</tr>
				</thead>
				<tbody id="the-list"
					<?php echo ( ! empty( $tbody_class ) && is_array( $tbody_class ) ) ? 'class="' . esc_attr( implode( ' ', $tbody_class ) ) . '"' : ''; ?>
					<?php
					if ( $singular ) {
						echo esc_attr( " data-wp-lists='list:$singular'" );
					}
					?>
					>
					<?php
					echo wp_kses( $data_rows_html, wtai_kses_allowed_html() );
					?>
				</tbody>
			</table>
		</div>
		<?php
		$this->display_tablenav( 'bottom wtai-tablenav-bottom' );
	}

	/**
	 * Generates the table navigation above or below the table
	 *
	 * @param string $which Position of the tablenav: 'top' or 'bottom'.
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}

		if ( $this->items ) {
			$total_items = count( $this->items );
		}

		?>
	<div class="tablenav <?php echo esc_attr( $which ); ?> <?php echo ( 'bottom' === $which && 0 === $total_items ) ? 'tablenavhidden' : ''; ?>">

		<?php
		$this->extra_tablenav( $which );
		?>
		<?php
		if ( 'top' === $which ) {
			echo '<div class="wtai-comparison-pager">';
				$this->pagination( $which );
			echo '</div>';
		}

		$taxonomy = $this->taxonomy;

		$tags_per_page = $this->get_items_per_page( "edit_{$taxonomy}_per_page" );
		?>

		<input type="hidden" id="wtai-prev-current-page-number" value="<?php echo esc_attr( $this->get_pagenum() ); ?>" />
		<input type="hidden" id="wtai-next-page-number" value="<?php echo esc_attr( $this->get_pagenum() ); ?>" />
		<input type="hidden" id="wtaItemsPerPage" value="<?php echo esc_attr( $tags_per_page ); ?>" />
		<input type="hidden" id="wtaCurrentPageNumber" value="<?php echo esc_attr( $this->get_pagenum() ); ?>" />
		<input type="hidden" id="wtaActualCurrentPageNumber" value="<?php echo esc_attr( $this->get_pagenum() ); ?>" />
		<input type="hidden" id="wtai-next-prev-max-page-number" value="<?php echo esc_attr( $this->wtai_max_num_pages ); ?>" />
		<input type="hidden" id="wtaNextCategoryId" value="0" />
		<input type="hidden" id="wtaPrevCategoryId" value="0" />
		<br class="clear" />
	</div>
		<?php
	}


	/**
	 * Generates the columns for a single row of the table.
	 *
	 * @since 3.1.0
	 *
	 * @param object|array $item The current item.
	 */
	protected function single_row_columns( $item ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		$category_id = $item['wtai_id'];

		$primary = 'wtai_title';
		foreach ( $columns as $column_name => $column_display_name ) {
			$classes = "$column_name column-$column_name";
			if ( $primary === $column_name ) {
				$classes .= ' has-row-actions column-primary';
			}

			if ( in_array( $column_name, $hidden, true ) ) {
				$classes .= ' hidden';
			}

			// Comments column uses HTML in the display name with screen reader text.
			// Strip tags to get closer to a user-friendly string.

			$add_tooltip_hover_class = false;

			$data = 'data-colname="' . esc_attr( wp_strip_all_tags( $column_display_name ) ) . '"';
			if ( strpos( $column_name, 'wtai_' ) !== false ) {
				$wtai_value_full = $item[ $column_name . '_full' ];
				$wtai_value      = $item[ $column_name ];

				$data .= ' data-text="' . htmlentities( $wtai_value_full ) . '"';

				if ( $this->ends_with_dots( $wtai_value ) ) {
					$add_tooltip_hover_class = true;
				}
			}

			$column_class = str_replace( array( 'wtai_' ), '', $column_name );

			if ( 'page_title' === $column_class || 'page_description' === $column_class || 'category_description' === $column_class || 'open_graph' === $column_class ) {
				$wp_value_full = $item[ $column_name . '_full' ];
				$wp_value      = $item[ $column_name ];

				$data .= ' data-text="' . htmlentities( $wp_value_full ) . '"';

				if ( $this->ends_with_dots( $wp_value ) ) {
					$add_tooltip_hover_class = true;
				}
			}

			if ( $add_tooltip_hover_class ) {
				$classes .= ' tooltip_hover';
			}

			$data .= ' data-colgrp="' . $column_class . '" ';

			$attributes = "class='$classes' $data";

			if ( method_exists( $this, '_column_' . $column_name ) ) {
				echo wp_kses_post( call_user_func( array( $this, '_column_' . $column_name ), $item, $classes, $data, $primary ) );
			} elseif ( method_exists( $this, 'column_' . $column_name ) ) {
				echo '<td ' . wp_kses_post( $attributes ) . '>';
				echo wp_kses_post( call_user_func( array( $this, 'column_' . $column_name ), $item ) );
				echo wp_kses_post( $this->handle_row_actions( $item, $column_name, $primary ) );
				echo '</td>';
			} else {
				echo '<td ' . wp_kses_post( $attributes ) . '>';
				echo wp_kses_post( $this->column_default( $item, $column_name ) );
				echo wp_kses_post( $this->handle_row_actions( $item, $column_name, $primary ) );
				echo '</td>';
			}
		}
	}

	/**
	 * Generates the columns for a single row of the table.
	 *
	 * @param array $item Item object.
	 */
	public function column_wtai_title( $item ) {
		$taxonomy = $this->taxonomy;

		$category_link = $item['wtai_category_link'];

		$pad = str_repeat( '&#8212; ', max( 0, $this->level ) );

		$name = apply_filters( 'term_name', $pad . ' ' . $item['term']->name, $item['term'] );

		$title_text = '<a href="' . $category_link . '" target="_blank" class="wtai-cwe-action-title" data-category-name="' . $item['term']->name . '" >' . $name . '</a>';

		return $title_text;
	}

	/**
	 * Generates content for a single row of the table.
	 *
	 * @param object|array $item The current item.
	 * @param int          $level The current level.
	 */
	public function single_row( $item, $level = 0 ) {
		$taxonomy = $this->taxonomy;

		$tag = $item['term'];

		$this->level = $level;

		if ( $tag->parent ) {
			$count = count( get_ancestors( $tag->term_id, $taxonomy, 'taxonomy' ) );
			$level = 'level-' . $count;
		} else {
			$level = 'level-0';
		}

		$class = $level;

		global $category_ids;

		if ( ! $category_ids ) {
			$category_ids = array();
		}

		$category_ids[] = $item['wtai_id'];

		echo '<tr id="wtai-table-list-' . esc_attr( $item['wtai_id'] ) . '" data-id="' . esc_attr( $item['wtai_id'] ) . '" 
				data-values=\'' . esc_attr( $item['wtai_data'] ) . '\' class="' . esc_attr( $class ) . '">';
				$this->single_row_columns( $item );
		echo '</tr>';
	}

	/** Display sub rows.
	 *
	 * @param string $taxonomy The taxonomy slug.
	 * @param array  $terms The terms to display.
	 * @param array  $children The children of the current term.
	 * @param int    $start The starting index of the terms to display.
	 * @param int    $per_page The number of terms to display per page.
	 * @param int    $count The current count of terms.
	 * @param int    $parent_term The parent term ID.
	 * @param int    $level The level of the current term.
	 */
	private function display_sub_rows( $taxonomy, $terms, &$children, $start, $per_page, &$count, $parent_term = 0, $level = 0 ) {
		$search_query = ( ! empty( $_REQUEST['s'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		$end = $start + $per_page;

		foreach ( $terms as $key => $term_data ) {
			if ( $count >= $end ) {
				break;
			}

			$term = $term_data['term'];

			if ( $term->parent !== $parent_term && empty( $search_query ) ) {
				continue;
			}

			// If the page starts in a subtree, print the parents.
			if ( $count === $start && $term->parent > 0 && empty( $search_query ) ) {
				$my_parents = array();
				$parent_ids = array();
				$p          = $term->parent;

				while ( $p ) {
					$my_parent    = get_term( $p, $taxonomy );
					$my_parents[] = $my_parent;
					$p            = $my_parent->parent;

					if ( in_array( $p, $parent_ids, true ) ) { // Prevent parent loops.
						break;
					}

					$parent_ids[] = $p;
				}

				unset( $parent_ids );

				$num_parents = count( $my_parents );

				foreach ( array_reverse( $my_parents ) as $my_parent ) {
					echo "\t";

					$parsed_term_data = $this->parse_wtai_term_data( $my_parent );
					$this->single_row( $parsed_term_data, $level - $num_parents );
					--$num_parents;
				}
			}

			if ( $count >= $start ) {
				echo "\t";

				$parsed_term_data = $this->parse_wtai_term_data( $term );
				$this->single_row( $parsed_term_data, $level );
			}

			++$count;

			unset( $terms[ $key ] );

			if ( isset( $children[ $term->term_id ] ) && empty( $search_query ) ) {
				$this->display_sub_rows( $taxonomy, $terms, $children, $start, $per_page, $count, $term->term_id, $level + 1 );
			}
		}
	}

	/**
	 * Check if last characters of a string is '...'.
	 *
	 * @param string $text Text.
	 */
	private function ends_with_dots( $text ) {
		// Check if the last three characters are '...'.
		return substr( $text, -3 ) === '...';
	}

	/**
	 * Get term ids for the filter.
	 */
	public function get_filtered_category_ids() {
		$wtai_writetext_status = '';
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_writetext_status'] ) ) {
			$wtai_writetext_status = sanitize_text_field( wp_unslash( $_GET['wtai_writetext_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		$wtai_writetext_custom_status = array();
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_writetext_custom_status'] ) ) {
			$wtai_writetext_custom_status = isset( $_GET['wtai_writetext_custom_status'] ) ? map_deep( wp_unslash( $_GET['wtai_writetext_custom_status'] ), 'wp_kses_post' ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
		}

		$wtai_writetext_fields = array();
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_writetext_fields'] ) && is_array( $_GET['wtai_writetext_fields'] ) ) {
			$wtai_writetext_fields = isset( $_GET['wtai_writetext_fields'] ) ? map_deep( wp_unslash( $_GET['wtai_writetext_fields'] ), 'wp_kses_post' ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
		}

		$category_ids = array();
		switch ( $wtai_writetext_status ) {
			case 'not_generated':
				$wtai_fields = isset( $wtai_writetext_fields ) && is_array( $wtai_writetext_fields ) ? $wtai_writetext_fields : array();

				if ( count( $wtai_fields ) > 0 ) {

					foreach ( $wtai_fields as $key => $field ) {
						$wtai_field = apply_filters( 'wtai_field_conversion', $field, 'category' );

						$api_params = array(
							'fields' => array( $wtai_field ),
						);

						$category_api_ids = apply_filters( 'wtai_generate_category_status', array(), $api_params );

						$category_ids = array_merge( $category_ids, $category_api_ids );
					}

					$category_ids = array_unique( $category_ids );
				}
				break;
			case 'wtai_custom_status':
				$wtai_fields = isset( $wtai_writetext_fields ) && is_array( $wtai_writetext_fields ) ? $wtai_writetext_fields : array();

				$wtai_fields_converted = array();
				foreach ( $wtai_fields as $key => $field ) {
					$wtai_fields_converted[] = apply_filters( 'wtai_field_conversion', $field, 'category' );
				}

				$has_reviewed_not_transferred = false;
				if ( in_array( 'reviewed_not_transferred', $wtai_writetext_custom_status, true ) ) {
					$wtai_writetext_custom_status_updated = array();
					foreach ( $wtai_writetext_custom_status as $wtai_custom_status ) {
						if ( 'reviewed_not_transferred' !== $wtai_custom_status ) {
							$wtai_writetext_custom_status_updated[] = $wtai_custom_status;
						}
					}

					$wtai_writetext_custom_status = $wtai_writetext_custom_status_updated;
					$has_reviewed_not_transferred = true;
				}

				$category_api_ids = array();
				if ( $wtai_writetext_custom_status ) {
					foreach ( $wtai_writetext_custom_status as $wtai_status ) {
						foreach ( $wtai_fields_converted as $field ) {
							$api_params = array(
								'status' => $wtai_status,
								'fields' => array( $field ),
							);

							$category_api_ids_init = apply_filters( 'wtai_generate_category_status', array(), $api_params );
							$category_api_ids      = array_merge( $category_api_ids, $category_api_ids_init );
						}
					}
				}

				// Lets get all reviewed category but is not transferred.
				if ( $has_reviewed_not_transferred ) {
					sleep( 1 );

					$transferred_category_ids = array();

					$api_params = array(
						'status' => 'transfered',
						'fields' => $wtai_fields_converted,
					);

					if ( $filter_date_from ) {
						$api_params['startDate'] = $filter_date_from;
					}

					if ( isset( $filter_date_to ) ) {
						$api_params['endDate'] = $filter_date_to;
					}

					$transferred_category_ids = apply_filters( 'wtai_generate_category_status', array(), $api_params );

					// Get reviewed category.
					sleep( 1 );

					$reviewed_category_ids = array();

					$api_params = array(
						'status' => 'reviewed',
						'fields' => $wtai_fields_converted,
					);

					if ( $filter_date_from ) {
						$api_params['startDate'] = $filter_date_from;
					}

					if ( isset( $filter_date_to ) ) {
						$api_params['endDate'] = $filter_date_to;
					}

					$reviewed_category_ids = apply_filters( 'wtai_generate_category_status', array(), $api_params );

					if ( $transferred_category_ids ) {
						$final_reviewed_category_ids = array();
						foreach ( $reviewed_category_ids as $reviewed_category_id ) {
							if ( ! in_array( $reviewed_category_id, $transferred_category_ids, true ) ) {
								$final_reviewed_category_ids[] = $reviewed_category_id;
							}
						}

						$category_api_ids = array_merge( $category_api_ids, $final_reviewed_category_ids );
					} else {
						$category_api_ids = array_merge( $category_api_ids, $reviewed_category_ids );
					}
				}

				if ( $category_api_ids ) {
					$category_ids = array_unique( $category_api_ids );
				} else {
					$category_ids = array( -1 );
				}

				break;
			case 'no_activity':
				$last_no_activity = isset( $_GET['no_activity_days'] ) ? intval( $_GET['no_activity_days'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $last_no_activity ) ) {
					$filter_date_from = gmdate( 'Y-m-d', strtotime( "-{$last_no_activity} days" ) ) . ' 00:00:00';
					$current_date     = gmdate( 'Y-m-d' );
					$filter_date_to   = $current_date . ' 23:59:59';
				}

				if ( $filter_date_from || $filter_date_to ) {
					$no_activity_query_args = array(
						'taxonomy'   => $this->taxonomy, // Replace 'your_taxonomy' with your actual taxonomy.
						'hide_empty' => false, // Include terms even if they do not have any posts.
						'number'     => 0, // Fetch all terms.
						'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
							'relation' => 'AND',
							array(
								'key'     => 'wtai_last_activity_date',
								'value'   => $filter_date_from,
								'compare' => '>=',
								'type'    => 'DATE',
							),
							array(
								'key'     => 'wtai_last_activity_date',
								'value'   => $filter_date_to,
								'compare' => '<=',
								'type'    => 'DATE',
							),
						),
						'orderby'    => 'meta_value',            // Order by meta value.
						'meta_key'   => 'wtai_last_activity_date', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'order'      => 'ASC',
					);

					$no_activity_result = new WP_Term_Query( $no_activity_query_args );

					$no_activity_term_ids = array();
					if ( ! empty( $no_activity_result->terms ) ) {
						$no_activity_term_ids = wp_list_pluck( $no_activity_result->terms, 'term_id' );
					}

					$no_activity_missing_query_args = array(
						'taxonomy'         => $this->taxonomy, // Replace with your actual taxonomy.
						'hide_empty'       => false, // Include terms even if they do not have any posts.
						'number'           => 0, // Fetch all terms.
						'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
							'relation' => 'OR', // Use 'OR' to match either condition.
							array(
								'key'     => 'wtai_last_activity_date',
								'compare' => 'NOT EXISTS', // Condition for meta key not existing.
							),
							array(
								'key'     => 'wtai_last_activity_date',
								'value'   => '', // Condition for meta key existing but being blank.
								'compare' => '=', // '=' to match exactly blank values.
							),
						),
						'orderby'          => 'term_id', // Order by term_id.
						'order'            => 'ASC', // Ascending order.
						'suppress_filters' => true,
					);

					$no_activity_missing_result = new WP_Term_Query( $no_activity_missing_query_args );
					if ( ! empty( $no_activity_missing_result->terms ) ) {
						$no_activity_term_ids = array_merge( $no_activity_term_ids, wp_list_pluck( $no_activity_missing_result->terms, 'term_id' ) );
					}

					if ( $no_activity_term_ids ) {
						$category_ids = $no_activity_term_ids;
					} else {
						$category_ids = array( -1 );
					}
				}

				break;
		} // End of switch.

		$filter_results = array(
			'category_ids'                 => $category_ids,
			'wtai_writetext_status'        => $wtai_writetext_status,
			'wtai_writetext_custom_status' => $wtai_writetext_custom_status,
			'wtai_writetext_fields'        => $wtai_writetext_fields,
		);

		return $filter_results;
	}

	/**
	 * Get sorted term IDs.
	 *
	 * @param string $orderby Order by.
	 * @param string $order   Order.
	 *
	 * @return array
	 */
	public function get_sorted_term_ids( $orderby = 'name', $order = 'asc' ) {
		$orderby_args = array(
			'taxonomy'   => $this->taxonomy, // Replace with your actual taxonomy.
			'hide_empty' => false, // Include terms even if they do not have any posts.
			'number'     => 0, // Fetch all terms.
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation' => 'OR',
				array(
					'key'     => trim( wp_unslash( $orderby ) ),
					'value'   => '',
					'compare' => '!=',
				),
				array(
					'key'     => trim( wp_unslash( $orderby ) ),
					'compare' => 'NOT EXISTS',
				),
			),
			'orderby'    => 'name', // Order by name to fetch all terms, we'll sort manually.
			'order'      => 'asc',
		);

		$orderby_args_result = new WP_Term_Query( $orderby_args );

		$term_ids = array();
		if ( ! empty( $orderby_args_result->terms ) ) {
			$terms = $orderby_args_result->terms;

			// Get meta values for sorting.
			$terms_array = array();
			foreach ( $terms as $term ) {
				$custom_meta_value = get_term_meta( $term->term_id, trim( wp_unslash( $orderby ) ), true );
				$custom_meta_value = $custom_meta_value ? $custom_meta_value : 0;

				$terms_array[] = array(
					'term_id' => $term->term_id,
					'value'   => $custom_meta_value,
				);
			}

			// Custom sorting function.
			if ( 'desc' === $order ) {
				usort(
					$terms_array,
					function ( $a, $b ) {
						return $b['value'] <=> $a['value']; // Descending order.
					}
				);
			} else {
				usort(
					$terms_array,
					function ( $a, $b ) {
						return $a['value'] <=> $b['value']; // Ascending order.
					}
				);
			}

			$term_ids = wp_list_pluck( $terms_array, 'term_id' );
		}

		return $term_ids;
	}

	/**
	 * Add custom sorting for WooCommerce product categories
	 *
	 * @param array $clauses The query clauses.
	 * @param array $taxonomy The taxonomy.
	 * @param array $args The query arguments.
	 *
	 * @return array
	 */
	public function add_custom_wtai_sorting( $clauses, $taxonomy, $args ) {
		if ( in_array( 'product_cat', $taxonomy, true ) && isset( $args['wtai_custom_term_sort'] ) && is_array( $args['wtai_custom_term_sort'] ) ) {
			$clauses['orderby'] = 'ORDER BY FIELD( t.term_id, ' . implode( ',', $args['wtai_custom_term_sort'] ) . ')';
			$clauses['order']   = '';
		}

		return $clauses;
	}
}