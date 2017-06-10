/*!
 * classie - class helper functions
 * from bonzo https://github.com/ded/bonzo
 * 
 * classie.has( elem, 'my-class' ) -> true/false
 * classie.add( elem, 'my-new-class' )
 * classie.remove( elem, 'my-unwanted-class' )
 * classie.toggle( elem, 'my-class' )
 */

/*jshint browser: true, strict: true, undef: true */
/*global define: false */

( function( window ) {

'use strict';

// class helper functions from bonzo https://github.com/ded/bonzo

function classReg( className ) {
  return new RegExp("(^|\\s+)" + className + "(\\s+|$)");
}

// classList support for class management
// altho to be fair, the api sucks because it won't accept multiple classes at once
var hasClass, addClass, removeClass;

if ( 'classList' in document.documentElement ) {
  hasClass = function( elem, c ) {
    return elem.classList.contains( c );
  };
  addClass = function( elem, c ) {
    elem.classList.add( c );
  };
  removeClass = function( elem, c ) {
    elem.classList.remove( c );
  };
}
else {
  hasClass = function( elem, c ) {
    return classReg( c ).test( elem.className );
  };
  addClass = function( elem, c ) {
    if ( !hasClass( elem, c ) ) {
      elem.className = elem.className + ' ' + c;
    }
  };
  removeClass = function( elem, c ) {
    elem.className = elem.className.replace( classReg( c ), ' ' );
  };
}

function toggleClass( elem, c ) {
  var fn = hasClass( elem, c ) ? removeClass : addClass;
  fn( elem, c );
}

var classie = {
  // full names
  hasClass: hasClass,
  addClass: addClass,
  removeClass: removeClass,
  toggleClass: toggleClass,
  // short names
  has: hasClass,
  add: addClass,
  remove: removeClass,
  toggle: toggleClass
};

// transport
if ( typeof define === 'function' && define.amd ) {
  // AMD
  define( classie );
} else {
  // browser global
  window.classie = classie;
}

})( window );


