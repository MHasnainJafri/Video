<?php
    if(isset($_SESSION[PRE_FIX . 'id'])) 
    {
        
        $url = $baseurl . 'showRequests';
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
                        <div class="page-header-text">Manage Request</div>
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
                                                        <th scope="col">Vehicle</th>
                                                        <th scope="col">Request</th>
                                                        <th scope="col">Status</th>
                                                        <th scope="col">Last Location</th>
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
                                                                    $currentRow=$countRow++;
                                                                   
                                                                    ?>
                                                                        <tr id="<?php echo $singleRow['Request']['id']; ?>">
                                                                            <th scope="row">
                                                                                <div class="td-container heightFix tabel-img-container">
                                                                                    <input type="checkbox" class="ticketCheckbox" value="<?php echo $singleRow['Request']['id']; ?>">
                                                                                </div>
                                                                            </th>
                                                                            
                                                                            <td title="<?php echo date("d M Y h:i:s",strtotime($singleRow['Request']['created']));?>">
                                                                                <div class="td-Allocated heightFix">
                                                                                    <?php echo $singleRow['Request']['id']; ?>
                                                                                    <div class="tdSubTitle">
                                                                                        <?php
                                                                                            $timestamp = strtotime($singleRow['Request']['created']);
                                                                                            echo date("d M Y", $timestamp);
                                                                                        ?>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-Allocated heightFix" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                    <?php echo ucwords(strtolower($singleRow['Car']['User']['UserInfo']['first_name']." ". $singleRow['Car']['User']['UserInfo']['last_name'])); ?>
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
                                                                                                <?php echo $singleRow['Car']['User']['UserInfo']['phone_no_1']; ?>
                                                                                            </p>
                                                                                            <?php
                                                                                                if($singleRow['Car']['User']['UserInfo']['phone_no_2']!="0")
                                                                                                {
                                                                                                    ?>
                                                                                                        <p>
                                                                                                            <a href="#">
                                                                                                                <?php echo $singleRow['Car']['User']['UserInfo']['phone_no_2']; ?>
                                                                                                            </a>
                                                                                                        </p>
                                                                                                    <?php
                                                                                                }
                                                                                                
                                                                                                if($singleRow['Car']['User']['UserInfo']['phone_no_3']!="0")
                                                                                                {
                                                                                                    ?>
                                                                                                        <p>
                                                                                                            <a href="#">
                                                                                                                <?php echo $singleRow['User']['UserInfo']['phone_no_3']; ?>
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
                                                                                <div class="td-Allocated heightFix">
                                                                                    <?php 
                                                                                        $requestName=str_replace("_"," ",$singleRow['RequestCategory']['name']);
                                                                                        echo ucwords(strtolower($requestName)); 
                                                                                    ?>
                                                                                    
                                                                                    <div class="tdSubTitle">
                                                                                        
                                                                                        <?php
                                                                                            echo ucwords(strtolower($singleRow['Request']['value'])); 
                                                                                        ?>
                                                                                    </div>
                                                                                    
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php 
                                                                                        if($singleRow['Request']['status']=="0")
                                                                                        {
                                                                                            echo "<span style='color:orange;'>Pending</span>";
                                                                                        }
                                                                                        else
                                                                                        if($singleRow['Request']['status']=="1")
                                                                                        {
                                                                                            echo "Approved";
                                                                                        }
                                                                                        
                                                                                    ?>
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
                                                                                            if($_SESSION[PRE_FIX.'id']=="1" && $singleRow['Request']['status']=="0")
                                                                                            {
                                                                                                ?>
                                                                                                    <span class="approveRequest updateCarRequest" data-status="1" data-id="<?php echo $singleRow['Request']['id']; ?>" style="padding: 20px;">
                                                                                                        <i title="Approve" class="fas fa-check" style="color: #90908f;cursor: pointer;" ></i>
                                                                                                    </span>
                                                                                                <?php
                                                                                            }
                                                                                            else
                                                                                            {
                                                                                                ?>
                                                                                                    <span style="padding: 20px;">
                                                                                                        <i title='Approve' class='fas fa-check-double' style='color:green;cursor: pointer;'></i>
                                                                                                    </span>
                                                                                                <?php
                                                                                            }
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