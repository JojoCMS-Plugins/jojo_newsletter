<?php

class newsletter_message {
    var $messageid;
    var $mail;
    var $data = array();
    /*
    CREATE TABLE {newsletter_message} (
      `messageid` int(11) NOT NULL auto_increment,
      `name` varchar(255) NOT NULL,
      `subject` varchar(255) NOT NULL,
      `bodytext` text NOT NULL,
      `bodyhtml` text NOT NULL,
      `datetime` int(11) NOT NULL,
      `sendafter` int(11) NOT NULL,
      `status` enum('draft','queued', 'sending', 'sent') NOT NULL,
      PRIMARY KEY  (`messageid`)
    ) TYPE=MyISAM;";
    */
    
    function newsletter_message($messageid=false)
    {
        $this->messageid = $messageid;
        if (!empty($this->messageid)) {
            $this->data = Jojo::selectRow("SELECT * FROM {newsletter_message} WHERE messageid=? LIMIT 1", $this->messageid);
            if (!count($this->data)) return false;
        } else {
            $this->data = array('messageid' => 0,
                                'name'     => 'New message',
                                'from'     => _SITETITLE,
                                'subject'  => '',
                                'bodytext' => '',
                                'bodyhtml' => '',
                                'groupid'  => 0,
                                'datetime' => time(),
                                'status'   => 'draft',
                                //'' => '',
                               );
        }
        return true;
    }
    
    function asArray()
    {
        return $this->data;
    }
    
    function setValue($var, $value)
    {
        if (($var == 'datetime') && (!is_numeric($value))) {
            $this->data[$var] = Jojo::strtotimeUk($value);
        } else {
            $this->data[$var] = $value;
        }
        //echo $value;
        return true;
    }
    
    function saveToDb()
    {
        $fields_str = '';
        $values = array();
        foreach ($this->data as $k => $v) {
            $fields_str .= "`$k`=?,";
            $values[] = $v;
        }
        $fields_str = rtrim($fields_str, ',');
        if (!empty($this->messageid)) {
            $query = "UPDATE {newsletter_message} SET ".$fields_str." WHERE messageid=".Jojo::cleanInt($this->messageid);
            Jojo::updateQuery($query, $values);
        } else {
            $query = "INSERT INTO {newsletter_message} SET ".$fields_str;
            //echo $query;
            $this->messageid = Jojo::insertQuery($query, $values);
        }
    }
    
    function render($format='html', $subscriberid=false)
    {
        global $smarty;
        $message = $this->asArray();
        
        if (!$subscriberid) {
            $firstname = 'Test';
            $unsubscribe_link = _SITEURL.'/'.Jojo_Plugin_jojo_newsletter::getPrefix().'/unsubscribe/xxxxxxxxxx/';
        } else {
            $subscriber = new newsletter_subscriber(false, false, $subscriberid);
            $firstname = $subscriber->firstname;
            $unsubscribe_link = $subscriber->getUnsubscribeUrl($this->data['groupid']);
        }
        
        /* replace [[firstname]] variable */
        $message['bodytext'] = str_replace('[[firstname]]', $firstname, $message['bodytext']);
        $message['bodyhtml'] = str_replace('[[firstname]]', $firstname, $message['bodyhtml']);
        
        
        
        $smarty->assign('message', $message);
        $smarty->assign('unsubscribe_link', $unsubscribe_link);
        $template = (!empty($this->data['template'])) ? $this->data['template'] : 'jojo_newsletter_default_template_'.$format.'.tpl';
        
        
        
        if ($format == 'text') {
            $text_template = str_replace('html', 'text', $template);
            if ($smarty->template_exists($text_template)) {
                $template = $text_template;
            } else {
                $template = 'jojo_newsletter_default_template_text.tpl';
            }
        }
        
        $rendered = $smarty->fetch($template);
        
        /* make images absolute */
        $rendered = preg_replace_callback('/(<img .*src *= *")(.*?)(")/', create_function('$matches', 'if (strpos($matches[2], "http://")!==false) {return $matches[0];} else {return $matches[1]._SITEURL."/".$matches[2].$matches[3];}'), $rendered);
        $rendered = preg_replace_callback('/(<img .*src *= *\')(.*?)(\')/', create_function('$matches', 'if (strpos($matches[2], "http://")!==false) {return $matches[0];} else {return $matches[1]._SITEURL."/".$matches[2].$matches[3];}'), $rendered);
        
        return $rendered;
    }
    
    function getNumSubscribers()
    {
    
    }
    
    function queue()
    {
        if (empty($this->data['groupid'])) return false;
        $num_queued = 0;
        $subscriptions = Jojo::selectQuery("SELECT * FROM {newsletter_subscription} WHERE active='yes' AND groupid=?", $this->data['groupid']);
        foreach ($subscriptions as $subscription) {
            $subscriber = new newsletter_subscriber(false, false, $subscription['subscriberid']);
            $data = Jojo::selectRow("SELECT * FROM {newsletter_queue} WHERE subscriberid=? AND messageid=?", array($subscriber->subscriberid, $this->messageid));//don't send the same message twice
            if (!count($data)) {
                Jojo::insertQuery("INSERT INTO {newsletter_queue} SET subscriberid=?, messageid=?, email=?, queued=?, status='queued'", array($subscriber->subscriberid, $this->messageid, $subscriber->email, time()));
                $num_queued++;
            }
        }

        $this->data['status'] = 'queued';
        $this->saveToDb();
        return $num_queued;
    }
    
