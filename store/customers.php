<?php
    if(isset($_SESSION[PRE_FIX . 'id'])) 
    {
        
        $url = $baseurl . 'showUsers';
        $data = array(
            "starting_point" => "0"
        );
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
                        <div class="page-header-text">Customers</div>
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
                                                        <th scope="col">Customer</th>
                                                        <th scope="col">Vehicles</th>
                                                        <th scope="col">-</th>
                                                        <th scope="col">Portal</th>
                                                        <th scope="col">Last Seen</th>
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
                                                                        <tr id="<?php echo $singleRow['User']['id']; ?>">
                                                                            <th scope="row">
                                                                                <div class="td-container heightFix tabel-img-container">
                                                                                    <input type="checkbox" class="ticketCheckbox" value="<?php echo $singleRow['User']['id']; ?>">
                                                                                </div>
                                                                            </th>
                                                                            
                                                                            <td title="<?php echo date("d M Y h:i:s",strtotime($singleRow['User']['created']));?>">
                                                                                <div class="td-Allocated heightFix">
                                                                                    <?php echo $singleRow['User']['id']; ?>
                                                                                    <div class="tdSubTitle">
                                                                                        <?php
                                                                                            $timestamp = strtotime($singleRow['User']['created']);
                                                                                            echo date("d M Y", $timestamp);
                                                                                        ?>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-Allocated heightFix" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                    <?php echo ucwords(strtolower($singleRow['UserInfo']['first_name']." ". $singleRow['UserInfo']['last_name'])); ?>
                                                                                    <i class="fas fa-caret-down downCaret"></i>
                                                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" >
                                                                                        <div class="customer-details">
                                                                                            <p>
                                                                                                <?php echo $singleRow['UserInfo']['address']; ?>
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
                                                                                        <?php echo $singleRow['UserInfo']['phone_no_1']; ?>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php
                                                                                        if($singleRow['User']['contacts']!="0")
                                                                                        {
                                                                                            ?>
                                                                                                <button class="statusbtn" type="button"><?php //echo $singleRow['User']['contacts']; ?></button>
                                                                                            <?php
                                                                                        }
                                                                                    ?>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    
                                                                                    <?php
                                                                                        if($singleRow['User']['contacts']!="0")
                                                                                        {
                                                                                            ?>
                                                                                                <button class="statusbtn" type="button"><?php //echo $singleRow['User']['contacts']; ?></button>
                                                                                            <?php
                                                                                        }
                                                                                    ?>
                                                                                    
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    
                                                                                    <?php
                                                                                        if($singleRow['User']['portal']="1")
                                                                                        {
                                                                                            ?>
                                                                                                <button class="statusbtn" type="button">Active</button>
                                                                                            <?php
                                                                                        }
                                                                                    ?>
                                                                                    
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php 
                                                                                        echo time_elapsed_string($singleRow['UserInfo']['last_seen']); 
                                                                                    ?>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            
                                                                            
                                                                            <td style="cursor:pointer;">
                                                                                <div class="td-container heightFix">
                                                                                    <div class="dropdown">
                                                                                        
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