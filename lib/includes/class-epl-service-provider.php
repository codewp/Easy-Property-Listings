<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service Provider of EPL.
 *
 * @since      1.0.0
 *
 * @package    Easy_Property_Listings
 * @subpackage Easy_Property_Listings/lib/incluces
 * @author     Taher Atashbar <taher.atashbar@gmail.com>
 */
class EPL_Service_Provider {

	/**
	 * Container for services.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private $container = array();

	/**
	 * Binding a service to container.
	 *
	 * @since  1.0.0
	 * @param  string $key
	 * @param  mixed  $value
	 * @return mixed
	 */
	public function bind( $key, $value ) {
		return $this->container[ $key ] = $value;
	}

	/**
	 * Getting a service from container.
	 *
	 * @since  1.0.0
	 * @param  string $key
	 * @return mixed
	 */
	public function make( $key ) {
		try {
			if ( isset( $this->container[ $key ] ) ) {
				return $this->container[ $key ];
			}
			throw new Exception( "Object {$key} not found exception" );
		} catch( Exception $e ) {
			die( $e->getMessage() );
		}
	}

}
