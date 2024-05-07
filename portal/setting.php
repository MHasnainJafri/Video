<?php 
if(isset($_SESSION[PRE_FIX.'id']))
{       
    
        $url=$baseurl . 'showSettings';
        $data =array();
        
        $json_data=@curl_request($data,$url);
    
        ?>

        <div class="qr-content">
            <div class="qr-page-content">
                <div class="qr-page zeropadding">
                    <div class="qr-content-area">
                        <div class="qr-row">
                            <div class="qr-el">

                                <div class="page-title">
                                    <h2>Settings</h2>
                                    <div class="head-area">
                                    </div>
                                </div>
                                
                                <div class="right" style="padding: 10px 0;">
                                    <button onclick="addSetting();" class="com-button com-submit-button com-button--large com-button--default">
                                        <div class="com-submit-button__content"><span>Add Setting</span></div>
                                    </button>
                                </div>
                                <!--start of datatable here-->


                                <table id="table_view" class="display" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Type</th>
                                            <th>Value</th>
                                            <th>Created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <?php 
                                    
                                        if(is_array($json_data['msg']) || is_object($json_data['msg']))
                                        {
                                            foreach ($json_data['msg'] as $singleRow): 
                                                   
                                                ?>
                                                    <tr>
                                                        <td><?php echo $singleRow['Setting']['id']; ?></td>
                                                        <td>
                                                            <?php
                                                                if($singleRow['Setting']['type']=="video_percentage_watch")
                                                                {
                                                                    echo "Video Watch Limit";
                                                                }
                                                                else
                                                                if($singleRow['Setting']['type']=="reward_video_percentage_watch")
                                                                {
                                                                    echo "Coins";
                                                                }
                                                                else
                                                                if($singleRow['Setting']['type']=="referral_coin")
                                                                {
                                                                    echo "Referral Coin Reward";
                                                                }
                                                                else
                                                                if($singleRow['Setting']['type']=="max_creator_earn_point_per_video")
                                                                {
                                                                    echo "Maximum coin Limit creator can earn";
                                                                }
                                                                else
                                                                if($singleRow['Setting']['type']=="creator_earn_point_per_video")
                                                                {
                                                                    echo "Creator earn per video";
                                                                }
                                                                else
                                                                if($singleRow['Setting']['type']=="show_advert_after")
                                                                {
                                                                    echo "Show advert After";
                                                                }
                                                                else
                                                                if($singleRow['Setting']['type']=="add_type")
                                                                {
                                                                    echo "Ad Type";
                                                                }
                                                                else
                                                                if($singleRow['Setting']['type']=="coin_worth")
                                                                {
                                                                    echo "Coins equivalent to dollar";
                                                                }
                                                                else
                                                                if($singleRow['Setting']['type']=="video_compression")
                                                                {
                                                                    echo "Video Compression";
                                                                }
                                                                
                                                                
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $singleRow['Setting']['value']; ?>
                                                        </td>
                                                        <td>
                                                           <?php echo $singleRow['Setting']['created']; ?>
                                                        </td>
                                                        <td>
                                                            <div class="more">
                                                                <button id="more-btn" class="more-btn">
                                                                    <span class="more-dot"></span>
                                                                    <span class="more-dot"></span>
                                                                    <span class="more-dot"></span>
                                                                </button>
                                                                <div class="more-menu">
                                                                    <div class="more-menu-caret">
                                                                        <div class="more-menu-caret-outer"></div>
                                                                        <div class="more-menu-caret-inner"></div>
                                                                    </div>
                                                                    <ul class="more-menu-items" tabindex="-1" role="menu" aria-labelledby="more-btn" aria-hidden="true">
                                                                        <!--<li class="more-menu-item" onclick="editSetting('<?php echo $singleRow['Setting']['id']; ?>');" role="presentation">-->
                                                                        <!--    <button type="button" class="more-menu-btn" role="menuitem">Edit</button>-->
                                                                        <!--</li>-->
                                                                        <li class="more-menu-item" role="presentation">
                                                                            <a href="process.php?action=deleteSetting&id=<?php echo $singleRow['Setting']['id']; ?>">
                                                                                <button type="button" class="more-menu-btn" role="menuitem">Delete</button>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        
                                                        
                                                    </tr>
                                                <?php 
                                                
                                            endforeach; 
                                        }
                                        
                                    ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>ID</th>
                                            <th>Type</th>
                                            <th>Value</th>
                                            <th>Created</th>
                                            <th>Action</th>
                                        </tr>
                                    </tfoot>
                                </table>


                            </div>
                        </div>
                    </div>
                </div>

            </div>
        
            <script>
                $(document).ready(function () {
                    $('#table_view').DataTable({
                            "pageLength": 100
                        }
                    );
                });
            </script>
        </div>
    <?php
    
        
} 
else 
{
	
	echo "<script>window.location='index.php'</script>";
    die;
    
} 

?>