<?php

namespace ale10257\sortable;

use yii\web\AssetBundle;

class SortableJsAssetBundle extends AssetBundle
{
    public $sourcePath = '@bower/sortablejs';

    public $js = [
        'Sortable.min.js'
    ];
}