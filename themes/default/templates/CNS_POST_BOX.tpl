<div class="cns_post_box">
	<div class="wide_table_wrap"><div class="wide_table cns_topic">
		<div>
			{POST}
		</div>
	</div></div>

	{+START,IF_PASSED,BREADCRUMBS}
		<nav class="breadcrumbs" itemprop="breadcrumb" role="navigation"><p>
			{!LOCATED_IN,{BREADCRUMBS}}
		</p></nav>

		{+START,IF_PASSED,URL}
			<p class="shunted_button">
				<a class="buttons__more button_screen_item" href="{URL*}" title="{!FORUM_POST} #{ID*}"><span>{!VIEW}</span></a>
			</p>
		{+END}
	{+END}
</div>
