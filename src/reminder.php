<?php

/*!
Reminder functionality

@license    GPL 2 (http://www.gnu.org/licenses/gpl.html)

*/

if(!defined('SRC_DIR')) die('meh.');

require_once(SRC_DIR.'/log.php');
require_once(SRC_DIR.'/mail.php');

/*!
\brief Stores all informations about a reminder entry.
*/
class ReminderEntry
{
	public $creator;
	public $creatorEmail;
	public $creationTime;
	public $expirationTime;
	public $title;
	public $content;
}

/*!
\brief Scans for outdated reminder messages.

This function scans in \a mailbox a set of \a messages for reminders and sends
the message back to the sender if it is a reminder that has timed out.

Ill-formatted messages are silently deleted.

This function assumes, that \a mailbox is an opended mailbox and that
\a messages is a set of filtered messages.

*/
function reminder_exec($mailbox, $messages) 
{
	global $conf;
	global $users;

	foreach($messages as $i)
	{
		$header = imap_headerinfo($mailbox, $i);
		$from = $header->from[0];
		$fromaddress = $from->mailbox."@".$from->host;

		// Check for allowed users
		if (!in_array($fromaddress, $users))
		{
			dbg(sprintf("Delete message from unknown sender '%s'.", $fromaddress));
		}
		// Check for correct reminder format
		else if (preg_match('/(?P<date>\d{4}-\d{2}-\d{2})\s?:\s?(?P<subject>.*)/', $header->subject, $matches) > 0)
		{
			$date = $matches['date'];
			$subject = $matches['subject'];

			if (strtotime($date) > time())
			{
				continue;
			}
			reminder_send($mailbox, $i, $subject);
		}
		else
		{
			dbg(sprintf("Delete eMail from '%s' with wrong subject: '%s'.", $fromaddress, $header->subject));
		}

		// And finally delete the message
		@imap_delete($mailbox, $i) or die("Could not delete message");
	}
}


/*!
\brief Sends a reminder eMail.

The reminder contents are extracted from \a mail in \a mailbox. The parameter
\a subject is used.

*/
function reminder_send($mailbox, $mail, $subject)
{
	global $conf;

	$header = imap_headerinfo($mailbox, $mail);
	$from = $header->from[0];
	$fromaddress = $from->mailbox."@".$from->host;

	// Prepare variables
	$id = $header->message_id;
	$toaddress = $fromaddress;
	$fromaddress = $conf['email'];

	// Create new mail
	$subject = "Reminder: $subject";
	$body = imap_body($mailbox, $mail);
	$header = '';
	$header .= "From: $fromaddress".MAILHEADER_EOL;
	$header .= "References: $id".MAILHEADER_EOL;
	$header .= sprintf("Message-ID: %s_%s".MAILHEADER_EOL, md5(time()), $fromaddress);
	$header .= sprintf("Return-Path: %s".MAILHEADER_EOL, $conf['email']);
	$header .= 'MIME-Version: 1.0'.MAILHEADER_EOL;
	$header .= 'Content-Type: text/plain; charset=UTF-8'.MAILHEADER_EOL;
	$header .= 'Content-Transfer-Encoding: quoted-printable'.MAILHEADER_EOL;
	$header  = trim($header);

	dbg("Send reminder to ".$toaddress." subject '".$subject."'");
	@mail($toaddress , $subject , $body, $header, sprintf("-f %s", $conf['email'])) or die("Could not send mail");
}
