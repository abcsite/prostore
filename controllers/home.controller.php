<?php

class HomeController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Home_page();
    }


    public function index()
    {
        
        /* Получаем список главных категорий (верхнего уровня) и список продуктов для этих категорий */
        $categories_all = $this->model->getCategList();
        $categories_all_line = structure_to_line($categories_all, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id']);
        $this->data['categories_url_base'] = '/articles/filter/';
        
        $main_categories = [];
        foreach ($categories_all_line as $category) {
            if ($category['nested_level'] == 0) {
                $main_categories[] = $category;
            }
        }

        $data_module_articles = [];
        $data_module_articles['articles_url_base'] = '/articles/filter/';
        $data_module_articles['filter']['limit_count'] = 5;
        $data_module_articles['filter']['limit_offset'] = 0;

        $images = [];
        
        foreach ($main_categories as $category) {

            unset($data_module_articles['filter']['categ']);
            $data_module_articles['filter']['categ'][] = $category['id'];

            unset($data_module_articles['filter']['action_type']);
            $data_module_articles['filter']['action_type'] = ['Скидка', 'Распродажа', 'Акция'];

            $module_articles_action_list = new Module_articles_listController($data_module_articles);
            $articles_action_list_data = $module_articles_action_list->get_articles_filter();

            /* Для списка акционных продуктов получаем список имен каждого первого изображения в продукте (для вывода в слайдере) */
            $data = $articles_action_list_data;
            $articles_count = (isset($data['articles'])) ? count($data['articles']) : 0 ;
            for ($i = 0; $i < $articles_count && $i < 2 ; $i++ ) {
                $article_images = $module_articles_action_list->getModel()->getImgsByArticleId( $data['articles'][$i]['art_id'] );
                if (isset($article_images[0])) {
                    $arr = ['img' => $article_images[0]['full_name'], 'article' => $data['articles'][$i] ];
                    $images[] = $arr;
                }
            }

            unset($data_module_articles['filter']['action_type']);

            $module_articles_list = new Module_articles_listController($data_module_articles);
            $articles_list_data = $module_articles_list->get_articles_filter();

            $new_articles_list = [];
            $products_actions_id = array_column($articles_action_list_data['articles'], 'art_id');
            foreach ($articles_list_data['articles'] as $article) {
                if ( !in_array($article['art_id'], $products_actions_id) ) {
                    $new_articles_list[] = $article;
                }
            }
            $articles_list_data['articles'] = $new_articles_list;

            /* Для списка не акционных продуктов получаем список имен каждого первого изображения в продукте (для вывода в списке продуктов) */
            $data = $articles_list_data;
            $articles_count = (isset($data['articles'])) ? count($data['articles']) : 0 ;
            for ($i = 0; $i < $articles_count && $i < 2 ; $i++ ) {
                $article_images = $module_articles_list->getModel()->getImgsByArticleId( $data['articles'][$i]['art_id'] );
                if (isset($article_images)) {
//                    $arr = ['img' => $article_images[0]['full_name'], 'article' => $data['articles'][$i] ];
                    $articles_list_data['articles'][$i]['images'] = $article_images;
                }
            }



            $this->data['categories'][] = ['category' => $category, 'products' => $articles_list_data, 'products_actions' => $articles_action_list_data ];
        }

        $this->data['images'] = $images;
        

        /* Задаем параметры и получаем контент для левой части главной страницы (список категорий) */
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

    public function admin_index()
    {
        $this->index();
        return VIEWS_PATH . '/home/index.html';
    }


}
