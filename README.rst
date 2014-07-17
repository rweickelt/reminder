Reminder
========
A server application that sends reminder messages via eMail. It watches an
eMail inbox via IMAP for incoming reminders from configured users and sends
them back to the sender on a given time. Date and time are set in the messages'
subject.

Reminder is a PHP-script, that must be run from time to time, for example by
a cronjob. All information is extracted from the eMails. No additional
database is needed.


How To Use
----------
Let's say, You want to create a reminder for November 9th 2014 and reminder is
configured to listen on ``reminder@host.tld``. Then write an eMail to
``reminder@host.tld`` containing the subject `2014-11-09: My reminder message`
The eMail body may contain arbitrary data. Depending on when the script
is executed it will send You back the reminder message if the script is run on
the given date or later. After sending the reminder message, the eMail will be
deleted to avoid double posts.


Environment
-----------
- eMail inbox with IMAP support
- PHP at unknown version with IMAP support. I guess, all version work.
- a mechanism that invokes the reminder script periodically, e.g. cron


Installation
------------
- rename ``conf/config.php.dist`` to ``conf/conig.php`` (server configuration)
- rename ``conf/users.php.dist`` to ``conf/users.php`` (user configuration)
- edit the ``.htaccess`` file and (maybe) add an authentication file


To Do
----------------
- create a regression test environment to run automated tests
- implement mutual exclusion based on directory locks to avoid concurrent run
- implement different reminder date formats
- add support for error responses, e.g. "Wrong date format"
- send erorr messages to an administrator, if something goes wrong
- add support for pgp signed messages
- update reminders via eMail
- add translation
- send reminders via SMS
- webinterface to edit reminders online

License
-------
GPL 2 (http://www.gnu.org/licenses/gpl.html)


Contributors
------------
- Richard Weickelt (richard@weickelt.de)

