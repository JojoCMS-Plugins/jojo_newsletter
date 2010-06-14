<?php

$table = 'newsletter_subscriber';
$query = "
    CREATE TABLE {newsletter_subscriber} (
      `subscriberid` int(11) NOT NULL auto_increment,
      `email` varchar(255) NOT NULL DEFAULT '',
      `firstname` varchar(255) NOT NULL DEFAULT '',
      `userid` int(11) NOT NULL DEFAULT 0,
      PRIMARY KEY  (`subscriberid`)
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