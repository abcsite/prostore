<?php

class Article_view extends Model
{

    public function getById($id)
    {
        $id = (int)$id;
        $sql = "select * from articles where id = '{$id}' limit 1";
        $result = $this->db->query($sql);
        return isset($result[0]) ? $result[0] : null;
    }

    public  function getSpecificationsAll( $specification_id = null){

        if (isset($specification_id)) {
            $specification_id = (int)$specification_id;
            $str = " where specification_id = {$specification_id} ";
            $sql = "select * from specifications  {$str}  order by specification_name ";

            $result =  $this->db->query($sql);
            return  $result[0];
            
        } else {
            $str = '';
            $sql = "select * from specifications  {$str}  order by specification_name ";

            return  $this->db->query($sql);
        }
    }

    public  function getOptionsByProductId($product_id, $specification_id = null){
        $product_id = (int)$product_id;

        if (isset($specification_id)) {
            $specification_id = (int)$specification_id;
            $str =  " AND id_of_specification = '{$specification_id}' " ;
        } else {
            $str = ' ' ;
        }

        $sql = "select * from options_of_product where id_of_product = '{$product_id}' {$str}  ";

        return $this->db->query($sql);
    }

    public  function getSpecificationsByCategoryId($categ_id) {
        $categ_id = (int)$categ_id;
        $sql = "select * from  specifications_of_category  where id_of_category = '{$categ_id}'  ";

        return $this->db->query($sql);
    }
    
    public  function getOptionById($option_id) {
        $option_id = (int)$option_id;
        $sql = "select * from   options_of_specification  where option_id = '{$option_id}'  ";

        $result = $this->db->query($sql);
        
        return (isset($result[0]) ? $result[0] : null);
    }

    public  function getCategList($only_published = false){
        $sql = 'select * from categories where 1';
        if ( $only_published) {
            $sql .= ' and displayed = 1';
        }
        $sql .= ' order by category_name ';
        return $this->db->query($sql);
    }

    public function visited_counter($id)
    {        
        $id = (int)$id;
        $sql = "
                update articles
                  set visited = visited + 1
                  where id = {$id}
            ";
        $result = $this->db->query($sql);
        return isset($result[0]) ? $result[0] : null;
    }

    public function comments_counter($article_id, $comments_count = 0)
    {
        $article_id = (int)$article_id;
        $comments_count = (int)$comments_count;
        $sql = "
                update articles
                  set comments_count = {$comments_count}
                  where id = {$article_id}
            ";
        return $this->db->query($sql);
    }

    public function getCommentsByArticleId($id_article = 1, $comments_filter, $only_published_comments = false)
    {
        $id_article = (int)$id_article;
        $only_published = $only_published_comments ? ' AND c.is_published = 1 ' : '';
        $comments_filter = $this->db->escape($comments_filter);
        if ($comments_filter == 'recommend') {
            $recommend = " WHERE  c.recommend =  '1' ";
        } elseif ($comments_filter == 'not_recommend') {
            $recommend = " WHERE  c.recommend =  '-1' ";
        } else {
            $recommend = ' ';
        }
        
        $sql = "select  c.* , u.* , count(like_user) as like_count, count(dislike_user) as dislike_count 
                      FROM comments c JOIN users u ON id = id_user AND id_article = '{$id_article}'  {$only_published}
                      LEFT JOIN likes ON id_comment = like_comment 
                      LEFT JOIN dislikes ON id_comment = dislike_comment 
                      {$recommend}
                      GROUP BY c.id_comment ORDER BY like_count DESC ";

        $result = $this->db->query($sql);
        return $result;
    }

    public function getCommentById($id_comment)
    {
        $id_comment = (int)$id_comment;

        $sql = "select * from comments where id_comment = '{$id_comment}'  ";
        $result = $this->db->query($sql);
        return isset($result[0]) ? $result[0] : null;
    }

