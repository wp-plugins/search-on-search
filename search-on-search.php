<?php
/*
 Plugin Name: Search on search
 Plugin URI: http://bagonca.com/blog/search-on-search-widget
 Description: Widget that presents posts matching search engine terms
 Author: PEZ <pez@pezius.com>
 Version: 1
 Author URI: http://bagonca.com/author/pez
 Licence: Feel free to use as you please. If you fix bugs or add features, consider sharing it back with me.
 */

/*
 search_keywords class from http://www.istanto.net/phpcatching-keyword-from-search-engine.html
 */
class search_keywords {
	var $referer;
	var $search_engine;
	var $keys;
	var $sep;
	var $sep2;
	
	function search_keywords() {
		$this->referer = '';
		$this->sep = '';
		$this->sep2 = '(?:\&|$)';
		
		if ($_SERVER['HTTP_REFERER'] OR $_ENV['HTTP_REFERER']) {
			$this->referer = urldecode(($_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : $_ENV['HTTP_REFERER']));
			$this->sep = (eregi('\?(q|qt|p)=', $this->referer)) ? '\?' : '\&';
		}
	}
	
	function get_keys() {
		if (!empty($this->referer)) {
			if (eregi('www\.google', $this->referer)) {
				// Google
				preg_match("#{$this->sep}q=(.*?){$this->sep2}#si", $this->referer, $this->keys);
				$this->search_engine = 'Google';
			}
			else if (eregi('(yahoo\.com|search\.yahoo)', $this->referer)) {
				// Yahoo
				preg_match("#{$this->sep}p=(.*?){$this->sep2}#si", $this->referer, $this->keys);
				$this->search_engine = 'Yahoo';
			}
			else if (eregi('search\.msn', $this->referer)) {
				// MSN
				preg_match("#{$this->sep}q=(.*?){$this->sep2}#si", $this->referer, $this->keys);
				$this->search_engine = 'MSN';
			}
			else if (eregi('www\.alltheweb', $this->referer)) {
				// AllTheWeb
				preg_match("#{$this->sep}q=(.*?){$this->sep2}#si", $this->referer, $this->keys);
				$this->search_engine = 'AllTheWeb';
			}
			else if (eregi('(looksmart\.com|search\.looksmart)', $this->referer)) {
				// Looksmart
				preg_match("#{$this->sep}qt=(.*?){$this->sep2}#si", $this->referer, $this->keys);
				$this->search_engine = 'Looksmart';
			}
			else if (eregi('(askjeeves\.com|ask\.com)', $this->referer)) {
				// AskJeeves
				preg_match("#{$this->sep}q=(.*?){$this->sep2}#si", $this->referer, $this->keys);
				$this->search_engine = 'AskJeeves';
			}
			else {
				return array();
			}
			return array(
						 $this->referer,
						 (!is_array($this->keys) ? $this->keys : $this->keys[1]),
						 $this->search_engine
						 );
		}
		return array();
	}
}

function searchonsearch() {
}

class SearchOnSearchWidget extends WP_Widget {

	function SearchOnSearchWidget() {
		parent::WP_Widget(false, $name = 'Search on search', array('description' => 'Posts and pages matching search engine terms'));	
	}
	
	/** @see WP_Widget::widget */
	function widget($args, $instance) {		
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		$keys =& new search_keywords();
		$terms = $keys->get_keys();
		$html = "";
		if (count($terms)) {
			$html .= $before_widget;
			$html .= $before_title . ($title ? $title : $this->name) . $after_title;
			$html .= '<em>' . htmlspecialchars($terms[1]) . "</em>\n";
			query_posts('s=' . urlencode($terms[1]) . '&posts_per_page=10');
			if (have_posts()) {
				$html .= "<ul>\n";
				while (have_posts()) : the_post();
					$html .= '<li><a href="' . get_permalink() . '">' . get_the_title($post) . "</a></li>\n";
				endwhile;
				$html .= '</ul>';
			}
			else {
				$html .= 'Sorry, no matching content found.';
			}
			$html .= $after_widget;
		}
		echo $html;
    }
	
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }
	
    /** @see WP_Widget::form */
    function form($instance) {
        $title = esc_attr($instance['title'], $this->name);
        ?>
<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
<?php 
    }
}

add_action('widgets_init', create_function('', 'return register_widget("SearchOnSearchWidget");'));

?>