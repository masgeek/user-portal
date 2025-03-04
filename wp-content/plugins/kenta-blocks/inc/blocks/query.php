<?php
/**
 * Query block config
 *
 * @package Kenta Blocks
 */

$attributes = array(
	'blockID'   => array(
		'type'    => 'string',
		'default' => '',
	),
	'namespace' => array(
		'type'    => 'string',
		'default' => '',
	),
	'tagName'   => array(
		'type'    => 'string',
		'default' => 'div'
	),
	'query'     => array(
		'type'    => 'object',
		'default' => array(
			'perPage'         => 6,
			'pages'           => 0,
			'offset'          => 0,
			'source'          => 'post',
			'order'           => 'desc',
			'orderBy'         => 'date',
			'selection'       => 'dynamic',
			'excludeNoImages' => false,
//			'notFoundText'    => __( 'No Posts Found!', 'kenta-blocks' ),
			'authors'         => array(),
			'excludeItems'    => array(),
			'taxonomyFilters' => array(),
			'selectedItems'   => array(),
		)
	)
);

$metadata = array(
	'title'            => __( 'Posts Query (KB)', 'kenta-blocks' ),
	'description'      => __( "An advanced block that allows displaying post types based on different query parameters and visual configurations.", 'kenta-blocks' ),
	'keywords'         => array( 'query', 'posts', 'grid', 'magazine' ),
	'supports'         => array(
		'anchor' => true,
		'align'  => array( 'wide', 'full' ),
	),
	'attributes'       => $attributes,
	'provides_context' => array(
		'queryId' => 'blockID',
		'query'   => 'query',
	)
);

return array(
	'metadata' => $metadata
);
