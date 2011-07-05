<?php
/**
 * Shogi_Kifu_Kif
 * @author Masato Bito <masato@bz2.jp>
 * License: MIT License
 * @package Shogi_Kifu_Kif
 */

class Shogi_Kifu_Kif
{
  protected $kifu_map = array(
    '同'   => 0,
    '　'   => 0,
    '１'   => 1,
    '２'   => 2,
    '３'   => 3,
    '４'   => 4,
    '５'   => 5,
    '６'   => 6,
    '７'   => 7,
    '８'   => 8,
    '９'   => 9,
    '一'   => 1,
    '二'   => 2,
    '三'   => 3,
    '四'   => 4,
    '五'   => 5,
    '六'   => 6,
    '七'   => 7,
    '八'   => 8,
    '九'   => 9,
    '歩'   => 'FU',
    '香'   => 'KY',
    '桂'   => 'KE',
    '銀'   => 'GI',
    '金'   => 'KI',
    '角'   => 'KA',
    '飛'   => 'HI',
    '王'   => 'OU',
    '玉'   => 'OU',
    '歩成' => 'TO',
    '香成' => 'NY',
    '桂成' => 'NK',
    '銀成' => 'NG',
    '角成' => 'UM',
    '飛成' => 'RY',
    'と'   => 'TO',
    '成香' => 'NY',
    '成桂' => 'NK',
    '成銀' => 'NG',
    '馬'   => 'UM',
    '龍'   => 'RY',
    '竜'   => 'RY');

  protected $board_piece_map = array(
    '歩' => 'FU',
    '香' => 'KY',
    '桂' => 'KE',
    '銀' => 'GI',
    '金' => 'KI',
    '角' => 'KA',
    '飛' => 'HI',
    '王' => 'OU',
    '玉' => 'OU',
    'と' => 'TO',
    '杏' => 'NY',
    '圭' => 'NK',
    '全' => 'NG',
    '馬' => 'UM',
    '龍' => 'RY',
    '竜' => 'RY');

  protected $kanji_number_map = array(
    '一' =>  1,
    '二' =>  2,
    '三' =>  3,
    '四' =>  4,
    '五' =>  5,
    '六' =>  6,
    '七' =>  7,
    '八' =>  8,
    '九' =>  9,
    '十' => 10);

  protected $handicap_name_map = array(
    '平手'     => 'Even',
    '香落ち'   => 'Lance',
    '右香落ち' => 'Right_Lance',
    '角落ち'   => 'Bishop',
    '飛車落ち' => 'Rook',
    '飛香落ち' => 'Rook_and_Lance',
    '二枚落ち' => 'Two_Drops',
    '四枚落ち' => 'Four_Drops',
    '六枚落ち' => 'Six_Drops',
    'その他'   => 'Other');

  public function __construct($kifu)
  {
    $this->kifu = $kifu;
  }

  public function initializeSuite()
  {
    $this->_board_setup = true;
  }

  public function parse()
  {
    $this->_henka = null;

    $lines = $this->toLines($this->kifu->info['source']);
    foreach ($lines as $line) {
      $this->parseByLine($line);
    }

    return $this;
  }

  public function parseByLine($line)
  {
    if ($result = $this->parseByLineAsComment($line)) {
      return $result;
    }

    if ($result = $this->parseByLineAsMove($line)) {
      return $result;
    }

    if ($result = $this->parseByLineAsInfo($line)) {
      return $result;
    }

    if ($result = $this->parseByLineAsInfo2($line)) {
      return $result;
    }

    return false;
  }

  public function parseByLineAsComment($line)
  {
    switch ($line[0]) {
    case '#':
      return true;
    case '*':
      // 変化は未対応
      if ($this->_henka) {
        return true;
      }
      if ($comment = substr($line, 1)) {
        $this->kifu->moves->addComment($comment);
      }
      return true;
    }

    return false;
  }

