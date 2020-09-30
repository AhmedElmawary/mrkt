<?php

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=orders.csv;");

require_once ("includes/config.php");

class OrdersDownloader {
    function __construct($query_infos){
        $this->is_sodic($query_infos);
        $this->prepare_array($query_infos);
        $this->finalize_array($this->init_array);
        $this->query_runner();
        $this->download();
        
    }public /**void */ function is_sodic(String $query_infos) :void {
        $result = strstr($query_infos,"SODIC");
        if ( ! empty ($result))$result = true; else $result =false;
        $this->sodic_query= $result;
     return; 
    }public /**void */ function prepare_array( String $query_infos ) :void {
        $query_infos = strstr($query_infos,"orders") ;
        $this->table = strstr($query_infos," ",true);
        $query_infos_to_arr =  strstr($query_infos,"area");
        if($query_infos_to_arr == false ){
            $search_day = date('j',time());
            $year= date('Y',time());
            $search_start_month =  $year ."-". date('m',time());
            $this->init_array = ["area_id",'%',"order_state_id","%","project",'%',"start_day",1,'search_start_month',$search_start_month,"search_day",$search_day,"search_month",$search_start_month]; 
        }else
        $this->init_array = explode (" ", $query_infos_to_arr);
     return;
    }public /**void */ function finalize_array( /*String[]*/ Array $query_infos ) :void {
        foreach ($this->init_array as $key => $value){
            $even = $key %2; 
            if ($even == 0) continue;
             $final_array[$this->init_array[$key-1]] = $value ;
        }
           $this->final_array= $final_array ;
   return;
  }public function query_runner() {
        
        $area_id = $this->final_array["area_id"];      
         $order_state_id = $this->final_array["order_state_id"];      
         $project = $this->final_array["project"];      
         $date_of_filter = $this->final_array["search_start_month"];       
         $date_of_filter .= "-".$this->final_array["start_day"];      
         $max_date = $this->final_array["search_month"];       
         $max_date .= "-".$this->final_array["search_day"];       
         $con = get_db_con();
         $query =
				"SELECT user_id, orders.id AS order_id,delivery_staff_id,total_price,delivery_time,payment_method,is_paid,orders.created_at AS order_timestamp,
				order_states.name AS order_state_name,color,projects.name as project_name,
				city.name AS city_name,area.name AS area_name,area.id AS area_id 
				FROM orders,user,order_states,states_per_order,city,area, projects,orders_projects op
				WHERE city_id=city.id 
				AND order_states.id=order_state_id 
				AND op.project_id = projects.id 
				AND projects.area_id = area.id
				AND op.order_id = orders.id 
				AND order_states.id LIKE '" . $order_state_id . "' 
				AND orders.id=states_per_order.order_id 
				AND user.id=user_id 
				AND orders.created_at BETWEEN  '" . $date_of_filter . "' 
				AND '" . $max_date . "' 
				AND orders.area_id =area.id 
				AND area.id LIKE '" . $area_id . "' 
				AND op.project_id LIKE '{$project}'
				AND states_per_order.created_at=(SELECT MAX(created_at) 
				FROM states_per_order 
				WHERE states_per_order.order_id=orders.id) 
				ORDER BY orders.created_at DESC";
			$result = db_query($con, $query);
    $output[]= ["Order ID","Coupon","Created at","Delivery time","Order state","Payment method","Is paid","Area","Receiver","Project","Total price","Delivery staff"];
         while ($row = mysqli_fetch_assoc($result))
            {

                $created_at = format_date($row['order_timestamp']);

                $delivery_time = format_date($row['delivery_time']);

                $cou_or_id = $row['order_id'];

                $get_offers = "SELECT coupon_id FROM coupuser WHERE order_id=$cou_or_id";

                $co_orders = db_query($con, $get_offers);

                $cou_orders = db_fetch_assoc($co_orders);

                $id_id = $cou_orders['coupon_id'];

                $get_co = "SELECT  present FROM coupons WHERE id=$id_id";

                $co_get = db_query($con, $get_co);

                if (!empty($co_get)) {
                    $coupon_get = db_fetch_assoc($co_get);
                    $present = (float) $coupon_get['present'];
                } else $present = 0;

                $total_price = format_money($row['total_price']) . ' ' . CURRENCY_CODE;


                if ($row['is_paid']) $is_paid = 'Yes';
                else $is_paid = 'No';

                $delivery_staff_id = $row['delivery_staff_id'];
                $delivery_staff = '-';

                $query = "SELECT CONCAT(first_name, ' ' ,last_name) as 'full_name' FROM user WHERE id='" . $row['user_id'] . "'";
                  $result1 = db_query($con, $query);
                $user = mysqli_fetch_assoc($result1)["full_name"];

                if ($delivery_staff_id) {
                    $query = "SELECT full_name FROM delivery_staff WHERE id='" . $delivery_staff_id . "'";
                    $result1 = db_query($con, $query);
                    $row1 = db_fetch_assoc($result1);
                    $delivery_staff_name = $row1["full_name"];

                }

                $output[]= [$row["order_id"],$present . " " . CURRENCY_CODE,$created_at, $delivery_time, 
                $row['order_state_name']
                ,
                $row['payment_method'],$is_paid, $row['city_name']."-".$row['area_name'],$user,$row['project_name'],$total_price,$delivery_staff
                ] ;
            }
            $this->downloadable = $output;
            return;
        }public /**void  */ function download () {
            $os = fopen("php://output",'w');

            foreach ($this->downloadable as $key )  {
                fputcsv($os, $key);
            }
            fclose($os);
        }

    private $sodic_query;
    private $final_array;
    private $init_array;
    private $table;
    private $downloadable;
    
    

}


new OrdersDownloader($_GET["query_infos"]);