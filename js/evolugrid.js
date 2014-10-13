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
 *  infiniteScroll: boolean, // To set a infinite scroll instead of a pager
 *  fixedHeader: boolean, // To sfixed the header of the evolugrid table
 *  onRowClick: function, // Callback called when we click on a row. Callback signature: function(rowObject, event)
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
			"fixedHeader": false,
			"rowClick": false,
            "noResultsMessage": "> No results are available <",
            "chevronUpClass" : "icon-chevron-up glyphicon glyphicon-chevron-up",
            "chevronDownClass" : "icon-chevron-down glyphicon glyphicon-chevron-down"
	}

	var sortKey;
	var sortOrder;
	
	//Only use for the infinite scroll
	var scrollOffset; 
	var scrollReady;
	var scrollNoMoreResults;
	
	//Only use for fixed header behavior
	var headerTopOffset;
	
	//Only use for historic state
	var manualStateChange = true;

    //Message to display if no results are shown
    var noResultsMessage;
	
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
			var colSortKey;
			if (columnDescriptor.sortKey) {
				colSortKey = columnDescriptor.sortKey;
			} else {
				colSortKey = key;
			}
			if (columnDescriptor.sortable) {
				(function(colSortKey) {
					var sortButtonAsc = $('<a href="#" />').click(function() {
						sortKey = colSortKey;
						sortOrder = "asc";
						if (descriptor.infiniteScroll) {
							evolugrid.evolugrid('scroll', true);
						} else {
							evolugrid.evolugrid('refresh', 0);
						}
						return false;
					});
					sortButtonAsc.append("<i class='"+descriptor.chevronUpClass+"'></i>");
					th.append(" ");
					th.append(sortButtonAsc);
					
					var sortButtonDown = $('<a href="#" />').click(function() {
						sortKey = colSortKey;
						sortOrder = "desc";
						if (descriptor.infiniteScroll) {
							evolugrid.evolugrid('scroll', true);
						} else {
							evolugrid.evolugrid('refresh', 0);
						}
						return false;
					});
					sortButtonDown.append("<i class='"+descriptor.chevronDownClass+"'></i>");
					th.append(" ");
					th.append(sortButtonDown);
				})(colSortKey);
			}
			tr.append(th);
		}		
	}
	
	var _getUrlParams =  function () {
		  var vars = [], hash;
		  var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		  		  
		  if (hashes[0] === window.location.href || hashes[0] === "") {
			  return vars;
		  }
		  
		  for(var i = 0; i < hashes.length; i++)
		  {
		      hash = hashes[i].split('=');
		      
		      var temp = {};
		      temp.name = hash[0];
		      temp.value = decodeURI(hash[1].replace(/\+/g, '%20'));
		      vars.push(temp);
		  }
		  return vars;
		}
	
	var rowClickElement = function (descriptor, tr, el) {
		// If tr is clickable add js callback
		if(descriptor.onRowClick) {
			tr.click(function (event) {
				descriptor.onRowClick(el, event);
			})
		}
		
	}
	
	var registerRowEvents = function (descriptor, tr, el) {
		if (!descriptor.rowEventListeners){
			return;
		}
		$.each(descriptor.rowEventListeners, function(index, listener){
			tr.find('td:not(.exclude_row_listener)').on(listener.event, function (event){
				listener.callback(el, event);
			});
		})
		
	}
	
	var getPagerElement = function (element, page) {
		return $('<span/>').append($('<a/>').text(' '+(page+1)+' ')
				.css('cursor', 'pointer')
				.click(function(){element.evolugrid('refresh',page);return false;}));
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
                		 
                		var lastElementPositon = $this.find('table tbody tr:last-child').position();
                		                         		
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
                if (descriptor.searchHistory) {                  	
               	 	History.Adapter.bind(window,'popstate',function(){
	               	 	  // Ignore inital popstate that some browsers fire on page load
               	 		  var popped = ('state' in window.history && window.history.state !== null);
	               	 	  if ( !popped ) return
	               	 	  
               	 		if(manualStateChange == true){
	               	 		var state = History.getState();	            		
		                    if (descriptor.infiniteScroll) {   
		                    	scrollOffset = 0;
		            			scrollNoMoreResults = false;
		            			scrollReady = false;
		            			$this.evolugrid('scroll', true, state.data.filters);
		                    } else {
		                    	$this.evolugrid('refresh', 0, state.data.filters);
		                    }	         
		                    if (descriptor.searchHistoryAutoFillForm) {
			                    $.each(state.data.filters, function(index, item){
			            		    var $el = $('[name="'+item.name+'"]');
			            		    var type = $el.attr('type');
			
			            		    switch(type){
			            		        case 'checkbox':
			            		            $el.attr('checked', 'checked');
			            		            break;
			            		        case 'radio':
			            		            $el.filter('[value="'+item.value+'"]').attr('checked', 'checked');
			            		            break;
			            		        default:
			            		            $el.val(item.value);
			            		    }
			            		});
			                }
               	 		}
               	 		manualStateChange = true;
               	 });	
               }            
               if (descriptor.loadOnInit) {
            	   if (descriptor.searchHistory) {  
            		   var filters = _getUrlParams();
            		   if (filters.length === 0 ) {
            			   History.replaceState({}, 'evolugrid', window.location.pathname + '?');
            		   } else {
            			   History.replaceState({filters:filters}, 'evolugrid', window.location.pathname + '?' + $.param(filters));
            		   }
            	   } else {
            		   if (descriptor.infiniteScroll) {
            			   scrollOffset = 0;
       					   scrollNoMoreResults = false;
            			   scrollReady = false;
            			   $this.evolugrid('scroll', true);
            		   } else {
   							$this.evolugrid('refresh', 0);
            		   }
            	   }
                }
 	        });
	    },
	    csvExport : function(filters) {
	    	var descriptor=$(this).data('descriptor');
	    	
	    	filters = _getFilters(descriptor, filters);
	    	
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
	    refresh : function( noPage, filters) {
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
	    	
	    	if (descriptor.searchHistory) {
	    		manualStateChange = false;
	    		History.pushState({filters:filters}, null,  window.location.pathname + '?' + $.param(filters));
	    	}
	    	
	    	filters.push({"name":"offset", "value": noPage*descriptor.limit});
	    	filters.push({"name":"limit", "value": descriptor.limit});
	    	if (sortKey) {
	    		filters.push({"name":"sort_key", "value": sortKey});
	    	}
	    	if (sortOrder) {
	    		filters.push({"name":"sort_order", "value": sortOrder});
	    	}

	    	$.ajax({url:descriptor.url, cache: false, dataType:'json', data : filters,
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

                //Show the no results message if data.length = 0
                if (data.data.length == 0 && descriptor.infiniteScroll == false) {
                    var noMoreResultsDiv = $('<div>').html(descriptor.noResultsMessage).addClass("noMoreResults").css({'font-style':'italic', 'text-align':'center', 'margin-top':20, 'margin-bottom':20});
                    $this.append(noMoreResultsDiv);
                }

	    		//construct td
	    		for (var i=0;i<data.data.length;i++){
	    			tr=$('<tr>');
	    			var dataTemp = data.data[i];
	    			rowClickElement(descriptor, tr, dataTemp);

	    			if (extendedDescriptor.rowCssClass) {
	    				tr.addClass(data.data[i][extendedDescriptor.rowCssClass]);
	    			}
	    			tbody.append(tr);
	    			for(var j=0;j<extendedDescriptor.columns.length;j++){
	    				var td=$('<td>');
	    				// variable to escape HTML tag
	    				var escape = false;
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
			    					if(typeof extendedDescriptor.columns[j].escapeHTML == 'undefined' || extendedDescriptor.columns[j].escapeHTML == true) {
			    						escape = true;
			    					}
			    					var html=data.data[i][display];
			    					if (html === 0) {
			    						html = "0";
			    					}
			    				}
		    				}
	    				}
	    				if(html){
	    					if(escape) {
		    					td.text(html);
	    					}
	    					else {
		    					td.html(html);	
	    					}
		    			}
                        var cssClass = extendedDescriptor.columns[j].cssClass;
                        if (cssClass){
                            td.addClass(cssClass);
                        }
	    				tr.append(td);
		    		}

                    registerRowEvents(descriptor, tr, dataTemp);
	    		}
	    		//construct pager
	    		var pager=$('<div>').addClass("pager");
	    		if(extendedDescriptor.pagerId){
	    			pager.attr('id',extendedDescriptor.pagerId);
	    		}
	    		
	    		    		
	    		if (extendedDescriptor.export_csv) {
	    			var span = $('<span/>').click(function(){$this.evolugrid('csvExport');});
	    			span.append($('<i/>').addClass('glyphicon glyphicon-file').attr('style', 'cursor:pointer;'));
	    			span.append("Export to CSV");
	    			pager.append(span);
	    		}
	    		
	    		// Number of all pages
	    		var pageCount = null;
	    		if (data.count != null) {
	    			pageCount=Math.ceil(data.count/extendedDescriptor.limit);
	    		}
	    		
	    		// Number max of element aroundthe page selected
	    		var pageElement = 2;
	    		
	    		if (pageCount>1) {
	    			// Add the < at the start
		    		if(noPage>0){
		    			pager.append($('<i>').addClass('icon-chevron-left pointer pager-cursor glyphicon glyphicon-chevron-left')//.text("<")
		    					.css('cursor', 'pointer')
		    					.click(function(){$this.evolugrid('refresh',noPage-1);}));
		    		}
		    		
		    		// If the page select is inferior of the number element display
		    		if(noPage - pageElement <= 0) {
		    			// Display only the possible element 
		    			for(var i = 0; i < noPage; i ++) {
		    				pager.append(getPagerElement($this, i));
		    				pager.append($('<span/>').text('-'));
			    		}
		    		}
		    		// Else display the first element, after ... if the number of page don't followed
		    		// end the x page (x is the number of dispayed element) 
		    		else {
		    			// First page
		    			pager.append(getPagerElement($this, 0));
		    			// The ... if the page is far of the first page
		    			if(noPage - pageElement > 1) {
		    				pager.append($('<span>').text('...'));
		    			}
		    			else {
		    				pager.append($('<span/>').text('-'));
		    			}
		    			// The x element
		    			for(var i = noPage - pageElement; i < noPage; i ++) {
		    				pager.append(getPagerElement($this, i));
		    				pager.append($('<span/>').text('-'));
			    		}
		    		}
	    		
	    			// Display the current page (it isn't a link)
		    		var pagerText = (noPage+1);
		    		pager.append($('<span>').text(pagerText));
		    		
		    		// Display the page after the page selected
		    		// If the page selected is near the end
    				if(pageCount - pageElement - 1 <= noPage) {
    					for(var i = noPage + 1; i < pageCount; i ++) {
		    				pager.append($('<span/>').text('-'));
		    				pager.append(getPagerElement($this, i));
			    		}
    				}
    				// Else display the x page and the ... and the last page
    				else {
    		    		if(noPage + pageElement < pageCount) {
    			    		for(var i = noPage + 1; i <= noPage + pageElement; i ++) {
    		    				pager.append($('<span/>').text('-'));
    		    				pager.append(getPagerElement($this, i));
    			    		}
    		    		}
    		    		if(pageCount != noPage + pageElement + 2) {
    		    			pager.append($('<span>').text('...'));
    		    		}
    		    		else {
		    				pager.append($('<span/>').text('-'));
    		    			
    		    		}
    					pager.append(getPagerElement($this, pageCount - 1));
    				}

    				// Display the > at the end
		    		if((data.count != null && (noPage+1)<pageCount) || (data.count == null && extendedDescriptor.limit && data.data.length == extendedDescriptor.limit)){
		    			pager.append($('<i>').addClass('icon-chevron-right pointer pager-cursor glyphicon glyphicon-chevron-right')//.text(">")
		    					.css('cursor', 'pointer')
		    					.click(function(){$this.evolugrid('refresh',noPage+1);}));
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

                $this.evolugrid('loaded');
	    	},
	    	error : function(err,status) { 
	    		console.error("Error on ajax callback: "+status);
	    	}
	    	
	    	})
	    },	    
	    scroll : function(init, filters) {
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
	    	filters = _getFilters(descriptor, filters);
	    	
	    	if (descriptor.searchHistory) {
	    		manualStateChange = false;
	    		History.pushState({filters:filters}, null, window.location.pathname + '?' + $.param(filters));
	    	}
	    	
	    	filters.push({"name":"offset", "value": scrollOffset});
	    	filters.push({"name":"limit", "value": descriptor.limit});
	    	if (sortKey) {
	    		filters.push({"name":"sort_key", "value": sortKey});
	    	}
	    	if (sortOrder) {
	    		filters.push({"name":"sort_order", "value": sortOrder});
	    	}
	    		    		    	
	    	$.ajax({url:descriptor.url, dataType:'json', cache: false, data : filters,
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
		    			var dataTemp = data.data[i];
		    			rowClickElement(descriptor, tr, dataTemp);
		    			
		    			if (extendedDescriptor.rowCssClass) {
		    				tr.addClass(data.data[i][extendedDescriptor.rowCssClass]);
		    			}
		    			$this.find('tbody').append(tr);
		    			for(var j=0;j<extendedDescriptor.columns.length;j++){
		    				var td=$('<td>');
		    				// variable to escape HTML tag
		    				var escape = false;
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
				    					if(typeof extendedDescriptor.columns[j].escapeHTML == 'undefined' || extendedDescriptor.columns[j].escapeHTML == true) {
				    						escape = true;
				    					}
				    					var html=data.data[i][display];
				    					if (html === 0) {
				    						html = "0";
				    					}
				    				}
			    				}
		    				}
		    				if(html){
		    					if(escape) {
			    					td.text(html);
		    					}
		    					else {
			    					td.html(html);	
		    					}
			    			}

                            var cssClass = extendedDescriptor.columns[j].cssClass;
                            if (cssClass){
                                td.addClass(cssClass);
                            }

		    				tr.append(td);
			    		}

                        registerRowEvents(descriptor, tr, dataTemp);
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
			    	if (data.data.length == 0 || data.data.length < descriptor.limit) {
			    		scrollNoMoreResults = true;
			    		$this.find('div.noMoreResults').show();
			    	}

                    $this.evolugrid('loaded');
		    	},
		    	error : function(err,status) { 
		    		console.error("Error on ajax callback for scroll: "+status);
		    	}
		    	})
	    },
        loaded : function(){
            var descriptor=$(this).data('descriptor');
            if (descriptor.onResultShown){
                descriptor.onResultShown();
            }
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