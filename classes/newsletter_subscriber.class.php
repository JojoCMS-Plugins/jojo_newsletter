<?php

class newsletter_subscriber {
    var $subscriberid;
    var $userid;
    var $firstname;
    var $email;
    
    function newsletter_subscriber ($id_or_email_or_actioncode, $firstname=false, $subscriberid=false)
    {
        if ($subscriberid) {
            /* create from existing subscriber record (from subscriber id) */
            $subscriber   = Jojo::selectRow("SELECT * FROM {newsletter_subscriber} WHERE subscriberid=? LIMIT 1", $subscriberid);
            $this->subscriberid = $subscriberid;
            if (!empty($subscriber['userid'])) {
                /* registered user */
                $this->userid       = $subscriber['userid'];
                $user = Jojo::selectRow("SELECT us_firstname, us_lastname, us_email, userid FROM {user} WHERE userid=? LIMIT 1", $this->userid);
                $this->firstname    = $user['us_firstname'];
                $this->email        = $user['us_email'];
            } else {
                /* casual subscriber */
                $this->userid       = false;
                $this->firstname    = $subscriber['firstname'];
                $this->email         = $subscriber['email'];
            }
        } elseif (is_numeric($id_or_email_or_actioncode) && !empty($id_or_email_or_actioncode)) {
            /* registered user */
            $user = Jojo::selectRow("SELECT us_firstname, us_lastname, us_email, userid FROM {user} WHERE userid=? LIMIT 1", $id_or_email_or_actioncode);
            $this->userid    = $user['userid'];
            $this->firstname = $user['us_firstname'];
            $this->email     = $user['us_email'];
            
            /* is this an existing subscriber? */
            $subscriber = Jojo::selectRow("SELECT * FROM {newsletter_subscriber} WHERE userid=? LIMIT 1", $user['userid']);
            if (!count($subscriber)) {
                $this->subscriberid = Jojo::insertQuery("INSERT INTO {newsletter_subscriber} SET userid=?", $user['userid']);
            } else {
                $this->subscriberid = $subscriber['subscriberid'];
            }
        } elseif (preg_match('/\\A[a-zA-Z0-9]{10}\\z/', $id_or_email_or_actioncode)) {
            /* create from actioncode */
            $subscription = Jojo::selectRow("SELECT subscriberid FROM {newsletter_subscription} WHERE actioncode=? LIMIT 1", $id_or_email_or_actioncode); //use the actioncode to find the subscriberid
            $subscriber   = Jojo::selectRow("SELECT * FROM {newsletter_subscriber} WHERE subscriberid=? LIMIT 1", $subscription['subscriberid']);
            $this->subscriberid = $subscriber['subscriberid'];
            if (!empty($subscriber['userid'])) {
                /* registered user */
                $this->userid       = $subscriber['userid'];
                $user = Jojo::selectRow("SELECT us_firstname, us_lastname, us_email, userid FROM {user} WHERE userid=? LIMIT 1", $this->userid);
                $this->firstname    = $user['us_firstname'];
                $this->email        = $user['us_email'];
            } else {
                /* casual subscriber */
                $this->userid       = false;
                $this->firstname    = $subscriber['firstname'];
                $this->email         = $subscriber['email'];
            }

        } else {
            /* casual subscriber */
            $this->userid    = false;
            $this->firstname = $firstname;
            $this->email     = $id_or_email_or_actioncode;
            
            /* is this an existing subscriber? */
            $subscriber = Jojo::selectRow("SELECT * FROM {newsletter_subscriber} WHERE email=? LIMIT 1", $this->email);
            if (!count($subscriber)) {
                $this->subscriberid = Jojo::insertQuery("INSERT INTO {newsletter_subscriber} SET userid=0, email=?, firstname=?", array($this->email, $this->firstname));
            } else {
                $this->subscriberid = $subscriber['subscriberid'];
            }
        }
        /* ensure email is valid */
        if (!Jojo::checkEmailFormat($this->email)) return false;
        /* uppercase the first letter of their first name */
        $this->firstname = ucfirst($this->firstname);
        return true;
    }
    
    function getUnsubscribeUrl($groupid)
    {
        if (!$this->is_subscribed($groupid)) return false; //make sure they are subscribed to the group
        $data = Jojo::selectRow("SELECT * FROM {newsletter_subscription} WHERE subscriberid=? AND groupid=?", array($this->subscriberid, $groupid));
        if (!count($data)) return false;
        return _SITEURL.'/'.Jojo_Plugin_jojo_newsletter::getPrefix().'/unsubscribe/'.$data['actioncode'].'/';
    }
    
