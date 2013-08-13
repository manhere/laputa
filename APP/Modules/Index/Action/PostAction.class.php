<?php
// +----------------------------------------------------------------------
// | AIMOZHEN [ SHARE VIDES SHARE LIFES ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://aimozhen.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Gavin Foo <fuxiaopang@msn.com>
// +----------------------------------------------------------------------


/*
 * AMZ 编辑作品主控制器
 * 主要有作品编辑等方法
 */
class PostAction extends CommonAction {



    // 添加视频
    public function addvideo() {
       if (!CommonAction::$user) $this->redirect('/');
       if (!IS_POST) _404('页面不存在...');
       $url = I('url');
       $exists_video = M('video')->field('id,url')->where(array('url' => $url))->find();

       //验证是否存在
       if ($exists_video[id]){

            $this->redirect("/video/".$exists_video[id]."/");

       } else {

            //获取UID
            $userid = CommonAction::$user[id];
            //引入验证与提取类
            import('Class.VideoUrlParser', APP_PATH);
            import('Class.Video', APP_PATH);
            //再次验证地址合法性
            $Video = new Video($url);
            if ($Video->type() == -1) $this->redirect('/');
            //获取截图标题
            $info = VideoUrlParser::parse($url);
            //准备数据
            $data = array(
                'createdTime' => time(),
                'userid' => $userid,
                'pre_tag' => 1,
                'url' => $url,
                'imageUrl' => $info['img'],
                'title' => $info['title']
            );
            //存入数据
            $vid = M('video')->add($data);
            M('user')->where(array('id' => $userid))->setInc('post');
            M('tag')->where(array('id' => 1))->setInc('count');

            $this->redirect("/edit/".$vid."/");

       }

    }

    //编辑视频页面生成
    public function editvideo() {

       if (!CommonAction::$user) $this->redirect('/');
       $visitor = CommonAction::$user;

       $vid = I('id');
       $exists_video = M('video')->find($vid);

       //验证是否存在视频
       if ($exists_video[id]){
           $video = $exists_video;
           //验证有用户权限
           if ( ($visitor[id] == $video[userid])||($visitor[group] == 1) ){
               $user = M('user')->field('username,email,weiboId')->find($video['userid']);
               $user[avatar] = getavatar($user);
               $this->user = $user;
               $this->video = $video;
               $this->display();
           } else {
                $this->error('您没有权限','/');
           }

       } else {
           $this->error('该视频已被删除了','/');
       }

    }


    //编辑视频页面生成
    public function editvideopost() {

       if (!CommonAction::$user) $this->redirect('/');
       if (!IS_POST) _404('页面不存在...');
       $visitor = CommonAction::$user;

       $vid = I('id');
       $exists_video = M('video')->find($vid);

       //验证是否存在视频
       if ($exists_video[id]){

           $video = $exists_video;
           //验证有用户权限
           if ( ($visitor[id] == $video[userid])||($visitor[group] == 1) ){

                if ($video[pre_tag]) {
                    $old_tag = M('tag')->find($video[pre_tag]);
                    if ($old_tag[count] > 0)  M('tag')->where(array('id' => $video[pre_tag]))->setDec('count');
                }
                M('tag')->where(array('id' => I('pre_tag') ))->setInc('count');

               $data = array (
               'title' => I('title'),
               'pre_tag' => I('pre_tag'),
               'tags' => I('tags'),
               'description' => I('description'),
               );

                if (I('collection')) {
                    if ($video[collection]) {
                        $old_coll = M('collection')->find($video[collection]);
                        if ($old_coll[count] > 0)  M('collection')->where(array('id' =>
                        $video[collection]))->setDec('count');
                    }
                    $new_coll = M('collection');
                    $new_coll->where(array('id' => I('collection') ))->setInc('count');
                    $new_coll->where(array('id' => I('collection') ))->setField('UpdateTime',time());
                    $data[collection] = I('collection');
                };

                if (I('userid')) {
                    M('user')->where(array('id' => $video[userid]))->setDec('post');
                    M('user')->where(array('id' => I('userid') ))->setInc('post');
                    $data[userid] = I('userid');
                };

                if (I('viewed')) { $data[viewed] = I('viewed'); };

                if (I('url')) {
                    import('Class.VideoUrlParser', APP_PATH);
                    $data[url] = I('url');
                    $info = VideoUrlParser::parse(I('url'));
                    $data[imageUrl] = $info['img'];
                };

                if (I('verify')) { $data[verify] = I('verify'); };

                if (M('video')->where(array('id' => $video[id]))->save($data)) {
                	$this->redirect("/video/".$video[id]."/");
                }




           } else {
                $this->error('您没有权限','/');
           }

       } else {
           $this->error('该视频已被删除了','/');
       }

    }








}
?>