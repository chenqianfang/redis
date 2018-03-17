<?php

function fs_get_reviews_id($products_id)
    {
        global $db;
        $categories_id = fs_get_data_from_db_fields('categories_id', 'products_to_categories', 'products_id=' . $products_id, '');
        //确定当前分类所在层级
        $subcategories_array = array();
        $this->zen_get_parcategories($subcategories_array, $categories_id);
        $subcategories_array = array_reverse($subcategories_array);
        $subcategories_array[] = $categories_id;
        $n = count($subcategories_array);
        if ($n > 3) {
            $categories_id = $subcategories_array[3];
        }
        $sql = "SELECT reviews_id FROM reviews r 
			where (r.products_id = 0 and r.categories_id = $categories_id) 
			or (r.products_id = $products_id and r.categories_id = 0) 
			or (r.products_id = $products_id and r.categories_id = $categories_id)";

        $result = get_redis_key_value($sql,'reviews-id');//读取redis缓存
        if($result){
            return $result;
        }else{
            $result = $db->Execute($sql);
            $reviews_id = array();
            if ($result->RecordCount()) {
                while (!$result->EOF) {
                    $reviews_id[] = $result->fields['reviews_id'];
                    $result->MoveNext();
                }
            }
            $reviews_id[] = '0';//供sql查询join使用，避免报错
            set_redis_key_value($sql,$reviews_id,3600*24*7,'reviews-id');//设置redis缓存
            return $reviews_id;
        }

    }
