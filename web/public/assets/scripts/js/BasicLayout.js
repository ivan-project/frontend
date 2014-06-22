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
  if(self == top) {
    $(window).scroll(scrollListener);
    $(window).resize(resizeListener);
    if(window.addEventListener) {
      document.addEventListener('DOMMouseScroll', scrollMouse, false);
    }
    document.onmousewheel = scrollMouse;

    //initReloads();
    initContainerScroll('#panel');
    initContainerScroll('#content');
    initDocumentRefresh();
    setTimeout(initReloads,10);
  } else {
    if($('#remote-content').length==0 && typeof top.reloadByIframe == 'function') {
      top.reloadByIframe($('#page-holder'), decodeURIComponent($('body').attr('data-url')));
    }
  }
});
function initReloads(context) {
  //console.log("RELOADS!!!!!!!!!!!!!");
  if(typeof(context)=='undefined') {
    context = $('body');
  } else {
    resizeListener();
    scrollListener();
  }

  if($('body').attr('data-ajax-enabled')=='true') {
    initPostRemote(context);
    initDataRemote(context);
  }
  initRadio(context);
  initCheckboxes(context);
  initFileBrowse(context);
  initTooltips(context);
  initPercentages(context);
  initStatuses();

  //initDocumentRefresh(context);
  $('#page-holder *[autofocus]').first().focus();
}
// CUSTOMS ####################################################

