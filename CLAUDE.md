# Branding Block Kit

A visual blocks-based style guide system for WordPress that automatically displays design tokens from `theme.json`.

## Overview

This plugin provides Gutenberg blocks that read from your theme's `theme.json` file and display the design tokens visually. This allows you to create comprehensive brand style guides that automatically stay in sync with your theme's configuration.

## Blocks Available

| Block | Description |
|-------|-------------|
| `bbk/color-palette` | Displays color palette from theme.json |
| `bbk/gradient-showcase` | Displays gradients from theme.json |
| `bbk/typography-samples` | Displays font families and sizes |
| `bbk/spacing-scale` | Displays spacing scale visually |
| `bbk/shadow-showcase` | Displays shadow presets |
| `bbk/border-radius` | Displays border radius values from custom section |
| `bbk/custom-properties` | Displays custom properties from theme.json |
| `bbk/style-guide` | Combined full style guide (all of the above) |

## Usage

1. Activate the plugin
2. Edit any page/post in the block editor
3. Add blocks from the "Brand Style Guide" category
4. Blocks automatically read from your theme's `theme.json`

### Example: Full Style Guide

```html
<!-- wp:bbk/style-guide {"title":"Brand Style Guide","showColors":true,"showGradients":true,"showTypography":true,"showSpacing":true,"showCustom":false} /-->
```

### Example: Just Colors

```html
<!-- wp:bbk/color-palette {"title":"Brand Colors","columns":4,"swatchStyle":"card","showHex":true,"showName":true} /-->
```

## Block Attributes

### Color Palette Block

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `title` | string | "Color Palette" | Section title |
| `showHex` | boolean | true | Show hex color values |
| `showName` | boolean | true | Show color names |
| `showSlug` | boolean | false | Show CSS variable slug |
| `columns` | number | 4 | Grid columns |
| `swatchStyle` | string | "card" | Style: card, chip, circle, square, pill, stripe, minimal, large-card |
| `layout` | string | "grid" | Layout: grid, row, list, inline |
| `swatchSize` | string | "medium" | Size: small, medium, large |
| `groupLabel` | string | "" | Small label above colors |
| `filterSlugs` | string | "" | Comma-separated slugs to filter |

### Gradient Showcase Block

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `title` | string | "Gradients" | Section title |
| `showName` | boolean | true | Show gradient names |
| `showCode` | boolean | true | Show CSS code |
| `layout` | string | "stack" | Layout: stack, grid, cards |
| `columns` | number | 2 | Grid columns |
| `swatchStyle` | string | "bar" | Style: bar, square, circle, card |

### Typography Samples Block

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `title` | string | "Typography" | Section title |
| `sampleText` | string | "The quick brown fox..." | Sample text to display |
| `showFontSize` | boolean | true | Show font size values |
| `showFontFamily` | boolean | true | Show font family values |
| `display` | string | "all" | Display: all, sizes, families |

### Spacing Scale Block

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `title` | string | "Spacing Scale" | Section title |
| `showValue` | boolean | true | Show spacing values |
| `showName` | boolean | true | Show spacing names |
| `direction` | string | "horizontal" | Direction: horizontal, vertical |

## theme.json Structure

The plugin reads from these theme.json paths:

```json
{
  "settings": {
    "color": {
      "palette": [...],
      "gradients": [...]
    },
    "typography": {
      "fontFamilies": [...],
      "fontSizes": [...]
    },
    "spacing": {
      "spacingSizes": [...]
    },
    "shadow": {
      "presets": [...]
    },
    "custom": {
      "borderRadius": {...},
      ...
    }
  }
}
```

## File Structure

```
branding-block-kit/
├── branding-block-kit.php        # Main plugin file with block registration
├── includes/
│   └── class-theme-json-reader.php  # Reads/parses theme.json
├── assets/
│   └── css/
│       └── blocks.css            # Block styles
├── build/
│   └── index.js                  # Editor JavaScript
└── CLAUDE.md                     # This file
```

## Extending

### Adding Custom Sections

The `bbk/custom-properties` block can display any section from the `settings.custom` object in theme.json. Use the `section` attribute to filter:

```html
<!-- wp:bbk/custom-properties {"title":"Sidebar Config","section":"sidebar"} /-->
```

### CSS Customization

All blocks use BEM-style class names prefixed with `.bbk-brand-`. Override styles in your theme:

```css
.bbk-brand-color-swatch--card {
    border-radius: 20px;
}
```

## Dark Mode

The blocks automatically support dark mode via CSS custom properties that respond to `[data-theme="light"]` and `.is-light-theme` classes.

## Print Styles

Print-optimized styles are included for generating PDF brand guides.
