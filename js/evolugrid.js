/**
 * A flexible datagrid that refreshes with Ajax and can export to CSV.
 * 
 * options: 
 * {
 *  filterForm: "selector", // A jQuery selector pointing to the form containing the filters (if any)
 *  filterFormSubmitButton: "selector", // A jQuery selector pointing to the button that will trigger search. This is optional, and can only be used if the filterForm option is used. If not passed, any submit button on the form will trigger a search.
 * 	filterCallback: function, // A function taking 0 arguments and returning a map of filters (passed as arguments to the Ajax URL). This is applied before the filterForm.
 * 				Returned parameters must match this format:
 * 				[{
					"name": "param1",
					"value": "paramValue" 
				}]
 *  url: url, // The Ajax URL
 *  tableClasses : "table", // The CSS class of the table
 *  limit  : 100, // The maximum number of rows to be returned in one page
 *  pagerId : 'listePager' // The ID of the pager,
 *  columns: [...], // A list of columns,
 *  export_csv: true, // Whether we can export to CSV or not,
 *  loadOnInit: true, // Whether we should start loading the table (true) or wait for the user to submit the search form (false),
 *  rowCssClass: "key", // If set, for each row, we will look in the dataset for the row, for the "key" passed in parameter. The associated value will be used as a class of the tr row. 
 *  infiniteScroll: boolean // To set a infinite scroll instead of a pager
 *  fixedHeader: boolean // To sfixed the header of the evolugrid table
 * }
 * 
 * Any parameter (except URL) can be dynamically passed from the server side.
 */
