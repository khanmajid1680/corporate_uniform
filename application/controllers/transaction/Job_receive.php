<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'core/MY_Controller.php';
class job_receive extends my_controller{
    protected $menu;
    protected $sub_menu;
	public function __construct(){
        $this->menu     = 'transaction'; 
        $this->sub_menu = 'job_receive'; 
        parent::__construct($this->menu, $this->sub_menu); 
    }
    public function remove(){
		$post_data  = $this->input->post();
		$id         = $post_data['id'];
		$result     = isMenuAssigned($this->menu, $this->sub_menu, 'delete');
		if(!$result['session'] || !$result['status'] || !$result['active']) return $result;

		$data = $this->db_operations->get_record($this->sub_menu.'_master', ['jrm_id' => $id, 'jrm_delete_status' => false]);
		if(empty($data)) return ['status' => REFRESH, 'msg' => '3. Job receive not found.'];	

		if($this->model->isExist($id)) return ['msg' => '1. Not allowed to delete.'];	

		$trans_data = $this->db_operations->get_record($this->sub_menu.'_trans', ['jrt_jrm_id' => $id, 'jrt_delete_status' => false]);
		if(empty($trans_data)) return ['msg' => '2. Transaction not found.'];
		
		$this->db->trans_begin();
		
		foreach ($trans_data as $key => $value) {
			if($this->model->isTransExist($value['jrt_id'])){
				$this->db->trans_rollback();
				return ['msg' => '2. Not allowed to delete transaction.'];
			}

			$update_data 						= [];
			$update_data['jrt_delete_status'] 	= true; 
            $update_data['jrt_jim_id'] 	        = 0; 
            $update_data['jrt_jit_id'] 	        = 0; 
			$update_data['jrt_updated_by'] 		= $_SESSION['user_id']; 
			$update_data['jrt_updated_at'] 		= date('Y-m-d H:i:s'); 
			if($this->db_operations->data_update($this->sub_menu.'_trans', $update_data, 'jrt_id', $value['jrt_id']) < 1){
				$this->db->trans_rollback();
				return ['msg' => '2. Transaction not deleted.'];
			}
		}

		$update_data 						= [];
		$update_data['jrm_entry_no'] 		= $data[0]['jrm_entry_no'].''.$id; 
		$update_data['jrm_delete_status'] 	= true; 
		$update_data['jrm_updated_by'] 		= $_SESSION['user_id']; 
		$update_data['jrm_updated_at'] 		= date('Y-m-d H:i:s'); 
		if($this->db_operations->data_update($this->sub_menu.'_master', $update_data, 'jrm_id', $id) < 1){
			$this->db->trans_rollback();
			return ['msg' => 'Job receive not deleted.'];
		}

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return ['msg' => '2. Transaction Rollback.'];
		}
		$this->db->trans_commit();

