<?php
/**
 * 模块说明信息
 * @program    T_PROGRAM
 * @author     T_AUTHOR
 * @devtime    T_DEVTIME
 */
namespace T_NAMESPACE;
use H2O\web\Controller;
class T_CLASS extends Controller
{
    /**
     * 列表页
     * @return string
     */
    public function actIndex()
    {
        $m = new \T_MODEL_NAMESPACE\T_CLASS();
        $data = $m->getAll(); //获取数据
        return $this->render('index',['data'=>$data]);
    }
    /**
     * 显示详情
     * @return mixed
     */
    public function actDetail()
    {
        $request = $this->request();
        $get = $request->get();//获取参数
        $m = new \T_MODEL_NAMESPACE\T_CLASS();
        $info = $m->getRow('TODO',$get['id']);
        return $this->render('detail',['info'=>$info]);
    }
    /**
     * 新增数据
     * @return mixed
     */
    public function actAdd()
    {
        $m = new \T_MODEL_NAMESPACE\T_CLASS();
        $request = $this->request();
        if($request->getIsGet()){
            return $this->render('add');
        }else{
            $m->add($request->post());
            $this->redirect('//TODO jump url');
        }
    }
    /**
     * 修改数据
     * @return mixed
     */
    public function actEdit()
    {
        $m = new \T_MODEL_NAMESPACE\T_CLASS();
        $request = $this->request();
        $id = $request->get();
        if($request->getIsGet()){
            return $this->render('edit');
        }else{
            $m->edit($request->post(),'//TODO',$id);
            $this->redirect('//TODO jump url');
        }
    }
    /**
     * 删除数据
     */
    public function actDel()
    {
        $m = new \T_MODEL_NAMESPACE\T_CLASS();
        $request = $this->request();
        $id = $request->get();
        if(!empty($id)){
            $m->delete('//TODO',$id);
            $this->redirect('//TODO jump url');
        }
    }
}
?>