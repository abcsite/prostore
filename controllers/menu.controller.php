<?php

class MenuController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Menu_model();
    }

    public function admin_index()
    {
        /* Список категорий на страницу загружается через AJAX методом admin_categories_get_ajax(). А этот метод необходим для перенаправления роутером на соответствующее представление */
    }

    public function getMenuData()
    {
        $categories = $this->model->getList();
        $categories_line = structure_to_line($categories, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'id_parent']);

        return $categories_line;
    }

    public function admin_add()
    {
        if ($_POST && $_POST['category_name'] != '') {
            $result = $this->model->save($_POST);
            if ($result) {
                Session::setFlash('Items of menu was saved.');
            } else {
                Session::setFlash('Error.');
            }
        }
        Router::redirect('/admin/menu/');
    }


    public function admin_categories_get_ajax()
    {
        $categories = $this->model->getList();
        $categories_line = structure_to_line($categories, $options = ['begin_id' => 0, 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'id_parent']);

        $data = [];
        $data['categories_all'] = $categories_line;

        echo(json_encode($data));
        die;
    }


    public function admin_category_add_ajax()
    {
        if (isset($_POST['category_name']) && isset($_POST['parent_id'])) {

            $result = $this->model->save($_POST, $_POST['id']);

            if (isset($_POST['id']) && $_POST['id'] != '' && $_POST['id'] != '0') {
                $category_id = $_POST['id'];
            } else {
                $categories_all = $this->model->getList();
                $categories_id = array_column($categories_all, 'id');
                rsort($categories_id);
                $category_id = $categories_id[0];
            }

            $this->admin_categories_get_ajax();
        }
        die;
    }

    public function admin_category_delete_ajax()
    {
        if (isset($this->params[0])) {
            $categories = $this->model->getList();
            $childs_categories_to_delete = structure_to_line($categories, $options = ['begin_id' => $this->params[0], 'nested_level' => 0, 'field_id' => 'id', 'field_id_parent' => 'id_parent']);

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