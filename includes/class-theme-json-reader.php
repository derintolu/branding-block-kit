<?php
/**
 * Theme JSON Reader
 *
 * Reads and parses design tokens from:
 * 1. The active theme's theme.json file (via WP_Theme_JSON_Resolver)
 * 2. Greenshift global settings (gspb_global_settings option)
 *
 * @package Branding_Block_Kit
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Theme JSON Reader class.
 */
class BBK_Theme_JSON_Reader {

    /**
     * Single instance of the class.
     *
     * @var BBK_Theme_JSON_Reader
     */
    private static $instance = null;

    /**
     * Cached theme.json data.
     *
     * @var array|null
     */
    private $theme_json_data = null;

    /**
     * Cached Greenshift settings.
     *
     * @var array|null
     */
    private $greenshift_data = null;

    /**
     * Get single instance of the class.
     *
     * @return BBK_Theme_JSON_Reader
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        // Data is loaded on demand.
    }

    /**
     * Check if Greenshift is active.
     *
     * @return bool
     */
    public function has_greenshift() {
        return defined( 'JEstarter_VERSION' ) || defined( 'JESTART_VERSION' ) || function_exists( 'greenshift_starter' );
    }

    /**
     * Get Greenshift global settings.
     *
     * @return array
     */
    public function get_greenshift_settings() {
        if ( is_null( $this->greenshift_data ) ) {
            $this->greenshift_data = get_option( 'gspb_global_settings', array() );
        }
        return $this->greenshift_data;
    }

    /**
     * Get the merged theme.json data.
     *
     * @return array
     */
    public function get_theme_json_data() {
        if ( is_null( $this->theme_json_data ) ) {
            $this->theme_json_data = $this->load_theme_json();
        }
        return $this->theme_json_data;
    }

    /**
     * Load theme.json using WordPress resolver.
     *
     * @return array
     */
    private function load_theme_json() {
        // Use WordPress's built-in resolver for proper merging.
        if ( class_exists( 'WP_Theme_JSON_Resolver' ) ) {
            $theme_json = WP_Theme_JSON_Resolver::get_merged_data();
            return $theme_json->get_raw_data();
        }

        // Fallback: Read theme.json directly.
        $theme_json_file = get_stylesheet_directory() . '/theme.json';
        
        if ( file_exists( $theme_json_file ) ) {
            $json_content = file_get_contents( $theme_json_file );
            return json_decode( $json_content, true ) ?: array();
        }

        return array();
    }

    /**
     * Flatten nested origin arrays (WP 6.x stores palette/gradients/etc as ['default' => [...], 'theme' => [...]]).
     *
     * @param array  $data   The nested array (e.g., palette data).
     * @param string $prefer Which origin to prefer: 'theme', 'default', or 'all'.
     * @return array Flattened array of items.
     */
    private function flatten_origin_array( $data, $prefer = 'all' ) {
        if ( empty( $data ) || ! is_array( $data ) ) {
            return array();
        }

        // Check if it's already flat (has 'slug' key in first item).
        $first_key = array_key_first( $data );
        if ( is_numeric( $first_key ) && isset( $data[0]['slug'] ) ) {
            return $data; // Already flat.
        }

        // It's nested by origin (default, theme, custom, etc.).
        $flattened = array();

        // Define priority order for origins.
        $origin_order = array( 'theme', 'custom', 'default' );

        if ( $prefer === 'all' ) {
            // Get all origins, theme first to avoid duplicates.
            foreach ( $origin_order as $origin ) {
                if ( isset( $data[ $origin ] ) && is_array( $data[ $origin ] ) ) {
                    foreach ( $data[ $origin ] as $item ) {
                        $item['_origin'] = $origin;
                        $flattened[] = $item;
                    }
                }
            }
            // Get any other origins not in our priority list.
            foreach ( $data as $origin => $items ) {
                if ( ! in_array( $origin, $origin_order, true ) && is_array( $items ) ) {
                    foreach ( $items as $item ) {
                        $item['_origin'] = $origin;
                        $flattened[] = $item;
                    }
                }
            }
        } elseif ( isset( $data[ $prefer ] ) && is_array( $data[ $prefer ] ) ) {
            foreach ( $data[ $prefer ] as $item ) {
                $item['_origin'] = $prefer;
                $flattened[] = $item;
            }
        }

        return $flattened;
    }

