<?php
/**
 * Shogi_Kifu_Csa
 * @author Masato Bito <masato@bz2.jp>
 * License: MIT License
 * @package Shogi_Kifu_Csa
 */

class Shogi_Kifu_Csa
{
  public function __construct($kifu)
  {
    $this->kifu = $kifu;
  }

  public function parse()
  {
    $lines = $this->toLines($this->kifu->info['source']);
    foreach ($lines as $line) {
      $this->parseByLine($line);
    }

    return $this;
  }

  public function parseByLine($line)
  {
    $kifu  =& $this->kifu;
    $info  =& $kifu->info;
    $moves =& $kifu->moves;
    $suite =& $kifu->suite_init;

    if ($line === '+') {
      $info['player_start'] = 'black';
      return true;
    } else if ($line === '-') {
      $info['player_start'] = 'white';
      return true;
    } else if (substr($line, 0, 2) === "'*") {
      $moves->addComment(substr($line, 2));
      return true;
    }

    switch ($line[0]) {
    case '$':
      $pos   = strpos($line, ':');
      $key   = strtolower(substr($line, 1, $pos-1));
      $value = substr($line, $pos+1);

      switch ($key) {
      case 'end_time':
      case 'start_time':
        $value = strtotime($value);
        break;

      case 'time_limit':
        $time_limit = sscanf($value, '%d:%2d+%d');
        $hours      = $time_limit[0];
        $minutes    = $time_limit[1];
        $extra      = $time_limit[2];
        $value = array(
          'allotted' => $hours * 60 + $minutes,
          'extra'    => $extra);
        break;
      }

      $info[$key] = $value;
      return true;

    case '%':
      $value   = substr($line, 1);
      $options = array();

      switch ($player = $value[0]) {
      case '+':
      case '-':
        $options['is_black'] = $player === '+' ? true : false;
        $value = substr($value, 1);
        break;
      }

      $moves->addSpecial($value, $options);
      return true;

    case '+':
    case '-':
      $values   = sscanf($line, '%1s%1d%1d%1d%1d%2s');
      $from     = array($values[1], $values[2]);
      $to       = array($values[3], $values[4]);
      $piece    = $values[5];
      $is_black = $values[0] === '+' ? true : false;
      $moves->addMove($from, $to, $piece, array('is_black' => $is_black));
      return true;

    case 'N':
      $player = 'player_' . ($line[1] === '+' ? 'black' : 'white');
      $info[$player] = substr($line, 2);
      return true;

    case 'P':
      switch ($line[1]) {
      case 'I':
        $suite->hirate();
        for ($i = 0; ; $i++) {
          $p_info = substr($line, 2+$i*4, 4);
          if (!$p_info) {
            break;
          }
          $values = sscanf($p_info, '%1d%1d%2s');
          $x     = $values[0];
          $y     = $values[1];
          $piece = $values[2];
          $suite->cellRemove($x, $y, $piece);
        }
        return true;

      case '+':
      case '-':
        $is_black = $line[1] === '+';
        for ($i = 0; ; $i++) {
          $p_info = substr($line, 2+$i*4, 4);
          if (!$p_info) {
            break;
          }
          $values = sscanf($p_info, '%1d%1d%2s');
          $x     = $values[0];
          $y     = $values[1];
          $piece = $values[2];
          if ($x === 0 && $y === 0) {
            $suite->standDeploy($piece, $is_black);
          } else {
            $suite->cellDeploy($x, $y, $piece, $is_black);
          }
        }
        return true;

      default:
        $values = sscanf($line, 'P%1d%3s%3s%3s%3s%3s%3s%3s%3s%3s');
        $y      = array_shift($values);
        foreach ($values as $i => $p_info) {
          switch ($p_info[0]) {
          case '+':
            $is_black = true;
            break;
          case '-':
            $is_black = false;
            break;
          default:
            continue 2;
          }
          $x     = 9 - $i;
          $piece = substr($p_info, 1, 2);
          $suite->cellDeploy($x, $y, $piece, $is_black);
        }
        return true;
      }
      return false;

    case 'T':
      $period = (int)substr($line, 1);
      $moves->addPeriod($period);
      return true;

    case 'V':
      $info['version'] = substr($line, 1);
      return true;
    }

    return false;
  }

  public function toLines($source)
  {
    $result = array();
    $source = preg_replace('/,(\r?\n|\r)/', '', $source);
    foreach (preg_split('/\r?\n|\r/', $source) as $line) {
      if ($line) {
        $result[] = $line;
      }
    }
    return $result;
  }
}

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
