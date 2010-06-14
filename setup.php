<?php

/* Base page */
$data = Jojo::selectRow("SELECT * FROM {page}  WHERE pg_link='Jojo_Plugin_Jojo_newsletter'");
if (!count($data)) {
    echo "Jojo_Plugin_Jojo_newsletter: Adding <b>Articles</b> Page to menu<br />";
    Jojo::insertQuery("INSERT INTO {page} SET pg_title='Newsletter', pg_link='Jojo_Plugin_Jojo_newsletter', pg_url='newsletter', pg_mainnav='no'");
}

/* Edit Newsletters */
$data = Jojo::selectRow("SELECT * FROM {page}  WHERE pg_url='admin/newsletters'");
if (!count($data)) {
    echo "Jojo_Plugin_Jojo_newsletter: Adding <b>Edit Newsletters</b> Page to menu<br />";
    Jojo::insertQuery("INSERT INTO {page} SET pg_title='Edit Newsletters', pg_link='Jojo_Plugin_Jojo_Newsletter_admin', pg_url='admin/newsletters', pg_parent=?, pg_order=4", array($_ADMIN_CONTENT_ID));
}