<div class="search_option float_surrounder">
	{+START,IF_NON_PASSED_OR_FALSE,HAS_RANGE}
		<label for="sd_{NAME*}">{DISPLAY*}:</label>

		{+START,INCLUDE,FORM_SCREEN_INPUT_DATE}
			NAME={NAME}
			TYPE=datetime
			REQUIRED=0
			UNLIMITED=1
			DAY={$PREG_REPLACE,^\d\d\d\d\-\d\d\-(\d\d)( \d+:\d\d)?$,$1,{SPECIAL}}
			MONTH={$PREG_REPLACE,^\d\d\d\d\-(\d\d)\-\d\d( \d+:\d\d)?$,$1,{SPECIAL}}
			YEAR={$PREG_REPLACE,^(\d\d\d\d)\-\d\d\-\d\d( \d+:\d\d)?$,$1,{SPECIAL}}
			HOUR={$PREG_REPLACE,^\d\d\d\d\-\d\d\-\d\d (\d+):\d\d$,$1,{SPECIAL}}
			MINUTE={$PREG_REPLACE,^\d\d\d\d\-\d\d\-\d\d \d+:(\d\d)$,$1,{SPECIAL}}
			MIN_DATE_DAY=
			MIN_DATE_MONTH=
			MIN_DATE_YEAR=
			MAX_DATE_DAY=
			MAX_DATE_MONTH=
			MAX_DATE_YEAR=
		{+END}
	{+END}

	{+START,IF_PASSED_AND_TRUE,HAS_RANGE}
		<label for="{NAME*}_from">{DISPLAY*}<span class="accessibility_hidden">, {!FROM}</span>:</label>
		<label for="{NAME*}_to" class="accessibility_hidden">>{DISPLAY*}, {!TO}:</label>

		{+START,INCLUDE,FORM_SCREEN_INPUT_DATE}
			NAME={NAME}_from
			TYPE=datetime
			REQUIRED=0
			UNLIMITED=1
			DAY={$PREG_REPLACE,^\d\d\d\d\-\d\d\-(\d\d)( \d+:\d\d)?$,$1,{$PREG_REPLACE,;.*$,,{SPECIAL}}}
			MONTH={$PREG_REPLACE,^\d\d\d\d\-(\d\d)\-\d\d( \d+:\d\d)?$,$1,{$PREG_REPLACE,;.*$,,{SPECIAL}}}
			YEAR={$PREG_REPLACE,^(\d\d\d\d)\-\d\d\-\d\d( \d+:\d\d)?$,$1,{$PREG_REPLACE,;.*$,,{SPECIAL}}}
			HOUR={$PREG_REPLACE,^\d\d\d\d\-\d\d\-\d\d (\d+):\d\d$,$1,{$PREG_REPLACE,;.*$,,{SPECIAL}}}
			MINUTE={$PREG_REPLACE,^\d\d\d\d\-\d\d\-\d\d \d+:(\d\d)$,$1,{$PREG_REPLACE,;.*$,,{SPECIAL}}}
			MIN_DATE_DAY=
			MIN_DATE_MONTH=
			MIN_DATE_YEAR=
			MAX_DATE_DAY=
			MAX_DATE_MONTH=
			MAX_DATE_YEAR=
		{+END}

		&ndash;

		{+START,INCLUDE,FORM_SCREEN_INPUT_DATE}
			NAME={NAME}_to
			TYPE=datetime
			REQUIRED=0
			UNLIMITED=1
			DAY={$PREG_REPLACE,^\d\d\d\d\-\d\d\-(\d\d)( \d+:\d\d)?$,$1,{$PREG_REPLACE,^.*;,,{SPECIAL}}}
			MONTH={$PREG_REPLACE,^\d\d\d\d\-(\d\d)\-\d\d( \d+:\d\d)?$,$1,{$PREG_REPLACE,^.*;,,{SPECIAL}}}
			YEAR={$PREG_REPLACE,^(\d\d\d\d)\-\d\d\-\d\d( \d+:\d\d)?$,$1,{$PREG_REPLACE,^.*;,,{SPECIAL}}}
			HOUR={$PREG_REPLACE,^\d\d\d\d\-\d\d\-\d\d (\d+):\d\d$,$1,{$PREG_REPLACE,^.*;,,{SPECIAL}}}
			MINUTE={$PREG_REPLACE,^\d\d\d\d\-\d\d\-\d\d \d+:(\d\d)$,$1,{$PREG_REPLACE,^.*;,,{SPECIAL}}}
			MIN_DATE_DAY=
			MIN_DATE_MONTH=
			MIN_DATE_YEAR=
			MAX_DATE_DAY=
			MAX_DATE_MONTH=
			MAX_DATE_YEAR=
		{+END}
	{+END}
</div>
