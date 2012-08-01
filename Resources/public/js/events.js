
$(document).ready(function() {
   
    $('#more-feed-items').live('click', function(e) {
       e.preventDefault();
       $('#feed .ccetc-item-container').show();
       $(this).hide();
    });     

});
