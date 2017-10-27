<?php

class Menu_model extends Model{

    public  function getList($only_published = false){
        $sql = 'select * from menu where 1';
        if ( $only_published) {
            $sql .= ' and displayed = 1';
        }
        $sql .= ' order by menu_name ';
        return $this->db->query($sql);
    }

    public  function getSpecificationsAll(){
        $sql = 'select * from specifications  order by specification_name ';

        return $this->db->query($sql);
    }

    public  function getSpecificationsByCategoryId($categ_id) {
        $categ_id = (int)$categ_id;
        $sql = "select * from  specifications_of_category  where id_of_category = '{$categ_id}'  ";

        return $this->db->query($sql);
    }

    public  function  save($data, $id = null){
        if ( !isset($data['category_name']) ) {
            return false;
        }

        $id = (int)$id;
        $parent_id = isset($data['parent_id']) ? (int)$data['parent_id'] : '0';
        $category_name = $this->db->escape($data['category_name']);
        $category_url = $this->db->escape($data['category_url']);

        if ( !$id) {
            $sql = "
                insert into menu
                  set id_parent = '{$parent_id}',
                     menu_name = '{$category_name}',
                     menu_url = '{$category_url}'
            ";
        } else {
            $sql = "
                update menu
                  set menu_name = '{$category_name}',
                  menu_url = '{$category_url}'
                  where id = {$id} 
            ";
        }

        return $this->db->query($sql);
    }

    public  function  saveSpecificationsOfCategory($specifications, $category_id){

        $category_id = (int)$category_id;

        $sql = "delete from specifications_of_category where id_of_category = '{$category_id}' ";
        $result = $this->db->query($sql);

        $arr = [];

        if (isset($specifications) && is_array($specifications) && count($specifications)) {
            foreach ($specifications as $specification_id) {
                $specification_id = (int)$specification_id;
                $arr[] = ' (' . $specification_id . ', ' . $category_id . ' ) ' ;
            }
            $str = implode(',' , $arr);

            $sql = " insert into specifications_of_category (id_of_specification, id_of_category)
                         values {$str}
             ";

            $result = $result && $this->db->query($sql);
        }

        return $result;
    }

    public function delete($id_arr) {
        foreach ($id_arr as $key => $id) {
            $id_arr[$key] = (int) $id;
        }
        $id_str = implode(',', $id_arr);
        $sql = "delete from menu where id IN ({$id_str}) ";
        return $this->db->query($sql);

    }

}