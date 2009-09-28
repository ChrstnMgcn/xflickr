XFlickr.panel.Summary = function(config) {
	config = config || {};
	Ext.apply(config,{
		border: false,
        autoHeight:true,
		baseCls: 'modx-panel',
		items: [{
			html: '<h2>XFlickr '+_('xflickr.summary')+'</h2>',
			border: false,
			cls: 'modx-page-header'
		},{
			xtype: 'panel',
			defaults: {},
			items: [{
				xtype: 'xflickr-panel-userinfo',
				preventRender: true
			}]
		}]
	});
	XFlickr.panel.Summary.superclass.constructor.call(this,config);
};
Ext.extend(XFlickr.panel.Summary,MODx.Panel);
Ext.reg('xflickr-panel-summary',XFlickr.panel.Summary);

XFlickr.panel.Userinfo = function(config) {
	config = config || {};
	this.tpl = this.createTpl();
	Ext.apply(config,{
		url: XFlickr.config.connector_url,
		border: false,
		bodyStyle: 'padding:5px',
        xtype: 'panel',
		tbar: [{
			xtype: 'xflickr-navigation'
		}],
		items:[{
			html: '<div id="xf-summary-content" style="width: 100%;"></div>'
		},{
            border: false,
            html: '<div id="xf-summary-bottom" style="width: 100%;">v0.1-alpha1</div>'
        }],
        listeners: {
			'setup': {fn:this.setup,scope:this}
        }
	});
	XFlickr.panel.Userinfo.superclass.constructor.call(this,config);
};
Ext.extend(XFlickr.panel.Userinfo,MODx.FormPanel,{
        setup: function() {
        MODx.Ajax.request({
            url: XFlickr.config.connector_url,
            params: {
                action: 'mgr/summary/getsummary'
            },
            listeners: {
                'success': {fn:function(r) {
					this.tpl.overwrite('xf-summary-content', r.object);
                    this.fireEvent('ready',r.object);
                },scope:this}
            }
        });
    },
	createTpl: function(config) {
		this.tpl = new Ext.XTemplate(
			'<div class="xf-short-profile" style="padding: 1em;">',
			'<tpl for=".">',
				'<div class="xf-profile" style="clear: both; margin-bottom: 10px;">',
				'<div style="float: left; width: 320px;">',
                '<img src="{buddyicon}" alt="{realname}" width="48" height="48" style="float: left; padding: 5px;" />',
                '<h2 style="widht: 260px; line-height: 28px; border: none; margin-bottom: 0;">{realname}</h2>',
                '<span style="display: block; widht: 260px; line-height: 18px; float: left;">'+_('xflickr.acc_type')+': <b>{acc_type}</b></span>',
                '</div>',
                '<div style="float: left">',
                '<span>'+_('xflickr.profile_url')+': <a href="{profileurl}" target="_blank">{profileurl}</a></span><br />',
				'<span>'+_('xflickr.photos_url')+': <a href="{photosurl}" target="_blank">{photosurl}</a></span><br />',
				'<tpl if="location">',
				'<span>'+_('xflickr.user_location')+': {location}</span><br />',
				'</tpl>',
				'</div>',
                '</div>',

                '<div class="xf-short-stats" style="clear: both;">',
                '<h3>'+_('xflickr.statistics')+'</h3>',
				'<tpl if="photos_count &lt; 1">',
				'<span>'+_('xflickr.total')+': '+_('xflickr.no_photos')+'</span><br />',
				'</tpl>',
				'<tpl if="photos_count &gt; 0">',
				'<span>'+_('xflickr.total')+': <b>{photos_count}</b> '+_('xflickr.photos')+' - <b>{views_count}</b> '+_('xflickr.views')+'</span><br />',
				'</tpl>',
				'<tpl if="favorites_count == 0">',
				'<span>'+_('xflickr.favorites')+': '+_('xflickr.no_favorites')+'</span><br />',
				'</tpl>',
				'<tpl if="favorites_count &gt; 0">',
				'<span>'+_('xflickr.favorites')+': <b>{favorites_count}</b> '+_('xflickr.favorited_photos')+'</span><br />',
				'</tpl>',
				'<tpl if="contacts_count == 0">',
				'<span>'+_('xflickr.contacts')+': '+_('xflickr.no_contacts')+'</span><br />',
				'</tpl>',
				'<tpl if="contacts_count &gt; 0">',
				'<span>'+_('xflickr.contacts')+': <b>{contacts_count}</b> '+_('xflickr.added_contacts')+'</span><br />',
				'</tpl>',
				'<tpl if="ispro == 1">',
				'<span>'+_('xflickr.limitations')+': '+_('xflickr.pro_limitations')+'</span><br />',
				'</tpl>',
				'<tpl if="ispro == 0">',
				'<span>'+_('xflickr.limitations')+': '+_('xflickr.size')+' - '+_('xflickr.photos')+' <b>{photo_max}</b>Mb, '+_('xflickr.videos')+' <b>{video_max}</b>Mb. '+_('xflickr.bandwidth')+' - '+_('xflickr.max')+' <b>{bw_max}</b>Mb, '+_('xflickr.used')+' <b>{bw_used}</b>Mb, '+_('xflickr.remaining')+' <b>{bw_remaining}</b>Mb. '+_('xflickr.sets')+' - '+_('xflickr.created')+' <b>{sets_created}</b>, '+_('xflickr.remaining')+' <b>{sets_remaining}</b> </span><br />',
				'</tpl>',

                '</div>',
			'</tpl></div>'
		);
		this.tpl.compile();
		return this.tpl;
	}
});
Ext.reg('xflickr-panel-userinfo',XFlickr.panel.Userinfo);
