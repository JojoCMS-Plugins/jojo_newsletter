<?php

$table = 'newsletter_subscription';
$query = "
    CREATE TABLE {newsletter_subscription} (
      `subscriberid` int(11) NOT NULL,
      `groupid` int(11) NOT NULL,
      `active` enum('yes','no') NOT NULL DEFAULT 'no',
      `actioncode` varchar(20) NOT NULL,
      PRIMARY KEY  (`subscriberid`, `groupid`)
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