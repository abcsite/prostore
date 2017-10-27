<?php

class User extends Model {

    public function getByLogin($login) {
        $login = $this->db->escape($login);
        $sql = "select * from users where login = '{$login}' limit 1";
        $result = $this->db->query($sql);
        if ( isset( $result[0]) ) {
            return $result[0];
        }
        return false;
    }

    public  function getList(){
        $sql = 'select * from users where 1';
        return $this->db->query($sql);
    }

    public  function  activate( $is_active ){

        foreach ($is_active as $id => $val) {
            $sql = " update users set is_active = '{$val}' where id = {$id}; ";
            if (!$this->db->query($sql)) return false;
        }
        return true;
    }

    public  function  register($data, $login = null){
        $login = $this->db->escape($login);
        
        if (!$login) {
            $sql = "insert into users
                  set login = '{$data['login']}',
                      email = '{$data['email']}',
                      role = 'user',
                      password = '{$data['password']}',
                      is_active = 1
            ";
        } else {
            $sql = "update users
                  set login = '{$data['login']}',
                      email = '{$data['email']}',
                      password = '{$data['password']}',
                      is_active = 1
                      where login = '{$login}'
            ";
        }
//        deb($sql);
        return $this->db->query($sql);
    }

}