<?php 

namespace Drupal\ssolver\Services;

class Cell {
    public $loc;
    public $value = null;
    public $availableOptions;
    public $isSolved;
    
    public function __construct($loc, $value = null) {
        $this->loc = $loc;
        $this->value = is_numeric($value) ? $value : null;
        $this->availableOptions = range(1, 9);
        $this->isSolved = !is_null($this->value);
    }

    public function row() {
        return floor($this->loc / 9);
    }
    
    public function column() {
        return $this->loc % 9;
    }
    
    public function group() {
        return (floor($this->loc / 27) * 3) + (floor(($this->loc % 9)/ 3));
    }
    
    public function solved() {
        $this->isSolved = !is_null($this->value);
        return !is_null($this->value);
    }
    
    public function open() {
        $this->isSolved = !is_null($this->value);
        return is_null($this->value);
    }
}