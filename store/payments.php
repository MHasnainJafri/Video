<?php
    if(isset($_SESSION[PRE_FIX . 'id'])) 
    {
        
        $url = $baseurl . 'showPaymentReceive';
        $data = array();
        $json_data = @curl_request($data, $url);
        $json_data = $json_data['msg'];
        
        ?>
            <style>
            .dropbtn {
              /*padding: 5px;*/
              border: none;
            }
            
            .dropdown {
              position: relative;
              display: inline-block;
            }
            
            .dropdown-content {
              display: none;
              position: absolute;
              background-color: #f1f1f1;
              min-width: 160px;
              z-index: 1;
            }
            
            .dropdown-content p {
              color: black;
              padding: 8px 16px;
              text-decoration: none;
              display: block;
              cursor:pointer;
              margin:0px;
            }
            
            .dropdown:hover .dropdown-content {display: block;}
            
            </style>
            
            <div class="main-content-container">
                <div class="main-content-container-wrap">
                    <div class="content-page-header">
                        <div class="page-header-text">Manage Payments</div>
                    </div>
                    <div class="dashboard-detail-content-container">
                        
                        <div class="order-tabel-container">
                            <div class="content-tabel-container">
                                <div class="content-tabel-nav">
                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#tab_all" role="tab" aria-controls="profile" aria-selected="true">All (<?php echo count($json_data); ?>)</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-content" id="myTabContent">
                                    
                                    <div class="tab-pane fade active show" id="tab_all" role="tabpanel" aria-labelledby="home-tab">
                                        <div class="order-tabel-container">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">
                                                            <input type="checkbox">
                                                        </th>
                                                        <th scope="col">ID</th> 
                                                        <th scope="col">TID</th>
                                                        <th scope="col">Amount</th>
                                                        <th scope="col">Adjustable Amount</th>
                                                        <th scope="col">Payment Date</th>
                                                        <th scope="col">Status</th>
                                                        <th scope="col">Invoices</th>
                                                        <th scope="col">Created By</th>
                                                        <th scope="col">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                    
                                                    <?php
                                                        
                                                        if(is_array($json_data) || is_object($json_data)) 
                                                        {
                                                            $countRow=1;
                                                            foreach($json_data as $singleRow) 
                                                            {
                                                                if(count($singleRow))
                                                                {
                                                                    $invoiceAmount="0";
                                                                    if(count($singleRow['Invoice'])!="0")
                                                                    {
                                                                        foreach ($singleRow['Invoice'] as $key=>$value)
                                                                        {
                                                                            $invoiceAmount+=$value['amount'];
                                                                        }
                                                                    }
                                                                    
                                                                    ?>
                                                                        <tr id="<?php echo $singleRow['PaymentReceive']['id']; ?>">
                                                                            <th scope="row">
                                                                                <div class="td-container heightFix tabel-img-container">
                                                                                    <input type="checkbox" class="ticketCheckbox" value="<?php echo $singleRow['PaymentReceive']['id']; ?>">
                                                                                </div>
                                                                            </th>
                                                                            
                                                                            <td title="<?php echo date("d M Y h:i:s",strtotime($singleRow['PaymentReceive']['created']));?>">
                                                                                <div class="td-Allocated heightFix">
                                                                                    <?php echo $singleRow['PaymentReceive']['id']; ?>
                                                                                    <div class="tdSubTitle">
                                                                                        <?php
                                                                                            $timestamp = strtotime($singleRow['PaymentReceive']['created']);
                                                                                            echo date("d M Y", $timestamp);
                                                                                        ?>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-Allocated heightFix">
                                                                                    #<?php 
                                                                                        echo ucwords(strtolower($singleRow['PaymentReceive']['tid'])); 
                                                                                    ?>
                                                                                    
                                                                                    <div class="tdSubTitle">
                                                                                        
                                                                                        <?php
                                                                                            echo ucwords(strtolower($singleRow['PaymentReceive']['source'])); 
                                                                                        ?>
                                                                                    </div>
                                                                                    
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php 
                                                                                        echo "Rs ".$singleRow['PaymentReceive']['amount'];
                                                                                    ?>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php 
                                                                                        $remaining_amount=$invoiceAmount-$singleRow['PaymentReceive']['amount'];
                                                                                        if($remaining_amount!="0")
                                                                                        {
                                                                                            ?>
                                                                                                <span style="color:red;">
                                                                                                    Rs<?php echo $remaining_amount; ?> 
                                                                                                </span>
                                                                                            <?php
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                            echo "Rs ".$remaining_amount;
                                                                                        }
                                                                                    ?>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php 
                                                                                        $payment_date = $singleRow['PaymentReceive']['date'];
                                                                                        echo date('d M Y', strtotime($payment_date));
                                                                                    ?>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php 
                                                                                        if($singleRow['PaymentReceive']['status']=="0")
                                                                                        {
                                                                                            echo "<span style='color:orange;'>Pending</span>";
                                                                                        }
                                                                                        else
                                                                                        if($singleRow['PaymentReceive']['status']=="1")
                                                                                        {
                                                                                            echo "Completed";
                                                                                        }
                                                                                    ?>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <button class="statusbtn" type="button">
                                                                                        <?php
                                                                                            echo count($singleRow['Invoice']);            
                                                                                        ?>
                                                                                        Invoices
                                                                                    </button>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php 
                                                                                        
                                                                                        if(count($singleRow['UserAdmin']))
                                                                                        {
                                                                                            echo ucwords(strtolower($singleRow['UserAdmin']['first_name']." ". $singleRow['UserAdmin']['last_name']));         
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                            echo "N/A";
                                                                                        }
                                                                                    ?>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td style="cursor:pointer;" data-id="<?php echo $singleRow['id'];?>">
                                                                                <div class="td-container heightFix">
                                                                                    <div class="dropdown">
                                                                                         
                                                                                        <?php
                                                                                            ?>
                                                                                                <span class="viewInvoices">
                                                                                                    <i title="View Invoices" class="fas fa-receipt" style="color: #90908f;cursor: pointer;" ></i>
                                                                                                </span>
                                                                                            <?php
                                                                                        ?>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    <?php
                                                                }
                                                            }
                                                        }
                                                        
                                                    ?>
                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>   
                                                  
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        <?php
    } 
    else 
    {
        echo "<script>window.location='index.php'</script>";
        die;
    }
?>