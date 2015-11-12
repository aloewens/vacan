<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* Andro Loewenstein : Openlife 31/10/2015 */
/**
 * menu_ul()
 * Genera una lista desordenada con la clase current en el elemento seleccionado.
 */
if ( ! function_exists('menu_ul'))
{
    function menu_ul($sel = 'home')
   {
        $CI =& get_instance();
        $items = $CI->config->item('navigation');
        
        $menu = '<ul class="main_nav">'."\n";
        foreach($items as $item)
        {
            $current = (in_array($sel, $item)) ? ' class="current"' : '';
            $id = (!empty($item['id'])) ? ' id="'.$item['id'].'"' : '';
            $menu .= '<li'.$current.'><a href="'.$item['link'].'"'.$id.'>'.$item['title'].'</a></li>'."\n";
        }
        $menu .= '</ul>'."\n";
        return $menu;
    }
}

// ------------------------------------------------------------------------

/**
 * menu_p
 * Genera un parrafo con la clase current en el elemento seleccionado
 */
if ( ! function_exists('menu_p'))
{
    function menu_p($sel = 'home', $separator = '')
   {
        $CI =& get_instance();
        $items = $CI->config->item('navigation');

        $count = count($items);
        $i = 0;
        $menu = "\n".'<p class="bottom_nav">';
        foreach($items as $item)
        {
            $current = (in_array($sel, $item)) ? ' class="current"' : '';
            $id = (!empty($item['id'])) ? ' id="'.$item['id'].'"' : '';
            $menu .= '<a'.$current.' href="'.$item['link'].'"'.$id.'>'.$item['title'].'</a>';
            $i++;
            if($count != $i)
            {
                $menu .= ' '.$separator.' ';
            }
        }
        $menu .= '</p>'."\n";
        return $menu;
    }
}
