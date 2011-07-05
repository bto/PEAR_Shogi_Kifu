<?php
/**
 * Shogi_Kifu_Suite
 * @author Masato Bito <masato@bz2.jp>
 * License: MIT License
 * @package Shogi_Kifu_Suite
 */

class Shogi_Kifu_Suite
{
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

  public function standDeplay($piece, $black, $number = 1)
  {
    $player = $black ? 'black' : 'white';
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
      $this->standSet($piece, $black, $number);
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

  public function standSet($piece, $black)
  {
    $player = $black ? 'black' : 'white';
    $this->stand[$player][$piece]++;
    return $this;
  }
}

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
