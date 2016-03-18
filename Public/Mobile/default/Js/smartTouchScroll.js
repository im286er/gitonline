// 阻止弹窗蒙板下页面的滚动
function smartTouchScroll() {
	var startY, allow, body = document.body;

    function isAllow(target, r, adjust) {
        adjust = adjust || 0;

        r.down = r.down || (target.scrollTop > 0);
        var scrollHeight = target !== body ? target.scrollHeight + adjust: target.clientHeight;

        var $target = $(target);
        if ($target.css('overflow-y') !== 'hidden') {
            r.up   = r.up || (target.scrollTop < scrollHeight - target.clientHeight);
        }

        if (!r.down || !r.up) {
            adjust = $target.css('margin-top');
            target = target.offsetParent;
            if (!!target) {
                return isAllow(target, r, adjust);
            }
        }
        return r;
    }

    document.addEventListener('touchstart', function(event) {
        allow = body.clientHeight > window.innerHeight
                    ? {up: true, down: true}
                    : isAllow(event.target, {});
        startY = event.pageY;
    });

    document.addEventListener('touchmove', function(event) {
        var up = (event.pageY < startY), down = (event.pageY > startY);
        startY = event.pageY;
        if ((up && allow.up) || (down && allow.down)) {
            event.stopPropagation();
        } else {
            event.preventDefault();
        }
    });
}