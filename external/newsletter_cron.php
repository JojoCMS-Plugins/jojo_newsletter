<?php

/* include the required classes */
foreach (Jojo::listPlugins('classes/newsletter_message.class.php') as $pluginfile) {
    require_once($pluginfile);
    break;
}
 
/* get up to X emails from the queue that aren't paused */
$queue = Jojo::selectQuery("SELECT q.subscriberid, m.messageid FROM {newsletter_queue} q INNER JOIN {newsletter_message} m ON q.messageid=m.messageid WHERE q.status='queued' AND m.status!='paused' ORDER BY queued LIMIT ".Jojo::getOption('newsletter_batch_size', 10));
foreach ($queue as $item) {
    $message = new newsletter_message($item['messageid']);
    $message->send($item['subscriberid']);    
}

/* update message status */
newsletter_message::updateMessageStatus();

echo 'done';
