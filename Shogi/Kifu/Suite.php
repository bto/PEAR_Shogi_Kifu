<?php
/**
 * Shogi_Kifu_Suite
 * @author Masato Bito <masato@bz2.jp>
 * License: MIT License
 * @package Shogi_Kifu_Suite
 */

class Shogi_Kifu_Suite
{
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

  public function __construct()
  {
    $this->board  = $this->boardEmpty();
    $this->pieces = $this->piecesDefault();
    $this->stand  = array(
      'black' => $this->standEmpty(),
      'white' => $this->standEmpty());
  }

  public function boardEmpty()
  {
    $borad = array();
    for ($i = 1; $i <= 9; $i++) {
      $borad[$i] = array();
      for ($j = 1; $j <= 9; $j++) {
        $borad[$i][$j] = null;
      }
    }
    return $borad;
  }

  public function cellDeploy($x, $y, $piece, $is_black)
  {
    if ($this->board[$x][$y]) {
      return false;
    }

    $piece_org = $this->piece_map[$piece];
    if (!$this->pieces[$piece_org]) {
      return false;
    }

    $this->cellSet($x, $y, $piece, $is_black);
    $this->pieces[$piece_org]--;
    return $this;
  }

  public function cellGet($x, $y)
  {
    return $this->board[$x][$y];
  }

  public function cellRemove($x, $y, $piece = null)
  {
    $cell = $this->board[$x][$y];
    if (!$cell) {
      return false;
    }
    if (!$this->cellTrash($x, $y, $piece)) {
      return false;
    }
    $this->pieces[$this->piece_map[$cell['piece']]]++;
    return $this;
  }

  public function cellSet($x, $y, $piece, $is_black)
  {
    $this->board[$x][$y] = array('is_black' => $is_black, 'piece' => $piece);
    return $this;
  }

  public function cellTrash($x, $y, $piece = null)
  {
    $cell = $this->board[$x][$y];
    if (!$cell) {
      return false;
    }
    if (!$piece) {
      $piece = $cell['piece'];
    }
    if ($piece !== $cell['piece']) {
      return false;
    }

    $this->board[$x][$y] = null;
    return $this;
  }

  public function hirate()
  {
    $this->cellDeploy(1, 9, 'KY', true);
    $this->cellDeploy(2, 9, 'KE', true);
    $this->cellDeploy(3, 9, 'GI', true);
    $this->cellDeploy(4, 9, 'KI', true);
    $this->cellDeploy(5, 9, 'OU', true);
    $this->cellDeploy(6, 9, 'KI', true);
    $this->cellDeploy(7, 9, 'GI', true);
    $this->cellDeploy(8, 9, 'KE', true);
    $this->cellDeploy(9, 9, 'KY', true);
    $this->cellDeploy(8, 8, 'KA', true);
    $this->cellDeploy(2, 8, 'HI', true);
    for ($i = 1; $i <= 9; $i++) {
      $this->cellDeploy($i, 7, 'FU', true);
    }

    $this->cellDeploy(1, 1, 'KY', false);
    $this->cellDeploy(2, 1, 'KE', false);
    $this->cellDeploy(3, 1, 'GI', false);
    $this->cellDeploy(4, 1, 'KI', false);
    $this->cellDeploy(5, 1, 'OU', false);
    $this->cellDeploy(6, 1, 'KI', false);
    $this->cellDeploy(7, 1, 'GI', false);
    $this->cellDeploy(8, 1, 'KE', false);
    $this->cellDeploy(9, 1, 'KY', false);
    $this->cellDeploy(2, 2, 'KA', false);
    $this->cellDeploy(8, 2, 'HI', false);
    for ($i = 1; $i <= 9; $i++) {
      $this->cellDeploy($i, 3, 'FU', false);
    }

    return $this;
  }

