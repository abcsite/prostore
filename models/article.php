<?php

class Article extends Model
{

    public function getList($only_published = false)
    {
        $sql = 'select * from articles where 1';
        if ($only_published) {
            $sql .= ' and is_published = 1';
        }
        $sql .= ' order by title ';
        return $this->db->query($sql);
    }

    public function getById($id)
    {
        $id = (int)$id;
        $sql = "select * from articles where id = '{$id}' limit 1";
        $result = $this->db->query($sql);
        return isset($result[0]) ? $result[0] : null;
    }

    public function getCategoryByIds($ids)
    {
        if (is_array($ids)) {
            $in_ids = '';
            foreach ($ids as $id) {
                $in_ids = $in_ids . ',' . $id;
            }
            $in_ids = substr($in_ids, 1);
        } else {
            return false;
        }

        $sql = "select * from categories where id IN ({$in_ids}) ";
        $result = $this->db->query($sql);
        return $result;
    }

    public function getCategories()
    {
        $sql = "SELECT DISTINCT  *  FROM  categories c WHERE 1 ORDER BY c.category_name ";
        $result = $this->db->query($sql);
        return $result;
    }

    public  function getCategList($only_published = false){
        $sql = 'select * from categories where 1';
        if ( $only_published) {
            $sql .= ' and displayed = 1';
        }
        $sql .= ' order by category_name ';
        return $this->db->query($sql);
    }

    public function getCategoryByName($name)
    {
        $sql = "SELECT DISTINCT  *  FROM  categories c WHERE c.category_name = '{$name}' ORDER BY c.category_name ";
        $result = $this->db->query($sql);
        return $result;
    }

    public function getTagsAll()
    {
        $sql = $sql = "SELECT tags FROM articles  ";
        $result = $this->db->query($sql);
        return $result;
    }

    public function getCategByArticleId($id)
    {
        $id = (int)$id;
        $sql = "SELECT DISTINCT  c.id cat_id, c.category_name  cat_name  FROM categories_of_article ca JOIN categories c  
                ON ca.id_article = '{$id}' AND ca.id_category = c.id ORDER BY c.category_name
         ";
        $result = $this->db->query($sql);
        return $result;
    }