  public function parseByLineAsInfo($line)
  {
    if (!preg_match('/^(.+?)：(.+)/', $line, $matches)) {
      return false;
    }

    $info  =& $this->kifu->info;
    $key   = $matches[1];
    $value = $this->strip($matches[2]);

    switch ($key) {
    case '対局ID':
      if (!isset($info['kif'])) $info['kif'] = array();
      $info['kif']['id'] = (int)$value;
      return true;

    case '開始日時':
      $info['start_time'] = strtotime($value);
      return true;

    case '終了日時':
      $info['end_time'] = strtotime($value);
      return true;

    case '表題':
      $info['title'] = $value;
      return true;

    case '棋戦':
      $info['event'] = $value;
      return true;

    case '持ち時間':
      if (preg_match('/([0-9]+)時間/', $value, $matches)) {
        if (!isset($info['time_limit'])) $info['time_limit'] = array();
        $info['time_limit']['allotted'] = (int)$matches[1] * 60;
      }
      return true;

    case '消費時間':
      if (preg_match('/[0-9]+▲([0-9]+)△([0-9]+)/', $value, $matches)) {
        $info['time_consumed'] = array(
          'black' => (int)$matches[1],
          'white' => (int)$matches[2]);
      }
      return true;

    case '場所':
      $info['site'] = $value;
      return true;

    case '戦型':
      $info['opening'] = $value;
      return true;

    case '手合割':
      $info['handicap'] = $this->handicap_name_map[$value];
      return true;

    case '先手':
    case '下手':
      $info['player_black'] = $value;
      return true;

    case '後手':
    case '上手':
      $info['player_white'] = $value;
      return true;

    case '先手の持駒':
    case '下手の持駒':
      return $this->parseStand($value, true);

    case '後手の持駒':
    case '上手の持駒':
      return $this->parseStand($value, false);

    case '変化':
      $this->_henka = true;
      return true;

    default:
      if (!isset($info['kif'])) $info['kif'] = array();
      $info['kif'][$key] = $value;
      return true;
    }

    return false;
  }

  public function parseByLineAsInfo2($line)
  {
    switch ($this->strip($line)) {
    case '先手番':
    case '下手番':
      $this->kifu->info['player_start'] = 'black';
      return true;

    case '上手番':
    case '後手番':
      $this->kifu->info['player_start'] = 'white';
      return true;
    }

    return false;
  }

  public function parseByLineAsMove($line)
  {
    if (!preg_match('/^ *([0-9]+) ([^ ]+)/', $line, $matches)) {
      return false;
    }

    // 変化は未対応
    if ($this->_henka) {
      return true;
    }

    $num   = (int)$matches[1];
    $move  = $matches[2];
    $moves =& $this->kifu->moves;

    switch ($move) {
    case '投了':
      $moves->addSpecial('TORYO');
      return true;
    case '千日手':
      $moves->addSpecial('SENNICHITE');
      return true;
    case '持将棋':
      $moves->addSpecial('JISHOGI');
    case '詰み':
      $moves->addSpecial('TSUMI');
      return true;
    }

    $to = array(
      $this->kifu_map[mb_substr($move, 0, 1)],
      $this->kifu_map[mb_substr($move, 1, 1)]);
    if (preg_match('/(.*)\(([1-9])([1-9])\)/', mb_substr($move, 2), $matches)) {
      $piece = $this->kifu_map[$matches[1]];
      $from  = array($matches[2], $matches[3]);
      preg_match('/(.*)\(/', $move, $matches);
      $str   = $matches[1];
    } else {
      $piece = $this->kifu_map[mb_substr($move, 2, 1)];
      $from  = array(0, 0);
      $str   = $move;
    }
    $moves->setMove($num, $from, $to, $piece, array('str' => $str));

    return false;
  }

  function parseStand($str, $black)
  {
    if ($str === 'なし') {
      return true;
    }

    foreach (mb_split('　*', $str) as $value) {
      $piece = $this->board_piece_map[mb_substr($value, 0, 1)];
      $num   = $this->parseKansuuchi(mb_substr($value, 1));
      if (!$piece || !$num) {
        continue;
      }

      $this->kifu->suite_init->standDeplay($piece, $black, $num);
    }

    return true;
  }

  function parseKansuuchi($str)
  {
    $num = 0;
    for ($i = 0; $s = mb_substr($str, $i, 1); $i++) {
      $num += $this->kanji_number_map[$s];
    }

    if (!$num) {
      $num = 1;
    }

    return $num;
  }

  public function strip($str)
  {
    $str = mb_ereg_replace('^[[:space:]　]+', '', $str);
    $str = mb_ereg_replace('[[:space:]　]+$', '', $str);
    return $str;
  }

  public function toLines($source)
  {
    $result = array();
    foreach (preg_split('/\r?\n|\r/', $source) as $line) {
      if ($this->strip($line)) $result[] = $line;
    }
    return $result;
  }
}

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
