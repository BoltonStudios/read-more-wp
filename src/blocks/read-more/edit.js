import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

const settings = window.rmwpBlockSettings || {};

const TEMPLATE = [
	[
		'core/paragraph',
		{
			placeholder: __(
				'Add the content visitors will see after clicking the button…',
				'read-more-wp'
			),
		},
	],
];

export default function Edit( { attributes, setAttributes } ) {
	const { moreLabel, lessLabel, showEllipsis, mode } = attributes;
	const isFade = settings.isPlus && mode === 'fade';

	const blockProps = useBlockProps( { className: 'rmwp-block-editor' } );
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'rmwp-block-editor__content' },
		{ template: TEMPLATE }
	);

	const buttonPreview = (
		<span className="rmwp-button-wrap">
			<button type="button" tabIndex={ -1 } aria-hidden="true">
				{ moreLabel || settings.more || __( 'Read More', 'read-more-wp' ) }
			</button>
		</span>
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Button', 'read-more-wp' ) }>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( '"Read More" label', 'read-more-wp' ) }
						value={ moreLabel }
						placeholder={ settings.more }
						help={ __(
							'Leave empty to use the default from Settings › Read More WP.',
							'read-more-wp'
						) }
						onChange={ ( value ) =>
							setAttributes( { moreLabel: value } )
						}
					/>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __( '"Read Less" label', 'read-more-wp' ) }
						value={ lessLabel }
						placeholder={ settings.less }
						onChange={ ( value ) =>
							setAttributes( { lessLabel: value } )
						}
					/>
					{ ! isFade && (
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __(
								'Show an ellipsis ("...") before the button',
								'read-more-wp'
							) }
							checked={ showEllipsis }
							onChange={ ( value ) =>
								setAttributes( { showEllipsis: value } )
							}
						/>
					) }
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<div className="rmwp-block-editor__header">
					{ ! isFade && showEllipsis && (
						<span aria-hidden="true">...</span>
					) }
					{ ! isFade && buttonPreview }
					<span className="rmwp-block-editor__note">
						{ isFade
							? __(
									'Visitors see the start of this content, faded out, with a button to show the rest.',
									'read-more-wp'
							  )
							: __(
									'The content below is hidden until visitors click the button.',
									'read-more-wp'
							  ) }
					</span>
				</div>
				<div { ...innerBlocksProps } />
				{ isFade && (
					<div className="rmwp-block-editor__footer">
						{ buttonPreview }
					</div>
				) }
			</div>
		</>
	);
}
