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
    $this->info       = array('player_start' => 'black');
    $this->move       = new Shogi_Kifu_Move();

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

    $class = 'Shogi_Kifu_'.ucfirst($this->info['format']);
    $this->parser = new $class($this);
    $this->parser->parse();
    $this->prepare();

    $this->black = $this->info['player_start'] == 'black';
    $this->step  = 0;
    $this->suite = clone $this->suite_init;

    return $this;
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
