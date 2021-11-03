<?php

namespace ale10257\sortable\testModels;

use ale10257\sortable\ISortableModel;

class SortModelI extends SortModel implements ISortableModel
{

    public function sortableCondition(): array
    {
        return ['parent_id' => $this->parent_id];
    }
}