  public function move($move)
  {
    $is_black = $move['is_black'];
    $from     = $move['from'];
    $stand    = $move['stand'];
    $to       = $move['to'];

    if ($from['x']) {
      $this->cellTrash($from['x'], $from['y']);
    } else {
      $this->standTrash($from['piece'], $is_black);
    }

    $this->cellSet($to['x'], $to['y'], $to['piece'], $is_black);

    if ($stand) {
      $this->standSet($stand['stand'], $is_black);
    }

    return $this;
  }

  public function moveReverse($move)
  {
    $is_black = $move['is_black'];
    $from     = $move['from'];
    $stand    = $move['stand'];
    $to       = $move['to'];

    if ($stand) {
      $this->standTrash($stand['stand'], $is_black);
      $this->cellSet($to['x'], $to['y'], $stand['piece'], $is_black);
    } else {
      $this->cellTrash($to['x'], $to['y']);
    }

    if ($from['x']) {
      $this->cellSet($from['x'], $from['y'], $from['piece'], $is_black);
    } else {
      $this->standSet($from['piece'], $is_black);
    }

    return $this;
  }

  public function piecesDefault()
  {
    return array(
      'FU' => 18,
      'KY' => 4,
      'KE' => 4,
      'GI' => 4,
      'KI' => 4,
      'KA' => 2,
      'HI' => 2,
      'OU' => 2);
  }

  public function standDeploy($piece, $is_black, $number = 1)
  {
    $player = $is_black ? 'black' : 'white';
    $stand  =& $this->stand[$player];

    if ($piece == 'AL') {
      foreach ($this->pieces as $piece => $amount) {
        if ($piece === 'OU') {
          continue;
        }
        $stand[$piece] += $this->pieces[$piece];
        $this->pieces[$piece] = 0;
      }
    } else if (isset($this->pieces[$piece])) {
      $this->standSet($piece, $is_black, $number);
      $this->pieces[$piece] -= $number;
    } else {
      return false;
    }

    return $this;
  }

  public function standEmpty()
  {
    return array(
      'FU' => 0,
      'KY' => 0,
      'KE' => 0,
      'GI' => 0,
      'KI' => 0,
      'KA' => 0,
      'HI' => 0,
      'OU' => 0);
  }

  public function standRemove($piece, $is_black) {
    if (!$this->standTrash($piece, $is_black)) {
      return false;
    }
    $this->pieces[$piece]++;
    return $this;
  }

  public function standSet($piece, $is_black)
  {
    $player = $is_black ? 'black' : 'white';
    $this->stand[$player][$piece]++;
    return $this;
  }

  public function standTrash($piece, $is_black)
  {
    $player =  $is_black ? 'black' : 'white';
    $stand  =& $this->stand[$player];
    if (!$stand[$piece]) {
      return false;
    }
    $stand[$piece]--;
    return $this;
  }

  public function setup($handicap)
  {
    if ($handicap === 'Other') {
      return $this;
    }

    $this->hirate();

    if ($handicap === 'Even' || !$handicap) {
      return $this;
    }

    switch ($handicap) {
    case 'Lance':
      $this->cellRemove(1, 1, 'KY');
      break;

    case 'Right_Lance':
      $this->cellRemove(9, 1, 'KY');
      break;

    case 'Bishop':
      $this->cellRemove(2, 2, 'KA');
      break;

    case 'Rook_and_Lance':
      $this->cellRemove(1, 1, 'KY');
    case 'Rook':
      $this->cellRemove(8, 2, 'HI');
      break;

    case 'Six_Drops':
      $this->cellRemove(2, 1, 'KE');
      $this->cellRemove(8, 1, 'KE');
    case 'Four_Drops':
      $this->cellRemove(1, 1, 'KY');
      $this->cellRemove(9, 1, 'KY');
    case 'Two_Drops':
      $this->cellRemove(8, 2, 'HI');
      $this->cellRemove(2, 2, 'KA');
      break;
    }

    return $this;
  }
}

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
