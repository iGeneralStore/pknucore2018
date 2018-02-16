<?php

namespace CASE27\Classes\Conditions;

class Conditions {
	private $field, $conditions, $listing, $package_id;

	public function __construct( $field, $listing = null ) {
		$this->field = $field;
		$this->conditions = ! empty( $field['conditions'] ) ? $field['conditions'] : [];
		$this->conditional_logic = isset( $field['conditional_logic'] ) ? $field['conditional_logic'] : false;
		$this->listing = $listing;
		$this->package_id = $this->get_package_id();
	}

	public function passes() {
		$results = [];

		// If there's no conditional logic, show the field.
		if ( ! $this->conditional_logic ) {
			return true;
		}

		// Title and Description need to always be visible.
		if ( in_array( $this->field['slug'], [ 'job_title', 'job_description' ] ) ) {
			return true;
		}

		$this->conditions = array_filter( $this->conditions );

		// Return true if there isn't any condition set.
		if ( empty( $this->conditions ) ) {
			return true;
		}

		// Loop through the condition blocks.
		// First level items consists of arrays related as "OR".
		// Second level items consists of conditions related as "AND".
		// dump( sprintf( 'Looping through %s condition groups...', $this->field['slug'] ) );
		foreach ( $this->conditions as $conditionGroup ) {
			if ( empty( $conditionGroup ) ) {
				continue;
			}

			foreach ( $conditionGroup as $condition ) {
				if ( $condition['key'] == '__listing_package' ) {
					if ( ! ( $package_id = $this->package_id ) ) {
						// dump( 'Condition failed (package id not found).' );
						$results[] = false;
						continue(2);
					}

					if ( ! $this->compare( $condition, $package_id ) ) {
						// dump( 'Condition failed.', $condition );
						$results[] = false;
						continue(2);
					}

					// dump( 'Condition passed.' );
				}
			}

			$results[] = true;
		}

		// Return true if any of the condition groups is true.
		return in_array( true, $results );
	}

	public function compare( $condition, $value ) {
		if ( $condition['compare'] == '==' ) {
			return $condition['value'] == $value;
		}

		if ( $condition['compare'] == '!=' ) {
			return $condition['value'] != $value;
		}

		return false;
	}

	public function get_package_id() {
		// Determine the paid package ID.
		// @todo: Check if it's the preview step on this first one.
		if ( $this->listing && ! in_array( $this->listing->post_status, [ 'preview', 'pending_payment' ] ) ) {
			return $this->listing->_package_id;
		} elseif ( ! empty( $_POST['job_package'] ) ) {
			if ( is_numeric( $_POST['job_package'] ) ) {
				return absint( $_POST['job_package'] );
			}

			$package = wc_paid_listings_get_user_package( substr( $_POST['job_package'], 5 ) );

			return $package->has_package() ? $package->get_product_id() : false;
		} elseif ( isset( $_COOKIE['chosen_package_id'] ) && isset( $_COOKIE['chosen_package_is_user_package'] ) ) {
			$package_id = absint( $_COOKIE['chosen_package_id'] );
			$is_user_package = absint( $_COOKIE['chosen_package_is_user_package'] ) === 1;

			if ( $is_user_package ) {
				$package = wc_paid_listings_get_user_package( $package_id );
				return $package->has_package() ? $package->get_product_id() : false;
			}

			return $package_id;
		}

		return false;
	}
}