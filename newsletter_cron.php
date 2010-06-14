<?php
include('config.php');
$f = fopen(_SITEURL.'/external/newsletter_cron.php', 'r');
fclose($f);