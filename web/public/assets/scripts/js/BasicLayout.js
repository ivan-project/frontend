var update_funs=[];
var scroll_funs=[];
var window_w=0;
var window_h=0;
var resp_timeout;
function busy(state) {
  var blocker = $('#ajax-block');
  if(state) {
    blocker.css({'display':'block'});
    blocker.addClass('anim');
    setTimeout(function() {
      blocker.addClass('show');
    },10);
  } else {
    blocker.removeClass('show');
    setTimeout(function() {
      blocker.removeClass('anim show');
      blocker.css({'display':'none'});
    },150);
  }
}
function responsiveAddFun(register_fun) {
  update_funs.push(register_fun);
}
function scrollAddFun(register_fun) {
  scroll_funs.push(register_fun);
}
function scrollRemoveFun(remove_fun) {
  scroll_funs = $.grep(scroll_funs, function(value) {
    return value != remove_fun;
  });
}
function resizeListener() {
  resizeListenerFinal();
  clearTimeout(resp_timeout);
  resp_timeout = setTimeout(resizeListenerFinal, 50);
}
function resizeListenerFinal() {
  window_w = $(window).width();
  window_h = $(window).height();
  $.each(update_funs,function(key,val) {
    val();
  });
}
function scrollListener() {
  $.each(scroll_funs,function(key,val) {
    val();
  });
}
function scrollMouse() {
  $('html,body').stop();
}
function isTouchDevice() {
  return (typeof(window.ontouchstart) != 'undefined') ? true : false;
}
$(document).ready(function() {
  $(window).scroll(scrollListener);
  $(window).resize(resizeListener);
  if(window.addEventListener) {
    document.addEventListener('DOMMouseScroll', scrollMouse, false);
  }
  document.onmousewheel = scrollMouse;

  initReloads();
  initPanelScroll();
  setTimeout(initReloads,1000);
});
function initReloads() {
  resizeListener();
  scrollListener();

  initPostRemote();
  initDataRemote();
  initRadios();
  initFileBrowse();
  $('#page-holder *[autofocus]').first().focus();
}
// CUSTOMS ####################################################

