<section id="tray_{TITLE|}" class="box box___block_main_forum_topics">
	<h3 class="toggleable_tray_title">
		<a class="toggleable_tray_button" href="#" onclick="return toggleable_tray(this.parentNode.parentNode,false,'{TITLE|}');"><img alt="{!CONTRACT}: {$STRIP_TAGS,{TITLE}}" title="{!CONTRACT}" src="{$IMG*,1x/trays/contract2}" srcset="{$IMG*,2x/trays/contract2} 2x" /></a>

		{+START,IF_NON_EMPTY,{TITLE}}
			<a class="toggleable_tray_button" href="#" onclick="return toggleable_tray(this.parentNode.parentNode,false,'{TITLE|}');">{TITLE}</a>
		{+END}
	</h3>

	<div class="toggleable_tray">
		{CONTENT}

		{+START,IF_NON_EMPTY,{SUBMIT_URL}}
			<ul class="horizontal_links associated_links_block_group force_margin">
				<li><a href="{SUBMIT_URL*}">{!ADD_TOPIC}</a></li>
			</ul>
		{+END}
	</div>
</section>

{+START,IF,{$JS_ON}}
	<script>// <![CDATA[
		handle_tray_cookie_setting('{TITLE|}');
	//]]></script>
{+END}
