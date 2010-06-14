<div>
  {include file="errors.tpl"}
  <form method="post" action="{$newsletter_prefix}/subscribe/{$groupid}/">
    {if $user.userid}
    <input type="hidden" name="userid" value="{$user.userid}" />
    <p>First name: {$user.us_firstname}<br />
    Email: {$user.us_email}</p>
    {else}
    <label>First name:</label>
    <input type="text" name="firstname" value="" /><br />
  
    <label>Email:</label>
    <input type="text" name="email" value="" /><br />
    {/if}
    <input type="submit" name="subscribe" value="Subscribe to Newsletter" />
  </form>
</div>