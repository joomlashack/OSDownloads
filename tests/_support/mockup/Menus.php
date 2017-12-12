<?php
class Menus
{
    public function getItem($itemId)
    {
        global $menus;

        foreach ($menus as $menu) {
            if ($menu->id === $itemId) {
                return $menu;
            }
        }

        return false;
    }
}
