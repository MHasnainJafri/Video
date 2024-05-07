<?php
    if (isset($_SESSION[PRE_FIX . 'id'])) 
    {
        $url = $baseurl . 'showProductDetail';
        $product_id=$_GET['id'];
        $data = array(
            "product_id" => $product_id,
            "user_id" => $_SESSION[PRE_FIX . 'id']
        );
        $json_data = @curl_request($data, $url);
        $json_data = $json_data['msg'];
        
        // 
        
        $url = $baseurl . 'showOnlyProductCategories';
        $product_id=$_GET['id'];
        $data = array(
            "parent_id" => "0"
        );
        $json_data_category = @curl_request($data, $url);
        $json_data_category = $json_data_category['msg'];
        
        ?>
            <div class="main-content-container">
                <div class="main-content-container-wrap">
                    <div class="content-page-header align-items-center justify-content-between">
                        <div class="page-header-text">
                            Add Product
                        </div>
                    </div>
                    
                    
                        <div class="dashbord-wdh-sidebar d-flex" style="margin-top: 16px;">
                            
                            <div class="main-content-left">
                                
                                <form action="process.php?action=updateProduct" id="updateProduct" method="post">  
                                <input type="hidden" class="statusValue" name="status" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                <input type="hidden" name="category" class="category_id" value="0">
                                
                                <div class="wrappr-sidebar-content">
                                    
                                    <div class="content-item">
                                        <div class="select-wrapper">
                                            <label for="subject-id">
                                                Title
                                            </label>
                                            <input type="text" class="main-content-input" id="subject-id" name="subject" value="<?php echo $json_data['Product']['title']; ?>" >
                                        </div>
                                        
                                        
                                        <div class="select-wrapper">
                                            <label for="subject-id">
                                                Description
                                            </label>
                                            
                                            <script src="assets/js/ckeditor.js"></script>
                                            <style>
                                                .ck.ck-content {
                                                  font-size: 1em;
                                                  line-height: 1.6em;
                                                  margin-bottom: 0.8em;
                                                  min-height: 250px;
                                                }
                                            </style>
                                            
                                            <textarea name="description" id="editor">
                                                <?php echo $json_data['Product']['description']; ?>
                                    		</textarea>
                                        
                                        </div>
                                        
                                    </div>
                                    
                                    <div class="content-item">
                                        
                                    </div>
                                    
                                </div>
                                
                                <div class="wrappr-sidebar-content">
                                    <div class="content-item">
                                        <h4>
                                            Pricing
                                        </h4>
                                    </div>
                                    
                                    <div class="content-item">
                                        <div class="select-wrapper">
                                            <label for="subject-id">
                                                Price
                                            </label>
                                            <input type="text" class="main-content-input" id="subject-id" name="price" value="<?php echo $json_data['Product']['price']; ?>">
                                        
                                        </div>
                                    </div>
                                </div>
                                
                                
                                <div class="wrappr-sidebar-content">
                                    <div class="content-item">
                                        <h4>
                                            Media
                                        </h4>
                                    </div>
                                    
                                    <div class="images-add-url-section">
                                        <div class="content-wrapper d-flex uploadedImages">
                                            
                                            <?php
                                                if(is_array($json_data['ProductImage']) || is_object($json_data['ProductImage'])) 
                                                {
                                                    foreach($json_data['ProductImage'] as $singleRow) 
                                                    {
                                                        if(count($singleRow))
                                                        {
                                                            ?>
                                                                <div class="item-single cover">
                                                                    <img src="<?php echo checkImageUrl($singleRow['image']); ?>">
                                                                </div>
                                                            <?php
                                                        }
                                                    }
                                                }
                                            ?>
                                            
                                            <div class="item-single add-new-url">
                                                <div class="input-file-form">
                                                    <label for="media-file-input">
                                                        Add
                                                    </label>
                                                    <p class="media-file-description">
                                                        Accept Images
                                                    </p>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    
                                </div>
                                
                                </form>
                                
                                <div class="wrappr-sidebar-content">
                                    <div class="content-item">
                                        <h4>
                                            Variants
                                        </h4>
                                    </div>
                                    
                                    <?php
                                        if(is_array($json_data['ProductAttribute']) || is_object($json_data['ProductAttribute'])) 
                                        {
                                            foreach($json_data['ProductAttribute'] as $singleRowAttribute) 
                                            {
                                                ?>
                                                    <div class="variation_row" data-id="<?php echo $singleRowAttribute['id']; ?>" >
                                                        <div class="category-price-wrapper">
                                                            <div class="single-price-variable">
                                                                <div class="category-name-variable">
                                                                    <?php
                                                                        echo $singleRowAttribute['name'];
                                                                    ?>
                                                                    
                                                                    <div class="category-items-variable" style="margin-top: 18px;">
                                                                        <?php
                                                                            if(is_array($singleRowAttribute['ProductAttributeVariation']) || is_object($singleRowAttribute['ProductAttributeVariation'])) 
                                                                            {
                                                                                foreach($singleRowAttribute['ProductAttributeVariation'] as $singleRowProductAttributeVariation) 
                                                                                {
                                                                                    ?>
                                                                                        <span>
                                                                                            <?php echo $singleRowProductAttributeVariation['value'];; ?>
                                                                                        </span>  
                                                                                    <?php 
                                                                                }
                                                                            }
                                                                        ?>
                                                                    </div>    
                                                                    
                                                                </div>
                                                            </div>
                                                            <div class="category-button-wrapper">
                                                                <div class="edit-button-wrapper editVariation" data-id="<?php echo $singleRowAttribute['id']; ?>">
                                                                    <button>
                                                                        Edit
                                                                    </button>
                                                                </div>
                                                                
                                                                <div class="edit-button-wrapper deleteVariation" data-id="<?php echo $singleRowAttribute['id']; ?>">
                                                                    <button>
                                                                        Delete
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php
                                            }
                                        }
                                    ?>
                                    
                                    <div class="select-wrapper" id="activeVariants"></div>
                                    
                                    <div class="select-wrapper" id="contentReceivedVariants"></div>
                                    
                                    <div class="content-item">
                                        <div class="select-wrapper addVariants" data-id="<?php echo $product_id; ?>">
                                            <label style="color: #008060;text-decoration: underline;font-size: 14px;cursor: pointer;">
                                                Add options like size or color
                                            </label>
                                        </div>
                                    </div>
                                    
                                </div>
                                
                                
                                <?php
                                
                                    // $arrays = [];

                                    // foreach ($json_data['ProductAttribute'] as $attribute) {
                                    //     $variations = $attribute['ProductAttributeVariation'];
                                        
                                    //     $sizes = [];
                                    //     foreach ($variations as $item) {
                                    //         $sizes[] = $item['value'];
                                    //     }
                                        
                                    //     $arrays[] = $sizes;
                                    // }
                                   
                                    
                                    
                                    
                                    
                                    
                                    // create array of all variations
                                    // $arrays = [];

                                    // foreach ($json_data['ProductAttribute'] as $attribute) {
                                    //     $variations = $attribute['ProductAttributeVariation'];
                                        
                                    //     $sizes="";
                                    //     foreach ($variations as $item) {
                                    //         $sizes= $variations;
                                    //     }
                                        
                                    //     $arrays[] = $sizes;
                                    // }
                                    
                                    // echo json_encode($arrays);
                                    
                                     
                                    // function generateCombinations($arrays, $currentIndex, $currentCombination, &$combinations) 
                                    // {
                                    //     if ($currentIndex == count($arrays)) 
                                    //     {
                                    //         $combinations[] = implode(' / ', $currentCombination);
                                    //         return;
                                    //     }
                                    
                                    //     foreach ($arrays[$currentIndex] as $value) 
                                    //     {
                                    //         $currentCombination[] = $value;
                                    //         generateCombinations($arrays, $currentIndex + 1, $currentCombination, $combinations);
                                    //         array_pop($currentCombination);
                                    //     }
                                    // }
                                    
                                    
                                    // $combinations = array();
                                    
                                    // generateCombinations($arrays, 0, array(), $combinations);
                                    
                                    
    

                                ?>
                                
                                
                                
                                
                            
                                
                                <div class="content-item varients-warpper" style="display:none;">
                                    
                                    <table style="width: 100%;">
                                        
                                        <?php
                                        
                                            // $arrays = [];

                                            // foreach ($json_data['ProductAttribute'] as $attribute) {
                                            //     $variations = $attribute['ProductAttributeVariation'];
                                                
                                            //     $sizes="";
                                            //     foreach ($variations as $item) {
                                            //         $sizes= $variations;
                                            //     }
                                                
                                            //     $arrays[] = $sizes;
                                            // }
                                            
                                            
                                            // Decode the JSON string to get an array
                                            // $arrays = json_decode($arrays, true);
                                            
                                            // function generateCombinations($arrays, $currentIndex, $currentCombination, &$combinations) 
                                            // {
                                            //     if ($currentIndex == count($arrays)) 
                                            //     {
                                            //         $combinations[] = implode(' / ', $currentCombination);
                                            //         return;
                                            //     }
                                            
                                            //     foreach ($arrays[$currentIndex] as $value) 
                                            //     {
                                            //         $currentCombination[] = $value['value'];
                                            //         generateCombinations($arrays, $currentIndex + 1, $currentCombination, $combinations);
                                            //         array_pop($currentCombination);
                                            //     }
                                            // }
                                            
                                            // $combinations = array();
                                            
                                            // generateCombinations($arrays, 0, array(), $combinations);
                                            
                                            // foreach ($combinations as $combination) 
                                            // {
                                            //     echo '
                                            //         <tr style="border-bottom: solid 1px #eaeaea;line-height:55px;">
                                            //             <td style="font-size: 14px;">
                                            //                 <input name="combination_string[]" type="hidden" value="' . $combination . '">
                                            //                 ' . $combination . '
                                            //             </td>
                                            //             <td><input type="text" name="price[]" placeholder="Price" style="border: solid 1px #dddcdc;width: 150px;height: 30px;"></td>
                                            //             <td><input type="text" name="sku[]" placeholder="SKU" style="border: solid 1px #dddcdc;width: 150px;height: 30px;"></td>
                                            //             <td><input type="text" name="available_stock[]" placeholder="Available Stock" style="border: solid 1px #dddcdc;width: 150px;height: 30px;"></td>
                                            //             <td>
                                            //                 <span class="fas fa-edit" style="color: #a2a2a2;font-weight: 600;"></span>
                                            //                 <span class="fas fa-trash" style="color: #a2a2a2;font-weight: 600;"></span>
                                            //             </td>
                                            //         </tr>';
                                            // }
                                        ?>
                                        
                                        
                                        <?php
                                            //foreach ($combinations as $combination) 
                                            // {
                                                ?>
                                                    <!--<tr style="border-bottom: solid 1px #eaeaea;line-height:55px;">-->
                                                    <!--    <td style="font-size: 14px;">-->
                                                    <!--        <input name="combination_string[]" type="hidden" value="<?php echo $combination; ?>">-->
                                                    <!--        <?php echo $combination; ?>-->
                                                    <!--    </td>-->
                                                    <!--    <td><input type="text" name="price[]" placeholder="Price" style="border: solid 1px #dddcdc;width: 150px;height: 30px;"></td>-->
                                                    <!--    <td><input type="text" name="sku[]" placeholder="SKU" style="border: solid 1px #dddcdc;width: 150px;height: 30px;"></td>-->
                                                    <!--    <td><input type="text" name="available_stock[]" placeholder="Available Stock" style="border: solid 1px #dddcdc;width: 150px;height: 30px;"></td>-->
                                                    <!--    <td>-->
                                                    <!--        <span class="fas fa-edit" style="color: #a2a2a2;font-weight: 600;"></span>-->
                                                    <!--        <span class="fas fa-trash" style="color: #a2a2a2;font-weight: 600;"></span>-->
                                                    <!--    </td>-->
                                                    <!--</tr>-->
                                                <?php    
                                            // }
                                        ?>
                                    
                                    </table>
                                    
                                </div>
                                
                                
                            </div>
                            
                            <div class="sidebar-right">
                                <div class="wrappr-sidebar-content">
                                    <div class="content-item">
                                        <div class="select-wrapper">
                                            <label for="input-selcet">
                                                Status
                                            </label>
                                            <select name="status" id="input-selcet" class="selectmain-content selectStatus">
                                                <option value="1" <?php if($json_data['Product']['status']=="1"){ echo "selected"; }  ?>>Active</option>
                                                <option value="0" <?php if($json_data['Product']['status']=="0"){ echo "selected"; }  ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="wrappr-sidebar-content">
                                    
                                    <div class="content-item">
                                        <div class="select-wrapper">
                                            <label for="input-selcet">
                                                Category 
                                            </label>
                                            
                                            <div class="select-box w-100 position-relative">
                                                <!--<form class="search-form d-flex align-items-center updateCategory">-->
                                                    <input type="text" placeholder="Search" class="sidebar-select-input-popup" value="<?php echo $json_data['Category']['title']; ?>">
                                                    
                                                    <input type="hidden" class="product_id" value="<?php echo $product_id; ?>">
                                                    <input type="hidden" class="user_id" value="<?php echo $_SESSION[PRE_FIX . 'id']; ?>">
                                                <!--</form>-->
                                                <div class="popup-search-result w-100 position-absolute" style="display: none;">
                                                    
                                                    <div class="contentReceivedCategory">
                                                        <?php
                                                            if(is_array($json_data_category) || is_object($json_data_category)) 
                                                            {
                                                                foreach($json_data_category as $singleRow) 
                                                                {
                                                                    ?>
                                                                        <div class="search-result-item">
                                                                            <div class="search-result-user selectCategory" data-id="<?php echo $singleRow['Category']['id']; ?>" data-title="<?php echo $singleRow['Category']['title']; ?>">
                                                                                <?php echo $singleRow['Category']['title']; ?>
                                                                            </div>
                                                                            
                                                                            
                                                                            <?php
                                                                                if(count($singleRow['Children']))
                                                                                {
                                                                                    ?>
                                                                                        <div class="next-cheron showSubCategory" data-id="<?php echo $singleRow['Category']['id']; ?>">
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
                                                            
                                                        ?>
                                                    </div>
                                                    
                                                    
                                                    <!--<div class="closed-wrapper">-->
                                                    <!--    <h3>-->
                                                    <!--        Browse All-->
                                                    <!--    </h3>-->
                                                        
                                                    <!--    <div class="search-result-item">-->
                                                    <!--        <div class="search-result-user">-->
                                                    <!--            Art and Entertainment-->
                                                    <!--        </div>-->
                                                    <!--        <div class="next-cheron">-->
                                                    <!--            <svg xmlns="http://www.w3.org/2000/svg" height="48"-->
                                                    <!--                 viewBox="0 -960 960 960" width="48">-->
                                                    <!--                <path d="M530-481 332-679l43-43 241 241-241 241-43-43 198-198Z"></path>-->
                                                    <!--            </svg>-->
                                                    <!--        </div>-->
                                                    <!--    </div>-->
                                                        
                                                    <!--    <div class="search-result-item">-->
                                                    <!--        <div class="search-result-user">-->
                                                    <!--            Art and Entertainment-->
                                                    <!--        </div>-->
                                                    <!--        <div class="next-cheron">-->
                                                    <!--            <svg xmlns="http://www.w3.org/2000/svg" height="48"-->
                                                    <!--                 viewBox="0 -960 960 960" width="48">-->
                                                    <!--                <path d="M530-481 332-679l43-43 241 241-241 241-43-43 198-198Z"></path>-->
                                                    <!--            </svg>-->
                                                    <!--        </div>-->
                                                    <!--    </div>-->
                                                        
                                                    <!--    <div class="search-result-item">-->
                                                    <!--        <div class="search-result-user">-->
                                                    <!--            Art and Entertainment-->
                                                    <!--        </div>-->
                                                    <!--        <div class="next-cheron">-->
                                                    <!--            <svg xmlns="http://www.w3.org/2000/svg" height="48"-->
                                                    <!--                 viewBox="0 -960 960 960" width="48">-->
                                                    <!--                <path d="M530-481 332-679l43-43 241 241-241 241-43-43 198-198Z"></path>-->
                                                    <!--            </svg>-->
                                                    <!--        </div>-->
                                                    <!--    </div>-->
                                                        
                                                    <!--</div>-->
                                                    
                                                    
                                                </div>
                                            </div>
                                            
                                            
                                        </div>
                                    </div>
                                    
                                    
                                </div>
                                
                                
                                <div class="side-button button-fill updateProduct">
                                    <a href="#">
                                        Save Product
                                    </a>
                                </div>
                                
                                
                            </div>
                        </div>
                   
                
                </div>
            </div> 
            
            
            
            
            <form id="uploadForm" enctype="multipart/form-data" style="display:none;">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="file" name="attachment" class="fileInput" id="media-file-input" accept=".jpg,.png,.jpeg,.mpeg">
            </form>
            
            
            <script>
            	ClassicEditor
            		.create( document.querySelector( '#editor' ), {
            			// toolbar: [ 'heading', '|', 'bold', 'italic', 'link' ]
            		} )
            		.then( editor => {
            			window.editor = editor;
            		} )
            		.catch( err => {
            			console.error( err.stack );
            		} );
            </script>
            
        <?php
        
        
        
        
    } 
    else 
    {
        echo "<script>window.location='index.php'</script>";
        die;
    }
?>