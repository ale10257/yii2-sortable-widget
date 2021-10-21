<?php

namespace ale10257\sortable;

interface ISortableModel
{
    /**
     * @return string|array
     */
    public function sortableCondition();

    public function getSortableService(): SortableService;
}