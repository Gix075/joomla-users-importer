$(document).on('ready', function(){
	getDocumentation();
	postFormData();
});

function postFormData() {
	$('#userImporterFormSend').on('click', function (e) {
		e.preventDefault();
		$('#messageBox').removeClass('alert-danger');
		$('#messageBox').removeClass('alert-success');
		$('#messageBox').addClass('alert alert-info');
		$('#messageBox').text('Elaboro Richiesta');
		
		var resultMessage = "";
		
		$.ajax({
			url: 'php/ws.users-importer.php',
			type: 'POST',
			data: $('#userImporterForm').serialize(),
			dataType: 'json',
			success: function(data) {
				setTimeout(function() {
					switch(data.result) {
						case "fail":
							resultMessage += "<h4>Error</h4>";
							$('#messageBox').removeClass('alert-info');
							$('#messageBox').addClass('alert-danger');
							break;
						case "success":
							resultMessage += "<h4>Success</h4>";
							$('#messageBox').removeClass('alert-info');
							$('#messageBox').addClass('alert-success');
							break;	
					}
						
					resultMessage += "<p>" + data.message + "</p>";	
					resultMessage += '<p>Here you can find the <a href="' + data.logs + '" title="Log File" target="_blank">LOG-FILE</a></p>';
					$('#messageBox').html(resultMessage);
						
				},1000);
			},
			error: function() {
				console.log('TOOL AJAXERROR');
			}
		});
		
	});
}

function getDocumentation() {
	$.ajax({
		url: 'docs.html',
		success: function(data) {
			$('#documentation').html(data);
		},
		error: function() {
			console.log('DOCUMENTATION AJAXERROR!');
		}
	});
}
