<?php 

namespace Drupal\ssolver\Services;

class EliminateSiblingsStrategy extends SolverStrategy {

    public function solve() {
        do {
            $tryAgain = false;
            
            foreach ($this->getOpenCells() as $cell) {
                $localVals = $this->getLocalVals($cell);
                $cell->availableOptions = array_diff($cell->availableOptions, $localVals);
                $cell = $this->setLonelyOption($cell);
                $this->board[$cell->loc] = $cell;
                if ($cell->solved()) {
                    $tryAgain = true;
                }
            }
        } while ($tryAgain);
    }

    public static function toString() {
        return 'Eliminate Twins';
    }
    
    private function getLocalVals(Cell $cell) {
        $locals = array_filter($this->board, function (Cell $item) use ($cell) {
            return $item->solved() &&
            ($item->row() == $cell->row() ||
                $item->column() == $cell->column() ||
                $item->group() == $cell->group());
        });
            return array_map(function ($item) { return $item->value; }, $locals);
    }
    
    private function getOpenCells() {
        return array_filter($this->board, function($item) {
            return $item->open();
        });
    }
    
}