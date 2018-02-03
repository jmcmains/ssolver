<?php

namespace Drupal\ssolver\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Lorem Ipsum block form
 */
class SSolverForm extends ConfigFormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'ssolver_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('ssolver.settings');
        $existingBoard = $config->get('ssolver.board');
        $form['#attached']['library'][] = 'ssolver/ssolver';
        for ($i = 0; $i < 81; $i++) {
            $class = 'sudoku-cell row-'.$this->row($i).' col-'.$this->col($i);
            $form['cell'.$i] = array (
                '#type' => 'textfield',
                '#attributes' => array('class' => array($class)),
                '#default_value' => null,
            );
            if (count($existingBoard) > 0) {
                $form['cell'.$i]['#default_value'] = $existingBoard[$i];
            }
            if ($this->col($i) == 8) {
                $form['cell'.$i]['#suffix'] = '<br>';
            }

        }
        // Submit.
        $form['submit-button'] = array(
            '#type' => 'submit',
            '#value' => t('Solve my puzzle!'),
        );
        $form['options']['reset'] = array(
            '#type' => 'submit',
            '#value' => t('Reset'),
            '#submit' => [function(array &$form, FormStateInterface $form_state) {
                $config = $this->config('ssolver.settings');
                $config->set('ssolver.board', null);
                $config->save();
                $form_state->setRedirect('ssolver.content');
            }],
        );

        return $form;
    }
    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        for ($i = 0; $i < 81; $i++) {
            $cell = $form_state->getValue('cell'.$i);
            if (!is_null($cell) && $cell != 0) {
                if (!is_numeric($cell)) {
                    $form_state->setErrorByName('cell'.$i, $this->t('Please use a number.'));
                }
                if ($cell < 1 || $cell > 9) {
                    $form_state->setErrorByName('cell'.$i, $this->t('Please use a number between 1 and 9'));
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config('ssolver.settings');
        $output = array();
        for ($i = 0; $i < 81; $i++) {
            $output[$i] = $form_state->getValue('cell' . $i);
        }
        $config->set('ssolver.board', $output);
        $config->save();
        $form_state->setRedirect('ssolver.solve');
    }

    private function row($i) {
        return floor($i / 9);
    }

    private function col($i) {
        return $i % 9;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return [
            'ssolver.settings',
        ];
    }
}