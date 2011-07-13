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

    $this->black = $this->info['player_start'] == 'black';
    $this->step  = 0;
    $this->suite = clone $this->suite_init;

    return $this;
  }

  public function prepare()
  {
    $info       =& $this->info;
    $moves      =& $this->moves;
    $suite_init =& $this->suite_init;

    if (!isset($info['player_start'])) {
      if (isset($info['handicap']) && $info['handicap'] !== 'Even') {
        $info['player_start'] = 'white';
      } else {
        $info['player_start'] = 'black';
      }
    }

    foreach ($this->moves->records as $i => &$move) {
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
