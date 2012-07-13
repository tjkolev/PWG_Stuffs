<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $page, $user, $conf;

$page_save = $page;

if (script_basename() == 'picture'
  or ($datas['cat_display'] == 'wo_thumb' and !empty($page['items']))
  or ($datas['cat_display'] == 'w_thumb' and empty($page['items']) and isset($page['category']))
  or ($datas['cat_display'] == 'selected_cats' and isset($page['category']) and !in_array($page['category']['id'], $datas['cat_selection'])))
{
  return false;
}

$forbidden = get_sql_condition_FandF
  (
    array
      (
        'forbidden_categories' => 'ic.category_id',
        'visible_categories' => 'ic.category_id',
        'visible_images' => 'i.id'
      ),
    'AND'
  );

$query = '
  SELECT DISTINCT (i.id)
    FROM '.IMAGES_TABLE.' as i
      INNER JOIN '.IMAGE_CATEGORY_TABLE.' AS ic ON i.id = ic.image_id
      INNER JOIN '.CATEGORIES_TABLE.' AS c ON ic.category_id = c.id
    WHERE i.hit > 0';

if (isset($page['category']))
{
  $query .= '
        AND ( c.uppercats LIKE \''.$page['category']['uppercats'].',%\' OR c.id = '.$page['category']['id'].' )
  ';
}

$query .= '
      '.$forbidden.'
      ORDER BY i.hit DESC, i.file ASC
    LIMIT 0, '.$datas['nb_images'].'
  ;';

$page['items'] = array_from_query($query, 'id');
$page['start'] = 0;
$page['nb_image_page'] = $datas['nb_images'];
$page['section'] = 'most_visited';

$tpl_thumbnails_var = array();
$pwg_stuffs_tpl_thumbnails_var = & $tpl_thumbnails_var;
include(PHPWG_ROOT_PATH.'include/category_default.inc.php');

if (!empty($pwg_stuffs_tpl_thumbnails_var))
{
  $block['thumbnails'] = $pwg_stuffs_tpl_thumbnails_var;
  $block['derivative_params'] = ImageStdParams::get_by_type(IMG_THUMB);
  $block['TEMPLATE'] = 'stuffs_thumbnails.tpl';
}

$page = $page_save;

?>