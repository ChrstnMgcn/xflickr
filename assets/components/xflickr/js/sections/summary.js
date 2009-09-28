Ext.onReady(function() {
    Ext.QuickTips.init();
    MODx.load({ xtype: 'xflickr-page-summary'});
});

XFlickr.page.Summary = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        components: [{
            xtype: 'xflickr-panel-summary',
            renderTo: 'xflickr-panel'
        }]
    }); 
    XFlickr.page.Summary.superclass.constructor.call(this,config);
};
Ext.extend(XFlickr.page.Summary,MODx.Component);
Ext.reg('xflickr-page-summary',XFlickr.page.Summary);