// REMOTE #####################################################
var remote_block = false;
var reloadByIframe;
var pop_state = false;
var stat_timeout;
window.addEventListener('popstate', function(event) {
  if($('body').attr('data-ajax-enabled','true')) {
    pop_state = window.location.href;
    console.log(window.location.href);
    loadContents(window.location.href);
  }
});
function historyBack() {
  if(false) {//document.referrer=="") {
    redirect('/');
  } else {
    history.back();
  }
}
function redirect(redirect, unblock) {
  if($('body').attr('data-ajax-enabled','true')) {
    //window.location.href=redirect;
    if(unblock) remote_block = false;
    loadContents(redirect);
  } else {
    window.location.href = redirect;
  }
}
function initPostRemote(context) {
  context.find('form[data-remote]:not(form[data-remote="true"])').each(function() {
    var th = $(this);
    th.attr('data-remote','true');
    th.attr('target','remote_iframe');
    th.append('<input type="hidden" name="data_remote_form" value="true"/>');
    var iframe = $('#remote_iframe');
    th.submit(function(e) {
      var iframeLoad = function(e) {
        iframe.unbind('load',iframeLoad);

      }
      iframe.bind('load',iframeLoad);
      if(!remote_block) {
        busy(true);
        remote_block = true;
        reloadByIframe = function(contents, url) {
          if(typeof url == 'undefined') url = null;
          preUpdateContents(contents[0].outerHTML, url);
          reloadByIframe = null;
          //iframe.attr('src','about:blank');
        }
      } else {
        e.preventDefault();
        return false;
      }
    });
  });
}
function initDataRemote(context) {
  context.find('a[data-remote]:not(a[data-remote="true"])').each(function() {
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
  //console.log(contents);
  if(typeof(contents)!="object" && contents.substr(0,1)=="{") {
    var json = $.parseJSON(contents);
    redirect(json.redirect, true);
  } else {
    updateContents(contents, url);
  }
}
function updateContents(contents, url) {
  var panel = $('#panel');
  var contents_holder = $('#panel > .inside, #content > .inside');
  var container = panel.find('> .inside > .contents');
  //panel.stop();
  //panel.find('> .inside, > .container-scroll').stop().animate({'top':0}, 250, 'easeInQuad');
  $('#page-holder').attr('class',$(contents).attr('class'));
  contents_holder.stop();
  contents_holder.addClass('hide');
  contents_holder.animate({'opacity':0}, 250, 'easeInQuad').promise().done(function () {
    contents_holder.empty();
    contents_holder.css({'top':0});
    //console.log(pop_state+" & "+url);
    if(pop_state==false || pop_state==url || url==null) {
      panel.find('> .inside, > .container-scroll').stop().css({'top':0});
      $('#panel > .inside').append($(contents).find('#panel > .inside').html());
      $('#content > .inside').append($(contents).find('#content > .inside').html());
      initReloads();
      if(url!=null && pop_state==false) {
        history.pushState(
          {'url':window.location.href},                 // HISTORY DATA
          $('title').text(),  // HISTORY TITLE
          url                 // HISTORY URL
        );
      }
      $('title').text($($(contents).attr('data-title')).text());
      $(contents).attr('data-title','');
      contents_holder.animate({'opacity':1,'top':0}, 400, 'easeOutQuart');
      contents_holder.removeClass('hide');
      remote_block = false;
      busy(false);
      pop_state = false;
    } else {
      remote_block = false;
      loadContents(pop_state);
    }
  });
}
// DOCUMENT REFRESH ###########################################
function initDocumentRefresh() {

  function timeout() {
    setTimeout(function() {
      var el = $('[data-document-refresh]');
      var el_parent = el.parent();
      var id = el.attr('data-document-refresh')+'';
      var url = baseUrl+"dashboard/documents/refresh";
      if(el.length>0 && !remote_block) {
        $.ajax({
          url: url,
          method: 'post',
          data: {'id':id},
          success: function(response, status, jqXhr) {
            if(el.length>0) {
              var contents = $('<div>'+response+'</div>');
              var refresh = false;
              // CHECK CHART
              /*var chart = contents.find('[data-id-refresh="chart"] > .chart').attr('data-percent');
              var chart_compare = el_parent.find('[data-id-refresh="chart"] > .chart').attr('data-percent');
              if(chart!=chart_compare) {
                console.log('NOT EQUAL CHART');
                refresh = true;
              }*/
              // CHECK STAT
              //if(!refresh) {
                var stat = el_parent.find('[data-id-refresh="status"]');
                var stat_new = contents.find('[data-id-refresh="status"]');
                if(stat.attr('href')!=stat_new.attr('href') ||
                    stat.find('> .status').attr('data-ttip')!=stat_new.find('> .status').attr('data-ttip')) 
                {
                  stat.attr('href',stat_new.attr('href'));
                  stat.find('> .status').attr('data-ttip',stat_new.find('> .status').attr('data-ttip'));
                  stat.find('> .status').attr('class',stat_new.find('> .status').attr('class'));
                  stat.find('> .status').empty();
                  stat.find('> .status').append(stat_new.find('> .status').html());
                  console.log('NOT EQUAL STATUS');
                  refresh = true;
                }
                var chart = el_parent.find('[data-id-refresh="chart"] > .chart');
                var chart_new = contents.find('[data-id-refresh="chart"] > .chart');
                var results = el_parent.find('[data-id-refresh="compare-results"] > li');
                var results_new = contents.find('[data-id-refresh="compare-results"] > li');
                if(chart.attr('data-percent')!=chart_new.attr('data-percent') || results.length!=results_new.length) {
                  chart[0].updatePercentages(chart_new.attr('data-percent'), contents.find('[data-connected="'+chart_new.attr('data-percent-connected')+'"]').text());
                  refresh = true;
                  // NIE IDEALNE, NIE WYKRYWA JEŚLI JEDEN PLIK DOSZEDŁ INNY ODSZEDŁ
                  results.parent().append(results_new);
                  results.remove();
                  initReloads(el_parent.find('[data-id-refresh="compare-results"]'));
                }
              //}

              if(false) {//refresh) {
                $('#content > .inside').empty();
                $('#content > .inside').append(contents);
                initReloads($('#content > .inside'));
              }
            }
          },
          error: function(response, status, jqXhr){
          },
          complete: function (response, status, jqXhr){
            timeout();
          }
        });
      } else {
        timeout();
      }
    },1000);
  }
  timeout();
}
// INPUTS #####################################################
function initRadio(context) {
  var radios = context.find('[data-select-radio]:not([data-select-radio="true"])');
  var count_def = 4;
  var catch_marg = 20;
  var el_h = 40;
  radios.attr('data-select-radio','true');

  var liClick = function() {
    var th = $(this);
    var whole = th.parent().parent().parent();
    var label = whole.find('> .label > span');
    th.find('input').prop('checked', true);
    label.text(th.find('> div > span').text());
    label.click();
  }
  var labelTrigger = function() {
    var th = $(this).parent();
    var ov = th.find('> .options');
    var label = th.find('> .label');
    var ul = ov.find('> ul');
    var lis = ul.find('> li');
    var lis_count = Math.min(count_def, lis.length);
    //console.log(lis_count);
    if(th.hasClass('opened')) {
      th.removeClass('opened');
      ov.css({'height':0});
      var selected = lis.find('input[type=radio]:checked').parent();
      label.find('> span').text(selected.find('> div').text());
      label.attr('class','label'+(!selected.attr('class') ? '' : ' '+selected.attr('class')));
      th.unbind('mousemove',moveTarget);
    } else {
      th.addClass('opened');
      ov.css({'height':lis_count*el_h+4});
      label.find('> span').text(label.attr('data-placeholder'));
      label.attr('class','label');
      if(lis_count<lis.length) {
        th.bind('mousemove',moveTarget);
      }
    }
  }
  var moveTarget = function(e) {
    var th = $(this);
    var ov = th.find('> .options');
    var ov_hei = ov.height();
    var ul = ov.find('> ul');
    var lis = ul.find('> li');
    var diff = (count_def-lis.length)*el_h;
    var offset = ov.offset(); 
    var perc = Math.max(0,Math.min(1, (e.pageY - offset.top - catch_marg)/(ov_hei-2*catch_marg)));
    ul.css('top',perc*diff);
  }
  radios.find('> .label').click(labelTrigger);
  radios.find('> .options > ul > li').click(liClick);
  radios.each(function() {
    var th = $(this);
    var ov = th.find('> .options');
    var label = th.find('> .label');
    var lis = ov.find('> ul > li');
    label.find('> span').text(lis.find('input[type=radio]:checked').parent().find('> div').text());
  });
}
function initCheckboxes(context) {
  var checkboxes = context.find('[data-select-checkbox]:not([data-select-checkbox="true"])');
  checkboxes.attr('data-select-checkbox','true');
  var selectMe = function() {
    var th = $(this);
    if(th.hasClass('selected')) {
      th.removeClass('selected');
      th.find('input').prop('checked', false);
    } else {
      th.addClass('selected');
      th.find('input').prop('checked', true);
    }
  }
  checkboxes.each(function() {
    var th = $(this);
    th.find('input[type=checkbox]:checked').parent().addClass('selected');
    th.find('li').click(selectMe);
  });
}
function initFileBrowse(context) {
  var files = context.find('[data-file-browse="true"]');
  files.attr('data-file-browse','');
  var trigger = function(e) {
    var th = $(this);
    th.parent().find('input[type="file"]').click();
    //console.log(th.parent().find('input[type="file"]'));
    e.preventDefault();
    return false;
  }
  var inputFileChange = function(e) {
    var el = $(e.target).parent();
    var files = e.target.files || false;
    if(files.length>0) {
      el.find('> .txt > span, > .txt > svg, > .overview').stop().animate({'opacity':0},150,"easeInQuad").promise().done(function() {
        el.find('> .txt > span').text(files[0].name);
        el.find('> .txt > svg').css({'display':'none'});
        checkOverview(el.find('> .overview'), files[0], true);
        el.find('> .txt > span, > .txt > svg, > .overview').animate({'opacity':1},450,"easeOutQuart");
      });
      el.addClass('selected');
    } else {
      el.find('> .txt > span, > .txt > svg, > .overview').stop().animate({'opacity':0},150,"easeInQuad").promise().done(function() {
        el.find('> .txt > svg').css({'display':'inline-block'});
        el.find('> .txt > span').text(el.find('> .txt').attr('data-placeholder'));
        checkOverview(el.find('> .overview'), files[0], false);
        el.find('> .txt > span, > .txt > svg, > .overview').animate({'opacity':1},450,"easeOutQuart");
      });
      el.removeClass('selected');
    }
  }
  var checkOverview = function(target, file, state) {
    if(target.hasClass('image')) {
      if(state) {
        target.addClass('showed');

        var reader = new FileReader();
        reader.onload = function (e) {
          target.empty();
          target.append('<img alt=""/>');
          var img = target.find('img');
          img.attr('src', e.target.result);
          var w = img.width();
          var h = img.height();
          var spec;
          var max = 58;
          if(w<h) {
            spec = Math.round(h*max/w)
            img.css({'width':max,'height':spec,'top':Math.round((max-spec)/2)});
          } else {
            spec = Math.round(w*max/h)
            img.css({'height':max,'width':spec,'left':Math.round((max-spec)/2)});
          }
        }
        reader.readAsDataURL(file);
      } else {
        target.removeClass('showed');
        target.empty();
      }
    }
  }
  files.change(inputFileChange);
  files.find('a').click(trigger);
  files.first().find('a').click();
}
// TOOLTIPS ###################################################
function initTooltips(context) {
  var ttips = context.find('[data-ttip]:not([data-ttip-ready])');
  ttips.attr('data-ttip-ready',true);
  var tooltips_holder = $('#tooltips');
  var tooltipEnter = function() {
    var tim, inter, top, left;
    var delay = 1000;
    var th = $(this);
    if(th.attr('data-ttip-time')) {
      delay = th.attr('data-ttip-time');
    }
    var el = $('<div><div><div class="arr"></div></div></div>');
    var el_in = el.find('> div');
    if($.contains($('#content')[0], th[0])) {
      el.addClass('t2');
    }
    el_in.append('<p>'+th.attr('data-ttip')+'</p>');
    function setMe() {
      top = th.offset().top - el_in.outerHeight();
      left = parseInt(th.offset().left + th.outerWidth()/2 - el_in.width()/2);
      el_in.css({'top':top, 'left':left});
    }
    function show() {
      tooltips_holder.append(el);
      setMe();
      
      el_in.addClass('showed');
      inter = setInterval(setMe, 10);
    }
    th.mouseleave(function() {
      try { clearTimeout(tim); } catch(e) {}
      try { 
        el_in.removeClass('showed');
        setTimeout(function() {
          clearInterval(inter);
          el.remove();
        },200);
        th.unbind('mouseleave');
      } catch(e) {}
    });
    th.click(function() {
      th.mouseleave();
    });
    if(tooltips_holder.is(':empty')) {
      tim = setTimeout(show, delay);
    } else {
      show();
    }
  }
  ttips.mouseenter(tooltipEnter);
}
// PERCENTAGES ################################################
function initPercentages(context) {
  context.find('[data-chart]:not([data-chart="true"])').each(function() {
    var th = $(this);
    th.attr('data-chart','true');
    th.append('<canvas width="100%" height="100%"></canvas>');
    var canvas = th.find('canvas');
    var perc;
    canvas[0].style.width ='100%';
    canvas[0].style.height='100%';
    canvas[0].width  = th.width();
    canvas[0].height = th.height();
    var draw = canvas[0].getContext('2d');
    var wid = canvas.width();
    var hei = canvas.height();
    var dim = Math.min(wid, hei);
    var thick = 40;
    var thick_half = thick/2;
    var x = dim/2;
    var y = dim/2;
    var rad = dim/2-thick/2-1;
    var sum;
    var connected = $('[data-connected="'+th.attr('data-percent-connected')+'"]');
    var connected_val = parseFloat(connected.text());
    connected.text('0');
    
    var colors = {};
    colors["ok"] = $('#conf-css .color-ok').css('color');
    colors["warning"] = $('#conf-css .color-warning').css('color');
    colors["alert"] = $('#conf-css .color-alert').css('color');
    colors["left"] = $('#conf-css .color-left').css('color');

    th[0].updatePercentages = function(data, percent) {
      connected_val = percent;
      th.attr('data-percent',data);
      perc = JSON.parse(data);
      //console.log(perc);
      sum = 0;
      $.each(perc, function(key, value) {
        sum += value.value;
      });
      initShow();
    };

    var drawArc = function(draw, from, to, color) {
      draw.beginPath();
      draw.arc(x, y, rad, from, to, false);
      draw.strokeStyle = color;
      draw.lineWidth = thick;
      draw.stroke();

      from/360*2*Math.PI+1.5*Math.PI
    }
    var drawDivisions = function(draw, divisions, color) {
      var x1, x2, y1, y2, divis;
      for(var i = 0; i < divisions; i++) {
        divis = (1.5 + i/divisions*2) * Math.PI;
        x1 = x + (rad-thick_half-1) * Math.cos(divis);
        y1 = y + (rad-thick_half-1) * Math.sin(divis);
        x2 = x + (rad+thick_half+1) * Math.cos(divis);
        y2 = y + (rad+thick_half+1) * Math.sin(divis);
        draw.beginPath();
        draw.moveTo(x1, y1);
        draw.lineTo(x2, y2);
        draw.strokeStyle = color;
        draw.lineWidth = 1.2;
        draw.stroke();
      }
    }

    

    //drawArc(draw, 100, 'rgba(210,200,191,1)');
    var interval;
    var initShow = function() {
      var time_now = 0;
      var time_delay = 10;
      var time_counter = 200;
      var now_perc;
      clearInterval(interval);
      interval = setInterval(function() {
        time_now++;
        if(time_now>=time_counter) {
          clearInterval(interval);
        }
        now_perc = $.easing.easeOutQuad(null, time_now, 0, 1, time_counter);
        draw.clearRect(0, 0, wid, hei);
        //drawArc(draw, 100, 'rgba(210,200,191,1)');
        //drawArc(draw, now_perc, 'rgba(61,195,98,1)');

        var from = 1.5*Math.PI;
        drawArc(draw, from, from+2*Math.PI, colors['left']);
        //console.log(now_perc);
        $.each(perc, function(key, value) {
          var to = from + now_perc*value.value/sum*2*Math.PI;
          drawArc(draw, from, to+0.001, colors[value.color]);
          from = to;
        });
        if(connected) {
          connected.text(connected_val);
        }
      }, time_delay);
    }

    th[0].updatePercentages(th.attr('data-percent'), connected_val);
  });
}
// STATUSES ###################################################
function initStatuses() {
  function show() {
    clearTimeout(stat_timeout);
    $('.status > span').parent().addClass('state2');
    stat_timeout = setTimeout(hide, 1500);
  }
  function hide() {
    clearTimeout(stat_timeout);
    $('.status > span').parent().removeClass('state2');
    stat_timeout = setTimeout(show, 3500);
  }
  clearTimeout(stat_timeout);
  stat_timeout = setTimeout(show, 500);
}
// SCROLL #####################################################
function initContainerScroll(target) {
  var panel = $(target);
  var inside = panel.find('> .inside');
  var scroll = panel.find('> .container-scroll');
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