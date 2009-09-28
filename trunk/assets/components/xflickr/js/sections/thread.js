Ext.onReady(function() {
    MODx.load({ 
        xtype: 'xflickr-page-thread'
        ,thread: XFlickr.request.thread
    });
});

XFlickr.page.Thread = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        formpanel: 'xflickr-panel-thread'
        ,buttons: [{
            text: _('xflickr.back_to_threads')
            ,id: 'xflickr-btn-back'
            ,handler: function() {
                location.href = '?a='+XFlickr.request.a+'&action=home';
            }
            ,scope: this
        }]
        ,components: [{
            xtype: 'xflickr-panel-thread'
            ,renderTo: 'xflickr-panel-thread-div'
            ,thread: config.thread
        }]
    }); 
    XFlickr.page.Thread.superclass.constructor.call(this,config);
};
Ext.extend(XFlickr.page.Thread,MODx.Component);
Ext.reg('xflickr-page-thread',XFlickr.page.Thread);