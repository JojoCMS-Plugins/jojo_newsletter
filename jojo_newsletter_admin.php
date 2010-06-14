<?php

class jojo_plugin_jojo_newsletter_admin extends JOJO_Plugin
{
    function _getContent()
    {
        global $smarty, $_USERID;
        $content = array();
        jojo_plugin_Admin::adminMenu();
        
        /* include the required classes */
        foreach (Jojo::listPlugins('classes/newsletter_message.class.php') as $pluginfile) {
            require_once($pluginfile);
            break;
        }
               
        $action    = Jojo::getFormData('action',    false);
        $save      = Jojo::getFormData('save',      false);
        $send      = Jojo::getFormData('send',      false);
        $test      = Jojo::getFormData('test',      false);
        $messageid = Jojo::getFormData('messageid', false);
        $preview   = Jojo::getFormData('preview',   false);
       
        //exit;
        /* preview */
        if ($preview) {
            Jojo::redirect(_SITEURL.'/'._ADMIN.'/newsletters/preview/'.$messageid.'/'); //redirect to the correct preview URL
        }
        
        if ($action == 'preview') {
            $messageid   = Jojo::getFormData('data', false);
            $message = new newsletter_message($messageid);
            $html = $message->render('html');
            $smarty->assign('messageid', $messageid);
            $smarty->assign('html', $html);
            $content['content'] = $smarty->fetch('jojo_newsletter_admin_preview.tpl');
            echo $content['content'];
            exit;
            //return $content;
        }
        
        /* save */
        if ($save) {
            $message = new newsletter_message($messageid);
            $fields = array('name', 'from', 'subject', 'bodytext', 'bodyhtml', 'datetime', 'groupid', 'template', 'publish');
            foreach ($fields as $f) {
                $v = Jojo::getFormData($f, false);
                if ($v !== false) $message->setValue($f, $v);
            }
            $message->saveToDb();
            Jojo::redirect(_SITEURL.'/'._ADMIN.'/newsletters/edit/'.$message->messageid.'/'); //now that the record is saved, we are editing it
        }
        
        /* send */
        if ($send) {
            $message = new newsletter_message($messageid);
            $message->queue();
            Jojo::redirect(_SITEURL.'/'._ADMIN.'/newsletters/edit/'.$message->messageid.'/');
        }
        
        /* test */
        if ($test) {
            /* get logged-in user's email address */
            $user = Jojo::selectRow("SELECT us_email, us_firstname FROM {user} WHERE userid=?", $_USERID);
            
            $message = new newsletter_message($messageid);
            $message->test($user['us_email'], $user['us_firstname']);
            Jojo::redirect(_SITEURL.'/'._ADMIN.'/newsletters/edit/'.$message->messageid.'/');
        }
        
        /* new / edit */
        if (($action == 'new') || ($action == 'edit')) {
            $messageid   = Jojo::getFormData('data', false);
            $message = new newsletter_message($messageid);
            $groups = Jojo::selectQuery("SELECT * FROM {newsletter_group} ORDER BY name");
            $smarty->assign('groups', $groups);
            
            $smarty->assign('message', $message->asArray());
            $content['content'] = $smarty->fetch('jojo_newsletter_admin_edit.tpl');
            return $content;
        }
        
        /* Index page */
        $messages = Jojo::selectQuery("SELECT * FROM {newsletter_message} WHERE 1 ORDER BY datetime DESC");
        $smarty->assign('messages', $messages);
        
        $content['content'] = $smarty->fetch('jojo_newsletter_admin.tpl');

        return $content;
    }
    
    function getCorrectUrl()
    {
        //Assume the URL is correct
        return _PROTOCOL.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }
}