    public function getLike($id_comment, $id_user)
    {
        $id_comment = (int)$id_comment;
        $id_user = (int)$id_user;

        $sql = "select * from likes where like_comment = '{$id_comment}' and  like_user = '{$id_user}' ";
        $result = $this->db->query($sql);
        return isset($result[0]) ? $result[0] : null;
    }

    public function getDislike($id_comment, $id_user)
    {
        $id_comment = (int)$id_comment;
        $id_user = (int)$id_user;

        $sql = "select * from dislikes where dislike_comment = '{$id_comment}' and  dislike_user = '{$id_user}' ";
        $result = $this->db->query($sql);
        return isset($result[0]) ? $result[0] : null;
    }

    public function addLike($id_comment, $id_user)
    {
        $id_comment = (int)$id_comment;
        $id_user = (int)$id_user;

        $sql = " insert into likes
                  set like_comment = '{$id_comment}',
                      like_user = '{$id_user}'
               ";
        $result = $this->db->query($sql);
        return $result;
    }

    public function addDislike($id_comment, $id_user)
    {
        $id_comment = (int)$id_comment;
        $id_user = (int)$id_user;

        $sql = " insert into dislikes
                  set dislike_comment = '{$id_comment}',
                      dislike_user = '{$id_user}'
               ";
        $result = $this->db->query($sql);
        return $result;
    }


    public function getImgsByArticleId($id)
    {
        $id = (int)$id;
        $sql = "select * from images_of_article where id_article = '{$id}' order by id ";
        $result = $this->db->query($sql);
        return $result;
    }

    public function getUserByLogin($login)
    {
        $login = $this->db->escape($login);
        $sql = "select * from users where login = '{$login}' limit 1";
        $result = $this->db->query($sql);
        if (isset($result[0])) {
            return $result[0];
        }
        return false;
    }

    public function comment_save($data, $comment_id = null)
    {
        if (!isset($data['text']) || !isset($data['id_comment']) || !isset($data['id_user']) || !isset($data['id_article'])) {
            return false;
        }

        $comment_id = (int)$comment_id;
        $parent_id = (int)$data['id_comment'];
        $user_id = (int)$data['id_user'];
        $article_id = (int)$data['id_article'];
        $text = $this->db->escape($data['text']);
        if (isset($data['recommend'])) {
            $recommend = (int)$data['recommend'];
        } else {
            $recommend = 0;
        }
        $is_published = ($recommend === -1) ? 0 : 1 ;
        
        if (!$comment_id) {  
            $date = date("Y-m-d H:i:s");

            $sql = "
                insert into comments
                  set id_parent_comment = '{$parent_id}',
                      id_user = '{$user_id}',
                      id_article = '{$article_id}',
                      text = '{$text}',
                      date = '{$date}',
                      is_published  = '{$is_published }',
                      like_ok = '',
                      recommend = '{$recommend}'
            ";

        } else {

            $sql = "
                update comments
                  set text = '{$text}',
                      is_published  = '{$is_published }',
                      recommend = '{$recommend}'
                  where id_comment = '{$comment_id}'
            ";
        }

        return $this->db->query($sql);
    }


    public function setPublishComment($comments_id, $is_published = 1)
    {
        $is_published = (int)$is_published;

        if (count($comments_id)) {
            foreach ($comments_id as $key => $id) {
                $comments_id[$key] = (int)$id;
            }
            $comments_id_str = implode(',' , $comments_id );

            $sql = "
                update comments
                  set is_published = '{$is_published}'
                  where id_comment IN ({$comments_id_str})
            ";
            return $this->db->query($sql);
        } else {
            return true;
        }
    }

    public function deleteComments($comments_id)
    {
        if (count($comments_id)) {
            foreach ($comments_id as $key => $id) {
                $comments_id[$key] = (int)$id;
            }
            $comments_id_str = implode(',' , $comments_id );
            
            $sql = "
                delete from  comments
                  where id_comment IN ({$comments_id_str})
            ";
            return $this->db->query($sql);
        } else {
            return true;
        }
    }


}


