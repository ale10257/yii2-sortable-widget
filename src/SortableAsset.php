<?php

namespace ale10257\sortable;

use yii\web\AssetBundle;

class SortableAsset extends AssetBundle
{
    public $sourcePath = __DIR__;
    public $js = ['sortable.js'];

    public $depends = [
        'yii\jui\JuiAsset'
    ];
}