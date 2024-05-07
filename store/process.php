<?php
include("config.php");
if (isset($_SESSION[PRE_FIX . 'id'])) 
{
    if (isset($_GET['action']))
    {
        if(@$_GET['action'] == "logout" ) 
        { 
            @session_destroy();
            echo "<script>window.location='index.php'</script>";
        }
        
        
        
        if($_GET['action'] == 'deleteProduct')
        {
            $id = htmlspecialchars($_GET['id'], ENT_QUOTES);
            
            $url=$baseurl . 'deleteProduct'; 
    
            $data =array(
                "product_id" => $id
            );
            
            $json_data=@curl_request($data,$url);
            
            if ($json_data['code'] == "200") 
            {
                echo "<script>window.location='dashboard.php?p=products'</script>";
            } 
            else 
            {
                echo "<script>window.location='dashboard.php?p=products'</script>";
            }
        }
        
        if($_GET['action'] == 'addProduct')
        {
            $url=$baseurl . 'addProduct'; 
    
            $data =array(
                "user_id" => $_SESSION[PRE_FIX . 'id'],
                "product_category_id" => "",
                "title" => "",
                "description" => "",
            );
            
            $json_data=@curl_request($data,$url);
            
            if ($json_data['code'] == "200") 
            {
                echo "<script>window.location='dashboard.php?p=addProduct&id=" . $json_data['msg']['Product']['id'] . "';</script>";
            } 
            else 
            {
                echo "<script>window.location='dashboard.php?p=products'</script>";
            }
        }
        
        if($_GET['action'] == 'updateProduct')
        {
            $id= $_POST['product_id'];
            $subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);
            $description = $_POST['description'];
            $price = htmlspecialchars($_POST['price'], ENT_QUOTES);
            $status = htmlspecialchars($_POST['status'], ENT_QUOTES);
            $category = htmlspecialchars($_POST['category'], ENT_QUOTES);
            
            
            
            $data =array(
                "id" => $id,
                "user_id" => $_SESSION[PRE_FIX . 'id'],
                "category_id" => $category,
                "title" => $subject,
                "description" => $description,
                "price" => $price,
                "status" => $status
            );
            
            $url=$baseurl . 'addProduct'; 
            $json_data=@curl_request($data,$url);
            
            if ($json_data['code'] == "200") 
            {
                echo "<script>window.location='dashboard.php?p=addProduct&id=" . $id . "';</script>";
            } 
            else 
            {
                echo "<script>window.location='dashboard.php?p=addProduct&id=" . $id . "';</script>";
            }
        }
        
        
        if($_GET['action']=="attachment")  
        {
            
            $filename = $_FILES['attachment']['name'];
            $filedata = $_FILES['attachment']['tmp_name'];
            $filesize = $_FILES['attachment']['size'];
            $filetype = $_FILES['attachment']['type'];
            $product_id = $_POST['product_id'];
            $user_id = $_SESSION[PRE_FIX . 'id'];
            
            $cfile = new CURLFILE($filedata, $filetype, $filename);
            $headers = array("Content-Type:multipart/form-data","api-key: ".API_KEY." "); // cURL headers for file uploading
            $data['file'] = $filename;
            $data['mimeType'] = $filetype;
            $data['file'] = $cfile;
            $data['product_id'] = $product_id;
            $data['user_id'] = $user_id;
            
            
            $ch = curl_init($baseurl."addProductImage");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_INFILESIZE, $filesize);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
            $return = curl_exec($ch);
            $json_data = json_decode($return, true);
            $curl_error = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if($json_data['code'] == 200)
            {   
                $filename=$json_data['msg']['ProductImage']['image'];
                
                ?>
                    <div class="item-single cover">
                        <img src="<?php echo checkImageUrl($filename); ?>">
                    </div>
                <?php
            }
            else
            {
                echo $json_data['msg'];
            }
    
        }
        
        
        
        if($_GET['action'] == 'addVariants')
        {
            $id = htmlspecialchars($_POST['product_id'], ENT_QUOTES);
            $name = htmlspecialchars($_POST['option_name'], ENT_QUOTES);
            
            $url=$baseurl . 'addProductAttribute'; 
    
            $data =array(
                "product_id" => $id,
                "name" => $name
            );
            
            $json_data=@curl_request($data,$url);
            
           
           
            if ($json_data['code'] == "200") 
            {
                
                // variation value
                $productAttribute=$json_data['msg']['ProductAttribute']['id'];
                $value = $_POST['option_value'];
                
                // make options array
                $valueArray=array();
                foreach ($value as $value) 
                {
                    $valueArray[]= array(
                                            "value" => $value,
                                        );
                }
                
                $url=$baseurl.'addProductAttributeVariation'; 
        
                $data =array(
                    "product_attribute_id" => $productAttribute,
                    "option" => $valueArray
                );
                
                $json_data=@curl_request($data,$url);
                
                echo json_encode($json_data);
                
                
            } 
            
        }
        
        
        
        if($_GET['action'] == 'editVariants')
        {
            
            $id = htmlspecialchars($_POST['id'], ENT_QUOTES);
            $product_id = htmlspecialchars($_POST['product_id'], ENT_QUOTES);
            $name = htmlspecialchars($_POST['option_name'], ENT_QUOTES);
            
            $url=$baseurl . 'addProductAttribute'; 
    
            $data =array(
                "id" => $id,
                "product_id" => $product_id,
                "name" => $name
            );
            
            $json_data=@curl_request($data,$url);
            
            if ($json_data['code'] == "200") 
            {
                
                // variation value
                $productAttribute=$json_data['msg']['ProductAttribute']['id'];
                $value = $_POST['option_value'];
                
                // make options array
                $valueArray=array();
                foreach ($value as $value) 
                {
                    $valueArray[]= array(
                                        "value" => $value,
                                    );
                }
                
                $url=$baseurl.'addProductAttributeVariation'; 
        
                $data =array(
                    "product_attribute_id" => $productAttribute,
                    "option" => $valueArray
                );
                
                $json_data=@curl_request($data,$url);
                
                echo json_encode($json_data);
                
                
            } 
            
        }
        
        if($_GET['action'] == 'deleteVariation')
        {
            
            $id = htmlspecialchars($_GET['id'], ENT_QUOTES);
            $url=$baseurl . 'deleteProductAttribute'; 
    
            $data =array(
                "id" => $id
            );
            
            $json_data=@curl_request($data,$url);
            
            echo $json_data['code'];
            
            
        }
        
        if($_GET['action'] == 'updateCategory')
        {
            
            $id = htmlspecialchars($_GET['id'], ENT_QUOTES);
            $product_id = htmlspecialchars($_GET['product_id'], ENT_QUOTES);
            
            
            $url=$baseurl . 'addProduct'; 
    
            $data =array(
                "id" => $product_id,
                "product_category_id" => $id,
                "user_id" => $_SESSION[PRE_FIX . 'id']
            );
            
            $json_data=@curl_request($data,$url);
            
            echo $json_data['code'];
            
            
        }
        
        
        
        
    }
}
else
{
    if (isset($_GET['action']))
    {
        if(@$_GET['action'] == "login" ) 
        {
            $email = htmlspecialchars($_POST['email'], ENT_QUOTES);
            $password = htmlspecialchars($_POST['password'], ENT_QUOTES);
            $data = [
                "email" => $email,
                "password" => $password,
                "role" => "admin"
            ];
            $url = $baseurl . 'login';
            $json_data = @curl_request($data, $url);
            $data = $json_data['msg'];
            
            if ($json_data['code'] == "200") 
            {
                $_SESSION[PRE_FIX.'id'] = $data['User']['id'];
                $_SESSION[PRE_FIX.'role'] = $data['User']['role'];
                $_SESSION[PRE_FIX.'email'] = $email;
                $_SESSION[PRE_FIX.'first_name'] = $data['User']['first_name'];
                $_SESSION[PRE_FIX.'last_name'] = $data['User']['last_name'];
                
                echo "<script>window.location='dashboard.php?p=products'</script>";
            } 
            else 
            {
                echo "<script>window.location='./'</script>";
            }
        }
    }
}
?>