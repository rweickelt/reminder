<?php
/*!
Webservice entry file.

@license    GPL 2 (http://www.gnu.org/licenses/gpl.html)

*/

if(!defined('SRC_DIR')) define('SRC_DIR',dirname(__FILE__).'/src/');


require_once(SRC_DIR.'/init.php');
require_once(SRC_DIR.'/log.php');
require_once(SRC_DIR.'/mail.php');
require_once(SRC_DIR.'/reminder.php');

function shutdown()
{

}


function main()
{
    global $conf;
	dbg("Begin");
	//register_shutdown_function('shutdown');
	dbg("Connect...");
	$inbox = mail_connect('INBOX');
    dbg("List...");
	$mails = mail_list($inbox);
	dbg("Found emails: ".print_r($mails, true));
	// Todo: The mailbox could gather messages from different email-addresses.
	// Thus, it would be possible, to handle messages according to their
	// toaddress with the same software, but with different handlers.

	// Execute reminders and remove processed messages
	dbg("Execute reminder...");
	reminder_exec($inbox, $mails);
 	dbg("Disconnect...");
	mail_disconnect($inbox);
    dbg("Success");
    dbg("End");
}


?>

<html>
	<body>
	<?php main(); ?>
	</body>
</html>