(function ($){
	var defaultOptions = {
			"loadOnInit": true,
			"export_csv": true,
			"limit": 100,
			"infiniteScroll": false,
			"fixedHeader": false
	}

	var sortKey;
	var sortOrder;
	
	//Only use for the infinite scroll
	var scrollOffset; 
	var scrollReady;
	var scrollNoMoreResults;
	
	//Only use for fixed header behavior
	var headerTopOffset;
	
	/**
	 * Returns the list of filters to be applied to the query.
	 * Some filters can be passed directly to the function. In that case only those filters are taken into account
	 */
	var _getFilters = function(descriptor, filters) {
		if (filters) {
			return filters;
		}
		
   		if(descriptor.filterCallback) {
    		return descriptor.filterCallback();
    	}
   		
   		if (descriptor.filterForm) {
   			return $(descriptor.filterForm).serializeArray();
    	}
   			
		return [];
	};
	
	var _generateHeaders = function(tr, descriptor, evolugrid) {
		for(var i=0;i<descriptor.columns.length;i++){
			var columnDescriptor = descriptor.columns[i];
			var th = $('<th>').html(columnDescriptor.title);
			if (columnDescriptor.width) {
				th.css("width", columnDescriptor.width);
			}
			var key = columnDescriptor.display;
			if (columnDescriptor.sortable) {
				(function(key) {
					var sortButtonAsc = $('<a href="#" />').click(function() {
						sortKey = key;
						sortOrder = "asc";
						if (descriptor.infiniteScroll) {
							evolugrid.evolugrid('scroll', true);
						} else {
							evolugrid.evolugrid('refresh', 0);
						}
						return false;
					});
					sortButtonAsc.append("<i class='icon-chevron-up'></i>");
					th.append(" ");
					th.append(sortButtonAsc);
					
					var sortButtonDown = $('<a href="#" />').click(function() {
						sortKey = key;
						sortOrder = "desc";
						if (descriptor.infiniteScroll) {
							evolugrid.evolugrid('scroll', true);
						} else {
							evolugrid.evolugrid('refresh', 0);
						}
						return false;
					});
					sortButtonDown.append("<i class='icon-chevron-down'></i>");
					th.append(" ");
					th.append(sortButtonDown);
				})(key);
			}
			tr.append(th);
		}		
	}
		
	var methods = {
	    init : function( options ) {
	    	var descriptor = $.extend(true, {}, defaultOptions, options);
	    	
	    	return this.each(function(){
                $(this).data('descriptor', descriptor);
                
                var $this = $(this);
                
                if (descriptor.infiniteScroll) {               	
                	// We initialise the infinite scroll
                	scrollReady = true;
                	scrollOffset = 0;
                	scrollNoMoreResults = false;
                	
                	$(window).scroll(function() {                		
                		// We test if we have not make a request
                		if (scrollReady == false) return;
                		 
                		var lastElementPositon = $this.find('table tbody tr:nth-last-child(' + descriptor.infiniteScroll_ElementPosition + ')').position();
                		
                		if(($(window).scrollTop() + $(window).height()) >= lastElementPositon.top)	{
                			if ( scrollNoMoreResults == true) {
                    			return;
                    		}
                
                			scrollReady = false;
                			$this.evolugrid('scroll', false);
                		}
                	});
                }
                
                if (descriptor.fixedHeader) {
                	if (descriptor.fixedHeader_NavBarSelector == undefined) {
                		descriptor.fixedHeader_NavBarSelector = ".navbar"
                	}
                	
                	if ($(descriptor.fixedHeader_NavBarSelector).length) {
                		var navBarPosition = $(descriptor.fixedHeader_NavBarSelector).position();
                		headerTopOffset = navBarPosition.top + $(descriptor.fixedHeader_NavBarSelector).height();
                	} else {
                		headerTopOffset = 0;
                	}
                }
                                
                if (descriptor.filterForm) {
                	if (descriptor.filterFormSubmitButton) {
                		$(descriptor.filterFormSubmitButton).click(function(event) {
                			try {
                				 if (descriptor.infiniteScroll) {
                					 scrollOffset = 0;
                					 scrollNoMoreResults = false;
                					 scrollReady = false;
                					 $this.evolugrid('scroll', true);
                				} else {
                					$this.evolugrid('refresh', 0);
                				}
                			} catch (e) {
                				console.error(e);
                			}
                			return false;
	                	});
                	} else {
	                	$(descriptor.filterForm).submit(function(event) {
                			try {
                				if (descriptor.infiniteScroll) {
                					scrollOffset = 0;
                					scrollNoMoreResults = false;
                					scrollReady = false;
               					 	$this.evolugrid('scroll', true);
                				} else {
                					$this.evolugrid('refresh', 0);
                				}
                			} catch (e) {
                				console.error(e);
                			}
                			return false;
	                	});
                	}
            	}
                if (descriptor.loadOnInit) {
                	if (descriptor.infiniteScroll) {
                		scrollReady = false;
                		$this.evolugrid('scroll', true);
    				} else {
    					$this.evolugrid('refresh', 0);
    				}
                }
 	        });
	    },
	    csvExport : function(filters) {
	    	var descriptor=$(this).data('descriptor');
	    	
	    	var filters = _getFilters(descriptor, filters);
	    	
	    	var url = descriptor.url;
	    	if (url.indexOf("?") == -1) {
	    		url += "?";
	    	} else {
	    		url += "&";
	    	}
	    	for (var i=0; i<filters.length; i++) {
    			url += filters[i]['name']+"="+encodeURIComponent(filters[i]['value'])+"&";
	    	}
	    	url += "output=csv";
	    	
	    	window.open(url);
	    },
	    refresh : function( noPage, filters ) {
	    	var descriptor=$(this).data('descriptor');
	    	
	    	//We show the ajax loader
	    	$(this).next('div.ajaxLoader').show();	    	
	    	
	    	// While refreshing, let's make sure noone touches the buttons!
	    	// FIXME: we should check which are already disabled and not reenable them later.... 
	    	if (descriptor.filterFormSubmitButton) {
	    		$(descriptor.filterFormSubmitButton).attr("disabled", true);
	    	} else if (descriptor.filterForm) {
	    		$(descriptor.filterForm).find("button").attr("disabled", true);
	    		$(descriptor.filterForm).find("input[type=button]").attr("disabled", true);
	    	}
	    	
	    	var $this=$(this);
	    	filters = _getFilters(descriptor, filters);
	    	filters.push({"name":"offset", "value": noPage*descriptor.limit});
	    	filters.push({"name":"limit", "value": descriptor.limit});
	    	filters.push({"name":"sort_key", "value": sortKey});
	    	filters.push({"name":"sort_order", "value": sortOrder});

	    	$.ajax({url:descriptor.url, dataType:'json', data : filters,
	    	success: function(data){
	    		
		    	var extendedDescriptor=$.extend(true, {}, descriptor, data.descriptor)

	    		//Display Count
	    		if(!extendedDescriptor.countTarget){
	    			var countTarget = "#count";
	    		} else {
	    			var countTarget=extendedDescriptor.countTarget
	    		}
	    		$(countTarget).html(data.count);
	    		//construct th
	    		//construct th
	    		$this.html("");
	    		var table= $('<table>').appendTo($this);
	    		var thead = $('<thead>').appendTo(table);
	    		var tbody = $('<tbody>').appendTo(table);
	    		var tr=$('<tr>');
	    		thead.append(tr);
	    		table.addClass(extendedDescriptor.tableClasses);		    		
	    		_generateHeaders(tr, extendedDescriptor, $this);
	    		
	    		if (descriptor.fixedHeader) {
	    			table.addClass("table-fixed-header");
	    			thead.addClass("header");
	    		}
	    		
	    		//construct td
	    		for (var i=0;i<data.data.length;i++){
	    			tr=$('<tr>');
	    			if (extendedDescriptor.rowCssClass) {
	    				tr.addClass(data.data[i][extendedDescriptor.rowCssClass]);
	    			}
	    			tbody.append(tr);
	    			for(var j=0;j<extendedDescriptor.columns.length;j++){
	    				var td=$('<td>');
	    				// jsdisplay is used when the data comes in JSON from the server (and you want js display)
	    				// if jsdipslay is used, display is ignored.
	    				var jsdisplay=extendedDescriptor.columns[j].jsdisplay;
	    				if (jsdisplay) {
	    					// Let's eval the function (its evil) and let's execute it.
	    					var myfunc = (new Function("return " + jsdisplay))();
	    					var html=myfunc(data.data[i]);
	    				} else {
		    				var display=extendedDescriptor.columns[j].display;
		    				if (display) {
			    				if(typeof display == 'function'){
			    					var html=display(data.data[i]);
			    				}else {
			    					var html=data.data[i][display];
			    					if (html === 0) {
			    						html = "0";
			    					}
			    				}
		    				}
	    				}
	    				if(html){
	    					td.html(html);
		    			}
	    				tr.append(td);
		    		}   			
	    		}
	    		//construct pager
	    		var pager=$('<div>').addClass("pager");
	    		if(extendedDescriptor.pagerId){
	    			pager.attr('id',extendedDescriptor.pagerId);
	    		}
	    		
	    		    		
	    		if (extendedDescriptor.export_csv) {
	    			var span = $('<span/>').click(function(){$this.evolugrid('csvExport');});
	    			span.append($('<i/>').addClass('icon-file pointer export-csv'));
	    			span.append("Export to CSV");
	    			pager.append(span);
	    		}
	    		
	    		var pageCount = null;
	    		if (data.count != null) {
	    			pageCount=Math.floor(data.count/extendedDescriptor.limit);
	    		}
	    		
	    		if (pageCount>0) {
		    		if(noPage>0){
		    			pager.append($('<i>').addClass('icon-chevron-left pointer pager-cursor').text("<").click(function(){$this.evolugrid('refresh',noPage-1);}));
		    		}
		    		var pagerText = "Page "+(noPage+1);
		    		
		    		if (data.count != null) {
		    			pagerText += " / "+(pageCount+1);
		    		}
		    		pager.append($('<span>').text(pagerText));
		    		
		    		if((data.count != null && noPage<pageCount) || (data.count == null && extendedDescriptor.limit && data.data.length == extendedDescriptor.limit)){
		    			pager.append($('<i>').addClass('icon-chevron-right pointer pager-cursor').text(">").click(function(){$this.evolugrid('refresh',noPage+1);}));
		    		}
	    		}

	    		$this.append(pager);
	    		
	    		// Finally, let's enable buttons again:
		    	if (descriptor.filterFormSubmitButton) {
		    		$(descriptor.filterFormSubmitButton).attr("disabled", false);
		    	} else if (descriptor.filterForm) {
		    		$(descriptor.filterForm).find("button").attr("disabled", false);
		    		$(descriptor.filterForm).find("input[type=button]").attr("disabled", false);
		    	}
		    	
		    	//We hide the ajax loader
		    	$this.next('div.ajaxLoader').hide();
		    	
		    	if (descriptor.fixedHeader) {
    				//Enable fixed header for the table
    				table.fixedHeader({topOffset:headerTopOffset});
    			}
	    	},
	    	error : function(err,status) { 
	    		console.error("Error on ajax callback: "+status);
	    	}
	    	
	    	})
	    },	    
	    scroll : function(init) {
	    	var descriptor=$(this).data('descriptor');
	    	
	    	//We show the ajax loader
	    	$(this).next('div.ajaxLoader').show();
	    		    	    		    	
	    	// While refreshing, let's make sure noone touches the buttons!
	    	// FIXME: we should check which are already disabled and not reenable them later.... 
	    	if (descriptor.filterFormSubmitButton) {
	    		$(descriptor.filterFormSubmitButton).attr("disabled", true);
	    	} else if (descriptor.filterForm) {
	    		$(descriptor.filterForm).find("button").attr("disabled", true);
	    		$(descriptor.filterForm).find("input[type=button]").attr("disabled", true);
	    	}
	    	
	    	if (init) {
	    		scrollOffset = 0;
	    	}
	    	
	    	var $this=$(this);
	    	var filters = _getFilters(descriptor);
	    	filters.push({"name":"offset", "value": scrollOffset});
	    	filters.push({"name":"limit", "value": descriptor.limit});
	    	filters.push({"name":"sort_key", "value": sortKey});
	    	filters.push({"name":"sort_order", "value": sortOrder});
	    		    	
	    	$.ajax({url:descriptor.url, dataType:'json', data : filters,
		    	success: function(data){
			    	var extendedDescriptor=$.extend(true, {}, descriptor, data.descriptor)
			    	
			    	if (init) {
			    		//construct th
			    		$this.html("");
			    		var table= $('<table>').appendTo($this);
			    		var thead = $('<thead>').appendTo(table);
			    		var tbody = $('<tbody>').appendTo(table);
			    		var tr=$('<tr>');
			    		thead.append(tr);
			    		table.addClass(extendedDescriptor.tableClasses);		    		
			    		_generateHeaders(tr, extendedDescriptor, $this);
			    		
			    		if (descriptor.fixedHeader) {
			    			table.addClass("table-fixed-header");
			    			thead.addClass("header");
			    		}
			    	}
		    		
		    		//construct td
		    		for (var i=0;i<data.data.length;i++){
		    			tr=$('<tr>');
		    			if (extendedDescriptor.rowCssClass) {
		    				tr.addClass(data.data[i][extendedDescriptor.rowCssClass]);
		    			}
		    			$this.find('tbody').append(tr);
		    			for(var j=0;j<extendedDescriptor.columns.length;j++){
		    				var td=$('<td>');
		    				// jsdisplay is used when the data comes in JSON from the server (and you want js display)
		    				// if jsdipslay is used, display is ignored.
		    				var jsdisplay=extendedDescriptor.columns[j].jsdisplay;
		    				if (jsdisplay) {
		    					// Let's eval the function (its evil) and let's execute it.
		    					var myfunc = (new Function("return " + jsdisplay))();
		    					var html=myfunc(data.data[i]);
		    				} else {
			    				var display=extendedDescriptor.columns[j].display;
			    				if (display) {
				    				if(typeof display == 'function'){
				    					var html=display(data.data[i]);
				    				}else {
				    					var html=data.data[i][display];
				    					if (html === 0) {
				    						html = "0";
				    					}
				    				}
			    				}
		    				}
		    				if(html){
		    					td.html(html);
			    			}
		    				tr.append(td);
			    		}   			
		    		}
		    		
		    		if (init) {
		    			//construct no more results
		    			var noMoreResultsDiv = $('<div>').html("> No more results <").addClass("noMoreResults").css({'display':'none', 'font-style':'italic', 'text-align':'center', 'margin-top':20, 'margin-bottom':20});
		    			$this.append(noMoreResultsDiv);
		    					    			
		    			if (descriptor.fixedHeader) {
		    				//Enable fixed header for the table
		    				table.fixedHeader({topOffset:headerTopOffset});
		    			}
		    		}
			    			    		
		    		// Finally, let's enable buttons again:
			    	if (descriptor.filterFormSubmitButton) {
			    		$(descriptor.filterFormSubmitButton).attr("disabled", false);
			    	} else if (descriptor.filterForm) {
			    		$(descriptor.filterForm).find("button").attr("disabled", false);
			    		$(descriptor.filterForm).find("input[type=button]").attr("disabled", false);
			    	}
			    	
			    	//We hide the ajax loader
			    	$this.next('div.ajaxLoader').hide();
			    	
			    	if (descriptor.fixedHeader) {
			    		//we compute the header size for fixed header
			    		$this.find('table').fixedHeaderComputeHeaderWidth();
			    	}
			    				    	
			    	// We update the scroll offset
			    	scrollOffset = scrollOffset + parseInt(descriptor.limit);
			    	
			    	// Enable scroll again
		    		scrollReady = true;
			    	
		    		// No more results
			    	if (data.data.length == 0) {
			    		scrollNoMoreResults = true;
			    		$this.find('div.noMoreResults').show();
			    	} 			    	
		    	},
		    	error : function(err,status) { 
		    		console.error("Error on ajax callback for scroll: "+status);
		    	}
		    	})
	    }
	  };

	  $.fn.evolugrid = function( method ) {	    
	    // Method calling logic
	    if ( methods[method] ) {
	      return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
	    } else if ( typeof method === 'object' || ! method ) {
	      return methods.init.apply( this, arguments );
	    } else {
	      $.error( 'Method ' +  method + ' does not exist on jQuery.evolugrid' );
	    }    
	  
	  };	
})(jQuery);


