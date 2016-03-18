var myScroll;

function pullUpAction () {	//上拉加载更多数据

	getShop();

	myScroll.refresh();		// Remember to refresh when contents are loaded (ie: on ajax completion)

}

function loaded() {
	var pullUpEl = document.getElementById('pullUp');
	var pullUpOffset = pullUpEl.offsetHeight;

	myScroll = new iScroll('wrapper', {
		// useTransition: true,
		hScroll: false,
		hScrollbar: false,
		vScrollbar: false,
		// topOffset: pullUpOffset,
		onRefresh: function () {
			if (pullUpEl.className.match('loading')) {
				pullUpEl.className = '';
				pullUpEl.querySelector('.pullUpLabel').innerHTML = '上拉加载更多';
			}
		},
		onScrollMove: function () {
			if (this.y < (this.maxScrollY - 5) && !pullUpEl.className.match('flip')) {
				pullUpEl.className = 'flip';
				// pullUpEl.querySelector('.pullUpLabel').innerHTML = '松开加载';
				this.maxScrollY = this.maxScrollY;
			} else if (this.y > (this.maxScrollY + 5) && pullUpEl.className.match('flip')) {
				pullUpEl.className = '';
				// pullUpEl.querySelector('.pullUpLabel').innerHTML = '上拉加载更多';
				this.maxScrollY = pullUpOffset;
			}
		},
		onScrollEnd: function () {
			if (pullUpEl.className.match('flip')) {
				pullUpEl.className = 'loading';
				pullUpEl.querySelector('.pullUpLabel').innerHTML = '加载中';
				pullUpAction();	// Execute custom function (ajax call?)
			}
		}
	});
	// setTimeout(function () { document.getElementById('wrapper').style.left = '0'; }, 300);
}

document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);

document.addEventListener('DOMContentLoaded', function () { setTimeout(loaded, 200); }, false);