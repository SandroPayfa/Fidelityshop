jQuery(document).ready(function() {
	$('[data-toggle="tooltip"]').tooltip();
	$('.toast').toast('show');

	$(document).on('click', '.sidebar-toggler', function() {
		$(this).toggleClass('toggled');
		if($(this).hasClass('toggled')) {
			$('#sidebar-collapse').animate({'width': '0'}, 300);
			$('body > .main').animate({'max-width': '100%'}, 300);
		} else {
			$('#sidebar-collapse').animate({'width': '25%'}, 300);
			$('body > .main').animate({'max-width': '75%'}, 300);
		}
	});


});

$(function () {
    $('#datetimepicker1').datetimepicker({
        inline: true,
        sideBySide: true,
        format: 'MM/DD/YYYY'
    });
    $('#datetimepicker2').datetimepicker({
        inline: true,
        sideBySide: true,
        format: 'MM/DD/YYYY'
    });
    if($(".no-records-found")){
    	$(".no-records-found td").html("Dima raja")
	}
});