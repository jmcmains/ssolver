<?php 

namespace Drupal\ssolver\Services;

abstract class SolverStrategy {
    public $board;
    public $updated;
    
    abstract protected function solve();
    abstract protected static function toString();
    
    public function __construct($board) {
        $this->board = $board;
        $this->updated = false;
    }
    
    public function isSolved() {
        $unsolved = array_filter($this->board, function(Cell $cell) {
            return $cell->open();
        });
        return count($unsolved) == 0;
    }
    
    public function setLonelyOption(Cell $cell) {
        if (count($cell->availableOptions) == 1) {
            $cell->value = array_shift($cell->availableOptions);
            $this->updated = true;
        }
        return $cell;
    }
    
    
    public function setLoners() {
        foreach ($this->board as $cell) {
            $this->board[$cell->loc] = $this->setLonelyOption($cell);
        }
    }
    
}