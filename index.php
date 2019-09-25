<?php
class Stats
{
    private static $title;
    private static $age;
    private static $SignM;

    public static function show_statistics($param)
    {
        self::$title =  strtolower(substr($param, 0, strpos($param, "|")));
        self::$SignM  =  strtolower(substr($param, strpos($param, "age") + 3, 1));
        self::$age  =  (int) strtolower(substr($param, strpos($param, "age") + 4));
        self::print_result(self::$title, self::$SignM, self::$age);
    }

    private function print_result(string $title, string $SignM, int $age)
    {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "zadanie_php";

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $conn->query('SET NAMES utf8');
        $sql = "SELECT b.name, book_date, (
            select AVG(r2.age) from reviews r2 where r2.book_id = b.id and r2.sex = 'm'
                ) as male_avg, (
            select AVG(r3.age) from reviews r3 where r3.book_id = b.id and r3.sex = 'f'
                ) as female_avg
            from books b inner join reviews r on r.book_id = b.id WHERE r.age " . $SignM . " 30  and LOWER(b.name) = '" . $title . "' group by b.name";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            self::print_table($result, $title);
        } else {
            // var_dump("brak ks");
            $sql = "SELECT b.name, b.book_date, (
            select AVG(r2.age) from reviews r2 where r2.book_id = b.id and r2.sex = 'm'
                ) as male_avg, (
            select AVG(r3.age) from reviews r3 where r3.book_id = b.id and r3.sex = 'f'
                ) as female_avg
            from books b inner join reviews r on r.book_id = b.id where r.age " . $SignM . " 30 group by b.id";
            $result = $conn->query($sql);
            self::print_table($result, $title);
        }

        $conn->close();
    }
    private static function print_table($result, $title)
    {

        echo '<table border="1"><tr>
            <th>Book |</th>
            <th>Compatibility |</th>
            <th>Book Date |</th>
            <th>Male AVG age |</th>
            <th>Female AVG age</th>
            </tr>';

        while ($row = $result->fetch_assoc()) {
            similar_text(strtolower($row["name"]), strtolower($title), $percent);
            echo "<tr>";
            echo "<td>" . $row["name"] . "</td><td>" . round($percent, 2) . " %</td><td>" . $row["book_date"] . "</td><td>" . round($row["female_avg"]) . "</td>";
            echo "<td>" . round($row["male_avg"]) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
};


Stats::show_statistics('ZieLoNa MiLa|age>30');
echo ("</br>");
Stats::show_statistics('ZiElonA Droga|age<30');
