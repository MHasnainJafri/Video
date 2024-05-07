<?php
    if(isset($_SESSION[PRE_FIX . 'id'])) 
    {
        
        $url = $baseurl . 'showStoreOrders';
        $data = array(
            "user_id" => $_SESSION[PRE_FIX . 'id'],
            "starting_point" => "0",
            "type" => "all"
        );
        $json_data = @curl_request($data, $url);
        $json_data = $json_data['msg'];
        
        ?>
            <div class="main-content-container">
                <div class="main-content-container-wrap">
                    <div class="content-page-header">
                        <div class="page-header-text">Manage Orders</div>
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
                                                        <th scope="col">Order</th> 
                                                        <th scope="col">Date</th> 
                                                        <th scope="col">Customer</th>
                                                        <th scope="col">Amount</th>
                                                        <th scope="col">Status</th>
                                                        <th scope="col">Items</th>
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
                                                                    
                                                                    ?>
                                                                        <tr id="<?php echo $singleRow['Order']['id']; ?>">
                                                                            
                                                                            <td>
                                                                                 <?php echo $singleRow['Order']['id']; ?>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <?php
                                                                                    $timestamp = strtotime($singleRow['Order']['created']);
                                                                                    echo date("d M Y", $timestamp);
                                                                                ?>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <?php 
                                                                                    echo ucwords(strtolower($singleRow['User']['first_name']." ".$singleRow['User']['last_name'])); 
                                                                                ?>
                                                                            </td>
                                                                            
                                                                            
                                                                            <td>
                                                                                <?php 
                                                                                    echo $singleRow['Order']['total'];
                                                                                ?>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <?php 
                                                                                    if($singleRow['Order']['status']=="0")
                                                                                    {
                                                                                        ?>
                                                                                            <button class="statusbtn grey" type="button">Pending</button>
                                                                                        <?php
                                                                                    }
                                                                                    else
                                                                                    if($singleRow['Order']['status']=="1")
                                                                                    {
                                                                                        ?>
                                                                                            <button class="statusbtn grey" type="button">Shipped</button>
                                                                                        <?php
                                                                                    }
                                                                                    else
                                                                                    if($singleRow['Order']['status']=="2")
                                                                                    {
                                                                                        ?>
                                                                                            <button class="statusbtn grey" type="button">Completed</button>
                                                                                        <?php
                                                                                    }
                                                                                    else
                                                                                    if($singleRow['Order']['status']=="3")
                                                                                    {
                                                                                        ?>
                                                                                            <button class="statusbtn doNothing" type="button">Cancel</button>
                                                                                        <?php
                                                                                    }
                                                                                ?>
                                                                            </td>
                                                                            
                                                                            
                                                                            <td>
                                                                                <?php 
                                                                                    echo count($singleRow['OrderProduct']);
                                                                                ?>
                                                                            </td>
                                                                            
                                                                            <td class="showOrderDetail" style="cursor:pointer;" data-id="<?php echo $singleRow['Order']['id']; ?>">
                                                                                <div class="dropdown">
                                                                                    <i title="View Detail" class="fas fa-info-circle" style="color: #90908f;cursor: pointer;"></i>
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