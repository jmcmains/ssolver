<?php 

namespace Drupal\ssolver\Services;

class GuessAndCheckStrategy extends SolverStrategy {
    public function solve() {
        $twins = array_filter($this->board, function($item) {
            return $item->open() && count($item->availableOptions) == 2;
        });
        if (count($twins) > 0) {
            $firstTwin = array_shift($twins);
            $options = $firstTwin->availableOptions;
            foreach ($options as $option) {
                $board = $this->board;
                $firstTwin->value = $option;
                $board[$firstTwin->loc] = $firstTwin;
                $boardArray = $this->boardToArray($board);
                $solver = new Solver();
                $output = $solver->solve($boardArray);
                if ($solver->isSolved()) {
                    $this->board = $output;
                    return $this->board;
                }
            }
        }
       
        return $this->board;
    }
    
    public static function toString() {
        return 'Guess and Check';
    }

    private function boardToArray($board) {
        return array_map(function($item) {
            return $item->value;
        }, $board);
    }
}