/*! 
 * ************************************************************************************ 
 *  Joomla Users Importer | Import users from CSV file into Joomla 3 database 
 *  Version 3.0.0 - Date: 09/09/2021 
 *  HomePage: https://github.com/Gix075/joomla-users-importer#readme 
 * ************************************************************************************ 
*/ 


$(document).on('ready', function(){
	getDocumentation();
	getModal();
});

function postFormData() {
	$('#startImport').on('click', function (e) {
		e.preventDefault();
		$('#modalConfirm').modal('hide');
		$('#messageBox').removeClass('alert-danger');
		$('#messageBox').removeClass('alert-warning');
		$('#messageBox').removeClass('alert-success');
		$('#messageBox').addClass('alert alert-info margin-bottom');
		$('#messageBox').text('Elaboro Richiesta');
		
		var resultMessage = "";
		
		$.ajax({
			url: 'php/ws.usersimporter.php',
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
						case "warning":
							resultMessage += "<h4>Warning</h4>";
							$('#messageBox').removeClass('alert-info');
							$('#messageBox').addClass('alert-warning');
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
				resultMessage += "<h4>Error</h4>";
				resultMessage += "<p>Ajax Error! Verify your data!</p>";
				$('#messageBox').removeClass('alert-info');
				$('#messageBox').addClass('alert-danger');
				$('#messageBox').html(resultMessage);
				console.log('TOOL AJAXERROR');
			}
		});
		
	});
}

//startImport //userImporterFormSend
function getModal() {
	$('#userImporterFormSend').on('click', function(e) {
		e.preventDefault();
		var dbhost = $('.form-control[name="dbhost"]').val(),
			dbname = $('.form-control[name="dbname"]').val(),
			dbprefix = $('.form-control[name="dbprefix"]').val(),
			dbusername = $('.form-control[name="dbusername"]').val(),
			dbpassword = $('.form-control[name="dbpassword"]').val(),
			usersgroup = $('.form-control[name="usersgroup"]').val(),
			usersblocked = ($('.form-control[name="usersblocked"]').val() === 1) ? "Yes" : "No",
			usersactivation = ($('.form-control[name="usersactivation"]').val() === 1) ? "Yes" : "No",
			userssendmail = ($('.form-control[name="userssendmail"]').val() === 1) ? "Yes" : "No",
			usersreset = ($('.form-control[name="usersreset"]').val() === 1) ? "Yes" : "No",
			csv = $('.form-control[name="usersfile"]').val(),
			groupname = "",
			markup = "";
			
			switch(usersgroup) {
				case "0":
					groupname = "None (no group assignement)";
					break;
				case "2":
					groupname = "Registred";
					break;	
				case "3":
					groupname = "Author";
					break;	
				case "4":
					groupname = "Editor";
					break;	
				case "5":
					groupname = "Publisher";
					break;	
				case "6":
					groupname = "Manager";
					break;	
				case "7":
					groupname = "Administrator";
					break;
				case "8":
					groupname = "Super User";
					break;
				case "9":
					groupname = "Guest";
					break;		
					
			}
			
			markup += '<p>You are importing users from file:<br>';
			markup += '<strong>csv/' + csv + '</strong></p>';
			
			markup +="<p>You are using the following settings</p>";
			
			markup += '<h4>Database</h4>';
			markup += '<div class="modal-confirm_index-item"><strong>DB Host:</strong> ' + dbhost + '</div>';
			markup += '<div class="modal-confirm_index-item"><strong>DB Name:</strong> ' + dbname + '</div>';
			markup += '<div class="modal-confirm_index-item"><strong>DB Prefix:</strong> ' + dbprefix + '_</div>';
			markup += '<div class="modal-confirm_index-item"><strong>DB Usrname:</strong> ' + dbusername + '</div>';
			markup += '<div class="modal-confirm_index-item"><strong>DB Password:</strong> ' + dbpassword + '</div>';
			
			markup += '<h4>Users</h4>';
			markup += '<div class="modal-confirm_index-item"><strong>Users Group:</strong> ' + groupname + '</div>';
			markup += '<div class="modal-confirm_index-item"><strong>Users Blocked:</strong> ' + usersblocked + '</div>';
			markup += '<div class="modal-confirm_index-item"><strong>Users Manual Activation:</strong> ' + usersactivation + '</div>';
			markup += '<div class="modal-confirm_index-item"><strong>Users Recive Email:</strong> ' + userssendmail + '</div>';
			markup += '<div class="modal-confirm_index-item"><strong>Users Require Password Reset (at first login):</strong> ' + usersreset + '</div>';
			
			markup +="<p>If all settings are ok, you can start import process.</p>";
			
			$("#modalContent").html(markup);
			$("#modalConfirm").modal();
			postFormData();
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
