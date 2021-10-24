<?php

namespace app\tests\models;

use ale10257\sortable\ISortableModel;

class SortModelI extends SortModel implements ISortableModel
{

    public function sortableCondition(): array
    {
        return ['parent_id' => $this->parent_id];
    }
}