    public function getArticlesFilter($filter, $only_published = false)
    {
        if (isset($filter['categ']) && is_array($filter['categ'])) {
            $in_cat = '';
            foreach ($filter['categ'] as $cat) {
                $cat = $this->db->escape($cat);
                $in_cat = $in_cat . ',' . $cat;
            }
            $in_cat = substr($in_cat, 1);
            $in_cat = " ca.id_category IN ('{$in_cat}')";
            $join_right = '';
        } else {
            $in_cat = '1 = 1';
            $join_right = 'RIGHT';
        }

        if ( isset($filter['tags']) && is_array($filter['tags'])) {
            $in_tag = '';
            foreach ($filter['tags'] as $tag) {
                $tag = $this->db->escape($tag);
                $str = " AND a.tags LIKE '%" . $tag . "%' ";
                $in_tag = $in_tag . $str;
            }
            $in_tag = substr($in_tag, 4);
        } else {
            $in_tag = '1 = 1';
        }

        if (isset($filter['action_type']) && is_array($filter['action_type'])) {
            $in_type = '';
            foreach ($filter['action_type'] as $type) {
                $type = $this->db->escape($type);
                $in_type = $in_type . ',' . "'" . $type . "'" ;
            }
            $in_type = substr($in_type, 1);
            $in_type = " a.action_type IN ({$in_type})";
        } else {
            $in_type = ' 1 = 1 ';
        }


        if ( isset($filter['price_min']) && $filter['price_max']) {
            $price_min = $this->db->escape($filter['price_min']);
            $price_min_str = " a.price >= '{$price_min}' ";
        } else {
            $price_min_str = " 1 ";
        }

        if ( isset($filter['price_max']) && $filter['price_max']) {
            $price_max = $this->db->escape($filter['price_max']);
            $price_max_str = " a.price <= '{$price_max}' ";
        } else {
            $price_max_str = " 1 ";
        }




        if ( isset($filter['date_min']) && $filter['date_min']) {
            $date_min = $this->db->escape($filter['date_min']);
            $date_min_str = " a.date_published >= '{$date_min}' ";
        } else {
            $date_min_str = " 1 ";
        }

        if ( isset($filter['date_max']) && $filter['date_max']) {
            $date_max = $this->db->escape($filter['date_max']);
            $date_max_str = " a.date_published <= '{$date_max}' ";
        } else {
            $date_max_str = " 1 ";
        }

        if ( isset($filter['order_by']) && $filter['order_by']) {

            if (is_array($filter['order_by'])) {
                $order_by_arr = $filter['order_by'];
            } else {
                $order_by_arr[0] = $filter['order_by'];
            }
            $order_by_list = [];
            foreach ($order_by_arr as $order_by) {
                if (substr($order_by, 0, 1) == '-') {
                    $order_by = substr($order_by, 1);
                    $order_by_list[] = "  {$order_by} DESC  ";
                } else {
                    $order_by_list[] = " {$order_by} ";
                }
            }
            $order_by_str = ' ORDER BY ' . implode(',' , $order_by_list);


        } else {
            $order_by_str = '';
        }

        if ( isset($filter['limit_count']) && $filter['limit_count']) {
            $limit_count = " LIMIT {$filter['limit_count']} ";

            if ( isset($filter['limit_offset']) && $filter['limit_offset']) {
                $limit_offset = " OFFSET {$filter['limit_offset']} ";
            } else {
                $limit_offset = '';
            }

        } else {
            $limit_count = '';
            $limit_offset = '';
        }

        $is_published = ($only_published) ? ' AND a.is_published = 1 ' : '';

        $sql = "SELECT DISTINCT  GROUP_CONCAT(ca.id_category) cat_id,  
                a.id art_id,  a.title art_title, a.description art_desc,  a.author art_author, a.text art_text,  
                a.is_published art_publish, a.date_created art_creat, a.date_published art_date, a.category art_categ, 
                a.tags art_tags, a.visited art_visit, a.comments_count art_comments_count, a.price art_price, a.action_type art_action_type, a.action_price art_action_price
                FROM categories_of_article ca {$join_right} JOIN  articles a  ON  ca.id_article = a.id  
                WHERE {$in_cat}  AND  {$in_tag} AND {$in_type} AND {$price_min_str} AND {$price_max_str} 
                AND {$date_min_str} AND {$date_max_str}  {$is_published}
                GROUP BY a.id
                {$order_by_str}  {$limit_count} {$limit_offset} 
             ";

        $result = $this->db->query($sql);
        return $result;
    }






    public  function getSpecificationsList(){
        $sql = 'select * from specifications  order by specification_name ';

        return $this->db->query($sql);
    }
    
    public  function getOptionsBySpecificationId($specif_id) {
        $specif_id = (int)$specif_id;
        $sql = "select * from options_of_specification  where specificat_id = '{$specif_id}'  ";

        return $this->db->query($sql);
    }

    public  function getOptionsByProductId($product_id, $specification_id = null){
        $product_id = (int)$product_id;
        
        if (isset($specification_id)) {
            $specification_id = (int)$specification_id;
            $str =  " AND id_of_specification = '{$specification_id}' " ;
        } else {
            $str = ' ' ;
        }

        $sql = "select * from options_of_product where id_of_product = '{$product_id}' {$str}  ";

        return $this->db->query($sql);
    }





    public function searchWordsInTitleOfArticles($words, $max_count, $min_length, $only_published = false)
    {
        if ($words) {

            $like_words = '';
            foreach ($words as $word) {
                if (mb_strlen($word) >= $min_length) {
                    $word = $this->db->escape($word);
                    $str = " AND title LIKE '%" . $word . "%' ";
                    $like_words = $like_words . $str;
                }
            }
            if ($like_words !== '') {
                $is_published = ($only_published) ? '  is_published = 1 ' : ' 1 = 1 ';
                $max_count = ($max_count) ? $max_count : 0;
    
                $sql = "SELECT * FROM   articles    WHERE {$is_published} {$like_words} LIMIT {$max_count}  ";
                return $this->db->query($sql);
            }
        } 
        return false;
    }

