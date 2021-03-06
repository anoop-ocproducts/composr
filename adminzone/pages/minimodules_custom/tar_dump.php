<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2015

 See text/EN/licence.txt for full licencing information.

*/

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

disable_php_memory_limit();
if (function_exists('set_time_limit')) {
    @set_time_limit(0);
}
$GLOBALS['NO_DB_SCOPE_CHECK'] = true;

require_code('tar');

$filename = 'composr-' . get_site_name() . '.' . date('Y-m-d') . '.tar';

header('Content-Disposition: attachment; filename="' . str_replace("\r", '', str_replace("\n", '', addslashes($filename))) . '"');

$tar = tar_open(null, 'wb');

$max_size = get_param_integer('max_size', null);
$subpath = get_param_string('path', '');
tar_add_folder($tar, null, get_file_base() . (($subpath == '') ? '' : '/') . $subpath, $max_size, $subpath, null, null, false, true);

tar_close($tar);

$GLOBALS['SCREEN_TEMPLATE_CALLED'] = '';
exit();
