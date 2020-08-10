<?php
/**
 * Trait file for Singletons.
 *
 * @package ES Admin
 */

namespace ES_Admin;

/**
 * Make a class into a singleton.
 */
trait Singleton {
	/**
	 * Existing instance.
	 *
	 * @var array
	 */
	protected static $instance;

	/**
	 * Get class instance.
	 *
	 * @return object
	 */
	public static function instance() {
		if ( ! isset( static::$instance ) ) { // phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.StaticOutsideClass
			static::$instance = new static(); // phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.StaticOutsideClass
			static::$instance->setup(); // phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.StaticOutsideClass
		}
		return static::$instance; // phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis.StaticOutsideClass
	}

	/**
	 * Setup the singleton.
	 */
	public function setup() {
		// Silence.
	}
}
