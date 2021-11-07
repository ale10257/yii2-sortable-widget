<?php

namespace ale10257\sortable;

use InvalidArgumentException;

class SortableService extends BaseService
{
    public function changeSort()
    {
        if (is_numeric($this->previous_id) && $this->previous_id < 0) {
            throw new InvalidArgumentException('Previous id must not be less than zero');
        }
        if (is_numeric($this->previous_id) && $this->previous_id == 0) {
            $newSortValue = $this->step;
        } else {
            $prev = $this->getPrev();
            $newSortValue = $prev + $this->step;
        }
        if ($newSortValue > $this->model->{$this->sortField}) {
            $this->ifMore($newSortValue);
        }
        if ($newSortValue < $this->model->{$this->sortField}) {
            $this->model::updateAllCounters(
                [
                    $this->sortField => $this->step
                ],
                [
                    'and',
                    $this->condition,
                    [
                        'between',
                        $this->sortField,
                        $newSortValue,
                        $this->model->{$this->sortField} - $this->step
                    ]
                ]
            );
        }
        $this->model->{$this->sortField} = $newSortValue;
        $this->model->save();
    }

    public function addToBeginning()
    {
        $this->model::updateAllCounters(
            [
                $this->sortField => $this->step
            ],
            $this->condition
        );
        $this->model->{$this->sortField} = $this->step;
        $this->model->save();
    }

    public function addToEnd()
    {
        $max = $this->model::find()->where($this->condition)->max($this->sortField);
        if ($this->model->{$this->sortField} !== null) {
            $this->ifMore($max);
        } else {
            $this->model->{$this->sortField} = $max + $this->step;
            $this->model->save();
        }
    }

    private function ifMore($newValue)
    {
        $this->model::updateAllCounters(
            [
                $this->sortField => -$this->step
            ],
            [
                'and',
                $this->condition,
                [
                    'between',
                    $this->sortField,
                    $this->model->{$this->sortField} + $this->step,
                    $newValue
                ]
            ]
        );
        $this->model->{$this->sortField} = $newValue;
        $this->model->save();
    }

    public function delete()
    {
        $this->model::updateAllCounters(
            [
                $this->sortField => -$this->step
            ],
            [
                'and',
                $this->condition,
                ['>', $this->sortField, $this->model->{$this->sortField}]
            ]

        );
    }

    public function updateSort()
    {
        $check = $this->model::find()
            ->where($this->condition)
            ->andWhere(['or', [$this->sortField => null], ['<=', $this->sortField, 0]])
            ->exists();
        if ($check) {
            $step = $this->step;
            foreach ($this->model::find()
                         ->where($this->condition)
                         ->orderBy(['id' => SORT_ASC, $this->sortField => SORT_ASC])
                         ->all() as $model) {
                $model->{$this->sortField} = $step;
                $model->save();
                $step += $this->step;
            }
        }
    }
}