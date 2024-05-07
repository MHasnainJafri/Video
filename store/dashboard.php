<?php 


    include("config.php");
    if(isset($_SESSION[PRE_FIX.'id'])) 
    {
        include("header.php"); 
        include("navbar.php");
        include("rightsidebar.php"); 
        
        
            if(isset($_GET['action']))
            {
                if($_GET['action']=="success")
                {
                    ?>
                        <div class="actionBar success">
                            <h6 style="margin: 0px;font-size: 14px;">Success</h6>
                            <p style="margin: 0px;font-size: 14px;">
                                Action has been performed.
                            </p>
                        </div>
                    <?php 
                }
                else
                if($_GET['action']=="error")
                {
                    ?>
                        <div class="actionBar error">
                            <h6 style="margin: 0px;font-size: 14px;">Error</h6>
                            <p style="margin: 0px;font-size: 14px;">
                                Action did not performed
                            </p>
                        </div>
                    <?php 
                }
            }
        
            if(isset($_GET['p']) ) 
            { 
                if( $_GET['p'] == "products" ) 
                { 
                    include("products.php");
                }
                
                if( $_GET['p'] == "order" ) 
                { 
                    include("order.php");
                }
                
                if( $_GET['p'] == "addProduct" ) 
                { 
                    include("addProduct.php");
                }
                
            } 
            
        include("footer.php"); 
    
    }
    else
    {
        echo "<script>window.location='index.php'</script>";
    }
?>