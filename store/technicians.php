<?php
    if(isset($_SESSION[PRE_FIX . 'id'])) 
    {
        
        $url = $baseurl . 'showMechanics';
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
                        <div class="page-header-text">Technicians</div>
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
                                                        <th scope="col">Name</th>
                                                        <th scope="col">Covered City</th>
                                                        <th scope="col">Installations</th>
                                                        <th scope="col">Wallet</th>
                                                        <th scope="col">Stock</th>
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
                                                                        <tr id="<?php echo $singleRow['Mechanic']['id']; ?>">
                                                                            <th scope="row">
                                                                                <div class="td-container heightFix tabel-img-container">
                                                                                    <input type="checkbox" class="ticketCheckbox" value="<?php echo $singleRow['PaymentReceive']['id']; ?>">
                                                                                </div>
                                                                            </th>
                                                                            
                                                                            <td title="<?php echo date("d M Y h:i:s",strtotime($singleRow['Mechanic']['created']));?>">
                                                                                <div class="td-Allocated heightFix">
                                                                                    <?php echo $singleRow['Mechanic']['id']; ?>
                                                                                    <div class="tdSubTitle">
                                                                                        <?php
                                                                                            $timestamp = strtotime($singleRow['Mechanic']['created']);
                                                                                            echo date("d M Y", $timestamp);
                                                                                        ?>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-Allocated heightFix" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                    <?php echo ucwords(strtolower($singleRow['Mechanic']['name'])); ?>
                                                                                    <i class="fas fa-caret-down downCaret"></i>
                                                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" >
                                                                                        <div class="customer-details">
                                                                                            <p>
                                                                                                <i class="fas fa-user" style="color: rgb(170, 170, 170);"></i>
                                                                                                <?php echo $singleRow['Mechanic']['contact_no1']; ?>
                                                                                            </p>
                                                                                            <?php
                                                                                                if($singleRow['Mechanic']['contact_no2']!="0")
                                                                                                {
                                                                                                    ?>
                                                                                                        <p>
                                                                                                            <a href="#">
                                                                                                                <?php echo $singleRow['Mechanic']['contact_no2']; ?>
                                                                                                            </a>
                                                                                                        </p>
                                                                                                    <?php
                                                                                                }
                                                                                                
                                                                                                if($singleRow['Mechanic']['contact_no3']!="0")
                                                                                                {
                                                                                                    ?>
                                                                                                        <p>
                                                                                                            <a href="#">
                                                                                                                <?php echo $singleRow['Mechanic']['contact_no3']; ?>
                                                                                                            </a>
                                                                                                        </p>
                                                                                                    <?php
                                                                                                }
                                                                                            ?>
                                                                                            
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="tdSubTitle">
                                                                                        <?php echo $singleRow['Mechanic']['covered_city']; ?>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            
                                                                            <td>
                                                                                <div class="td-Allocated heightFix">
                                                                                    <?php echo $singleRow['Mechanic']['covered_city']; ?>
                                                                                    <div class="tdSubTitle">
                                                                                        <?php echo $singleRow['Mechanic']['reff']; ?>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php
                                                                                        if($singleRow['Mechanic']['total_installations']!="0")
                                                                                        {
                                                                                            echo $singleRow['Mechanic']['total_installations'];
                                                                                        }
                                                                                    ?>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php echo $singleRow['Mechanic']['total_installations_payment']; ?>
                                                                                </div>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="td-container heightFix">
                                                                                    <?php
                                                                                        if($singleRow['Mechanic']['available_stock']!="0")
                                                                                        {
                                                                                            echo $singleRow['Mechanic']['available_stock'];
                                                                                        }
                                                                                    ?>
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