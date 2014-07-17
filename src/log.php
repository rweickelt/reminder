<?php

/*!
Logging and debug functionality.

@license    GPL 2 (http://www.gnu.org/licenses/gpl.html)

*/

if(!defined('SRC_DIR')) die('meh.');

$time_start = microtime(true);

function dbg($msg, $hidden = false)
{
  global $time_start;

  (!$hidden) ? print '<pre class="dbg">' : print "<!--\n";
  $time_exec = round(microtime(true) - $time_start, 3);
  print($time_exec.": ");
  print_r($msg);
  (!$hidden) ? print '</pre>' : print "\n-->";
  flush();
}