    public function searchWordsInCategories($words, $max_count, $min_length, $only_published = false)
    {
        if ($words) {

            $like_words = '';
            foreach ($words as $word) {
                if (mb_strlen($word) >= $min_length) {
                    $word = $this->db->escape($word);
                    $str = " AND category_name LIKE '%" . $word . "%' ";
                    $like_words = $like_words . $str;
                }
            }
            if ($like_words !== '') {
                $is_published = ($only_published) ? '  displayed = 1 ' : ' 1 = 1 ';
                $max_count = ($max_count) ? $max_count : 0;

                $sql = "SELECT * FROM   categories    WHERE {$is_published} {$like_words} LIMIT {$max_count}  ";
                return $this->db->query($sql);
            }
        }
        return false;
    }

    

    public function getMaxValue($table, $field, $whereField = '1', $whereValue = '1')
    {
        $sql = "select max({$field}) as max from {$table} where {$whereField} = '{$whereValue}' ";
        $res = $this->db->query($sql);

        return $res[0]['max'];
    }

    
    
    public function save($data, $id = null)
    {
        $data['author'] = ( isset($data['author']) ) ? $data['author'] : '';
        $data['title'] = ( isset($data['title']) && $data['title'] != '' ) ? $data['title'] : '';
        $data['description'] = ( isset($data['description']) ) ? $data['description'] : '';
        $data['text'] = ( isset($data['text']) ) ? $data['text'] : '';
        $data['tags'] = ( isset($data['tags']) ) ? $data['tags'] : '';
        $data['price'] = ( isset($data['price']) && $data['price'] != ''  ) ? $data['price'] : 0;
        $data['action_type'] = ( isset($data['action_type']) ) ? $data['action_type'] : '';
        $data['action_price'] = ( isset($data['action_price']) && $data['action_price'] != '' ) ? $data['action_price'] : 0;

        if ($data['title'] == '' ) {
            return false;
        }

        $id = (int)$id;
        $author = $this->db->escape($data['author']);
        $title = $this->db->escape($data['title']);
        $description = $this->db->escape($data['description']);
        $text = $this->db->escape($data['text']);
        $tags = $this->db->escape($data['tags']);
        $price = $this->db->escape($data['price']);
        $action_type = $this->db->escape($data['action_type']);
        $action_price = $this->db->escape($data['action_price']);
        $is_published = isset($data['is_published']) ? 1 : 0;

        if (!$id) {  
            $date_created = date("Y-m-d h:i:s");

            $sql = "
                insert into articles
                  set author = '{$author}',
                      title = '{$title}',
                      description = '{$description}',
                      text = '{$text}',
                      is_published = '{$is_published}',
                      date_created = '{$date_created}',
                      date_published = '{$date_created}',
                      tags = '{$tags}',
                      price = '{$price}',
                      action_type = '{$action_type}',
                      action_price = '{$action_price}'
            ";

        } else {  

            if (isset($data['date_created']) && $data['date_created'] !=='') {
                $date_created = $data['date_created'];
            } else {
                $date_created = date("Y-m-d H:i:s");
            }

            if (isset($data['date_published']) && isset($data['is_published_old'])) {
                if (isset($data['is_published']) && $data['is_published_old'] == 0) {
                    $date_published = date("Y-m-d H:i:s");
                } else {
                    $date_published = $data['date_published'];
                }
            } else {
                return false;
            }

            $sql = "
                update articles
                  set author = '{$author}',
                      title = '{$title}',
                      description = '{$description}',
                      text = '{$text}',
                      is_published = '{$is_published}',
                      date_created = '{$date_created}',
                      date_published = '{$date_published}',
                      tags = '{$tags}',
                      price = '{$price}',
                      action_type = '{$action_type}',
                      action_price = '{$action_price}'
                  where id = {$id}
            ";
        }

        return $this->db->query($sql);
    }
    
    
    
