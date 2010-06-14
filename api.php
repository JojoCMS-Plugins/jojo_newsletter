<?php
$_provides['pluginClasses'] = array(
        'Jojo_Plugin_Jojo_Newsletter' => 'Jojo Newsletter - frontend pages',
        'Jojo_Plugin_Jojo_Newsletter_admin' => 'Jojo Newsletter - Admin page'
        );

Jojo::registerUri(_ADMIN.'/newsletters/[action:string]/[data:string]',                                           'Jojo_Plugin_jojo_newsletter_admin'); //admin/newsletters/edit/1234/
Jojo::registerUri(_ADMIN.'/newsletters/[action:string]',                                                         'Jojo_Plugin_jojo_newsletter_admin'); //admin/newsletters/new/
Jojo::registerUri(Jojo_Plugin_jojo_newsletter::getPrefix().'/[action:unsubscribe]/[actioncode:[a-zA-Z0-9]{10}]', 'Jojo_Plugin_jojo_newsletter'); //newsletter/unsubscribe/wxmmWlMv9y/
Jojo::registerUri(Jojo_Plugin_jojo_newsletter::getPrefix().'/[action:activate]/[actioncode:[a-zA-Z0-9]{10}]',    'Jojo_Plugin_jojo_newsletter'); //newsletter/activate/wxmmWlMv9y/
Jojo::registerUri(Jojo_Plugin_jojo_newsletter::getPrefix().'/[action:subscribe]/[groupid:integer]',              'Jojo_Plugin_jojo_newsletter'); //newsletter/subscribe/123/
Jojo::registerUri(Jojo_Plugin_jojo_newsletter::getPrefix().'/[action:string]',                                   'Jojo_Plugin_jojo_newsletter'); //newsletter/subscribe/

$_options[] = array(
    'id'          => 'newsletter_noreply_address',
    'category'    => 'Newsletter',
    'label'       => 'No-reply address',
    'description' => 'An email address that can be used for sending out newsletters',
    'type'        => 'text',
    'default'     => '',
    'options'     => '',
    'plugin'      => 'jojo_newsletter'
);

$_options[] = array(
    'id'          => 'newsletter_batch_size',
    'category'    => 'Newsletter',
    'label'       => 'Batch size',
    'description' => 'The number of emails to send each time the cron job runs. To send mail out faster, increase this number or run the cron job more often.',
    'type'        => 'integer',
    'default'     => '10',
    'options'     => '',
    'plugin'      => 'jojo_newsletter'
);
