<?php
/**
 * Plugin Name: Branding Block Kit
 * Plugin URI: https://github.com/derintolu/branding-block-kit
 * Description: Visual blocks that automatically display your theme.json design tokens - colors, gradients, typography, spacing, and more. Build comprehensive brand style guides from your theme's configuration.
 * Version: 1.1.0
 * Author: Derin Tolu
 * Author URI: https://derintolu.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: branding-block-kit
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package Branding_Block_Kit
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BRANDING_BLOCK_KIT_VERSION', '1.0.0' );
define( 'BRANDING_BLOCK_KIT_DIR', plugin_dir_path( __FILE__ ) );
define( 'BRANDING_BLOCK_KIT_URL', plugin_dir_url( __FILE__ ) );

// Include the helper class for reading theme.json.
require_once BRANDING_BLOCK_KIT_DIR . 'includes/class-theme-json-reader.php';

/**
 * Main plugin class.
 */
final class Branding_Block_Kit {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'register_blocks' ) );
        add_filter( 'block_categories_all', array( $this, 'register_block_category' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
        add_action( 'enqueue_block_assets', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Enqueue editor assets.
     */
    public function enqueue_editor_assets() {
        wp_enqueue_script(
            'branding-block-kit-editor',
            BRANDING_BLOCK_KIT_URL . 'build/index.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-block-editor', 'wp-server-side-render' ),
            BRANDING_BLOCK_KIT_VERSION,
            true
        );
    }

    /**
     * Register block category.
     */
    public function register_block_category( $categories ) {
        return array_merge(
            array(
                array(
                    'slug'  => 'branding-block-kit',
                    'title' => __( 'Brand Style Guide', 'branding-block-kit' ),
                    'icon'  => 'art',
                ),
            ),
            $categories
        );
    }

    /**
     * Register all blocks.
     */
    public function register_blocks() {
        // Color Palette Block
        register_block_type( 'bbk/color-palette', array(
            'render_callback' => array( $this, 'render_color_palette' ),
            'attributes'      => array(
                'title'       => array( 'type' => 'string', 'default' => 'Color Palette' ),
                'showHex'     => array( 'type' => 'boolean', 'default' => true ),
                'showName'    => array( 'type' => 'boolean', 'default' => true ),
                'showSlug'    => array( 'type' => 'boolean', 'default' => false ),
                'columns'     => array( 'type' => 'number', 'default' => 4 ),
                'swatchStyle' => array( 'type' => 'string', 'default' => 'chip' ), // card, circle, square, pill, stripe, minimal, large-card, chip, row
                'layout'      => array( 'type' => 'string', 'default' => 'row' ), // grid, list, masonry, inline, row
                'swatchSize'  => array( 'type' => 'string', 'default' => 'medium' ), // small, medium, large
                'groupLabel'  => array( 'type' => 'string', 'default' => '' ), // Optional label above the palette
                'filterSlugs' => array( 'type' => 'string', 'default' => '' ), // comma-separated slugs to include
            ),
        ) );

        // Gradient Showcase Block
        register_block_type( 'bbk/gradient-showcase', array(
            'render_callback' => array( $this, 'render_gradient_showcase' ),
            'attributes'      => array(
                'title'       => array( 'type' => 'string', 'default' => 'Gradients' ),
                'showName'    => array( 'type' => 'boolean', 'default' => true ),
                'showCode'    => array( 'type' => 'boolean', 'default' => true ),
                'layout'      => array( 'type' => 'string', 'default' => 'grid' ), // grid, row, stack, list, inline
                'columns'     => array( 'type' => 'number', 'default' => 3 ),
                'swatchStyle' => array( 'type' => 'string', 'default' => 'card' ), // card, large-card, bar, square, circle, pill, chip, minimal, brand-chips, brand-squares, brand-bars, brand-cards
                'swatchSize'  => array( 'type' => 'string', 'default' => 'medium' ), // small, medium, large
            ),
        ) );

        // Typography Samples Block
        register_block_type( 'bbk/typography-samples', array(
            'render_callback' => array( $this, 'render_typography_samples' ),
            'attributes'      => array(
                'title'        => array( 'type' => 'string', 'default' => 'Typography' ),
                'sampleText'   => array( 'type' => 'string', 'default' => 'The quick brown fox jumps over the lazy dog' ),
                'showFontSize' => array( 'type' => 'boolean', 'default' => true ),
                'showFontFamily' => array( 'type' => 'boolean', 'default' => true ),
                'display'      => array( 'type' => 'string', 'default' => 'all' ), // all, sizes, families
                'layout'       => array( 'type' => 'string', 'default' => 'stack' ), // stack, grid, list, cards
                'columns'      => array( 'type' => 'number', 'default' => 2 ),
                'cardStyle'    => array( 'type' => 'string', 'default' => 'card' ), // card, minimal, bordered, brand-card, brand-minimal
                'textAlign'    => array( 'type' => 'string', 'default' => 'left' ), // left, right
            ),
        ) );

        // Spacing Scale Block
        register_block_type( 'bbk/spacing-scale', array(
            'render_callback' => array( $this, 'render_spacing_scale' ),
            'attributes'      => array(
                'title'     => array( 'type' => 'string', 'default' => 'Spacing Scale' ),
                'showValue' => array( 'type' => 'boolean', 'default' => true ),
                'showName'  => array( 'type' => 'boolean', 'default' => true ),
                'direction' => array( 'type' => 'string', 'default' => 'horizontal' ), // horizontal, vertical
            ),
        ) );

        // Shadow Showcase Block
        register_block_type( 'bbk/shadow-showcase', array(
            'render_callback' => array( $this, 'render_shadow_showcase' ),
            'attributes'      => array(
                'title'    => array( 'type' => 'string', 'default' => 'Shadows' ),
                'showName' => array( 'type' => 'boolean', 'default' => true ),
                'showCode' => array( 'type' => 'boolean', 'default' => false ),
            ),
        ) );

        // Border Radius Block
        register_block_type( 'bbk/border-radius', array(
            'render_callback' => array( $this, 'render_border_radius' ),
            'attributes'      => array(
                'title'     => array( 'type' => 'string', 'default' => 'Border Radius' ),
                'showValue' => array( 'type' => 'boolean', 'default' => true ),
            ),
        ) );

        // Custom Properties Block (displays theme.json custom section)
        register_block_type( 'bbk/custom-properties', array(
            'render_callback' => array( $this, 'render_custom_properties' ),
            'attributes'      => array(
                'title'   => array( 'type' => 'string', 'default' => 'Custom Properties' ),
                'section' => array( 'type' => 'string', 'default' => '' ), // empty = all, or specific key
            ),
        ) );

        // Logo Showcase Block
        register_block_type( 'bbk/logo-showcase', array(
            'render_callback' => array( $this, 'render_logo_showcase' ),
            'attributes'      => array(
                'title'            => array( 'type' => 'string', 'default' => 'Logo' ),
                'layout'           => array( 'type' => 'string', 'default' => 'grid' ), // grid, list, tabs
                'columns'          => array( 'type' => 'number', 'default' => 2 ),
                'cardStyle'        => array( 'type' => 'string', 'default' => 'card' ), // card, minimal, bordered
                'showLabels'       => array( 'type' => 'boolean', 'default' => true ),
                'showDownload'     => array( 'type' => 'boolean', 'default' => true ),
                // Logo variations - each stores media ID and URL
                'logoPrimaryId'    => array( 'type' => 'number', 'default' => 0 ),
                'logoPrimaryUrl'   => array( 'type' => 'string', 'default' => '' ),
                'logoPrimaryLabel' => array( 'type' => 'string', 'default' => 'Primary Logo' ),
                'logoSecondaryId'    => array( 'type' => 'number', 'default' => 0 ),
                'logoSecondaryUrl'   => array( 'type' => 'string', 'default' => '' ),
                'logoSecondaryLabel' => array( 'type' => 'string', 'default' => 'Secondary Logo' ),
                'logoDarkId'       => array( 'type' => 'number', 'default' => 0 ),
                'logoDarkUrl'      => array( 'type' => 'string', 'default' => '' ),
                'logoDarkLabel'    => array( 'type' => 'string', 'default' => 'Logo (Dark Background)' ),
                'logoLightId'      => array( 'type' => 'number', 'default' => 0 ),
                'logoLightUrl'     => array( 'type' => 'string', 'default' => '' ),
                'logoLightLabel'   => array( 'type' => 'string', 'default' => 'Logo (Light Background)' ),
                'logoIconId'       => array( 'type' => 'number', 'default' => 0 ),
                'logoIconUrl'      => array( 'type' => 'string', 'default' => '' ),
                'logoIconLabel'    => array( 'type' => 'string', 'default' => 'Icon / Mark' ),
                'logoMonoId'       => array( 'type' => 'number', 'default' => 0 ),
                'logoMonoUrl'      => array( 'type' => 'string', 'default' => '' ),
                'logoMonoLabel'    => array( 'type' => 'string', 'default' => 'Monochrome Logo' ),
            ),
        ) );

        // Full Style Guide Block (combines all)
        register_block_type( 'bbk/style-guide', array(
            'render_callback' => array( $this, 'render_full_style_guide' ),
            'attributes'      => array(
                'title'         => array( 'type' => 'string', 'default' => 'Brand Style Guide' ),
                'showColors'    => array( 'type' => 'boolean', 'default' => true ),
                'showGradients' => array( 'type' => 'boolean', 'default' => true ),
                'showTypography' => array( 'type' => 'boolean', 'default' => true ),
                'showSpacing'   => array( 'type' => 'boolean', 'default' => true ),
                'showCustom'    => array( 'type' => 'boolean', 'default' => false ),
            ),
        ) );
    }

    /**
     * Enqueue block assets.
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'branding-block-kit',
            BRANDING_BLOCK_KIT_URL . 'assets/css/blocks.css',
            array(),
            BRANDING_BLOCK_KIT_VERSION
        );

        // Frontend click-to-copy script (only on frontend, not in editor)
        if ( ! is_admin() ) {
            wp_enqueue_script(
                'branding-block-kit-frontend',
                BRANDING_BLOCK_KIT_URL . 'assets/js/frontend.js',
                array(),
                BRANDING_BLOCK_KIT_VERSION,
                true
            );
        }
    }

    /**
     * Render Color Palette block.
     */
    public function render_color_palette( $attributes ) {
        $reader = BBK_Theme_JSON_Reader::instance();
        $colors = $reader->get_colors();

        if ( empty( $colors ) ) {
            return '<p class="bbk-brand-empty">' . esc_html__( 'No colors defined in theme.json', 'branding-block-kit' ) . '</p>';
        }

        // Filter colors if filterSlugs is set
        if ( ! empty( $attributes['filterSlugs'] ) ) {
            $filter_slugs = array_map( 'trim', explode( ',', $attributes['filterSlugs'] ) );
            $colors = array_filter( $colors, function( $color ) use ( $filter_slugs ) {
                return in_array( $color['slug'], $filter_slugs, true );
            } );
        }

        $style      = $attributes['swatchStyle'] ?? 'card';
        $layout     = $attributes['layout'] ?? 'grid';
        $size       = $attributes['swatchSize'] ?? 'medium';
        $columns    = absint( $attributes['columns'] ?? 4 );
        $groupLabel = $attributes['groupLabel'] ?? '';

        // Brand styles that use overlay for hover text reveal
        $overlay_styles = array( 'chip', 'row', 'brand-chips', 'brand-squares', 'brand-bars', 'brand-cards', 'grid-expand' );
        
        // Brand styles need their own layout class
        $is_brand_style = in_array( $style, array( 'brand-chips', 'brand-squares', 'brand-bars', 'brand-cards', 'grid-expand' ), true );

        // Build CSS classes
        $grid_classes = array(
            'bbk-brand-color-grid',
            'bbk-brand-color-grid--' . $style,
        );
        
        // For brand styles, use the style as the layout; otherwise use the layout attribute
        if ( $is_brand_style ) {
            $grid_classes[] = 'bbk-brand-color-grid--layout-' . $style;
        } else {
            $grid_classes[] = 'bbk-brand-color-grid--layout-' . $layout;
        }
        
        $grid_classes[] = 'bbk-brand-color-grid--size-' . $size;

        ob_start();
        ?>
        <div class="bbk-brand-block bbk-brand-color-palette">
            <?php if ( ! empty( $attributes['title'] ) ) : ?>
                <h3 class="bbk-brand-block__title"><?php echo esc_html( $attributes['title'] ); ?></h3>
            <?php endif; ?>

            <?php if ( ! empty( $groupLabel ) ) : ?>
                <div class="bbk-brand-color-label"><?php echo esc_html( $groupLabel ); ?></div>
            <?php endif; ?>
            
            <div class="<?php echo esc_attr( implode( ' ', $grid_classes ) ); ?>" style="--bbk-columns: <?php echo esc_attr( $columns ); ?>">
                <?php foreach ( $colors as $color ) : ?>
                    <div class="bbk-brand-color-swatch bbk-brand-color-swatch--<?php echo esc_attr( $style ); ?> bbk-brand-color-swatch--size-<?php echo esc_attr( $size ); ?>" data-color="<?php echo esc_attr( $color['color'] ); ?>" title="<?php echo esc_attr( sprintf( __( 'Click to copy %s', 'branding-block-kit' ), $color['color'] ) ); ?>">
                        <div class="bbk-brand-color-swatch__color" style="background-color: <?php echo esc_attr( $color['color'] ); ?>">
                            <?php if ( in_array( $style, $overlay_styles, true ) ) : ?>
                                <div class="bbk-brand-color-swatch__overlay">
                                    <?php if ( $attributes['showName'] ) : ?>
                                        <span class="bbk-brand-color-swatch__name"><?php echo esc_html( $color['name'] ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( $attributes['showHex'] ) : ?>
                                        <span class="bbk-brand-color-swatch__hex"><?php echo esc_html( $color['color'] ); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ( $attributes['showHex'] && in_array( $style, array( 'card', 'large-card' ), true ) ) : ?>
                                <span class="bbk-brand-color-swatch__hex"><?php echo esc_html( $color['color'] ); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php 
                        // Show info section for non-overlay styles OR for brand-cards (which has both overlay and info)
                        $show_info = ! in_array( $style, array( 'chip', 'row', 'brand-chips', 'brand-squares', 'brand-bars' ), true ) 
                                     && ( $attributes['showName'] || $attributes['showSlug'] || ( $attributes['showHex'] && ! in_array( $style, array( 'card', 'large-card', 'minimal' ), true ) ) );
                        
                        // Brand cards always show info section
                        if ( $style === 'brand-cards' ) {
                            $show_info = true;
                        }
                        
                        if ( $show_info ) : 
                        ?>
                            <div class="bbk-brand-color-swatch__info">
                                <?php if ( $attributes['showName'] ) : ?>
                                    <span class="bbk-brand-color-swatch__name"><?php echo esc_html( $color['name'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( $attributes['showSlug'] ) : ?>
                                    <code class="bbk-brand-color-swatch__slug">--wp--preset--color--<?php echo esc_html( $color['slug'] ); ?></code>
                                <?php endif; ?>
                                <?php if ( $attributes['showHex'] && ! in_array( $style, array( 'card', 'large-card', 'minimal', 'brand-cards' ), true ) ) : ?>
                                    <span class="bbk-brand-color-swatch__hex-below"><?php echo esc_html( $color['color'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( $attributes['showHex'] && $style === 'brand-cards' ) : ?>
                                    <span class="bbk-brand-color-swatch__hex"><?php echo esc_html( $color['color'] ); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Gradient Showcase block.
     */
    public function render_gradient_showcase( $attributes ) {
        $reader = BBK_Theme_JSON_Reader::instance();
        $gradients = $reader->get_gradients();

        if ( empty( $gradients ) ) {
            return '<p class="bbk-brand-empty">' . esc_html__( 'No gradients defined in theme.json', 'branding-block-kit' ) . '</p>';
        }

        $layout  = $attributes['layout'] ?? 'grid';
        $style   = $attributes['swatchStyle'] ?? 'card';
        $size    = $attributes['swatchSize'] ?? 'medium';
        $columns = absint( $attributes['columns'] ?? 3 );

        // Styles that use overlay for hover text reveal (all styles now)
        $overlay_styles = array( 'chip', 'card', 'large-card', 'bar', 'square', 'circle', 'pill', 'minimal', 'brand-chips', 'brand-squares', 'brand-bars', 'brand-cards', 'grid-expand' );
        
        // Brand styles need their own layout class
        $is_brand_style = in_array( $style, array( 'brand-chips', 'brand-squares', 'brand-bars', 'brand-cards', 'grid-expand' ), true );

        $grid_classes = array(
            'bbk-brand-gradient-grid',
            'bbk-brand-gradient-grid--' . $style,
        );
        
        // For brand styles, use the style as the layout; otherwise use the layout attribute
        if ( $is_brand_style ) {
            $grid_classes[] = 'bbk-brand-gradient-grid--layout-' . $style;
        } else {
            $grid_classes[] = 'bbk-brand-gradient-grid--layout-' . $layout;
        }
        
        $grid_classes[] = 'bbk-brand-gradient-grid--size-' . $size;

        ob_start();
        ?>
        <div class="bbk-brand-block bbk-brand-gradient-showcase">
            <?php if ( ! empty( $attributes['title'] ) ) : ?>
                <h3 class="bbk-brand-block__title"><?php echo esc_html( $attributes['title'] ); ?></h3>
            <?php endif; ?>
            
            <div class="<?php echo esc_attr( implode( ' ', $grid_classes ) ); ?>" style="--bbk-columns: <?php echo esc_attr( $columns ); ?>">
                <?php foreach ( $gradients as $gradient ) : ?>
                    <div class="bbk-brand-gradient-swatch bbk-brand-gradient-swatch--<?php echo esc_attr( $style ); ?> bbk-brand-gradient-swatch--size-<?php echo esc_attr( $size ); ?>" data-gradient="<?php echo esc_attr( $gradient['gradient'] ); ?>" title="<?php echo esc_attr( sprintf( __( 'Click to copy %s', 'branding-block-kit' ), $gradient['name'] ) ); ?>">
                        <div class="bbk-brand-gradient-swatch__preview" style="background: <?php echo esc_attr( $gradient['gradient'] ); ?>">
                            <?php if ( in_array( $style, $overlay_styles, true ) ) : ?>
                                <div class="bbk-brand-gradient-swatch__overlay">
                                    <?php if ( $attributes['showName'] ) : ?>
                                        <span class="bbk-brand-gradient-swatch__name"><?php echo esc_html( $gradient['name'] ); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php 
                        // Show info section for non-overlay styles
                        $show_info = ! in_array( $style, array( 'chip', 'brand-chips', 'brand-squares', 'brand-bars' ), true ) 
                                     && ( $attributes['showName'] || $attributes['showCode'] );
                        
                        // Brand cards always show info section
                        if ( $style === 'brand-cards' ) {
                            $show_info = true;
                        }
                        
                        if ( $show_info ) : 
                        ?>
                            <div class="bbk-brand-gradient-swatch__info">
                                <?php if ( $attributes['showName'] ) : ?>
                                    <span class="bbk-brand-gradient-swatch__name"><?php echo esc_html( $gradient['name'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( $attributes['showCode'] ) : ?>
                                    <code class="bbk-brand-gradient-swatch__code"><?php echo esc_html( $gradient['gradient'] ); ?></code>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Typography Samples block.
     */
    public function render_typography_samples( $attributes ) {
        $reader = BBK_Theme_JSON_Reader::instance();
        $font_sizes = $reader->get_font_sizes();
        $font_families = $reader->get_font_families();

        $sample_text = $attributes['sampleText'];
        $display     = $attributes['display'];
        $layout      = $attributes['layout'] ?? 'stack';
        $columns     = absint( $attributes['columns'] ?? 2 );
        $card_style  = $attributes['cardStyle'] ?? 'card';
        $text_align  = $attributes['textAlign'] ?? 'left';

        $block_classes = array(
            'bbk-brand-block',
            'bbk-brand-typography-samples',
            'bbk-brand-typography-samples--layout-' . $layout,
            'bbk-brand-typography-samples--style-' . $card_style,
            'bbk-brand-typography-samples--align-' . $text_align,
        );

        ob_start();
        ?>
        <div class="<?php echo esc_attr( implode( ' ', $block_classes ) ); ?>" style="--bbk-columns: <?php echo esc_attr( $columns ); ?>">
            <?php if ( ! empty( $attributes['title'] ) ) : ?>
                <h3 class="bbk-brand-block__title"><?php echo esc_html( $attributes['title'] ); ?></h3>
            <?php endif; ?>

            <?php if ( ( $display === 'all' || $display === 'families' ) && ! empty( $font_families ) ) : ?>
                <div class="bbk-brand-typography-section bbk-brand-typography-section--families">
                    <h4 class="bbk-brand-typography-section__title"><?php esc_html_e( 'Font Families', 'branding-block-kit' ); ?></h4>
                    <div class="bbk-brand-typography-grid">
                        <?php foreach ( $font_families as $family ) : ?>
                            <div class="bbk-brand-font-family bbk-brand-font-family--<?php echo esc_attr( $card_style ); ?>">
                                <div class="bbk-brand-font-family__sample" style="font-family: <?php echo esc_attr( $family['fontFamily'] ); ?>">
                                    <?php echo esc_html( $sample_text ); ?>
                                </div>
                                <div class="bbk-brand-font-family__info">
                                    <span class="bbk-brand-font-family__name"><?php echo esc_html( $family['name'] ); ?></span>
                                    <?php if ( $attributes['showFontFamily'] ) : ?>
                                        <code class="bbk-brand-font-family__value"><?php echo esc_html( $family['fontFamily'] ); ?></code>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ( $display === 'all' || $display === 'sizes' ) && ! empty( $font_sizes ) ) : ?>
                <div class="bbk-brand-typography-section bbk-brand-typography-section--sizes">
                    <h4 class="bbk-brand-typography-section__title"><?php esc_html_e( 'Font Sizes', 'branding-block-kit' ); ?></h4>
                    <div class="bbk-brand-typography-grid">
                        <?php foreach ( $font_sizes as $size ) : ?>
                            <div class="bbk-brand-font-size bbk-brand-font-size--<?php echo esc_attr( $card_style ); ?>">
                                <div class="bbk-brand-font-size__sample" style="font-size: <?php echo esc_attr( $size['size'] ); ?>">
                                    <?php echo esc_html( $sample_text ); ?>
                                </div>
                                <div class="bbk-brand-font-size__info">
                                    <span class="bbk-brand-font-size__name"><?php echo esc_html( $size['name'] ); ?></span>
                                    <?php if ( $attributes['showFontSize'] ) : ?>
                                        <code class="bbk-brand-font-size__value"><?php echo esc_html( $size['size'] ); ?></code>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Spacing Scale block.
     */
    public function render_spacing_scale( $attributes ) {
        $reader = BBK_Theme_JSON_Reader::instance();
        $spacing = $reader->get_spacing_sizes();

        if ( empty( $spacing ) ) {
            return '<p class="bbk-brand-empty">' . esc_html__( 'No spacing sizes defined in theme.json', 'branding-block-kit' ) . '</p>';
        }

        $direction = $attributes['direction'];

        ob_start();
        ?>
        <div class="bbk-brand-block bbk-brand-spacing-scale">
            <?php if ( ! empty( $attributes['title'] ) ) : ?>
                <h3 class="bbk-brand-block__title"><?php echo esc_html( $attributes['title'] ); ?></h3>
            <?php endif; ?>
            
            <div class="bbk-brand-spacing-list bbk-brand-spacing-list--<?php echo esc_attr( $direction ); ?>">
                <?php foreach ( $spacing as $space ) : ?>
                    <div class="bbk-brand-spacing-item">
                        <div class="bbk-brand-spacing-item__visual">
                            <div class="bbk-brand-spacing-item__bar" style="<?php echo $direction === 'horizontal' ? 'width' : 'height'; ?>: <?php echo esc_attr( $space['size'] ); ?>"></div>
                        </div>
                        <div class="bbk-brand-spacing-item__info">
                            <?php if ( $attributes['showName'] ) : ?>
                                <span class="bbk-brand-spacing-item__name"><?php echo esc_html( $space['name'] ); ?></span>
                            <?php endif; ?>
                            <?php if ( $attributes['showValue'] ) : ?>
                                <code class="bbk-brand-spacing-item__value"><?php echo esc_html( $space['size'] ); ?></code>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Shadow Showcase block.
     */
    public function render_shadow_showcase( $attributes ) {
        $reader = BBK_Theme_JSON_Reader::instance();
        $shadows = $reader->get_shadows();

        if ( empty( $shadows ) ) {
            return '<p class="bbk-brand-empty">' . esc_html__( 'No shadows defined in theme.json', 'branding-block-kit' ) . '</p>';
        }

        ob_start();
        ?>
        <div class="bbk-brand-block bbk-brand-shadow-showcase">
            <?php if ( ! empty( $attributes['title'] ) ) : ?>
                <h3 class="bbk-brand-block__title"><?php echo esc_html( $attributes['title'] ); ?></h3>
            <?php endif; ?>
            
            <div class="bbk-brand-shadow-grid">
                <?php foreach ( $shadows as $shadow ) : ?>
                    <div class="bbk-brand-shadow-item">
                        <div class="bbk-brand-shadow-item__preview" style="box-shadow: <?php echo esc_attr( $shadow['shadow'] ); ?>"></div>
                        <div class="bbk-brand-shadow-item__info">
                            <?php if ( $attributes['showName'] ) : ?>
                                <span class="bbk-brand-shadow-item__name"><?php echo esc_html( $shadow['name'] ); ?></span>
                            <?php endif; ?>
                            <?php if ( $attributes['showCode'] ) : ?>
                                <code class="bbk-brand-shadow-item__code"><?php echo esc_html( $shadow['shadow'] ); ?></code>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Border Radius block.
     */
    public function render_border_radius( $attributes ) {
        $reader = BBK_Theme_JSON_Reader::instance();
        $radii = $reader->get_border_radius();

        if ( empty( $radii ) ) {
            return '<p class="bbk-brand-empty">' . esc_html__( 'No border radius values defined in theme.json custom section', 'branding-block-kit' ) . '</p>';
        }

        ob_start();
        ?>
        <div class="bbk-brand-block bbk-brand-border-radius">
            <?php if ( ! empty( $attributes['title'] ) ) : ?>
                <h3 class="bbk-brand-block__title"><?php echo esc_html( $attributes['title'] ); ?></h3>
            <?php endif; ?>
            
            <div class="bbk-brand-radius-grid">
                <?php foreach ( $radii as $name => $value ) : ?>
                    <div class="bbk-brand-radius-item">
                        <div class="bbk-brand-radius-item__preview" style="border-radius: <?php echo esc_attr( $value ); ?>"></div>
                        <div class="bbk-brand-radius-item__info">
                            <span class="bbk-brand-radius-item__name"><?php echo esc_html( ucfirst( $name ) ); ?></span>
                            <?php if ( $attributes['showValue'] ) : ?>
                                <code class="bbk-brand-radius-item__value"><?php echo esc_html( $value ); ?></code>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Custom Properties block.
     */
    public function render_custom_properties( $attributes ) {
        $reader = BBK_Theme_JSON_Reader::instance();
        $custom = $reader->get_custom();

        if ( empty( $custom ) ) {
            return '<p class="bbk-brand-empty">' . esc_html__( 'No custom properties defined in theme.json', 'branding-block-kit' ) . '</p>';
        }

        // Filter to specific section if set
        if ( ! empty( $attributes['section'] ) && isset( $custom[ $attributes['section'] ] ) ) {
            $custom = array( $attributes['section'] => $custom[ $attributes['section'] ] );
        }

        ob_start();
        ?>
        <div class="bbk-brand-block bbk-brand-custom-properties">
            <?php if ( ! empty( $attributes['title'] ) ) : ?>
                <h3 class="bbk-brand-block__title"><?php echo esc_html( $attributes['title'] ); ?></h3>
            <?php endif; ?>
            
            <?php foreach ( $custom as $section_name => $section_values ) : ?>
                <div class="bbk-brand-custom-section">
                    <h4 class="bbk-brand-custom-section__title"><?php echo esc_html( ucfirst( str_replace( array( '-', '_' ), ' ', $section_name ) ) ); ?></h4>
                    <div class="bbk-brand-custom-section__items">
                        <?php if ( is_array( $section_values ) ) : ?>
                            <?php foreach ( $section_values as $prop_name => $prop_value ) : ?>
                                <div class="bbk-brand-custom-item">
                                    <span class="bbk-brand-custom-item__name"><?php echo esc_html( $prop_name ); ?></span>
                                    <code class="bbk-brand-custom-item__value"><?php echo esc_html( is_array( $prop_value ) ? json_encode( $prop_value ) : $prop_value ); ?></code>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <code class="bbk-brand-custom-item__value"><?php echo esc_html( $section_values ); ?></code>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Logo Showcase block.
     */
    public function render_logo_showcase( $attributes ) {
        $layout     = $attributes['layout'] ?? 'grid';
        $columns    = absint( $attributes['columns'] ?? 2 );
        $card_style = $attributes['cardStyle'] ?? 'card';
        $show_labels = $attributes['showLabels'] ?? true;
        $show_download = $attributes['showDownload'] ?? true;

        // Collect all logos that have URLs
        $logos = array();
        
        $logo_types = array(
            'Primary'   => array( 'id' => $attributes['logoPrimaryId'] ?? 0, 'url' => $attributes['logoPrimaryUrl'] ?? '', 'label' => $attributes['logoPrimaryLabel'] ?? 'Primary Logo', 'bg' => 'light' ),
            'Secondary' => array( 'id' => $attributes['logoSecondaryId'] ?? 0, 'url' => $attributes['logoSecondaryUrl'] ?? '', 'label' => $attributes['logoSecondaryLabel'] ?? 'Secondary Logo', 'bg' => 'light' ),
            'Dark'      => array( 'id' => $attributes['logoDarkId'] ?? 0, 'url' => $attributes['logoDarkUrl'] ?? '', 'label' => $attributes['logoDarkLabel'] ?? 'Logo (Dark Background)', 'bg' => 'dark' ),
            'Light'     => array( 'id' => $attributes['logoLightId'] ?? 0, 'url' => $attributes['logoLightUrl'] ?? '', 'label' => $attributes['logoLightLabel'] ?? 'Logo (Light Background)', 'bg' => 'light' ),
            'Icon'      => array( 'id' => $attributes['logoIconId'] ?? 0, 'url' => $attributes['logoIconUrl'] ?? '', 'label' => $attributes['logoIconLabel'] ?? 'Icon / Mark', 'bg' => 'light' ),
            'Mono'      => array( 'id' => $attributes['logoMonoId'] ?? 0, 'url' => $attributes['logoMonoUrl'] ?? '', 'label' => $attributes['logoMonoLabel'] ?? 'Monochrome Logo', 'bg' => 'light' ),
        );

        foreach ( $logo_types as $key => $logo ) {
            if ( ! empty( $logo['url'] ) ) {
                $logos[ $key ] = $logo;
            }
        }

        if ( empty( $logos ) ) {
            return '<p class="bbk-brand-empty">' . esc_html__( 'No logos uploaded. Add logos in the block settings.', 'branding-block-kit' ) . '</p>';
        }

        $grid_classes = array(
            'bbk-brand-logo-grid',
            'bbk-brand-logo-grid--layout-' . $layout,
            'bbk-brand-logo-grid--style-' . $card_style,
        );

        ob_start();
        ?>
        <div class="bbk-brand-block bbk-brand-logo-showcase">
            <?php if ( ! empty( $attributes['title'] ) ) : ?>
                <h3 class="bbk-brand-block__title"><?php echo esc_html( $attributes['title'] ); ?></h3>
            <?php endif; ?>

            <div class="<?php echo esc_attr( implode( ' ', $grid_classes ) ); ?>" style="--bbk-columns: <?php echo esc_attr( $columns ); ?>">
                <?php foreach ( $logos as $key => $logo ) : ?>
                    <div class="bbk-brand-logo-box bbk-brand-logo-box--<?php echo esc_attr( $card_style ); ?> bbk-brand-logo-box--bg-<?php echo esc_attr( $logo['bg'] ); ?>">
                        <div class="bbk-brand-logo-box__preview">
                            <img src="<?php echo esc_url( $logo['url'] ); ?>" alt="<?php echo esc_attr( $logo['label'] ); ?>" class="bbk-brand-logo-box__image" />
                        </div>
                        <?php if ( $show_labels || $show_download ) : ?>
                            <div class="bbk-brand-logo-box__info">
                                <?php if ( $show_labels ) : ?>
                                    <span class="bbk-brand-logo-box__label"><?php echo esc_html( $logo['label'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( $show_download && ! empty( $logo['url'] ) ) : ?>
                                    <a href="<?php echo esc_url( $logo['url'] ); ?>" download class="bbk-brand-logo-box__download" title="<?php esc_attr_e( 'Download', 'branding-block-kit' ); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Full Style Guide block.
     */
    public function render_full_style_guide( $attributes ) {
        ob_start();
        ?>
        <div class="bbk-brand-block bbk-brand-style-guide">
            <?php if ( ! empty( $attributes['title'] ) ) : ?>
                <h2 class="bbk-brand-style-guide__title"><?php echo esc_html( $attributes['title'] ); ?></h2>
            <?php endif; ?>

            <?php if ( $attributes['showColors'] ) : ?>
                <?php echo $this->render_color_palette( array(
                    'title'       => __( 'Colors', 'branding-block-kit' ),
                    'showHex'     => true,
                    'showName'    => true,
                    'showSlug'    => false,
                    'columns'     => 4,
                    'swatchStyle' => 'card',
                    'filterSlugs' => '',
                ) ); ?>
            <?php endif; ?>

            <?php if ( $attributes['showGradients'] ) : ?>
                <?php echo $this->render_gradient_showcase( array(
                    'title'    => __( 'Gradients', 'branding-block-kit' ),
                    'showName' => true,
                    'showCode' => true,
                    'layout'   => 'stack',
                ) ); ?>
            <?php endif; ?>

            <?php if ( $attributes['showTypography'] ) : ?>
                <?php echo $this->render_typography_samples( array(
                    'title'        => __( 'Typography', 'branding-block-kit' ),
                    'sampleText'   => 'The quick brown fox jumps over the lazy dog',
                    'showFontSize' => true,
                    'showFontFamily' => true,
                    'display'      => 'all',
                ) ); ?>
            <?php endif; ?>

            <?php if ( $attributes['showSpacing'] ) : ?>
                <?php echo $this->render_spacing_scale( array(
                    'title'     => __( 'Spacing', 'branding-block-kit' ),
                    'showValue' => true,
                    'showName'  => true,
                    'direction' => 'horizontal',
                ) ); ?>
            <?php endif; ?>

            <?php if ( $attributes['showCustom'] ) : ?>
                <?php echo $this->render_custom_properties( array(
                    'title'   => __( 'Custom Properties', 'branding-block-kit' ),
                    'section' => '',
                ) ); ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize plugin.
Branding_Block_Kit::instance();