    public function demo_mode_save($date_published, $id = null)
    {
        $sql = "
            update articles
              set date_published = '{$date_published}'
              where id = {$id}
        ";
        
        return $this->db->query($sql);
    }





    public  function  saveOptions($options, $value_type, $product_id){

        $product_id = (int)$product_id;

        $sql = "delete from options_of_product where id_of_product = '{$product_id}' ";
        $result = $this->db->query($sql);

        if (is_array($options) && count($options) ) {
            foreach ($options as $specific_id => $specific_options) {
                $specific_id = (int)$specific_id;

                if ( isset($value_type[$specific_id]) && $value_type[$specific_id] != '') {
                    $val_type = $this->db->escape($value_type[$specific_id]);
                } else {
                    $val_type = 'text';
                }

                $field_type = 'custom_value_' . $val_type;
                $arr = [];

                foreach ($specific_options as $option_id => $option_val) {

                    if ($option_id == 0) {
                        
                        $option_val = (is_array($option_val)) ? $option_val : [$option_val] ;
                        foreach ($option_val as $custom_option_id => $custom_option_val) {

                            $custom_option_val = $this->db->escape($custom_option_val);

                            if ($val_type == 'text') {
                                $custom_option_val = "'" . $custom_option_val . "'" ;
                            } elseif ($val_type == 'numeric') {
                                if (is_numeric($custom_option_val)) {
                                    $custom_option_val = (int)$custom_option_val;
                                } else {
                                    continue;
                                }
                            } elseif ($val_type == 'timestamp') {
                                $custom_option_val = (int)strtotime($custom_option_val);
                            }

                            $arr[] = ' (' . $product_id . ', ' . $specific_id . ', ' . 0 . ', ' . $custom_option_val . ' ) ' ;
                        }
                    } else {
                        $option_id = (int)$option_id;

                        $arr[] = ' (' . $product_id . ', ' . $specific_id . ', ' . $option_id . ', ' . 'NULL' . ' ) ' ;
                    }
                }
                $str = implode(',' , $arr);

                $sql = " insert into options_of_product (id_of_product, id_of_specification, id_of_option, {$field_type} )
                          values {$str} 
                           ";

                if ($result) {
                    $result = $result && $this->db->query($sql);
                }
            }
        }
        return $result;
    }






    public function delete($id)
    {
        $id = (int)$id;
        $result = true;

        $resArr = $this->getImgsByArticleId($id);
        if ($resArr) {
            foreach ($resArr as $img) {
                $uploadfile = ARTICLES_IMG_PATH . DS . $img['full_name'];
                $result = $result && unlink($uploadfile);
            }
        }

        $sql = "delete from articles where id = {$id}";
        $result = $result && $this->db->query($sql);

        $sql = "delete from images_of_article where id_article = {$id}";
        $result = $result && $this->db->query($sql);

        return $result;
    }

    
    
    
    
    
    
    public function getImgsByArticleId($id)
    {
        $id = (int)$id;
        $sql = "select * from images_of_article where id_article = '{$id}' order by id ";
        $result = $this->db->query($sql);
        return $result;
    }


