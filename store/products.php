<?php
    if(isset($_SESSION[PRE_FIX . 'id'])) 
    {
        
        $url = $baseurl . 'showProducts';
        $data = array(
            "user_id" => $_SESSION[PRE_FIX . 'id']
        );
        $json_data = @curl_request($data, $url);
        $json_data = $json_data['msg'];
        
        ?>
            <div class="main-content-container">
                <div class="main-content-container-wrap">
                    <div class="content-page-header align-items-center justify-content-between">
                        <div class="page-header-text">
                            Manage Product
                        </div>
                        <div class="button-wraapper-content-header">
                            <div class="button-content-header button-fill">
                                <a href="process.php?action=addProduct">
                                    Add Product
                                </a>
                            </div>
                        </div>
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
                                                        <th scope="col">Title</th>
                                                        <th scope="col">Status</th>
                                                        <th scope="col">Category</th>
                                                        <th scope="col">Views</th>
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
                                                                    
                                                                    if(count($singleRow['ProductImage']))
                                                                    {
                                                                        $thum=$singleRow['ProductImage'][0]['thum'];
                                                                    }
                                                                    else
                                                                    {
                                                                        $thum="assets/img/noimage.jpg";
                                                                    }
                                                                   
                                                                    ?>
                                                                        <tr id="<?php echo $singleRow['Product']['id']; ?>">
                                                                           
                                                                            <td>
                                                                                <img src="<?php echo checkImageUrl($thum); ?>" style="width: 60px; border: solid 1px #ececec;height: 52px;">
                                                                                <?php echo $singleRow['Product']['title']; ?>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <?php 
                                                                                    if($singleRow['Product']['status']=="0")
                                                                                    {
                                                                                        ?>
                                                                                            <button class="statusbtn grey" type="button">Draft</button>
                                                                                        <?php
                                                                                    }
                                                                                    else
                                                                                    if($singleRow['Product']['status']=="1")
                                                                                    {
                                                                                        ?>
                                                                                            <button class="statusbtn" type="button">Active</button>
                                                                                        <?php
                                                                                    }
                                                                                    
                                                                                ?>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <?php 
                                                                                    if(count($singleRow['Category']))
                                                                                    {
                                                                                        echo $singleRow['Category']['title'];
                                                                                    }
                                                                                ?>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <?php 
                                                                                    echo $singleRow['Product']['view'];
                                                                                ?>
                                                                            </td>
                                                                            
                                                                            <td>
                                                                                <div class="dropdown">
                                                                                    <a href="process.php?action=deleteProduct&id=<?php echo $singleRow['Product']['id'];?>">
                                                                                        <i class="fas fa-trash" style="color: #90908f;" ></i>
                                                                                    </a>
                                                                                    &nbsp;&nbsp;
                                                                                    <a href="dashboard.php?p=addProduct&id=<?php echo $singleRow['Product']['id'];?>">
                                                                                        <i class="fas fa-edit" style="color: #90908f;" ></i>
                                                                                    </a>
                                                                                    
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