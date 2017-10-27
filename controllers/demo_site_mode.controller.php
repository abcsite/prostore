<?php

class demo_site_modeController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Article();
    }

    public function products_update()
    {

        $products = $this->model->getList(false);

        if ( !isset($products) || count($products) < 30) {
            $products = $this->generate_contents();

        }

        if ( isset($products)) {

            $max_date = strtotime('2017-00-01');

            foreach ($products as $product) {
                $date_published = strtotime($product['date_published']);
                if ($date_published > $max_date) {
                    $max_date = $date_published;
                }
            }

            $now = time();
            $count_products = count($products);
            $time_interval = 60 * 60 * 24 * 90 ;

            if ( ($now - $max_date) > (60 * 60 * 24) ) {

                for($i = 0; $i < $count_products; $i++) {
                    $new_time_published = (int) ($now - ($time_interval * $i) - mt_rand(1, $time_interval) );
                    $new_data_published = date('Y-m-d H:i:s', $new_time_published);

                    $this->model->demo_mode_save($new_data_published, $products[$i]['id']);
                }
            }
        }


    }


    public function generate_contents() {

        $words_str = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sed arcu non dolor condimentum mollis. Morbi mi metus, suscipit sit amet eros sit amet, gravida consectetur nisi. Sed sem enim, elementum ac dui in, faucibus finibus dui. Nulla facilisi. Maecenas tincidunt tortor sit amet odio faucibus aliquam. Nam varius consectetur faucibus. Vestibulum accumsan, magna feugiat fermentum finibus, diam arcu convallis magna, non tristique purus libero at sem. Nam mollis mauris ante, at laoreet felis egestas a. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Quisque eget mauris condimentum, fermentum risus ut, efficitur metus. Ut pretium.';
        $words_arr = explode(' ', $words_str);
        $words_all = [];
        foreach ($words_arr as $word) {
            $word = trim($word, '.,:;?!- ');
            if (strlen($word) > 4) {
                $words_all[] = strtolower($word);
            }
        }
        $words = [];
        foreach ($words_all as $word) {
            $word = trim($word, '.,:;?!- ');
            if (strlen($word) > 4) {
                $words[] = strtolower($word);
            }
        }

        function str_generator($words, $count_words_min = 1, $count_words_max = 4, $first_to_upper = true) {
            $count_all = count($words);
            $count = mt_rand($count_words_min, $count_words_max);
            $arr = [];

            for ($i = 0; $i < $count; $i++) {
                $ind_rand = (int)mt_rand(0, $count_all - 1);
                $arr[] = $words[$ind_rand];
            }
            $str = implode(' ', $arr);
            if ($first_to_upper) {
                $str = ucfirst($str);
            }

            return $str;
        }

        $user = [];
        $user_count = 10;
        $user_model = new User();
        $users_all = $user_model->getList();
        $users_id = array_column($users_all, 'id');
        $users_logins = array_column($users_all, 'login');

        for ($i = 0; $i < $user_count; $i++) {
            $user[$i]['login'] = 'user_' . $i;
            $user[$i]['email'] = str_generator($words, 1, 1, false) . '@' . str_generator($words, 1, 1, false)  . '.com' ;
            $user[$i]['is_active'] = 1;
            $user[$i]['role'] = 'user';

            $password = 'user_' . $i;
            $valid = new Validation( App::$db);
            $user[$i]['password'] = $valid->filter_hash_md5($password);

            if ( !in_array($user[$i]['login'], $users_logins) ) {
                $user_model->register($user[$i]);
            }
        }

        $dir_demo_images = ROOT . DS . 'webroot' . DS . 'img' . DS . 'demo_mode'  ;
        $dir_scan = scandir($dir_demo_images);
        $all_dir_of_images = [];

        foreach ($dir_scan as $dir) {
            if ($dir === '.' || $dir == '..') {
                continue;
            }
            $all_dir_of_images[] = $dir;
        }

        $dir_images_count = count($all_dir_of_images);

        $products_model = new Article();
        $old_products = $products_model->getList(false);
        $old_products_titles = array_column($old_products, 'title');

        $new_products = [];
        $new_products_count = 50;

        for ($i = 0; $i < $new_products_count; $i++) {
            $new_products[$i]['title'] = 'Product_' . ($i + 1) ;
            $new_products[$i]['description'] = str_generator($words, 7, 10, true);
            $new_products[$i]['text'] = str_generator($words, 15, 30, true) . str_generator($words, 15, 30, true) . str_generator($words, 15, 30, true) . str_generator($words, 15, 30, true) ;
            $new_products[$i]['author'] = str_generator($words, 1, 1, true) . ' ' . str_generator($words, 1, 1, true) ;
            $new_products[$i]['is_published'] = 1;

            $now = time();
            $time_interval = 60 * 60 * 24 * 90;
            $time_random = (int)mt_rand($now - $time_interval, $now);

            $new_products[$i]['date_created'] = date('Y-m-d h:i:s' ,$time_random );
            $new_products[$i]['date_published'] = date('Y-m-d h:i:s' ,$time_random );
            $new_products[$i]['tags'] = str_generator($words, 1, 2, false);
            $new_products[$i]['visited'] = (int)mt_rand(0, 50);
            $new_products[$i]['price'] = 100 * ((int)mt_rand(10, 300));

            $action_type = ['Скидка', 'Распродажа', 'Акция'];
            $action_type_id = (int)mt_rand(0, count($action_type) - 1);
            $rand_percent = (int)mt_rand(0, 100);
            $new_products[$i]['action_type'] = ($rand_percent < 30) ? $action_type[$action_type_id] : null;
            $action_type_str = (isset($new_products[$i]['action_type'])) ? "action_type = '" . $new_products[$i]['action_type'] . "', " : ' ';

            $new_products[$i]['action_price'] = 5 * ((int)mt_rand(-8, -1));

            $new_products[$i]['comments_count'] = ((int)mt_rand(5, 10));

            $sql = "
                insert into articles
                  set author = '{$new_products[$i]['author']}',
                      title = '{$new_products[$i]['title']}',
                      description = '{$new_products[$i]['description']}',
                      text = '{$new_products[$i]['text']}',
                      is_published = '{$new_products[$i]['is_published']}',
                      date_created = '{$new_products[$i]['date_created']}',
                      date_published = '{$new_products[$i]['date_published']}',
                      tags = '{$new_products[$i]['tags']}',
                      visited = '{$new_products[$i]['visited']}',
                      comments_count = 0,
                      price = '{$new_products[$i]['price']}',
                      {$action_type_str}
                      action_price = '{$new_products[$i]['action_price']}'
            ";

            $db = App::$db;

            if ( !in_array($new_products[$i]['title'], $old_products_titles) ) {
                $db->query($sql);
            }

            $last_product_id = $products_model->getMaxValue('articles', 'id');

            $dir_images = $i % $dir_images_count + 1;
            $images = scandir($dir_demo_images . DS . $dir_images);

            foreach ($images as $image ) {
                if ($image === '.' || $image == '..') {
                    continue;
                }

                $name = $db->escape(basename($image));
                $full_name = $last_product_id . '_' . $name;
                $source_path = $dir_demo_images . DS . $dir_images . DS . $name;
                $new_path = ARTICLES_IMG_PATH . DS . $full_name;
                copy($source_path, $new_path);

                $sql = "
                        insert into images_of_article
                          set id_article = '{$last_product_id}',
                              num = 0,
                              name = '{$name}',
                              full_name = '{$full_name}'
                    ";

                $db->query($sql);
            }


            $article_view_model = new Article_view();

            $users_count = count($users_id);
            $count_parent_comments = (int)mt_rand(3, 4);

            for ($j = 0; $j < $count_parent_comments; $j++) {
                $comment = [];
                $comment['id_comment'] = 0 ;
                $comment['id_article'] = $last_product_id ;
                $comment['id_user'] = $users_id[(int)mt_rand(0, $users_count - 1)] ;
                $comment['text'] = str_generator($words, 7, 20, true);
                $comment['is_published'] = 1;
                $comment['recommend'] = (int)mt_rand(-1, 1);

                $article_view_model->comment_save($comment);

                $comments_by_article_id = $article_view_model->getCommentsByArticleId($last_product_id, ' ');
                $comments_id = array_column($comments_by_article_id, 'id_comment');
                $max_id = 1;
                foreach($comments_id as $id){
                    if ( $max_id < (int)$id) {
                        $max_id = (int)$id;
                    }
                }

                $count_like_comment = (int)mt_rand(0, 3);
                for ($m1 = 0; $m1 < $count_like_comment; $m1++) {
                    $article_view_model->addLike($max_id, $users_id[(int)mt_rand(0, $users_count - 1)]);
                }
                $count_dislike_comment = (int)mt_rand(0, 2);
                for ($m2 = 0; $m2 < $count_dislike_comment; $m2++) {
                    $article_view_model->addDislike($max_id, $users_id[(int)mt_rand(0, $users_count - 1)]);
                }

                $count_child_comments = (int)mt_rand(2, 3);

                for ($n = 0; $n < $count_child_comments; $n++) {
                    $child_comment = [];
                    $child_comment['id_comment'] = $max_id ;
                    $child_comment['id_article'] = $last_product_id ;
                    $child_comment['id_user'] = $users_id[(int)mt_rand(0, $users_count - 1)] ;
                    $child_comment['text'] = str_generator($words, 7, 20, true);
                    $child_comment['is_published'] = 1;
                    $child_comment['recommend'] = $comment['recommend'] ;

                    $article_view_model->comment_save($child_comment);

                    $comments_by_article_id = $article_view_model->getCommentsByArticleId($last_product_id, ' ');
                    $comments_id = array_column($comments_by_article_id, 'id_comment');
                    $child_max_id = 1;
                    foreach($comments_id as $id){
                        if ( $child_max_id < (int)$id) {
                            $child_max_id = (int)$id;
                        }
                    }

                    $count_like_comment = (int)mt_rand(0, 3);
                    for ($h1 = 0; $h1 < $count_like_comment; $h1++) {
                        $article_view_model->addLike($child_max_id, $users_id[(int)mt_rand(0, $users_count - 1)]);
                    }
                    $count_dislike_comment = (int)mt_rand(0, 2);
                    for ($h2 = 0; $h2 < $count_dislike_comment; $h2++) {
                        $article_view_model->addDislike($child_max_id, $users_id[(int)mt_rand(0, $users_count - 1)]);
                    }
                }
            }

            $get_comments_all = $article_view_model->getCommentsByArticleId($last_product_id, '', true);
            $get_comments_count = (isset($get_comments_all)) ? count($get_comments_all) : 0 ;
            $article_view_model->comments_counter($last_product_id, $get_comments_count);



            $specifications_all = $this->model->getSpecificationsList();

            $options_of_product = [];
            foreach ($specifications_all as $specif) {
                $options = $this->model->getOptionsBySpecificationId($specif['specification_id']);
                if ($specif['specification_id'] == 13 || $specif['specification_id'] == 15 ) {
                    foreach ($options as $option) {
                        $options_of_product[$specif['specification_id']][$option['option_id']] = 0;
                    }
                } else {
                    $options_count = count($options);
                    $options_ind_rand = mt_rand(0, $options_count - 1 );
                    $options_of_product[$specif['specification_id']][$options[$options_ind_rand]['option_id']] = 0;
                }

            }
            
            $this->model->saveOptions($options_of_product, [], $last_product_id);



            $categories = $this->model->getCategories();
            $categories_line = structure_to_line($categories, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id']);

            $categories_count = count($categories_line);
            $categories_of_product_count = 5;

            $product_categories_id = [];
            for ($p = 0; $p < $categories_of_product_count; $p++) {
                $categories_ind_rand = mt_rand(0, $categories_count - 1 );
                $ceteg_id = $categories_line[$categories_ind_rand]['id'];
                $product_categories_id[] = $ceteg_id;

                $parents_categories_by_id = structure_to_line($categories, $options = ['begin_id' => $ceteg_id, 'nested_level' => 0, 'field_id' => 'parent_id', 'field_id_parent' => 'id']);
                foreach ($parents_categories_by_id as $parent_categ) {
                    $product_categories_id[] = $parent_categ['id'];
                }
            }
            $this->model->update_categ_of_article($product_categories_id, $last_product_id);

        }
    }

}
