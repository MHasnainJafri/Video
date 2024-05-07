<?php 
if(isset($_SESSION[PRE_FIX.'id']))
{       
        
        $url=$baseurl . 'showCategories';
        $data =array(
            "parent_id" => "0"
        );
        
        $json_data=@curl_request($data,$url);
        $json_data=$json_data['msg'];
        
        ?>
        
        <style>
            .categories-button-wrapper {
                border-radius: 5px;
                padding: 10px 10px;
                background: white;
                max-width: 270px;
                width: 100%;
                margin-right: 10px;
                border: solid 1px #eee;
            }
            .categories-single-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border: 1px solid #EEE;
                border-radius: 3px;
                padding: 5px 10px;
                cursor: pointer;
                margin-bottom: 10px;
            }
            .categories-single-item .text-label {
                display: flex;
                align-items: center;
                font-size: 12px;
            }
            .categories-single-item .fa {
                margin-right: 4px;
                color: #A5A5A5;
                font-size: 12px;
            }
            .categories-single-item .fa:hover {
                color: #C42027;
            }
            .categories-single-item.active-tab {
                background-color: #DD3636;
                color: #fff;
            }
            .categories-single-item.active-tab .fa {
                color: #fff;
            }
            .categories-single-item:hover {
                background-color: #f7f7f7;
            }
            
            #myTabContent {
                display: flex;
                flex-wrap: wrap;
            }
        </style>

        <div class="qr-content">
            <div class="qr-page-content">
                <div class="qr-page zeropadding">
                    <div class="qr-content-area">
                        <div class="qr-row">
                            <div class="qr-el">

                                <div class="page-title">
                                    <h2>All Categories</h2>
                                    <div class="head-area">
                                    </div>
                                </div>
                                
                                <div id="myTabContent">
                                    
                                    <?php
                                        if(count($json_data))
                                        {
                                            ?>
                                                <div class="categories-button-wrapper" data-parent-id="0">
                                        
                                                    <?php  
                                                        foreach ($json_data as $singleRow)
                                                        {
                                                                ?>
                                                                    <div class="categories-single-item showCategories" data-id="<?php echo $singleRow['Category']['id']; ?>" data-parent-id="0">
                                                                       <div class="text-label">
                                                                           <img src="<?php echo checkImage($singleRow['Category']['image']); ?>" width="20px" height="20px" style="border-radius: 20px;border: solid 1px grey;margin-right: 5px;">
                                                                           <p class="label-name">
                                                                                <?php
                                                                                    echo ucwords($singleRow['Category']['title']);
                                                                                ?>
                                                                           </p>
                                                                       </div>
                                                                       <div class="functional-icons">
                                                                           <i class="fa fa-pencil edit_Category" aria-hidden="true" data-id="<?php echo $singleRow['Category']['id']; ?>"></i>
                                                                           
                                                                           <i class="fa fa-trash deleteCategory" data-id="<?php echo $singleRow['Category']['id']; ?>" aria-hidden="true"></i>
                                                                           
                                                                           <!--<a href="#">-->
                                                                           <!--    <i class="fa fa-star-o" aria-hidden="true"></i>-->
                                                                           <!--</a>-->
                                                                       </div>
                                                                    </div>
                                                                <?php
                                                            }
                                                    ?>
                                                    
                                                    <div class="addCategories" data-id="0" style="border: dashed 1.5px #c4c2c2;padding: 4px 10px;font-size: 13px;text-align: center;background: #f9f9f9;cursor: pointer;">
                                                       <div class="text-label">
                                                           <p class="label-name">
                                                                Add Category
                                                           </p>
                                                       </div>
                                                    </div>
                                                    
                                                </div>
                                                
                                            <?php
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