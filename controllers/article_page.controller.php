<?php

class Article_pageController extends Controller
{
    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Article_view();
    }


    public function view()
    {
        if (isset($this->params[0])) {

            if (Session::get('role') != 'admin') {
                $this->model->visited_counter($this->params[0]);
            }
            
            $result = $this->model->getById($this->params[0]);

            if ($result) {
                if (Session::get('role') != 'admin' && $result['is_published'] == '0') {
                    Session::setFlash('Такой страницы не существует.');
                } else {
                    $this->data['article_page'] = $result;
                    $tags = explode(',', $result['tags']);
                    $this->data['article_page']['tags'] = $tags;

                    $this->data['article_images'] = $this->model->getImgsByArticleId($this->params[0]);


                    /* Получаем список характеристик и опций для продукта */

                    if ( isset($_GET['categ']) && $_GET['categ'][0] != '' && is_array($_GET['categ']) && count($_GET['categ']) === 1) {
                        $specifications_of_category = $this->model->getSpecificationsByCategoryId($_GET['categ'][0]);
                        $specifications_id = array_column($specifications_of_category, 'id_of_specification');
                    } else {
                        $specification_all = $this->model->getSpecificationsAll();
                        $specifications_id = array_column($specification_all, 'specification_id');
                    }

                    $article_specifications = $this->model->getOptionsByProductId($this->params[0]);
                    $article_specifications_id = array_column($article_specifications , 'id_of_specification');
//  deb($specifications_id);
                    foreach ($specifications_id as $id) {
                        $specification = $this->model->getSpecificationsAll($id);

                        if ( in_array($id, $article_specifications_id) ) {
                            $product_options = $this->model->getOptionsByProductId($this->params[0], $id);
                            $option_list = [];
                            foreach ($product_options as $option) {
                                if ($option['id_of_option'] == 0) {
                                    $option_list[] = $option['custom_value_' . $specification['specification_type']];
                                } else {
                                    $option = $this->model->getOptionById($option['id_of_option']);
                                    $option_list[] = $option['option_value_' . $specification['specification_type']];
                                }
                            }
                            $specification['options'] = $option_list;

                        } else {
                            $specification['options'] = [' не задано '];
                        }

                        $this->data['article_specifications'][] = $specification ;
                    }


                    if (Session::get('login')) {
                        $login = Session::get('login');
                        $user = $this->model->getUserByLogin($login);
                        $this->data['user'] = $user;
                    } else {
                        $this->data['user'] = null;
                    }
                    
                }

            }

            //* Если пользователем выбрана категория , передаем ее и родительские категории на страницу (для создания "хлебных крошек") */
            if ( isset($_GET['categ']) && is_array($_GET['categ']) && count($_GET['categ']) === 1) {

                $categories_all = $this->model->getCategList();
                $parents_by_category_id = structure_to_line($categories_all, $options = ['begin_id' => (int)$_GET['categ'][0], 'nested_level' => 0, 'field_id' => 'parent_id', 'field_id_parent' => 'id']);

                $hornav_categories = [];
                foreach ($parents_by_category_id as $parent_categ) {
                    $hornav_categories[] = $parent_categ;
                }
                $this->data['selected_categ'] = array_reverse($hornav_categories);
                $this->data['categories_url_base'] = '/articles/filter/';
            }
            
            /* Задаем параметры и получаем контент для левой части страницы статьи  */
            $data_module_side_left = [];
            $data_module_side_left['categories_url_base'] = '/articles/filter/';
            $module_side_left = new Module_side_leftController( $data_module_side_left );
            $this->data['module_side_left'] = $module_side_left->get_view();

            /* Задаем параметры для модуля для получения списка ТОП-новостей за все время в правой части страницы статьи  */
            $data_module_articles_top_all = [];
            $data_module_articles_top_all['filter']['order_by'][] = '-a.comments_count';
            $data_module_articles_top_all['filter']['order_by'][] = '-a.date_published';
            $data_module_articles_top_all['filter']['limit_count'] = 5;
            $data_module_articles_top_all['filter']['limit_offset'] = 0;

            $module_articles_top_all = new Module_articles_listController( $data_module_articles_top_all );
            $data_top_all = $module_articles_top_all->get_articles_filter();

            /* Получаем контент для правой части страницы статьи  */
            $view_data_side_right = [];
            $view_data_side_right['top_all'] = $data_top_all;

            $path_side_right = VIEWS_PATH . DS .'modules' . DS . 'side_right.html';
            $view_object_side_right = new View( $view_data_side_right, $path_side_right);
            $this->data['module_side_right'] = $view_object_side_right->render();

        } else {
            Session::setFlash('Такая страница не существует.');
        }
    }


    public function admin_view()
    {
        $this->view();
        
        return VIEWS_PATH . DS . 'article_page' . DS . 'view.html' ;
    }



    public function comments_get_ajax()
    {
        if (isset($_POST['comments_filter'])) {
            $comments_filter = $_POST['comments_filter'];
        } else {
            $comments_filter = 0;
        }
        if (Session::get('role') === 'admin') {
            $comments = $this->model->getCommentsByArticleId($this->params[0], $comments_filter, false);
        } else {
            $comments = $this->model->getCommentsByArticleId($this->params[0], $comments_filter, true);
        }

        $comments_line = structure_to_line($comments, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id_comment', 'field_id_parent' => 'id_parent_comment' ]);
        if ($comments_line) {
            foreach ($comments_line as $key => $row) {
                if (!$row['date']) {
                    $comments_line[$key]['date'] = '';
                    $comments_line[$key]['time_stamp'] = 0;
                } else {
                    $comments_line[$key]['date'] = my_format_date( $row['date'] );
                    $comments_line[$key]['time_stamp'] = strtotime($row['date']);
                }
            }
        }

        $comments_all = $this->model->getCommentsByArticleId($this->params[0], '', true);
        $comments_count = (isset($comments_all)) ? count($comments_all) : 0 ;
        $this->model->comments_counter($this->params[0], $comments_count );

        echo(json_encode($comments_line));
        die;
    }

    
    public function comment_add_ajax()
    {
        if (Session::get('role')) {
            if (isset($_POST['text']) && isset($_POST['id_comment']) && isset($_POST['id_user']) && isset($_POST['id_article']) ) {
                $result = $this->model->comment_save($_POST);

                $this->comments_get_ajax();
            }
            die;
        }
    }

    public function comment_edit_ajax()
    {
        if (Session::get('role')) {
            if ( isset($_POST['text']) && isset($_POST['id_comment'])  ) {
                $result = $this->model->comment_save($_POST, $_POST['id_comment'] );

                $this->comments_get_ajax();
            }
            die;
        }
    }

    public function comment_like_ajax()
    {
        if (Session::get('role')) {
            if (isset($this->params[0]) && isset($this->params[1])) {
                $like = $this->model->getLike($this->params[0], $this->params[1]);

                if (!$like) {
                    $result = $this->model->addLike($this->params[0], $this->params[1]);

                    if ($result) {
                        echo 1;
                        die;
                    }
                }
            }
            die;
        }
    }

    public function comment_dislike_ajax()
    {
        if (Session::get('role')) {
            if (isset($this->params[0]) && isset($this->params[1])) {
                $dislike = $this->model->getDislike($this->params[0], $this->params[1]);

                if (!$dislike) {
                    $result = $this->model->addDislike($this->params[0], $this->params[1]);

                    if ($result) {
                        echo 1;
                        die;
                    }
                }
            }
            die;
        }
    }

    public function admin_comment_publish_ajax()
    {
        if (isset($this->params[1]) ) {

            $comments = $this->model->getCommentsByArticleId($this->params[0], false);
            $comments_line = structure_to_line($comments, $options = ['begin_id' => $this->params[1], 'nested_level' => 0, 'field_id' => 'id_comment', 'field_id_parent' => 'id_parent_comment' ]);
            $comments_id = [];
            $comments_id[] = $this->params[1];
            if (count($comments_line)) {
                foreach ($comments_line as $key => $row) {
                    $comments_id[] = $row['id_comment'];
                }
            }

            $this->model->setPublishComment($comments_id, 1);

            $comments_all = $this->model->getCommentsByArticleId($this->params[0], '', true);
            $comments_count = (isset($comments_all)) ? count($comments_all) : 0 ;
            $this->model->comments_counter($this->params[0], $comments_count );

            $this->comments_get_ajax();
        }
        die;
    }

    public function admin_comment_hide_ajax()
    {
        if (isset($this->params[1]) ) {
            $comments = $this->model->getCommentsByArticleId($this->params[0], false);
            $comments_line = structure_to_line($comments, $options = ['begin_id' => $this->params[1], 'nested_level' => 0, 'field_id' => 'id_comment', 'field_id_parent' => 'id_parent_comment' ]);
            $comments_id = [];
            $comments_id[] = $this->params[1];
            if (count($comments_line)) {
                foreach ($comments_line as $key => $row) {
                    $comments_id[] = $row['id_comment'];
                }
            }

            $this->model->setPublishComment($comments_id, 0);

            $comments_all = $this->model->getCommentsByArticleId($this->params[0], '', true);
            $comments_count = (isset($comments_all)) ? count($comments_all) : 0 ;
            $this->model->comments_counter($this->params[0], $comments_count );

            $this->comments_get_ajax();
        }
        die;
    }

    public function comment_delete_ajax()
    {
        if (isset($this->params[1]) ) {

            $comments = $this->model->getCommentsByArticleId($this->params[0], false);
            $comments_line = structure_to_line($comments, $options = ['begin_id' => $this->params[1], 'nested_level' => 0, 'field_id' => 'id_comment', 'field_id_parent' => 'id_parent_comment' ]);
            $comments_id = [];
            $comments_id[] = $this->params[1];
            if (count($comments_line)) {
                foreach ($comments_line as $key => $row) {
                    $comments_id[] = $row['id_comment'];
                }
            }

            $this->model->deleteComments($comments_id);

            $comments_all = $this->model->getCommentsByArticleId($this->params[0], '', true);
            $comments_count = (isset($comments_all)) ? count($comments_all) : 0 ;
            $this->model->comments_counter($this->params[0], $comments_count );

            $this->comments_get_ajax();
        }
        die;
    }

}



