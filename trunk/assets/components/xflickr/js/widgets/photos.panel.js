XFlickr.panel.Photos = function(config) {
	config = config || {};
	Ext.apply(config,{
		border: false,
		baseCls: 'modx-panel',
		items: [{
			html: '<h2>XFlickr '+_('xflickr.photos')+'</h2>',
			border: false,
			cls: 'modx-page-header'
		},{
			xtype: 'panel',
			defaults: {},
			items: [{
				xtype: 'xflickr-panel-dataview'
			}]
		}]
	});
	XFlickr.panel.Photos.superclass.constructor.call(this,config);
};
Ext.extend(XFlickr.panel.Photos,MODx.Panel);
Ext.reg('xflickr-panel-photos',XFlickr.panel.Photos);

XFlickr.panel.Dataview = function(config) {
	config = config || {};
	this.perPage = 25;
	this.loadPhotoStore(config);
	this.getThumbsView();
	this.getPhotoContextMenu();
	this.getDetailsTpl();
	Ext.apply(config,{
		layout: 'border',
		minWidth: 500,
		minHeight: 500,
		height: 500,
		border: false,
		bodyStyle: 'padding:5px;',
		tbar: [{
			xtype: 'xflickr-navigation'
		}],
		items:[{
			id: 'photo-chooser-view',
			region: 'center',
			autoScroll: true,
			items: this.thumbsView,
			layout: 'fit',
			tbar:[{
				text: _('xflickr.filter_by_set')+':'
			}, {
				id: 'filterSelect',
				xtype: 'combo',
				typeAhead: true,
				triggerAction: 'all',
				width: 200,
				editable: false,
				displayField: 'title',
				valueField: 'set_id',
				lazyInit: false,
				emptyText: _('xflickr.all_photos'),
				store: new Ext.data.JsonStore({
					url: XFlickr.config.connector_url,
					baseParams: { 
						action: 'mgr/photos/getsetsfilterlist'
					},
					root: 'object',
					totalProperty: 'total',
					autoDestroy: true,
					autoLoad: true,
					fields: ['set_id', 'title']
				}),
				listeners: {
					'select': {fn:this.filterPhotos, scope:this}
				}
			}],
			bbar: new Ext.PagingToolbar({
				store: this.photoStore,
				displayInfo: true,
				id: 'photo-view-paging-bar',
				displayMsg: 'Displaying photos {0} - {1} of {2}',
				pageSize: this.perPage,
				prependButtons: true
			})
		},{
			id: 'photo-detail-panel',
			region: 'east',
			split: true,
			width: 250,
			minWidth: 250,
			maxWidth: 280
		}]
	});
	XFlickr.panel.Dataview.superclass.constructor.call(this,config);
};
Ext.extend(XFlickr.panel.Dataview,MODx.Panel,{
	getDetailsTpl : function() {
		this.detailsTemplate = new Ext.XTemplate(
			'<div class="photo-details">',
				'<tpl for=".">',
					'<div style="padding: 5px; text-align: center">',
					'<img src="{url_s}">',
					'</div>',
					
					'<div class="details-info">',
					'<p><span>'+_('xflickr.title')+': </span>',
					'{title}</p>',
					'<p><span>'+_('xflickr.uploaded')+': </span>',
					'{dateupload}</p>',
					'<p><span>'+_('xflickr.taken')+': </span>',
					'{datetaken}</p>',
					'<p><span>'+_('xflickr.original_size')+': </span>',
					'{width_o}px x {height_o}px</p>',
					'<tpl if="tags">',
						'<p><span>'+_('xflickr.tags')+': </span>',
						'{tags}</p>',
					'</tpl>',
					'<p><span>'+_('xflickr.views')+': </span>',
					'{views}</p>',
					'<p><a href="http://www.flickr.com/photos/{pathalias}/{id}" target="_blank">'+_('xflickr.view_on_flickr')+'</a></p>',
					
					
				'</tpl>',
			'</div>'
		);
		this.detailsTemplate.compile();
	},
	loadPhotoStore : function(config) {
        this.photoStore = new Ext.data.JsonStore({
            url: XFlickr.config.connector_url,
            baseParams: { 
                action: 'mgr/photos/getlist',
				per_page: this.perPage
            },
            root: 'object',
            fields: [
				'id',
				'owner',
				'secret',
				'server',
				'farm',
				'title',
				'ispublic',
				'isfriend',
				'isfamily',
				'license',
				{name:'dateupload', type:'date', dateFormat:'timestamp'},
				{name:'datetaken', type:'date', dateFormat:'Y-m-d H:i:s'},
				'datetakengranularity',
				'ownername',
				{name:'lastupdate', type:'date', dateFormat:'timestamp'},
				'latitude',
				'longitude',
				'accuracy',
				'tags',
				'machine_tags',
				{name:'views', type: 'int'},
				'media',
				'media_status',
				'pathalias',
				'url_sq', 'height_sq', 'width_sq',
				'url_t', {name:'height_t', type:'int'}, {name:'width_t', type:'int'},
				'url_s', {name:'height_s', type:'int'}, {name:'width_s', type:'int'},
				'url_m', {name:'height_m', type:'int'}, {name:'width_m', type:'int'},
				'url_o', {name:'height_o', type:'int'}, {name:'width_o', type:'int'}
			],
            totalProperty: 'total',
			autoDestroy: true,
			idIndex: 0,
			storeId: 'photoStore',
            listeners: {
                'load': {fn:function(){
						this.thumbsView.select(0);
					},
					scope:this, single:true
				},
				'beforeload': {
					fn:function(){
						this.photoStore.baseParams.filter = this.getSetsFilter();
					},
					scope:this
				}
            }
        });
        this.photoStore.load();
	},
	getSetsFilter : function() {
		var filter;
		if (Ext.getCmp('filterSelect')) {
			filter = Ext.getCmp('filterSelect').getValue();
			if (!filter) {
				filter = 'all';
			}
		} else {
			filter = 'all';
		}
		return filter;
	},
	getThumbsView : function() {
		this.thumbsView = new Ext.DataView({
			id: 'thumbs-view',
			tpl:  new Ext.XTemplate(
				'<tpl for=".">',
					'<div class="thumb-wrap" id="{id}">',
					'<div class="thumb"><img src="{url_t}" title="{title}" height="{height_t}" width="{width_t}" /></div>',
					'<span>{title}</span></div>',
				'</tpl>'
			),
			singleSelect: true,
			overClass:'x-view-over',
			itemSelector: 'div.thumb-wrap',
			emptyText : '<div style="padding:10px;">'+_('xflickr.filter_no_photos')+'</div>',
			store: this.photoStore,
			listeners: {
				'selectionchange': {fn:this.showDetails, scope:this, buffer:100},
				'contextmenu': {fn:this.showPhotoContextMenu, scope:this}
			}
		});
	},
	filterPhotos : function(){
    	this.thumbsView.store.load({params: {'filter': this.getSetsFilter()}});
    	this.thumbsView.select(0);
    },
	showDetails : function(){
	    var selNode = this.thumbsView.getSelectedNodes();
	    var detailEl = Ext.getCmp('photo-detail-panel').body;
		if(selNode && selNode.length > 0){
			selNode = selNode[0];
			var row = this.thumbsView.store.getById(selNode.id);
            detailEl.hide();
            this.detailsTemplate.overwrite(detailEl, row.data);
            detailEl.fadeIn('l', {stopFx:true,duration:.2});
		}else{
		    detailEl.update('');
		}
	},
	getPhotoContextMenu: function() {
		this.photoContextMenu = new Ext.menu.Menu({
			id: 'photoContextMenu',
			items: [{
				text: _('xflickr.show_photo_urls'),
				iconCls: 'xf-urls16',
				handler: this.showPhotoUrls
			},{
				text: _('xflickr.edit'),
				iconCls: 'xf-edit16',
				handler: this.editPhotoInfo
			},{
				text: _('xflickr.delete'),
				iconCls: 'xf-delete16',
				handler: this.deletePhoto
			}]
		});
	},
	showPhotoContextMenu: function(view, index, node, e) {
		var sindex = view.getSelectedIndexes();
		if (!sindex[0] || sindex[0] != index) {
			view.select(index);
		}
		e.stopEvent();
		var coords = e.getXY();
		this.photoContextMenu.showAt([coords[0], coords[1]]);
	},
	showPhotoUrls: function(btn, e) {
            this.showPhotoUrlsWindow = MODx.load({
                xtype: 'xflickr-window-show-urls'
            });
        this.showPhotoUrlsWindow.show(e.target);
        
    },
    editPhotoInfo: function(btn,e) {
		if (!this.updatePhotoInfo) {
			this.updatePhotoInfo = MODx.load({
				xtype: 'xflickr-window-edit-photo',
				listeners: {
					'success': {fn:function(){
							var store = Ext.getCmp('thumbs-view').store;
							store.reload(store.lastOptions);
						},scope:this
					}
				}
			});
		}
		this.updatePhotoInfo.show(e.target);
    },
	deletePhoto: function() {
		var snodes = Ext.getCmp('thumbs-view').getSelectedNodes();
        MODx.msg.confirm({
            title: _('warning'),
            text: _('xflickr.photo_delete_confirm'),
            url: XFlickr.config.connector_url,
            params: {
                action: 'mgr/photos/delete',
                photo_id: snodes[0].id
            },
			listeners: {
				'success': {fn:function(){
						var store = Ext.getCmp('thumbs-view').store;
						store.reload(store.lastOptions);
					},scope:this
				}
			}
        });
    },
	getSelectedPhoto: function() {
		var snodes = Ext.getCmp('thumbs-view').getSelectedNodes();
		return snodes[0].id;
	}
});
Ext.reg('xflickr-panel-dataview',XFlickr.panel.Dataview);