    /**
     * Get color palette from theme.json and Greenshift.
     *
     * @param string $source Source to get colors from: 'all', 'theme', 'greenshift', 'theme-only'.
     * @return array Array of colors with slug, color, and name.
     */
    public function get_colors( $source = 'theme-only' ) {
        $colors = array();

        // Theme.json colors.
        if ( $source === 'all' || $source === 'theme' || $source === 'theme-only' ) {
            $data = $this->get_theme_json_data();
            if ( isset( $data['settings']['color']['palette'] ) ) {
                // Use 'theme' origin only by default (excludes WP default colors like orange, cyan, etc.)
                $origin_filter = ( $source === 'all' ) ? 'all' : 'theme';
                $palette = $this->flatten_origin_array( $data['settings']['color']['palette'], $origin_filter );
                foreach ( $palette as $color ) {
                    if ( isset( $color['slug'], $color['color'] ) ) {
                        $colors[] = array(
                            'slug'   => $color['slug'],
                            'color'  => $color['color'],
                            'name'   => $color['name'] ?? ucfirst( str_replace( '-', ' ', $color['slug'] ) ),
                            'origin' => $color['_origin'] ?? 'theme',
                            'var'    => '--wp--preset--color--' . $color['slug'],
                        );
                    }
                }
            }
        }

        // Greenshift colors.
        if ( ( $source === 'all' || $source === 'greenshift' ) && $this->has_greenshift() ) {
            $gs = $this->get_greenshift_settings();
            if ( ! empty( $gs['colours'] ) && is_array( $gs['colours'] ) ) {
                foreach ( $gs['colours'] as $key => $color_value ) {
                    if ( ! empty( $color_value ) ) {
                        $colors[] = array(
                            'slug'   => 'gs-color' . $key,
                            'color'  => $color_value,
                            'name'   => 'Greenshift Color ' . $key,
                            'origin' => 'greenshift',
                            'var'    => '--gs-color' . $key,
                        );
                    }
                }
            }
        }

        return $colors;
    }

    /**
     * Get gradients from theme.json and Greenshift.
     *
     * @param string $source Source: 'all', 'theme', 'greenshift', 'theme-only'.
     * @return array Array of gradients with slug, gradient, and name.
     */
    public function get_gradients( $source = 'theme-only' ) {
        $gradients = array();

        // Theme.json gradients.
        if ( $source === 'all' || $source === 'theme' || $source === 'theme-only' ) {
            $data = $this->get_theme_json_data();
            if ( isset( $data['settings']['color']['gradients'] ) ) {
                // Use 'theme' origin only by default (excludes WP default gradients)
                $origin_filter = ( $source === 'all' ) ? 'all' : 'theme';
                $gradient_list = $this->flatten_origin_array( $data['settings']['color']['gradients'], $origin_filter );
                foreach ( $gradient_list as $gradient ) {
                    if ( isset( $gradient['slug'], $gradient['gradient'] ) ) {
                        $gradients[] = array(
                            'slug'     => $gradient['slug'],
                            'gradient' => $gradient['gradient'],
                            'name'     => $gradient['name'] ?? ucfirst( str_replace( '-', ' ', $gradient['slug'] ) ),
                            'origin'   => $gradient['_origin'] ?? 'theme',
                            'var'      => '--wp--preset--gradient--' . $gradient['slug'],
                        );
                    }
                }
            }
        }

        // Greenshift gradients.
        if ( ( $source === 'all' || $source === 'greenshift' ) && $this->has_greenshift() ) {
            $gs = $this->get_greenshift_settings();
            if ( ! empty( $gs['gradients'] ) && is_array( $gs['gradients'] ) ) {
                foreach ( $gs['gradients'] as $key => $gradient_value ) {
                    if ( ! empty( $gradient_value ) ) {
                        $gradients[] = array(
                            'slug'     => 'gs-gradient' . $key,
                            'gradient' => $gradient_value,
                            'name'     => 'Greenshift Gradient ' . $key,
                            'origin'   => 'greenshift',
                            'var'      => '--gs-gradient' . $key,
                        );
                    }
                }
            }
        }

        return $gradients;
    }

    /**
     * Get font sizes from theme.json.
     *
     * @param string $source Source: 'all' or 'theme-only'.
     * @return array Array of font sizes with slug, size, and name.
     */
    public function get_font_sizes( $source = 'theme-only' ) {
        $data = $this->get_theme_json_data();
        
        $sizes = array();

        if ( isset( $data['settings']['typography']['fontSizes'] ) ) {
            $origin_filter = ( $source === 'all' ) ? 'all' : 'theme';
            $size_list = $this->flatten_origin_array( $data['settings']['typography']['fontSizes'], $origin_filter );
            foreach ( $size_list as $size ) {
                if ( isset( $size['slug'], $size['size'] ) ) {
                    $sizes[] = array(
                        'slug'   => $size['slug'],
                        'size'   => $size['size'],
                        'name'   => $size['name'] ?? ucfirst( str_replace( '-', ' ', $size['slug'] ) ),
                        'origin' => $size['_origin'] ?? 'theme',
                    );
                }
            }
        }

        return $sizes;
    }

    /**
     * Get font families from theme.json.
     *
     * @param string $source Source: 'all' or 'theme-only'.
     * @return array Array of font families with slug, fontFamily, and name.
     */
    public function get_font_families( $source = 'theme-only' ) {
        $data = $this->get_theme_json_data();
        
        $families = array();

        if ( isset( $data['settings']['typography']['fontFamilies'] ) ) {
            $origin_filter = ( $source === 'all' ) ? 'all' : 'theme';
            $family_list = $this->flatten_origin_array( $data['settings']['typography']['fontFamilies'], $origin_filter );
            foreach ( $family_list as $family ) {
                if ( isset( $family['slug'], $family['fontFamily'] ) ) {
                    $families[] = array(
                        'slug'       => $family['slug'],
                        'fontFamily' => $family['fontFamily'],
                        'name'       => $family['name'] ?? ucfirst( str_replace( '-', ' ', $family['slug'] ) ),
                        'origin'     => $family['_origin'] ?? 'theme',
                    );
                }
            }
        }

        return $families;
    }

