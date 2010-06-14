{include file="admin/header.tpl"}

<form method="post" action="">
  <input type="hidden" name="messageid" value="{$message.messageid}" />
  <table>
    <!-- Name -->
    <tr>
      <td>Name:</td>
      <td><input type="text" size="50" name="name" value="{$message.name}" /></td>
    </tr>
    
    <!-- Date / time -->
    <tr>
      <td>Date / time:</td>
      <td><input type="text" size="50" name="datetime" value="{$message.datetime|date_format:'%A, %B %e, %Y %R'}" /></td>
    </tr>
    
    <!-- From -->
    <tr>
      <td>From:</td>
      <td><input type="text" size="50" name="from" value="{$message.from}" /></td>
    </tr>
    
    <!-- Subject -->
    <tr>
      <td>Subject:</td>
      <td><input type="text" size="50" name="subject" value="{$message.subject}" /></td>
    </tr>
    
    <!-- Body HTML -->
    <tr>
      <td>Body HTML</td>
      <td><textarea name="bodyhtml" rows="15" cols="100">{$message.bodyhtml}</textarea></td>
    </tr>
    
    <!-- Body Plain Text -->
    <tr>
      <td>Body Plain Text</td>
      <td><textarea name="bodytext" rows="15" cols="100">{$message.bodytext}</textarea></td>
    </tr>
    
    <!-- Template -->
    <tr>
      <td>Template:</td>
      <td><input type="text" name="template" value="{$message.template}" /> Enter the filename of the .tpl template (HTML version) you will be using. Leave blank for the default.</td>
    </tr>
    
    <!-- Group -->
    <tr>
      <td>Group:</td>
      <td>
        <select name="groupid">
          <option value="0">Select group</option>
          {foreach from=$groups item=g}
          <option value="{$g.groupid}"{if $g.groupid==$message.groupid} selected="selected"{/if}>{$g.name}</option>
          {/foreach}  
        </select>
      </td>
    </tr>
    
    <!-- Publish -->
    <tr>
      <td>Publish:</td>
      <td>
        <select name="publish">
          <option value="no">no</option>
          <option value="yes"{if $message.publish=='yes'} selected="selected"{/if}>yes</option>
        </select> Will this newsletter be added to the archive on the website?
      </td>
    </tr>
    
    <!-- -->
    <tr>
      <td></td>
      <td></td>
    </tr>
  </table>
  <input type="submit" name="save" value="Save" />
  <input type="submit" name="preview" value="Preview" />
  <input type="submit" name="test" value="Test" title="Send a one-off test message to {$foo}" />
  {if $message.name && $message.groupid && $message.bodyhtml && $message.subject}<input type="submit" name="send" value="Send" />{/if}
</form>
{include file="admin/footer.tpl"}