XFlickr.window.ShowUrls = function(config) {
    config = config || {};
	this.getSizesStore();
    Ext.applyIf(config,{
        title: _('xflickr.show_photo_urls'),
        url: XFlickr.config.connector_url,
        baseParams: {
            action: 'mgr/photos/geturls'
        },
        width: 640,
		y: 150,
		autoHeight: true,
		modal: Ext.isIE ? false : true,
		items: new MODx.Panel({
			items: new Ext.DataView ({
				store : this.sizesStore,
				itemSelector: 'div.photo-url',
				tpl : new Ext.XTemplate(
				   '<tpl for=".">',
						'<div class="photo-url" style="padding: 5px 10px">',
						'<p><label for="photo-url-{label}">{label} ({width}px x {height}px)</label> ',
						'<input type="textfield" id="photo-url-{label}" class="x-form-text x-form-field" style="width: 400px" value="{source}" /></p>',
						'</div',
					'</tpl>'
				)
			})
		}),
		buttons: [{
			text: _('ok')
			,handler: function() { this.close(); }
			,scope: this
		}]
    });
    XFlickr.window.ShowUrls.superclass.constructor.call(this,config);
};
Ext.extend(XFlickr.window.ShowUrls,MODx.Window,{
	getSizesStore : function(config) {
        this.sizesStore = new Ext.data.JsonStore({
            url: XFlickr.config.connector_url,
            baseParams: { 
                action: 'mgr/photos/geturls'
            },
            root: 'object',
            fields: [
				'label',
				{name:'width', type: 'int'},
				{name:'height', type: 'int'},
				'source',
				'url',
				'media'
			],
			autoDestroy: true,
			autoLoad: false,
			idIndex: 0,
			storeId: 'sizesStore'
        });
		var snodes = Ext.getCmp('thumbs-view').getSelectedNodes();
		this.sizesStore.reload({params: {photo_id: snodes[0].id}});
	}
});
Ext.reg('xflickr-window-show-urls',XFlickr.window.ShowUrls);

