<?php
/**
 * Shogi_Kifu_Move
 * @author Masato Bito <masato@bz2.jp>
 * License: MIT License
 * @package Shogi_Kifu_Move
 */

class Shogi_Kifu_Move
{
  public function __construct()
  {
    $this->records = array(array('type' => 'init'));
  }

  public function addComment($comment)
  {
    $move =& $this->records[count($this->records)-1];
    if (!isset($move['comment'])) {
      $move['comment'] = '';
    }
    $move['comment'] .= $comment."\n";
    return $this;
  }

  public function addMove($from, $to, $piece, $options = array()) {
    $move =& $this->newMove();
    $move['from'] = array('x' => $from[0], 'y' => $from[1]);
    $move['to']   = array('piece' => $piece, 'x' => $to[0], 'y' => $to[1]);
    $move['type'] = 'move';
    foreach ($options as $key => $value) {
      $move[$key] = $value;
    }
    return $this;
  }

  public function addPeriod($period)
  {
    $this->records[count($this->records)-1]['period'] = $period;
    return $this;
  }

  public function addSpecial($type, $options = array())
  {
    $move =& $this->newMove();
    $move['type'] = $type;
    foreach ($options as $key => $value) {
      $move[$key] = $value;
    }
    return $this;
  }

  public function get($step)
  {
    return $this->records[$step];
  }

  public function getLastMoveNum()
  {
    for ($i = count($this->records)-1; 0 < $i; $i--) {
      if ($this->records[$i]['type'] === 'move') {
        return $i;
      }
    }
    return 0;
  }

  public function &newMove()
  {
    $move =& $this->records[count($this->records)-1];
    if (isset($move['type'])) {
      $this->records[] = array();
      $move =& $this->records[count($this->records)-1];
    }
    return $move;
  }

  public function setMove($num, $from, $to, $piece, $options = array())
  {
    $records =& $this->records;

    if (!isset($records[$num])) {
      $records[$num] = array();
    }

    $move =& $records[$num];
    $move['from'] = array('x' => $from[0], 'y' => $from[1]);
    $move['to']   = array('piece' => $piece, 'x' => $to[0], 'y' => $to[1]);
    $move['type'] = 'move';
    foreach ($options as $key => $value) {
      $move[$key] = $value;
    }

    return $this;
  }
}

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
