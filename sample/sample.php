<?php
require_once(dirname(dirname(__FILE__)).'/src/Shogi/Kifu.php');

mb_internal_encoding('UTF-8');

$data = file_get_contents(dirname(__FILE__).'/081217-h.kif');
$kifu = new Shogi_Kifu($data, 'kif');