XFlickr.window.EditPhoto = function(config) {
    config = config || {};
	Ext.applyIf(config,{
		title: _('xflickr.edit_photo'),
		url: XFlickr.config.connector_url,
        baseParams: {
            action: 'mgr/photos/updateinfo'
        },
		id: 'xflickr-window-edit-photo',
		width: 600,
		autoHeight: true,
		modal: Ext.isIE ? false : true,
		fields: [{
			xtype: 'hidden',
			name: 'id'
		},{
			xtype: 'textfield',
			name: 'title',
			fieldLabel: _('xflickr.title'),
			width: 390
		},{
			xtype: 'textarea',
			name: 'description',
			fieldLabel: _('xflickr.description'),
			width: 390,
			grow: true
		},{
			xtype: 'textfield',
			name: 'alltags',
			fieldLabel: _('xflickr.tags'),
			width: 390
		}]
	});
    XFlickr.window.EditPhoto.superclass.constructor.call(this,config);
	this.getInfoStore();
	this.on('show', function() {
		var snodes = Ext.getCmp('thumbs-view').getSelectedNodes();
		this.infoStore.reload({params: {photo_id: snodes[0].id}});
	});
};
Ext.extend(XFlickr.window.EditPhoto,MODx.Window,{
	getInfoStore : function(config) {
        this.infoStore = new Ext.data.JsonStore({
            url: XFlickr.config.connector_url,
            baseParams: {
                action: 'mgr/photos/getinfo'
            },
            root: 'object',
            fields: [
				'id',
				{name:'title', mapping: 'title._content'},
				{name:'description', mapping: 'description._content'},
				'tags',
				'alltags'
			],
			autoDestroy: true,
			allowNull: true,
			idIndex: 0,
			storeId: 'infoStore',
			listeners: {
                'load': {fn:function(){
						this.setValues(this.infoStore.getAt(0).data);
					},
					scope:this
				}
			}
        });
	}
});
Ext.reg('xflickr-window-edit-photo',XFlickr.window.EditPhoto);
