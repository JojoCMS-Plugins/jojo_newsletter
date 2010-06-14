<?php
class Jojo_Plugin_jojo_newsletter extends Jojo_Plugin
{
    function _getContent()
    {
        global $smarty, $_USERID;
        $content = array();
        $smarty->assign('newsletter_prefix', self::getPrefix());
        
        /* include the required classes */
        foreach (Jojo::listPlugins('classes/newsletter_subscriber.class.php') as $pluginfile) {
            require_once($pluginfile);
            break;
        }
        
        $action     = Jojo::getFormData('action', false);
        $groupid    = Jojo::getFormData('groupid', false);
        $actioncode = Jojo::getFormData('actioncode', false);

        /* the subscribe page */
        if ($action == 'subscribe') {
            $subscribe = Jojo::getFormData('subscribe', false);
            /* form has been submitted */
            if ($subscribe) {
                $result = false;
                if (!empty($_USERID)) {
                    /* subscribing as a registered user - no need for double opt-in */
                    $subscriber = new newsletter_subscriber($_USERID);
                    $result = $subscriber->subscribe($groupid, false);
                } else {
                    /* subscribing as a casual visitor */
                    $firstname = Jojo::getFormData('firstname', false);
                    $email     = Jojo::getFormData('email', false);
                    $errors = array();
                    if (empty($firstname)) $errors[] = 'Please enter your first name';
                    if (empty($email)) $errors[] = 'Please enter your email address';
                    if (!empty($email) && !Jojo::checkEmailFormat($email)) $errors[] = 'Please enter a valid email address';
                    if (!count($errors)) {
                        $subscriber = new newsletter_subscriber($email, $firstname);
                        $result = $subscriber->subscribe($groupid);
                        
                    } else {
                        $smarty->assign('errors',     $errors);
                    }
                }
                
                if ($result) {
                    $content['title']     = 'Subscribe successful';
                    $content['seotitle']  = 'Subscribe successful';
                    $content['content']   = $smarty->fetch('jojo_newsletter_subscribed.tpl');
                    return $content;
                }
            }
            
            /* display subscribe form */
            $content['title']     = 'Subscribe to Newsletter';
            $content['seotitle']  = 'Subscribe to Newsletter';
            
            if (!empty($_USERID)) {
                $subscriber = new newsletter_subscriber($_USERID);
                if ($subscriber->is_subscribed($groupid)) {
                    $content['content']  = '<p>You are already subscribed to this newsletter.</p>';
                    return $content;
                }
                $user = Jojo::selectRow("SELECT us_firstname, us_lastname, us_email, userid FROM {user} WHERE userid=? LIMIT 1", $_USERID);
                $smarty->assign('user', $user);
            }
            $smarty->assign('groupid', $groupid);
            
            $content['content']  = $smarty->fetch('jojo_newsletter_subscribe.tpl');
            return $content;
        }
        
        /* unsubscribe */
        if ($action == 'unsubscribe') {
            $errors = array();
            $subscriber = new newsletter_subscriber($actioncode);
            $smarty->assign('firstname',  $subscriber->firstname);
            $smarty->assign('email',      $subscriber->email);
            $smarty->assign('actioncode', $actioncode);
            
            /* form has been submitted */
            $unsubscribe = Jojo::getFormData('unsubscribe', false);
            if ($unsubscribe) {
                $result = $subscriber->unsubscribe($actioncode);
                if ($result) {
                    $content['title']     = 'Unsubscribe successful';
                    $content['seotitle']  = 'Unsubscribe successful';
                    $content['content']   = $smarty->fetch('jojo_newsletter_unsubscribed.tpl');
                    return $content;
                } else {
                    $errors[] = 'There was an error unsubscribing you from this newsletter, you may have already been unsubscribed.';
                }
            }
            
            $smarty->assign('errors',     $errors);
            
            $content['title']     = 'Unsubscribe';
            $content['seotitle']  = 'Unsubscribe';
            $content['content']   = $smarty->fetch('jojo_newsletter_unsubscribe.tpl');
            return $content;
        }
        
        /* activate */
        if ($action == 'activate') {
            $errors = array();
            $subscriber = new newsletter_subscriber($actioncode);
            $smarty->assign('firstname',  $subscriber->firstname);
            $smarty->assign('email',      $subscriber->email);
            $smarty->assign('actioncode', $actioncode);
            
            $result = $subscriber->activate($actioncode);
            if ($result) {
                    $content['title']     = 'Unsubscribe successful';
                    $content['seotitle']  = 'Unsubscribe successful';
                    $content['content']   = $smarty->fetch('jojo_newsletter_activated.tpl');
                    return $content;
                } else {
                    $errors[] = 'There was an error activating this subscription, the link may have expired or the subscription may already be active.';
                }
            
            $smarty->assign('errors',     $errors);
            
            $content['title']     = 'Activate subscription';
            $content['seotitle']  = 'Activate subscription';
            $content['content']   = $smarty->fetch('jojo_newsletter_error.tpl');
            return $content;
        }

        $content['content']  = '';
        return $content;
    }
    
    /* returns the HTML for a signup form. TODO: Make group id optional for when there is only ever to be one newsletter */
    static function getSignupForm($groupid, $template='sidebar')
    {
        global $smarty, $_USERID;
        
        /* this ensures a unique ID for all DOM elements if multiple forms are used */
        static $formid;
        if (!isset($formid)) {
            $formid = 1;
        } else {
            $formid++;
        }
        
        if (!empty($_USERID)) {
            /* include the required classes */
            foreach (Jojo::listPlugins('classes/newsletter_subscriber.class.php') as $pluginfile) {
                require_once($pluginfile);
                break;
            }
            $subscriber = new newsletter_subscriber($_USERID);
            if ($subscriber->is_subscribed($groupid)) return false; //don't show the form to logged-in users who have already subscribed
        }
        
        $smarty->assign('newsletter_prefix', self::getPrefix());
        $smarty->assign('formid', $formid);
        $smarty->assign('groupid', $groupid);
        
        /* other templates available soon */
        if ($template == 'sidebar') {
            return $smarty->fetch('jojo_newsletter_sidebar.tpl');
        }
        return false;
    }
    
    function getPrefix()
    {
        return 'newsletter';
    }

    function getCorrectUrl()
    {
        //Assume the URL is correct
        return _PROTOCOL.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

}