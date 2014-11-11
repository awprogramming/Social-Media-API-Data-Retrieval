$(function(){

	$("#getJSON").click(function(){
	

		$.ajax('getJSON.php',   
	        {
	             type: 'GET',
	             data:{id:$("#twitterid").val()},
	             cache: false,
	             beforeSend: function() {
    				$('#response').html("<img src='ajax-loader.gif' />");
 				 },
	             success: function (data) {printJSON($.parseJSON(data));},
	             error: function () {alert('Error receiving JSON');}
		});
	});

	var printJSON = function(jsonResponse)
	{
		$("#response").html(JSON.stringify(jsonResponse));
	}
});