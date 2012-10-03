/**
 *  This version of JPeople is developed by Stefan Mirea @ Mercator College Office
 * If you plan on using it for your site, please give credits
 * Also, if you want to modify it or use it for something else, don't forget
 * who did it first :)
 *
 * Contact: steven.mirea@gmail.com
**/

(function($){
  $.fn.center = function () {
    this.css("position","absolute");
    this.css("top", ( $(window).height() - this.height() ) / 2+$(window).scrollTop() + "px");
    this.css("left", ( $(window).width() - this.width() ) / 2+$(window).scrollLeft() + "px");
    return this;
  }

  $.extend($.expr[':'], {
    jPeople : function(){
      return !!$(a).data('jPeople');
    },
    textarea  : function(a){
      return a.nodeName.toLowerCase() == 'textarea';
    }
  });


  $.jPeople = {
    options : {
      'ajaxFile'      : 'ajax.php',
      'tipsFile'      : 'tips.php',
      'infoFile'      : 'info.php',
      'feedbackFile'  : 'feedback.php',
      'minLength'     : 3,
      // the number of ms to wait before a request performed
      'timeout': {
        getFace     : 100,
        autoComplete  : 800
      },
      // which columns from the table to display in the description section
      fields: ['college', 'room', 'title', 'office', 'phone', 'email', 'birthday'],
      placeHolder : 'Click and start typing...'
    },  classes : {
      main            : 'jPeople',
      outputContainer : 'jPeople-outputContainer',
      autoComplete    : 'jPeople-autoComplete',
      faceContainer   : 'jPeople-faceContainer',
      searchInput     : 'jPeople-search',
      wrapper         : 'jPeople-wrapper',
      itemName        : 'jPeople-item-name',
      itemMeta        : 'jPeople-item-meta',
      itemOdd         : 'jPeople-item-odd',
      itemFocused     : 'jPeople-item-selected',
      menu            : 'jPeople-menu',
      button          : 'jPeople-button',
      buttonSelected  : 'jPeople-button-selected',
      displayBtn      : 'jPeople-displayBtn',
      infoBtn         : 'jPeople-infoBtn',
      infoOverlay     : 'jPeople-infoOverlay',
      tipsBtn         : 'jPeople-tipsBtn',
      popup           : 'jPeople-popup',
      tipsPopup       : 'jPeople-tipsPopup',
      popupActions    : 'jPeople-popup-actions',
      popupOptions    : 'jPeople-popup-options',
      popupCWrapper   : 'jPeople-popup-content-wrapper',
      popupContent    : 'jPeople-popup-content',
      popupInputs     : 'jPeople-popup-inputs',
      popupTitleClose : 'jPeople-popup-close',
      faceid          : 'jPeople-ID',
      faceidTD        : 'jPeople-ID-TD',
      faceidInfo      : 'jPeople-ID-info',
      faceidMarginTd  : 'jPeople-ID-marginTD',
      faceidInfoCell  : 'jPeople-ID-infoCell',
      print           : 'jPeople-printable'
    }
  };

  /* this allows us to pass in HTML tags to autocomplete. Without this they get escaped */
  $[ "ui" ][ "autocomplete" ].prototype["_renderItem"] = function( ul, item ) {
    return $( "<li></li>" )
      .data( "item.autocomplete", item )
      .append( $( "<a></a>" ).html( item.label ) )
      .appendTo( ul );
  };

  $.fn.jPeople = function(options, classes){

    var opt = {};
    var cls = {};

    $.extend(opt, $.jPeople.options, options);
    $.extend(cls, $.jPeople.classes, classes);

    return this.each(function(o){

      $(this).addClass(cls.main);

      var com = {
        opt: opt,
        cls: cls,
        // will hold the input
        wrapper       : null,
        // will store the jquery-ui autocomplete widget
        autoComplete  : null,
        // will hold the individual info
        faceContainer : $(document.createElement('div')).addClass(cls.faceContainer),
        // the actual search field
        textField     : $(this),
        // wrapper for the buttons under the textfields
        menu          : $(document.createElement('div')),
        // the trigger for displaying all the results
        displayBtn    : $(document.createElement('a')),
        //
        infoBtn       : $(document.createElement('a')),
        //
        tipsBtn       : $(document.createElement('a')),
        //
        tipsPopup     : $(document.createElement('div')),
        //
        infoOverlay   : null,
        // a popup that can be used for various things
        // TODO: make popup a standalone-pluggable plugin so it can be replaced with any popup plugin
        popup         : $('<div class="'+cls.popup+'"></div>'),
        //
        autoComplete  : null,
        // used to send request at reasonable intervals
        timeout       : {
          getFace     : null,
          autoComplete  : null
        },
        // used to store the last result parameters
        store         : {
          getFace       : {},
          autoComplete  : {},
          layouts       : {}
        }
      };

      com.textField
        .attr({
          placeholder : opt.placeHolder
        })
        .addClass( cls.searchInput )
        .wrap('<div></div>');

      com.wrapper = com.textField.parent();
      com.wrapper.addClass( cls.wrapper );

      com.menu
        .addClass( cls.menu )
        .insertAfter( com.textField )
        .append( com.displayBtn.add(com.infoBtn).add(com.tipsBtn) )

      com.displayBtn
        .attr({
          'href'  : 'javascript:void(0)',
          'title' : 'Click to see the full result of the search'
        })
        .addClass( cls.button )
        .addClass( cls.displayBtn )
        .html('Show all <span class="numberOfItems">0</span>');

      com.infoOverlay = make_info_overlay(com);
      com.infoBtn
        .attr({
          'href'  : 'javascript:void(0)',
          'title' : 'Click to see the full result of the search'
        })
        .addClass( cls.button )
        .addClass( cls.infoBtn )
        .html( 'Info' )
        .bind( 'click.showInfoOverlay', function () {
          com.infoOverlay.fadeIn();
        });

      com.tipsBtn
        .attr({
          'href'  : 'javascript:void(0)',
          'title' : 'Click to see the full result of the search'
        })
        .addClass( cls.button )
        .addClass( cls.tipsBtn )
        .html( 'Tips' );

      com.tipsPopup
        .css({
          position  : 'absolute'
        })
        .addClass( cls.tipsPopup )
        .hide()
        .html( '<img src="images/ajax.gif" alt="Loading content..." />' )
        .appendTo( com.textField.parent() )
        .load( opt.tipsFile, {}, function(){
          $(this)
            .find('code')
            .bind('click.addQuery', function(){
              com.textField.val( $(this).html() );
              com.textField.autocomplete('search');
            });
          $(this).find('.section').removeAttr('title');
        });


      com.textField.autocomplete({
        autofocus : true,
        minLength : opt.minLength,
        delay     : opt.timeout.autoComplete,
        create    : function( e, data ){
          com.autoComplete = $(this).autocomplete('widget');
          com.autoComplete.addClass( cls.autoComplete );
          com.faceContainer.appendTo( com.autoComplete.parent() );
        },
        focus : function( e, data ){
          com.faceContainer
            .fadeIn()
            .html( faceTemplate(com, data.item.full) )
            .css({
              position  : 'absolute',
              left      : com.autoComplete.offset().left + com.autoComplete.outerWidth(),
              top       : com.autoComplete.offset().top
            });
          return false;
        },
        close : function( e, ui ){
          com.faceContainer.fadeOut();
        },
        search : function( e, ui ){
          com.faceContainer.fadeOut();
        },
        open  : function( e, ui ){
          var widget = $(this).autocomplete('widget');
          widget
            .position({
              of        : com.wrapper,
              my        : "left top",
              at        : "left bottom",
              collision : "none"
            })
            .hide()
            .show( 'blind' )
            .find( '.ui-menu-item:odd' )
            .addClass( cls.itemOdd );
        },
        source    : function( request, response ){
          $.get( opt.ajaxFile,{
              action  : 'fullAutoComplete',
              str     : request.term
            }, function( data ){
              com.store.autoComplete.result = data;
              com.displayBtn.find('.numberOfItems').html(data.length);
              response($.map( data.records, function( item ){
                var college = item.college ? collegeIcon( item.college ) : '';
                var country = item.country && item.country.length > 1 ? '<img src="'+item.flag_small_url+'" title="'+item.country+'" />' : '';
                var label = '<span class="'+cls.itemName+'">'+item.fname+' '+item.lname+'</span> <span class="'+cls.itemMeta+'">'+college+country+'</span>'
                return {
                  label : label,
                  value : item.fname+' '+item.lname,
                  full  : item
                }
              }));
          });
        }
      });

      // Override default select method for the autocomplete to prevent the menu from closing
      $(this).data("autocomplete").menu.options.selected = function(event, data) {
        com.textField.focus();
        return false;
      }


      com.displayBtn
        .bind('click.togglePopup', function(){
          com.popup.trigger('togglePopup', ['Loading results... ']);
            com.popup.data('title').html('Search results: ');
            var r       = com.store.autoComplete.result.records;
            var emails  = [];
            var eids    = [];
            var view    = 'full';
            var h = getFaceLayout( com, r, view );

            for( var i in r ){
              eids.push( r[i].eid );
              if( r[i].email ) {
                emails.push( r[i].email );
              }
            }

            com.popup
              .data('actions')
              .find('.btn-email')
              .attr('href','mailto:'+emails.pop()+'?bcc='+emails.join(', '));

            com.popup
              .data('actions')
              .find('.btn-contacts')
              .attr('href', opt.ajaxFile+'?action=vcf&str='+eids.join('_'));

            com.popup.data('content').html( h );
            com.popup.find('input[type="checkbox"]').tCheckbox('set');
          });

        com.tipsBtn
          .bind('click.toggleTips', function(){
            if( com.tipsPopup.is(':hidden') ){
              com.tipsBtn.addClass( cls.buttonSelected );
              com.tipsPopup
                .fadeIn()
                .position({
                  of        : com.textField,
                  my        : "left top",
                  at        : "right top",
                  offset    : "-1",
                  collision : "none"
                });
            } else {
              com.tipsBtn.removeClass( cls.buttonSelected );
              com.tipsPopup.fadeOut();
            }
          });

        com.popup
          .html('<div class="'+cls.popupCWrapper+'">'+
                '<h1><span>No title...</span><a href="javascript:void(0)" class="'+cls.popupTitleClose+'">X</a></h1>'+
                '<div class="'+cls.popupActions+'">'+
                  '<a href="javascript:void(0)" class="btn btn-email">Mail all</a>'+
                  '<a href="javascript:void(0)" class="btn btn-contacts">Export contacts</a>'+
                  '<a href="javascript:void(0)" class="btn btn-print">Print all</a>'+
                '</div>'+
                '<div class="'+cls.popupOptions+'">'+
                '</div>'+
                '<div class="'+cls.popupContent+'">No content...</div>'+
                '<div class="'+cls.popupInputs+'">'+
                  '<input type="button" id="closeMe" value="Close" />'+
                '</div>'+
              '</div>')
          .data('title', com.popup.find('h1 span'))
          .data('actions', com.popup.find('.'+cls.popupActions))
          .data('content', com.popup.find('.'+cls.popupContent).eq(0))
          .data('closeBtn', com.popup.find('#closeMe').add( com.popup.find('.'+cls.popupTitleClose) ) )
          .data('printBtn', com.popup.find('.btn-print') )
          .bind('togglePopup', function(e, title, content){
            if($(this).data('isVisible')){
                $(this).fadeOut();
              } else {
                com.popup
                  .data('title')
                  .html(title);
                if(content){
                    com.popup
                      .data('content')
                      .html(content)
                      .css('text-align', 'left');
                } else {
                    com.popup
                      .data('content')
                      .html('<img src="images/ajax.gif" />');
                }
                $(this)
                  .fadeIn()
                  .center();
              }
          })
          .data('closeBtn')
          .bind('click.closePopup', function(){
            com.popup.toggle('togglePopup');
          });

        com.popup
          .data('printBtn')
          .bind('click.print', function(){
            com.popup.data('content').jqprint();
          });

        com.popup.data('content').addClass(cls.print);
        $('body').append(com.popup);

        com.popup
          .data('closeBtn')
          .bind('focus', $(this).blur());

        setupDisplayOptions( com, com.popup.find('.'+cls.popupOptions) );

        $(this)
          .data('jPeople', true)
          .data('jPeople-options', opt)
          .data('jProple-classes', cls)
          .data('jPeople-components', com);

    });

  }

  function make_info_overlay (com) {
    var overlay = $(document.createElement('div'));
    overlay
      .appendTo(document.documentElement)
      .addClass(com.cls.infoOverlay)
      .load(com.opt.infoFile, function () {
        overlay.find('#feedback-form').bind('submit.ajaxSend', function (event) {
          event.preventDefault();
          var formData = $(this).serialize();
          $.get(com.opt.feedbackFile + '?' + formData, function (r) {
            overlay.find('.' + com.cls.popupTitleClose).click();
          });
        });
      });
    return overlay;
  }

  function setupDisplayOptions( com, container ){
    var fields = [
      [ 'header', 'fname', 'lname', 'majorlong', 'description'],
      com.opt.fields,
      [ 'country' ]
    ];
    var layouts = {
      'Full'  : getFaceTemplate_full,
      'Table' : getFaceTemplate_table
    };

    var columns = $();
    var i;

    for( i=0; i<fields.length; ++i ){
      columns = columns.add( $(document.createElement('table')) );
      for( var j=0; j<fields[i].length; ++j ){
        columns.last().append(
          '<tr>'+
            '<td><b>'+fields[i][j]+':</b> </td>'+
            '<td><input type="checkbox" checked="checked" toggles="'+fields[i][j]+'" value="'+fields[i][j]+'" /></td>'+
          '</tr>'
        );
      }
    }

    columns.addClass('column');
    container
      .append( columns )
      .append('<div class="clearBoth"></div>')
      .find( 'input[type="checkbox"]' )
      .tCheckbox({})

    container.find('input[type="checkbox"][toggles]')
      .bind('tCheckbox-toggle', function(){
        var tags = com.popup.data('content').find('.face [tag="'+$(this).attr('toggles')+'"]');
        if( $(this).attr('checked') ){
          tags.show();
        } else {
          tags.hide();
        }
      });

      var layoutObjects = container.find('input[type="checkbox"][layout]');
      layoutObjects
        .data('allLayouts', layoutObjects)
        .bind('tCheckbox-set', function(){
          $(this).data('allLayouts').tCheckbox('unset');
          $(this).tCheckbox('set');
        });

  }

  function collegeIcon( college ){
    college = college.toLowerCase();
    var CI = {
      'mercator'    : '<span class="college-icon mercator">M</span>',
      'krupp'       : '<span class="college-icon krupp">K</span>',
      'college-iii' : '<span class="college-icon college-iii">C3</span>',
      'nordmetall'  : '<span class="college-icon nordmetall">N</span>',
    };
    if( ['mercator','krupp','college-iii','nordmetall'].indexOf( college ) > -1 ){
      return CI[ college ];
    }
    return '';
  }

  function days_between(date1, date2) {
    var ONE_DAY = 1000 * 60 * 60 * 24;
    if( !date2 ){
      date2 = new Date();
      date2.setYear( 2000 + date1.getYear() % 100 );
      if( date2.getMonth() > date1.getMonth() || date2.getMonth() == date1.getMonth() && date2.getDate() > date1.getDate() ){
        date1.setYear( 2000 + date1.getYear() % 100 + 1 );
      }
    }
    date1.setHours( 0 );
    date1.setMinutes( 0 );
    date1.setSeconds( 0 );
    date2.setHours( 0 );
    date2.setMinutes( 0 );
    date2.setSeconds( 0 );
    var date1_ms = date1.getTime();
    var date2_ms = date2.getTime();
    var difference_ms = Math.abs(date1_ms - date2_ms);
    return Math.round(difference_ms/ONE_DAY);
  }

  function prettyBirthday( date ){
    var months = [ 'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December' ];
    var d = date.split('.');
    var a;
    var b;
    if( d.length == 2 && (a = parseInt(d[0], 10)) && (b = parseInt(d[1], 10)) && a >= 1 && a <=31 && b>=1 && b<=12 ){
      var p = '';
      switch( a % 10 ){
        case 1: p = 'st'; break;
        case 2: p = 'nd'; break;
        case 3: p = 'rd'; break;
        default: p = 'th';
      }
      var numDays   = days_between(new Date(2012,b-1,a));
      var daysLeft  = '';
      switch( numDays ){
        case 0: daysLeft = '<b style="color:blue">TODAY!</b>'; break
        case 1: daysLeft = '<b>Tomorrow!</b>'; break;
        default: daysLeft = numDays+' days left'
      }
      return a+'<sup>'+p+'</sup> of '+months[b-1]+
             '<span class="daysLeft"> ('+daysLeft+')</span>';
    } else {
      return date;
    }
  }

  function getFaceLayout( com, dataObject, template ){
    var data = [];
    $.extend( data, dataObject );
    template = template || 'full';

    var html = '';
    for( var i=0; i<data.length; ++i) {
      html += faceTemplate( com, data[i], template );
    }

    switch( template ){
      case 'table':
        return $(document.createElement('table')).html( html );
      default: case 'full':
        return $(document.createElement('div')).html( html );
    }
  }

  function faceTemplate( com, dataObject, template ){
    var data = {};
    $.extend( data, dataObject );
    template = template || 'full';

    if( data.email ){
      data.email = '<a href="mailto:'+data.email+'" title="Email '+data.fname+'">'+data.email+'</a>';
    }

    switch( template ){
      case 'table':
        return getFaceTemplate_table( com, data );
      default: case 'full':
        return getFaceTemplate_full( com, data );
    }
  }

  function getFaceTemplate_table( com, dataObject ){
    var data = {};
    $.extend( data, dataObject );

    if( data.country ){
      data.country = data.country+' <img src="'+data.flag_url+'" alt="'+data.county+'" />';
    }
    if( data.college ){
      data.college = collegeIcon( data.college );
    }

    var keys = [ 'fname', 'lname', 'majorlong', 'description', 'college', 'email', 'phone', 'room', 'birthday', 'country' ];

    var template = [];
    for( var i in keys ){
      data[ keys[i] ] = data[ keys[i] ] || '-';
      template.push( '<td tag="'+keys[i]+'">'+data[ keys[i] ]+'</td>' );
    }
    template = '<tr class="face_row tagSelector">'+template.join('')+'</tr>';

    return template;
  }

  function getFaceTemplate_full( com, dataObject ){
    var data = {};
    $.extend( data, dataObject );

    var country   = 'No man\'s land :(';
    if( data.country ){
      country = data.country+' <img src="' + data.flag_url + '" alt="'+data.country+'" />';
    }
    if( data.college ){
      data.college = collegeIcon( data.college ) + ' ' + data.college;
    }
    if( data.phone && data.phone.length == 4 ){
      data.phone = '+49 421 200 <b>'+data.phone+'</b>';
    }
    if( data.birthday ){
      data.birthday = prettyBirthday( data.birthday );
    }

    var template =
      '<div class="face tagSelector">'+
        '<div class="header" tag="header">'+
          '<table class="photo" cellspacing="0" cellpadding="0">'+
            '<tr><td><img src="' + data.photo_url + '" alt="The photo" /></td></tr>'+
          '</table>'+
          '<div class="name">'+
            '<span class="fname" tag="fname">'+data.fname+',</span> '+
            '<span class="lname" tag="lname">'+data.lname+'</span>'+
          '</div>'+
          '<div>'+
            '<span class="majorlong" tag="majorlong">'+data.majorlong+'</span> <br />'+
            '<span class="description" tag="description">'+data.description+'</span>'+
          '</div>'+
          '<div class="clearBoth"></div>'+
        '</div>'+
        '<table class="body" tag="info" cellpadding="1">';

    var attributes = com.opt.fields;

    for (var i=0; i<attributes.length; ++i ){
      if( data[attributes[i]] ){
        template += '<tr tag="'+attributes[i]+'">'+
                      '<td class="infoCell"> '+(attributes[i].slice(0,1).toUpperCase()+attributes[i].slice(1))+' </td>'+
                      '<td><span class="'+attributes[i]+'">'+data[attributes[i]]+'</span></td>'+
                    '</tr>';
      }
    }
    template += '</table>'+
                '<div class="country" tag="country">'+
                    country+
                  '</div>'+
                '</div>'+
              '</div>';

    return template;
  }

  /**
   * A Message function. checks to see if firebug is enabled
   * @param obj the object to output. If firebut is disable, will only work for: string, int, float
   * @param type log, info, err
  **/
  function M(obj, type){
    type = type ? type : "log";
    if(typeof console != 'undefined' && console != null){
      switch(type){
        case "log"  : console.log( obj ); break;
        case "info" : console.info( obj ); break;
        case "warn" : console.warn( obj ); break;
        case "err"  : console.err( obj ); break;
      }
    }
  }

})(jQuery);
