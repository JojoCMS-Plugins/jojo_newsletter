<?php

$table = 'newsletter_message';
$query = "
    CREATE TABLE {newsletter_message} (
      `messageid` int(11) NOT NULL auto_increment,
      `name` varchar(255) NOT NULL,
      `from` varchar(255) NOT NULL,
      `subject` varchar(255) NOT NULL,
      `bodytext` text NOT NULL,
      `bodyhtml` text NOT NULL,
      `datetime` int(11) NOT NULL,
      `sendafter` int(11) NOT NULL,
      `template` varchar(255) NOT NULL,
      `groupid` int(11) NOT NULL,
      `status` enum('draft','queued', 'paused', 'sending', 'sent') NOT NULL,
      `numqueued` int(11) NOT NULL DEFAULT 0,
      `numsent` int(11) NOT NULL DEFAULT 0,
      `publish` enum('yes', 'no') NOT NULL,
      PRIMARY KEY  (`messageid`)
    ) TYPE=MyISAM;";

/* Check table structure */
$result = Jojo::checkTable($table, $query);

/* Output result */
if (isset($result['created'])) {
    echo sprintf("jojo_newsletter: Table <b>%s</b> Does not exist - created empty table.<br />", $table);
}

if (isset($result['added'])) {
    foreach ($result['added'] as $col => $v) {
        echo sprintf("jojo_newsletter: Table <b>%s</b> column <b>%s</b> Does not exist - added.<br />", $table, $col);
    }
}

if (isset($result['different'])) Jojo::printTableDifference($table,$result['different']);