<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'core/MY_Model.php';
class style_setting_model extends my_model{
	public function __construct(){ parent::__construct('master', 'style_setting'); }
	public function isExist($id){
		// $data = $this->db->query("SELECT pt_id FROM purchase_trans WHERE pt_style_setting_id = $id LIMIT 1")->result_array();
		// if(!empty($data)) return true;

		return false;
	}
    public function get_list($wantCount, $per_page = 20, $offset = 0){
        $record 	= [];
        $subsql 	= '';
        $limit  	= '';
        $ofset  	= '';
        
        if(!$wantCount){
            $limit .= " LIMIT $per_page";
            $ofset .= " OFFSET $offset";
        }
        
        if(isset($_GET['_apparel_name']) && !empty($_GET['_apparel_name'])){
            $subsql .=" AND apparel.apparel_name LIKE '%".$_GET['_apparel_name']."%'";
            $record['filter']['_apparel_name']['value'] = $_GET['_apparel_name'];
            $record['filter']['_apparel_name']['text'] = $_GET['_apparel_name'];
        }
        if(isset($_GET['_style_name']) && !empty($_GET['_style_name'])){
            $subsql .=" AND style.style_name LIKE '%".$_GET['_style_name']."%'";
            $record['filter']['_style_name']['value'] = $_GET['_style_name'];
            $record['filter']['_style_name']['text'] = $_GET['_style_name'];
        }
        if(isset($_GET['_status'])){
            $status = $_GET['_status'] == 2 ? 0 : $_GET['_status'];
            $subsql .=" AND apparel_status = ".$status;
            $record['filter']['_status'] = $this->Commonmdl->get_status($_GET['_status']);
        }
        $query="SELECT style_setting.*,
                style_setting.style_setting_default as isExist,
                UPPER(apparel.apparel_name) as apparel_name, 
                IFNULL(UPPER(style.style_name), '') as style_name
                FROM style_setting_master style_setting
                INNER JOIN apparel_master apparel ON(apparel.apparel_id = style_setting.style_setting_apparel_id)
                INNER JOIN style_master style ON(style.style_id = style_setting.style_setting_style_id)
                WHERE 1
                $subsql
                ORDER BY style_setting.style_setting_id DESC
                $limit
                $ofset";
        // echo "<pre>"; print_r($query); exit;
        if($wantCount){
            return $this->db->query($query)->num_rows();
        }
        $record['data'] = $this->db->query($query)->result_array();
        if(!empty($record['data'])){
            foreach ($record['data'] as $key => $value) {
                $record['data'][$key]['isExist']    = $value['isExist'] ?  $value['isExist'] : $this->isExist($value['style_setting_id']);
            }
        }
        return $record;
    }
    public function get_data($id){
        $query="SELECT style_setting.*,
                UPPER(apparel.apparel_name) as apparel_name, 
                IFNULL(UPPER(style.style_name), '') as style_name
                FROM style_setting_master style_setting
                INNER JOIN apparel_master apparel ON(apparel.apparel_id = style_setting.style_setting_apparel_id)
                INNER JOIN style_master style ON(style.style_id = style_setting.style_setting_style_id)
                WHERE style_setting.style_setting_id = $id";
        return $this->db->query($query)->result_array();
    }
    public function _apparel_name(){
        $subsql = "";
        $limit  = PER_PAGE;
        $offset = OFFSET;
        $page 	= 1;
        if(isset($_GET['limit']) && !empty($_GET['limit'])){
            $limit = $_GET['limit'];
        }
        if(isset($_GET['page']) && !empty($_GET['page'])){
            $page 	= $_GET['page'];
            $offset = $limit * ($page - 1);
        }
        if(isset($_GET['name']) && !empty($_GET['name'])){
            $name 	= $_GET['name'];
            $subsql .= " AND (apparel.apparel_name LIKE '".$name."%') ";
        }
        if(isset($_GET['param']) && !empty($_GET['param'])){
            $param 	= $_GET['param'];
            $subsql .= " AND (style_setting.style_setting_status = $param) ";
        }
        $query="SELECT apparel.apparel_name as id, UPPER(apparel.apparel_name) as name
                FROM style_setting_master style_setting
                INNER JOIN apparel_master apparel ON(apparel.apparel_id = style_setting.style_setting_apparel_id)
                WHERE 1
                $subsql
                GROUP BY apparel.apparel_name ASC
                LIMIT $limit
                OFFSET $offset";
        // echo $query; exit();
        return $this->db->query($query)->result_array();
    }
    public function _style_name(){
        $subsql = "";
        $limit  = PER_PAGE;
        $offset = OFFSET;
        $page 	= 1;
        if(isset($_GET['limit']) && !empty($_GET['limit'])){
            $limit = $_GET['limit'];
        }
        if(isset($_GET['page']) && !empty($_GET['page'])){
            $page 	= $_GET['page'];
            $offset = $limit * ($page - 1);
        }
        if(isset($_GET['name']) && !empty($_GET['name'])){
            $name 	= $_GET['name'];
            $subsql .= " AND (style.style_name LIKE '".$name."%') ";
        }
        if(isset($_GET['param']) && !empty($_GET['param'])){
            $param 	= $_GET['param'];
            $subsql .= " AND (style_setting.style_setting_status = $param) ";
        }
        $query="SELECT style.style_name as id, UPPER(style.style_name) as name
                FROM style_setting_master style_setting
                INNER JOIN style_master style ON(style.style_id = style_setting.style_setting_style_id)
                WHERE 1
                $subsql
                GROUP BY style.style_name ASC
                LIMIT $limit
                OFFSET $offset";
        // echo $query; exit();
        return $this->db->query($query)->result_array();
    }
}
?>
