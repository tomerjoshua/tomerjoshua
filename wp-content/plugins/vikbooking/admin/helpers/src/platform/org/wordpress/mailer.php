<?php
/** 
 * @package     VikBooking
 * @subpackage  core
 * @author      E4J s.r.l.
 * @copyright   Copyright (C) 2021 E4J s.r.l. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://vikwp.com
 */

// No direct access
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Implements the mailer interface for the Wordpress platform.
 * 
 * @since 1.5
 */
class VBOPlatformOrgWordpressMailer implements VBOPlatformMailerInterface
{
	/**
	 * Sends an e-mail through the pre-installed mailing system.
	 * 
	 * @param 	VBOMailWrapper  $mail  The e-mail encapsulation.
	 * 
	 * @return 	boolean         True on success, false otherwise.
	 */
	public function send(VBOMailWrapper $mail)
	{
		// sends through PHP mailer
		$service = new VBOMailServicePhpmailer();

		// interprets shortcodes contained within the full text
		$mail->setContent(do_shortcode($mail->getContent()));

		// send the e-mail
		return $service->send($mail);
	}
}
