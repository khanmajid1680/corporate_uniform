<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'core/MY_Model.php';
class daily_collection_model extends my_model{
    public function __construct(){ parent::__construct('report', 'daily_collection'); }
    public function get_record(){
        $record     = [];
        $subsql 	= '';
        $subsql1 	= '';
        $having 	= '';
        if(isset($_REQUEST['_module_name']) && !empty($_REQUEST['_module_name'])){
            if($_REQUEST['_module_name'] == 'ORDER'){
                $subsql1.=" AND em.em_id = 'XXX'";
            }else{
                $subsql.=" AND om.om_id = 'XXX'";
            }
            $record['filter']['_module_name']['value'] = $_REQUEST['_module_name'];
            $record['filter']['_module_name']['text']  = $_REQUEST['_module_name'];
        }
        if(isset($_REQUEST['_payment_mode_name']) && !empty($_REQUEST['_payment_mode_name'])){
            $subsql .=" AND payment_mode.payment_mode_name = '".$_REQUEST['_payment_mode_name']."'";
            $subsql1.=" AND payment_mode.payment_mode_name = '".$_REQUEST['_payment_mode_name']."'";
            $record['filter']['_payment_mode_name']['value'] = $_REQUEST['_payment_mode_name'];
            $record['filter']['_payment_mode_name']['text']  = $_REQUEST['_payment_mode_name'];
        }
        if(isset($_REQUEST['_entry_date_from'])){
            if($_REQUEST['_entry_date_from'] != ''){
                $subsql .=" AND om.om_entry_date >= '".$_REQUEST['_entry_date_from']."'";
                $subsql1.=" AND em.em_entry_date >= '".$_REQUEST['_entry_date_from']."'";
                $record['filter']['_entry_date_from'] = $_REQUEST['_entry_date_from'];
            }
        }
        if(isset($_REQUEST['_entry_date_to'])){
            if($_REQUEST['_entry_date_to'] != ''){
                $subsql .=" AND om.om_entry_date <= '".$_REQUEST['_entry_date_to']."'";
                $subsql1.=" AND em.em_entry_date <= '".$_REQUEST['_entry_date_to']."'";
                $record['filter']['_entry_date_to'] = $_REQUEST['_entry_date_to'];
            }
        }
        if(isset($_REQUEST['_payment_mode_amt_from'])){
            if($_REQUEST['_payment_mode_amt_from'] != ''){
                $subsql .=" AND opmt.opmt_amt >= ".$_REQUEST['_payment_mode_amt_from'];
                $subsql1.=" AND epmt.epmt_amt >= ".$_REQUEST['_payment_mode_amt_from'];
                $record['filter']['_payment_mode_amt_from'] = $_REQUEST['_payment_mode_amt_from'];
            }
        }
        if(isset($_REQUEST['_payment_mode_amt_to'])){
            if($_REQUEST['_payment_mode_amt_to'] != ''){
                $subsql .=" AND opmt.opmt_amt <= ".$_REQUEST['_payment_mode_amt_to'];
                $subsql1.=" AND epmt.epmt_amt <= ".$_REQUEST['_payment_mode_amt_to'];
                $record['filter']['_payment_mode_amt_to'] = $_REQUEST['_payment_mode_amt_to'];
            }
        }
        $query="SELECT module_name,
                entry_date,
                payment_mode_name,
                payment_mode_amt,
                created_at
                FROM (
                    SELECT 'ORDER' as module_name, 
                    IF(om.om_status=0,DATE_FORMAT(om.om_em_entry_date, '%d-%m-%Y'),DATE_FORMAT(om.om_entry_date, '%d-%m-%Y')) as entry_date,
                    UPPER(payment_mode.payment_mode_name) as payment_mode_name,
                    SUM(opmt.opmt_amt) as payment_mode_amt,
                    om.om_created_at as created_at
                    FROM order_master om
                    INNER JOIN order_payment_mode_trans opmt ON(opmt.opmt_om_id = om.om_id)
                    INNER JOIN payment_mode_master payment_mode ON(payment_mode.payment_mode_id = opmt.opmt_payment_mode_id)
                    WHERE om.om_delete_status = 0
                    AND opmt.opmt_delete_status = 0
                    $subsql
                    GROUP BY om.om_entry_date, payment_mode.payment_mode_id
                    UNION ALL
                    SELECT 'ESTIMATE' as module_name,
                    DATE_FORMAT(em.em_entry_date, '%d-%m-%Y') as entry_date,
                    UPPER(payment_mode.payment_mode_name) as payment_mode_name,
                    SUM(epmt.epmt_amt) as payment_mode_amt,
                    em.em_created_at as created_at
                    FROM estimate_master em
                    INNER JOIN estimate_payment_mode_trans epmt ON(epmt.epmt_em_id = em.em_id)
                    INNER JOIN payment_mode_master payment_mode ON(payment_mode.payment_mode_id = epmt.epmt_payment_mode_id)
                    WHERE em.em_delete_status = 0
                    AND epmt.epmt_delete_status = 0
                    AND em.em_om_id = 0
                    $subsql1
                    GROUP BY em.em_entry_date, payment_mode.payment_mode_id
                ) temp
                WHERE 1
                ORDER BY created_at DESC";
        $data = $this->db->query($query)->result_array();
        // echo "<pre>"; print_r($query); exit();
        // echo "<pre>"; print_r($data); exit();
        
        $record['totals']['rows']               = count($data);
        $record['totals']['payment_mode_amt']   = 0;
        $record['data']                         = [];
        if(!empty($data)){
            foreach ($data as $key => $value) {
                array_push($record['data'], [
                                                'module_name'       => $value['module_name'],
                                                'entry_date'        => $value['entry_date'],
                                                'payment_mode_name' => $value['payment_mode_name'],
                                                'payment_mode_amt'  => (float)$value['payment_mode_amt']
                                            ]);

                $record['totals']['payment_mode_amt'] = $record['totals']['payment_mode_amt'] + $value['payment_mode_amt'];
            }
        }
        
        // echo "<pre>"; print_r($record); exit();
        return $record;
    }
    public function _module_name(){ 
        return [0 => ['id' => 'ESTIMATE', 'name' => 'ESTIMATE'], 1 => ['id' => 'ORDER', 'name' => 'ORDER']];
    }
    public function _payment_mode_name(){ 
        $subsql = '';
        $limit  = PER_PAGE;
        $offset = OFFSET;
        $page   = 1;
        if(isset($_GET['limit']) && !empty($_GET['limit'])){
            $limit = $_GET['limit'];
        }
        if(isset($_GET['page']) && !empty($_GET['page'])){
            $page   = $_GET['page'];
            $offset = $limit * ($page - 1);
        }
        if(isset($_GET['name']) && !empty($_GET['name'])){
            $name   = $_GET['name'];
            $subsql .= " AND (payment_mode.payment_mode_name LIKE '%".$name."%') ";
        }
        $query="SELECT id, name
                FROM (
                        SELECT payment_mode.payment_mode_name as id , UPPER(payment_mode.payment_mode_name) as name 
                        FROM order_master om 
                        INNER JOIN order_payment_mode_trans opmt ON(opmt.opmt_om_id = om.om_id)
                        INNER JOIN payment_mode_master payment_mode ON(payment_mode.payment_mode_id = opmt.opmt_payment_mode_id)
                        WHERE om.om_delete_status = 0
                        AND opmt.opmt_delete_status = 0
                        $subsql
                        UNION
                        SELECT payment_mode.payment_mode_name as id , UPPER(payment_mode.payment_mode_name) as name 
                        FROM estimate_master em 
                        INNER JOIN estimate_payment_mode_trans epmt ON(epmt.epmt_em_id = em.em_id)
                        INNER JOIN payment_mode_master payment_mode ON(payment_mode.payment_mode_id = epmt.epmt_payment_mode_id)
                        WHERE em.em_delete_status = 0
                        AND epmt.epmt_delete_status = 0
                        AND em.em_om_id = 0
                        $subsql
                    ) temp
                WHERE 1
                GROUP BY id ASC
                LIMIT $limit
                OFFSET $offset";
        // echo $query; exit();
        return $this->db->query($query)->result_array();
    }
}
?>