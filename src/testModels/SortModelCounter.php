<?php

namespace ale10257\sortable\testModels;

use ale10257\sortable\SortableBehavior;

class SortModelCounter extends SortModel
{
    public function behaviors(): array
    {
        return [
            [
                'class' => SortableBehavior::class
            ]
        ];
    }
}