    function subscribe($groupid, $activation_required=true, $welcome_email=true)
    {
        global $smarty;
        
        /* generate unique action code */
        $unique = false;
        do {
            $actioncode = Jojo::randomString(10);
            $data = Jojo::selectQuery("SELECT * FROM {newsletter_subscription} WHERE actioncode=?", $actioncode);
            if (!count($data)) $unique = true;
        } while (!$unique);
        
        $active = ($activation_required) ? 'no' : 'yes';
        
        /* save to DB */
        Jojo::insertQuery("REPLACE INTO {newsletter_subscription} SET subscriberid=?, groupid=?, actioncode=?, active=?", array($this->subscriberid, $groupid, $actioncode, $active));
        /* email a confirmation of their subscription */
        global $smarty;
        $smarty->assign('firstname', $this->firstname);
        $smarty->assign('email', $this->email);
        $smarty->assign('actioncode', $actioncode);
        $smarty->assign('activate_link', _SITEURL.'/'.Jojo_Plugin_jojo_newsletter::getPrefix().'/activate/'.$actioncode.'/');
        $smarty->assign('unsubscribe_link', _SITEURL.'/'.Jojo_Plugin_jojo_newsletter::getPrefix().'/unsubscribe/'.$actioncode.'/');
        //$smarty->assign('cancel_link', _SITEURL.'/'.Jojo_Plugin_jojo_newsletter::getPrefix().'/cancel/'.$actioncode.'/');
        
        if ($activation_required) {
            $message = array(
                            'to_name'  => $this->firstname,
                            'to_email' => $this->email,
                            'subject'  => 'Activation required for '.Jojo::getOption('sitetitle'),
                            'message'  => $smarty->fetch('jojo_newsletter_activation_email.tpl'),
                            );
            Jojo::simpleMail($message['to_name'], $message['to_email'], $message['subject'], $message['message']);
        } elseif ($welcome_email) {
            $message = array(
                            'to_name'  => $this->firstname,
                            'to_email' => $this->email,
                            'subject'  => 'Subscription successful',
                            'message'  => $smarty->fetch('jojo_newsletter_welcome_email.tpl'),
                            );
            Jojo::simpleMail($message['to_name'], $message['to_email'], $message['subject'], $message['message']);
        }
        return true; //subscription successful
    }
    
    function is_subscribed($groupid)
    {
        $subscription = Jojo::selectRow("SELECT * FROM newsletter_subscription WHERE active='yes' AND groupid=? AND subscriberid=?", array($groupid, $this->subscriberid));
        if (count($subscription)) return true;
        return false;
    }
    
    function unsubscribe($groupid_or_actioncode)
    {
        global $smarty;
        if (is_numeric($groupid_or_actioncode)) {
            /* groupid */
            $numrows = Jojo::updateQuery("UPDATE {newsletter_subscription} SET active='no' WHERE subscriberid=? AND groupid=? LIMIT 1", array($this->subscriberid, $groupid_or_actioncode));
        } else {
            /* actioncode */
            $numrows = Jojo::updateQuery("UPDATE {newsletter_subscription} SET active='no' WHERE subscriberid=? AND actioncode=? LIMIT 1", array($this->subscriberid, $groupid_or_actioncode));
        }
        if ($numrows) return true; //unsubscribe successful
        return false; //no rows affected - perhaps already unsubscribed?
    }
    
    function activate($groupid_or_actioncode)
    {
        global $smarty;
        if (is_numeric($groupid_or_actioncode)) {
            /* groupid */
            $numrows = Jojo::updateQuery("UPDATE {newsletter_subscription} SET active='yes' WHERE subscriberid=? AND groupid=? LIMIT 1", array($this->subscriberid, $groupid_or_actioncode));
            $subscription = Jojo::selectRow("SELECT actioncode FROM {newsletter_subscription} WHERE  subscriberid=? AND groupid=? LIMIT 1");
            $actioncode = $subscription['actioncode'];
        } else {
            /* actioncode */
            $numrows = Jojo::updateQuery("UPDATE {newsletter_subscription} SET active='yes' WHERE subscriberid=? AND actioncode=? LIMIT 1", array($this->subscriberid, $groupid_or_actioncode));
            $actioncode = $groupid_or_actioncode;
        }

        if ($numrows) {
            /* send welcome email */
            $smarty->assign('unsubscribe_link', _SITEURL.'/'.Jojo_Plugin_jojo_newsletter::getPrefix().'/unsubscribe/'.$actioncode.'/');
            $message = array(
                            'to_name'  => $this->firstname,
                            'to_email' => $this->email,
                            'subject'  => 'Subscription successful',
                            'message'  => $smarty->fetch('jojo_newsletter_welcome_email.tpl'),
                            );
            Jojo::simpleMail($message['to_name'], $message['to_email'], $message['subject'], $message['message']);
            return true; //activation successful
        }
        return false; //no rows affected
    }
}