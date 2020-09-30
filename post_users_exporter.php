<?php
if (!($_SERVER["REQUEST_METHOD"] == "POST")) {
    echo "<h1 style='color:red; text-align:center; text-decoration:underline;'> This method is not supported </h1>";
    die;
}
header("Content-type: application/csv", true, 200);
header("Content-Disposition: attachment; filename=mrktUsers.csv");

require "./connection_trait.php";

class CSVExporter
{
    use \DB;

    private $inbonde;
    private $dbConnection;
    private $columns = ["ID","name", "email", "phone", "area", "Orders Count", "Total Revenue"];

    public function __construct($inbonde)
    {
        $this->setinbonde($inbonde);
        $this->dbConnection = $this->makeConnection();
    }

    private function setinbonde($inbonde)
    {
        $this->inbonde = $inbonde;
    }
    
    private function validate($data) :bool
    {
        $table = trim($data["user"]);
        if ($table != true) {
            throw new Exception("Not valid data provided");
        }
        return true;
    }

    private function retriveData() : Array
    {
        $preparedStatment = $this->dbConnection->prepare(
            "SELECT user.id, CONCAT(first_name,' ',last_name) as user_name, email,phone
            ,CONCAT(city.name,'-',area.name) as area
            ,count(user_id) as total_orders, TRUNCATE(sum(total_price), 2) as total_reve
            FROM orders
            Right JOIN user ON  orders.user_id  = user.id  
            JOIN area ON area.id = user.area_id
            JOIN city ON  city_id=city.id 
            group by user.id;"
        );
        
        $preparedStatment->execute();
        return $preparedStatment->fetchAll(PDO::FETCH_ASSOC);
    }

    public function export()
    {
        if ($this->validate($this->inbonde)) {
            $stream = fopen("php://output", 'w');
            fputcsv($stream, $this->columns);
            foreach ($this->retriveData() as $user) {
                fputcsv($stream, $user);
            }
            fclose($stream);
            $this->dbConnection = null;
        }
    }
}

$exproter = new CSVExporter($_POST);
$exproter->export();
