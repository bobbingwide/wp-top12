/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * These imports were added using the best guess technique.
 *
 */
import { ServerSideRender } from '@wordpress/editor';
import { Fragment } from '@wordpress/element';
import { InspectorControls, PlainText, BlockControls } from '@wordpress/block-editor';
//const { InspectorControls } = wp.blockEditor;
// deprecated.js?ver=cd9e35508705772fbc5e2d9736bde31b:177 wp.editor.InspectorControls is deprecated. Please use wp.blockEditor.InspectorControls instead.
import { TextControl, PanelBody, SelectControl, Toolbar, ToolbarButton, PanelRow, ToggleControl, RangeControl } from '@wordpress/components';
import { map } from 'lodash';
import { useEffect } from '@wordpress/element';
import { withInstanceId } from '@wordpress/compose';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';


/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @param {Object} [props]           Properties passed from the editor.
 * @param {string} [props.className] Class name generated for the block.
 *
 * @return {WPElement} Element to render.
 */
function edit ( { attributes, className, isSelected, setAttributes, instanceId } )   {

	const onChangeLimit = ( event ) => {
		setAttributes( { limit: event } );
	};

	const onChangeIncludes = ( event ) => {
		setAttributes( { includes: event } );
	};

	const onChangeExcludes = (value) => {
		setAttributes({ excludes: value });
	};

	const onChangeSlugs = (value) => {
		setAttributes( { slugs: value });
	}


	return (
		<Fragment>

			<InspectorControls>
				<PanelBody>
					<PanelRow>

					</PanelRow>

				</PanelBody>
				<PanelBody>

					<PanelRow>
						<RangeControl
							label={ __( "Limit", 'wp-top12' ) }
							value={ attributes.limit }
							initialPosition={12}
							onChange={ onChangeLimit }
							min={ 1 }
							max={ 1000 }
							allowReset
						/>

					</PanelRow>

				</PanelBody>
			</InspectorControls>

			<div className="wp-block-wp-top12">
				<PlainText
					value={attributes.includes}
					placeholder={__('Enter include strings')}
					onChange={onChangeIncludes}
				/>
				<PlainText
					value={attributes.excludes}
					placeholder={__('Enter exclude strings')}
					onChange={onChangeExcludes}
				/>
				<PlainText
					value={attributes.slugs}
					placeholder={__('Enter slugs')}
					onChange={onChangeSlugs}
				/>


					<ServerSideRender block="wp-top12/wp-top12" attributes={attributes} />

			</div>
		</Fragment>

	);
}

/**
 * I honestly don't understand Higher Order Components,
   but this seems to wrap the edit component with withInstanceId,
   which enables the function to access the instance ID.
   The save() function doesn't get this parameter, but it does get attributes.
   So we use the myChartId attribute to pass the ID for the canvas.
 */
export default withInstanceId( edit );
