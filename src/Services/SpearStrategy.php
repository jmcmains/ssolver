<?php 

namespace Drupal\ssolver\Services;

class SpearStrategy extends SolverStrategy {
    public function solve() {
        $unsolved = array_filter($this->board, function($item) {
            return $item->open();
        });
        for($groupNum=0; $groupNum < 9; $groupNum++) {
            $unsolvedInGroup = array_filter($unsolved, function($item) use ($groupNum) {
                return $item->group() == $groupNum;
            });
            if (count($unsolvedInGroup) > 0) {
                for ($guessNumber = 1; $guessNumber <= 9; $guessNumber++ ) {
                    $unsolvedInGroupVal = array_filter($unsolvedInGroup, function($item) use ($guessNumber) {
                        return in_array($guessNumber, $item->availableOptions);
                    });
                    $this->spear($unsolved, $unsolvedInGroupVal, $groupNum, $guessNumber, 'row');
                    $this->spear($unsolved, $unsolvedInGroupVal, $groupNum, $guessNumber, 'column');
                }
            }
        }
    }
    
    public static function toString() {
        return 'Spear';
    }
    
    
    public function spear($unsolved, $unsolvedInGroup, $group, $guessNumber, $setType) {
        $set = array_map(function($item) use ($setType) {
            return $item->{$setType}();
        }, $unsolvedInGroup);
            $uniqueInSet = array_unique($set);
            if (count($uniqueInSet) == 1) {
                $uniqueLine = array_shift($uniqueInSet);
                $unsolvedInLine = array_filter($unsolved, function($item) use ($uniqueLine, $group, $setType) {
                    return $item->{$setType}() == $uniqueLine && $item->group() != $group;
                });
                    foreach($unsolvedInLine as $item) {
                        $item->availableOptions = array_diff($item->availableOptions,[$guessNumber]);
                        $this->board[$item->loc] = $item;
                        $this->setLoners();
                    }
            }
    }
    
}