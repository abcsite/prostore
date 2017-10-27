<?php

class Module_articles_listController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new modul_articles_list();
    }

    public function get_articles_filter()
    {
        $filter = [];

        if ( isset($this->data['filter']['filter_url']) && $this->data['filter']['filter_url'] != '') {
            $filter = $this->data['filter']['filter_url'];
        } else {
            $filter = $this->data['filter'];

            if (isset($this->data['filter']['yyyy']) && isset($this->data['filter']['mm']) && isset($this->data['filter']['dd'])  ) {
                $yyyy = $this->data['filter']['yyyy'] ;
                $mm = $this->data['filter']['mm'] ;
                $dd = $this->data['filter']['dd'] ;

                $date_min = $yyyy . '-' . $mm . '-' . $dd . ' ' . '00:00:00';
                $filter['date_min'] = date('Y-m-d h:i:s' , strtotime($date_min) ) ;
            }

            if (  isset($this->data['filter']['yyyy_']) && isset($this->data['filter']['mm_']) && isset($this->data['filter']['dd_']) ) {
                $yyyy_ =  $this->data['filter']['yyyy_'] ;
                $mm_ =  $this->data['filter']['mm_'] ;
                $dd_ =  $this->data['filter']['dd_'] ;

                $date_max = $yyyy_ . '-' . $mm_ . '-' . $dd_ . ' ' . '00:00:00' ;
                $filter['date_max'] = date('Y-m-d h:i:s' , strtotime($date_max) )  ;
            }


            $filter_url_arr = $this->data['filter'];
            unset($filter_url_arr['to_page']);
            unset($filter_url_arr['order_by']) ;
            $filter_url = http_build_query($filter_url_arr);

            $this->data['filter']['filter_url'] = $filter_url;
        }

        if ( isset($this->data['filter']['to_page']) && $this->data['filter']['to_page'] != '' ) {
            $currentPage = $this->data['filter']['to_page'];
        } else {
            $currentPage = 1;
        }

        if ( isset($this->data['filter']['itemsPerPage']) && $this->data['filter']['itemsPerPage'] != '' ) {
            $itemsPerPage = $this->data['filter']['itemsPerPage'];
        } else {
            $itemsPerPage = Config::get('pagination_count_per_page');
        }

        if ( isset($this->data['filter']['limit_count']) && isset($this->data['filter']['limit_offset']) )  {
            $filter['limit_count'] = $this->data['filter']['limit_count'];
            $filter['limit_offset'] = $this->data['filter']['limit_offset'];

            $article_list = $this->model->getArticlesFilter($filter, true);

        } else {

            $itemsPerPage = Config::get('pagination_count_per_page');

            $articles = $this->model->getArticlesFilter($filter, true);

            function filterProductsByOptions($articles, $options, $model) {
                $articles_filtered = [];
                foreach ($articles as $art) {
                    $art_id = $art['art_id'];
                    foreach ($options as $id => $opt) {
                        $products = $model->getProductsByOptionId($id);
                        $products_id = array_column($products, 'id_of_product');
                        if ( in_array($art_id, $products_id ) ) {
                            $articles_filtered[] = $art;
                        }
                     }
                }
                return $articles_filtered;
            }


            if (isset($this->data['filter']['options'])) {
                $articles = filterProductsByOptions($articles, $this->data['filter']['options'], $this->model);
            }

            $articles_count = count($articles);

            $pagination = new Pagination($articles_count, $itemsPerPage, $currentPage);
            $pagin = $pagination->result;

            $filter['limit_count'] = $pagin['itemsEnd'] - $pagin['itemsStart'] + 1;
            $filter['limit_offset'] = $pagin['itemsStart'] - 1;

            $this->data['pagination'] = $pagin;

            $article_list = [];

            for ($i = $pagin['itemsStart']; $i < $pagin['itemsEnd'] ; $i++) {
                $article_list[] = $articles[$i];
            }

        }

        foreach ($article_list as $key => $article) {
            $images = $this->model->getImgsByArticleId( $article['art_id'] );
            $article_list[$key]['images'] = $images;
        }

        if ($article_list) {
            $this->data['articles'] = $article_list;

            $filter_categ_list = ( isset($this->data['filter']['categ']) ) ? $this->data['filter']['categ'] : null;
            $categories = $this->model->getCategoryByIds($filter_categ_list);
            $this->data['filter']['categ'] = $categories;
        }
       
        return $this->data;
    }


    public function get_view($data = null) {

        if (isset($data) ) {
            $view_data = $data;
        } else {
            $view_data = $this->get_articles_filter();
        }
        
        $view_path = VIEWS_PATH . DS . 'modules' . DS . 'articlesList.html';
        $view_object = new View( $view_data, $view_path);
        $content = $view_object->render();

        return $content;

    }


}
