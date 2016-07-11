$(function(){
	$(".evaluate_all_fontul li").click(function(){
        $(this).siblings().removeClass('licur');
        $(this).addClass('licur');
			$(".evaluate_all_text").slideDown(800)
		});
	$(".star2 li").click(function(){
        $(this).parents('.evaluate_buy_font').siblings('.evaluate_buy_rice').slideDown(800);

		});
})



//星星评价

$(document).ready(function(){
    var stepW = 30;
    var description = new Array("非常差，回去再练练","真的是差，都不忍心说你了","一般，还过得去吧","很好，是我想要的东西","太完美了");
    var stars = $(".star > li");
    var descriptionTemp;
    $(".current-rating").css("width",0);
    stars.each(function(i){
        $(stars[i]).click(function(e){
            var n = i+1;
            $(".current-rating").css({"width":stepW*n});
            descriptionTemp = description[i];
            $(this).find('a').blur();
            return stopDefault(e);
            return descriptionTemp;
        });
    });
    stars.each(function(i){
        $(stars[i]).hover(
            function(){
                $(".description").text(description[i]);
            },
            function(){
                if(descriptionTemp != null)
                    $(".description").text(descriptionTemp);
                else 
                    $(".description").text(" ");
            }
        );
    });
});
function stopDefault(e){
    if(e && e.preventDefault)
           e.preventDefault();
    else
           window.event.returnValue = false;
    return false;
};

/*肉沫茄子*/
$(function(){
    var stepW2 = 28;
    var starss = $(".star2 > li");
    var descriptionTemp2;
    var n;
    starss.each(function(i){
        $(starss[i]).click(function(e){
            n = $(this).children('a').attr('title');
           $(this).parents().siblings('.current-rating2').css({"width":stepW2*n});


        });
    });

});
function stopDefault(e){
    if(e && e.preventDefault)
           e.preventDefault();
    else
           window.event.returnValue = false;
    return false;
};



