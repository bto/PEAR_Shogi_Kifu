<?php
/**
 * Shogi_Kifu
 * @author Masato Bito <masato@bz2.jp>
 * License: MIT License
 * @package Shogi_Kifu
 */
require_once(dirname(__FILE__).'/Kifu/Move.php');
require_once(dirname(__FILE__).'/Kifu/Suite.php');

class Shogi_Kifu
{
  protected $number_x_map = array(
    1 => '１',
    2 => '２',
    3 => '３',
    4 => '４',
    5 => '５',
    6 => '６',
    7 => '７',
    8 => '８',
    9 => '９');

  protected $number_y_map = array(
    1 => '一',
    2 => '二',
    3 => '三',
    4 => '四',
    5 => '五',
    6 => '六',
    7 => '七',
    8 => '八',
    9 => '九');

  protected $piece_map = array(
    'FU' => 'FU',
    'KY' => 'KY',
    'KE' => 'KE',
    'GI' => 'GI',
    'KI' => 'KI',
    'KA' => 'KA',
    'HI' => 'HI',
    'OU' => 'OU',
    'TO' => 'FU',
    'NY' => 'KY',
    'NK' => 'KE',
    'NG' => 'GI',
    'UM' => 'KA',
    'RY' => 'HI');

  protected $piece_string_map = array(
    'FU' => '歩',
    'KY' => '香',
    'KE' => '桂',
    'GI' => '銀',
    'KI' => '金',
    'KA' => '角',
    'HI' => '飛',
    'OU' => '王',
    'TO' => 'と',
    'NY' => '成香',
    'NK' => '成桂',
    'NG' => '成銀',
    'UM' => '馬',
    'RY' => '竜');

  public function __construct($source = null, $format = null)
  {
    $this->suite_init = new Shogi_Kifu_Suite();
    $this->info       = array();
    $this->moves      = new Shogi_Kifu_Move();

    if ($source) {
      $this->source($source);
    }

    if ($format) {
      $this->parse($format);
    }
  }

  public function hasNext()
  {
    $move = $this->moves->get($this->step+1);
    return ($move && $move['type'] === 'move');
  }

  public function hasPrev()
  {
    if ($this->step < 1) {
      return false;
    }
    $move = $this->moves->get($this->step-1);
    return ($move && $move['type'] === 'move');
  }

  public function moveCurrent()
  {
    $move = $this->moves->get($this->step);
    if ($move && $move['type'] === 'move') {
      return $move;
    } else {
      return null;
    }
  }

  public function moveFirst()
  {
    $this->is_black = $this->info['player_start'] === 'black';
    $this->step     = 0;
    $this->suite    = clone $this->suite_init;
    return $this;
  }

  public function moveLast()
  {
    do {
      $step = $this->step;
      $this->moveNext();
    } while($step !== $this->step);
  }

  public function moveNext()
  {
    $move = $this->moves->get($this->step+1);
    if ($move && $move['type'] === 'move') {
      $this->suite->move($move);
      $this->is_black = !$move['is_black'];
      $this->step++;
    }
    return $move;
  }

  public function movePrev()
  {
    $move = $this->moves->get($this->step);
    if ($move && $move['type'] === 'move') {
      $this->suite->moveReverse($move);
      $this->is_black = $move['is_black'];
      $this->step--;
    }
    return $move;
  }

  public function moveStrings()
  {
    $result = array();
    foreach ($this->moves->records as $move) {
      $result[] = $move['str'];
    }
    return $result;
  }

  public function moveTo($step)
  {
    $this->moveFirst();
    while ($step !== $this->step) {
      $this->moveNext();
    }
  }

  public function parse($format)
  {
    if ($format) {
      $this->info['format'] = $format;
    }

    $class = ucfirst($this->info['format']);
    require_once(dirname(__FILE__).'/Kifu/'.$class.'.php');
    $class = 'Shogi_Kifu_'.$class;

    $this->parser = new $class($this);
    $this->parser->parse();
    $this->prepare();

    return $this->moveFirst();
  }

  public function prepare()
  {
    $info =& $this->info;

    if (!isset($info['player_start'])) {
      $info['player_start'] = 'black';
    }

    $suite        = clone $this->suite_init;
    $move_records = $this->moves->records;
    foreach ($move_records as $i => &$move) {
      if ($move['type'] !== 'move') {
        continue;
      }

      $move_prev =  $move_records[$i-1];
      $from      =& $move['from'];
      $to        =& $move['to'];

      if (!isset($move['is_black'])) {
        if ($move_prev['type'] === 'init') {
          $move['is_black'] = $info['player_start'] === 'black';
        } else {
          $move['is_black'] = !$move_prev['is_black'];
        }
      }

      if (!isset($from['piece'])) {
        if ($from['x']) {
          $from['piece'] = $suite->board[$from['x']][$from['y']]['piece'];
        } else {
          $from['piece'] = $to['piece'];
        }
      }

      if (!$to['x']) {
        $to['x'] = $move_prev['to']['x'];
        $to['y'] = $move_prev['to']['y'];
      }

      if (!isset($move['stand'])) {
        $move['stand'] = null;
      }

      if ($cell = $suite->board[$to['x']][$to['y']]) {
        $move['stand'] = array(
          'piece' => $cell['piece'],
          'stand' => $this->piece_map[$cell['piece']]);
      }

      if (!isset($move['str'])) {
        $str  = $this->number_x_map[$to['x']];
        $str .= $this->number_y_map[$to['y']];
        $str .= $this->piece_string_map[$from['piece']];
        if ($from['piece'] !== $to['piece']) {
          $str .= '成';
        }
        if (!$from['x']) {
          $str .= '打';
        }
        $move['str'] = $str;
      }

      $suite->move($move);
    }
  }

  public function source($source = null)
  {
    if ($source) {
      $this->info['source'] = $source;
    }
    return $this->info['source'];
  }
}

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