    public function saveImages($data, $id = null)
    {
        $id = (int)$id;
        $maxId = $this->getMaxValue('articles', 'id');

        if ($id) {
            $maxNum = $this->getMaxValue('images_of_article', 'num', 'id_article', $id);
            $maxNum = ($maxNum === null) ? 0 : $maxNum + 1;
        } else {
            $maxNum = 0;
        }

        $maxImgId = $this->getMaxValue('images_of_article', 'id');
        $maxImgId = ($maxImgId === null) ? 0 : $maxImgId;

        $countImgs = count($data['name']);
        $result = true;

        for ($i = 0; $i < $countImgs; $i++) {
            $num = $i + $maxNum + 1;
            $imgId = $maxImgId + $i + 1;

            if ($data['name'][$i] == '') {
                break;
            } else {
                $name = $this->db->escape(basename($data['name'][$i]));
                $full_name = $id . '_' . $name;
                $uploadfile = ARTICLES_IMG_PATH . DS . $full_name;
                if (!move_uploaded_file($data['tmp_name'][$i], $uploadfile)) {
                    $result = false;
                    break;
                }
            }
            if ($result) {

                if (!$id) {
                    $sql = "
                        insert into images_of_article
                          set id = '{$imgId}',
                              id_article = '{$maxId}',
                              num = '{$num}',
                              name = '{$name}',
                              full_name = '{$full_name}'
                    ";
                } else {
                    $sql = "
                        insert into images_of_article
                          set id = '{$imgId}',
                              id_article = '{$id}',
                              num = '{$num}',
                              name = '{$name}',
                              full_name = '{$full_name}'
                    ";
                }

                if (!$this->db->query($sql)) {
                    $result = false;
                    break;
                }
            }
        }
        return $result;

    }

    
    public function replaceImages($data, $id)
    {
        $id = (int)$id;
        $result = true;

        foreach ($data['name'] as $imgId => $imgName) {
            if ($imgName != '') {
                $name = $this->db->escape($imgName);
                $full_name = $id . '_' . $name;

                $sql = "select full_name from images_of_article where id = {$imgId} ";
                $res = $this->db->query($sql);

                if ($res) {
                    $old_full_name = $res[0]['full_name'];
                    $result = $result && true;
                } else $result = false;

                $uploadfile = ARTICLES_IMG_PATH . DS . $full_name;

                if ($result) {
                    $result = $result && unlink(ARTICLES_IMG_PATH . DS . $old_full_name);
                    $result = $result && move_uploaded_file($data['tmp_name'][$imgId], $uploadfile);
                }
                if ($result) {
                    $sql = "
                            update images_of_article
                              set name = '{$name}',
                                  full_name = '{$full_name}'
                              where id = {$imgId}
                            ";
                    $result = $result && $this->db->query($sql);
                }
            }
        }
        return $result;
    }
    
    
    public function checkImagesByHTML($post, $article_id)
    {
        $article_id = (int)$article_id;
        $text = $post['text'];

        /* Получение списка всех файлов изображений для всех статей */
        $all_images = scandir(ARTICLES_IMG_PATH);

        /* Перебор файлов изображений и поиск файлов, принадлежащих данной статье */
        foreach ($all_images as $image_name) {

            if ($image_name === '.' || $image_name == '..') {
                continue;
            }
            
            /* Получение из имени файла подстроки с номером статьи и подстроки с изначальным именем файла */
            $position_substr = stripos($image_name, '_');
            $article_id_by_image = (int)substr($image_name, 0 , $position_substr);
            $source_image_name = substr($image_name, $position_substr + 1);

            /* Для случая редактирования пользователем уже существующей статьи находим и обрабатываем изображения этой статьи*/
            if ( ($post['id']) && ($article_id_by_image === $article_id) ) {

                /* В введенном пользователем тексте (и изображениях) плагин TinyMCE автоматически меняет ссылки на картинки, поэтому здесь ссылки в тексте приводятся к нужному виду */
                $pattern = "@(<img[^<>]*src=\"){1}(../)*(webroot/img/articles/" . $image_name . "\"[^<>]*>)@is";
                $replacement = '${1}/${3}';
                $text = preg_replace($pattern, $replacement, $text, -1, $count);

                if (!$count) {
                    /* Удаление из БД и с диска этого изображения в случае, если на него отсутствуют ссылкм в тексте статьи (например, пользователь в редакторе удалил изображение ) */

//                    $this->del_image($image_name);

                } else {
                    /* Проверка наличия в БД записи этого изображения для данной статьи и в случае отсутствия - добавление такой записи */
                    $images_by_article_id = $this->getImgsByArticleId($article_id);
                    $images_name_by_article_id = array_column( $images_by_article_id, 'full_name');

                    if ( !in_array($image_name, $images_name_by_article_id) ) {

                        $sql = "
                            insert into images_of_article
                              set id_article = '{$article_id}',
                                  num = '0',
                                  name = '',
                                  full_name = '{$image_name}'
                        ";
                        $this->db->query($sql);
                    }
                }

            }

            /* Для случая редактирования и сохранения пользователем новой статьи находим и обрабатываем изображения этой статьи*/
            if ( ( !$post['id'] ) && ($article_id_by_image === -9) ) {

                /* Создаем новое имя для изображения (с префиксом равным id статьи) вместо временного имени (с префиксом '-1') */
                $image_new_name = $article_id . '_' . $source_image_name ;

                /* В тексте статьи в ссылках на данное изображение заменяем старое имя файла на новое, а также немного меняем формат ссылки*/
                $pattern = "@(<img[^<>]*src=\"){1}(../)*(webroot/img/articles/){1}" . $image_name . "(\"[^<>]*>)@is";
//                $pattern = "@(webroot/img/articles/){1}" . $image_name . "@is";
                $replacement = '${1}/${3}' . $image_new_name . '${4}' ;
//                $replacement = '111/${1}' ;
                $text = preg_replace($pattern, $replacement, $text, -1, $count);

                if (!$count) {
                    /* Удаление из БД и с диска этого изображения в случае, если на него отсутствуют ссылкм в тексте статьи (например, пользователь в редакторе удалил изображение ) */

//                    $this->del_image($image_name);

                } else {

                    rename(ARTICLES_IMG_PATH . DS . $image_name,  ARTICLES_IMG_PATH . DS . $image_new_name);

                    /* Проверка наличия в БД записи этого изображения для данной статьи и в случае отсутствия - добавление такой записи */
                    $images_by_article_id = $this->getImgsByArticleId($article_id);
                    $images_name_by_article_id = array_column( $images_by_article_id, 'full_name');

                    if ( !in_array($image_new_name, $images_name_by_article_id) ) {

                        $sql = "
                            insert into images_of_article
                              set id_article = '{$article_id}',
                                  num = '0',
                                  name = '',
                                  full_name = '{$image_new_name}'
                        ";
                        $this->db->query($sql);
                    }
                }
            }
        }
        return $text;
    }


