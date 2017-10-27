<?php

class SpecificationsController extends Controller
{

    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Specification();
    }

    public function admin_index()
    {
        /* Список характеристик на страницу загружается через AJAX методом admin_specifications_get_ajax(). А этот метод необходим для перенаправления роутером на соответствующее представление */
    }

    public function admin_add()
    {
        if ($_POST && $_POST['specification_name'] != '') {
            $result = $this->model->save($_POST); 
            if ($result) {
                Session::setFlash('Specification was saved.');
            } else {
                Session::setFlash('Error.');
            }
        }
        Router::redirect('/admin/specifications/');
    }


    public function admin_specifications_get_ajax()
    {  
        $data = [];
        $specifications = $this->model->getList();
        
        if (isset($specifications) && is_array($specifications)) {
            foreach($specifications as $specific) {
                $options = $this->model->getOptionsBySpecificationId($specific['specification_id']);
                $data[] = ['specification' => $specific, 'options' => $options];
            }
        }
        
        echo(json_encode($data));
        die;
    }


    public function admin_specification_add_ajax()
    {
        if (isset($_POST['specification_name']) && isset($_POST['specification_id']) && isset($_POST['options']) ) {
            $result = $this->model->save($_POST, $_POST['specification_id']);
            $result = $this->model->saveOptions($_POST['options'], $_POST['specification_id'], $_POST['specification_type']);

            $this->admin_specifications_get_ajax();deb('aaa');
        }
        die;
    }

    public function admin_specification_delete_ajax()
    {
        if (isset($this->params[0])) {
            $result = $this->model->delete([$this->params[0]]);

            $this->admin_specifications_get_ajax();
        }
        die;
    }


}