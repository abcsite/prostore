<?php

class Specification extends Model{

    public  function getList(){
        $sql = 'select * from specifications  order by specification_name ';

        return $this->db->query($sql);
    }

    public  function getOptionsBySpecificationId($specif_id) {
        $specif_id = (int)$specif_id;
        $sql = "select * from options_of_specification  where specificat_id = '{$specif_id}'  ";

        return $this->db->query($sql);
    }

    public  function  save($data, $specification_id = null){
        if ( !isset($data['specification_name']) ) {
            return false;
        }
        if ( isset($data['specification_type']) ) {
            $specification_type = $data['specification_type'];
        } else {
            $specification_type = 'text';
        }

        $specification_id = (int)$specification_id;
        $specification_name = $this->db->escape($data['specification_name']);
        $specification_type = $this->db->escape($specification_type);

        if ( !$specification_id) {
            $sql = "
                insert into specifications
                  set specification_type = '{$specification_type}',
                     specification_name = '{$specification_name}'
            ";
        } else {  
            $sql = "
                update specifications
                  set specification_type = '{$specification_type}',
                     specification_name = '{$specification_name}'
                  where specification_id = {$specification_id} 
            ";
        }

        return $this->db->query($sql);
    }

    public  function  saveOptions($options, $specification_id, $specification_type){

        if ( isset($specification_type) && $specification_type != '') {
            $specification_type = $this->db->escape($specification_type);
        } else {
            $specification_type = 'text';
        }
        $specification_id = (int)$specification_id;

        $sql = "delete from options_of_specification where specificat_id = '{$specification_id}' ";
        $result = $this->db->query($sql);

        $arr = [];
        $field_type = 'option_value_' . $specification_type;

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $val = $this->db->escape($val);

                if ($specification_type == 'text') {
                    $val = "'" . $val . "'" ;
                } elseif ($specification_type == 'numeric') {
                    if (is_numeric($val)) {
                        $val = (int)$val;
                    } else {
                        $result = false;
                    }
                } elseif ($specification_type == 'timestamp') {
                    $val = (int)strtotime($val);
                }

                $arr[] = ' (' . $specification_id . ', ' . $val . ' ) ' ;
            }
            $str = implode(',' , $arr);

            $sql = " insert into options_of_specification (specificat_id, {$field_type})
                  values {$str}
             ";
            if ($result) {
                $result = $result && $this->db->query($sql);
            }
            
        }

        return $result;
    }

    public function delete($id_arr) {
        foreach ($id_arr as $key => $id) {
            $id_arr[$key] = (int) $id;
        }
        $id_str = implode(',', $id_arr);
        $sql = "delete from specifications where specification_id IN ({$id_str}) ";
        return $this->db->query($sql);

    }

}