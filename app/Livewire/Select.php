<?php

namespace App\Livewire;

use Livewire\Component;

class Select extends component
{
  public $items;

  public $selected = null;

  public $label;

  public $open = false;

  public function toggle()
  {
      $this->open = !$this->open;
  }
  public function select($index) {
    $this->selected = $this->selected !== $index ? $index : null;
    $this->open = false;
  }


}
