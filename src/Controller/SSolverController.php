<?php

namespace Drupal\ssolver\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ssolver\Services\Solver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class SSolverController extends ControllerBase {

    private $solver;
    /**
     * Display the markup.
     *
     * @return array
     */
    public function solve() {
        $config = \Drupal::config('ssolver.settings');
        $array = $config->get('ssolver.board');
        $solved = $this->solver->solve($array);
        $element['#original'] = $array;
        $element['#solved'] = $solved;
        $element['#theme'] = 'ssolver';
        $element['#attached']['library'][] = 'ssolver/ssolver';
        if ($this->solver->isSolved($solved)) {
            $element['#title'] = 'Solved!';
        } else {
            $element['#title'] = "Couldn't quite crack it!";
        }
        return $element;
    }

    public function __construct(Solver $solver)
    {
        $this->solver = $solver;
    }

    public static function create(ContainerInterface $container)
    {
        $x = $container->get('ssolver.solver');
        return new static ($x);
    }
}