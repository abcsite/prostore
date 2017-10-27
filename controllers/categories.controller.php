<?php

class CategoriesController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Category();
    }

    public function admin_index()
    {
        /* Список категорий на страницу загружается через AJAX методом admin_categories_get_ajax(). А этот метод необходим для перенаправления роутером на соответствующее представление */
    }

    public function admin_add()
    {
        if ($_POST && $_POST['category_name'] != '') {
            $result = $this->model->save($_POST);
            if ($result) {
                Session::setFlash('Category was saved.');
            } else {
                Session::setFlash('Error.');
            }
        }
        Router::redirect('/admin/categories/');
    }


    public function admin_categories_get_ajax()
    {
        $categories = $this->model->getList();
        $categories_line = structure_to_line($categories, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id']);

        $data = [];
        $data['categories_all'] = $categories_line;
        $data['specifications_of_category'] = [];

        foreach ($categories_line as $categ) {
            $specifications_of_category = $this->model->getSpecificationsByCategoryId($categ['id']);
            $data['specifications_of_category'][$categ['id']] = array_column($specifications_of_category, 'id_of_specification');
        }

        $data['specifications_all'] = $this->model->getSpecificationsAll();

        echo(json_encode($data));
        die;
    }


    public function admin_category_add_ajax()
    {
        if (isset($_POST['category_name']) && isset($_POST['parent_id'])) {

            $result = $this->model->save($_POST, $_POST['id']);

            if (isset($_POST['specifications_of_category'])) {
                $specifications_of_category = $_POST['specifications_of_category'];
            } else {
                $specifications_of_category = [];
            }

            if (isset($_POST['id']) && $_POST['id'] != '' && $_POST['id'] != '0') {
                $category_id = $_POST['id'];
            } else {
                $categories_all = $this->model->getList();
                $categories_id = array_column($categories_all, 'id');
                rsort($categories_id);
                $category_id = $categories_id[0];
            }

            $result = $result && $this->model->saveSpecificationsOfCategory($specifications_of_category, $category_id);

            $this->admin_categories_get_ajax();
        }
        die;
    }

    public function admin_category_delete_ajax()
    {
        if (isset($this->params[0])) {
            $categories = $this->model->getList();
            $childs_categories_to_delete = structure_to_line($categories, $options = ['begin_id' => $this->params[0], 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'parent_id']);

            $id_arr = [(int)$this->params[0]];
            foreach ($childs_categories_to_delete as $child) {
                $id_arr[] = (int)$child['id'];
            }

            $result = $this->model->delete($id_arr);

            $this->admin_categories_get_ajax();
        }
        die;
    }


}