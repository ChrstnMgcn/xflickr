var XFlickr = function(config) {
    config = config || {};
    XFlickr.superclass.constructor.call(this,config);
};
Ext.extend(XFlickr,Ext.Component,{
    page:{},window:{},grid:{},tree:{},panel:{},combo:{},menu:{},config: {}
});
Ext.reg('xflickr',XFlickr);

var XFlickr = new XFlickr();