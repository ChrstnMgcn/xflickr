XFlickr.Navigation = function(config) {
    config = config || {};
    Ext.apply(config,{
        //border: false,
        items: [{
            text: _('xflickr.summary'),
            iconCls: 'xf-summary16',
            handler: function() {
                location.href = '?a='+MODx.request.a+'&action=summary';
            }
        }, '-', {
            xtype:'splitbutton',
            text: _('xflickr.photos'),
            iconCls: 'xf-photos16',
            handler: function() {
                location.href = '?a='+MODx.request.a+'&action=photos';
            },
            menu: [{
                text: _('xflickr.photostream'),
                iconCls: 'xf-stream16',
                handler: function() {
                    location.href = '?a='+MODx.request.a+'&action=photos';
                }
            },{
                text: _('xflickr.upload'),
                iconCls: 'xf-upload16',
                handler: function() {
                    location.href = '?a='+MODx.request.a+'&action=upload';
                }
            }]
        }, '->' ,{
            text: _('xflickr.clear_cache'),
            iconCls: 'xf-clear16',
            handler: function() {
                location.href = '?a='+MODx.request.a+'&action=clear';
            }
        }]
    });
    XFlickr.Navigation.superclass.constructor.call(this,config);
};
Ext.extend(XFlickr.Navigation, Ext.Toolbar);
Ext.reg('xflickr-navigation', XFlickr.Navigation);
