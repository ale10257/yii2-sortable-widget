<?php

namespace ale10257\sortable;

use yii\base\InvalidArgumentException;
use yii\base\Widget;
use yii\jui\JuiAsset;

class SortableJsWidget extends Widget
{
    public ?string $cssSelector = null;
    public ?string $ajaxUrl = null;

    public function run()
    {
        $view = $this->getView();
        JuiAsset::register($view);
        if (!$this->cssSelector) {
            throw new InvalidArgumentException('Unknown css selector');
        }
        $js = "
            $(function () {
                let el = document.querySelector('$this->cssSelector')
                if (!el) {
                    console.error('Element $this->cssSelector not found!')
                    return false
                }
                el = el.tagName.toLowerCase()
                if (el !== 'table' && el !== 'ul') {
                    console.error('Element must be table or ul only!')
                    return false
                }
                let items = $('$this->cssSelector')
                items.each(function () {
                    let url = '$this->ajaxUrl'
                    if (!url) {
                        console.error('Url is undefined!')
                    }
                    let sortable
                    if (el === 'table') {
                        sortable = $(this).find('tbody')
                        if (!sortable) {
                            console.error('tbody in table not found!')
                            return false
                        }
                    } else {
                        sortable = $(this)
                    }
                    sortable.sortable({
                        cursor: 'move',
                        update: (event, ui) => {
                            if (ui.item.data('excluded') !== undefined) {
                                alert('You cannot change inactive positions!')
                                return false
                            }                            
                            if (url) {
                                let prev = ui.item.prev()
                                let previous_id = prev.length === 0 ? 0 : prev.data('id')
                                let id = ui.item.data('id')
                                $.post(url, {id, previous_id})                                
                            }
                        },
                        delay: 200
                    })
                })
            })
        ";
        $view->registerJs($js);
    }
}