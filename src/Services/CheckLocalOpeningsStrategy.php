<?php 

namespace Drupal\ssolver\Services;

class CheckLocalOpeningsStrategy extends SolverStrategy {
    public function solve() {
        foreach (['row','column','group'] as $setType) {
            $this->checkLocalOpening($setType);
        }
    }
    
    public static function toString() {
        return 'Check Local Openings';
    }
    
    private function checkLocalOpening($setType) {
        for ($i = 0; $i < 9; $i++) {
            $locals = array_filter($this->board, function ($item) use ($i, $setType) {
                return $item->open() && $item->{$setType} == $i;
            });
            for ($j = 1; $j < 10; $j++) {
                $hasNum = array_filter($locals, function ($item) use ($j) {
                    return in_array($j, $item->availableOptions);
                });
                if (count($hasNum) == 1 ) {
                    $found = array_shift($hasNum);
                    $found->value = $j;
                    $this->board[$found->loc] = $found;
                    $this->updateSiblings($found);
                    $this->updated = true;
                }
            }
        }
    }
    
    private function updateSiblings($found) {
        $siblings = array_filter($this->board, function ($item) use ($found) {
            return $item->open() &&
            $item->loc != $found->loc &&
            ($item->row() == $found->row() ||
                $item->column() == $found->column() ||
                $item->group() == $found->group());
        });
        foreach ($siblings as $sibling) {
            $sibling->availableOptions = array_diff($sibling->availableOptions,[$j]);
            $this->board[$sibling->loc] = $sibling;
        }
    }
    
}