<?php 

namespace Drupal\ssolver\Services;

class ClearTwinsStrategy extends SolverStrategy {
    public function solve() {
        $twins = array_filter($this->board, function(Cell $item) {
            return $item->open() && count($item->availableOptions) == 2;
        });
        foreach ($twins as $child) {
            foreach (['row', 'column','group'] as $setType) {
                $this->updateTwins($twins, $child, $setType);
                $this->setLoners();
            }
        }
    }
    
    public static function toString() {
        return 'Clear Twins';
    }
    
    private function updateTwins($twins, $child, $setType) {
        $localTwin = array_filter($twins, function($item) use ($child, $setType) {
            return $item->loc != $child->loc &&
            $child->availableOptions == $item->availableOptions &&
            $child->{$setType}() == $item->{$setType}();
        });
            if (count($localTwin) > 0) {
                $localTwin = array_shift($localTwin);
                $set = array_filter($this->board, function($item) use ($child, $localTwin, $setType) {
                    return $item->open() &&
                    $item->{$setType}() ==  $child->{$setType}() &&
                    $item->loc != $child->loc &&
                    $item->loc != $localTwin->loc;
                });
                    foreach ($set as $item) {
                        $item->availableOptions = array_diff($item->availableOptions, $child->availableOptions);
                        $this->board[$item->loc] = $item;
                    }
            }
    }
    
}