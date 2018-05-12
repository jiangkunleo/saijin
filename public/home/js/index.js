
$(function(){
  //导航
  $('.nav').on('click','li',function(){
    $(this).addClass('active').siblings().removeClass('active');
  });

  //加载完成后执行对选项卡默认位置
  $(document).ready(function(){ 
    $('.gather .gather-title ul li:first-child').attr('class','active');
    $('.main-box .box-item:first-child').css('display','block');
    $('.main-box .box-item:first-child').siblings().css('display','none');
    add_url();
    show_li5();
  });

  //给第二个更多链接进行更改
  function add_url(){
    url_1 = window.location.href;
    url_2 = url_1.substring(0,url_1.length-10)+"house.html";
    $('.credit2 .gather-title a').attr('href',url_2);
  }

  //每个选项卡子分类信息最多显示5条
  function show_li5() {
    $('.box-item .gather-item li:nth-child(5)').nextAll().remove();
  }

  //主体内容选项
  $('.credit1 .gather-title').on('mouseenter','li',function(){
    $(this).addClass('active').siblings().removeClass('active');
    var selected = '.credit1 .box-item:eq(' + $(this).index() + ')';
    $(selected).css('display','block');
    $(selected).siblings().css('display','none');
    show_li5();
  });

  $('.credit2 .gather-title').on('mouseenter','li',function(){
    $(this).addClass('active').siblings().removeClass('active');
    var selected = '.credit2 .box-item:eq(' + $(this).index() + ')';
    $(selected).css('display','block');
    $(selected).siblings().css('display','none');
    show_li5();
  });

  $('.credit3 .gather-title').on('mouseenter','li',function(){
    $(this).addClass('active').siblings().removeClass('active');
    var selected = '.credit3 .box-item:eq(' + $(this).index() + ')';
    $(selected).css('display','block');
    $(selected).siblings().css('display','none');
    show_li5();
  });


  $('.strategy .gather-title').on('mouseenter','li',function(){
    $(this).addClass('active').siblings().removeClass('active');
    var selected = '.strategy .box-item:eq(' + $(this).index() + ')';
    $(selected).css('display','block');
    $(selected).siblings().css('display','none');
  });

  $('.ask-title').on('mouseenter','li',function(){
    $(this).addClass('active').siblings().removeClass('active');
    var selected = '.ask-content:eq(' + $(this).index() + ')';
    $(selected).css('display','block');
    $(selected).siblings('.ask-content').css('display','none');
  });
});


//联系我们
$(window).ready(function(){
  $('.contact-slider').css('height',$('.contact-box').height() - 50);
});
$(function(){
  $('.contact-slider').on('click','li',function(){
    $(this).addClass('active').siblings().removeClass('active');
    var selected = '.contact-item:eq(' + $(this).index() + ')';
    $(selected).css('display','block');
    $(selected).siblings().css('display','none');
  });
})


//协议弹出框
$(function(){
  $('.quick-item span a').click(function(){
    $('.agreement').css('display','block');
    $('.agreement .close').one('click',function(){
      $('.agreement').css('display','none')
    })
  })
})

//问答
// $(function(){
//   $('.wenda-nav').on('click','li',function(){
//     $(this).addClass('active').siblings().removeClass('active');
//     var selected = '.wenda-content-list:eq(' + $(this).index() + ')';
//     $(selected).css('display','block');
//     $(selected).siblings().css('display','none');
//   })
// })




