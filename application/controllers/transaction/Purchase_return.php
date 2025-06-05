<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'core/MY_Controller.php';
class purchase_return extends my_controller{
    protected $menu;
    protected $sub_menu;
	public function __construct(){
        $this->menu     = 'transaction'; 
        $this->sub_menu = 'purchase_return'; 
        parent::__construct($this->menu, $this->sub_menu); 
    }
    public function get_barcode_data(){
        $post_data  = $this->input->post();
        $id         = $post_data['id'];
        $supplier_id= $post_data['supplier_id'];
        // echo "<pre>"; print_r($post_data); exit;

        $data = $this->model->get_barcode_data($id);
        if((empty($data))) return ['msg' => '1. Barcode not found.'];
        // echo "<pre>"; print_r($data); exit;


        if($data[0]['delete_status'] == 1) return ['msg' => '1. Barcode is deleted.'];

		if($supplier_id != 0 && ($supplier_id != $data[0]['supplier_id'])) return ['msg' => '1. Scanned barcode is from different supplier.'];

        if($data[0]['bal_mtr'] <= 0) return ['msg' => '1. Barcode not available.'];
        
        return['status' => TRUE, 'data' => $data, 'msg' => 'Barcode scanned successfully.'];
	}
    public function remove(){
		$post_data  = $this->input->post();
		$id         = $post_data['id'];
		$result     = isMenuAssigned($this->menu, $this->sub_menu, 'delete');
		if(!$result['session'] || !$result['status'] || !$result['active']) return $result;

		$data = $this->db_operations->get_record('purchase_return_master', ['prm_id' => $id, 'prm_delete_status' => false]);
		if(empty($data)) return ['status' => REFRESH, 'msg' => '3. Purchase return not found.'];	

		if($this->model->isExist($id)) return ['msg' => '1. Not allowed to delete.'];	

		$trans_data = $this->db_operations->get_record('purchase_return_trans', ['prt_prm_id' => $id, 'prt_delete_status' => false]);
		if(empty($trans_data)) return ['msg' => '2. Transaction not found.'];
		
		$this->db->trans_begin();
		
		foreach ($trans_data as $key => $value) {
			if($this->model->isTransExist($value['prt_id'])){
				$this->db->trans_rollback();
				return ['msg' => '2. Not allowed to delete transaction.'];
			}

			$result = $this->delete_barcode($value['prt_bm_id'], $value['prt_total_mtr']);
			if(!isset($result['status'])) {
				$this->db->trans_rollback();
				return $result;
			}

			$update_data 						= [];
			$update_data['prt_delete_status'] 	= true; 
			$update_data['prt_updated_by'] 		= $_SESSION['user_id']; 
			$update_data['prt_updated_at'] 		= date('Y-m-d H:i:s'); 
			if($this->db_operations->data_update('purchase_return_trans', $update_data, 'prt_id', $value['prt_id']) < 1){
				$this->db->trans_rollback();
				return ['msg' => '2. Transaction not deleted.'];
			}
		}

		$update_data 						= [];
		$update_data['prm_entry_no'] 		= $data[0]['prm_entry_no'].''.$id; 
		$update_data['prm_delete_status'] 	= true; 
		$update_data['prm_updated_by'] 		= $_SESSION['user_id']; 
		$update_data['prm_updated_at'] 		= date('Y-m-d H:i:s'); 
		if($this->db_operations->data_update('purchase_return_master', $update_data, 'prm_id', $id) < 1){
			$this->db->trans_rollback();
			return ['msg' => 'Purchase return not deleted.'];
		}

		if ($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return ['msg' => '2. Transaction Rollback.'];
		}
		$this->db->trans_commit();

		return ['status' => TRUE, 'msg' => 'Purchase return deleted successfully'];
	}
    // purchase_return_master
        public function add_edit(){
			$post_data  = $this->input->post();
            $id         = $post_data['id'];
			if(!isset($post_data['trans_data']) || (isset($post_data['trans_data']) && empty($post_data['trans_data']))) return ['msg' => '1. Barcode not added in list.'];
			$post_data['trans_data'] = json_decode($post_data['trans_data'], true);

			// echo "<pre>"; print_r($post_data); exit;
			// master_data
                $master_data['prm_uuid'] 				    = trim($post_data['prm_uuid']);
                $master_data['prm_entry_no'] 				= trim($post_data['prm_entry_no']);
                $master_data['prm_entry_date'] 				= date('Y-m-d', strtotime($post_data['prm_entry_date']));
                $master_data['prm_supplier_id'] 			= trim($post_data['prm_supplier_id']);
                $master_data['prm_total_qty'] 				= trim($post_data['prm_total_qty']);
                $master_data['prm_total_mtr'] 				= trim($post_data['prm_total_mtr']);
                $master_data['prm_notes'] 					= trim($post_data['prm_notes']);
                $master_data['prm_sub_amt'] 				= trim($post_data['prm_sub_amt']);
                $master_data['prm_disc_amt'] 				= trim($post_data['prm_disc_amt']);
                $master_data['prm_taxable_amt'] 			= trim($post_data['prm_taxable_amt']);
                $master_data['prm_sgst_amt']				= trim($post_data['prm_sgst_amt']);
                $master_data['prm_cgst_amt']				= trim($post_data['prm_cgst_amt']);
                $master_data['prm_igst_amt']				= trim($post_data['prm_igst_amt']);
                $master_data['prm_round_off']				= trim($post_data['prm_round_off']);
                $master_data['prm_bill_disc_per']			= trim($post_data['prm_bill_disc_per']);
                $master_data['prm_bill_disc_amt']			= trim($post_data['prm_bill_disc_amt']);
                $master_data['prm_total_amt']				= trim($post_data['prm_total_amt']);
                $master_data['prm_updated_by'] 				= $_SESSION['user_id'];
				$master_data['prm_updated_at'] 				= date('Y-m-d H:i:s');
            // master_data

			$this->db->trans_begin();
			if($id == 0){
				$master_data['prm_entry_no'] 	= $this->model->get_max_entry_no(['entry_no' => 'prm_entry_no', 'delete_status' => 'prm_delete_status', 'fin_year' => 'prm_fin_year']);
				$master_data['prm_created_by'] 	= $_SESSION['user_id'];
				$master_data['prm_created_at'] 	= date('Y-m-d H:i:s');
				$master_data['prm_fin_year'] 	= $_SESSION['fin_year'];
				$master_data['prm_branch_id'] 	= $_SESSION['user_branch_id'];
				$uuidExist 						= $this->db_operations->get_cnt($this->sub_menu.'_master', ['prm_uuid' => $master_data['prm_uuid']]);
				if($uuidExist > 0){
					$this->db->trans_rollback();
					return ['msg' => 'Form already submited.'];
				}
				$id = $this->db_operations->data_insert('purchase_return_master', $master_data);
				$msg = 'Purchase return added successfully.';
				if($id < 1){
					$this->db->trans_rollback();
					return ['msg' => '1. Purchase return not added.'];
				}
			}else{
				$prev_data = $this->db_operations->get_record('purchase_return_master', ['prm_id' => $id, 'prm_delete_status' => false]);
				if(empty($prev_data)){
					$this->db->trans_rollback();
					return ['status' => REFRESH, 'msg' => '1. Purchase return not found.'];
				}
				$msg = 'Purchase return updated successfully.';
				if($this->db_operations->data_update('purchase_return_master', $master_data, 'prm_id', $id) < 1){
					$this->db->trans_rollback();
					return ['msg' => '1. Purchase return not updated.'];
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

			$data['name'] 	= strtoupper($master_data['prm_entry_no']);
			return ['status' => TRUE, 'data' => $data,  'msg' => $msg];
		}
    // purchase_return_master

    // purchase_return_trans
        public function add_update_trans($post_data, $id){
			$trans_db_data = $this->db_operations->get_record('purchase_return_trans', ['prt_prm_id' => $id, 'prt_delete_status' => false]);
			$ids 	   	   = $this->get_id($post_data['trans_data'], 'prt_id');
			if(!empty($trans_db_data)){
				foreach ($trans_db_data as $key => $value){
					if(!in_array($value['prt_id'], $ids)){
						if($this->model->isTransExist($value['prt_id'])) return ['msg' => '1. Not allowed to delete transaction.'];
						$result = $this->delete_barcode($value['prt_bm_id'], $value['prt_total_mtr']);
						if(!isset($result['status'])) return $result;
	
						$update_data 						= [];
						$update_data['prt_delete_status'] 	= true; 
						$update_data['prt_updated_by'] 		= $_SESSION['user_id']; 
						$update_data['prt_updated_at'] 		= date('Y-m-d H:i:s'); 
						if($this->db_operations->data_update('purchase_return_trans', $update_data, 'prt_id', $value['prt_id']) < 1) return ['msg' => '1. Transaction not deleted.'];
					}
				}
			}
			foreach ($post_data['trans_data'] as $key => $value){
				$trans_data 					= [];
				$trans_data['prt_prm_id'] 		= $id;
				$trans_data['prt_prm_uuid'] 	= $post_data['prm_uuid'];
				$trans_data['prt_pm_id'] 		= isset($value['prt_pm_id']) ? $value['prt_pm_id'] : 0;
				$trans_data['prt_pt_id'] 		= isset($value['prt_pt_id']) ? $value['prt_pt_id'] : 0;
				$trans_data['prt_bm_id'] 		= isset($value['prt_bm_id']) ? $value['prt_bm_id'] : 0;
				$trans_data['prt_qty'] 			= trim($value['prt_qty']);
				$trans_data['prt_rate'] 		= trim($value['prt_rate']);
				$trans_data['prt_mtr'] 			= trim($value['prt_mtr']);
				$trans_data['prt_total_mtr'] 	= trim($value['prt_total_mtr']);
				$trans_data['prt_amt'] 			= trim($value['prt_amt']);
				$trans_data['prt_disc_per'] 	= trim($value['prt_disc_per']);
				$trans_data['prt_disc_amt'] 	= trim($value['prt_disc_amt']);
				$trans_data['prt_taxable_amt'] 	= trim($value['prt_taxable_amt']);
				$trans_data['prt_sgst_per'] 	= trim($value['prt_sgst_per']);
				$trans_data['prt_sgst_amt'] 	= trim($value['prt_sgst_amt']);
				$trans_data['prt_cgst_per'] 	= trim($value['prt_cgst_per']);
				$trans_data['prt_cgst_amt'] 	= trim($value['prt_cgst_amt']);
				$trans_data['prt_igst_per'] 	= trim($value['prt_igst_per']);
				$trans_data['prt_igst_amt'] 	= trim($value['prt_igst_amt']);
				$trans_data['prt_total_amt'] 	= trim($value['prt_total_amt']);
				$trans_data['prt_updated_by'] 	= $_SESSION['user_id'];
				$trans_data['prt_updated_at'] 	= date('Y-m-d H:i:s');

				if($value['prt_id'] == 0){
					$trans_data['prt_created_by'] 	= $_SESSION['user_id'];
					$trans_data['prt_created_at'] 	= date('Y-m-d H:i:s');

					if($this->db_operations->data_insert('purchase_return_trans', $trans_data) < 1){
						return ['msg' => 'Transaction not added.'];
					}
				}else{
					$prev_data = $this->db_operations->get_record('purchase_return_trans', ['prt_id' => $value['prt_id'], 'prt_delete_status' => false]);
					if(empty($prev_data)) return ['msg' => '2. Transaction not found.'];

					if($this->db_operations->data_update('purchase_return_trans', $trans_data, 'prt_id', $value['prt_id']) < 1){
						return ['msg' => 'Transaction not updated.'];
					}

					$result = $this->delete_barcode($trans_data['prt_bm_id'], $trans_data['prt_total_mtr']);
					if(!isset($result['status'])) return $result;
				}
				$result = $this->update_barcode($trans_data['prt_bm_id'], $trans_data['prt_total_mtr']);
				if(!isset($result['status'])) return $result;
			}
			return ['status' => TRUE];
		}
		
    // purchase_return_trans

    // barcode_master
		public function update_barcode($bm_id, $mtr){
			$data = $this->model->get_barcode_data($bm_id);
			if(empty($data)) return ['status' => FALSE, 'data' => FALSE, 'msg' => '2. Barcode not found.'];

			if($data[0]['delete_status'] == 1) return ['status' => FALSE, 'data' => FALSE, 'msg' => '2. Barcode is deleted.'];

			if($data[0]['bal_mtr'] < 1) return ['status' => FALSE, 'data' => FALSE, 'msg' => '2. Barcode not available for purchase return'];

			$prt_mtr = $data[0]['bm_prt_mtr'] + $mtr;
			if($this->db_operations->data_update('barcode_master', ['bm_prt_mtr' => $prt_mtr], 'bm_id', $bm_id) < 1){
				return ['status' => FALSE, 'data' => FALSE, 'msg' => '1.Barcode not updated.'];	
			}
			return ['status' => TRUE, 'data' => TRUE, 'msg' => ''];	
		}
		public function delete_barcode($bm_id, $mtr){
			$data = $this->model->get_barcode_data($bm_id);
			if(empty($data)) return ['status' => FALSE, 'data' => FALSE, 'msg' => '3. Barcode not found'];

			if($data[0]['bm_prt_mtr'] <= 0) return ['status' => FALSE, 'data' => FALSE, 'msg' => 'Barcode not available.'];
			
			$prt_mtr = $data[0]['bm_prt_mtr'] - $mtr;
			if($this->db_operations->data_update('barcode_master', ['bm_prt_mtr' => $prt_mtr], 'bm_id', $bm_id) < 1){
				return ['status' => FALSE, 'data' => FALSE, 'msg' => '2.Barcode not updated.'];	
			}
			return ['status' => TRUE, 'data' => TRUE, 'msg' => ''];	
		}
    // barcode_master
}
?>