    public function saveTempImages($images, $article_id)
    {
        
        $article_id = (int)$article_id;

        $countImgs = count($images['name']);
        $temp_images_name = [];
        $result = true;

        for ($i = 0; $i < $countImgs; $i++) {
            $imgName = $images['name'][$i];

            if ($imgName == '') {
                continue;
            } else {
                $imgName = $this->db->escape(basename($imgName));
                $full_name = $article_id . '_' . $imgName ;

                $image_path = ARTICLES_IMG_PATH . DS . $full_name;

                $result = move_uploaded_file($images['tmp_name'][$i], $image_path);

                if (!$result) {
                    continue;
                } else {
                    $temp_images_name[] = $full_name;
                }
            }
        }
        if ($result) {
            return $temp_images_name;
        }
        return false;

    }
    

    
    
    
    public function del_image($full_name)
    {
        $sql = "delete from images_of_article where full_name = '{$full_name}' ";
        $result = $this->db->query($sql);

        $image_path = ARTICLES_IMG_PATH . DS . $full_name;
        $result = $result && @unlink($image_path);

        return $result;
    }


    public function update_categ_of_article($categoriesId, $article_id)
    {
        $article_id = (int)$article_id;
        $categories_add_rows = [];

        if (count($categoriesId)) {

            foreach ($categoriesId as $cat_id) {
                $cat_id = (int)$cat_id;
                $categories_add_rows[] = '(' . $article_id . ',' . $cat_id . ')';
            }
        }
        $result = true;

        $sql_del = " delete from categories_of_article  where id_article = '{$article_id}' ";
        $result = $result && $this->db->query($sql_del);

        if (count($categories_add_rows)) {
            $categories_add_str = implode(',', $categories_add_rows);
            $sql_add = " insert into categories_of_article (id_article, id_category) values {$categories_add_str}  ";
            $result = $this->db->query($sql_add);
        }

        return $result;
    }



}


