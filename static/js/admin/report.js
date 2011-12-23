/**
 * ModernWeb
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.modernweb.pl/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@modernweb.pl so we can send you a copy immediately.
 *
 * @category    Pimcore
 * @package     Plugin_Poll
 * @author      Rafał Gałka <rafal@modernweb.pl>
 * @copyright   Copyright (c) 2007-2011 ModernWeb (http://www.modernweb.pl)
 * @license     http://www.modernweb.pl/license/new-bsd     New BSD License
 */
pimcore.registerNS("pimcore.plugin.poll.report");

pimcore.plugin.poll.report = Class.create({

    initialize: function(poll) {
        this.poll = poll;
    },

    getLayout: function() {
        this.store = new Ext.data.Store({
            reader: new Ext.data.JsonReader({
                root: 'answers'
            }, [
                {name: 'title', allowBlank: false},
                {name: 'responses', allowBlank: false}
            ]),
            data: this.poll.data
        });
        this.store.loadData(this.poll.data);

        this.layout = new Ext.Panel({
            title: t('report'),
            border: false,
            layout: "fit",
            iconCls: "plugin_poll_icon_report",
            padding: 10,
            items: {
                xtype: 'piechart',
                store: this.store,
                dataField: 'responses',
                categoryField : 'title',
                extraStyle: {
                    legend:{
                        display: 'top',
                        padding: 10,
                        border:{
                           color: '#CBCBCB',
                           size: 1
                        }
                    }
                }
            }
        });

        return this.layout;
    }

});
