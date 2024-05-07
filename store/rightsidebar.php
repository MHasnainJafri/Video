<?php 
    if(isset($_GET['p']))
    {
        $products ="";
        $orders ="";
        if($_GET['p'] == 'products')
        {
            $products ="active-side-bar";
        }
        else
        if($_GET['p'] == 'orders')
        {
            $orders ="active-side-bar";
        }
        
        
        
    }
    
    
?>

<div class="sidebar-container">
    <div class="sidebar-navigation-container">
        <div class="sidebar-navigation">
            <ul class="navigation-content-container">
                <li class="navigation-list-item">
                    <a href="dashboard.php?p=products" class="list-item-content <?php echo $products; ?>">
                        <i class="fa fa-user sidebarIcon" aria-hidden="true"></i>
                        <span class="list-item-text">Products</span>
                    </a>
                </li>
                
                <li class="navigation-list-item">
                    <a href="dashboard.php?p=order" class="list-item-content <?php echo $orders; ?>">
                        <i class="fas fa-shopping-cart sidebarIcon" aria-hidden="true"></i>
                        <span class="list-item-text">Orders</span>
                    </a>
                </li>
                
                
            </ul>
            
        </div>
    </div>
</div>