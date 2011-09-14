<?php   

# Inheritence Model > Core > Query > Table > DB 

$post = KFactory::get('admin::com.tena.model.posts');

$posts = $post->limit(20)->all(); 

class ComTenaPostsModel extends ComTenaDefaultModel
{   
  public function _initialize(KConfig $config)
  {    
    parent::_initialize($config);
    $this->key('title', 'string', array('default' => 'Post Title'));
    $this->key('created_at', 'datetime');      
  }
}