//To fix header (optional)
//Inspired from https://github.com/oma/table-fixed-header

(function($) {

	$.fn.fixedHeader = function (options) {
	 var config = {
	   topOffset: 0
	 };
	 if (options){ $.extend(config, options); }

	 return this.each( function() {
	  var o = $(this);

	  var $win = $(window)
	    , $head = $('thead.header', o)
	    , isFixed = 0;
	  var headTop = $head.length && $head.offset().top - config.topOffset;

	  function processScroll() {
	    if (!o.is(':visible')) return;
	    if ($('thead.header-copy').size())
	    var i, scrollTop = $win.scrollTop();
	    var t = $head.length && $head.offset().top - config.topOffset;
	    if (!isFixed && headTop != t) { headTop = t; }
	    if (scrollTop >= headTop && !isFixed) {
	      isFixed = 1;
	    } else if (scrollTop <= headTop && isFixed) {
	      isFixed = 0;
	    }
	    isFixed ? $('thead.header-copy', o).removeClass('hide')
	            : $('thead.header-copy', o).addClass('hide');
	  }
	  
	  $win.on('scroll', processScroll);

	  $head.on('click', function () {
	    if (!isFixed) setTimeout(function () {  $win.scrollTop($win.scrollTop() - config.topOffset) }, 10);
	  })

	  $head.clone().removeClass('header').addClass('header-copy header-fixed').appendTo(o);
	  
	  $(this).fixedHeaderComputeHeaderWidth();
	  
	  $head.css({ margin:'0 auto',
	              width: o.width()});
	  
	  $(this).find('.header-fixed').css({ 'position': 'fixed',
		  								  'top': config.topOffset,
		  								  'z-index': '1020'});
	  
	  processScroll();
	 });
	};
	
	$.fn.fixedHeaderComputeHeaderWidth = function () {
		var o = $(this);
		var $head = $('thead.header', o);
		var header_width = $head.width();
		o.find('thead.header-copy').width(header_width);
		o.find('thead.header > tr:first > th').each(function (i, h){
			var w = $(h).width();
		    o.find('thead.header-copy> tr > th:eq('+i+')').width(w)
		 });
	};
})(jQuery);