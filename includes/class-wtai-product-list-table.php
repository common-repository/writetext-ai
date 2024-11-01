<?php
/**
 * Custom WP List class for product lists
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
class WTAI_Product_List_Table extends WP_List_Table {
	/**
	 * Pending bulk ids.
	 *
	 * @var array
	 */
	public $wtai_pending_ids = array();

	/**
	 * WTA is fetched flag.
	 *
	 * @var bool
	 */
	public $wtai_fetched = false;

	/**
	 * WTA total items.
	 *
	 * @var int
	 */
	public $wtai_total_items = 0;

	/**
	 * WTA max number of pages.
	 *
	 * @var int
	 */
	public $wtai_max_num_pages = 0;

	/**
	 * Get pending bulk ids.
	 */
	public function get_wtai_pending_ids() {
		if ( false === $this->wtai_fetched ) {
			$this->wtai_pending_ids = wtai_get_all_pending_bulk_ids( array(), true );
			$this->wtai_fetched     = true;
		}

		return $this->wtai_pending_ids;
	}

	/**
	 * Prepare the items to display
	 */
	public function prepare_items() {
		$order_by = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$order    = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		// Passing the data to items.
		$data_search = array();

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_writetext_status'] ) ) {
			$data_search['wtai_writetext_status'] = sanitize_text_field( wp_unslash( $_GET['wtai_writetext_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_writetext_custom_status'] ) ) {
			$data_search['wtai_writetext_custom_status'] = isset( $_GET['wtai_writetext_custom_status'] ) ? map_deep( wp_unslash( $_GET['wtai_writetext_custom_status'] ), 'wp_kses_post' ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_writetext_fields'] ) && is_array( $_GET['wtai_writetext_fields'] ) ) {
			$data_search['wtai_writetext_fields'] = isset( $_GET['wtai_writetext_fields'] ) ? map_deep( wp_unslash( $_GET['wtai_writetext_fields'] ), 'wp_kses_post' ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
		}

		$doing_search = false;
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['s'] ) ) {
			$data_search['s'] = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$doing_search     = true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['post_status'] ) ) {
			$data_search['post_status'] = esc_attr( sanitize_text_field( wp_unslash( $_GET['post_status'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_product_cat'] ) ) {
			$data_search['cat'] = esc_attr( sanitize_text_field( wp_unslash( $_GET['wtai_product_cat'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_stock_status'] ) ) {
			$data_search['stock_status'] = esc_attr( sanitize_text_field( wp_unslash( $_GET['wtai_stock_status'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['filter_date_from'] ) ) {
			$filter_date_from                = gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_GET['filter_date_from'] ) ) ) ) . ' 00:00:00'; // phpcs:ignore WordPress.Security.NonceVerification
			$data_search['filter_date_from'] = $filter_date_from;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['filter_date_to'] ) ) {
			$filter_date_to                = gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_GET['filter_date_to'] ) ) ) ) . ' 23:59:59'; // phpcs:ignore WordPress.Security.NonceVerification
			$data_search['filter_date_to'] = $filter_date_to;
		}

		// Get date of last xxx days from today.
		$last_no_activity = isset( $_GET['no_activity_days'] ) ? intval( $_GET['no_activity_days'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $last_no_activity ) && isset( $_GET['wtai_writetext_status'] ) && 'no_activity' === $_GET['wtai_writetext_status'] ) {
			$filter_date_from                = gmdate( 'Y-m-d', strtotime( "-{$last_no_activity} days" ) );
			$data_search['filter_date_from'] = $filter_date_from;

			$current_date                  = gmdate( 'Y-m-d' );
			$data_search['filter_date_to'] = $current_date;
			$filter_date_to                = $current_date;
		}

		$total_items   = 0;
		$post_per_page = get_option( 'posts_per_page', 10 );
		$per_page      = $this->get_items_per_page( 'edit_product_per_page', $post_per_page );
		$current_page  = $this->get_pagenum();

		$this->items = $this->wtai_list_table_data( $order_by, $order, $data_search, $per_page );

		if ( ! $this->items && $doing_search ) {
			$this->items = $this->wtai_list_table_data( $order_by, $order, $data_search, $per_page, 1 );
		}

		// Get all defined columns.
		$wtai_column = $this->get_columns();

		// Get all defined hidden columns.
		$wtai_hd_column = array();

		// Get all defined sortable columns.
		$wtai_sortable = $this->get_sortable_columns();

		// Pass the data to headers to apply.
		$this->_column_headers = array( $wtai_column, $wtai_hd_column, $wtai_sortable );

		global $product_ids;
		$product_ids = array();

		$max_num_pages = 1;
		if ( $this->items ) {
			$total_items   = $this->wtai_total_items;
			$max_num_pages = $this->wtai_max_num_pages;

			// Current items.
			foreach ( $this->items as $item_data ) {
				$product_ids[] = $item_data['wtai_id'];
			}
		}

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);

		$this->process_bulk_action();
	}

	/**
	 * Get all the data to be displayed
	 *
	 * @param string $order_by Order by column name.
	 * @param string $order Order by order.
	 * @param array  $search_term Search term.
	 * @param int    $post_per_page Post per page.
	 * @param int    $page_number Page number.
	 */
	public function wtai_list_table_data( $order_by = '', $order = '', $search_term = array(), $post_per_page = 10, $page_number = 0 ) {
		$paged = isset( $_GET['paged'] ) ? sanitize_text_field( wp_unslash( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification

		if ( $page_number ) {
			$paged = $page_number;
		}

		$datas = array();
		global $wtai_product_dashboard;

		$column_objs = $wtai_product_dashboard->get_fields_list();
		$args        = array(
			'post_type'      => WTAI_POST_TYPE,
			'post_status'    => array( 'publish', 'draft', 'private', 'future', 'pending' ),
			'fields'         => 'ids',
			'posts_per_page' => $post_per_page,
			'paged'          => $paged,
		);

		// Sort by date.
		if ( $order_by && in_array( $order_by, array( 'date', 'post_title' ), true ) ) {
			$args['orderby'] = esc_attr( $order_by );
			$args['order']   = esc_attr( $order );
		}

		$meta_queries = array();
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['post_status'] ) ) {
			$args['post_status'] = sanitize_text_field( wp_unslash( $_GET['post_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		// By category.
		if ( isset( $search_term['cat'] ) && 0 !== intval( $search_term['cat'] ) ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => array( $search_term['cat'] ),
					'operator' => 'IN',
				),
			);
		}

		if ( isset( $search_term['stock_status'] ) && '' !== $search_term['stock_status'] ) {
			$meta_queries[] = array(
				'key'     => '_stock_status',
				'value'   => $search_term['stock_status'],
				'compare' => '=',
			);
		}

		if ( isset( $search_term['wtai_writetext_status'] ) ) {
			$filter_date_from = $search_term['filter_date_from'];
			if ( isset( $search_term['filter_date_to'] ) && $search_term['filter_date_to'] ) {
				$filter_date_to = $search_term['filter_date_to'];
			}

			$language_code   = '';
			$my_current_lang = apply_filters( 'wpml_current_language', null );
			if ( $my_current_lang ) {
				$wpml_active_languages = apply_filters( 'wpml_active_languages', null );
				if ( isset( $wpml_active_languages[ $my_current_lang ]['default_locale'] ) ) {
					$language_code = trim( $wpml_active_languages[ $my_current_lang ]['default_locale'] );
				}
			}

			switch ( $search_term['wtai_writetext_status'] ) {
				case 'not_generated':
					$wtai_fields = isset( $search_term['wtai_writetext_fields'] ) && is_array( $search_term['wtai_writetext_fields'] ) ? $search_term['wtai_writetext_fields'] : array();

					if ( count( $wtai_fields ) > 0 ) {
						$product_ids = array();
						foreach ( $wtai_fields as $key => $field ) {
							$wtai_field = apply_filters( 'wtai_field_conversion', $field, 'product' );

							$api_params = array(
								'fields' => array( $wtai_field ),
							);

							if ( $language_code ) {
								$api_params['language'] = $language_code;
							}

							if ( $filter_date_from ) {
								$api_params['startDate'] = $filter_date_from;
							}

							if ( $filter_date_to ) {
								$api_params['endDate'] = $filter_date_to;
							}

							$product_api_ids = apply_filters( 'wtai_generate_product_status', array(), $api_params );

							$product_ids = array_merge( $product_ids, $product_api_ids );
						}

						$product_ids = array_unique( $product_ids );
					}

					if ( $product_ids ) {
						$args['post__not_in'] = $product_ids;
					} else {
						$args['post__in'] = 0;
					}
					break;
				case 'wtai_custom_status':
					$wtai_fields = isset( $search_term['wtai_writetext_fields'] ) && is_array( $search_term['wtai_writetext_fields'] ) ? $search_term['wtai_writetext_fields'] : array();

					$wtai_fields_converted = array();
					foreach ( $wtai_fields as $key => $field ) {
						$wtai_fields_converted[] = apply_filters( 'wtai_field_conversion', $field, 'product' );
					}

					$wtai_writetext_custom_status = $search_term['wtai_writetext_custom_status'];
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

					$product_api_ids = array();
					if ( $wtai_writetext_custom_status ) {
						foreach ( $wtai_writetext_custom_status as $wtai_status ) {
							foreach ( $wtai_fields_converted as $field ) {
								$api_params = array(
									'status' => $wtai_status,
									'fields' => array( $field ),
								);

								if ( $filter_date_from ) {
									$api_params['startDate'] = $filter_date_from;
								}

								if ( isset( $filter_date_to ) ) {
									$api_params['endDate'] = $filter_date_to;
								}

								if ( $language_code ) {
									$api_params['language'] = $language_code;
								}

								$product_api_ids_init = apply_filters( 'wtai_generate_product_status', array(), $api_params );
								$product_api_ids      = array_merge( $product_api_ids, $product_api_ids_init );
							}
						}
					}

					// Lets get all reviewed products but is not transferred.
					if ( $has_reviewed_not_transferred ) {
						sleep( 1 );

						$transferred_product_ids = array();

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

						if ( $language_code ) {
							$api_params['language'] = $language_code;
						}

						$transferred_product_ids = apply_filters( 'wtai_generate_product_status', array(), $api_params );

						// Get reviewed products.
						sleep( 1 );

						$reviewed_product_ids = array();

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

						if ( $language_code ) {
							$api_params['language'] = $language_code;
						}

						$reviewed_product_ids = apply_filters( 'wtai_generate_product_status', array(), $api_params );

						if ( $transferred_product_ids ) {
							$final_reviewed_product_ids = array();
							foreach ( $reviewed_product_ids as $reviewed_product_id ) {
								if ( ! in_array( $reviewed_product_id, $transferred_product_ids, true ) ) {
									$final_reviewed_product_ids[] = $reviewed_product_id;
								}
							}

							$product_api_ids = array_merge( $product_api_ids, $final_reviewed_product_ids );
						} else {
							$product_api_ids = array_merge( $product_api_ids, $reviewed_product_ids );
						}
					}

					if ( $product_api_ids ) {
						$product_api_ids = array_unique( $product_api_ids );

						$args['post__in'] = $product_api_ids;
					} else {
						$args['post__in'] = array( 0 );
					}

					break;
				case 'no_activity':
					$last_no_activity = isset( $_GET['no_activity_days'] ) ? intval( $_GET['no_activity_days'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
					if ( isset( $last_no_activity ) ) {
						$filter_date_from                = gmdate( 'Y-m-d', strtotime( "-{$last_no_activity} days" ) );
						$data_search['filter_date_from'] = $filter_date_from;

						$current_date                  = gmdate( 'Y-m-d' );
						$data_search['filter_date_to'] = $current_date;
						$filter_date_to                = $current_date;
					}

					if ( $filter_date_from || $filter_date_to ) {
						$args_product_with_activity = array(
							'fields'         => 'ids',
							'post_type'      => 'product',
							'post_status'    => 'any',
							'posts_per_page' => -1,
							'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
								'relation' => 'OR',
								array(
									'key'     => 'wtai_last_activity_date',
									'value'   => array( $filter_date_from, $filter_date_to ),
									'type'    => 'date',
									'compare' => 'between',
								),
								array(
									'key'     => 'wtai_last_activity_date',
									'compare' => 'NOT EXISTS',
								),
							),
							'orderby'        => 'meta_value',            // Order by meta value.
							'meta_key'       => 'wtai_last_activity_date', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
							'order'          => 'ASC',
						);
						$result_active_product      = new WP_Query( $args_product_with_activity );
						$result_active_product_ids  = $result_active_product->posts;

						if ( $result_active_product_ids ) {
							$args['post__not_in'] = $result_active_product_ids;
						}
					}

					break;
				case 'wtai_review_status':
					$writetext_custom_review_status = isset( $_GET['writetext_custom_review_status'] ) ? map_deep( wp_unslash( $_GET['writetext_custom_review_status'] ), 'wp_kses_post' ) : array(); // phpcs:ignore WordPress.Security.NonceVerification

					$wtai_fields = isset( $search_term['wtai_writetext_fields'] ) && is_array( $search_term['wtai_writetext_fields'] ) ? $search_term['wtai_writetext_fields'] : array();

					$wtai_fields_converted = array();
					foreach ( $wtai_fields as $key => $field ) {
						$wtai_fields_converted[] = apply_filters( 'wtai_field_conversion', $field, 'product' );
					}

					$extension_review_status = array();
					if ( is_array( $writetext_custom_review_status ) ) {
						if ( in_array( 'for_rewrite', $writetext_custom_review_status, true ) && in_array( 'for_fact_checking', $writetext_custom_review_status, true ) ) {
							$extension_review_status[] = 'EditForRewriteAndCorrection';
							$extension_review_status[] = 'EditForRewrite';
							$extension_review_status[] = 'EditForCorrection';
						} elseif ( in_array( 'for_rewrite', $writetext_custom_review_status, true ) ) {
							$extension_review_status[] = 'EditForRewrite';
							$extension_review_status[] = 'EditForRewriteAndCorrection';
						} elseif ( in_array( 'for_fact_checking', $writetext_custom_review_status, true ) ) {
							$extension_review_status[] = 'EditForCorrection';
							$extension_review_status[] = 'EditForRewriteAndCorrection';
						}
					}

					$api_params = array(
						'status' => $extension_review_status,
						'fields' => $wtai_fields_converted,
					);

					$extension_reviewed_product_ids = apply_filters( 'wtai_get_review_product_extension_status', array(), $api_params );

					if ( $extension_reviewed_product_ids ) {
						$extension_reviewed_product_ids = array_unique( $extension_reviewed_product_ids );

						$args['post__in'] = $extension_reviewed_product_ids;
					} else {
						$args['post__in'] = array( 0 );
					}

					break;
			}
		}

		if ( ! empty( $meta_queries ) ) {
			$args['meta_query'] = count( $meta_queries ) > 1 ? array_merge( array( 'relation' => 'AND' ), $meta_queries ) : $meta_queries; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		if ( isset( $search_term['s'] ) && $search_term['s'] ) {
			$excluded_in_search = array();
			if ( isset( $args['post__not_in'] ) ) {
				$excluded_in_search = $args['post__not_in'];
			}

			$included_in_search = array();
			if ( isset( $args['post__in'] ) ) {
				$included_in_search = $args['post__in'];
			} elseif ( isset( $search_term['wtai_writetext_status'] ) ) {
				$custom_search_args                   = $args;
				$custom_search_args['posts_per_page'] = -1;
				unset( $custom_search_args['paged'] );

				$products_temp      = new WP_Query( $custom_search_args );
				$included_in_search = $products_temp->posts;
			}

			$data_store = WC_Data_Store::load( 'product' );
			$ids        = $data_store->search_products( wc_clean( wp_unslash( $search_term['s'] ) ), '', true, true, null, null, $excluded_in_search );

			$final_search_ids = array();
			if ( $included_in_search ) {
				foreach ( $ids as $search_id ) {
					// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
					if ( in_array( $search_id, $included_in_search ) ) {
						$final_search_ids[] = $search_id;
					}
				}
			} else {
				$final_search_ids = $ids;
			}

			if ( $final_search_ids ) {
				$args['post__in'] = $final_search_ids;
			} else {
				$args['post__in'] = array( 0 );// Force to no result.
			}
		}

		// Sort by last generated and last transferred date.
		$has_date_sorting = false;
		if ( $order_by && in_array( $order_by, array( 'wtai_generate_date', 'wtai_transfer_date' ), true ) ) {
			$current_meta_query = $args['meta_query'];

			if ( ! $current_meta_query ) {
				$current_meta_query = array();
			}

			$order_by_key = str_replace( 'wtai_', 'wtai_', $order_by );

			$current_meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => $order_by_key,
					'compare' => 'EXISTS',
				),
				array(
					'key'     => $order_by_key,
					'compare' => 'NOT EXISTS',
					'value'   => '', // Set a dummy value.
				),
			);

			$args['meta_query'] = $current_meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$args['orderby']    = 'meta_value_num';
			$args['order']      = $order;

			$has_date_sorting = true;
		}

		$products = new WP_Query( $args );

		$this->wtai_total_items   = $products->found_posts;
		$this->wtai_max_num_pages = $products->max_num_pages;

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_grid_filter_debug'] ) ) {
			echo wp_kses_post( $products->request ) . '<br>';
		}

		if ( $products->have_posts() ) {
			$product_ids = array();
			$api_results = array();
			while ( $products->have_posts() ) {
				$products->the_post();
				$product_ids[] = get_the_ID();
			}

			if ( ! empty( $product_ids ) ) {
				$meta_keys = apply_filters( 'wtai_fields', array() );
				$meta_keys = array_keys( $meta_keys );
				$fields    = array(
					'fields'       => $meta_keys,
					'single_value' => 1,
				);

				$api_record_per_page = 5;
				$api_total_records   = count( $product_ids );
				$api_max_page        = ceil( $api_total_records / $api_record_per_page );

				$api_results = array();
				for ( $api_page_no = 1; $api_page_no <= $api_max_page; $api_page_no++ ) {
					$batch_product_ids = array_slice( $product_ids, ( ( $api_page_no - 1 ) * $api_record_per_page ), $api_record_per_page );

					$batch_api_results = apply_filters( 'wtai_generate_product_text', array(), implode( ',', $batch_product_ids ), $fields );

					if ( $batch_api_results ) {
						foreach ( $batch_api_results as $api_record_id => $api_record ) {
							$api_results[ $api_record_id ] = $api_record;
						}
					}
				}

				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_GET['wtai_grid_filter_debug'] ) ) {
					print_r( $api_results ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
					print '</pre>';
				}
			}

			while ( $products->have_posts() ) {
				$products->the_post();
				$post_id    = get_the_ID();
				$post_title = get_the_title( $post_id );
				$post       = get_post( $post_id );

				$post_return                        = array();
				$post_return['post_status_ucfirst'] = ucfirst( get_post_status( $post_id ) );

				switch ( $post->post_status ) {
					case 'private':
						$post_return['status'] = __( 'Privately Published' );
						break;
					case 'publish':
						$post_return['status'] = __( 'Published' );
						break;
					case 'future':
						$post_return['status'] = __( 'Scheduled' );
						break;
					case 'pending':
						$post_return['status'] = __( 'Pending' );
						break;
					case 'pending':
						$post_return['status'] = __( 'Pending Review' );
						break;
					case 'draft':
					case 'auto-draft':
						$post_return['status'] = __( 'Draft' );
						break;
				}

				if ( 'private' === $post->post_status ) {
					$post->post_password = '';
					$visibility          = 'private';
					$visibility_trans    = __( 'Private' );
				} elseif ( ! empty( $post->post_password ) ) {
					$visibility       = 'password';
					$visibility_trans = __( 'Password protected' );
				} elseif ( 'post' === $post->post_type && is_sticky( $post_id ) ) {
					$visibility       = 'public';
					$visibility_trans = __( 'Public, Sticky' );
				} else {
					$visibility       = 'public';
					$visibility_trans = __( 'Public' );
				}

				$post_return['post_visibility'] = esc_html( $visibility_trans );

				/* translators: %1$s: date  %2$s time */
				$date_string = __( '%1$s at %2$s' );
				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );
				if ( 'future' === $post->post_status ) { // Scheduled for publishing at a future date.
					/* translators: %s: datetime */
					$stamp = __( 'Scheduled for: %s' );
				} elseif ( 'publish' === $post->post_status || 'private' === $post->post_status ) { // Already published.
					/* translators: %s: datetime */
					$stamp = __( 'Published on: %s' );
				} elseif ( '0000-00-00 00:00:00' === $post->post_date_gmt ) { // Draft, 1 or more saves, no date specified.
					$stamp = __( 'Publish <b>immediately</b>' );
				} elseif ( time() < strtotime( $post->post_date_gmt . ' +0000' ) ) { // Draft, 1 or more saves, future date specified.
					/* translators: %s: datetime */
					$stamp = __( 'Schedule for: %s' );
				} else { // Draft, 1 or more saves, date specified.
					/* translators: %s: datetime */
					$stamp = __( 'Publish on: %s' );
				}

				$date = sprintf(
					$date_string,
					date_i18n( $date_format, strtotime( $post->post_date ) ),
					date_i18n( $time_format, strtotime( $post->post_date ) )
				);

				$post_return['post_timedate'] = sprintf( $stamp, '<b>' . $date . '</b>' );

				$product_sku = get_post_meta( $post_id, '_sku', true );

				$row = array(
					'wtai_id'            => $post_id,
					'wtai_thumb'         => wtai_get_product_thumbnail( $post_id ),
					'wtai_title'         => '<a href="' . get_permalink( $post_id ) . '" target="_blank" class="wtai-cwe-action-title" data-sku="' . esc_attr( $product_sku ) . '">' . $post_title . '</a>',
					'wtai_sku'           => $product_sku,
					'wtai_language'      => apply_filters( 'wtai_column_language', get_locale(), $post ),
					'wtai_generate_date' => get_post_meta( $post_id, 'wtai_generate_date', true ),
					'wtai_transfer_date' => get_post_meta( $post_id, 'wtai_transfer_date', true ),
					'wtai_data'          => wp_json_encode( $post_return ),
				);

				ob_start();

				$this->column_date( $post );
				if ( isset( $formatted_column_date ) ) {
					echo wp_kses_post( $formatted_column_date );
				}

				// MCR modified date.
				$last_modified = get_the_modified_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

				$last_activity_date = get_post_meta( $post_id, 'wtai_last_activity_date', true );

				$row['wtai_publish_date'] = ob_get_clean();
				foreach ( $column_objs as $column_obj_key => $column_obj_value ) {
					switch ( $column_obj_key ) {
						case 'page_title':
							$column_name = wtai_get_meta_key_source( 'title' );
							break;
						case 'page_description':
							$column_name = wtai_get_meta_key_source( 'desc' );
							break;
						case 'open_graph':
							$column_name = wtai_get_meta_key_source( 'opengraph' );
							break;
						default:
							$column_name = $column_obj_key;
							break;
					}

					if ( 'page_title' === $column_obj_key ) {
						$api_content = '';
						if ( isset( $api_results[ $post_id ][ $column_obj_key ][0]['value'] ) ) {
							$api_content = str_replace( "\\'", "'", $api_results[ $post_id ][ $column_obj_key ][0]['value'] );
							$api_content = str_replace( '\\"', '"', $api_content );
						}
						$row[ 'wtai_' . $column_obj_key ]           = $api_content;
						$row[ 'wtai_' . $column_obj_key . '_full' ] = $api_content;
						if ( $row[ 'wtai_' . $column_obj_key ] ) {
							$row[ 'wtai_' . $column_obj_key ] = wp_trim_words( $row[ 'wtai_' . $column_obj_key ], 15, null );
						}
						$row[ $column_obj_key ] = wtai_yoast_seo_format_value( $post_id, $column_name );

						// added by mcr.
						$row[ $column_obj_key ] = ( $row[ $column_obj_key ] ) ? wp_trim_words( $row[ $column_obj_key ], 15, null ) : '';

					} else {
						if ( in_array( $column_obj_key, array( 'product_description', 'product_excerpt' ), true ) ) {
							// Handling product with password protect.
							$prod                   = wc_get_product( $post_id );
							$product_content        = $prod->get_description();
							$product_excerpt        = $prod->get_short_description();
							$row[ $column_obj_key ] = ( 'product_description' === $column_obj_key ) ? $product_content : $product_excerpt;
						} else {

							$row[ $column_obj_key ] = wtai_yoast_seo_format_value( $post_id, $column_name );
						}

						$api_content = '';
						if ( isset( $api_results[ $post_id ][ $column_obj_key ][0]['value'] ) ) {
							$api_content = str_replace( "\\'", "'", $api_results[ $post_id ][ $column_obj_key ][0]['value'] );
							$api_content = str_replace( '\\"', '"', $api_content );
						}

						$api_content = wpautop( nl2br( $api_content ) );

						$row[ $column_obj_key ]                     = ( $row[ $column_obj_key ] ) ? wp_trim_words( $row[ $column_obj_key ], 15, null ) : '';
						$row[ 'wtai_' . $column_obj_key ]           = $api_content;
						$row[ 'wtai_' . $column_obj_key . '_full' ] = $api_content;
						$row[ 'wtai_' . $column_obj_key ]           = ( $row[ 'wtai_' . $column_obj_key ] ) ? wp_trim_words( $row[ 'wtai_' . $column_obj_key ], 15, null ) : '';
					}
				}

				$datas[] = $row;
			}
		}

		return $datas;
	}

	/**
	 * Define displayed columns
	 */
	public function get_columns() {
		global $wtai_product_dashboard;
		$column_objs = $wtai_product_dashboard->get_fields_list();

		$columns = array(
			'cb'         => '<input type="checkbox" class="wtai-cwe-selected" />',
			'wtai_thumb' => '<img src="' . WTAI_DIR_URL . 'assets/images/ic_thumb.png" class="wtai-thumb" />',
			'wtai_title' => __( 'Name', 'writetext-ai' ),
			'wtai_sku'   => __( 'SKU', 'writetext-ai' ),

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
			} elseif ( 'product_description' === $column_obj_key ) {
				$wp_header_label   = __( 'WordPress product description', 'writetext-ai' );
				$wtai_header_label = __( 'WriteText.ai product description', 'writetext-ai' );
			} elseif ( 'product_excerpt' === $column_obj_key ) {
				$wp_header_label   = __( 'WordPress product short description', 'writetext-ai' );
				$wtai_header_label = __( 'WriteText.ai product short description', 'writetext-ai' );
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
		$columns['wtai_publish_date']  = __( 'Date', 'writetext-ai' );

		return $columns;
	}

	/**
	 * Define sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'wtai_title'         => array( 'post_title', true ),
			'wtai_generate_date' => array( 'wtai_generate_date', true ),
			'wtai_transfer_date' => array( 'wtai_transfer_date', true ),
			'wtai_publish_date'  => array( 'date', true ),
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

		$wp_pending_bulk_ids = $this->get_wtai_pending_ids();

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

				$disable_tranasfer_class = '';
				if ( in_array( $post_id, $wp_pending_bulk_ids, true ) ) {
					$disable_tranasfer_class = 'wtai-disabled-button';
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
			case 'page_description':
			case 'open_graph':
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
	 * Set the checkbox element
	 *
	 * @param array $items Items.
	 */
	public function column_cb( $items ) {
		$wp_pending_bulk_ids = $this->get_wtai_pending_ids();

		$user_bulk_product_ids     = wtai_get_current_user_bulk_generation_products();
		$user_transfer_product_ids = wtai_get_current_user_bulk_transfer_products();
		$disabled                  = '';
		$checked                   = '';

		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( is_array( $wp_pending_bulk_ids ) && in_array( $items['wtai_id'], $wp_pending_bulk_ids ) ) {
			$disabled = 'disabled';

			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( $user_bulk_product_ids && in_array( $items['wtai_id'], $user_bulk_product_ids ) ) {
				$checked = 'checked';
			}

			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( $user_transfer_product_ids && in_array( $items['wtai_id'], $user_transfer_product_ids ) ) {
				$checked = 'checked';
			}
		}

		$checkox = '<input type="checkbox" class="wtai-cwe-selected" data-post-id="' . $items['wtai_id'] . '" ' . $disabled . ' ' . $checked . ' />';
		return $checkox;
	}

	/**
	 * Add bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array();

		if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
			$actions['wtai_bulk_generate'] = __( 'Bulk generate', 'writetext-ai' );
		}

		if ( wtai_current_user_can( 'writeai_transfer_generated_text' ) ) {
			$actions['wtai_bulk_transfer'] = __( 'Bulk transfer', 'writetext-ai' );
		}

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

		$item_id = $item['wtai_id'];

		$bulk_pending_ids = $this->get_wtai_pending_ids();

		$disable_bulk_class = '';
		if ( in_array( $item_id, $bulk_pending_ids, true ) ) {
			$disable_bulk_class = 'wtai-disabled-button';
		}

		$action      = array();
		$post_status = get_post_status( $item_id );
		if ( ! in_array( $post_status, array( 'trash' ), true ) ) {
			$action['wtai-pid'] = '<span>ID: ' . $item_id . '</span>';
		}
		if ( ! in_array( $post_status, array( 'trash' ), true ) ) {
			$action['edit'] = '<a href="#" class="wtai-cwe-action-button wtai-cwe-action-button-edit" data-action="edit" >' . __( 'Edit', 'writetext-ai' ) . '</a>';
		}
		if ( ! in_array( $post_status, array( 'trash' ), true ) ) {
			$action['view'] = '<a href="' . get_permalink( $item_id ) . '" class="view" data-action="view" target="_blank">' . __( 'View', 'writetext-ai' ) . '</a>';
		}

		if ( ! in_array( $post_status, array( 'trash' ), true ) && wtai_current_user_can( 'writeai_transfer_generated_text' ) && WTAI_PREMIUM ) {
			$action['transfer'] = '<a href="#" class="wtai-cwe-action-button wtai-cwe-action-button-transfer ' . $disable_bulk_class . '" data-action="transfer">' . __( 'Transfer to WordPress', 'writetext-ai' ) . '</a>';
		}

		return $this->row_actions( $action );
	}

	/**
	 * Display count per post status
	 */
	protected function get_views() {
		$page_url      = menu_page_url( 'write-text-ai', false );
		$current       = ( ! empty( $_REQUEST['post_status'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_status'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification
		$class_all     = '';
		$class_publish = '';
		$class_future  = '';
		$class_draft   = '';
		$class_private = '';
		$class_pending = '';

		$post_type        = wp_count_posts( WTAI_POST_TYPE );
		$post_type_status = false;
		if ( property_exists( $post_type, 'publish' ) ) {
			$post_type_status = true;
		}

		$count_publish = $post_type_status ? wp_count_posts( WTAI_POST_TYPE )->publish : 0;
		$count_future  = $post_type_status ? wp_count_posts( WTAI_POST_TYPE )->future : 0;
		$count_draft   = $post_type_status ? wp_count_posts( WTAI_POST_TYPE )->draft : 0;
		$count_private = $post_type_status ? wp_count_posts( WTAI_POST_TYPE )->private : 0;
		$count_pending = $post_type_status ? wp_count_posts( WTAI_POST_TYPE )->pending : 0;
		$count_all     = $post_type_status ? $count_publish + $count_future + $count_draft + $count_private + $count_pending : 0;

		$current_language = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '';

		if ( $current_language && ! function_exists( 'pll_current_language' ) ) {
			$count_publish = 0;
			$count_future  = 0;
			$count_draft   = 0;
			$count_private = 0;
			$count_pending = 0;
			$count_all     = 0;

			global $wpdb;
			$post_type = WTAI_POST_TYPE; // Replace with your post type.

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"
						SELECT post_status, COUNT(ID) AS count
						FROM {$wpdb->posts}
						INNER JOIN {$wpdb->prefix}icl_translations icl ON {$wpdb->posts}.ID = icl.element_id
						WHERE icl.language_code = %s
						AND icl.element_type = %s
						GROUP BY post_status
					",
					$current_language,
					'post_' . $post_type
				),
				ARRAY_A
			);

			foreach ( $results as $result ) {
				switch ( $result['post_status'] ) {
					case 'publish':
						$count_publish = $result['count'];
						break;
					case 'future':
						$count_future = $result['count'];
						break;
					case 'draft':
						$count_draft = $result['count'];
						break;
					case 'private':
						$count_private = $result['count'];
						break;
					case 'pending':
						$count_pending = $result['count'];
						break;
				}
			}

			$count_all = $count_publish + $count_future + $count_draft + $count_private + $count_pending;
		} elseif ( function_exists( 'pll_current_language' ) ) {
			$pl_current_lang = apply_filters( 'wtai_language_code', '' );
			if ( $pl_current_lang ) {
				$count_publish_pl = pll_count_posts(
					$pl_current_lang,
					array(
						'post_type'   => WTAI_POST_TYPE,
						'post_status' => 'publish',
					)
				);
				if ( $count_publish_pl ) {
					$count_publish = $count_publish_pl;
				}

				$count_future_pl = pll_count_posts(
					$pl_current_lang,
					array(
						'post_type'   => WTAI_POST_TYPE,
						'post_status' => 'future',
					)
				);
				if ( $count_future_pl ) {
					$count_future = $count_future_pl;
				}

				$count_draft_pl = pll_count_posts(
					$pl_current_lang,
					array(
						'post_type'   => WTAI_POST_TYPE,
						'post_status' => 'draft',
					)
				);
				if ( $count_draft_pl ) {
					$count_draft = $count_draft_pl;
				}

				$count_private_pl = pll_count_posts(
					$pl_current_lang,
					array(
						'post_type'   => WTAI_POST_TYPE,
						'post_status' => 'private',
					)
				);
				if ( $count_private_pl ) {
					$count_private = $count_private_pl;
				}

				$count_pending_pl = pll_count_posts(
					$pl_current_lang,
					array(
						'post_type'   => WTAI_POST_TYPE,
						'post_status' => 'pending',
					)
				);
				if ( $count_pending_pl ) {
					$count_pending = $count_pending_pl;
				}

				$count_all = $post_type_status ? $count_publish + $count_future + $count_draft + $count_private + $count_pending : 0;
			}
		}

		switch ( $current ) {
			case 'all':
				$class_all = 'current';
				break;
			case 'publish':
				$class_publish = 'current';
				break;
			case 'future':
				$class_future = 'current';
				break;
			case 'pending':
				$class_pending = 'current';
				break;
			case 'draft':
				$class_draft = 'current';
				break;
			case 'private':
				$class_private = 'current';
				break;
			default:
				$class_all = 'current';
				break;
		}
		$all_text       = __( 'All' );
		$publish_text   = __( 'Published' );
		$scheduled_text = __( 'Scheduled' );
		$draft_text     = __( 'Draft' );
		$pending_text   = __( 'Pending' );
		$private_text   = __( 'Private' );

		$status_links['all'] = "<a href='$page_url' class='$class_all'>$all_text ($count_all)</a>";

		if ( $count_publish > 0 ) {
			$status_links['publish'] = "<a href='$page_url&post_status=publish' class='$class_publish'>$publish_text ($count_publish)</a>";
		}

		if ( $count_future > 0 ) {
			$status_links['future'] = "<a href='$page_url&post_status=future' class='$class_future'>$scheduled_text ($count_future)</a>";
		}

		if ( $count_draft > 0 ) {
			$status_links['draft'] = "<a href='$page_url&post_status=draft' class='$class_draft'>$draft_text ($count_draft)</a>";
		}

		if ( $count_pending > 0 ) {
			$status_links['pending'] = "<a href='$page_url&post_status=pending' class='$class_pending'>$pending_text ($count_pending)</a>";
		}

		if ( $count_private > 0 ) {
			$status_links['private'] = "<a href='$page_url&post_status=private' class='$class_private'>$private_text ($count_private)</a>";
		}

		return $status_links;
	}


	/**
	 * Add fields for filtering functions
	 *
	 * @param string $which Top or bottom.
	 */
	public function extra_tablenav( $which ) {

		if ( 'top' === $which ) {
			$meta_keys   = apply_filters( 'wtai_fields', array() );
			$wtai_status = isset( $_GET['wtai_writetext_status'] ) ? sanitize_text_field( wp_unslash( $_GET['wtai_writetext_status'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification
			$wtai_fields = isset( $_GET['wtai_writetext_fields'] ) && is_array( $_GET['wtai_writetext_fields'] ) ? map_deep( wp_unslash( $_GET['wtai_writetext_fields'] ), 'sanitize_text_field' ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
			?>
				<form method="get" class="wtai-wp-table-list-filter">
					<input name="page" type="hidden" value="write-text-ai" />
				<?php if ( isset( $_GET['orderby'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
							<input name="orderby" type="hidden" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?>" />
					<?php endif; ?>
				<?php if ( isset( $_GET['order'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
							<input name="order" type="hidden" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?>" />
					<?php endif; ?>
				<?php
				$cat_args = array(
					'show_option_all' => __( 'All categories' ),
					'taxonomy'        => 'product_cat',
					'hide_empty'      => 0,
					'hierarchical'    => 1,
					'orderby'         => 'name',
					'order'           => 'asc',
					'name'            => 'wtai_product_cat',
					'id'              => 'wtai-product-cat',
				);

				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_GET['wtai_product_cat'] ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification
					$cat_args['selected'] = sanitize_text_field( wp_unslash( $_GET['wtai_product_cat'] ) );
				}

				wp_dropdown_categories( $cat_args );

				if ( function_exists( 'wc_get_product_stock_status_options' ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification
					$current_stock_status = isset( $_GET['wtai_stock_status'] ) ? sanitize_text_field( wp_unslash( $_GET['wtai_stock_status'] ) ) : false;
					$stock_statuses       = wc_get_product_stock_status_options();

					?>
					<select name="wtai_stock_status" >
						<option value=""><?php echo esc_html__( 'Filter by stock status', 'woocommerce' ); ?></option>
						<?php
						foreach ( $stock_statuses as $status => $label ) {
							?>
							<option <?php echo selected( $status, $current_stock_status, false ); ?> value="<?php echo esc_attr( wp_unslash( $status ) ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php
						}
						?>
						</select>
					<?php
				}

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
									<label>
										<input type="radio" class="wtai-status-rd wtai-custom-status-rv" name="wtai_writetext_status" value="wtai_review_status" <?php $wtai_status ? checked( $wtai_status, 'wtai_review_status' ) : ''; ?> />
										<?php echo wp_kses_post( __( 'Review status', 'writetext-ai' ) ); ?>&nbsp;<em><?php echo wp_kses_post( __( '(from browser ext.)', 'writetext-ai' ) ); ?></em>
									</label>
									<?php
									$wtai_review_status_disabled           = '';
									$wtai_label_review_status_disabled     = '';
									$wtai_writetext_custom_status_selected = isset( $_GET['writetext_custom_review_status'] ) ? map_deep( wp_unslash( $_GET['writetext_custom_review_status'] ), 'wp_kses_post' ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
									$class_show                            = '';

									// phpcs:ignore WordPress.Security.NonceVerification
									if ( isset( $_GET['wtai_writetext_status'] ) && 'wtai_review_status' === $_GET['wtai_writetext_status'] ) {
										$class_show = 'show';
									}
									?>
									<div class="wtai-custom-grid-review-status-wrap <?php echo esc_attr( $class_show ); ?>">
										<label class="wtai-custom-status-label <?php echo esc_attr( $wtai_label_status_disabled ); ?>">
											<input type="checkbox" class="wtai-custom-reviewer-status-cb" <?php echo esc_attr( $wtai_status_disabled ); ?> name="writetext_custom_review_status[]" value="for_rewrite" <?php echo in_array( 'for_rewrite', $wtai_writetext_custom_status_selected, true ) ? 'checked' : ''; ?> />
											<span><?php echo wp_kses_post( __( 'For rewrite', 'writetext-ai' ) ); ?></span>
										</label>
										<label class="wtai-custom-status-label <?php echo esc_attr( $wtai_label_status_disabled ); ?>">
											<input type="checkbox" class="wtai-custom-reviewer-status-cb" <?php echo esc_attr( $wtai_status_disabled ); ?> name="writetext_custom_review_status[]" value="for_fact_checking" <?php echo in_array( 'for_fact_checking', $wtai_writetext_custom_status_selected, true ) ? 'checked' : ''; ?> />
											<span><?php echo wp_kses_post( __( 'For fact checking', 'writetext-ai' ) ); ?></span>
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
										<input type="radio" class="wtai-status-rd" name="wtai_writetext_status" value="no_activity" <?php $wtai_status ? checked( $wtai_status, 'no_activity' ) : ''; ?> />
									<?php echo wp_kses_post( __( 'Show products without activity for the last', 'writetext-ai' ) ); ?>
									</label>
									<span class="wtai-input-group">
										<input type="number" class="wtai-no-activity-days" id="wtai-no-activity-days" 
											name="no_activity_days" 
											data-date_from="<?php echo esc_attr( $filter_date_from ); ?>" 
											data-date_to="<?php echo esc_attr( $filter_date_to ); ?>" 
											class="wtai-specs-input noactivity" value="<?php echo esc_attr( wp_unslash( $last_no_activity ) ); ?>" 
											data-mintext="1" 
											data-maxtext="365" 
											data-original-value="7" title="<?php echo wp_kses_post( __( 'Please enter a value less than or equal to 365 days.', 'writetext-ai' ) ); ?>" >

										<span class="wtai-plus-minus-wrapper">
											<span class="dashicons dashicons-plus wtai-txt-plus noactivity noactivity-btn"></span>
											<span class="dashicons dashicons-minus wtai-txt-minus noactivity noactivity-btn"></span>
										</span>
									</span>
									<span>
									<?php echo wp_kses_post( __( 'day/s', 'writetext-ai' ) ); ?>
									</span>
							</div>
							<div class="wtai-reset wtai-btn-reset-status-wrapper">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=write-text-ai' ) ); ?>" class="wtai-btn-reset-status"><?php echo wp_kses_post( __( 'Reset', 'writetext-ai' ) ); ?></a>
							</div>
						</div>
					</div>
					<?php if ( isset( $_GET['s'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
						<input type="hidden" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?>" name="s" />
					<?php endif; ?>
					<?php if ( isset( $_GET['post_status'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
						<input type="hidden" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_GET['post_status'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?>" name="post_status">
					<?php endif; ?>
					<div class="wtai-button-wrapper">
						<input type="submit" value="<?php echo wp_kses_post( __( 'Filter', 'writetext-ai' ) ); ?>" class="button" id="wtai-filter-submit">
					</div>
					<div class="wtai-tooltip wtai-two-cols"><span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
						<div class="wtai-tooltiptext"><div class="wtai-tooltip-arrow wtai-noshadow"></div>
							<div class="wtai-col wtai-col-1">
							<?php
							echo '<p class="wtai-heading">' . wp_kses_post( __( 'Not generated', 'writetext-ai' ) ) . '</p>
                            <p>' . wp_kses_post( __( 'You have not yet generated any text for these products. Basically, these are the products that are "untouched" by WriteText.ai.', 'writetext-ai' ) ) . '</p>

                            <p class="wtai-heading">' . wp_kses_post( __( 'Generated', 'writetext-ai' ) ) . '</p>
                            <p>' . wp_kses_post( __( 'You have generated at least one text type (meta title, meta description, product description, product short description, or Open Graph text) for these products, but you have not yet done any editing to the text.', 'writetext-ai' ) ) . '</p>

                            <p class="wtai-heading">' . wp_kses_post( __( 'Edited', 'writetext-ai' ) ) . '</p>
                            <p>' . wp_kses_post( __( 'You have generated and edited at least one text type for these products, but you have not yet marked any of them as "Reviewed".', 'writetext-ai' ) ) . '</p>
                            <p class="wtai-heading">' . wp_kses_post( __( 'Reviewed', 'writetext-ai' ) ) . '</p>
                            <p>' . wp_kses_post( __( 'You have marked these products as "Reviewed" using the checkbox in the individual product editing page.', 'writetext-ai' ) ) . '</p>';
							?>
							</div>
							<div class="wtai-col wtai-col-2">
							<?php
							echo '<p class="wtai-heading">' . wp_kses_post( __( 'Transferred', 'writetext-ai' ) ) . '</p>
                            <p>' . wp_kses_post( __( 'You have transferred the text from WriteText.ai (whether edited by you or not) to WordPress. Transferred does not automatically mean published on your live site. If your product is still in draft, then transferring only saves text in the draft as well. If the product is already published, then transferring publishes the text changes.', 'writetext-ai' ) ) . '</p>
                            <p class="wtai-heading">' . wp_kses_post( __( 'Show products without activity', 'writetext-ai' ) ) . '</p>
                            <p>' . wp_kses_post( __( 'Select this box to see products without any activity (i.e., generating text, editing, reviewing, and transferring to WordPress) for the past number of days as defined in the field.', 'writetext-ai' ) ) . '</p>
                            <p><br><i>' . wp_kses_post( __( 'Note that WriteText.ai status is based on the last action done in the product.', 'writetext-ai' ) ) . '</i></p>';
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
		global $product_ids;
		$singular    = $this->_args['singular'];
		$tbody_class = array();
		if ( ! wtai_current_user_can( 'writeai_transfer_generated_text' ) ) {
			$tbody_class[] = 'no_transfer';
		}
		$this->display_tablenav( 'top' );

		$this->screen->render_screen_reader_content( 'heading_list' );

		$wp_pending_bulk_ids = $this->get_wtai_pending_ids();

		$product_edit_nonce = wp_create_nonce( 'wtai-product-nonce' );
		?>
		<div class="outer" id="wtai-start-sticky">
			<table class="wtai-list-table wp-list-table <?php echo esc_attr( implode( ' ', $this->get_table_classes() ) ); ?>" 
				data-product-nonce="<?php echo esc_attr( $product_edit_nonce ); ?>" 
				data-bulk-ids="<?php echo is_array( $wp_pending_bulk_ids ) ? implode( ',', map_deep( wp_unslash( $wp_pending_bulk_ids ), 'wp_kses_post' ) ) : ''; ?>" 
				data-ids="<?php echo is_array( $product_ids ) ? implode( ',', map_deep( wp_unslash( $product_ids ), 'wp_kses_post' ) ) : ''; ?>">
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
					<?php $this->display_rows_or_placeholder(); ?>
				</tbody>
			</table>
		</div>
		<?php
		$this->display_tablenav( 'bottom wtai-tablenav-bottom' );
	}

	/**
	 * Column date display.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function column_date( $post ) {
		global $mode;

		if ( '0000-00-00 00:00:00' === $post->post_date ) {
			$t_time    = __( 'Unpublished' );
			$time_diff = 0;
		} else {
			$t_time = sprintf(
				/* translators: 1: Post date, 2: Post time. */
				__( '%1$s at %2$s' ),
				/* translators: Post date format. See https://www.php.net/manual/datetime.format.php */
				get_the_time( get_option( 'date_format' ), $post ),
				/* translators: Post time format. See https://www.php.net/manual/datetime.format.php */
				get_the_time( get_option( 'time_format' ), $post )
			);

			$time      = get_post_timestamp( $post );
			$time_diff = time() - $time;
		}

		if ( 'publish' === $post->post_status ) {
			$status = __( 'Published' );
		} elseif ( 'future' === $post->post_status ) {
			if ( $time_diff > 0 ) {
				$status = '<strong class="error-message">' . __( 'Missed schedule' ) . '</strong>';
			} else {
				$status = __( 'Scheduled' );
			}
		} else {
			$status = __( 'Last Modified' );
		}

		/**
		 * Filters the status text of the post.
		 *
		 * @since 4.8.0
		 *
		 * @param string  $status      The status text.
		 * @param WP_Post $post        Post object.
		 * @param string  $column_name The column name.
		 * @param string  $mode        The list display mode ('excerpt' or 'list').
		 */
		$status = apply_filters( 'post_date_column_status', $status, $post, 'date', $mode );

		if ( $status ) {
			echo wp_kses_post( $status ) . '<br />';
		}

		/**
		 * Filters the published time of the post.
		 *
		 * @since 2.5.1
		 * @since 5.5.0 Removed the difference between 'excerpt' and 'list' modes.
		 *              The published time and date are both displayed now,
		 *              which is equivalent to the previous 'excerpt' mode.
		 *
		 * @param string  $t_time      The published time.
		 * @param WP_Post $post        Post object.
		 * @param string  $column_name The column name.
		 * @param string  $mode        The list display mode ('excerpt' or 'list').
		 */
		echo wp_kses_post( apply_filters( 'post_date_column_time', $t_time, $post, 'date', $mode ) );
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

		$wp_pending_bulk_ids   = $this->get_wtai_pending_ids();
		$disable_bulk_generate = 0;
		if ( $wp_pending_bulk_ids ) {
			if ( wtai_get_current_user_bulk_generation_products() ) {
				$disable_bulk_generate = 1;
			} elseif ( wtai_get_current_user_bulk_transfer_products() ) {
				$disable_bulk_generate = 1;
			}
		}
		if ( $this->items ) {
			$total_items = count( $this->items );
		}

		?>
	<div class="tablenav <?php echo esc_attr( $which ); ?> <?php echo ( 'bottom' === $which && 0 === $total_items ) ? 'tablenavhidden' : ''; ?>">

		<?php
		if ( 'top' === $which ) :
			$wtai_bulk_generate_ppopup = get_user_meta( get_current_user_id(), 'wtai_bulk_generate_popup', true );
			?>
			<div class="alignleft actions bulkactions wtai-bulkactions-wrap" data-disable-bulk-action="<?php echo esc_attr( $disable_bulk_generate ); ?>" >
				<div class="wtai-bulk-action-option-wrap <?php echo WTAI_PREMIUM ? '' : 'wtai-disable-premium-feature'; ?>">
					<?php $this->bulk_actions( $which ); ?>
				</div>

				<?php
				if ( wtai_current_user_can( 'writeai_generate_text' ) || wtai_current_user_can( 'writeai_transfer_generated_text' ) ) {
					do_action( 'wtai_product_single_premium_badge', 'wtai-premium-bulk-options' );
				}
				?>
				
				<?php
				if ( wtai_current_user_can( 'writeai_generate_text' ) || wtai_current_user_can( 'writeai_transfer_generated_text' ) ) {
					?>
					<div class="wtai-tooltip wtai-two-cols"><span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
						<div class="wtai-tooltiptext"><div class="wtai-tooltip-arrow wtai-noshadow"></div>
						<?php
						echo '
						<div class="wtai-col wtai-col-1">
						<p class="wtai-heading">' . wp_kses_post( __( 'Bulk generate', 'writetext-ai' ) ) . '</p>
						<p>' . wp_kses_post( __( 'Generate multiple or all product text types (meta title, meta description, product description, product short description, and Open Graph text) at once for the products you have selected.', 'writetext-ai' ) ) . '</p>
						<p class="wtai-heading">' . wp_kses_post( __( 'Bulk generate pop-up settings', 'writetext-ai' ) ) . '</p>
						<p>' . wp_kses_post( __( 'Choose your preferred tones, style, and audiences, as well as the product attributes that you want to be considered in generating text, including your product images. Note that only the featured image per product will be used in generating text. You can check what image is used as the featured image in the individual product page. Make sure that the featured image set accurately represents the product (i.e., it is not some kind of placeholder or a generic image for your shop) in order for AI to generate relevant text. You can also set the target length for your product descriptions here. Note that your credit cost will depend on the target length you set so make sure to set a reasonable target range.', 'writetext-ai' ) ) . '</p>
						<p>&nbsp;</p>
						<p>&nbsp;</p>
						</div>
						<div class="wtai-col wtai-col-2">
						<p class="wtai-heading">' . wp_kses_post( __( 'Bulk transfer', 'writetext-ai' ) ) . '</p>
						<p>' . wp_kses_post( __( 'Transferring your text to WordPress will either save it as a draft or publish it on the website, depending on the current status of your product. For example, if the product is already published, the text you transfer from WriteText.ai will automatically be published live. If the product is still in draft, the text you transfer will also be saved as a draft, and you will need to publish the product first if you want to see the text live. ', 'writetext-ai' ) ) . '</p>						
						<p>' . wp_kses_post( __( 'To see the current status of your product, click on Edit and look for the “Transfer to WordPress” box in the upper right corner of the page.', 'writetext-ai' ) ) . '</p>
						<p>' . wp_kses_post( __( 'Note: Any media or shortcode you have inserted in your current WordPress text will be overwritten when you transfer from WriteText.ai.', 'writetext-ai' ) ) . '</p>
						</p>
						</div>';
						?>

						</div>
					</div>
					<?php
				} else {
					?>
					<style>
						.wtai-table-list-wrapper .tablenav .wtai-wp-table-list-filter{
							margin-left: 0;
						}
					</style>
					<?php
				}
				?>

				<?php
				$prod_desc_length_min                = apply_filters( 'wtai_global_settings', 'wtai_installation_product_description_min' );
				$prod_desc_length_max                = apply_filters( 'wtai_global_settings', 'wtai_installation_product_description_max' );
				$prod_excerpt_length_min             = apply_filters( 'wtai_global_settings', 'wtai_installation_product_excerpt_min' );
				$prod_excerpt_length_max             = apply_filters( 'wtai_global_settings', 'wtai_installation_product_excerpt_max' );
				$tones_array                         = apply_filters( 'wtai_global_settings', 'wtai_installation_tones' );
				$tones                               = is_array( $tones_array ) ? implode( ',', $tones_array ) : '';
				$style                               = apply_filters( 'wtai_global_settings', 'wtai_installation_styles' );
				$audience_array                      = apply_filters( 'wtai_global_settings', 'wtai_installation_audiences' );
				$audiences                           = is_array( $audience_array ) ? implode( ',', $audience_array ) : '';
				$product_attr_array                  = apply_filters( 'wtai_global_settings', 'wtai_installation_product_attr' );
				$product_attr                        = is_array( $product_attr_array ) ? implode( ',', $product_attr_array ) : '';
				$user_bulk_generate_text_fields_init = wtai_get_bulk_generate_text_fields_user_preference();
				$user_bulk_generate_text_fields      = '';
				if ( $user_bulk_generate_text_fields_init && is_array( $user_bulk_generate_text_fields_init ) ) {
					$user_bulk_generate_text_fields = implode( ',', $user_bulk_generate_text_fields_init );
				}

				$product_fields_all = apply_filters( 'wtai_fields', array() );
				$product_field_key  = array_keys( $product_fields_all );
				$product_fields_key = implode( ',', $product_field_key );

				$user_bulk_generate_text_fields = $product_fields_key;
				?>
			<input type="hidden" name="wtai_bulk_generate_ppopup" id="wtai-bulk-generate-ppopup" value="<?php echo esc_attr( $wtai_bulk_generate_ppopup ); ?>"
				data-pdesc_length_min="<?php echo esc_attr( $prod_desc_length_min ); ?>"
				data-pdesc_length_max="<?php echo esc_attr( $prod_desc_length_max ); ?>" 
				data-pexcerpt_length_min="<?php echo esc_attr( $prod_excerpt_length_min ); ?>" 
				data-pexcerpt_length_max="<?php echo esc_attr( $prod_excerpt_length_max ); ?>" 
				data-tones="<?php echo esc_attr( $tones ); ?>" 
				data-style="<?php echo esc_attr( $style ); ?>" 
				data-audiences="<?php echo esc_attr( $audiences ); ?>" 
				data-productattr="<?php echo esc_attr( $product_attr ); ?>" 
				data-textfields="<?php echo esc_attr( $user_bulk_generate_text_fields ); ?>" 
			/>
			</div>
			<?php
		endif;

		$this->extra_tablenav( $which );
		?>
		<?php
		if ( 'top' === $which ) {
			echo '<div class="wtai-comparison-pager">';
				$this->pagination( $which );
			echo '</div>';
		}
		?>

		<input type="hidden" id="wtai-prev-current-page-number" value="<?php echo esc_attr( $this->get_pagenum() ); ?>" />
		<input type="hidden" id="wtai-next-page-number" value="<?php echo esc_attr( $this->get_pagenum() ); ?>" />
		<input type="hidden" id="wtai-next-prev-max-page-number" value="<?php echo esc_attr( $this->wtai_max_num_pages ); ?>" />
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

		$post_id = $item['wtai_id'];
		$post    = get_post( $post_id );
		$prod    = wc_get_product( $post_id );
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

			$data = 'data-colname="' . esc_attr( wp_strip_all_tags( $column_display_name ) ) . '"';
			if ( strpos( $column_name, 'wtai_' ) !== false ) {
				switch ( $column_name ) {
					case 'wtai_page_title':
					case 'wtai_page_description':
					case 'wtai_product_description':
					case 'wtai_product_excerpt': // Added for undefined fixed on hover.
					case 'wtai_open_graph':
						$data .= ' data-text="' . htmlentities( $item[ $column_name . '_full' ] ) . '"';
						break;
				}
			}

			$column_class = str_replace( array( 'wtai_' ), '', $column_name );
			switch ( $column_class ) {
				case 'page_title':
				case 'page_description':
				case 'product_description':
					if ( ! empty( $post->post_password ) && 'product_description' === $column_name ) {
						$product_content = $prod->get_description();
						$data           .= ' data-text="' . htmlentities( $product_content ) . '"';
					}
					// No break.
				case 'product_excerpt': // No break.
					if ( ! empty( $post->post_password ) && 'product_excerpt' === $column_name ) {
						$product_excerpt = $prod->get_short_description();
						$data           .= ' data-text="' . htmlentities( $product_excerpt ) . '"';
					}
					// No break.
				case 'open_graph':
					if ( ( isset( $item[ 'wtai_' . $column_class ] ) && strpos( $item[ 'wtai_' . $column_class ], '&hellip;' ) !== false ) ||
						( isset( $item[ $column_class ] ) && strpos( $item[ $column_class ], '&hellip;' ) !== false ) ) {
						$classes .= ' tooltip_hover';
					}
					$data .= ' data-colgrp="' . $column_class . '"';
					break;
			}

			$attributes = "class='$classes' $data";

			if ( 'cb' === $column_name ) {
				echo '<th scope="row" class="check-column">';
				echo wp_kses( $this->column_cb( $item ), wtai_kses_allowed_html() );
				echo '</th>';
			} elseif ( method_exists( $this, '_column_' . $column_name ) ) {
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
	 * Generates content for a single row of the table.
	 *
	 * @param object|array $item The current item.
	 */
	public function single_row( $item ) {
		$wtai_pending_ids = $this->get_wtai_pending_ids();
		$class            = '';
		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
		if ( is_array( $wtai_pending_ids ) && in_array( $item['wtai_id'], $wtai_pending_ids ) ) {
			$class = 'wtai-processing';
		}

		// Get image alt ids.
		$product_id    = $item['wtai_id'];
		$alt_image_ids = wtai_get_product_image( $product_id );

		echo '<tr id="wtai-table-list-' . esc_attr( $item['wtai_id'] ) . '" data-id="' . esc_attr( $item['wtai_id'] ) . '" data-image-ids="' . esc_attr( implode( ',', $alt_image_ids ) ) . '" 
				data-values=\'' . esc_attr( $item['wtai_data'] ) . '\' class="' . esc_attr( $class ) . '">';
				$this->single_row_columns( $item );
		echo '</tr>';
	}
}