<?php
    
    $res = $_REQUEST;
    // print_r($res);

    if(!empty($res)){
        echo 'Yes';
        $responsecode = $res['Response_Code']; 
        $uniquerefnumber = $res['Unique_Ref_Number']; 
        $servicetaxamount = $res['Service_Tax_Amount']; 
        $processingfee = $res['Processing_Fee_Amount']; 
        $totalamount = $res['Total_Amount']; 
        $transactionamount = $res['Transaction_Amount'];
        $transactiondate =$res['Transaction_Date'];
        $interchangevalue = $res['Interchange_Value'];
        $tdr = $res['TDR'];
        $paymode = $res['Payment_Mode']; 
        $submerchantid = $res['SubMerchantId']; 
        $referenceno = $res['ReferenceNo']; 
        $id = $res['ID']; 
        $tps = $res['TPS']; 
        $hrs = $res['RS'];
        $mand_field = $res['mandatory_fields'];
        $opt_field = $res['optional_fields'];
        $rsv = $res['RSV'];
        
        // ---------------------------------------------------------------------
        // Database Connection
        
        $servername = "localhost";
        $username = "kayasmu3_eazyuse";
        $password = 'f(&oat{)E3$c';
        $dbname = "kayasmu3_eazypay";
        
        // Create connection
        $conn = mysqli_connect($servername, $username, $password, $dbname);
        
        // Check connection
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        
        $sql = "SELECT id, email, ReferenceNo FROM pr_eazypay_data WHERE ReferenceNo = '$referenceno'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            
            $row = mysqli_fetch_assoc($result);
            
            $db_ref_no = $row['ReferenceNo'];
            $updateid = $row['id'];
            // print_r($db_ref_no);
            if($responsecode != "" && $referenceno == $db_ref_no){
                $sql = "UPDATE pr_eazypay_data 
                SET
                response_code = '$responsecode',
                unique_ref_number = '$uniquerefnumber', 
                service_tax_amount = '$servicetaxamount', 
                processing_fee_amount = '$processingfee', 
                total_amount = '$totalamount', 
                transaction_amount = '$transactionamount', 
                transaction_date = '$transactiondate', 
                interchange_value = '$interchangevalue', 
                TDR = '$tdr', 
                payment_mode = '$paymode', 
                SubMerchantId = '$submerchantid',
                merchant_id = '$id',
                RS = '$hrs', 
                TPS = '$tps', 
                mandatory_fields = '$mand_field', 
                optional_fields = '$opt_field', 
                RSV = '$rsv' WHERE id='$updateid'";
                
                if (mysqli_query($conn, $sql)) {
                    //echo "New record created successfully";
                } else {
                    //echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                }
                
                mysqli_close($conn);
            }
        }
    }
    
        
?>