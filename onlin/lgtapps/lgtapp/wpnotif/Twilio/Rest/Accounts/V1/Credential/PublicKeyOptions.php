<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Accounts\V1\Credential;

use Twilio\Options;
use Twilio\Values;

abstract class PublicKeyOptions {
	/**
	 * @param string $friendlyName A human readable description of this resource
	 * @param string $accountSid The Subaccount this Credential should be
	 *                           associated with.
	 *
	 * @return CreatePublicKeyOptions Options builder
	 */
	public static function create( $friendlyName = Values::NONE, $accountSid = Values::NONE ) {
		return new CreatePublicKeyOptions( $friendlyName, $accountSid );
	}

	/**
	 * @param string $friendlyName A human readable description of this resource
	 *
	 * @return UpdatePublicKeyOptions Options builder
	 */
	public static function update( $friendlyName = Values::NONE ) {
		return new UpdatePublicKeyOptions( $friendlyName );
	}
}

class CreatePublicKeyOptions extends Options {
	/**
	 * @param string $friendlyName A human readable description of this resource
	 * @param string $accountSid The Subaccount this Credential should be
	 *                           associated with.
	 */
	public function __construct( $friendlyName = Values::NONE, $accountSid = Values::NONE ) {
		$this->options['friendlyName'] = $friendlyName;
		$this->options['accountSid']   = $accountSid;
	}

	/**
	 * A human readable description of this resource, up to 64 characters.
	 *
	 * @param string $friendlyName A human readable description of this resource
	 *
	 * @return $this Fluent Builder
	 */
	public function setFriendlyName( $friendlyName ) {
		$this->options['friendlyName'] = $friendlyName;

		return $this;
	}

	/**
	 * The Subaccount this Credential should be associated with. Needs to be a valid Subaccount of the account issuing the request
	 *
	 * @param string $accountSid The Subaccount this Credential should be
	 *                           associated with.
	 *
	 * @return $this Fluent Builder
	 */
	public function setAccountSid( $accountSid ) {
		$this->options['accountSid'] = $accountSid;

		return $this;
	}

	/**
	 * Provide a friendly representation
	 *
	 * @return string Machine friendly representation
	 */
	public function __toString() {
		$options = array();
		foreach ( $this->options as $key => $value ) {
			if ( $value != Values::NONE ) {
				$options[] = "$key=$value";
			}
		}

		return '[Twilio.Accounts.V1.CreatePublicKeyOptions ' . implode( ' ', $options ) . ']';
	}
}

class UpdatePublicKeyOptions extends Options {
	/**
	 * @param string $friendlyName A human readable description of this resource
	 */
	public function __construct( $friendlyName = Values::NONE ) {
		$this->options['friendlyName'] = $friendlyName;
	}

	/**
	 * A human readable description of this resource, up to 64 characters.
	 *
	 * @param string $friendlyName A human readable description of this resource
	 *
	 * @return $this Fluent Builder
	 */
	public function setFriendlyName( $friendlyName ) {
		$this->options['friendlyName'] = $friendlyName;

		return $this;
	}

	/**
	 * Provide a friendly representation
	 *
	 * @return string Machine friendly representation
	 */
	public function __toString() {
		$options = array();
		foreach ( $this->options as $key => $value ) {
			if ( $value != Values::NONE ) {
				$options[] = "$key=$value";
			}
		}

		return '[Twilio.Accounts.V1.UpdatePublicKeyOptions ' . implode( ' ', $options ) . ']';
	}
}