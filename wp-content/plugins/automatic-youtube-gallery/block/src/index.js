/**
 * Import block dependencies
 */
import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';
import metadata from './block.json';

import { getBlockAttributes } from './helper.js';

/**
 * Register the block.
 */
registerBlockType( metadata.name, {
	attributes: getBlockAttributes(),
	edit: Edit
} );
