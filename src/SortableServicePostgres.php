<?php

namespace ale10257\sortable;

use InvalidArgumentException;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use Yii;

class SortableServicePostgres extends BaseService
{
    private ?int $newSortValue = null;

    /**
     * @throws Exception
     */
    public function changeSort()
    {
        if (!$this->newSortValue) {
            if (is_numeric($this->previous_id) && $this->previous_id < 0) {
                throw new InvalidArgumentException('Previous id must not be less than zero');
            }
            if (is_numeric($this->previous_id) && $this->previous_id == 0) {
                $this->newSortValue = 1;
            } else {
                $prev = $this->getPrev();
                $this->newSortValue = ++$prev;
            }
        }
        $this->model->{$this->sortField} = $this->newSortValue;
        $this->model->save();
        $this->updateSort();
        $this->model->refresh();
    }

    /**
     * @throws Exception
     */
    public function addToBeginning()
    {
        $this->newSortValue = 1;
        $this->changeSort();
    }

    /**
     * @throws Exception
     */
    public function addToEnd()
    {
        $max = $this->model::find()->where($this->condition)->max($this->sortField);
        if ($max == $this->model->{$this->sortField}) {
            return;
        }
        $this->newSortValue = $max + 1;
        $this->changeSort();
    }

    /**
     * @throws Exception
     */
    public function updateSort()
    {
        $condition = $this->model->sortableCondition();
        $select = (new Query())
            ->from($this->model::tableName())
            ->select(new Expression("id, rank() OVER (ORDER BY $this->sortField, id) * 10 AS newsort"))
            ->where($condition)
            ->createCommand()
            ->getRawSql();
        /** @noinspection SqlInsertValues */
        $sql = "
            INSERT INTO {$this->model::tableName()} (id, $this->sortField)
            $select
            ON CONFLICT (id)
                DO UPDATE
                SET $this->sortField = EXCLUDED.$this->sortField;
        ";
        Yii::$app->db->createCommand($sql)->execute();
    }

    /**
     * @throws Exception
     */
    public function delete()
    {
        $this->updateSort();
    }
}