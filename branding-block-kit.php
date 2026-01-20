<?php
/**
 * Plugin Name: Branding Block Kit
 * Plugin URI: https://github.com/derintolu/branding-block-kit
 * Description: Visual blocks that automatically display your theme.json design tokens - colors, gradients, typography, spacing, and more. Build comprehensive brand style guides from your theme's configuration.
 * Version: 1.0.0
 * Author: Derin Oluwole
 * Author URI: https://derinoluwole.com
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
                'swatchStyle' => array( 'type' => 'string', 'default' => 'card' ), // card, circle, square, pill, stripe, minimal, large-card, chip, row
                'layout'      => array( 'type' => 'string', 'default' => 'grid' ), // grid, list, masonry, inline, row
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
                'layout'      => array( 'type' => 'string', 'default' => 'stack' ), // stack, grid, cards
                'columns'     => array( 'type' => 'number', 'default' => 2 ),
                'swatchStyle' => array( 'type' => 'string', 'default' => 'bar' ), // bar, square, circle, card
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

        // Build CSS classes
        $grid_classes = array(
            'bbk-brand-color-grid',
            'bbk-brand-color-grid--' . $style,
            'bbk-brand-color-grid--layout-' . $layout,
            'bbk-brand-color-grid--size-' . $size,
        );

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
                    <div class="bbk-brand-color-swatch bbk-brand-color-swatch--<?php echo esc_attr( $style ); ?> bbk-brand-color-swatch--size-<?php echo esc_attr( $size ); ?>">
                        <div class="bbk-brand-color-swatch__color" style="background-color: <?php echo esc_attr( $color['color'] ); ?>">
                            <?php if ( $style === 'chip' || $style === 'row' ) : ?>
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
                        <?php if ( ! in_array( $style, array( 'chip', 'row' ), true ) && ( $attributes['showName'] || $attributes['showSlug'] || ( $attributes['showHex'] && ! in_array( $style, array( 'card', 'large-card', 'minimal' ), true ) ) ) ) : ?>
                            <div class="bbk-brand-color-swatch__info">
                                <?php if ( $attributes['showName'] ) : ?>
                                    <span class="bbk-brand-color-swatch__name"><?php echo esc_html( $color['name'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( $attributes['showSlug'] ) : ?>
                                    <code class="bbk-brand-color-swatch__slug">--wp--preset--color--<?php echo esc_html( $color['slug'] ); ?></code>
                                <?php endif; ?>
                                <?php if ( $attributes['showHex'] && ! in_array( $style, array( 'card', 'large-card', 'minimal' ), true ) ) : ?>
                                    <span class="bbk-brand-color-swatch__hex-below"><?php echo esc_html( $color['color'] ); ?></span>
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

        $layout  = $attributes['layout'] ?? 'stack';
        $style   = $attributes['swatchStyle'] ?? 'bar';
        $columns = absint( $attributes['columns'] ?? 2 );

        $list_classes = array(
            'bbk-brand-gradient-list',
            'bbk-brand-gradient-list--' . $layout,
            'bbk-brand-gradient-list--style-' . $style,
        );

        ob_start();
        ?>
        <div class="bbk-brand-block bbk-brand-gradient-showcase">
            <?php if ( ! empty( $attributes['title'] ) ) : ?>
                <h3 class="bbk-brand-block__title"><?php echo esc_html( $attributes['title'] ); ?></h3>
            <?php endif; ?>
            
            <div class="<?php echo esc_attr( implode( ' ', $list_classes ) ); ?>" style="--bbk-columns: <?php echo esc_attr( $columns ); ?>">
                <?php foreach ( $gradients as $gradient ) : ?>
                    <div class="bbk-brand-gradient-item bbk-brand-gradient-item--<?php echo esc_attr( $style ); ?>">
                        <div class="bbk-brand-gradient-item__preview" style="background: <?php echo esc_attr( $gradient['gradient'] ); ?>"></div>
                        <?php if ( $attributes['showName'] || $attributes['showCode'] ) : ?>
                            <div class="bbk-brand-gradient-item__info">
                                <?php if ( $attributes['showName'] ) : ?>
                                    <span class="bbk-brand-gradient-item__name"><?php echo esc_html( $gradient['name'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( $attributes['showCode'] ) : ?>
                                    <code class="bbk-brand-gradient-item__code"><?php echo esc_html( $gradient['gradient'] ); ?></code>
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
        $display = $attributes['display'];

        ob_start();
        ?>
        <div class="bbk-brand-block bbk-brand-typography-samples">
            <?php if ( ! empty( $attributes['title'] ) ) : ?>
                <h3 class="bbk-brand-block__title"><?php echo esc_html( $attributes['title'] ); ?></h3>
            <?php endif; ?>

            <?php if ( ( $display === 'all' || $display === 'families' ) && ! empty( $font_families ) ) : ?>
                <div class="bbk-brand-typography-section">
                    <h4 class="bbk-brand-typography-section__title"><?php esc_html_e( 'Font Families', 'branding-block-kit' ); ?></h4>
                    <?php foreach ( $font_families as $family ) : ?>
                        <div class="bbk-brand-font-family">
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
            <?php endif; ?>

            <?php if ( ( $display === 'all' || $display === 'sizes' ) && ! empty( $font_sizes ) ) : ?>
                <div class="bbk-brand-typography-section">
                    <h4 class="bbk-brand-typography-section__title"><?php esc_html_e( 'Font Sizes', 'branding-block-kit' ); ?></h4>
                    <?php foreach ( $font_sizes as $size ) : ?>
                        <div class="bbk-brand-font-size">
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
