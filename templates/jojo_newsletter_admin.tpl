{include file="admin/header.tpl"}

<a href="{$ADMIN}/newsletters/new/">Create new message</a><br />

{if $messages}
<ul>
{foreach from=$messages item=m}
<li><a href="{$ADMIN}/newsletters/edit/{$m.messageid}/">{$m.name} - {$m.status}{if $m.status=='queued'}: {$m.numqueued}, sent: {$m.numsent}{/if}</a></li>
{/foreach}
</ul>
{/if}
{include file="admin/footer.tpl"}