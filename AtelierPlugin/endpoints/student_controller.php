<?php

class WP_REST_Student_Controller extends WP_REST_Controller {

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        $version = '1';
        $namespace = 'atelier/v' . $version;
        $base = 'student';
        register_rest_route( $namespace, '/' . $base, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => array(

                ),
            ),
            array(
                'methods'         => WP_REST_Server::CREATABLE,
                'callback'        => array( $this, 'create_item' ),
                'permission_callback' => array( $this, 'create_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( true ),
            ),
        ) );
        register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_item' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'            => array(
                    'context'          => array(
                        'default'      => 'view',
                    ),
                ),
            ),
            array(
                'methods'         => WP_REST_Server::EDITABLE,
                'callback'        => array( $this, 'update_item' ),
                'permission_callback' => array( $this, 'update_item_permissions_check' ),
                'args'            => $this->get_endpoint_args_for_item_schema( false ),
            ),
            array(
                'methods'  => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_item' ),
                'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                'args'     => array(
                    'force'    => array(
                        'default'      => false,
                    ),
                ),
            ),
        ) );
        register_rest_route( $namespace, '/' . $base . '/schema', array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'get_public_item_schema' ),
        ) );
    }

    /**
     * Get a collection of items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_items( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'students';
        $items = $wpdb->get_results("SELECT * FROM $table_name");
        $data = array();
        foreach( $items as $item ) {
            $itemdata = $this->prepare_item_for_response( $item, $request );
            $data[] = $this->prepare_response_for_collection( $itemdata );
        }

        return new WP_REST_Response( $data, 200 );
    }

    /**
     * Get one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_item( WP_REST_Request $request ) {
		global $wpdb;
        //get parameters from request
        $params = $request->get_params();
		$id = $params['id'];
		$table_name = $wpdb->prefix . 'students';
        $item = $wpdb->get_row("SELECT * FROM $table_name WHERE student_id = $id");
        $data = $this->prepare_item_for_response( $item, $request );

        if ( null !== $data ) {
            return new WP_REST_Response( $data, 200 );
        } else {
			return new WP_Error( 'Not Found', "No student found with id: $id", array( 'status' => 404 ) );
        }
    }

    /**
     * Create one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
    public function create_item( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'students';
		
        $item = $this->prepare_item_for_database( $request );
		$result = $wpdb->insert($table_name, $item);
        if ( $result ) {
			$item['student_id'] = $wpdb->insert_id;
			return new WP_REST_Response( $item, 200 );
        }

        return new WP_Error( 'Internal Server Error', "DB error: ".$wpdb->last_error, array( 'status' => 500 ) );


    }

    /**
     * Update one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
    public function update_item( $request ) {
        $item = $this->prepare_item_for_database( $request );

        if ( function_exists( 'slug_some_function_to_update_item')  ) {
            $data = slug_some_function_to_update_item( $item );
            if ( is_array( $data ) ) {
                return new WP_REST_Response( $data, 200 );
            }
        }

        return new WP_Error( 'cant-update', __( 'message', 'text-domain'), array( 'status' => 500 ) );

    }

    /**
     * Delete one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Request
     */
    public function delete_item( $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'students';
		
        $id = $this->prepare_item_for_database( $request )['id'];
		$deleted = $wpdb->delete($table_name, "student_id = $id");
		if (  $deleted  ) {
			return new WP_REST_Response( null, 204 );
		}

        return new WP_Error( 'Internal Server Error', ($wpdb->last_error), array( 'status' => 500 ) );
    }

    /**
     * Check if a given request has access to get items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_items_permissions_check( $request ) {
        return true; //<--use to make readable by all
        return current_user_can( 'edit_something' );
    }

    /**
     * Check if a given request has access to get a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_item_permissions_check( $request ) {
        return $this->get_items_permissions_check( $request );
    }

    /**
     * Check if a given request has access to create items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function create_item_permissions_check( $request ) {
        return current_user_can( 'edit_plugins' );
    }

    /**
     * Check if a given request has access to update a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function update_item_permissions_check( $request ) {
        return $this->create_item_permissions_check( $request );
    }

    /**
     * Check if a given request has access to delete a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function delete_item_permissions_check( $request ) {
        return $this->create_item_permissions_check( $request );
    }

    /**
     * Prepare the item for create or update operation
     *
     * @param WP_REST_Request $request Request object
     * @return WP_Error|object $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        return $request->get_params();
    }

    /**
     * Prepare the item for the REST response
     *
     * @param mixed $item WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     * @return mixed
     */
    public function prepare_item_for_response( $item, $request ) {
        return $item;
    }

    /**
     * Get the query params for collections
     *
     * @return array
     */
    public function get_collection_params() {
        return array(
            'page'                   => array(
                'description'        => 'Current page of the collection.',
                'type'               => 'integer',
                'default'            => 1,
                'sanitize_callback'  => 'absint',
            ),
            'per_page'               => array(
                'description'        => 'Maximum number of items to be returned in result set.',
                'type'               => 'integer',
                'default'            => 10,
                'sanitize_callback'  => 'absint',
            ),
            'search'                 => array(
                'description'        => 'Limit results to those matching a string.',
                'type'               => 'string',
                'sanitize_callback'  => 'sanitize_text_field',
            ),
        );
    }
	
	public function get_endpoint_args_for_item_schema( $create ) {
		return array(
			'first_name'			=> array(
				'type'				=> 'string',
				'sanitize_callback'  => 'sanitize_text_field',
			),
			'last_name'			=> array(
				'type'				=> 'string',
				'sanitize_callback'  => 'sanitize_text_field',
			),
		);
	}
}