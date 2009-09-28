Ext.onReady(function() {
	Ext.QuickTips.init();
    MODx.load({ xtype: 'xflickr-page-auth'});
});

XFlickr.page.Auth = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        components: [{
            xtype: 'xflickr-panel-auth',
			renderTo: 'xflickr-panel'
        }]
    });
    XFlickr.page.Auth.superclass.constructor.call(this,config);
};
Ext.extend(XFlickr.page.Auth,MODx.Component);
Ext.reg('xflickr-page-auth',XFlickr.page.Auth);