// REMOTE #####################################################
var remote_block = false;
var reloadByIframe;
function redirect(redirect, unblock) {
  //window.location.href=redirect;
  if(unblock) remote_block = false;
  loadContents(redirect);
}
function initPostRemote() {
  var panel = $('#panel');
  var panel_contents = panel.find('> .inside > .contents');
  $('form[data-remote]:not(form[data-remote="true"])').each(function() {
    var th = $(this);
    th.attr('data-remote','true');
    th.attr('target','remote_iframe');
    th.append('<input type="hidden" name="data_remote_form" value="true"/>');
    th.submit(function(e) {
      if(!remote_block) {
        remote_block = true;
        reloadByIframe = function(contents) {
          preUpdateContents(contents, null);
          remoteFormFinal = null;
        }
      } else {
        e.preventDefault();
        return false;
      }
    });
  });
}
function initDataRemote() {
  $('a[data-remote]:not(a[data-remote="true"])').each(function() {
    var th = $(this);
    var url = $(this).attr('href');
    th.attr('data-remote','true');
    th.click(function(e) {
      loadContents(url);
      e.preventDefault();
      return false;
    });
  });
}
function loadContents(url) {
  if(!remote_block) {
    busy(true);
    remote_block = true;
    $.ajax({
      url: url,
      data: {'ajax':'true'},
      success: function(response, status, jqXhr) {
         preUpdateContents(response, url);
      },
      error: function(response, status, jqXhr){
         console.log("error!");
      },
      complete: function (response, status, jqXhr){
      }
    });
  }
}
function preUpdateContents(contents, url) {
  if(typeof(contents)!="object" && contents.substr(0,1)=="{") {
    var json = $.parseJSON(contents);
    redirect(json.redirect, true);
  } else {
    updateContents(contents, url);
  }
}
function updateContents(contents, url) {
  var panel = $('#panel');
  var contents_holder = $('#panel > .inside, #content > .inside > .contents');
  var container = panel.find('> .inside > .contents');
  var move = 0;
  panel.stop();
  panel.find('> .inside, > .panel-scroll').stop().animate({'top':0}, 250, 'easeInOutQuart');
  $('#page-holder').attr('class',$(contents).attr('class'));
  contents_holder.stop();
  contents_holder.animate({'opacity':0,'top':-move}, 250, 'easeInQuad', function() {
    contents_holder.empty();
    $('#panel > .inside').append($(contents).find('#panel > .inside').html());
    $('#content > .inside > .contents').append($(contents).find('#content > .inside > .contents').html());
    initReloads();
    contents_holder.css({'top':move});
    if(url!=null) {
      history.replaceState(
        {},                 // HISTORY DATA
        $('title').text(),  // HISTORY TITLE
        url                 // HISTORY URL
      );
    }
    contents_holder.animate({'opacity':1,'top':0}, 400, 'easeOutQuart');
    remote_block = false;
    busy(false);
  });
}
// INPUTS #####################################################
function initRadios() {
  var radios = $('[data-select-radio="radio"]');
  radios.attr('data-select-radio','');
  var selectMe = function() {
    var th = $(this);
    console.log(th);
    th.parent().find('> li').removeClass('selected');
    th.addClass('selected');
    th.find('input').prop('checked', true);
  }
  radios.each(function() {
    var th = $(this);
    th.find('input[type=radio]:checked').parent().addClass('selected');
    th.find('li').click(selectMe);
  });
}
function initFileBrowse() {
  var files = $('[data-file-browse="true"]');
  files.attr('data-file-browse','');
  var trigger = function(e) {
    var th = $(this);
    th.parent().find('input[type="file"]').click();
    //console.log(th.parent().find('input[type="file"]'));
    e.preventDefault();
    return false;
  }
  files.find('a').click(trigger);
  files.first().find('a').click();
}
// SCROLL #####################################################
function initPanelScroll() {
  var panel = $('#panel');
  var inside = panel.find('> .inside');
  var scroll = panel.find('> .panel-scroll');
  var panel_h, view_h, inside_h, scroll_h;
  var scroll_start, scroll_new, y_mouse, y_mouse_new;
  var inside_y = 0;
  var max_y = 0;
  var speed = 60;

  var updateScrollHeight = function() {
    if(scroll.hasClass('hidden') && view_h!=panel.height() || inside_h!=inside.outerHeight()) {
      view_h = panel.height();
      panel_h = panel.outerHeight();
      inside_h = inside.outerHeight();
      scroll_h = Math.round(Math.max(panel_h/3, Math.min(panel_h, panel_h+view_h-inside_h)));
      inside_y = 0;
      max_y = Math.max(0, inside_h-view_h);
      inside_y = Math.max(0, Math.min(max_y, inside_y));
      scroll_new = Math.round((panel_h-scroll_h)*(inside_y/max_y));
      inside.css({'top':-inside_y});
      scroll.css({'height':scroll_h, 'top':scroll_new});
    }
  }
  var panelOver = function() {
    updateScrollHeight();
    if(scroll_h!=panel_h) {
      scroll.removeClass('hidden');
    } else {
      scroll.addClass('hidden');
    }
  }
  var panelOut = function() {
    scroll.addClass('hidden');
  }
  function dragStart(e) {
    $('#page-holder').addClass('no-touch');
    scroll.addClass('dragging');
    if(e.type=='mousedown') {
        y_mouse=e.pageY;
    } else {
        e.preventDefault();
        y_mouse=e.changedTouches[0].pageY;
    }
    y_mouse_new = y_mouse;
    scroll_start = parseInt(scroll.css('top'));
    scroll_new = scroll_start+(y_mouse_new-y_mouse);
    updateScrollPre();
    document.addEventListener('touchmove', dragMove, false);
    document.addEventListener('touchend', dragEnd, false);
    document.addEventListener('touchcancel', dragEnd, false);
    document.addEventListener('mousemove', dragMove, false);
    document.addEventListener('mouseup', dragEnd, false);
  }
  function dragMove(e) {
    if(e.type=='mousemove') {
        y_mouse_new = e.pageY;
    } else {
        y_mouse_new = e.changedTouches[0].pageY;
    }
    scroll_new = scroll_start+(y_mouse_new-y_mouse);
    console.log(scroll_start + " & " + scroll_new);
    updateScrollPre();
  }
  function dragEnd(e) {
    updateScrollPre();
    $('#page-holder').removeClass('no-touch');
    scroll.removeClass('dragging');
    document.removeEventListener('touchmove', dragMove, false);
    document.removeEventListener('touchend', dragEnd, false);
    document.removeEventListener('touchcancel', dragEnd, false);
    document.removeEventListener('mousemove', dragMove, false);
    document.removeEventListener('mouseup', dragEnd, false);
  }
  var updateScrollPre = function() {
    scroll_new = Math.max(0, Math.min(panel_h-scroll_h, scroll_new));
    inside_y = Math.round(scroll_new/(panel_h-scroll_h)*max_y);
    updateScroll(true);
  }
  var updateScroll = function(hard) {
    inside_y = Math.max(0, Math.min(max_y, inside_y));
    scroll_new = Math.round((panel_h-scroll_h)*(inside_y/max_y));
    if(hard) {
      inside.css({'top':-inside_y});
      scroll.css({'top':scroll_new});
    } else {
      inside.stop().animate({'top':-inside_y},250,"easeOutQuart");
      scroll.stop().animate({'top':scroll_new},250,"easeOutQuart");
    }
  }
  var scrollEvent = function(e) {
    var e0 = e.originalEvent;
    var delta = e0.wheelDelta || -e0.detail;
    inside_y -= delta/Math.abs(delta)*speed;
    updateScroll(false);
    e.preventDefault();
    return false;
  }
  responsiveAddFun(updateScrollHeight);
  updateScrollHeight();
  panel.mouseenter(function() {
    panel.bind('mousewheel DOMMouseScroll', scrollEvent);
    panel.mouseleave(function() {
      panel.unbind('mousewheel DOMMouseScroll', scrollEvent);
    });
  });
  panel.mouseover(panelOver);
  panel.mouseleave(panelOut);
  try {
    scroll[0].removeEventListener('touchstart', dragStart, false);
    scroll[0].removeEventListener('mousedown', dragStart, false);
  } catch(e) {}
  try {
    scroll[0].addEventListener('touchstart', dragStart, false);
    scroll[0].addEventListener('mousedown', dragStart, false);
  } catch(e) {}
}