jQuery.fn.XGallery = function(opts) {
	opts = jQuery.extend({
		items_per_page:25,
		init_page: 1,
		prev_text: "&#8592;",
		next_text:"&#8594;",
		ellipse_text:"&#8230;"
	},opts||{});

	return this.each(function() {
		var xgContainer = jQuery(this);
		var xgPhotos = jQuery("<div />")
			.addClass("xgallery-photos")
			.appendTo(xgContainer);
		function loadData(page_id) {
			var p = jQuery.extend({},XGalleryBaseAjax,{
				data: {
					'action': 'web/gallery/getphotos',
					'set': opts.set,
					'page': page_id,
					'perpage': opts.perpage,
					'size': opts.size,
					'fullSize': opts.fullSize
				},
				beforeSend: function(){
					xgPhotos.css({opacity:0.5});
				},
				complete: function(){
					xgPhotos.css({opacity:1});
				},
				success: function(r) {
					if (r.success == false) {
						jQuery("<div />")
						.addClass("xgallery-error")
						.html(r.message)
						.appendTo(xgPhotos);
						return false;
					}

					//var out = '';
					xgPhotos.empty();
					jQuery.each(r.object, function(i, photo) {
						//out += '<div class="xgallery-photo" style="width: '+ photo.width +'">';
						//out += '<a href="'+photo.larger_url+'" title="'+photo.title+'" rel="'+opts.set+'">';
						//out += '<img src="'+photo.url+'" width='+photo.width+' height='+photo.height+' />';
						//out += '</a></div>';

						jQuery('<div/>')
						.addClass('xgallery-photo')
						.css('width', photo.width)
						.append(
							jQuery('<a/>')
							.attr('href', photo.larger_url)
							.attr('title', photo.title)
							.attr('rel', opts.set)
							.append(
								jQuery('<img/>')
								.attr('src', photo.url)
								.attr('width', photo.width)
								.attr('height', photo.height)
							)
						)
						.appendTo(xgPhotos);
					});

					//xgPhotos.html(out);
					jQuery("a[rel='"+opts.set+"']").colorbox({maxWidth: "98%", maxHeight: "98%"});
					return false;
				}
			});
			jQuery.ajax(p);
		}

		function numPages() {
			return Math.ceil(opts.total/opts.perpage);
		}

		function pageSelected(event){
			current_page = event.data.page_id;
			drawPagination();
			loadData(current_page);
			if (event.stopPropagation) {
				event.stopPropagation();
			} else {
				event.cancelBubble = true;
			}
		}

		function drawPagination() {
			xgPagination.empty();
			var np = numPages();
			var appendItem = function(page_id, appendopts) {
				page_id = page_id<1 ? 1 : (page_id<=np ? page_id : np);
				appendopts = jQuery.extend({text:page_id, classes:""}, appendopts||{});
				if(page_id == current_page) {
					var lnk = jQuery("<span class='current'>"+(appendopts.text)+"</span>");
				} else {
					var lnk = jQuery("<span>"+(appendopts.text)+"</span>")
						.bind("click", {page_id: page_id}, pageSelected);
				}
				if(appendopts.classes){lnk.addClass(appendopts.classes);}
				xgPagination.append(lnk);
			}
			//Prev link
			if (opts.prev_text && (current_page >= 1)) {
				appendItem(current_page-1,{text:opts.prev_text, classes:"prev"});
			}

			// Internal links
			if (np<=11) {
				for (var i=1; i<=np; i++) {
					appendItem(i);
				}
			} else {
				if (current_page<7) {
					for (var i=1; i<=7; i++) {
						appendItem(i);
					}
					jQuery("<span class='ellipse'>"+opts.ellipse_text+"</span>").appendTo(xgPagination);
					for (var i=np-2; i<=np; i++) {
						appendItem(i);
					}
				} else if (np-current_page<6) {
					for (var i=1; i<=3; i++) {
						appendItem(i);
					}
					jQuery("<span class='ellipse'>"+opts.ellipse_text+"</span>").appendTo(xgPagination);
					for (var i=np-6; i<=np; i++) {
						appendItem(i);
					}
				} else {
					for (var i=1; i<=3; i++) {
						appendItem(i);
					}
					jQuery("<span class='ellipse'>"+opts.ellipse_text+"</span>").appendTo(xgPagination);
					for (var i=current_page-1; i<=current_page+1; i++) {
						appendItem(i);
					}
					jQuery("<span class='ellipse'>"+opts.ellipse_text+"</span>").appendTo(xgPagination);
					for (var i=np-2; i<=np; i++) {
						appendItem(i);
					}
				}
			}
			// Next link
			if(opts.next_text && (current_page <= np)){
				appendItem(current_page+1,{text:opts.next_text, classes:"next"});
			}
		}
		var current_page = opts.init_page;
		if(opts.pagination) {
			var xgPagination = jQuery("<div />")
				.addClass("xgallery-pagination")
				.appendTo(xgContainer);
			drawPagination();
			loadData(current_page);
		} else {
			loadData(current_page);
		}
    });
}
