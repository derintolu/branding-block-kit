(function(wp) {
    const { registerBlockType } = wp.blocks;
    const { createElement: el, Fragment } = wp.element;
    const { InspectorControls, useBlockProps } = wp.blockEditor;
    const { PanelBody, TextControl, ToggleControl, RangeControl, SelectControl } = wp.components;
    const { ServerSideRender } = wp.serverSideRender || wp.editor;
    const __ = wp.i18n.__;

    // Icon for brand blocks
    const brandIcon = el('svg', { 
        xmlns: 'http://www.w3.org/2000/svg', 
        viewBox: '0 0 24 24',
        width: 24,
        height: 24
    },
        el('path', { 
            d: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z',
            fill: 'currentColor'
        })
    );

    // Color Palette Block
    registerBlockType('bbk/color-palette', {
        title: __('Color Palette', 'branding-block-kit'),
        description: __('Display color palette from theme.json', 'branding-block-kit'),
        icon: brandIcon,
        category: 'branding-block-kit',
        attributes: {
            title: { type: 'string', default: '' },
            groupLabel: { type: 'string', default: '' },
            showHex: { type: 'boolean', default: true },
            showName: { type: 'boolean', default: true },
            showSlug: { type: 'boolean', default: false },
            columns: { type: 'number', default: 4 },
            swatchStyle: { type: 'string', default: 'chip' },
            layout: { type: 'string', default: 'row' },
            swatchSize: { type: 'string', default: 'medium' },
            filterSlugs: { type: 'string', default: '' }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            return el(Fragment, {},
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Layout', 'branding-block-kit') },
                        el(SelectControl, {
                            label: __('Layout Style', 'branding-block-kit'),
                            value: attributes.layout,
                            options: [
                                { label: 'Horizontal Row (Expandable)', value: 'row' },
                                { label: 'Grid', value: 'grid' },
                                { label: 'List', value: 'list' },
                                { label: 'Inline', value: 'inline' }
                            ],
                            onChange: (val) => setAttributes({ layout: val })
                        }),
                        el(RangeControl, {
                            label: __('Columns (for Grid)', 'branding-block-kit'),
                            value: attributes.columns,
                            onChange: (val) => setAttributes({ columns: val }),
                            min: 2,
                            max: 8
                        }),
                        el(SelectControl, {
                            label: __('Swatch Shape', 'branding-block-kit'),
                            value: attributes.swatchStyle,
                            options: [
                                { label: 'Chip (Expandable)', value: 'chip' },
                                { label: 'Card', value: 'card' },
                                { label: 'Large Card', value: 'large-card' },
                                { label: 'Circle', value: 'circle' },
                                { label: 'Square', value: 'square' },
                                { label: 'Pill', value: 'pill' },
                                { label: 'Stripe', value: 'stripe' },
                                { label: 'Minimal', value: 'minimal' },
                                { label: 'Grid Expand (Touching)', value: 'grid-expand' },
                                { label: '— 21C Brand Styles —', value: '', disabled: true },
                                { label: 'Brand Chips (21C)', value: 'brand-chips' },
                                { label: 'Brand Squares (21C)', value: 'brand-squares' },
                                { label: 'Brand Bars (21C)', value: 'brand-bars' },
                                { label: 'Brand Cards (21C)', value: 'brand-cards' }
                            ],
                            onChange: (val) => setAttributes({ swatchStyle: val })
                        }),
                        el(SelectControl, {
                            label: __('Swatch Size', 'branding-block-kit'),
                            value: attributes.swatchSize,
                            options: [
                                { label: 'Small', value: 'small' },
                                { label: 'Medium', value: 'medium' },
                                { label: 'Large', value: 'large' }
                            ],
                            onChange: (val) => setAttributes({ swatchSize: val })
                        })
                    ),
                    el(PanelBody, { title: __('Labels & Display', 'branding-block-kit'), initialOpen: false },
                        el(TextControl, {
                            label: __('Section Title', 'branding-block-kit'),
                            value: attributes.title,
                            onChange: (val) => setAttributes({ title: val })
                        }),
                        el(TextControl, {
                            label: __('Group Label', 'branding-block-kit'),
                            value: attributes.groupLabel,
                            onChange: (val) => setAttributes({ groupLabel: val }),
                            help: __('Small uppercase label above colors (e.g. "Primary Palette")', 'branding-block-kit')
                        }),
                        el(ToggleControl, {
                            label: __('Show Hex Values', 'branding-block-kit'),
                            checked: attributes.showHex,
                            onChange: (val) => setAttributes({ showHex: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Names', 'branding-block-kit'),
                            checked: attributes.showName,
                            onChange: (val) => setAttributes({ showName: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show CSS Variable Slugs', 'branding-block-kit'),
                            checked: attributes.showSlug,
                            onChange: (val) => setAttributes({ showSlug: val })
                        })
                    ),
                    el(PanelBody, { title: __('Filter Colors', 'branding-block-kit'), initialOpen: false },
                        el(TextControl, {
                            label: __('Filter by Slugs', 'branding-block-kit'),
                            value: attributes.filterSlugs,
                            onChange: (val) => setAttributes({ filterSlugs: val }),
                            help: __('Comma-separated color slugs to include. Leave empty for all.', 'branding-block-kit')
                        })
                    )
                ),
                el('div', blockProps,
                    el(ServerSideRender, {
                        block: 'bbk/color-palette',
                        attributes: attributes
                    })
                )
            );
        },
        save: function() {
            return null; // Server-side rendered
        }
    });

    // Gradient Showcase Block
    registerBlockType('bbk/gradient-showcase', {
        title: __('Gradient Showcase', 'branding-block-kit'),
        description: __('Display gradients from theme.json', 'branding-block-kit'),
        icon: brandIcon,
        category: 'branding-block-kit',
        attributes: {
            title: { type: 'string', default: 'Gradients' },
            showName: { type: 'boolean', default: true },
            showCode: { type: 'boolean', default: true },
            layout: { type: 'string', default: 'grid' },
            columns: { type: 'number', default: 3 },
            swatchStyle: { type: 'string', default: 'card' },
            swatchSize: { type: 'string', default: 'medium' }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            return el(Fragment, {},
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Layout', 'branding-block-kit') },
                        el(SelectControl, {
                            label: __('Layout Style', 'branding-block-kit'),
                            value: attributes.layout,
                            options: [
                                { label: 'Grid', value: 'grid' },
                                { label: 'Horizontal Row', value: 'row' },
                                { label: 'Stack (Full Width)', value: 'stack' },
                                { label: 'List', value: 'list' },
                                { label: 'Inline', value: 'inline' }
                            ],
                            onChange: (val) => setAttributes({ layout: val })
                        }),
                        el(RangeControl, {
                            label: __('Columns (for Grid)', 'branding-block-kit'),
                            value: attributes.columns,
                            onChange: (val) => setAttributes({ columns: val }),
                            min: 2,
                            max: 6
                        }),
                        el(SelectControl, {
                            label: __('Swatch Shape', 'branding-block-kit'),
                            value: attributes.swatchStyle,
                            options: [
                                { label: 'Card', value: 'card' },
                                { label: 'Large Card', value: 'large-card' },
                                { label: 'Bar', value: 'bar' },
                                { label: 'Square', value: 'square' },
                                { label: 'Circle', value: 'circle' },
                                { label: 'Pill', value: 'pill' },
                                { label: 'Chip (Expandable)', value: 'chip' },
                                { label: 'Minimal', value: 'minimal' },
                                { label: 'Grid Expand (Touching)', value: 'grid-expand' },
                                { label: '— 21C Brand Styles —', value: '', disabled: true },
                                { label: 'Brand Chips (21C)', value: 'brand-chips' },
                                { label: 'Brand Squares (21C)', value: 'brand-squares' },
                                { label: 'Brand Bars (21C)', value: 'brand-bars' },
                                { label: 'Brand Cards (21C)', value: 'brand-cards' }
                            ],
                            onChange: (val) => setAttributes({ swatchStyle: val })
                        }),
                        el(SelectControl, {
                            label: __('Swatch Size', 'branding-block-kit'),
                            value: attributes.swatchSize,
                            options: [
                                { label: 'Small', value: 'small' },
                                { label: 'Medium', value: 'medium' },
                                { label: 'Large', value: 'large' }
                            ],
                            onChange: (val) => setAttributes({ swatchSize: val })
                        })
                    ),
                    el(PanelBody, { title: __('Display Options', 'branding-block-kit'), initialOpen: false },
                        el(TextControl, {
                            label: __('Title', 'branding-block-kit'),
                            value: attributes.title,
                            onChange: (val) => setAttributes({ title: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Names', 'branding-block-kit'),
                            checked: attributes.showName,
                            onChange: (val) => setAttributes({ showName: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show CSS Code', 'branding-block-kit'),
                            checked: attributes.showCode,
                            onChange: (val) => setAttributes({ showCode: val })
                        })
                    )
                ),
                el('div', blockProps,
                    el(ServerSideRender, {
                        block: 'bbk/gradient-showcase',
                        attributes: attributes
                    })
                )
            );
        },
        save: function() {
            return null;
        }
    });

    // Typography Samples Block
    registerBlockType('bbk/typography-samples', {
        title: __('Typography Samples', 'branding-block-kit'),
        description: __('Display typography from theme.json', 'branding-block-kit'),
        icon: brandIcon,
        category: 'branding-block-kit',
        attributes: {
            title: { type: 'string', default: 'Typography' },
            sampleText: { type: 'string', default: 'The quick brown fox jumps over the lazy dog' },
            showFontSize: { type: 'boolean', default: true },
            showFontFamily: { type: 'boolean', default: true },
            display: { type: 'string', default: 'all' },
            layout: { type: 'string', default: 'stack' },
            columns: { type: 'number', default: 2 },
            cardStyle: { type: 'string', default: 'card' },
            textAlign: { type: 'string', default: 'left' }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            return el(Fragment, {},
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Layout', 'branding-block-kit') },
                        el(SelectControl, {
                            label: __('Layout Style', 'branding-block-kit'),
                            value: attributes.layout,
                            options: [
                                { label: 'Stack (Full Width)', value: 'stack' },
                                { label: 'Grid', value: 'grid' },
                                { label: 'List (Compact)', value: 'list' },
                                { label: 'Cards', value: 'cards' }
                            ],
                            onChange: (val) => setAttributes({ layout: val })
                        }),
                        el(RangeControl, {
                            label: __('Columns (for Grid/Cards)', 'branding-block-kit'),
                            value: attributes.columns,
                            onChange: (val) => setAttributes({ columns: val }),
                            min: 2,
                            max: 4
                        }),
                        el(SelectControl, {
                            label: __('Card Style', 'branding-block-kit'),
                            value: attributes.cardStyle,
                            options: [
                                { label: 'Card', value: 'card' },
                                { label: 'Minimal', value: 'minimal' },
                                { label: 'Bordered', value: 'bordered' },
                                { label: '— 21C Brand Styles —', value: '', disabled: true },
                                { label: 'Brand Card (21C)', value: 'brand-card' },
                                { label: 'Brand Minimal (21C)', value: 'brand-minimal' }
                            ],
                            onChange: (val) => setAttributes({ cardStyle: val })
                        }),
                        el(SelectControl, {
                            label: __('Text Alignment', 'branding-block-kit'),
                            value: attributes.textAlign,
                            options: [
                                { label: 'Left', value: 'left' },
                                { label: 'Center', value: 'center' },
                                { label: 'Right', value: 'right' }
                            ],
                            onChange: (val) => setAttributes({ textAlign: val })
                        })
                    ),
                    el(PanelBody, { title: __('Content', 'branding-block-kit'), initialOpen: false },
                        el(TextControl, {
                            label: __('Title', 'branding-block-kit'),
                            value: attributes.title,
                            onChange: (val) => setAttributes({ title: val })
                        }),
                        el(TextControl, {
                            label: __('Sample Text', 'branding-block-kit'),
                            value: attributes.sampleText,
                            onChange: (val) => setAttributes({ sampleText: val })
                        }),
                        el(SelectControl, {
                            label: __('Display', 'branding-block-kit'),
                            value: attributes.display,
                            options: [
                                { label: 'All', value: 'all' },
                                { label: 'Font Sizes Only', value: 'sizes' },
                                { label: 'Font Families Only', value: 'families' }
                            ],
                            onChange: (val) => setAttributes({ display: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Font Size Values', 'branding-block-kit'),
                            checked: attributes.showFontSize,
                            onChange: (val) => setAttributes({ showFontSize: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Font Family Values', 'branding-block-kit'),
                            checked: attributes.showFontFamily,
                            onChange: (val) => setAttributes({ showFontFamily: val })
                        })
                    )
                ),
                el('div', blockProps,
                    el(ServerSideRender, {
                        block: 'bbk/typography-samples',
                        attributes: attributes
                    })
                )
            );
        },
        save: function() {
            return null;
        }
    });

    // Spacing Scale Block
    registerBlockType('bbk/spacing-scale', {
        title: __('Spacing Scale', 'branding-block-kit'),
        description: __('Display spacing scale from theme.json', 'branding-block-kit'),
        icon: brandIcon,
        category: 'branding-block-kit',
        attributes: {
            title: { type: 'string', default: 'Spacing Scale' },
            showValue: { type: 'boolean', default: true },
            showName: { type: 'boolean', default: true },
            direction: { type: 'string', default: 'horizontal' }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            return el(Fragment, {},
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Settings', 'branding-block-kit') },
                        el(TextControl, {
                            label: __('Title', 'branding-block-kit'),
                            value: attributes.title,
                            onChange: (val) => setAttributes({ title: val })
                        }),
                        el(SelectControl, {
                            label: __('Direction', 'branding-block-kit'),
                            value: attributes.direction,
                            options: [
                                { label: 'Horizontal', value: 'horizontal' },
                                { label: 'Vertical', value: 'vertical' }
                            ],
                            onChange: (val) => setAttributes({ direction: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Values', 'branding-block-kit'),
                            checked: attributes.showValue,
                            onChange: (val) => setAttributes({ showValue: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Names', 'branding-block-kit'),
                            checked: attributes.showName,
                            onChange: (val) => setAttributes({ showName: val })
                        })
                    )
                ),
                el('div', blockProps,
                    el(ServerSideRender, {
                        block: 'bbk/spacing-scale',
                        attributes: attributes
                    })
                )
            );
        },
        save: function() {
            return null;
        }
    });

    // Shadow Showcase Block
    registerBlockType('bbk/shadow-showcase', {
        title: __('Shadow Showcase', 'branding-block-kit'),
        description: __('Display shadow presets from theme.json', 'branding-block-kit'),
        icon: brandIcon,
        category: 'branding-block-kit',
        attributes: {
            title: { type: 'string', default: 'Shadows' },
            showName: { type: 'boolean', default: true },
            showCode: { type: 'boolean', default: false }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            return el(Fragment, {},
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Settings', 'branding-block-kit') },
                        el(TextControl, {
                            label: __('Title', 'branding-block-kit'),
                            value: attributes.title,
                            onChange: (val) => setAttributes({ title: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Names', 'branding-block-kit'),
                            checked: attributes.showName,
                            onChange: (val) => setAttributes({ showName: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show CSS Code', 'branding-block-kit'),
                            checked: attributes.showCode,
                            onChange: (val) => setAttributes({ showCode: val })
                        })
                    )
                ),
                el('div', blockProps,
                    el(ServerSideRender, {
                        block: 'bbk/shadow-showcase',
                        attributes: attributes
                    })
                )
            );
        },
        save: function() {
            return null;
        }
    });

    // Border Radius Block
    registerBlockType('bbk/border-radius', {
        title: __('Border Radius', 'branding-block-kit'),
        description: __('Display border radius values from theme.json', 'branding-block-kit'),
        icon: brandIcon,
        category: 'branding-block-kit',
        attributes: {
            title: { type: 'string', default: 'Border Radius' },
            showValue: { type: 'boolean', default: true }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            return el(Fragment, {},
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Settings', 'branding-block-kit') },
                        el(TextControl, {
                            label: __('Title', 'branding-block-kit'),
                            value: attributes.title,
                            onChange: (val) => setAttributes({ title: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Values', 'branding-block-kit'),
                            checked: attributes.showValue,
                            onChange: (val) => setAttributes({ showValue: val })
                        })
                    )
                ),
                el('div', blockProps,
                    el(ServerSideRender, {
                        block: 'bbk/border-radius',
                        attributes: attributes
                    })
                )
            );
        },
        save: function() {
            return null;
        }
    });

    // Custom Properties Block
    registerBlockType('bbk/custom-properties', {
        title: __('Custom Properties', 'branding-block-kit'),
        description: __('Display custom properties from theme.json', 'branding-block-kit'),
        icon: brandIcon,
        category: 'branding-block-kit',
        attributes: {
            title: { type: 'string', default: 'Custom Properties' },
            section: { type: 'string', default: '' }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            return el(Fragment, {},
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Settings', 'branding-block-kit') },
                        el(TextControl, {
                            label: __('Title', 'branding-block-kit'),
                            value: attributes.title,
                            onChange: (val) => setAttributes({ title: val })
                        }),
                        el(TextControl, {
                            label: __('Section Key', 'branding-block-kit'),
                            value: attributes.section,
                            onChange: (val) => setAttributes({ section: val }),
                            help: __('e.g., "sidebar", "glass", "borderRadius". Leave empty for all.', 'branding-block-kit')
                        })
                    )
                ),
                el('div', blockProps,
                    el(ServerSideRender, {
                        block: 'bbk/custom-properties',
                        attributes: attributes
                    })
                )
            );
        },
        save: function() {
            return null;
        }
    });

    // Logo Showcase Block
    registerBlockType('bbk/logo-showcase', {
        title: __('Logo Showcase', 'branding-block-kit'),
        description: __('Display logo variations with download options', 'branding-block-kit'),
        icon: brandIcon,
        category: 'branding-block-kit',
        attributes: {
            title: { type: 'string', default: 'Logo' },
            layout: { type: 'string', default: 'grid' },
            columns: { type: 'number', default: 2 },
            cardStyle: { type: 'string', default: 'card' },
            showLabels: { type: 'boolean', default: true },
            showDownload: { type: 'boolean', default: true },
            logoPrimaryId: { type: 'number', default: 0 },
            logoPrimaryUrl: { type: 'string', default: '' },
            logoPrimaryLabel: { type: 'string', default: 'Primary Logo' },
            logoSecondaryId: { type: 'number', default: 0 },
            logoSecondaryUrl: { type: 'string', default: '' },
            logoSecondaryLabel: { type: 'string', default: 'Secondary Logo' },
            logoDarkId: { type: 'number', default: 0 },
            logoDarkUrl: { type: 'string', default: '' },
            logoDarkLabel: { type: 'string', default: 'Logo (Dark Background)' },
            logoLightId: { type: 'number', default: 0 },
            logoLightUrl: { type: 'string', default: '' },
            logoLightLabel: { type: 'string', default: 'Logo (Light Background)' },
            logoIconId: { type: 'number', default: 0 },
            logoIconUrl: { type: 'string', default: '' },
            logoIconLabel: { type: 'string', default: 'Icon / Mark' },
            logoMonoId: { type: 'number', default: 0 },
            logoMonoUrl: { type: 'string', default: '' },
            logoMonoLabel: { type: 'string', default: 'Monochrome Logo' }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();
            const { MediaUpload, MediaUploadCheck } = wp.blockEditor;
            const { Button } = wp.components;

            // Helper to create media upload control
            const LogoUpload = ({ label, idAttr, urlAttr, labelAttr }) => {
                const id = attributes[idAttr];
                const url = attributes[urlAttr];
                const logoLabel = attributes[labelAttr];

                return el('div', { className: 'bbk-logo-upload-control', style: { marginBottom: '16px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px' } },
                    el('div', { style: { fontWeight: '600', marginBottom: '8px' } }, label),
                    el(TextControl, {
                        label: __('Label', 'branding-block-kit'),
                        value: logoLabel,
                        onChange: (val) => setAttributes({ [labelAttr]: val })
                    }),
                    el(MediaUploadCheck, {},
                        el(MediaUpload, {
                            onSelect: (media) => setAttributes({ [idAttr]: media.id, [urlAttr]: media.url }),
                            allowedTypes: ['image'],
                            value: id,
                            render: ({ open }) => el('div', {},
                                url ? el('div', { style: { marginBottom: '8px' } },
                                    el('img', { src: url, alt: logoLabel, style: { maxWidth: '100%', maxHeight: '80px', objectFit: 'contain', background: '#f0f0f0', padding: '8px', borderRadius: '4px' } })
                                ) : null,
                                el(Button, { onClick: open, variant: 'secondary', style: { marginRight: '8px' } }, url ? __('Replace', 'branding-block-kit') : __('Upload Logo', 'branding-block-kit')),
                                url ? el(Button, { onClick: () => setAttributes({ [idAttr]: 0, [urlAttr]: '' }), isDestructive: true, variant: 'tertiary' }, __('Remove', 'branding-block-kit')) : null
                            )
                        })
                    )
                );
            };

            return el(Fragment, {},
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Layout', 'branding-block-kit') },
                        el(SelectControl, {
                            label: __('Layout Style', 'branding-block-kit'),
                            value: attributes.layout,
                            options: [
                                { label: 'Grid', value: 'grid' },
                                { label: 'List', value: 'list' }
                            ],
                            onChange: (val) => setAttributes({ layout: val })
                        }),
                        el(RangeControl, {
                            label: __('Columns', 'branding-block-kit'),
                            value: attributes.columns,
                            onChange: (val) => setAttributes({ columns: val }),
                            min: 1,
                            max: 4
                        }),
                        el(SelectControl, {
                            label: __('Card Style', 'branding-block-kit'),
                            value: attributes.cardStyle,
                            options: [
                                { label: 'Card', value: 'card' },
                                { label: 'Minimal', value: 'minimal' },
                                { label: 'Bordered', value: 'bordered' }
                            ],
                            onChange: (val) => setAttributes({ cardStyle: val })
                        })
                    ),
                    el(PanelBody, { title: __('Display Options', 'branding-block-kit'), initialOpen: false },
                        el(TextControl, {
                            label: __('Title', 'branding-block-kit'),
                            value: attributes.title,
                            onChange: (val) => setAttributes({ title: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Labels', 'branding-block-kit'),
                            checked: attributes.showLabels,
                            onChange: (val) => setAttributes({ showLabels: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Download Button', 'branding-block-kit'),
                            checked: attributes.showDownload,
                            onChange: (val) => setAttributes({ showDownload: val })
                        })
                    ),
                    el(PanelBody, { title: __('Primary Logos', 'branding-block-kit'), initialOpen: true },
                        LogoUpload({ label: 'Primary Logo', idAttr: 'logoPrimaryId', urlAttr: 'logoPrimaryUrl', labelAttr: 'logoPrimaryLabel' }),
                        LogoUpload({ label: 'Secondary Logo', idAttr: 'logoSecondaryId', urlAttr: 'logoSecondaryUrl', labelAttr: 'logoSecondaryLabel' })
                    ),
                    el(PanelBody, { title: __('Background Variations', 'branding-block-kit'), initialOpen: false },
                        LogoUpload({ label: 'Logo (Dark Background)', idAttr: 'logoDarkId', urlAttr: 'logoDarkUrl', labelAttr: 'logoDarkLabel' }),
                        LogoUpload({ label: 'Logo (Light Background)', idAttr: 'logoLightId', urlAttr: 'logoLightUrl', labelAttr: 'logoLightLabel' })
                    ),
                    el(PanelBody, { title: __('Additional Variations', 'branding-block-kit'), initialOpen: false },
                        LogoUpload({ label: 'Icon / Mark', idAttr: 'logoIconId', urlAttr: 'logoIconUrl', labelAttr: 'logoIconLabel' }),
                        LogoUpload({ label: 'Monochrome Logo', idAttr: 'logoMonoId', urlAttr: 'logoMonoUrl', labelAttr: 'logoMonoLabel' })
                    )
                ),
                el('div', blockProps,
                    el(ServerSideRender, {
                        block: 'bbk/logo-showcase',
                        attributes: attributes
                    })
                )
            );
        },
        save: function() {
            return null;
        }
    });

    // Full Style Guide Block
    registerBlockType('bbk/style-guide', {
        title: __('Full Style Guide', 'branding-block-kit'),
        description: __('Complete brand style guide from theme.json', 'branding-block-kit'),
        icon: brandIcon,
        category: 'branding-block-kit',
        attributes: {
            title: { type: 'string', default: 'Brand Style Guide' },
            showColors: { type: 'boolean', default: true },
            showGradients: { type: 'boolean', default: true },
            showTypography: { type: 'boolean', default: true },
            showSpacing: { type: 'boolean', default: true },
            showCustom: { type: 'boolean', default: false }
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            return el(Fragment, {},
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Settings', 'branding-block-kit') },
                        el(TextControl, {
                            label: __('Title', 'branding-block-kit'),
                            value: attributes.title,
                            onChange: (val) => setAttributes({ title: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Colors', 'branding-block-kit'),
                            checked: attributes.showColors,
                            onChange: (val) => setAttributes({ showColors: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Gradients', 'branding-block-kit'),
                            checked: attributes.showGradients,
                            onChange: (val) => setAttributes({ showGradients: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Typography', 'branding-block-kit'),
                            checked: attributes.showTypography,
                            onChange: (val) => setAttributes({ showTypography: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Spacing', 'branding-block-kit'),
                            checked: attributes.showSpacing,
                            onChange: (val) => setAttributes({ showSpacing: val })
                        }),
                        el(ToggleControl, {
                            label: __('Show Custom Properties', 'branding-block-kit'),
                            checked: attributes.showCustom,
                            onChange: (val) => setAttributes({ showCustom: val })
                        })
                    )
                ),
                el('div', blockProps,
                    el(ServerSideRender, {
                        block: 'bbk/style-guide',
                        attributes: attributes
                    })
                )
            );
        },
        save: function() {
            return null;
        }
    });

})(window.wp);
