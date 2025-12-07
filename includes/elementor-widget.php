<?php
/**
 * Elementor Widget for HO Tracking
 * 
 * @package HO_Tracking
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * HO Tracking Elementor Widget Class
 */
class HO_Tracking_Elementor_Widget extends \Elementor\Widget_Base {
    
    /**
     * Get widget name
     */
    public function get_name() {
        return 'ho_tracking_table';
    }
    
    /**
     * Get widget title
     */
    public function get_title() {
        return __('HO Tracking Table', 'ho-tracking');
    }
    
    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-table';
    }
    
    /**
     * Get widget categories
     */
    public function get_categories() {
        return array('general');
    }
    
    /**
     * Get widget keywords
     */
    public function get_keywords() {
        return array('tracking', 'postal', 'package', 'shipping', 'ho tracking');
    }
    
    /**
     * Register widget controls
     */
    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'ho-tracking'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );
        
        $this->add_control(
            'widget_description',
            array(
                'label' => __('Widget Description', 'ho-tracking'),
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => __('This widget displays the HO Tracking table with search functionality. Users can search for their tracking codes or recipient names.', 'ho-tracking'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            )
        );
        
        $this->end_controls_section();
        
        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Style', 'ho-tracking'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        
        $this->add_control(
            'search_title_color',
            array(
                'label' => __('Search Title Color', 'ho-tracking'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .ho-tracking-search h3' => 'color: {{VALUE}};',
                ),
            )
        );
        
        $this->add_control(
            'button_color',
            array(
                'label' => __('Button Color', 'ho-tracking'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .search-button' => 'background-color: {{VALUE}};',
                ),
            )
        );
        
        $this->add_control(
            'button_hover_color',
            array(
                'label' => __('Button Hover Color', 'ho-tracking'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .search-button:hover' => 'background-color: {{VALUE}};',
                ),
            )
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Render widget output on the frontend
     */
    protected function render() {
        // Use the existing shortcode functionality
        // The shortcode template uses _e() for static text and JavaScript escapeHtml() for dynamic data
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- do_shortcode() output is already properly escaped in template
        echo do_shortcode('[ho_tracking_table]');
    }
    
    /**
     * Render widget output in the editor
     * This method is not required for this widget as we want to show the live preview
     * Elementor will automatically use the render() method for the editor preview
     */
    protected function content_template() {
        // Leave empty to use render() method for live preview in editor
    }
}
