<div>
  {include file='errors.tpl'}
  {if $email}
  <form method="post" action="{$newsletter_prefix}/unsubscribe/{$actioncode}/">
    <input type="hidden" name="actioncode" value="{$actioncode}" />
    <input type="hidden" name="groupid" value="{$groupid}" />
    
    <p>Please confirm your request to unsubscribe from this newsletter:</p>
    <p>First name: {$firstname}<br />
    Email: {$email}</p> 
    
    <input type="submit" class="button" name="unsubscribe" value="Confirm Unsubscribe" />
  </form>
  {else}
  <p>This does not appear to be a valid unsubscribe link, or you may have already been unsubscribed. If you believe this message is in error, please contact the <a href="mailto:{$OPTIONS.webmasteraddress}">webmaster</a>.</p>
  {/if}
</div>