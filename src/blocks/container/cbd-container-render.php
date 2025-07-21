<?php
/**
 * Container Block Server-side Rendering
 *
 * @package ContainerBlockDesigner
 * 
 * Available variables:
 * $attributes (array): The block attributes.
 * $content (string): The block default content.
 * $block (WP_Block): The block instance.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Extract attributes
$block_id = !empty($attributes['blockId']) ? esc_attr($attributes['blockId']) : '';
$custom_classes = !empty($attributes['customClasses']) ? esc_attr($attributes['customClasses']) : '';
$full_width = !empty($attributes['fullWidth']) ? $attributes['fullWidth'] : false;

// Build class list
$classes = ['cbd-container'];

if ($block_id) {
    $classes[] = 'cbd-container-' . $block_id;
}

if ($custom_classes) {
    $classes[] = $custom_classes;
}

if ($full_width) {
    $classes[] = 'alignfull';
}

// Get wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes([
    'class' => implode(' ', $classes),
    'data-block-id' => $block_id,
]);

// Get block configuration if block ID is set
$inline_styles = '';
if ($block_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cbd_blocks';
    
    $block_config = $wpdb->get_var($wpdb->prepare(
        "SELECT block_config FROM {$table_name} WHERE id = %s AND status = 'active' AND deleted_at IS NULL",
        $block_id
    ));
    
    if ($block_config) {
        $config = json_decode($block_config, true);
        
        // Generate inline styles
        if (!empty($config['styles']['desktop'])) {
            $styles = $config['styles']['desktop'];
            $css_rules = [];
            
            // Padding
            if (!empty($styles['padding'])) {
                $css_rules[] = sprintf(
                    'padding: %dpx %dpx %dpx %dpx',
                    $styles['padding']['top'],
                    $styles['padding']['right'],
                    $styles['padding']['bottom'],
                    $styles['padding']['left']
                );
            }
            
            // Margin
            if (!empty($styles['margin'])) {
                $css_rules[] = sprintf(
                    'margin: %dpx %dpx %dpx %dpx',
                    $styles['margin']['top'],
                    $styles['margin']['right'],
                    $styles['margin']['bottom'],
                    $styles['margin']['left']
                );
            }
            
            // Background
            if (!empty($styles['backgroundColor'])) {
                $css_rules[] = 'background-color: ' . $styles['backgroundColor'];
            }
            
            // Border
            if (!empty($styles['borderWidth']) && $styles['borderWidth'] > 0) {
                $css_rules[] = sprintf(
                    'border: %dpx %s %s',
                    $styles['borderWidth'],
                    $styles['borderStyle'] ?? 'solid',
                    $styles['borderColor'] ?? '#ddd'
                );
            }
            
            // Border Radius
            if (!empty($styles['borderRadius'])) {
                $css_rules[] = 'border-radius: ' . $styles['borderRadius'] . 'px';
            }
            
            // Min Height
            if (!empty($styles['minHeight'])) {
                $css_rules[] = 'min-height: ' . $styles['minHeight'];
            }
            
            // Max Width
            if (!empty($styles['maxWidth'])) {
                $css_rules[] = 'max-width: ' . $styles['maxWidth'];
                $css_rules[] = 'margin-left: auto';
                $css_rules[] = 'margin-right: auto';
            }
            
            // Flexbox
            if (!empty($styles['display']) && $styles['display'] === 'flex') {
                $css_rules[] = 'display: flex';
                
                if (!empty($styles['flexDirection'])) {
                    $css_rules[] = 'flex-direction: ' . $styles['flexDirection'];
                }
                
                if (!empty($styles['justifyContent'])) {
                    $css_rules[] = 'justify-content: ' . $styles['justifyContent'];
                }
                
                if (!empty($styles['alignItems'])) {
                    $css_rules[] = 'align-items: ' . $styles['alignItems'];
                }
                
                if (!empty($styles['gap'])) {
                    $css_rules[] = 'gap: ' . $styles['gap'] . 'px';
                }
            }
            
            $inline_styles = ' style="' . esc_attr(implode('; ', $css_rules)) . '"';
        }
    }
}

// Output
?>
<div <?php echo $wrapper_attributes . $inline_styles; ?>>
    <?php echo $content; ?>
</div>