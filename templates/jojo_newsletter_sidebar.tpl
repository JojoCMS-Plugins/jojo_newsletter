<!-- subscribe to newsletter -->
<div id="subscribe_sidebar_{$formid}" class="subscribe_sidebar">
<form method="post" action="{$newsletter_prefix}/subscribe/{$groupid}/">
  {if $user.userid}
  <input type="hidden" name="userid" value="{$user.userid}" />
  <p>First name: {$user.us_firstname}<br />
  Email: {$user.us_email}</p>
  {else}
  <label>First name:</label>
  <input type="text" name="firstname" id="subscribe_firstname_{$formid}" value="" /><br />
  
  <label>Email:</label>
  <input type="text" name="email" id="subscribe_email_{$formid}" value="" /><br />
  {/if}
  <input class="button" type="submit" name="subscribe" value="Subscribe to Newsletter" />
</form>
</div>
