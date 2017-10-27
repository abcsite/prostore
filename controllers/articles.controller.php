<?php

class ArticlesController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Article();
    }

    public function index()
    {
        $this->data['pages'] = $this->model->getList(true);
    }



        /* Обработка поиска по продуктам  (поиск одновременно по тегам, заголовкам продуктов и категориям продуктов) */
    public function search_ajax()
    {
        if (isset($this->params[0])) {
            
            $inp = $this->params[0];
            $inp_words = explode(' ', $inp);
            /* Максимальное количество выводимых пользователю найденных слов и минимальная длина вводимых пользователем слов, которые будут обрабатываться */
            $max_count = 10;
            $min_length = 1;
            
            if ($inp_words) {

                /* Поиск в тегах продуктов */
                $res_tags = [];
                foreach ($inp_words as $word) {


                    if (mb_strlen($word) >= $min_length) {
                        $filter['tags'][0] =  $word;
                        $articles_has_word = $this->model->getArticlesFilter($filter, true);

                        foreach ($articles_has_word as $row) {
                            $tags_str = $row['art_tags'];
                            $tags_arr = explode(',', $tags_str);

                            foreach ($tags_arr as $tag) {
                                if (strstr($tag,  $word)) {
                                    $res_tags[$tag] = $tag;
                                }
                            }
                        }
                    }
                }
                $new_res_tags = [];
                foreach ($res_tags as $kay => $val) {
                    $new_res_tags[] = $kay;
                }

                /* Поиск в заголовках продуктов */
                $articles = $this->model->searchWordsInTitleOfArticles($inp_words, $max_count, $min_length, true);

                /* Поиск в категориях продуктов */
                $categories = $this->model->searchWordsInCategories($inp_words, $max_count, $min_length, true);

                $res = [];
                $res['tags'] = $new_res_tags;
                $res['articles'] = $articles;
                $res['categories'] = $categories;
                $res['max_count'] = $max_count;
                $res['min_length'] = $min_length;

                echo(json_encode($res));
            }
        }
        die;
    }


    public function filter()
    {
        /* Задаем параметры и получаем контент для центральной части страницы поиска (список найденных статей) */
        $data_module_articles = [];
        $data_module_articles['articles_url_base'] = '/articles/filter/';
        $data_module_articles['filter'] = $_REQUEST;
        $data_module_articles['filter']['order_by'][] = '-a.date_published';
        $data_module_articles['filter']['order_by'][] = '-a.title';
//deb($_REQUEST);
        $module_articles_list = new Module_articles_listController($data_module_articles);
        $articles_list_data = $module_articles_list->get_articles_filter();
        $this->data['products'] = $articles_list_data;

        //* Если пользователем выбрана категория , передаем ее и родительские категории на страницу (для создания "хлебных крошек") */
        if ( isset($_GET['categ']) && is_array($_GET['categ']) && count($_GET['categ']) === 1) {

            $categories_all = $this->model->getCategList();
            $parents_by_category_id = structure_to_line($categories_all, $options = ['begin_id' => (int)$_GET['categ'][0], 'nested_level' => 0, 'field_id' => 'parent_id', 'field_id_parent' => 'id']);

            $hornav_categories = [];
            foreach ($parents_by_category_id as $parent_categ) {
                $hornav_categories[] = $parent_categ;

                $this->data['selected_categ'] = array_reverse($hornav_categories);
                $this->data['categories_url_base'] = '/articles/filter/';
            }
        }

        /* Для списка продуктов получаем список имен каждого первого изображения в продукте (для вывода в слайдере) */
        $data = $articles_list_data;
        $images = [];
        $articles_count = (isset($data['articles'])) ? count($data['articles']) : 0 ;
        for ($i = 0; $i < $articles_count && $i < 10 ; $i++ ) {
            $article_images = $module_articles_list->getModel()->getImgsByArticleId( $data['articles'][$i]['art_id'] );
            if (isset($article_images[0])) {
                $arr = ['img' => $article_images[0]['full_name'], 'article' => $data['articles'][$i] ];
                $images[] = $arr;
            }
        }
        $this->data['images'] = $images;

        /* Задаем параметры и получаем контент для левой части страницы (список категорий) */
        $data_module_side_left = [];
        $data_module_side_left['categories_url_base'] = '/articles/filter/';
        $module_side_left = new Module_side_leftController($data_module_side_left);
        $this->data['module_side_left'] = $module_side_left->get_view();

        /* Задаем параметры для модуля для получения списка ТОП-продуктов (самых комментируемых) в правой части страницы продуктов  */
        $data_module_articles_top_all = [];
        $data_module_articles_top_all['filter']['order_by'][] = '-a.comments_count';
        $data_module_articles_top_all['filter']['order_by'][] = '-a.date_published';
        $data_module_articles_top_all['filter']['limit_count'] = 5;
        $data_module_articles_top_all['filter']['limit_offset'] = 0;

        $module_articles_top_all = new Module_articles_listController( $data_module_articles_top_all );
        $data_top_all = $module_articles_top_all->get_articles_filter();


        /* Получаем контент для правой части главной страницы */
        $view_data_side_right = [];
        $view_data_side_right['top_all'] = $data_top_all;

        $path_side_right = VIEWS_PATH . DS . 'modules' . DS . 'side_right.html';
        $view_object_side_right = new View($view_data_side_right, $path_side_right);
        $this->data['module_side_right'] = $view_object_side_right->render();


    }


    public function filter_set() {

        $categ_all = $this->model->getCategories();
        $this->data['filter']['categ_all'] = structure_to_line($categ_all, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id']);

        $tags_list_str = $this->model->getTagsAll();
        $res_tags = [];
        foreach ($tags_list_str as $row) {
            $tags_str = $row['tags'];
            $tags_arr = explode(',', $tags_str);
            foreach ($tags_arr as $word) {
                if ( !in_array( $word , $res_tags)){
                    $res_tags[] =  $word ;
                }
            }
        }
        $this->data['filter']['tags_all'] = $res_tags;
        
        
        $specifications_all = $this->model->getSpecificationsList();

        $options_of_specifications = [];
        foreach ($specifications_all as $specif) {
            $options = $this->model->getOptionsBySpecificationId($specif['specification_id']);

            $options_of_specifications[] = ['specification' => $specif,
                                            'options' => $options ] ;
        }
        $this->data['specifications_all'] = $options_of_specifications;

        
        /* Задаем параметры и получаем контент для левой части страницы (список категорий) */
        $data_module_side_left = [];
        $data_module_side_left['categories_url_base'] = '/articles/filter/';
        $module_side_left = new Module_side_leftController($data_module_side_left);
        $this->data['module_side_left'] = $module_side_left->get_view();

        /* Задаем параметры для модуля для получения списка ТОП-продуктов (самых комментируемых) в правой части страницы продуктов  */
        $data_module_articles_top_all = [];
        $data_module_articles_top_all['filter']['order_by'][] = '-a.comments_count';
        $data_module_articles_top_all['filter']['order_by'][] = '-a.date_published';
        $data_module_articles_top_all['filter']['limit_count'] = 5;
        $data_module_articles_top_all['filter']['limit_offset'] = 0;

        $module_articles_top_all = new Module_articles_listController( $data_module_articles_top_all );
        $data_top_all = $module_articles_top_all->get_articles_filter();


        /* Получаем контент для правой части главной страницы */
        $view_data_side_right = [];
        $view_data_side_right['top_all'] = $data_top_all;

        $path_side_right = VIEWS_PATH . DS . 'modules' . DS . 'side_right.html';
        $view_object_side_right = new View($view_data_side_right, $path_side_right);
        $this->data['module_side_right'] = $view_object_side_right->render();

//        if (isset($_POST) ) {
//            return  VIEWS_PATH . DS . 'articles' . DS . 'filter.html';
//        }

    }




    public function admin_index()
    {
        $this->data['articles'] = $this->model->getList();
    }


    public function admin_edit()
    {

        if ($_POST) {
            $article_id = ($_POST['id']) ? $_POST['id'] : null;

            /* Сохраняем основные данные продукта */
            $result = $this->model->save($_POST, $article_id);

            /* Сохраняем добавленные изображения продукта */
            if ($result && isset($_FILES['images']) ) {
                $result = $this->model->saveImages($_FILES['images'], $article_id);
            }

            /* Сохраняем замененные изображения продукта */
            if ($result && isset($_FILES['image']) ) {
                $result = $this->model->replaceImages($_FILES['image'], $article_id);
            }

            /* Если добавлен новый продукт, получаем его id */
            $new_article_id = $this->model->getMaxValue('articles', 'id');
            if (!$article_id) {
                $article_id = $new_article_id;
            }

            /* Сохраняем характеристики и опции продукта */
            $options = ( isset($_POST['options']) ) ? $_POST['options'] : [] ;
            $specification_value_type = ( isset($_POST['specification_value_type']) ) ? $_POST['specification_value_type'] : [] ;
            $result = $result && $this->model->saveOptions($options, $specification_value_type, $article_id);

            /* Удаляем неиспользуемые в продукте изображения, обновляем ссылки на изображения, получаем новый текст описания продукта (HTML-разметку) */
            $new_text = $this->model->checkImagesByHTML($_POST, $article_id);

            $_POST['text'] = $new_text;

            /* Обновляем запись в БД для продукта с обновленным текстом (HTML-разметкой) */
            $result = $result && $this->model->save($_POST, $article_id);

            /* Обновляем в БД категории продукта */
            if ($_POST['categories']) {
                $categories_id = $_POST['categories'];
                $result = $result && $this->model->update_categ_of_article($categories_id, $article_id);
            }

            if (!$result) {
                Session::setFlash('Ошибка сохранения статьи');
            }

            Router::redirect('/admin/articles/edit/' . $article_id . '/');

        } else {

            $specifications_all = $this->model->getSpecificationsList();

            $options_of_specifications = [];
            foreach ($specifications_all as $specif) {
                $options = $this->model->getOptionsBySpecificationId($specif['specification_id']);

                $options_of_specifications[] = ['specification' => $specif,
                    'options' => $options,
                    'options_of_product' => [],
                    'options_only_this_product_specific' => [] ] ;
            }
            $this->data['specifications_all'] = $options_of_specifications;
        }
        
        if (isset($this->params[0])) {
            /* В случае перехода на данную страницу сайта получаем данные для представления этой страницы */
            $this->data['article'] = $this->model->getById($this->params[0]);
            $this->data['article_images'] = $this->model->getImgsByArticleId($this->params[0]);
            $specifications_all = $this->model->getSpecificationsList();

            $options_of_specifications = [];
            foreach ($specifications_all as $specif) {
                $options = $this->model->getOptionsBySpecificationId($specif['specification_id']);
                $options_of_product = $this->model->getOptionsByProductId( $this->params[0], $specif['specification_id']);
                
                $options_only_this_product_specific = [];
                foreach ($options_of_product as $opt) {
                    if ( $opt['id_of_option'] == 0 ) {
                        $options_only_this_product_specific[] = $opt;
                    }
                }
                $options_of_specifications[] = ['specification' => $specif,
                                                'options' => $options,
                                                'options_of_product' => $options_of_product,
                                                'options_only_this_product_specific' => $options_only_this_product_specific ] ;
            }
            $this->data['specifications_all'] = $options_of_specifications;
        }
    }

    /* Удаляем продукт и связанные с ним изображения */
    public function admin_delete()
    {
        if (isset($this->params[0])) {

            $article_images = $this->model->getImgsByArticleId($this->params[0]);

            foreach ($article_images as $image) {
                $this->model->del_image($image['full_name']);
            }

            $result = $this->model->delete($this->params[0]);

            if ($result) {
                Session::setFlash('Статья была удалена');
            } else {
                Session::setFlash('Ошибка.');
            }
        }
        Router::redirect('/admin/articles/');
    }


    public function admin_deleteimage()
    {
        if (isset($this->params[0])) {
            $result = $this->model->del_image($this->params[0]);
            if ($result) {
                Session::setFlash('Изображение было удалено.');
            } else {
                Session::setFlash('Ошибка');
            }
        }
        Router::redirect('/admin/articles/edit/' . $this->params[1]);
    }


    public function admin_categories_get_ajax()
    {
        $categories = $this->model->getCategories();
        $categories_line = structure_to_line($categories, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id']);

        if (isset($this->params[0])) {
            $article_categories = $this->model->getCategByArticleId($this->params[0]);
            $article_categories_id = array_column($article_categories, 'cat_id');
        } else {
            $article_categories_id = [];
        }

        $data['categories'] = $categories_line;
        $data['article_categories'] = $article_categories_id;   
        echo(json_encode($data));
        die;
    }

    public function admin_update_categ_ajax()
    { 
        $categories_all = $this->model->getCategories();
        $categories_all_line = structure_to_line($categories_all, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id']);
        $article_categories_id = isset($_POST['article_categories']) ? $_POST['article_categories'] : [];

        if ( isset($_POST['operation']) && $_POST['operation'] == 'add') {
            $article_categories_id[] = $this->params[0];
            $parents_categories_by_id = structure_to_line($categories_all, $options = ['begin_id' => $this->params[0], 'nested_level' => 0, 'field_id' => 'parent_id', 'field_id_parent' => 'id']);
            foreach ($parents_categories_by_id as $parent_categ) {
                $article_categories_id[] = $parent_categ['id'];
            }
        } elseif ( isset($_POST['operation']) && $_POST['operation'] == 'delete') {
            $childs_categories_by_id = structure_to_line($categories_all, $options = ['begin_id' => $this->params[0], 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id']);
            $childs_categories_by_id = array_column($childs_categories_by_id, 'id');
            $childs_categories_by_id[] = $this->params[0];

            $new_categories_of_article = [];
            foreach ($article_categories_id as $categ) {
                if (!in_array($categ, $childs_categories_by_id))
                    $new_categories_of_article[] = $categ;
            }
            $article_categories_id = $new_categories_of_article;
        }

        $data['categories'] = $categories_all_line;
        $data['article_categories'] = $article_categories_id;

        echo(json_encode($data));
        die;
    }


    /* Сохранение изображения (добавленного пользователем в описание продукта) как временного файла и возвращение ссылки на него в редактор TinyMCE */
    public function admin_tinymce_upload_image_to_article()
    {
        if ($_FILES['image_tinymce'] && isset($this->params[0])) {

            $temp_images = $this->model->saveTempImages($_FILES['image_tinymce'], $this->params[0]);

            $data = [];
            $data['location'] = '/webroot/img/articles/' . $temp_images[0];
            $data_json = json_encode($data);
            echo $data_json;
        }
        die;
    }


}
