
$(document).ready(function() {
    $('.actionBar').delay(8000).fadeOut();
});

function ClosePopup() 
{
    document.getElementById("PopupParent").style.display = "none";
    $("body").css("overflow", "");
}


$(document).on('click', '.addVariants', function(event){
    
    document.getElementById("PopupParent").style.display = "block";
    document.getElementById("contentReceived").innerHTML = "Loading...";
    
    var id=$(this).attr("data-id"); 
    
    $.ajax({
        type: "GET",
        url: "ajex-events.php?q=addVariants&product_id="+id,
        success: function(msg) {
            
            document.getElementById("contentReceived").innerHTML = msg;
            
        }
    });
    
});

$(document).on('click', '.editVariation', function(event){
    
    document.getElementById("PopupParent").style.display = "block";
    document.getElementById("contentReceived").innerHTML = "Loading...";
    
    var id=$(this).attr("data-id"); 
    
    $.ajax({
        type: "GET",
        url: "ajex-events.php?q=editVariants&id="+id,
        success: function(msg) {
            
            document.getElementById("contentReceived").innerHTML = msg;
            
        }
    });
    
});


$(document).on('click', '.showOrderDetail', function(event){
    
    document.getElementById("PopupParent").style.display = "block";
    document.getElementById("contentReceived").innerHTML = "Loading...";
    
    var id=$(this).attr("data-id"); 
    
    $.ajax({
        type: "GET",
        url: "ajex-events.php?q=showOrderDetail&id="+id,
        success: function(msg) {
            
            document.getElementById("contentReceived").innerHTML = msg;
            
        }
    });
    
});



$(document).on('click', '.deleteVariation', function(event){
    
    var id=$(this).attr("data-id"); 
    
    $.ajax({
        type: "GET",
        url: "process.php?action=deleteVariation&id="+id,
        success: function(msg) {
            
            // Remove the parent div
            $('.variation_row[data-id="' + id + '"]').remove();
            
        }
    });
    
});






$(document).on('click', '.addAnotherValue', function(event){
     
    $('#contentReceivedAddAnotherValue').append('<div class="select-wrapper"> <label for="subject-id">Option Value</label><input type="text" class="main-content-input" name="option_value[]" style="width: 96%;"><span class="fas fa-trash deleteAnotherValue" style="float: right;margin-top: -30px;"></span></div>');
    
});


$(document).on('click', '.deleteAnotherValue', function(event){
    
    $(this).parent('.select-wrapper').remove();
    
});


$(document).on('click', '.updateProduct', function(event){
    
    $('#updateProduct').submit();

});





$(document).on('click', '.showSubCategory', function(event){
    
    var id=$(this).attr("data-id"); 
    
    
    $.ajax({
        type: "GET",
        url: "ajex-events.php?q=showSubCategory&id="+id,
        success: function(msg) {
            
            $(".contentReceivedCategory").html(msg); 
            
        }
    });  
    
});

$(document).on('click', '.selectCategory', function(event){
    
    event.preventDefault();
    
    var title=$(this).attr("data-title"); 
    var id=$(this).attr("data-id"); 
    
    
    $(".sidebar-select-input-popup").val(title);  
    
    $(".category_id").val(id);  
      
      
    // var category_id=$(".category_id").val();
    // var product_id=$(".product_id").val();
    
    // $.ajax({
    //     type: "GET",
    //     url: "process.php?action=updateCategory&id="+category_id+"&product_id="+product_id,
    //     success: function(msg) {
            
    //     }
    // });  
    
});



$(document).on('submit', '.addVariantsForm', function(event){
    
    event.preventDefault();
    
    $.ajax({
        type: "POST",
        data: $('.addVariantsForm').serialize(),
        url: "process.php?action=addVariants",
        success: function(msg) {
            
            location.reload(true);
            
        }
    });    
      
});




$(document).on('submit', '.editVariantsForm', function(event){
    
    event.preventDefault();
    
    $.ajax({
        type: "POST",
        data: $('.editVariantsForm').serialize(),
        url: "process.php?action=editVariants",
        success: function(msg) {
            
            location.reload(true);
            
        }
    });    
      
});


$(document).on('change', '.selectStatus', function(event){
    
    var selectedOption = $(this).val(); // Get the selected value
    
    
    $(".statusValue").val(selectedOption);
    console.log(selectedOption);
      
});


$(document).on('change', '.fileInput', function(event){
    
    $('#uploadForm').submit();
    
});


$(document).on('submit', '#uploadForm', function(event){
    
    event.preventDefault();
    
    $.ajax({
        type: 'POST',
        url: 'process.php?action=attachment',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData: false,
        success: function(msg) {
            $('.uploadedImages').prepend(msg)
        }
    });    
    
    
      
});







$('.popup-search-result').hide();
$('.select-box').click(function () {
    $('.popup-search-result').show();
});
$(document).click(function (e) {
    var target = e.target;
    if (!jQuery(target).is('.select-box') && !jQuery(target).parents().is('.select-box')) {
        jQuery(".popup-search-result").hide();
    }
});




