<?php
/*!
eMail helper functions

@license    GPL 2 (http://www.gnu.org/licenses/gpl.html)

*/

if(!defined('SRC_DIR')) die('meh.');

if(!defined('MAILHEADER_EOL')) define('MAILHEADER_EOL',"\n");

function mail_connect($folder)
{
	global $conf;
	$server = $conf['server'];
	$user = $conf['user'];
	$password = $conf['password'];

	$mailbox = @imap_open("{".$server."}".$folder, $user, $password, CL_EXPUNGE)
		or die("Error connecting to imap server");

	return $mailbox;
}

function mail_disconnect($mailbox)
{
	imap_expunge($mailbox);
	imap_close($mailbox);
}

/**
 \brief Returns a list of mails in the inbox.
*/
function mail_list($mailbox)
{
	$num = imap_num_msg($mailbox);
	if ($num > 0)
	{
		return range(1, $num);
	}
	
	return array();
}


/*!
\brief Deletes \a mails from the \a mailbox.  
*/
function mail_delete($mailbox, $mails)
{
	foreach($mails as $i)
	{
		@imap_delete($mailbox, $i) or die("Could not delete message");
	}
}


/*!
\brief Returns a subset of \a mails from \a inbox with matching \a senders.
*/
function mail_filterBySender($mailbox, $mails, $senders)
{
	$result = array();
	foreach($mails as $i)
	{
		$header = imap_headerinfo($mailbox, $i);
		$from = $header->from[0];
		$fromaddress = $from->mailbox."@".$from->host;
		
		if (in_array($fromaddress, $senders))
		{
			$result[] = $i;
		} 
	}

	return $result;
}

/**
 * Forwards email with the index $mail from the mailbox $mailbox
 * to all users in $users.
 */
function mail_forward($mailbox, $mail, $users, $subject)
{
	global $conf;

	// Read/parse original mail
	$headers = imap_headerinfo($mailbox, $mail);
	$from = $headers->from[0];
	//$fromaddress = $from->mailbox."@".$from->host;
	$fromaddress = $conf['email'];
	$subject = $headers->subject;
	$body = imap_body($mailbox, $mail);
	$id = $headers->message_id;
	$inreplyto = $headers->in_reply_to;
	$references = $headers->references;

	// Add subject identifier [list-title]
	if (strpos($subject, $conf['title']) === FALSE)
	{
		$title = $conf['title'];
		$subject = "[$title] $subject";
	}

	// Create new mail
	$headers = '';
	$headers .= "From: $fromaddress\r\n";
	//$headers .= sprintf("Reply-To: %s\r\n", $conf['email']);
	$headers .= "Bcc: ".implode($users, ', ')."\r\n";
	$headers .= "Message-ID: $id\r\n";
	$headers .= sprintf("Return-Path: %s\r\n", $conf['email']);
	if (isset($inreplyto) || isset($references))
	{
		$headers .= "In-Reply-To: $inreplyto\r\n";
		$headers .= "References: $references\r\n";
	}

	// FIXME: Fix the empty toaddr
	@mail('' , $subject , $body, $headers, sprintf("-f %s", $conf['email']));// or die("Could not send mail");
}

