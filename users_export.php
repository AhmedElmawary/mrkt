<?php 
    
require_once("includes/config.php");

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=users.csv');

class Downloader{

    private $sodic_query;
    private $array_of_values;
    private $table;
    private $area;
    private $city;
    private $query;
    static $result;
    private $conn;
    
    function __construct(String $query_infos){
        $this->sodic_query= false ;
        $this->city= "%" ;
        $this->area= "%" ;

        $this->to_array($query_infos);
        $this->assigner($this->array_of_values);
        $this->query_runner();

    }
    public /*void*/ function query_runner() :void {
$this->conn = get_db_con();
        $this->query= "
        SELECT user.id AS user_id,first_name, last_name,email,phone,created_at,active, 
        area_id,area.name AS area_name, city.name AS city_name
        FROM `{$this->table}`
        JOIN area ON area.id=user.area_id
        JOIN city ON area.city_id=city.id 
        WHERE city.id=3 AND user.area_id like '". $this->area ."' ORDER BY area.name,first_name,last_name";
    $result =  mysqli_query($this->conn,$this->query);
            $cols=
            ["user_id","first_name","last_name","email","phone","created_at","active", "area_id", "area_name","city_name"];    
        
    self::$result[]= $cols;

    while ( $col = mysqli_fetch_row($result) ){
        self::$result[]= $col;    
};

    }
    public /*void*/ function assigner(/**String[] */Array $array) :void {
        foreach ($array as $value){
                if ( $value === "SODIC"){
                    $this->sodic_query= true ;
                    $this->city= 3 ;
                    continue;
                }
               if ( (int) $value ===  0 ) 
       {
                if ( $value === "%") $this->area =  $value;else
            {
                if (  $value ===  "users")  
                $this->table = "user";
else
                 $this->table = $value;
            }

        }else $this->area =  $value;

            }
    }
  public /*void*/ function to_array(String $string) :void {$this->array_of_values = explode(" ", $string);    }
  public /*void*/ function is_sodic(String $string) :void {/**boolean */ $sodic =  false;    if ($sodic !== false ) $this->sodic_query = true ; else $this->sodic_query = false; }}






if ($_SERVER["REQUEST_METHOD"] === "GET"){new Downloader($_GET["query_infos"]);}



$fp =  fopen('php://output', 'w');


foreach (Downloader::$result as  $key => $value) {
   
    fputcsv($fp,$value );
}

fclose($fp);