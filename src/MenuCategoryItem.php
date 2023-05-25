<?php

namespace plokko\MenuBuilder;

use plokko\MenuBuilder\MenuItem;

/**
 * @method $this icon(string $value) Set item icon
 * @method $this color(string $value) Set item text color
 * @method $this class(string $value) Set menu item CSS class
 *
 */
class MenuCategoryItem extends MenuItem
{

    public function subItem($name, $forceTwoLevels = true): MenuItem
    {
        return $this->item($name);
    }


    function _toMenuItem(array $opt = [],$level=0)
    {
        $data = parent::_toMenuItem($opt,$level);
        $data['type'] = 'category';
        unset($data['url']);

        return $data;
    }
}
