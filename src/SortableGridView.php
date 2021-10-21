<?php

namespace ale10257\sortable;

use yii\base\InvalidArgumentException;
use yii\grid\GridView;

class SortableGridView extends GridView
{
    public string $sortableClass = 'sortable-table';
    public ?string $ajaxUrl = null;

    public function init()
    {
        parent::init();
        $this->tableOptions['class'] .= ' ' . $this->sortableClass;
        $sortableJs = new SortableJs();
        $sortableJs->cssSelector = '.' . $this->sortableClass;
        if (!$this->ajaxUrl) {
            throw new InvalidArgumentException('ajaxUrl is required');
        }
        $sortableJs->ajaxUrl = $this->ajaxUrl;
        $sortableJs->registerJs();
    }

}