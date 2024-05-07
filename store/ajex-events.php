
<?php
include("config.php");
if(isset($_GET['q']))
{
    if (@$_GET['q'] == "addVariants")
    {
        $product_id=$_GET['product_id'];
        
        ?>
        
            <form method="post" class="addVariantsForm">
                
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Add Variations</h5>
                    <button type="button" class="close" onclick="ClosePopup();">
                        <span aria-hidden="true">
                            <i class="fal fa-times"></i>
                        </span>
                    </button>
                </div>
                <div class="modal-body modelHeight" style="height: auto; overflow: scroll;">
                    <div class="input-email-container">
                        <div class="email-input-container">
                            <label for="subject-id">
                                Option name
                            </label>
                            <input type="text" name="option_name" class="main-content-input">
                        </div>
                        
                        <div class="select-wrapper">
                            <label for="subject-id">
                                Option Value
                            </label>
                            <input type="text" name="option_value[]" class="main-content-input" >
                        </div>
                        
                        <div id="contentReceivedAddAnotherValue" class="select-wrapper"></div>
                    
                        <p class="addAnotherValue" style="color: #008060;text-decoration: underline;font-size: 14px;cursor: pointer;">Add another value</p>
                        
                    </div>    
                </div>
                <div class="modal-footer mt-3">
                    <button type="button" class="footer-card-btn" onclick="ClosePopup();">Cancel</button>
                    <button type="submit" class="add-product-btn px-3">Save</button>
                </div>
            </form>
            
            <!--<form class="addVariantsForm">-->
            <!--    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" >-->
            <!--    <div class="content-item">-->
            <!--        <div class="select-wrapper">-->
            <!--            <label for="subject-id">-->
            <!--                Option name-->
            <!--            </label>-->
            <!--            <input type="text" name="option_name" class="main-content-input">-->
            <!--        </div>-->
                    
                    
            <!--        <div class="select-wrapper">-->
            <!--            <label for="subject-id">-->
            <!--                Option Value-->
            <!--            </label>-->
            <!--            <input type="text" name="option_value[]" class="main-content-input" >-->
            <!--        </div>-->
                    
                    
                    
            <!--        <button type="submit" class="side-button button-fill" style="color:white;">-->
            <!--            Save Variants-->
            <!--        </button>-->
                    
            <!--    </div>-->
            <!--</form>-->
        <?php
    }
    
    if (@$_GET['q'] == "editVariants")
    {
        $id=$_GET['id'];
        
        $url = $baseurl . 'showProductsAttributes';
        
        $data = array(
            "id" => $id
        );
        $json_data = @curl_request($data, $url);
        $json_data = $json_data['msg'];
        
        
        ?>
        
            <form method="post" class="editVariantsForm">
                
                <input type="hidden" name="product_id" value="<?php echo $json_data['ProductAttribute']['product_id']; ?>" >
                <input type="hidden" name="id" value="<?php echo $json_data['ProductAttribute']['id']; ?>" >
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Edit Variations</h5>
                    <button type="button" class="close" onclick="ClosePopup();">
                        <span aria-hidden="true">
                            <i class="fal fa-times"></i>
                        </span>
                    </button>
                </div>
                <div class="modal-body modelHeight" style="height: auto; overflow: scroll;">
                    <div class="input-email-container">
                        <div class="email-input-container">
                            <label for="subject-id">
                                Option name
                            </label>
                            <input type="text" name="option_name" class="main-content-input" value="<?php echo $json_data['ProductAttribute']['name']; ?>">
                        </div>
                        
                        <?php
                            if(is_array($json_data['ProductAttributeVariation']) || is_object($json_data['ProductAttributeVariation'])) 
                            {
                                foreach($json_data['ProductAttributeVariation'] as $singleRow) 
                                {
                                    ?>
                                        <div class="select-wrapper">
                                            <label for="subject-id">
                                                Option Value
                                            </label>
                                            <input type="text" name="option_value[]" class="main-content-input" value="<?php echo $singleRow['value']; ?>" >
                                        </div>
                                    <?php
                                }
                            }
                        ?>
                        
                        
                        <div id="contentReceivedAddAnotherValue" class="select-wrapper"></div>
                    
                        <p class="addAnotherValue" style="color: #008060;text-decoration: underline;font-size: 14px;cursor: pointer;">Add another value</p>
                        
                    </div>    
                </div>
                <div class="modal-footer mt-3">
                    <button type="button" class="footer-card-btn" onclick="ClosePopup();">Cancel</button>
                    <button type="submit" class="add-product-btn px-3">Save</button>
                </div>
            </form>
            
           
        <?php
    }
    
    
    if (@$_GET['q'] == "showSubCategory")
    {
        $id=$_GET['id'];
        
        $url = $baseurl . 'showOnlyProductCategories';
        
        $data = array(
            "parent_id" => $id
        );
        $json_data = @curl_request($data, $url);
        
        if(is_array($json_data['msg']) || is_object($json_data['msg'])) 
        {   
            ?>
                <div class="header-productsearch-popup showSubCategory" data-id="<?php echo $json_data['msg'][0]['Parent']['parent_id']; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24">
                        <path d="M400-240 160-480l241-241 43 42-169 169h526v60H275l168 168-43 42Z"/>
                    </svg>
                    <?php echo $json_data['msg'][0]['Parent']['title']; ?>
                </div>
            <?php
            
            foreach($json_data['msg'] as $singleRow) 
            {
                ?>
                    <div class="search-result-item">
                        <div class="search-result-user selectCategory" data-id="<?php echo $singleRow['Category']['id']; ?>" data-title="<?php echo $singleRow['Category']['title']; ?>" >
                            <?php echo $singleRow['Category']['title']; ?>
                        </div>
                        
                        
                        <?php
                            if(count($singleRow['Children']))
                            {
                                ?>
                                    <div class="next-cheron showSubCategory" data-id="<?php echo $singleRow['Category']['id']; ?>" >
                                        <svg xmlns="http://www.w3.org/2000/svg" height="48"
                                             viewBox="0 -960 960 960" width="48">
                                            <path d="M530-481 332-679l43-43 241 241-241 241-43-43 198-198Z"></path>
                                        </svg>
                                    </div>
                                <?php   
                            }
                        ?>
                        
                        
                        
                    </div>
                <?php
            }   
        }
        else
        {
            ?>
                <div class="header-productsearch-popup showSubCategory" data-id="0">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24">
                        <path d="M400-240 160-480l241-241 43 42-169 169h526v60H275l168 168-43 42Z"/>
                    </svg>
                    All
                </div>
            
                <div class="search-result-item">
                    <div class="search-result-user">
                        No Category Found
                    </div>
                </div>
            <?php
        }
        
            
    }
    
    
    
    if (@$_GET['q'] == "showOrderDetail")
    {
        $id=$_GET['id'];
        
        $url = $baseurl . 'showOrderDetail';
        
        $data = array(
            "order_id" => $id,
            "user_id" => $_SESSION[PRE_FIX . 'id']
        );
        $json_data = @curl_request($data, $url);
        $json_data = $json_data['msg'];
        
        ?>
        
            
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Order #<?php echo $id; ?></h5>
            <button type="button" class="close" onclick="ClosePopup();">
                <span aria-hidden="true">
                    <i class="fal fa-times"></i>
                </span>
            </button>
        </div>
        <div class="modal-body modelHeight" style="height: auto; overflow: scroll;">
            <div class="input-email-container">
                <div class="email-input-container">
                    <label style="font-weight: 600;">
                        Customer Name
                    </label>
                    
                    <p style="font-size: 14px;">
                        <?php
                            echo ucwords($json_data['User']['first_name']." ".$json_data['User']['last_name']);
                        ?>
                    </p>
                    
                    <label style="font-weight: 600;">
                        Amount
                    </label>
                    
                    <p style="font-size: 14px;">
                        <?php
                            echo $json_data['Order']['total'];
                        ?>
                    </p>
                    
                    <label style="font-weight: 600;">
                        Delivery Address
                    </label>
                    
                    <p style="font-size: 14px;">
                        <a href="http://maps.google.com/maps?q=+<?php echo $json_data['DeliveryAddress']['lat']; ?>,+<?php echo $json_data['DeliveryAddress']['long']; ?>" target="_blank">
                            <?php
                                echo $json_data['DeliveryAddress']['street']. "," .$json_data['DeliveryAddress']['city'] . "," .@$json_data['DeliveryAddress']['Country']['name'];
                            ?>
                        </a>
                    </p>
                </div>
                
                
                <div class="email-input-container">
                    <label style="font-weight: 600;">
                        Order Items
                    </label>
                    <br><br>
                    <?php
                       
                        if(is_array($json_data) || is_object($json_data)) 
                        {   
                            foreach($json_data['OrderProduct'] as $singleRow) 
                            {
                                ?>
                                    <p style="font-size: 14px;">
                                        <img src="<?php echo checkImageUrl($singleRow['product_image']); ?>" style="width: 40px;">
                                        <?php
                                            echo $singleRow['product_title'];
                                        ?>
                                    </p>
                                <?php
                            }
                        }
                        
                    ?>
                    
                    
                </div>
                
            </div>    
        </div>
        
           
        <?php
    }
    
}
?>

