<?php

/**
 * This core class is used to add an additional admin menu for use of all How to Make plugins.
 * 
 * @package howtomake
 * @author  kraggle
 */

if (!class_exists('HTM_Core_Menu')) {

	define('HTM_CORE_DIR', plugin_dir_path(__FILE__));
	define('HTM_CORE_URI', trailingslashit(plugins_url('', __FILE__)));

	/**
	 * HTM Core Menu is used to have one base menu for all How to Make plugins.
	 * This allows for a much cleaner backend menu and makes it a lot easier to
	 * create additional menus.
	 * 
	 * @package howtomake
	 */
	class HTM_Core_Menu {

		private static $instance;

		private $name = 'How to Make';
		private $slug = 'htm-core-menu';
		private $dash;
		private $capability = 'administrator';
		private $pages;

		function __construct() {
			$this->pages = (object) [];

			add_action('admin_menu', array($this, 'init_menu'));
		}

		/**
		 * Used to create the Instance of HTM_Core_Menu and ensure we don't
		 * create more than one instance of it.
		 * 
		 * @return HTM_Core_Menu 
		 */
		public static function getInstance() {
			return !isset(self::$instance) ?
				self::$instance = new HTM_Core_Menu() :
				self::$instance;
		}

		function init_menu() {
			$this->dash = "{$this->slug}-dashboard";

			wp_enqueue_script(
				'module-htm-core-js',
				HTM_CORE_URI . 'scripts/core.js',
				[],
				filemtime(HTM_CORE_DIR . 'scripts/core.js')
			);
			wp_enqueue_style(
				'htm-core-css',
				HTM_CORE_URI . 'styles/core.css',
				[],
				filemtime(HTM_CORE_DIR . 'styles/core.css')
			);

			add_menu_page($this->name, $this->name, $this->capability, $this->dash, array($this, 'dashboard_page'), 'data:image/svg+xml,' . HTM_MENU_ICON);
			add_submenu_page($this->dash, "Dashboard", "Dashboard", $this->capability, $this->dash, array($this, 'dashboard_page'));
		}

		/**
		 * Used to add a new menu page to the How to Make menu item.
		 * 
		 * @param mixed           $name    The display name of the menu.
		 * @param string|function $content The menu content. This can be a function that returns a string.
		 * @param object[]        $script  An array of objects that contain the script info.
		 * @param object[]        $style   An array of objects that contain the style info. 
		 * @return self The instance of this menu so you can chain commands. 
		 */
		public function add_menu($name, $content = '', $script = [], $style = []) {

			$slug = str_replace(' ', '-', strtolower($name));
			$menu_slug = "{$this->slug}-{$slug}";

			$this->pages->$name = (object) [
				'name' => $name,
				'slug' => $slug,
				'menu_slug' => $menu_slug,
				'content' => $content,
				'scripts' => $script,
				'styles' => $style
			];

			if (!empty($script)) {
				foreach ($script as $obj)
					if (!is_object($obj) && is_array($obj))
						$obj = to_object($obj);
			}

			if (!empty($style)) {
				foreach ($style as $obj)
					if (!is_object($obj) && is_array($obj))
						$obj = to_object($obj);
			}

			add_submenu_page($this->dash, $name, $name, $this->capability, $menu_slug, array($this, 'build_page'));

			return self::$instance;
		}

		private function page_before($type) {

			global $title;
			printf(
				'<div class="ks-box" data-nonce="%5$s">
					<div class="ks-head">
						<div class="ks-icon">%1$s</div>
						<span class="ks-title">%2$s</span>
						<span class="ks-sub">%3$s</span>
					</div>
					<div class="ks-content %4$s">',
				urldecode(HTM_MENU_ICON),
				$this->name,
				$title,
				$type,
				wp_create_nonce('htm_settings_nonce')
			);
		}

		private function page_after() {
			echo '</div>
				<div class="ks-feed">
					<div class="ks-feed-text"></div>
				</div>
				<div class="ks-footer">
					<div class="ks-progress">
						<div class="ks-progress-back"></div>
						<div class="ks-progress-number"></div>
						<div class="ks-progress-time"></div>
					</div>
				</div>
			</div>';
		}

		function dashboard_page() {
			$this->page_before('dashboard');

			$this->page_after();
		}

		private function build_page() {
			global $title;
			$page = $this->pages->$title;

			foreach ($page->scripts as $script) {
				if (!is_object($script) || !file_exists($script->path))
					continue;

				wp_enqueue_script(
					$script->tag,
					$script->uri,
					[],
					filemtime($script->path)
				);
			}

			foreach ($page->styles as $style) {
				if (!is_object($script) || !file_exists($style->path))
					continue;

				wp_enqueue_style(
					$style->tag,
					$style->uri,
					[],
					filemtime($style->path)
				);
			}

			$this->page_before($page->slug);

			if (is_callable($page->content))
				call_user_func($page->content);
			else
				echo $page->content;

			$this->page_after();
		}
	}
}

if (!defined('HTM_MENU_ICON')) {
	define('HTM_MENU_ICON', "%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 250 250'%3E%3Cpath d='M224.5 62.9L204 51l5.3-3.1-10.1-5.8-5.2 3-40-23.3 5.1-3-10-5.8-5.1 2.9L125 5l-18.3 10.6-5.3-3-10.1 5.8 5.3 3.1-40 23-5.5-3.2L41 47.1l5.5 3.2-21 12.1 19.9 11.5-5.4 3.2 10 5.8 5.4-3.1L95.6 103l-5.6 3.3 10 5.8 5.6-3.3 19.3 11.2 19.4-11.1 5.6 3.3 10-5.8-5.6-3.2 40.3-23.1 5.5 3.2 10-5.8-5.4-3.1 19.8-11.5zm-55.1 10.2l-24.8-14.4L119.7 73l24.8 14.4-15.8 9.1L67.2 61 83 51.9l24 13.8 24.8-14.3-23.9-13.9 15.9-9.2L185.3 64l-15.9 9.1z' fill='%23cdcdcd'/%3E%3Cpath d='M125 167v-11.6l-5.8-3.3v-22.2l-19.3-11.1.1.1V112l-10-5.8v6.8L50.1 90v-7.1l-10-5.8v7.1L19.7 72.4v25.1l-5.8-3.3v11.6l5.8 3.3v46.3l-5.8-3.3v11.6l5.8 3.3v20.3L40 199v6.7l10 5.8v-6.7l40 23.1v6.8l10 5.7v-6.7l19.2 11.1v-23.2l5.8 3.3v-11.6l-5.8-3.3v-46.3l5.8 3.3zm-29-13.4L77.7 143v55.8l-16.4-9.5v-55.8L43 123v-15.1l53 30.6v15.1z' fill='%239b9b9b'/%3E%3Cpath d='M236.1 104.6V93l-5.8 3.3V72.9l-20.1 11.5v-7.1l-10 5.8v7.1l-40.1 23v-6.9l-10 5.8v6.8l-19.3 11v22.2l-5.8 3.3V167l5.8-3.3V210l-5.8 3.3v11.6l5.8-3.3V245l19.3-11v6.4l10-5.7v-6.4l40.1-22.8v6.2l10-5.7v-6.2l20.1-11.4v-22.5l5.8-3.3V151l-5.8 3.3V108l5.8-3.4zM211.4 177l-14.2 8.1-.1-32.6-11.7 39.3-10.3 5.9-11.7-25.5v32.2l-14 8v-71.1l12.7-7.2 18.4 38.1 18.3-59.1 12.6-7.2V177z' fill='%23acacac'/%3E%3C/svg%3E");
}
