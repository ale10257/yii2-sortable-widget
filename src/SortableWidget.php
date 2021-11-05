<?php

namespace ale10257\sortable;

use yii\base\Widget;

class SortableWidget extends Widget
{
    public string $cssSelector = '.sortable';
    public array $pluginOptions = [];
    public array $defaultOptions = [
        'onMove' => '(e) => {
            if (e.dragged.dataset.excluded !== undefined) {
                return false;
            }
        }',
        'onEnd' => 'async (e) => {
            if (url) {
                let previous = e.to.children[e.newIndex - 1]
                await fetch(url, {
                    method: \'POST\',
                    headers: {
                        \'Content-Type\': \'application/json;charset=utf-8\',
                        \'X-CSRF-Token\': document.querySelector(\'meta[name="csrf-token"]\').content
                    },
                    body: JSON.stringify({
                        id: e.item.dataset.id,
                        previous_id: e.newIndex === 0 ? 0 : previous.dataset.id
                    })
                })
            }
        }'
    ];

    public function run()
    {
        $this->pluginOptions = array_merge($this->defaultOptions, $this->pluginOptions);
        $pluginOptions = [];
        foreach ($this->pluginOptions as $option => $value) {
            $pluginOptions[] = "$option: $value";
        }
        $pluginOptions = implode(",\n", $pluginOptions);
        $js = <<<js
        (function () {
            let elements = document.querySelectorAll('$this->cssSelector')
            const options = (url) => {
                return {
                    $pluginOptions
                }
            }
            elements.forEach((el) => {
                let url = el.dataset.url
                if (el.tagName.toLowerCase() === 'table') {
                    el = el.querySelector('tbody')
                    if (!el) {
                        console.error('TBODY in table not found!')
                        return false;
                    }
                }
                Sortable.create(el, options(url))
            })
        }())
js;
        $view = $this->getView();
        SortableJsAssetBundle::register($view);
        $view->registerJs($js);
    }
}