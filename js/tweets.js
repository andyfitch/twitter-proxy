$(function(){

	$.ajax({
		url: 'get_tweets.php',
		type: 'GET',
		success: function(response) {

			if (typeof $('.tweets-container') !== 'undefined') {

				if (typeof response === 'string') {
					response = $.parseJSON(response);
				}

				var $tweets = $('<ul></ul>');
				$.each(response, function(i, obj) {
					$tweets.append('<li>' + obj.text + '</li>');
				});

				$('.tweets-container').html($tweets);
			}
		}
	});
});