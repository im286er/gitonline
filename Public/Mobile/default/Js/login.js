var tapReady = false;
// 兼容pc click事件
var touch = 'click';
if (window.orientation == undefined) {
  tapReady = true;
  $('.ty-login').width(280);
} else {
  touch = 'tap';
}

// 屏幕滚动时设置tap状态，用于阻止屏幕滚动时误触发tap事件
$(document.body).on('touchstart', function(){
  tapReady = true;
});
$(document.body).on('touchmove', function(){
  tapReady = false;
});


// 跳转链接
$('.j-call-url').on(touch, function(){
  if (tapReady){
    var href = $(this).data('href');
    if (href) {
      location.href = href;
    }
  }
});

// 表单提交
$('.j_register_btn').on(touch, function(){
  var target = $(this);
  if (tapReady && !target.hasClass('disable')) {
    target.addClass('disable');
    var formState = true;
    var formParam = {};
    $('.mod-wrap input').each(function(){
      if (formState) {
        //var inputName = $(this).attr('name');
        var inputValue = $(this).val();
        //formParam[inputName] = inputValue;
        var errText = $(this).attr('placeholder') || '完整填写表单';
        if ($(this).attr('required')) {
          if (inputValue == '') {
            formState = false;
            alert('请输入' + errText);
          }
        }
      }
    });
    // 验证手机号码
    if (formState && $('.j-input-phone').length) {
      var phoneNum = + $('.j-input-phone').val();
      if (isNaN(phoneNum)) {
        formState = false;
        alert('请正确输入手机号码！');
      } else {
        var minPNum = 130 * 100000000;
        var maxPNum = 200 * 100000000;
        if (phoneNum < minPNum || phoneNum > maxPNum) {
          formState = false;
          alert('请正确输入手机号码！');
        }
      }
    }
    if (formState) {
      // 提交表单
      var postUrl = target.data('url');
      if (postUrl) {
        $.ajax({
          type: 'POST',
          url: postUrl,
          data: formParam,
          dataType: 'json',
          timeout: 8000,
          success: function(response){
            // 表单处理完成
            setTimeout(function(){
              target.removeClass('disable');
            }, 1500);
            // 表单处理成功
            alert('操作成功！');
          },
          error: function(xhr, type){
            alert(callScreen, '服务器连接失败，请稍后再试！');
          }
        });
      }
    } else {
      setTimeout(function(){
        target.removeClass('disable');
      }, 1500);
    }
  }
});

// 动态验证码
$('#j-dypwd').click( function(){
  var target = $(this);
  if (tapReady && !target.hasClass('disable')) {
    target.addClass('disable');
    var formState = true;
    var formParam = {};
    // 验证手机号码
    var phoneNum = + $('.j-input-phone').val();
    if (isNaN(phoneNum)) {
      formState = false;
      alert('请正确输入手机号码！');
    } else {
      var minPNum = 130 * 100000000;
      var maxPNum = 200 * 100000000;
      if (phoneNum < minPNum || phoneNum > maxPNum) {
        formState = false;
        alert('请正确输入手机号码！');
      }
    }
    if (formState) {
      var postUrl = target.data('url');
      if (postUrl) {
        formParam['mobile'] = phoneNum;
		formParam['verify'] = target.data('type');
        $.ajax({
          type: 'POST',
          url: postUrl,
          data: formParam,
          dataType: 'json',
          timeout: 8000,
          success: function(response){
			  if(response.errcode>0){alert(response.errmsg); target.html('发送'); target.removeClass('disable');return false;}
            // 表单处理完成
            var targetHtml = target.html();
            var timeNum = 60;
            function timeout(){
              target.html(timeNum + 'S');
              timeNum --;
              if (timeNum > -1) {
                setTimeout(timeout, 1000);
              } else {
                target.html('重新发送');
                target.removeClass('disable');
              }
            }
            timeout();
          },
          error: function(xhr, type){
            alert(callScreen, '服务器连接失败，请稍后再试！');
          }
        });
      }
    } else {
      setTimeout(function(){
        target.removeClass('disable');
      }, 1500);
    }
  }
});






