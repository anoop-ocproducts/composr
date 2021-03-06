{TITLE}

{MESSAGE}

<p>{!USE_CHAT_RULES,{$PAGE_LINK*,:rules},{$PAGE_LINK*,:privacy}}</p>

<div class="box box___chat_lobby_screen_rooms box_prominent"><div class="box_inner">
	<h2>{!CHATROOMS_LOBBY_TITLE}</h2>

	<div class="float_surrounder">
		{+START,IF_NON_EMPTY,{ADD_CHATROOM_URL}{PRIVATE_CHATROOM}{BLOCKING_LINK}{MOD_LINK}{SETEFFECTS_LINK}}
			<nav class="chat_actions" role="navigation">
				<h3>{!OTHER_ACTIONS}</h3>

				<ul role="navigation" class="actions_list">
					{+START,IF_NON_EMPTY,{ADD_CHATROOM_URL}}
						<li class="icon_14_add"><a href="{ADD_CHATROOM_URL*}" rel="add">{!ADD_CHATROOM}</a></li>
					{+END}
					{+START,IF_NON_EMPTY,{PRIVATE_CHATROOM}}
						<li class="icon_14_proceed">{PRIVATE_CHATROOM}</li>
					{+END}
					{+START,IF_NON_EMPTY,{BLOCKING_LINK}}
						<li class="icon_14_remove_manage">{BLOCKING_LINK}</li>
					{+END}
					{+START,IF_NON_EMPTY,{MOD_LINK}}
						<li class="icon_14_tools">{MOD_LINK}</li>
					{+END}
					{+START,IF_NON_EMPTY,{SETEFFECTS_LINK}}
						<li class="icon_14_sound_effects">{SETEFFECTS_LINK}</li>
					{+END}
				</ul>
			</nav>
		{+END}

		<div class="chat_rooms">
			<h3>{!SELECT_CHATROOM}</h3>

			{+START,IF_NON_EMPTY,{CHATROOMS}}
				<ul class="actions_list">
					{CHATROOMS}
				</ul>

				<p class="chat_multi_tab">{!OPEN_CHATROOMS_IN_TABS}</p>
			{+END}
			{+START,IF_EMPTY,{CHATROOMS}}
				<p class="nothing_here">{!NO_CATEGORIES}</p>
			{+END}
		</div>
	</div>
</div></div>

{+START,IF,{$NOT,{$IS_GUEST}}}
	<div class="chat_im_convos_wrap">
		<div class="box box___chat_lobby_screen_im box_prominent"><div class="box_inner">
			<h2>{!INSTANT_MESSAGING}</h2>

			<div class="float_surrounder chat_im_convos_inner">
				<div class="chat_lobby_convos">
					<div class="chat_lobby_convos_tabs" id="chat_lobby_convos_tabs" style="display: none"></div>
					<div class="chat_lobby_convos_areas" id="chat_lobby_convos_areas">
						<p class="nothing_here">
							{!NO_IM_CONVERSATIONS}
						</p>
					</div>

					<script> // <![CDATA[
						var im_area_template='{IM_AREA_TEMPLATE;^/}';
						var im_participant_template='{IM_PARTICIPANT_TEMPLATE;^/}';
						var top_window=window;

						function begin_im_chatting()
						{
							window.load_from_room_id=-1;
							if ((window.chat_check) && (window.do_ajax_request)) chat_check(true,0); else window.setTimeout(begin_im_chatting,500);
						}
						begin_im_chatting();
					// ]]></script>
				</div>

				<div class="chat_lobby_friends">
					<h3>{!FRIEND_LIST}</h3>

					{+START,IF_NON_EMPTY,{FRIENDS}}
						<form autocomplete="off" title="{!FRIEND_LIST}" method="post" action="{$?,{$IS_EMPTY,{URL_REMOVE_FRIENDS}},index.php,{URL_REMOVE_FRIENDS*}}">
							<div id="friends_wrap">
								{FRIENDS}
							</div>

							<div class="friend_actions">
								{+START,IF,{CAN_IM}}
									<input class="menu___generic_admin__add_to_category button_screen_item" disabled="disabled" id="invite_ongoing_im_button" type="button" value="{!INVITE_CURRENT_IM}" onclick="var people=get_ticked_people(this.form); if (people) invite_im(people);" />
									<input class="menu__social__chat__chat button_screen_item" type="button" value="{!START_IM}" onclick="var people=get_ticked_people(this.form); if (people) start_im(people);" />
								{+END}
								{+START,IF_NON_EMPTY,{URL_REMOVE_FRIENDS}}
									<input class="menu___generic_admin__delete button_screen_item" type="submit" value="{!DUMP_FRIENDS}" onclick="var people=get_ticked_people(this.form); if (!people) return false; var t=this; window.fauxmodal_confirm('{!Q_SURE=;}',function(result) { if (result) { disable_button_just_clicked(t); click_link(t); } }); return false;" />
								{+END}
							</div>
						</form>
					{+END}

					{+START,IF_NON_EMPTY,{URL_ADD_FRIEND}}
						<p>{!MUST_ADD_CONTACTS}</p>

						<form onsubmit="var _this=this; load_snippet('im_friends_rejig&amp;member_id={MEMBER_ID%}','add='+window.encodeURIComponent(this.elements['friend_username'].value),function(ajax_result) { set_inner_html(document.getElementById('friends_wrap'),ajax_result.responseText); _this.elements['friend_username'].value=''; }); return false;" autocomplete="off" title="{!ADD}: {!FRIEND_LIST}" method="post" action="{URL_ADD_FRIEND*}">
							<label class="accessibility_hidden" for="friend_username">{!USERNAME}: </label>
							<input{+START,IF,{$MOBILE}} autocorrect="off"{+END} autocomplete="off" size="18" maxlength="80" onkeyup="update_ajax_member_list(this,null,false,event);" type="text" onfocus="placeholder_focus(this);" onblur="placeholder_blur(this);" class="field_input_non_filled" value="{!USERNAME}" id="friend_username" name="friend_username" /><input class="menu___generic_admin__add_one button_micro" type="submit" value="{!ADD}" />
						</form>
					{+END}

					<h3 class="chat_lobby_options_header">{!OPTIONS}</h3>

					{CHAT_SOUND}

					<form title="{!SOUND_EFFECTS}" action="index.php" method="post" class="inline sound_effects_form">
						<p>
							<label for="play_sound">{!SOUND_EFFECTS}:</label> <input type="checkbox" id="play_sound" name="play_sound" checked="checked" />
						</p>
					</form>

					<div class="alert_box_wrap" id="alert_box_wrap" style="display: none">
						<section class="box"><div class="box_inner">
							<h3>{!ALERT}</h3>

							<div id="alert_box"></div>
						</div></section>
					</div>
				</div>
			</div>
		</div></div>
	</div>
{+END}
