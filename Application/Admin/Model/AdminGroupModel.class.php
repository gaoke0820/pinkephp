<?php
namespace Admin\Model;
use Think\Model;
class AdminGroupModel extends Model{

    const ADMIN_ADD        = 11; // 添加
    const ADMIN_SAVE       = 12; // 修改
    const ADMIN_DEL        = 13; // 删除

    private $tmp_data;
    private $old_data;
    private $scene_id;

    //字段衍射
    protected $_map = array(
                            
                        );
    //修改插入后自动完成
    protected $_auto = array(

        // 添加
        array('create_time','time',self::ADMIN_ADD,'function'),


        // 修改
        array('update_time','time',self::ADMIN_SAVE,'function'),


    );

    protected $_validate = array(

        // 添加
        array('title', 'is_form_token_pass', '过期的token或来自非指定方法创建的表单', self::MUST_VALIDATE,'function',self::ADMIN_ADD),
        array('title', 'is_notempty_pass', '用户组名称不能为空', self::MUST_VALIDATE,'function',self::ADMIN_ADD),
        array('title', 'is_nothassamename_pass', '已有相同的分组名称', self::MUST_VALIDATE,'callback',self::ADMIN_ADD),
        array('menu_auth', 'get_menu_auth', 'return_true', self::MUST_VALIDATE,'callback',self::ADMIN_ADD),

        // 修改
        array('id', 'is_form_token_pass', '过期的token或来自非指定方法创建的表单', self::MUST_VALIDATE,'function',self::ADMIN_SAVE),
        array('id', 'is_id_pass', '错误的id', self::MUST_VALIDATE,'callback',self::ADMIN_SAVE),
        array('title', 'is_notempty_pass', '用户组名称不能为空', self::MUST_VALIDATE,'function',self::ADMIN_SAVE),
        array('title', 'is_nothassamename_pass', '已有相同的分组名称', self::MUST_VALIDATE,'callback',self::ADMIN_SAVE),
        array('menu_auth', 'get_menu_auth', 'return_true', self::MUST_VALIDATE,'callback',self::ADMIN_SAVE),
        

        // 删除
        array('id', 'is_id_pass', '错误的id', self::MUST_VALIDATE,'callback',self::ADMIN_DEL),
        array('id', 'is_notsuppergroupid_pass', '不能删除超级管理员组', self::MUST_VALIDATE,'callback',self::ADMIN_DEL), 
        array('id', 'is_nothassonid_pass', '先删除该管理组下的子管理组才能删除', self::MUST_VALIDATE,'callback',self::ADMIN_DEL),       
        
    );

    public function getAdminMenuAuth(){
        return $this->tmp_data['menu_auth']['Admin'];
    }
    /**
     ***********************
     * 记录方法
     ***********************
     */
    protected function _after_insert($data, $options) {
        $id = $this->getLastInsID();
        admin_log('AdminGroup',self::ADMIN_ADD,$id,admin_session_admin_id(),'','',$data);
    }

    protected function _after_update($data, $options) {
        $id = $data['id'];
        admin_log('AdminGroup',self::ADMIN_SAVE,$id,admin_session_admin_id(),"",$this->old_data,$data);
    }
    protected function _after_delete($data, $options) {
        $id = $data['id'];
        admin_log('AdminGroup',self::ADMIN_DEL,$id,admin_session_admin_id(),"",$this->old_data,$data);
    }
    /**
     ***********************
     * 业务方法
     ***********************
     */
    protected function is_id_pass($id){
        $info = $this->where(array('id'=>$id))->find();
        if($info){
            $this->old_data = $info;
            return true;
        }
        return false;  
    }

    protected function get_menu_auth($menu_auth){
        $this->tmp_data['menu_auth'] = $menu_auth;
        return true;
    }

    protected function is_notsuppergroupid_pass($id){
        if($id == 1){
            return false;
        }
        return true;
    }
    protected function is_nothassonid_pass($id){
        $has = $this->where(array("pid"=>$id))->find();
        if($has){
            return false;
        }
        return true;
    }
    protected function is_nothassamename_pass($name){
        $this_id = (int)$this->old_data['id'];
        $has = $this->where(array('title'=>$name,'id'=>array('neq',$this_id)))->find();
        if($has){
            return false;
        }
        return true;
    }    

}