<?php

$table = 'newsletter_queue';
$query = "
    CREATE TABLE {newsletter_queue} (
      `subscriberid` int(11) NOT NULL,
      `messageid` int(11) NOT NULL,
      `email` varchar(255) NOT NULL,
      `sendafter` int(11) NOT NULL,
      `queued` int(11) NOT NULL,
      `status` enum('queued', 'sending', 'sent', 'cancelled', 'error') NOT NULL,
      PRIMARY KEY  (`subscriberid`, `messageid`)
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