    function test($email, $firstname='')
    {
        $to_name    = (!empty($firstname)) ? $firstname : 'Test';
        $to_email   = $email;
        $from_name  = $this->data['from'];
        $from_email = Jojo::getOption('newsletter_noreply_address', _WEBMASTERADDRESS);
        $html       = $this->render('html', false);
        $text       = $this->render('text', false);
        $subject    = $this->data['subject'];
        
        $this->mail = new htmlMimeMail();
        $html = preg_replace_callback('/(<img .*src *= *")(.*?)(")/', array(&$this, 'i_am_a_regex_callback'), $html);
    	
        $this->mail->setHtml($html, $text);
        $this->mail->setFrom('"'.$from_name.'" <'.$from_email.'>');
    	$this->mail->setSubject($subject);
    	
    	$result = $this->mail->send(array($to_email));
    	return $result;
    }
    
    function send($subscriberid)
    {
     
        /* include subscriber class */
        foreach (Jojo::listPlugins('classes/newsletter_subscriber.class.php') as $pluginfile) {
            require_once($pluginfile);
            break;
        }
        
        foreach (Jojo::listPlugins('external/mimemail/htmlMimeMail.php') as $pluginfile) {
            require_once($pluginfile);
            break;
        }
        
        $subscriber = new newsletter_subscriber(false, false, $subscriberid);
        
        /* confirm they are still subscribed */
        if (!$subscriber->is_subscribed($this->data['groupid'])) {
            Jojo::updateQuery("UPDATE {newsletter_queue} SET status='cancelled' WHERE subscriberid=? AND messageid=?", array($subscriber->subscriberid, $this->messageid));
            return false;
        }
        
        /* send */
        Jojo::updateQuery("UPDATE {newsletter_queue} SET status='sending' WHERE subscriberid=? AND messageid=?", array($subscriber->subscriberid, $this->messageid));
        $to_name    = $subscriber->firstname;
        $to_email   = $subscriber->email;
        $from_name  = $this->data['from'];
        $from_email = Jojo::getOption('newsletter_noreply_address', _WEBMASTERADDRESS);
        $html       = $this->render('html', $subscriber->subscriberid);
        $text       = $this->render('text', $subscriber->subscriberid);
        $subject    = $this->data['subject'];
        
        
        $this->mail = new htmlMimeMail();
        
        /* embed images */
        $html = preg_replace_callback('/(<img .*src *= *")(.*?)(")/', array(&$this, 'i_am_a_regex_callback'), $html);
    	
        $this->mail->setHtml($html, $text);
        $this->mail->setFrom('"'.$from_name.'" <'.$from_email.'>');
    	$this->mail->setSubject($subject);
    	
    	$result = $this->mail->send(array($to_email)); 
    	if ($result) {
    	    Jojo::updateQuery("UPDATE {newsletter_queue} SET status='sent' WHERE subscriberid=? AND messageid=?", array($subscriber->subscriberid, $this->messageid));
    	    return true;
    	} else {
    	    Jojo::updateQuery("UPDATE {newsletter_queue} SET status='error' WHERE subscriberid=? AND messageid=?", array($subscriber->subscriberid, $this->messageid));
    	    return false;
    	}
    }
    
    function i_am_a_regex_callback($matches)
    {
        $url = $matches[2];
        $cache = imageCache($url);
        $image = $this->mail->getFile($cache);
        $this->mail->addHtmlImage($image, basename($url), Jojo::getMimeType($cache));
        return $matches[1].basename($url).$matches[3];
    }
    
    static function updateMessageStatus()
    {
        $messages = Jojo::selectQuery("SELECT * FROM {newsletter_message} WHERE status='queued'");
        foreach ($messages as $k => $message) {
            $queued = Jojo::selectQuery("SELECT * FROM {newsletter_queue} WHERE messageid=? AND status='queued'", $message['messageid']);
            $sent = Jojo::selectQuery("SELECT * FROM {newsletter_queue} WHERE messageid=? AND status='sent'", $message['messageid']);
            $errors = Jojo::selectQuery("SELECT * FROM {newsletter_queue} WHERE messageid=? AND (status='error' OR status='sending')", $message['messageid']);
            Jojo::updateQuery("UPDATE {newsletter_message} SET numqueued=?, numsent=? WHERE messageid=?", array(count($queued), count($sent), $message['messageid']));
            if ((count($queued) == 0) && (count($sent) > 0)) {
                Jojo::updateQuery("UPDATE {newsletter_message} SET status='sent' WHERE messageid=?", array($message['messageid']));
            }
        }
    }
}



/* caches a URL and returns the cached filename. Use for embedding images into emails */
function imageCache($url)
{
    /* create the cache folder if needed */
    if (!file_exists(_CACHEDIR.'/newsletter/')) {
        mkdir(_CACHEDIR.'/newsletter/');
    }
    $filetype = Jojo::getFileExtension(basename($url));
    $cachefile = _CACHEDIR.'/newsletter/'.md5($url).'.'.strtolower($filetype);
    
    /* delete the cache file - TODO: check the timestamp before doing this */
    if (Jojo::fileExists($cachefile)) {
        unlink($cachefile);
    }
    
    /* read file */
    $handle = fopen($url, "r");
    if ($handle) {
        while ($tmp = fread($handle, 1024)) {
            $buffer .= $tmp;
        }
        fclose($handle);
        /* save file */
        file_put_contents($cachefile, $buffer);
        return $cachefile;
    }
    return false;
}