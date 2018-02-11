<?php
/**
 * Created by PhpStorm.
 * User: j
 * Date: 2/2/2018
 * Time: 12:14 AM
 */

namespace Drupal\ssolver\Services;

class Solver {
    public $inputArray;
    public $board;
    public $maxStrategy;
    
    public function strategies() {
        return [
            EliminateSiblingsStrategy::class,
            CheckLocalOpeningsStrategy::class,
            ClearTwinsStrategy::class,
            SpearStrategy::class,
            GuessAndCheckStrategy::class,
        ];
    }
    
    public function solve($inputArray) {
        $this->inputArray = $inputArray;
        $this->maxStrategy = 0;
        $boardFactory = new BoardFactory();
        $this->board = $boardFactory->buildBoard($inputArray);
        $attempting = 0;
        do {
            $this->maxStrategy = max([$attempting, $this->maxStrategy]);
            $strategy = $this->strategies()[$attempting];
            $instance = new $strategy($this->board);
            $instance->solve();
            $this->board = $instance->board;
            if ($instance->isSolved()) {
                return $this->board;
            } else {
                if ($instance->updated) {
                    $attempting = 0;
                } else {
                    $attempting++;
                }
            }
        } while ($attempting < count($this->strategies()));
        return $this->board;
    }
    
    public function maxStrategyTried() {
        $strategies = $this->strategies();
        $strategy = $strategies[$this->maxStrategy];
        
        return $strategy::toString(); // (SolverStrategy $strategies[0])->toString();
    }

    public function isSolved() {
        $solved = array_filter($this->board, function($item) {
            return $item->solved();
        });
        return count($solved) == 81;
    }


}