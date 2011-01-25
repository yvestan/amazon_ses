<?php

/* Yves Tannier / grafactory.net */

/*
 * Copyright 2010 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

/**
 * File: AmazonSES
 *
 *
 * Version:
 * 	Tue Nov 09 21:03:19 PST 2010
 *
 * License and Copyright:
 * 	See the included NOTICE.md file for complete information.
 *
 * See Also:
 * 	[Amazon Simple Notification Service](http://aws.amazon.com/sns/)
 * 	[Amazon Simple Notification Service documentation](http://aws.amazon.com/documentation/sns/)
 */


/*%******************************************************************************************%*/
// EXCEPTIONS

/**
 * Exception: SES_Exception
 * 	Default SES Exception.
 */
class SES_Exception extends Exception {}


/*%******************************************************************************************%*/
// MAIN CLASS

/**
 * Class: AmazonS3
 * 	Container for all Amazon S3-related methods. Inherits additional methods from CFRuntime.
 */
class AmazonSES extends CFRuntime
{
	/*%******************************************************************************************%*/
	// CLASS CONSTANTS

	/**
	 * Constant: DEFAULT_URL
	 * 	Specify the default queue URL.
	 */
	const DEFAULT_URL = 'email.us-east-1.amazonaws.com';

	/**
	 * Constant: REGION_US_E1
	 * 	Specify the queue URL for the US-East (Northern Virginia) Region.
	 */
	const REGION_US_E1 = self::DEFAULT_URL;

	/**
	 * Constant: REGION_US_W1
	 * 	Specify the queue URL for the US-West (Northern California) Region.
	 */
	const REGION_US_W1 = 'email.us-west-1.amazonaws.com';

	/**
	 * Constant: REGION_EU_W1
	 * 	Specify the queue URL for the EU (Ireland) Region.
	 */
	const REGION_EU_W1 = 'email.eu-west-1.amazonaws.com';

	/**
	 * Constant: REGION_APAC_SE1
	 * 	Specify the queue URL for the Asia Pacific (Singapore) Region.
	 */
	const REGION_APAC_SE1 = 'email.ap-southeast-1.amazonaws.com';


	/*%******************************************************************************************%*/
	// SETTERS

	/**
	 * Method: set_region()
	 * 	This allows you to explicitly sets the region for the service to use.
	 *
	 * Access:
	 * 	public
	 *
	 * Parameters:
	 * 	$region - _string_ (Required) The region to explicitly set. Available options are <REGION_US_E1>, <REGION_US_W1>, <REGION_EU_W1>, or <REGION_APAC_SE1>.
	 *
	 * Returns:
	 * 	`$this`
	 */
	public function set_region($region)
	{
		$this->set_hostname($region);
		return $this;
	}

    /*%******************************************************************************************%*/
    // CONSTRUCTOR

    /**
     * Method: __construct()
     * 	Constructs a new instance of <AmazonS3>.
     *
     * Access:
     * 	public
     *
     * Parameters:
     * 	$key - _string_ (Optional) Amazon API Key. If blank, the <AWS_KEY> constant is used.
     * 	$secret_key - _string_ (Optional) Amazon API Secret Key. If blank, the <AWS_SECRET_KEY> constant is used.
     *
     * Returns:
     * 	_boolean_ A value of `false` if no valid values are set, otherwise `true`.
     */
    public function __construct($key = null, $secret_key = null)
    {
        $this->api_version = '2010-12-01';
        $this->hostname = self::DEFAULT_URL;

        if (!$key && !defined('AWS_KEY'))
        {
            throw new SES_Exception('No account key was passed into the constructor, nor was it set in the AWS_KEY constant.');
        }

        if (!$secret_key && !defined('AWS_SECRET_KEY'))
        {
            throw new SES_Exception('No account secret was passed into the constructor, nor was it set in the AWS_SECRET_KEY constant.');
        }

        return parent::__construct($key, $secret_key);
    }

	/*%******************************************************************************************%*/
	// SERVICE METHODS

	/**
	 * Method: get_send_quota()
	 * 	Returns the user's current activity limits.
	 *
	 * Access:
	 * 	public
	 *
	 * Returns:
	 * 	_array_ http://docs.amazonwebservices.com/ses/latest/APIReference/API_GetSendQuota.html
	 *
	 * See Also:
	 * 	[AWS SES Docs](http://docs.amazonwebservices.com/ses/latest/APIReference/API_GetSendQuota.html)
	 */
    public function get_send_quota()
	{
		return $this->authenticate('GetSendQuota', $opt, $this->hostname, 3);
	}

    /**
	 * Method: verify_email_address()
	 * 	Verifies an email address. This action causes a confirmation email message to be sent to the specified address.
	 *
	 * Access:
	 * 	public
	 *
	 * Returns:
	 * 	_array_ http://docs.amazonwebservices.com/ses/latest/APIReference/API_VerifyEmailAddress.html
	 *
	 * See Also:
	 * 	[AWS SES Docs](http://docs.amazonwebservices.com/ses/latest/APIReference/API_GetSendQuota.html)
	 */
    public function verify_email_address($email)
	{
		return $this->authenticate('VerifyEmailAddress', array('Email' => $email), $this->hostname, 3);
	}

    /**
	 * Method: send_email()
	 * 	Composes an email message based on input data, and then immediately queues the message for sending.
	 *
	 * Access:
	 * 	public
	 *
	 * Returns:
	 * 	_array_ http://docs.amazonwebservices.com/ses/latest/APIReference/API_SendEmail.html
	 *
	 * See Also:
	 * 	[AWS SES Docs](http://docs.amazonwebservices.com/ses/latest/APIReference/API_GetSendQuota.html)
	 */

    public function send_email($source, $message, $opt)
	{
		if (!$opt) $opt = array();

        $params['Source'] = $source;

        if(empty($opt['to'])) {
            throw new SES_Exception('No destination message specified');
        }

        foreach(array('to','cc','bcc') as $f) {
            if(!empty($opt[$f])) {
                if(is_array($opt[$f])) {
                    for($i = 0; $i <= count($opt[$f]); $i++) {
                        $params['Destination.'.ucfirst($f).'Addresses.member.'.($i+1)] = $opt[$f][$i];
                    }
                } else {
                    $params['Destination.'.ucfirst($f).'Addresses.member.1'] = $opt[$f];    
                }
            }
        }
        // message (subject & body)
        if(!empty($message['subject'])) {
            $params['Message.Subject.Data'] = $message['subject'];
        } else {
            throw new SES_Exception('No subject message specified');
        }
        if(!empty($message['body'])) {
            $params['Message.Body.Text.Data'] = $message['body'];
        } else {
            throw new SES_Exception('No body message specified');
        }

		return $this->authenticate('SendEmail', $params, $this->hostname, 3);
	}

}
