(function($){
  
  $.tCheckbox = {
    options : {
      
    },
    classes : {
      main  : 'tCheckbox',
      on    : 'tCheckbox-on',
      off   : 'tCheckbox-off',
      blank : 'tCheckbox-blank'
    }
  }
  
   var methods = {
    init : function( options, classes ){
      var opt = {};
      var cls = {};
      
      $.extend( opt, $.tCheckbox.options, options );
      $.extend( cls, $.tCheckbox.classes, classes );
      
      return this.each(function(){

        var com = {
          checkbox  : $(this),
          element   : $(document.createElement('a')),
          on        : $(document.createElement('a')),
          off       : $(document.createElement('a')),
          blank     : $(document.createElement('a')),
          opt       : opt,
          cls       : cls
        };

        com.element
          .add( com.on )
          .add( com.off )
          .attr('href', 'javascript:void(0)');

        com.element
          .addClass( cls.main )
          .append( com.on )
          .append( com.off )
          .insertBefore( com.checkbox )
          .bind( 'click.toggleState', function(){
            if( com.checkbox.attr('checked') ){
              com.blank.insertBefore( com.off );
              com.on.hide();
              com.off.show();
              com.checkbox.attr('checked', false);
              com.checkbox.trigger('tCheckbox-unset');
            } else {
              com.blank.insertAfter( com.on );
              com.off.hide();
              com.on.show();
              com.checkbox.attr('checked', true);
              com.checkbox.trigger('tCheckbox-set');
            }
            com.checkbox.trigger('tCheckbox-toggle');
          });

        com.on.addClass( cls.on ).html('On');
        com.off.addClass( cls.off ).html('Off');
        com.blank.addClass( cls.blank ).html('&nbsp;');

        com.checkbox.hide();

        com.element
          .trigger( 'click.toggleState' )
          .trigger( 'click.toggleState' );
        
        $(this).data('com', com);
      });
      
    },
    toggle : function(){
      $(this).trigger('click.toggleState');
    },
    set : function(){
      if(!$(this).attr('checked')){
        $(this).tCheckbox('toggle');
      }
    },
    unset : function(){
      console.log(this, $(this));
      if($(this).attr('checked')){
        $(this).tCheckbox('toggle');
      }
    }
  };

  $.fn.tCheckbox = function( method ){
    if ( methods[method] ) {
      return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
      return methods.init.apply( this, arguments );
    } else {
      $.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
    }
  }
  
})(jQuery); 
