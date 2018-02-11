<?php 

namespace Drupal\ssolver\Services;

class BoardFactory {
    public function buildBoard($array) {
        $board = [];
        for ($i = 0; $i< count($array); $i++) {
            $board[] = new Cell($i, $array[$i]);
        }
        return $board;
        
    }
}