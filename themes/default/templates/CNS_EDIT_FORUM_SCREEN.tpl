{TITLE}

<p>
	{!CHOOSE_FORUM_EDIT}
</p>

<form title="{!PRIMARY_PAGE_FORM}" method="post" action="{REORDER_URL*}">
	{$INSERT_SPAMMER_BLACKHOLE}

	{ROOT_FORUM}

	{+START,IF_NON_EMPTY,{REORDER_URL}}
		<p class="proceed_button">
			<input accesskey="u" onclick="disable_button_just_clicked(this);" class="buttons__proceed button_screen" type="submit" value="{!REORDER_FORUMS}" />
		</p>
	{+END}
</form>

<div class="box box___edit_forum_screen"><div class="box_inner help_jumpout">
	<p>
		{!CHOOSE_FORUM_EDIT_2}
	</p>
</div></div>
