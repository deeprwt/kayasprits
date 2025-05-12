<?php
class DB
     {
        ///Declaration of variables
        var $host="localhost";
        var $user="kayasmu3_kayaspirits"; 
        var $pwd="kayaspirits1@#";
        var $persist=false;
        var $database="kayasmu3_kayaspiritsdb";

        var $conn=NULL;
        var $result=false;
        var $fields;
        var $check_fields;
        var $tbname;
        var $addNewFlag=false;
        ///End
		    

        function DB($host="",$user="",$pwd="",$dbname="",$open=true)
        {
         if($host!="")
            $this->host=$host;
         if($user!="")
            $this->user=$user;
         if($pwd!="")
            $this->pwd=$pwd;
         if($dbname!="")
            $this->database=$dbname;

         if($open)
           $this->open();
        }
        function open($host="",$user="",$pwd="",$dbname="")
        {
         if($host!="")
            $this->host=$host;
         if($user!="")
            $this->user=$user;
         if($pwd!="")
            $this->pwd=$pwd;
         if($dbname!="")
            $this->database=$dbname;

         $this->connect();
         $this->select_db();
        }
        function set_host($host,$user,$pwd,$dbname)
        {
         $this->host=$host;
         $this->user=$user;
         $this->pwd=$pwd;
         $this->database=$dbname;
        }
        function affectedRows() //-- Get number of affected rows in previous operation
        {
         return @mysqli_affected_rows($this->conn);
        }
        function close()//Close a connection to a  Server
        {
         return @mysqli_close($this->conn);
        }
        function connect() //Open a connection to a Server
        {
          // Choose the appropriate connect function
          if ($this->persist)
              $func = 'mysqli_pconnect';
          else
              $func = 'mysqli_connect';

          // Connect to the database server
          $this->conn = $func($this->host, $this->user, $this->pwd);
          if(!$this->conn)
             return false;

        }
        function select_db($dbname="") //Select a databse
        {
          if($dbname=="")
             $dbname=$this->database;
          mysqli_select_db($this->conn,$dbname);
        }
        function create_db($dbname) //Create a database
        {
          return @mysqli_create_db($this->conn,$dbname);
        }
        function drop_db($dbname) //Drop a database
        {
         return @mysqli_drop_db($this->conn,$dbname);
        }
        function data_seek($row) ///Move internal result pointer
        {
         return mysqli_data_seek($this->result,$row);
        }
        function error() //Get last error
        {
            return (mysqli_error());
        }
        function errorno() //Get error number
        {
            return mysqli_errno();
        }
        function query($sql = '') //Execute the sql query
        {
            $this->result = @mysqli_query($this->conn,$sql);
            return ($this->result != false);
        }
        function numRows() //Return number of rows in selected table
        {
            return (@mysqli_num_rows($this->result)); 
        }
        function fieldName($field)
        {
           return (@mysqli_field_name($this->result,$field));
        }
        function fieldColumns() {
           return (@mysqli_num_fields($this->result));
        }
        function insertID()
        {
            return (@mysqli_insert_id($this->conn));
        }
        function fetchObject()
        {
            return (@mysqli_fetch_object($this->result, MYSQL_BOTH));
        }
        function fetchArray()
        {
            return (@mysqli_fetch_array($this->result));
        }
                function fetchRow()
        {
            return (@mysqli_fetch_row($this->result));
        }
        function fetchAssoc()
        {
            return (@mysqli_fetch_assoc($this->result));
        }
        function freeResult()
        {
            return (@mysqli_free_result($this->result));
        }


     }


$db = new DB(); #######USE THIS CONNECTION INTO THE APPLICATION.
?>