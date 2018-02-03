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
    public $hash;

    public function solve($hash) {
        $this->inputArray = $hash;
        $this->buildOperatingArray();
        $tryAgain = true;
        while ($tryAgain) {
            $tryAgain = false;
            $updatedSibs = $this->eliminateSiblings();
            $updatedRow = $this->checkLocalOpening('row');
            $updatedCol = $this->checkLocalOpening('column');
            $updatedGroup = $this->checkLocalOpening('group');
            $updatedTwins = $this->clearTwins();
            if ($updatedRow || $updatedCol || $updatedGroup || $updatedSibs || $updatedTwins) {
                $tryAgain = true;
            }
        }

        return $this->hash;
    }

    public function getArray() {
        return array_map(function($item) {
            return $item['value'];
        }, $this->hash);
    }

    public function isSolved() {
        $hash = $this->hash;
        $unsolved = array_filter($hash, function($item) {
            return !is_numeric($item['value']);
        });
        return count($unsolved) == 0;
    }

    public function buildOperatingArray() {
        $array = $this->inputArray;
        $hash = [];
        for ($i = 0; $i< count($array); $i++) {
            $hash[$i] = [
                'loc' => $i,
                'value' => $array[$i],
                'row' => $this->row($i),
                'column' => $this->column($i),
                'group' => $this->group($i),
                'availableOptions' => [1,2,3,4,5,6,7,8,9]
            ];
        }
        $this->hash = $hash;
    }

    public function eliminateSiblings() {
        $hash = $this->hash;
        $updated = false;
        $tryAgain = true;
        while ($tryAgain) {
            $tryAgain = false;
            foreach ($hash as $currentVal) {
                if (!is_numeric($currentVal['value'])) {
                    $locals = array_filter($hash, function ($item) use ($currentVal) {
                        return is_numeric($item['value']) &&
                            ($item['row'] == $currentVal['row'] ||
                                $item['column'] == $currentVal['column'] ||
                                $item['group'] == $currentVal['group']);
                    });
                    $localVals = array_map(function ($item) {
                        return $item['value'];
                    }, $locals);
                    $currentVal['availableOptions'] = array_diff($currentVal['availableOptions'], $localVals);
                    if (count($currentVal['availableOptions']) == 1) {
                        $avail = $currentVal['availableOptions'];
                        $currentVal['value'] = array_shift($avail);
                        $tryAgain = true;
                        $updated = true;
                    }
                    $hash[$currentVal['loc']] = $currentVal;
                }
            }
        }
        $this->hash = $hash;
        return $updated;
    }

    public function checkLocalOpening($groupingName) {
        $hash = $this->hash;
        $updated = false;
        for ($i = 0; $i < 9; $i++) {
            $locals = array_filter($hash, function ($item) use ($i, $groupingName) {
                return !is_numeric($item['value']) && $item[$groupingName] == $i;
            });
            for ($j = 1; $j < 10; $j++) {
                $hasNum = array_filter($locals, function ($item) use ($j) {
                    return in_array($j, $item['availableOptions']);
                });
                if (count($hasNum) == 1 ) {
                    $found = array_shift($hasNum);
                    $found['value'] = $j;
                    $hash[$found['loc']] = $found;
                    $siblings = array_filter($hash, function ($item) use ($found) {
                        return !is_numeric($item['value']) &&
                            $item['loc'] != $found['loc'] &&
                            ($item['row'] == $found['row'] ||
                                $item['column'] == $found['column'] ||
                                $item['group'] == $found['group']);
                    });
                    foreach ($siblings as $sibling) {
                        $sibling['availableOptions'] = array_diff($sibling['availableOptions'],[$j]);
                        $hash[$sibling['loc']] = $sibling;
                    }
                    $updated = true;
                }
            }
        }
        $this->hash = $hash;
        return $updated;
    }

    public function updateTwins($twins, $child, $grouping) {
        $hash = $this->hash;
        $localTwin = array_filter($twins, function($item) use ($child, $grouping) {
            return $item['loc'] != $child['loc'] &&
                array_intersect($child['availableOptions'], $item['availableOptions']) == $child['availableOptions'] &&
                $child[$grouping] == $item[$grouping];
        });
        if (count($localTwin) > 0) {
            $localTwin = array_shift($localTwin);
            $set = array_filter($hash, function($item) use ($child, $localTwin, $grouping) {
                return !is_numeric($item['value']) &&
                    $item[$grouping] ==  $child[$grouping] &&
                    $item['loc'] != $child['loc'] &&
                    $item['loc'] != $localTwin['loc'];
            });
            foreach ($set as $item) {
                $item['availableOptions'] = array_diff($item['availableOptions'],$child['availableOptions']);
                $hash[$item['loc']] = $item;
            }
            \Drupal::logger('ssolver-'.$grouping)->notice(print_r($child, true), ['@type' => 'blah']);
            \Drupal::logger('ssolver-'.$grouping)->notice(print_r($localTwin, true), ['@type' => 'blah']);
            \Drupal::logger('ssolver-'.$grouping)->notice(print_r($set, true), ['@type' => 'blah']);
        }

        $this->hash = $hash;
    }

    public function clearTwins() {
        $hash = $this->hash;
        $twins = array_filter($hash, function($item) {
            return !is_numeric($item['value']) && count($item['availableOptions']) == 2;
        });
        \Drupal::logger('ssolver-twins')->notice(print_r($twins, true), ['@type' => 'blah']);
        foreach ($twins as $child) {
            $this->updateTwins($twins, $child, 'row');
            $this->updateTwins($twins, $child, 'column');
            $this->updateTwins($twins, $child, 'group');
        }
        $updated = $this->setLoners();
        return $updated;
    }

    public function setLoners() {
        $hash = $this->hash;
        $updated = false;
        for ($i = 0; $i < count($hash); $i++) {
            if(count($hash[$i]['availableOptions']) == 1 && !is_numeric($hash[$i]['value'])) {
                $hash[$i]['value'] = array_shift($hash[$i]['availableOptions']);
                $updated = true;
            }
        }
        $this->hash = $hash;
        return $updated;
    }

    public function spear($unsolved, $unsolvedInGroup, $group, $setType) {
        $hash = $this->hash;
        $set = array_map(function($item) use ($setType) {
            return $item[$setType];
        }, $unsolvedInGroup);
        $uniqueInSet = array_unique($set);
        if (count($uniqueInSet) == 1) {
            $uniqueLine = array_shift($uniqueInSet);
            $unsolvedInLine = array_filter($unsolved, function($item) use ($uniqueLine, $group, $setType) {
                return $item[$setType] == $uniqueLine && $item['group'] != $group;
            });
            foreach($unsolvedInLine as $item) {
                $item['availableOptions'] = array_diff($item['availableOptions'],[$uniqueLine]);
                $hash[$item['loc']] = $item;
            }
        }
        $this->hash = $hash;
    }

    public function spearMethod() {
        $hash = $this->hash;
        $unsolved = array_filter($hash, function($item) {
            return !is_numeric($item['value']);
        });
        for($i=0; $i < 9; $i++) {
            $unsolvedInGroup = array_filter($unsolved, function($item) use ($i) {
                return $item['group'] == $i;
            });
            if (count($unsolvedInGroup) > 0) {
                for ($j = 1; $j <= 9; $j++ ) {
                    $unsolvedInGroupValJ = array_filter($unsolvedInGroup, function($item) use ($j) {
                        return in_array($j,$item['availableOptions']);
                    });
                    $this->spear($unsolved, $unsolvedInGroupValJ, $i, 'row');
                    $this->spear($unsolved, $unsolvedInGroupValJ, $i, 'column');
                }
            }
        }
        $updated = $this->setLoners();
        return $updated;
    }



    private function row($i) {
        return floor($i / 9);
    }

    private function column($i) {
        return $i % 9;
    }

    private function group($i) {
        return (floor($i / 27) * 3) + (floor(($i % 9)/ 3));
    }
}