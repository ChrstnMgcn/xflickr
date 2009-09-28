XFlickr.panel.Auth = function(config) {
    config = config || {};
    Ext.apply(config,{
        border: false,
        baseCls: 'modx-panel',
        items: [{
            html: '<h2>'+_('xflickr.auth')+'</h2>',
            border: false,
            cls: 'modx-page-header'
        },{
            xtype: 'panel',
            defaults: {
                style: 'padding: 1em .5em;'
            },
            items: [{
                html: '<p>'+_('xflickr.auth_intro')+'</p>',
                border: false
            },{
                xtype: 'xflickr-panel-authenticate',
                preventRender: true
            }]
        }]
    });
    XFlickr.panel.Auth.superclass.constructor.call(this,config);
};
Ext.extend(XFlickr.panel.Auth,MODx.Panel);
Ext.reg('xflickr-panel-auth',XFlickr.panel.Auth);


XFlickr.panel.Authenticate = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'xflickr-panel-authenticate',
        url: XFlickr.config.connector_url,
        baseParams: {},
        items: [{
            xtype: 'panel',
			id: 'xflickr-form-auth',
            preventRender: true,
            defaults: {
                autoHeight: true,
                layout: 'form',
                labelWidth: 150,
				border: false,
                bodyStyle: 'padding: 5px'
            },
			items: this.getFields(config),
			bbar: this.getButtons(config)
        }],
        listeners: {
			'setup': {fn:this.setup,scope:this},
			'success': {fn:this.success,scope:this},
			'beforeSubmit': {fn:this.beforeSubmit,scope:this}
        }
    });
    XFlickr.panel.Authenticate.superclass.constructor.call(this,config);
};
Ext.extend(XFlickr.panel.Authenticate,MODx.FormPanel,{
    setup: function() {
        MODx.Ajax.request({
            url: XFlickr.config.connector_url,
            params: {
                action: 'mgr/auth/getauthsettings'
            },
            listeners: {
                'success': {fn:function(r) {
                    this.getForm().setValues(r.object);
					var lnktpl = '<p style="padding: 1em"><a href="{auth_link}" target="_blank">{auth_link}</a></p>';
					var tpl = new Ext.DomHelper.createTemplate(lnktpl);
					tpl.overwrite('xflickr-auth-link', {
						auth_link: r.object.auth_link
					});
					Ext.select('#xflickr-auth-link').highlight('#c3daf9', {block: true, duration: 3});
                    this.fireEvent('ready',r.object);
                },scope:this}
            }
        });
    },
    beforeSubmit: function(o) {
        var g = Ext.getCmp('modx-grid-user-settings');
        var h = Ext.getCmp('modx-grid-user-groups');
        Ext.apply(o.form.baseParams,{
            settings: g ? g.encodeModified() : {}
            ,groups: h.encode()
        });
    },
    newAuthLink: function(p) {
		MODx.Ajax.request({
			url: XFlickr.config.connector_url,
			params: {
				action: 'mgr/auth/getnewauthlink',
				key: p['key'],
				secret: p['secret'],
				frob: p['frob']
			},
			listeners: {
				'success': {fn:function(r) {
					this.getForm().setValues(r.object);
					//Ext.getCmp('xflickr-form-auth').getForm().setValues(r.object);
					var lnktpl = '<p style="padding: 1em"><a href="{auth_link}" target="_blank">{auth_link}</a></p>';
					var tpl = new Ext.DomHelper.createTemplate(lnktpl);
					tpl.overwrite('xflickr-auth-link', {
						auth_link: r.object.auth_link
					});
					Ext.select('#xflickr-auth-link').highlight('#c3daf9', {block: true, duration: 3});
					this.fireEvent('ready',r.object);
				},scope:this}
			}
		});
	},
    confirmAuth: function(p) {
		MODx.Ajax.request({
			url: XFlickr.config.connector_url,
			params: {
				action: 'mgr/auth/savetoken',
				//key: p['key'],
				//secret: p['secret'],
				frob: p['frob']
			},
			listeners: {
				'success': {fn:function() {
					location.href = '?a='+MODx.request.a
				},scope:this}
			}
		});
	},
    success: function(o) {
        if (Ext.getCmp('modx-user-passwordnotifymethod-s').getValue() === true && o.result.message != '') {
            Ext.Msg.hide();
            Ext.Msg.show({
                title: _('password_notification')
                ,msg: o.result.message
                ,buttons: Ext.Msg.OK
                ,fn: function(btn) {
                    if (btn == 'ok') { 
                        location.href = '?a='+MODx.action['security/user/update']+'&id='+o.result.object.id; 
                    }
                    return false;
                }
            });
        } else if (this.config.user == 0) {            
            location.href = '?a='+MODx.action['security/user/update']+'&id='+o.result.object.id;
        }
    }
    
    ,showNewPassword: function(cb,v) {
        var el = Ext.getCmp('modx-user-panel-newpassword').getEl();
        if (v) {
            el.slideIn('t',{useDisplay:true});
        } else {
            el.slideOut('t',{useDisplay:true});
        }
    },
    
    getFields: function(config) {
        var f = [{
            items: [{
				id: 'xflickr-api_key',
				name: 'api_key',
				fieldLabel: _('xflickr.api_key'),
				xtype: 'textfield',
				width: 300,
				maxLength: 255,
				allowBlank: false
            },{
				id: 'xflickr-api_secret',
				name: 'api_secret',
				fieldLabel: _('xflickr.api_secret'),
				xtype: 'textfield',
				width: 300,
				maxLength: 255,
				allowBlank: false
            },{
				id: 'xflickr-frob',
				name: 'frob',
				xtype: 'hidden'
            },{
                id: 'xflickr-panel-frob',
				xtype: 'panel',
				preventRender: true,
				border: true,
				defaults: {
					autoHeight: true,
					bodyStyle: 'padding: 1.5em;'
				},
				items: [{
					html: '<p>'+_('xflickr.authlink_desc')+'</p>',
					border: false,
					id: 'xflickr-auth-desc'
				},{
					html: '<p> </p>',
					border: false,
					id: 'xflickr-auth-link'
				}]
            }]
        }];
        return f;
    },
	getButtons: function(config) {
		var b = [{
			text: _('xflickr.new_auth_link'),
			cls: 'x-btn-text-icon',
			handler: function() {
				var p = [];
				p['key'] = Ext.getCmp('xflickr-api_key').getValue();
				p['secret'] = Ext.getCmp('xflickr-api_secret').getValue();
				p['frob'] = Ext.getCmp('xflickr-frob').getValue();
				this.newAuthLink(p)
			},
			scope: this
		},{
			text: _('xflickr.confirm_auth'),
			cls: 'x-btn-text-icon',
			handler: function() {
				var p = [];
				//p['key'] = Ext.getCmp('xflickr-api_key').getValue();
				//p['secret'] = Ext.getCmp('xflickr-api_secret').getValue();
				p['frob'] = Ext.getCmp('xflickr-frob').getValue();
				this.confirmAuth(p)
			},
			scope: this
		}]
		return b;
		//{disabled:true, text: 'Finish', scope: this, handler: this.finish, itemId: 'finishBtn'}
	}
});
Ext.reg('xflickr-panel-authenticate',XFlickr.panel.Authenticate);
