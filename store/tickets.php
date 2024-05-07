<?php
    if(isset($_SESSION[PRE_FIX . 'id'])) 
    {
        
        $url = $baseurl . 'showComplainCategoriesWithComplains';
        $data = array();
        $json_data = @curl_request($data, $url);
        $json_data = $json_data['msg'];
       
        $all=0;
        foreach ($json_data as $singleRowCount) 
        {
            $all+=count($singleRowCount['Complain']);
        }
       
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
                        <div class="page-header-text">Manage Tickets</div>
                    </div>
                    <div class="dashboard-detail-content-container">
                        
                        <div class="order-tabel-container">
                            <div class="content-tabel-container">
                                <div class="content-tabel-nav">
                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                       
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#tab_all" role="tab" aria-controls="profile" aria-selected="true">All (<?php echo $all; ?>)</a>
                                        </li>
                                        <?php
                                            if(is_array($json_data) || is_object($json_data)) 
                                            {
                                                $countRow=1;
                                                foreach ($json_data as $singleRow) 
                                                {
                                                    if(count($singleRow['Complain']))
                                                    {
                                                        $currentRow=$countRow++;
                                                        ?>
                                                            <li class="nav-item" role="presentation">
                                                                <a class="nav-link " id="profile-tab" data-toggle="tab" href="#tab_<?php echo $singleRow['ComplainCategory']['id']; ?>" role="tab" aria-controls="profile" aria-selected="true"><?php echo $singleRow['ComplainCategory']['title']; ?> (<?php echo count($singleRow['Complain']); ?>) </a>
                                                            </li>
                                                        <?php         
                                                    }
                                                }
                                            }
                                       ?>
                                       
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
                                                        <th scope="col">Subject</th>
                                                        <th scope="col">Category</th>
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
                                                                if(count($singleRow['Complain']))
                                                                {
                                                                    $currentRow=$countRow++;
                                                                   
                                                                    if(is_array($singleRow['Complain']) || is_object($singleRow['Complain'])) 
                                                                    {
                                                                        
                                                                        foreach ($singleRow['Complain'] as $singleRow1) 
                                                                        {
                                                                            ?>
                                                                                <tr id="<?php echo $singleRow1['id']; ?>">
                                                                                    <th scope="row">
                                                                                        <div class="td-container heightFix tabel-img-container">
                                                                                            <input type="checkbox" class="ticketCheckbox" value="<?php echo $singleRow1['id']; ?>">
                                                                                        </div>
                                                                                    </th>
                                                                                    
                                                                                    <td title="<?php echo date("d M Y h:i:s",strtotime($singleRow1['created']));?>">
                                                                                        <div class="td-Allocated heightFix">
                                                                                            <?php echo $singleRow1['id']; ?>
                                                                                            <div class="tdSubTitle">
                                                                                                <?php
                                                                                                    $timestamp = strtotime($singleRow1['created']);
                                                                                                    echo date("d M Y", $timestamp);
                                                                                                ?>
                                                                                            </div>
                                                                                        </div>
                                                                                    </td>
                                                                                    
                                                                                    <td>
                                                                                        <div class="td-Allocated heightFix" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                            <?php echo ucwords(strtolower($singleRow1['User']['UserInfo']['first_name']." ". $singleRow1['User']['UserInfo']['last_name'])); ?>
                                                                                            <i class="fas fa-caret-down downCaret"></i>
                                                                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" >
                                                                                                <div class="customer-details">
                                                                                                    <h6>
                                                                                                        <span class="fas fa-hashtag" style="color: rgb(170, 170, 170);"></span>
                                                                                                        <?php echo $singleRow1['Car']['id']; ?>    
                                                                                                    </h6>
                                                                                                    <p>
                                                                                                        <span class="fas fa-sim-card" style="color: rgb(170, 170, 170);"></span>
                                                                                                        <?php echo $singleRow1['Car']['sim']; ?> 
                                                                                                    </p>
                                                                                                    
                                                                                                    <br>
                                                                                                    <p>
                                                                                                        <?php echo ucwords(strtolower($singleRow1['Car']['brand']." ". $singleRow1['Car']['model'])); ?> 
                                                                                                    </p>
                                                                                                    <p>
                                                                                                        <?php echo $singleRow1['Car']['city']; ?>
                                                                                                    </p>
                                                                                                    
                                                                                                    <p>
                                                                                                        <i class="fas fa-user" style="color: rgb(170, 170, 170);"></i>
                                                                                                        <?php echo $singleRow1['User']['UserInfo']['phone_no_1']; ?>
                                                                                                    </p>
                                                                                                    <?php
                                                                                                        if($singleRow1['User']['UserInfo']['phone_no_2']!="0")
                                                                                                        {
                                                                                                            ?>
                                                                                                                <p>
                                                                                                                    <a href="#">
                                                                                                                        <?php echo $singleRow1['User']['UserInfo']['phone_no_2']; ?>
                                                                                                                    </a>
                                                                                                                </p>
                                                                                                            <?php
                                                                                                        }
                                                                                                        
                                                                                                        if($singleRow1['User']['UserInfo']['phone_no_3']!="0")
                                                                                                        {
                                                                                                            ?>
                                                                                                                <p>
                                                                                                                    <a href="#">
                                                                                                                        <?php echo $singleRow1['User']['UserInfo']['phone_no_3']; ?>
                                                                                                                    </a>
                                                                                                                </p>
                                                                                                            <?php
                                                                                                        }
                                                                                                    ?>
                                                                                                    
                                                                                                    
                                                                                                    
                                                                                                    
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="tdSubTitle">
                                                                                                <?php echo $singleRow1['Car']['registration_no']; ?>
                                                                                            </div>
                                                                                        </div>
                                                                                    </td>
                                                                                    
                                                                                    <td title="<?php echo ucwords(strtolower($singleRow1['complaint'])); ?>">
                                                                                        <div class="td-Allocated heightFix">
                                                                                            <?php 
                                                                                                $subject=ucwords(strtolower($singleRow1['complaint'])); 
                                                                                                echo mb_strimwidth($subject, 0, 20, '...');
                                                                                            ?>
                                                                                            
                                                                                            <div class="tdSubTitle">
                                                                                                
                                                                                                <?php
                                                                                                    if($singleRow1['tag']!="")
                                                                                                    {
                                                                                                        ?>
                                                                                                            <i class="fa fa-tag" aria-hidden="true"></i>
                                                                                                        <?php
                                                                                                        echo $singleRow1['tag'];
                                                                                                    }
                                                                                                ?>
                                                                                            </div>
                                                                                            
                                                                                        </div>
                                                                                    </td>
                                                                                    
                                                                                    <td>
                                                                                        <div class="td-container heightFix">
                                                                                            <?php 
                                                                                                echo $singleRow['ComplainCategory']['title'];
                                                                                            ?>
                                                                                        </div>
                                                                                    </td>
                                                                                    
                                                                                    <td>
                                                                                        <div class="td-container heightFix">
                                                                                            <?php 
                                                                                                if($singleRow1['status']=="0")
                                                                                                {
                                                                                                    echo "<span style='color:orange;'>Pending</span>";
                                                                                                }
                                                                                                else
                                                                                                if($singleRow1['status']=="1")
                                                                                                {
                                                                                                    echo "Working";
                                                                                                }
                                                                                                else
                                                                                                if($singleRow1['status']=="2")
                                                                                                {
                                                                                                    echo "Pending Feedback";
                                                                                                }
                                                                                                else
                                                                                                if($singleRow1['status']=="3")
                                                                                                {
                                                                                                    echo "<span style='color:red;'>Closed</span>";
                                                                                                }
                                                                                                else
                                                                                                if($singleRow1['status']=="4")
                                                                                                {
                                                                                                    echo "Completed";
                                                                                                }
                                                                                                else
                                                                                                if($singleRow1['status']=="5")
                                                                                                {
                                                                                                    echo "<span style='color:#910aff;'>Pending HQ</span>";
                                                                                                }
                                                                                                
                                                                                                
                                                                                            ?>
                                                                                        </div>
                                                                                    </td>
                                                                                    
                                                                                    <td>
                                                                                        <div class="td-container heightFix">
                                                                                            <?php
                                                                                                $timestamp=@$singleRow1['Car']['updated'];
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
                                                                                                
                                                                                                if(count($singleRow1['UserAdmin']))
                                                                                                {
                                                                                                    echo ucwords(strtolower($singleRow1['UserAdmin']['first_name']." ". $singleRow1['UserAdmin']['last_name']));         
                                                                                                }
                                                                                                else
                                                                                                {
                                                                                                    echo "N/A";
                                                                                                }
                                                                                            ?>
                                                                                        </div>
                                                                                    </td>
                                                                                    
                                                                                    <td class="showTickets" style="cursor:pointer;" data-id="<?php echo $singleRow1['id'];?>">
                                                                                        <div class="td-container heightFix">
                                                                                            <div class="dropdown">
                                                                                                <i title="View Detail" class="fas fa-info-circle" style="color: #90908f;cursor: pointer;" ></i>
                                                                                            </div>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            <?php
                                                                        }
                                                                    
                                                                    }
                                                                }
                                                            }
                                                        }
                                                        
                                                    ?>
                                                    
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>   
                                                        
                                                    <?php
                                                    
                                                        if(is_array($json_data) || is_object($json_data)) 
                                                        {
                                                            $countRow=1;
                                                            foreach($json_data as $singleRow) 
                                                            {
                                                                if(count($singleRow['Complain']))
                                                                {
                                                                    $currentRow=$countRow++;
                                                                    ?>
                                                                        <div class="tab-pane fade" id="tab_<?php echo $singleRow['ComplainCategory']['id']; ?>" role="tabpanel" aria-labelledby="home-tab">
                                                                            <div class="order-tabel-container">
                                                                                <table class="table table-hover">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th scope="col">
                                                                                                <input type="checkbox">
                                                                                            </th>
                                                                                            <th scope="col">ID</th> 
                                                                                            <th scope="col">Vehicle</th>
                                                                                            <th scope="col">Subject</th>
                                                                                            <th scope="col">Category</th>
                                                                                            <th scope="col">Status</th>
                                                                                            <th scope="col">Last Location</th>
                                                                                            <th scope="col">Created By</th>
                                                                                            <th scope="col">Action</th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                    <?php
                                                                                        if(is_array($singleRow['Complain']) || is_object($singleRow['Complain'])) 
                                                                                        {
                                                                                            
                                                                                            foreach ($singleRow['Complain'] as $singleRow1) 
                                                                                            {
                                                                                                ?>
                                                                                                    <tr id="<?php echo $singleRow1['id']; ?>">
                                                                                                        <th scope="row">
                                                                                                            <div class="td-container heightFix tabel-img-container">
                                                                                                                <input type="checkbox" class="ticketCheckbox" value="<?php echo $singleRow1['id']; ?>">
                                                                                                            </div>
                                                                                                        </th>
                                                                                                        
                                                                                                        <td title="<?php echo date("d M Y h:i:s",strtotime($singleRow1['created']));?>">
                                                                                                            <div class="td-Allocated heightFix">
                                                                                                                <?php echo $singleRow1['id']; ?>
                                                                                                                <div class="tdSubTitle">
                                                                                                                    <?php
                                                                                                                        $timestamp = strtotime($singleRow1['created']);
                                                                                                                        echo date("d M Y", $timestamp);
                                                                                                                    ?>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </td>
                                                                                                        
                                                                                                        <td>
                                                                                                            <div class="td-Allocated heightFix" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                                                <?php echo ucwords(strtolower($singleRow1['User']['UserInfo']['first_name']." ". $singleRow1['User']['UserInfo']['last_name'])); ?>
                                                                                                                <i class="fas fa-caret-down downCaret"></i>
                                                                                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" >
                                                                                                                    <div class="customer-details">
                                                                                                                        <h6>
                                                                                                                            <span class="fas fa-hashtag" style="color: rgb(170, 170, 170);"></span>
                                                                                                                            <?php echo $singleRow1['Car']['id']; ?>    
                                                                                                                        </h6>
                                                                                                                        <p>
                                                                                                                            <span class="fas fa-sim-card" style="color: rgb(170, 170, 170);"></span>
                                                                                                                            <?php echo $singleRow1['Car']['sim']; ?> 
                                                                                                                        </p>
                                                                                                                        
                                                                                                                        <br>
                                                                                                                        <p>
                                                                                                                            <?php echo ucwords(strtolower($singleRow1['Car']['brand']." ". $singleRow1['Car']['model'])); ?> 
                                                                                                                        </p>
                                                                                                                        <p>
                                                                                                                            <?php echo $singleRow1['Car']['city']; ?>
                                                                                                                        </p>
                                                                                                                        
                                                                                                                        <p>
                                                                                                                            <i class="fas fa-user" style="color: rgb(170, 170, 170);"></i>
                                                                                                                            <?php echo $singleRow1['User']['UserInfo']['phone_no_1']; ?>
                                                                                                                        </p>
                                                                                                                        <?php
                                                                                                                            if($singleRow1['User']['UserInfo']['phone_no_2']!="0")
                                                                                                                            {
                                                                                                                                ?>
                                                                                                                                    <p>
                                                                                                                                        <a href="#">
                                                                                                                                            <?php echo $singleRow1['User']['UserInfo']['phone_no_2']; ?>
                                                                                                                                        </a>
                                                                                                                                    </p>
                                                                                                                                <?php
                                                                                                                            }
                                                                                                                            
                                                                                                                            if($singleRow1['User']['UserInfo']['phone_no_3']!="0")
                                                                                                                            {
                                                                                                                                ?>
                                                                                                                                    <p>
                                                                                                                                        <a href="#">
                                                                                                                                            <?php echo $singleRow1['User']['UserInfo']['phone_no_3']; ?>
                                                                                                                                        </a>
                                                                                                                                    </p>
                                                                                                                                <?php
                                                                                                                            }
                                                                                                                        ?>
                                                                                                                        
                                                                                                                        
                                                                                                                        
                                                                                                                        
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                                <div class="tdSubTitle">
                                                                                                                    <?php echo $singleRow1['Car']['registration_no']; ?>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </td>
                                                                                                        
                                                                                                        <td title="<?php echo ucwords(strtolower($singleRow1['complaint'])); ?>">
                                                                                                            <div class="td-Allocated heightFix">
                                                                                                                <?php 
                                                                                                                    $subject=ucwords(strtolower($singleRow1['complaint'])); 
                                                                                                                    echo mb_strimwidth($subject, 0, 20, '...');
                                                                                                                ?>
                                                                                                                
                                                                                                                <div class="tdSubTitle">
                                                                                                                    
                                                                                                                    <?php
                                                                                                                        if($singleRow1['tag']!="")
                                                                                                                        {
                                                                                                                            ?>
                                                                                                                                <i class="fa fa-tag" aria-hidden="true"></i>
                                                                                                                            <?php
                                                                                                                            echo $singleRow1['tag'];
                                                                                                                        }
                                                                                                                    ?>
                                                                                                                </div>
                                                                                                                
                                                                                                            </div>
                                                                                                        </td>
                                                                                                        
                                                                                                        <td>
                                                                                                            <div class="td-container heightFix">
                                                                                                                <?php 
                                                                                                                    echo $singleRow['ComplainCategory']['title'];
                                                                                                                ?>
                                                                                                            </div>
                                                                                                        </td>
                                                                                                        
                                                                                                        <td>
                                                                                                            <div class="td-container heightFix">
                                                                                                                <?php 
                                                                                                                    if($singleRow1['status']=="0")
                                                                                                                    {
                                                                                                                        echo "<span style='color:orange;'>Pending</span>";
                                                                                                                    }
                                                                                                                    else
                                                                                                                    if($singleRow1['status']=="1")
                                                                                                                    {
                                                                                                                        echo "Working";
                                                                                                                    }
                                                                                                                    else
                                                                                                                    if($singleRow1['status']=="2")
                                                                                                                    {
                                                                                                                        echo "Pending Feedback";
                                                                                                                    }
                                                                                                                    else
                                                                                                                    if($singleRow1['status']=="3")
                                                                                                                    {
                                                                                                                        echo "<span style='color:red;'>Closed</span>";
                                                                                                                    }
                                                                                                                    else
                                                                                                                    if($singleRow1['status']=="4")
                                                                                                                    {
                                                                                                                        echo "Completed";
                                                                                                                    }
                                                                                                                    else
                                                                                                                    if($singleRow1['status']=="5")
                                                                                                                    {
                                                                                                                        echo "<span style='color:#910aff;'>Pending HQ</span>";
                                                                                                                    }
                                                                                                                    
                                                                                                                    
                                                                                                                ?>
                                                                                                            </div>
                                                                                                        </td>
                                                                                                        
                                                                                                        <td>
                                                                                                            <div class="td-container heightFix">
                                                                                                                <?php
                                                                                                                    $timestamp=@$singleRow1['Car']['updated'];
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
                                                                                                                    
                                                                                                                    if(count($singleRow1['UserAdmin']))
                                                                                                                    {
                                                                                                                        echo ucwords(strtolower($singleRow1['UserAdmin']['first_name']." ". $singleRow1['UserAdmin']['last_name']));         
                                                                                                                    }
                                                                                                                    else
                                                                                                                    {
                                                                                                                        echo "N/A";
                                                                                                                    }
                                                                                                                ?>
                                                                                                            </div>
                                                                                                        </td>
                                                                                                        
                                                                                                        <td class="showTickets" style="cursor:pointer;" data-id="<?php echo $singleRow1['id'];?>">
                                                                                                            <div class="td-container heightFix">
                                                                                                                <div class="dropdown">
                                                                                                                    <i title="View Detail" class="fas fa-info-circle" style="color: #90908f;cursor: pointer;" ></i>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                <?php
                                                                                            }
                                                                                        
                                                                                        }
                                                                                    ?>    
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    <?php        
                                                                }
                                                            }
                                                        }
                                                   ?>
                                    
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