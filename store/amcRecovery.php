<?php
    if(isset($_SESSION[PRE_FIX . 'id'])) 
    {
        
        $url = $baseurl . 'showCarsWhoseBillingDateIsLessThenCurrentDate';
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
                        <div class="page-header-text">AMC Recovery</div>
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
                                                        <th scope="col">Vehicle</th>
                                                        <th scope="col">Installation Date</th>
                                                        <th scope="col">Billing Date</th>
                                                        <th scope="col">Plan</th>
                                                        <th scope="col">Last Location</th>
                                                        <th scope="col">Amount</th>
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
                                                                        <tr id="<?php echo $singleRow['Car']['id']; ?>">
                                                                            <th scope="row">
                                                                                <div class="td-container heightFix tabel-img-container">
                                                                                    <input type="checkbox" class="ticketCheckbox" value="<?php echo $singleRow['Car']['id']; ?>">
                                                                                </div>
                                                                            </th>
                                                                            
                                                                            <td>
                                                                                <div class="td-Allocated heightFix" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                    <?php echo ucwords(strtolower($singleRow['UserInfo']['first_name']." ". $singleRow['UserInfo']['last_name'])); ?>
                                                                                    <i class="fas fa-caret-down downCaret"></i>
                                                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" >
                                                                                        <div class="customer-details">
                                                                                            <h6>
                                                                                                <span class="fas fa-hashtag" style="color: rgb(170, 170, 170);"></span>
                                                                                                <?php echo $singleRow['Car']['id']; ?>    
                                                                                            </h6>
                                                                                            <p>
                                                                                                <span class="fas fa-sim-card" style="color: rgb(170, 170, 170);"></span>
                                                                                                <?php echo $singleRow['Car']['sim']; ?> 
                                                                                            </p>
                                                                                            
                                                                                            <br>
                                                                                            <p>
                                                                                                <?php echo ucwords(strtolower($singleRow['Car']['brand']." ". $singleRow['Car']['model'])); ?> 
                                                                                            </p>
                                                                                            <p>
                                                                                                <?php echo $singleRow['Car']['city']; ?>
                                                                                            </p>
                                                                                            
                                                                                            <p>
                                                                                                <i class="fas fa-user" style="color: rgb(170, 170, 170);"></i>
                                                                                                <?php echo $singleRow['UserInfo']['phone_no_1']; ?>
                                                                                            </p>
                                                                                            <?php
                                                                                                if($singleRow['UserInfo']['phone_no_2']!="0")
                                                                                                {
                                                                                                    ?>
                                                                                                        <p>
                                                                                                            <a href="#">
                                                                                                                <?php echo $singleRow['UserInfo']['phone_no_2']; ?>
                                                                                                            </a>
                                                                                                        </p>
                                                                                                    <?php
                                                                                                }
                                                                                                
                                                                                                if($singleRow['UserInfo']['phone_no_3']!="0")
                                                                                                {
                                                                                                    ?>
                                                                                                        <p>
                                                                                                            <a href="#">
                                                                                                                <?php echo $singleRow['UserInfo']['phone_no_3']; ?>
                                                                                                            </a>
                                                                                                        </p>
                                                                                                    <?php
                                                                                                }
                                                                                            ?>
                                                                                            
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="tdSubTitle">
                                                                                        <?php echo $singleRow['Car']['registration_no']; ?>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php 
                                                                                        $date_installation = $singleRow['Car']['installation_date'];
                                                                                        echo date('d M Y', strtotime($date_installation));
                                                                                    ?>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php 
                                                                                        $date_installation = $singleRow['Car']['billing_date'];
                                                                                        echo date('d M Y', strtotime($date_installation));
                                                                                    ?>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-Allocated heightFix">
                                                                                    <?php 
                                                                                        echo ucwords($singleRow['Car']['package_plan']);
                                                                                    ?>
                                                                                    <div class="tdSubTitle">
                                                                                        <?php 
                                                                                            echo ucwords($singleRow['Car']['billing_type']);
                                                                                        ?>
                                                                                    </div>
                                                                                </div>
                                                                            
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php
                                                                                        $timestamp=@$singleRow['Car']['updated'];
                                                                                        if (strpos($timestamp, '-') !== false)
                                                        			                    {
                                                        			                        $timedataa=time_elapsed_string($timestamp);
                                                                							$expld=explode(" ", $timedataa );
                                                                							
                                                                							if($expld[1] ==  "seconds" || $expld[1] ==  "second" )
                                                                							{
                                                                                                ?>
                                                                                                    <button class="statusbtn" type="button"><?php echo time_elapsed_string($timestamp); ?></button>
                                                                                                    <!--<span style="background:#1eae4d; color:white; padding: 1px 3px; border-radius: 2px; font-size: 11px;"><?php echo time_elapsed_string($timestamp); ?></span>-->
                                                                                                <?php
                                                                							}
                                                                							else
                                                                							if($expld[1] ==  "minutes" || $expld[1] ==  "minute" )
                                                                							{
                                                                							      ?>
                                                                							            <button class="statusbtn" style="background:orange !important; color:white;" type="button"><?php echo time_elapsed_string($timestamp); ?></button>
                                                                							      <?php
                                                                							}
                                                                							else
                                                                							if($expld[1] ==  "hours" || $expld[1] ==  "hour" ) 
                                                                							{
                                                                							      ?>
                                                                							            <button class="statusbtn" style="background:red !important; color:white;" type="button"><?php echo time_elapsed_string($timestamp); ?></button>
                                                                							      <?php
                                                                							}
                                                                							else
                                                                							if($expld[1] ==  "month" || $expld[1] ==  "week" || $expld[1] ==  "weeks" || $expld[1] ==  "months" || $expld[1] ==  "day" || $expld[1] ==  "days") 
                                                                							{
                                                                							      ?>
                                                        							                    <button class="statusbtn" type="button" style="background:#910aff !important; color:white;"><?php echo time_elapsed_string($timestamp); ?></button>
                                                                							      <?php
                                                                							}
                                                                							else
                                                                							{
                                                                							    ?>
                                                            							            <button class="statusbtn" style="background:#d0cfcf !important; color:white;" type="button"><?php echo time_elapsed_string($timestamp); ?></button>
                                                            							      <?php
                                                                							}
                                                        			                    }
                                                        			                    else
                                                        			                    {
                                                        			                        echo $timestamp;
                                                        			                    }
                                                                                    ?>
                                                                                    
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php 
                                                                                        echo "Rs ";
                                                                                        echo $singleRow['Car']['yearly']+$singleRow['Car']['service_fee'];
                                                                                    ?>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td style="cursor:pointer;" data-id="<?php echo $singleRow['id'];?>">
                                                                                <div class="td-container heightFix">
                                                                                    <div class="dropdown">
                                                                                         
                                                                                        <?php
                                                                                           $phone_no_1 = preg_replace('/0/', '92',$singleRow['UserInfo']['phone_no_1'],1);
                                                                                            $yearlyFee=$singleRow['Car']['yearly'];
                                                                                            $serviceFee=$singleRow['Car']['service_fee'];
                                                                                            $totalPrice=$yearlyFee+$serviceFee;
                                                                                            
                                                                                            $msg="Please pay the $totalPrice ".$singleRow['Car']['billing_type']." charges for your vehicle ".$singleRow['Car']['registration_no'];
                                                                                        ?>
                                                                                        
                                                                                        
                                                                                        <?php
                                                                                            if($singleRow['Car']['payment_block']=="0")
                                                                                            {
                                                                                                if($singleRow['Car']['payment_received']=="")
                                                                                                {
                                                                                                    ?>
                                                                                                        <span class="fas fa-exclamation-circle" style="color:red;" title="Payment Pending"></span>
                                                                                                    <?php
                                                                                                }
                                                                                                
                                                                                                if($singleRow['Car']['acc']=="ON")
                                                                                                {
                                                                                                    ?>
                                                                                                        <span class="fas fa-car-side" style="color:#1eae4d;" title="Ignition ON"></span>
                                                                                                    <?php
                                                                                                }
                                                                                                else
                                                                                                {
                                                                                                    ?>
                                                                                                        <span class="fas fa-car-side" style="color:#d0cfcf;" title="Ignition Off"></span>
                                                                                                    <?php
                                                                                                }
                                                                                                
                                                                                                
                                                                                                if($singleRow['Car']['ignition_on_off_enable']=="1")
                                                                                                {
                                                                                                    ?>
                                                                                                        <span class="fas fa-envelope" style="color:#1eae4d;" title="Ignition SMS ON"></span>
                                                                                                    <?php
                                                                                                }
                                                                                                else
                                                                                                {
                                                                                                    ?>
                                                                                                        <span class="fas fa-envelope" style="color:#d0cfcf;" title="Ignition SMS Off"></span>
                                                                                                    <?php
                                                                                                }
                                                                                                
                                                                                                ?>
                                                                                                    <span class="fas fa-mobile-alt" style="color:#1eae4d;" title="Mobile App Enable"></span>
                                                                                                <?php
                                                                                            }
                                                                                            else
                                                                                            {
                                                                                                ?>
                                                                                                    <span class="fas fa-lock" style="color:red; font-size: 12px;" title="Services Disabled"></span>
                                                                                                <?php
                                                                                            }
                                                                                            
                                                                                        ?>
                                                                                        
                                                                                        
                                                                                        
                                                                                        <a href="https://api.whatsapp.com/send/?phone=<?php echo $phone_no_1;?>&text=<?php echo $msg; ?>%0A%0AMeezan Bank%0AHamza%0A04310104666881%0A============ %0AEasyPaisa%0AHamza%0A0313-7370772%0A============ %0A%0AOnce you have made the payment, kindly share the screenshot here" style="color:green;" target="_blank">
                                                                                            <span class="fab fa-whatsapp" style="color:green;"></span>
                                                                                        </a>
                                                                                        
                                                                                        
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