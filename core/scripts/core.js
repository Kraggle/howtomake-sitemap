import { jQuery as $ } from './src/jquery-3.5.1-min.js';

$(() => {

	// time.build($('.ks-progress-time'));
	// feed.build($('.ks-feed-text'));

	// feed.add('Some message to append to the log!');
	// feed.add('Another thing to just test it works.');
	// feed.add('We then want a really long line to see what it looks like when it flows past one single line of text maybe just a few more words and a really long one like rawrrr, lol!');

	// setTimeout(() => {
	// 	feed.add('A line that appears later and the first one should hide.');
	// }, 5000);

	// setTimeout(() => {
	// 	feed.add('Same again this should have hidden the first 2 lines.');
	// }, 10000);

	$('.ks-button[count]').addClass('doMe');

	$('.ks-button').on('click', function() {
		const count = $(this).attr('count'),
			action = $(this).attr('action'),
			other = $(this).attr('other'),
			type = $(this).attr('type');

		if (type) {
			if (!$(this).hasClass('doMe'))
				return;

			const inputs = {};
			$(this).parents('.ks-setting-box').find('input').each(function() {
				inputs[$(this).attr('name')] = $(this).is(':checked');
			});

			ajax(action, {
				inputs
			}, data => {
				if (data.success) {
					$(this).removeClass('doMe');

					if (data.action && data.ids && data.ids.length) {
						const total = data.ids.length;

						// console.log(data);

						const loopCall = () => {
							time.start();

							if (!data.ids.length) {
								$(this).removeClass('doMe');
								$(`#${other}`).addClass('doMe');
								doProgress(10, 10);
								time.reset();
								return;
							}


							ajax(data.action, {
								ids: data.ids.splice(0, data.loop)
							}, result => {
								if (result.success) {
									doProgress(total, data.ids.length);
									time.left(data.ids.length).end();
									loopCall();
								}
							});
						};

						loopCall();
					}
				}
			});

		} else if (count) {
			ajax(action, data => {
				$(`#${count}`).val(data.count);

				if (data.count > 0) {
					$(this).removeClass('doMe');
					$(`#${other}`).addClass('doMe');
				}

				if (data.ids)
					$(`#${other}`).data('ids', data.ids);
			});

		} else {
			if (!$(this).hasClass('doMe'))
				return;

			const loop = parseInt($(this).attr('repeat') || 0),
				ids = $(this).data('ids');

			if (loop && ids.length) {

				const total = ids.length;

				const loopCall = () => {
					time.start();
					if (!ids.length) {
						$(this).removeClass('doMe');
						$(`#${other}`).addClass('doMe');
						doProgress(10, 10);
						time.reset();
						return;
					}

					ajax(action, {
						ids: ids.splice(0, loop)
					}, data => {
						if (data.success) {
							$(`#${$(`#${other}`).attr('count')}`).val(ids.length);

							doProgress(total, ids.length);
							time.left(ids.length).end();
							loopCall();
						}
					});
				};

				loopCall();
			} else {

				ajax(action, data => {

					if (data.success) {
						$(this).removeClass('doMe');
						$(`#${other}`).addClass('doMe');

						$(`#${$(`#${other}`).attr('count')}`).val(data.count);
					}
				});
			}
		}
	});

	$('input[type="checkbox"').on('change', function() {
		$(this).parents('.ks-setting-box').find('.ks-button').addClass('doMe');
	});
});

function doProgress(total, left) {
	const current = total - left,
		goto = current * 100 / total;
	$('.ks-progress-back').css('width', `${goto}%`);
	$('.ks-progress-number').text(!goto ? '' : goto.toFixed(2) + '%');
}

function ajax(action) {
	const a = arguments;

	let data = {},
		callback = function() { };

	if (typeof a[1] === 'object') {
		data = a[1];

		if (typeof a[2] === 'function')
			callback = a[2];

	} else if (typeof a[1] === 'function')
		callback = a[1];

	// console.log(data);

	$.ajax({
		url: '/wp-admin/admin-ajax.php',
		data: {
			action,
			nonce: $('.ks-box').data('nonce'),
			data
		}
	}).done(result => {
		result = JSON.parse(result.replace(/0$/, ''));
		// console.log(result);

		callback(result);
	});
}

const feed = {
	element: null,
	count: 0,

	build(element) {
		this.element = element;
	},

	clear() {
		if (!$(this.element).length) return;
		$(this.element).html('');
	},

	add(msg) {
		if (!$(this.element).length) return;
		this.count++;
		$(this.element).append(`<p>${msg}</p>`);
		const h = $(this.element).outerHeight(true);
		if (h > 84)
			$(this.element).css('top', `-${h - 84}px`);
	}
};

const time = {
	startTime: null,
	endTime: null,
	average: [],
	element: null,
	remain: 0,

	build(element) {
		this.element = element;
		return this;
	},

	reset() {
		this.average = [];
		$(this.element).html('');
		return this;
	},

	start() {
		this.startTime = new Date();
		return this;
	},

	end() {
		this.endTime = new Date();
		this.average.push(this.endTime - this.startTime);
		this.update();
		return this;
	},

	update() {
		if (!this.element || this.average.length < 3) return;
		const avg = (this.average.reduce((a, b) => a + b, 0) / this.average.length) || 0;
		const remain = avg * this.remain;
		$(this.element).html(this.time(remain));
	},

	time(ms) {
		let seconds = (ms / 1000).toFixed(0),
			minutes = Math.floor(seconds / 60),
			hours = "";
		if (minutes > 59) {
			hours = Math.floor(minutes / 60);
			minutes = minutes - (hours * 60);
			minutes = (minutes >= 10) ? minutes : "0" + minutes;
		}

		seconds = Math.floor(seconds % 60);

		let result = hours ? `${hours} hours` : '';
		if (minutes && seconds) result += ` ${minutes} minutes and ${seconds} seconds`;
		else if (hours && seconds) result += ` and ${seconds} seconds`;
		else if (hours && minutes) result += ` and ${minutes} minutes`;
		else if (minutes) result += `${minutes} minutes`;
		else if (seconds) result += `${seconds} seconds`;
		result += ' estimated';
		return result;
	},

	left(left) {
		this.remain = left;
		return this;
	}
}