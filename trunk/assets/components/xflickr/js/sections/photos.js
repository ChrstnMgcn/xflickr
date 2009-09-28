Ext.onReady(function() {
	Ext.QuickTips.init();
    MODx.load({ xtype: 'xflickr-page-photos'});
});

XFlickr.page.Photos = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        components: [{
            xtype: 'xflickr-panel-photos',
            renderTo: 'xflickr-panel'
        }]
    }); 
    XFlickr.page.Photos.superclass.constructor.call(this,config);
};
Ext.extend(XFlickr.page.Photos,MODx.Component);
Ext.reg('xflickr-page-photos',XFlickr.page.Photos);