;( function( window ) {
  
  'use strict';

  function extend( a, b ) {
    for( var key in b ) { 
      if( b.hasOwnProperty( key ) ) {
        a[key] = b[key];
      }
    }
    return a;
  }

  // taken from https://github.com/inuyaksa/jquery.nicescroll/blob/master/jquery.nicescroll.js
  function hasParent( e, id ) {
    if (!e) return false;
    var el = e.target||e.srcElement||e||false;
    while (el && el.id != id) {
      el = el.parentNode||false;
    }
    return (el!==false);
  }

  // returns the depth of the element "e" relative to element with id=id
  // for this calculation only parents with classname = waypoint are considered
  function getLevelDepth( e, id, waypoint, cnt ) {
    cnt = cnt || 0;
    if ( e.id.indexOf( id ) >= 0 ) return cnt;
    if( classie.has( e, waypoint ) ) {
      ++cnt;
    }
    return e.parentNode && getLevelDepth( e.parentNode, id, waypoint, cnt );
  }

  // http://coveroverflow.com/a/11381730/989439
  function mobilecheck() {
    var check = false;
    (function(a){if(/(android|ipad|playbook|silk|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
    return check;
  }

  // returns the closest element to 'e' that has class "classname"
  function closest( e, classname ) {
    if( classie.has( e, classname ) ) {
      return e;
    }
    return e.parentNode && closest( e.parentNode, classname );
  }

  function mlPushMenu( el, trigger, options ) { 
    this.el = el;
    this.trigger = trigger;
    this.options = extend( this.defaults, options );
    // support 3d transforms
    this.support = Modernizr.csstransforms3d;
    if( this.support ) {
      this._init();
    }
  }


  mlPushMenu.prototype = {
    defaults : {
      // overlap: there will be a gap between open levels
      // cover: the open levels will be on top of any previous open level
      type : 'overlap', // overlap || cover
      // space between each overlaped level
      levelSpacing : 40,
      // classname for the element (if any) that when clicked closes the current level
      backClass : 'mp-back'
    },
    _init : function() {
      // if menu is open or not
      this.open = false;
      // level depth
      this.level = 0;
      // the moving wrapper
      this.wrapper = document.getElementById( 'mp-pusher' );
      // the mp-level elements
      this.levels = Array.prototype.slice.call( this.el.querySelectorAll( 'div.mp-level' ) );
      // save the depth of each of these mp-level elements
      var self = this;
      this.levels.forEach( function( el, i ) { el.setAttribute( 'data-level', getLevelDepth( el, self.el.id, 'mp-level' ) ); } );
      // the menu items
      this.menuItems = Array.prototype.slice.call( this.el.querySelectorAll( 'li' ) );
      // if type == "cover" these will serve as hooks to move back to the previous level
      this.levelBack = Array.prototype.slice.call( this.el.querySelectorAll( '.' + this.options.backClass ) );
      // event type (if mobile use touch events)
      this.eventtype = mobilecheck() ? 'touchstart' : 'click';
      // add the class mp-overlap or mp-cover to the main element depending on options.type
      classie.add( this.el, 'mp-' + this.options.type );
      // initialize / bind the necessary events
      this._initEvents();
    },
    _initEvents : function() {
      var self = this;

      // the menu should close if clicking somewhere on the body
      var bodyClickFn = function( el ) {
        self._resetMenu();
        el.removeEventListener( self.eventtype, bodyClickFn );
      };

      // open (or close) the menu
      this.trigger.addEventListener( this.eventtype, function( ev ) {
        $("#contextModal").modal('hide');
        $(this).find(".flashing").removeClass("flashing");
        $('.hand-pointer').hide();
        ev.stopPropagation();
        ev.preventDefault();
        if( self.open ) {
          self._resetMenu();
        }
        else {
          self._openMenu();
          // the menu should close if clicking somewhere on the body (excluding clicks on the menu)
          document.addEventListener( self.eventtype, function( ev ) {
            if( self.open && !hasParent( ev.target, self.el.id ) ) {
              bodyClickFn( this );
            }
          } );
        }
      } );

      // opening a sub level menu
      this.menuItems.forEach( function( el, i ) {
        // check if it has a sub level
        var subLevel = el.querySelector( 'div.mp-level' );
        if( subLevel ) {
          el.querySelector( 'a' ).addEventListener( self.eventtype, function( ev ) {
            ev.preventDefault();
            var level = closest( el, 'mp-level' ).getAttribute( 'data-level' );
            if( self.level <= level ) {
              ev.stopPropagation();
              classie.add( closest( el, 'mp-level' ), 'mp-level-overlay' );
              self._openMenu( subLevel );
            }
          } );
        }
      } );

      // closing the sub levels :
      // by clicking on the visible part of the level element
      this.levels.forEach( function( el, i ) {
        el.addEventListener( self.eventtype, function( ev ) {
          ev.stopPropagation();
          var level = el.getAttribute( 'data-level' );
          if( self.level > level ) {
            self.level = level;
            self._closeMenu();
          }
        } );
      } );

      
      // by clicking on a specific element
      this.levelBack.forEach( function( el, i ) {
        el.addEventListener( self.eventtype, function( ev ) {
          ev.preventDefault();
          var level = closest( el, 'mp-level' ).getAttribute( 'data-level' );
          if( self.level <= level ) {
            ev.stopPropagation();
            self.level = closest( el, 'mp-level' ).getAttribute( 'data-level' ) - 1;
            self.level === 0 ? self._resetMenu() : self._closeMenu();
          }
        } );
      } );  
    },
    
    _openMenu : function( subLevel ) {
      // increment level depth
      ++this.level;

      // move the main wrapper
      var levelFactor = ( this.level - 1 ) * this.options.levelSpacing,
        translateVal = this.options.type === 'overlap' ? this.el.offsetWidth + levelFactor : this.el.offsetWidth;
      
      this._setTransform( 'translate3d(' + translateVal + 'px,0,0)' );

      if( subLevel ) {
        // reset transform for sublevel
        this._setTransform( '', subLevel );
        // need to reset the translate value for the level menus that have the same level depth and are not open
        for( var i = 0, len = this.levels.length; i < len; ++i ) {
          var levelEl = this.levels[i];
          if( levelEl != subLevel && !classie.has( levelEl, 'mp-level-open' ) ) {
            this._setTransform( 'translate3d(-100%,0,0) translate3d(' + -1*levelFactor + 'px,0,0)', levelEl );
          }
        }
      }
      // add class mp-pushed to main wrapper if opening the first time
      if( this.level === 1 ) {
        classie.add( this.wrapper, 'mp-pushed' );
        this.open = true;
      }
      // add class mp-level-open to the opening level element
      classie.add( subLevel || this.levels[0], 'mp-level-open' );
    },
    // close the menu
    _resetMenu : function() {
      this._setTransform('translate3d(0,0,0)');
      this.level = 0;
      // remove class mp-pushed from main wrapper
      classie.remove( this.wrapper, 'mp-pushed' );
      this._toggleLevels();
      this.open = false;
    },
    // close sub menus
    _closeMenu : function() {
      var translateVal = this.options.type === 'overlap' ? this.el.offsetWidth + ( this.level - 1 ) * this.options.levelSpacing : this.el.offsetWidth;
      this._setTransform( 'translate3d(' + translateVal + 'px,0,0)' );
      this._toggleLevels();
    },
    // translate the el
    _setTransform : function( val, el ) {
      el = el || this.wrapper;
      el.style.WebkitTransform = val;
      el.style.MozTransform = val;
      el.style.transform = val;
    },
    // removes classes mp-level-open from closing levels
    _toggleLevels : function() {
      for( var i = 0, len = this.levels.length; i < len; ++i ) {
        var levelEl = this.levels[i];
        if( levelEl.getAttribute( 'data-level' ) >= this.level + 1 ) {
          classie.remove( levelEl, 'mp-level-open' );
          classie.remove( levelEl, 'mp-level-overlay' );
        }
        else if( Number( levelEl.getAttribute( 'data-level' ) ) == this.level ) {
          classie.remove( levelEl, 'mp-level-overlay' );
        }
      }
    }
  }

  // add to global namespace
  window.mlPushMenu = mlPushMenu;
  window.mobilecheck = mobilecheck;

} )( window );







// ************************************************************ //
// ************************ SubShuffle ************************ //
// ************************************************************ //

var subshuffle = function(){
  var log;
  var title;
  var toTranslate;
  var eventtype = mobilecheck() ? 'touchstart' : 'click';
  
  // Templates
  var empty = '<li class="empty-list"><a>{{{message}}}</a></li>';
  var loader = $("#loader-template").html();
  var template = new Array();
  template['my-translations'] = $("#my-translations-template").html();
  template['subtitle-sequences'] = $("#subtitle-sequences-template").html();
  
  // Init
  getRandomSequence();
  loadLoaders();
  $("#my-translations").on(eventtype,function(){
    getTranslationsData();
  });
  $(".subtitle-item").on(eventtype, function(){
    var subId = $(this).attr('sub-id');
    getSubtitleSequencesData(subId);
  });
  $('#random').on(eventtype, function(){
    getRandomSequence();
    $("#to-textarea").focus();
  });
  $(".neighbour-seq").on(eventtype, function(){
    // console.log(this);
    if( $(this).attr("seq-num")!='' )
      getSequence($(".title-info").attr("sub-id"),$(this).attr("seq-num"));
    $("#to-textarea").focus();
  });
  $('#save').on(eventtype, function(){
    saveSequence();
  });

  // var textarea = document.getElementById("to-textarea");
  // textarea.onkeydown = function(event) {
  //   console.log(event);
  // }



  // Methods
  function rebindEvents() {
    $("#my-translations-more").on(eventtype,function(){
      $(this).parent().addClass('more-loader');
      $(this).html(Mustache.render(loader));
      var loadFrom = $(this).parent().prev('.list-item').attr('seq-id');
      getTranslationsData(loadFrom);
    });

    $(".more-sequences").on(eventtype,function(){
      var subId = $(this).closest("ul").attr("sub-id");
      var loadFrom = $(this).parent().prev('.list-item').attr('seq-id');
      getSubtitleSequencesData(subId, loadFrom);
    });
    
    $('.final-item').on(eventtype, function(){
      getSequence($(this).attr("sub-id"),$(this).attr("seq-num"));
      menu._resetMenu();
      $("#to-textarea").focus();
    });
  }

  function loadLoaders() {
    $(".load-loader").each(function(){
      $(this).html(Mustache.render(loader));
    });
  }
    
  function camelize(string) {
    return string.replace (/(?:^|[-_])(\w)/g, function (_, c, pos) {
      return pos ? c.toUpperCase () : c;
    })
  }

  function getTranslationsData(loadMore) {
    loadMore = loadMore || "";
    $.get( "subshuffle/myTranslations/"+loadMore, function( data ) {
      // addLog('last-translations',data);
      if(loadMore)
        more("my-translations", JSON.parse(data));
      else
        render("my-translations", JSON.parse(data));
      rebindEvents();
    });    
  }

  function getSubtitleSequencesData(subId, loadMore) {
    loadMore = loadMore || "";
    $.get( 'subshuffle/subtitleSequences/'+subId+'/'+loadMore, function( data ) {
      // addLog('last-sequences',data);
      if(loadMore)
        more("subtitle-sequences", JSON.parse(data),"sub-"+subId);
      else
        render("subtitle-sequences", JSON.parse(data),"sub-"+subId);
      rebindEvents();
    });
  }

  function render(list, items, container) {
      container = container || list;

      if(items.empty == null) {
        $("#" + container + "-container").html(Mustache.render(template[list], {items : items}));   
        // $(".nano").nanoScroller();
      } else {
        $("#" + container + "-container").html(Mustache.render(empty, {message : items.empty}));           
      }
  }

  function more(list, items, container) {
    container = container || list;
    $("#" + container + "-container li:last-child").remove();
    if(items.noMore == null) {
      $("#" + container + "-container").append(Mustache.render(template[list], {items : items}));   
    } else {
      $("#" + container + "-container").append(Mustache.render(empty, {message : items.noMore}));           
    }
  }

  function getRandomSequence () {
    $.get( "subshuffle/randomSequence", function( data ) {
      // addLog('last-random',data);
      var obj = JSON.parse(data);   
      obj['duration'] = calcDuration(obj.start_time, obj.start_time_fraction, obj.end_time, obj.end_time_fraction);
      if(obj.text_es !== undefined) {
        // obj['cpl'] = cpl(obj.text_es);

        obj['cps'] = cps(obj.text_es, obj.duration);
        // obj['cps'] = ((Math.round(totalChars(obj.text_es)/duration*100)/100).toString()).replace('.',',');
      }
      // console.log(obj);
      placeSequence(obj);
    });    
  }

  function getSequence (subId,sequence) {
    $.get( "subshuffle/getSequence/"+subId+"/"+sequence, function( data ) {
      // addLog('last-sequence',data);
      var obj = JSON.parse(data);   
      obj['duration'] = calcDuration(obj.start_time, obj.start_time_fraction, obj.end_time, obj.end_time_fraction);
      if(obj.text_es !== undefined) {
        // obj['cpl'] = cpl(obj.text_es);

        obj['cps'] = cps(obj.text_es, obj.duration);
        // obj['cps'] = ((Math.round(totalChars(obj.text_es)/duration*100)/100).toString()).replace('.',',');

      }
      // console.log(obj);
      placeSequence(obj);
    });    
  }

  function saveSequence() {
    console.log("¡Guardado!");
    $('#saved').fadeIn();
    setTimeout(function(){ $('#saved').fadeOut(); }, 3000);
  }

  function placeSequence (sequence) {

    if(sequence.noSequence == null) {
      $(".from-textarea p").html(nl2br(sequence.text_en));
      $(".title-info").text(sequence.title).attr('sub-id',sequence.subID);
      $(".sequence-number").text(sequence.sequence);
      $("#to-textarea").attr("data-duration", sequence.duration);

      if(!sequence.hasNext)
        $("#next").addClass("disabled").attr('seq-num','');
      else
        $("#next").removeClass("disabled").attr('seq-num',parseInt(sequence.sequence)+1);
      
      if(!sequence.hasPrev)
        $("#prev").addClass("disabled").attr('seq-num','');
      else
        $("#prev").removeClass("disabled").attr('seq-num',parseInt(sequence.sequence)-1);

      if(sequence.text_es) {
        $("#to-textarea").val(sequence.text_es);
        $(".cps").text(sequence.cps);
        if( (sequence.cps).replace(",", ".") > 25 ) $(".cps").addClass('over-cps');
        else $(".cps").removeClass('over-cps');
        // $(".chars").text(sequence.cpl);
        cpl(sequence.text_es);
      } else {
        $("#to-textarea").val('');
        $(".cps").text('0').removeClass('over-cps');
        // $(".chars").text('0 / 0');
        $(".first-line-chars").text(0);
        $(".second-line-chars").text(0);
      }

      if(sequence.usertoken) {
        $('#locked span').text(sequence.usertoken);
        $('#to-textarea').prop('disabled',true);
        $('#locked').show();
      } else {
        $('#locked span').text('');
        $('#to-textarea').prop('disabled',false);
        $('#locked').hide();
      }

    } else {
      $.alert({
        type: 'red',
        title: '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;&nbsp;Errare Humanum Est',
        content: '<div class="alert-red">'+sequence.noSequence+'</div>',
        backgroundDismiss: true
      });
      // <pre class="log-container">'+$('#log').html()+'</pre>
    }
  }

  function calcDuration(startTime, startFraction, endTime, endFraction) {
    return (timestampToSeconds(endTime, endFraction) - timestampToSeconds(startTime, startFraction)).toFixed(3);
  }

  function timestampToSeconds (hhmmss, ms) {
    var a = hhmmss.split(':');
    var seconds = (+a[0]) * 60 * 60 + (+a[1]) * 60 + (+a[2]) + parseFloat('0.' + ms);
    return seconds;
  }

  function cpl(text) {
    var lines = text.split('\n');
    var firstLineChars = 0;
    var secondLineChars = 0;

    for(var i = 0; i < lines.length; i++){
      var cleanedText = lines[i].replace(/<(?:.|\n)*?>/gm, '').trim();      
      
      // console.log(cleanedText);
      if(i === 0) {
        if(cleanedText) firstLineChars = cleanedText.length;
      } else {
        if(cleanedText) secondLineChars = cleanedText.length;
      }
    }
    $(".first-line-chars").text(firstLineChars);
    $(".second-line-chars").text(secondLineChars);
  }

  function cps(text, duration) {
    var cps = ((Math.round(totalChars(text)/duration*100)/100).toString()).replace('.',',');
    return cps;
  }

  function totalChars(text) {
    var lines = text.split('\n');
    var totalChars = 0;
    for(var i = 0; i < lines.length; i++){
      var cleanedText = lines[i].replace(/<(?:.|\n)*?>/gm, '');
      totalChars += parseInt(cleanedText.length);
    }
    return totalChars;
  }


  function nl2br (str, is_xhtml) {
      var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
      return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
  }

  function addLog(className, log) {
    $('#log .'+className).text(log);
  }

  return {
    cpl : cpl,
    cps : cps
  };

}();


// (
//     [entryID] => 194852
//     [subID] => 167
//     [title] => Black-ish - 03x04 - Who is Afraid of the Big Black Man
//     [sequence] => 275
//     [start_time] => 00:11:14
//     [start_time_fraction] => 845
//     [end_time] => 00:11:16
//     [end_time_fraction] => 779
//     [text] => Hazlos esperar.
//     [fversion] => 0
// )

// jQuery(document).ready(function() { 
  
//   $(document).on('keyup','textarea',function() {
//     var sequenceNumber = $(this).closest('form').attr('id').substring(2);
//     var lines = $(this).val().split('\n');
//     var totalChars = 0;
//     for(var i = 0; i < lines.length; i++){
//       if(!i) $('#chars-column-sequence-'+sequenceNumber).html('');
//       var cleanedText = lines[i].replace(/<(?:.|\n)*?>/gm, '');
//       $('#chars-column-sequence-'+sequenceNumber).append(cleanedText.length+'<br>');
//       totalChars += parseInt(cleanedText.length);
//     }
//     var secondsDuration = parseFloat($('#cps-column-sequence-'+sequenceNumber).attr('data-seconds'));
//     var cps = Math.round(totalChars/secondsDuration*100)/100;
//     $('#cps-column-sequence-'+sequenceNumber).html(cps);
//     if (cps > 24) $('#cps-column-sequence-'+sequenceNumber).css('background-color', '#f76c6c'); else jQuery('#cps-column-sequence-'+sequenceNumber).css('background-color', '#E5E5E5');
//   });

// });