    /**
     * Get spacing sizes from theme.json.
     *
     * @param string $source Source: 'all' or 'theme-only'.
     * @return array Array of spacing sizes with slug, size, and name.
     */
    public function get_spacing_sizes( $source = 'theme-only' ) {
        $data = $this->get_theme_json_data();
        
        $sizes = array();

        if ( isset( $data['settings']['spacing']['spacingSizes'] ) ) {
            $origin_filter = ( $source === 'all' ) ? 'all' : 'theme';
            $size_list = $this->flatten_origin_array( $data['settings']['spacing']['spacingSizes'], $origin_filter );
            foreach ( $size_list as $size ) {
                if ( isset( $size['slug'], $size['size'] ) ) {
                    $sizes[] = array(
                        'slug'   => $size['slug'],
                        'size'   => $size['size'],
                        'name'   => $size['name'] ?? $size['slug'],
                        'origin' => $size['_origin'] ?? 'theme',
                    );
                }
            }
        }

        return $sizes;
    }

    /**
     * Get shadows from theme.json.
     *
     * @param string $source Source: 'all' or 'theme-only'.
     * @return array Array of shadows with slug, shadow, and name.
     */
    public function get_shadows( $source = 'theme-only' ) {
        $data = $this->get_theme_json_data();
        
        $shadows = array();

        // Check settings.shadow.presets (WP 6.1+)
        if ( isset( $data['settings']['shadow']['presets'] ) ) {
            $origin_filter = ( $source === 'all' ) ? 'all' : 'theme';
            $shadow_list = $this->flatten_origin_array( $data['settings']['shadow']['presets'], $origin_filter );
            foreach ( $shadow_list as $shadow ) {
                if ( isset( $shadow['slug'], $shadow['shadow'] ) ) {
                    $shadows[] = array(
                        'slug'   => $shadow['slug'],
                        'shadow' => $shadow['shadow'],
                        'name'   => $shadow['name'] ?? ucfirst( str_replace( '-', ' ', $shadow['slug'] ) ),
                        'origin' => $shadow['_origin'] ?? 'theme',
                    );
                }
            }
        }

        return $shadows;
    }

    /**
     * Get custom properties from theme.json.
     *
     * @return array The custom settings object.
     */
    public function get_custom() {
        $data = $this->get_theme_json_data();
        
        return $data['settings']['custom'] ?? array();
    }

    /**
     * Get border radius values from theme.json custom section.
     *
     * @return array Array of border radius values.
     */
    public function get_border_radius() {
        $custom = $this->get_custom();
        
        return $custom['borderRadius'] ?? array();
    }

    /**
     * Get layout settings from theme.json.
     *
     * @return array Layout settings including contentSize and wideSize.
     */
    public function get_layout() {
        $data = $this->get_theme_json_data();
        
        return $data['settings']['layout'] ?? array();
    }

    /**
     * Get all design tokens as a structured array.
     *
     * @return array All design tokens organized by category.
     */
    public function get_all_tokens() {
        return array(
            'colors'       => $this->get_colors(),
            'gradients'    => $this->get_gradients(),
            'fontSizes'    => $this->get_font_sizes(),
            'fontFamilies' => $this->get_font_families(),
            'spacing'      => $this->get_spacing_sizes(),
            'shadows'      => $this->get_shadows(),
            'borderRadius' => $this->get_border_radius(),
            'custom'       => $this->get_custom(),
            'layout'       => $this->get_layout(),
        );
    }

    /**
     * Get the CSS variable name for a color.
     *
     * @param string $slug Color slug.
     * @return string CSS variable name.
     */
    public function get_color_var( $slug ) {
        return '--wp--preset--color--' . $slug;
    }

    /**
     * Get the CSS variable name for a gradient.
     *
     * @param string $slug Gradient slug.
     * @return string CSS variable name.
     */
    public function get_gradient_var( $slug ) {
        return '--wp--preset--gradient--' . $slug;
    }

    /**
     * Get the CSS variable name for a font size.
     *
     * @param string $slug Font size slug.
     * @return string CSS variable name.
     */
    public function get_font_size_var( $slug ) {
        return '--wp--preset--font-size--' . $slug;
    }

    /**
     * Get the CSS variable name for a font family.
     *
     * @param string $slug Font family slug.
     * @return string CSS variable name.
     */
    public function get_font_family_var( $slug ) {
        return '--wp--preset--font-family--' . $slug;
    }

    /**
     * Get the CSS variable name for spacing.
     *
     * @param string $slug Spacing slug.
     * @return string CSS variable name.
     */
    public function get_spacing_var( $slug ) {
        return '--wp--preset--spacing--' . $slug;
    }

    /**
     * Clear cached data (useful for testing or after theme switch).
     */
    public function clear_cache() {
        $this->theme_json_data = null;
    }
}
