<ul id="forumReageren">
	{foreach from=$reageren item=react}
		<li class="reagerenLid" title="{ProfielModel::getNaam($react->uid, 'user')} is een reactie aan het schrijven">{icon get=comment_edit} {ProfielModel::getNaam($react->uid, 'user')}</li>
	{foreachelse}
		<li class="reagerenLid"><br/></li>
	{/foreach}
</ul>