		return ['status' => TRUE, 'msg' => 'Job receive deleted successfully'];
	}
    public function get_barcode_data(){
        $post_data  = $this->input->post();
        $id         = $post_data['id'];
        $data       = $this->model->get_barcode_data($id);

        if(empty($data)) return ['msg' => '1. Barcode not found.'];
        
        if($data[0]['obt_delete_status'] == 1) return ['msg' => '1. Barcode is deleted.'];

        if($data[0]['jrt_jit_id'] != 0) return ['msg' => '1. Barcode already received from '.$data[0]['proces_name']];
        
        #TODO: check validation for barcode

        return ['status' => TRUE, 'data' => $data, 'msg' => 'Barcode scan successfully.'];
    }

    public function add_edit(){
        $post_data  = $this->input->post();
        $id         = $post_data['id'];
        
        $post_data['trans_data'] = json_decode($post_data['trans_data'], true);
        if(!isset($post_data['trans_data']) || (isset($post_data['trans_data']) && empty($post_data['trans_data']))) return ['msg' => '1. Item not aded in list.'];

        // master_data
            $master_data                    = [];
            $master_data['jrm_uuid'] 	    = trim($post_data['jrm_uuid']);
        // master_data

        $this->db->trans_begin();
			if($id == 0){
				$master_data['jrm_entry_no'] 	= $this->model->get_max_entry_no(['entry_no' => 'jrm_entry_no', 'delete_status' => 'jrm_delete_status', 'fin_year' => 'jrm_fin_year']);
                $master_data['jrm_entry_date'] 	= date('Y-m-d');
				$master_data['jrm_fin_year'] 	= $_SESSION['fin_year'];
				$master_data['jrm_created_by'] 	= $_SESSION['user_id'];
                $master_data['jrm_created_at'] 	= date('Y-m-d H:i:s');
                $master_data['jrm_updated_by'] 	= $_SESSION['user_id'];
                $master_data['jrm_updated_at'] 	= date('Y-m-d H:i:s');
				$uuidExist 						= $this->db_operations->get_cnt($this->sub_menu.'_master', ['jrm_uuid' => $master_data['jrm_uuid']]);
				if($uuidExist > 0){
					$this->db->trans_rollback();
					return ['msg' => 'Form already submited.'];
				}
				$id = $this->db_operations->data_insert($this->sub_menu.'_master', $master_data);
				$msg = 'Job receive added successfully.';
				if($id < 1){
					$this->db->trans_rollback();
					return ['msg' => '1. Job receive not added.'];
				}
			}else{
				$prev_data = $this->db_operations->get_record($this->sub_menu.'_master', ['jrm_id' => $id, 'jrm_delete_status' => false]);
				if(empty($prev_data)){
					$this->db->trans_rollback();
					return ['status' => REFRESH, 'msg' => '1. Job receive not found.'];
				}
				$msg = 'Job receive updated successfully.';
				if($this->db_operations->data_update($this->sub_menu.'_master', $master_data, 'jrm_id', $id) < 1){
					$this->db->trans_rollback();
					return ['msg' => '1. Job receive not updated.'];
				}
			}
			$result = $this->add_update_trans($post_data, $id);
			if(!isset($result['status'])){
				$this->db->trans_rollback();
				return $result;
			}

			if ($this->db->trans_status() === FALSE){
			    $this->db->trans_rollback();
			    return ['msg' => '1. Transaction Rollback.'];
		    }
		    $this->db->trans_commit();

			$data['id'] 	= encrypt_decrypt("encrypt", $id, SECRET_KEY);
			$data['name'] 	= '';
			return ['status' => TRUE, 'data' => $data,  'msg' => $msg];
    }
    public function add_update_trans($post_data, $id){
        $trans_db_data = $this->db_operations->get_record($this->sub_menu.'_trans', ['jrt_jrm_id' => $id, 'jrt_delete_status' => false]);
        $ids 	   	   = $this->get_id($post_data['trans_data'], 'jrt_id');
        if(!empty($trans_db_data)){
            foreach ($trans_db_data as $key => $value){
                if(!in_array($value['jrt_id'], $ids)){
                    if($this->model->isTransExist($value['jrt_id'])) return ['msg' => '1. Not allowed to delete transaction.'];
                    
                    $update_data 						= [];
                    $update_data['jrt_delete_status'] 	= true; 
                    $update_data['jrt_jim_id'] 	        = 0; 
                    $update_data['jrt_jit_id'] 	        = 0; 
                    $update_data['jrt_updated_by'] 		= $_SESSION['user_id']; 
                    $update_data['jrt_updated_at'] 		= date('Y-m-d H:i:s'); 
                    if($this->db_operations->data_update($this->sub_menu.'_trans', $update_data, 'jrt_id', $value['jrt_id']) < 1) return ['msg' => '1. Transaction not deleted.'];
                }
            }
        }
        foreach ($post_data['trans_data'] as $key => $value){
            $trans_data 					= [];
            $trans_data['jrt_jrm_id'] 		= $id;
            $trans_data['jrt_jrm_uuid'] 	= $post_data['jrm_uuid'];
            $trans_data['jrt_obt_id'] 	    = trim($value['obt_id']);
            $trans_data['jrt_jim_id'] 	    = trim($value['jim_id']);
            $trans_data['jrt_jit_id'] 	    = trim($value['jit_id']);
            $trans_data['jrt_delete_status']= false;
            $trans_data['jrt_updated_by'] 	= $_SESSION['user_id'];
            $trans_data['jrt_updated_at'] 	= date('Y-m-d H:i:s');
            
            if($value['jrt_id'] == 0){
                $trans_data['jrt_created_by'] 	= $_SESSION['user_id'];
                $trans_data['jrt_created_at'] 	= date('Y-m-d H:i:s');
                if($this->db_operations->data_insert($this->sub_menu.'_trans', $trans_data) < 1) return ['msg' => 'Transaction not added.'];
            }else{
                $prev_data = $this->db_operations->get_record($this->sub_menu.'_trans', ['jrt_id' => $value['jrt_id'], 'jrt_delete_status' => false]);
                if(empty($prev_data)) return ['msg' => '1. Transaction not found.'];

                if($this->db_operations->data_update($this->sub_menu.'_trans', $trans_data, 'jrt_id', $value['jrt_id']) < 1){
                    return ['msg' => 'Transaction not updated.'];
                }
            }
        }
        return ['status' => TRUE];
    }
    
}
?>